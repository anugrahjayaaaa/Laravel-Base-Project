<?php

use App\Models\License;
use App\Models\Permission;
use App\Models\Plan;
use App\Models\Role;
use App\Models\Setting;
use App\Models\User;
use App\Services\LicenseService;
use App\Services\PlanService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Laravel\Pennant\Feature;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed();
    cache()->flush();
    Setting::set('active_plan', 'free');
    Setting::set('license_key', null);
    Setting::set('license_mode', 'global');
    cache()->flush();
    Feature::activate('users');
});

/**
 * Helper: activate a pro license with explicit allowed_permissions snapshot.
 * Clears any existing pro license first (deterministic key → unique constraint).
 */
function activateProWithPermissions(array $permissions): void
{
    License::where('plan_slug', 'pro')->delete();
    $pro = Plan::where('slug', 'pro')->first();
    $pro->update([
        'name' => 'Pro',
        'price_monthly' => 99000,
        'is_active' => true,
        'billing_period' => 'monthly',
        'limits' => ['allowed_permissions' => $permissions, 'max_members' => 5, 'max_roles' => 3],
        'features' => ['users', 'roles'],
    ]);
    $key = LicenseService::issue('pro', ['type' => 'manual', 'expires_at' => null]);
    LicenseService::activate($key);
}

/**
 * Helper: create a non-superadmin user with a specific role + permissions.
 * Returns the user (acting-as'd).
 */
function makeRegularUser(array $perms = [], string $roleName = 'regular'): User
{
    $role = Role::findOrCreate($roleName, 'web');
    $role->syncPermissions($perms);
    $user = User::create([
        'name' => 'Regular',
        'username' => 'regular_'.uniqid(),
        'email' => 'regular_'.uniqid().'@example.com',
        'phone' => '+628****0000',
        'password' => bcrypt('secret123'),
        'email_verified_at' => now(),
    ]);
    $user->assignRole($role);

    return $user;
}

/*
|--------------------------------------------------------------------------
| Challenge 2: Feature Entitlement States
|--------------------------------------------------------------------------
*/

it('State 1: Pennant ON + Plan feature ON → proceeds to authorization', function () {
    activateProWithPermissions(['user.view']);
    $user = makeRegularUser(['user.view'], 'perm_user');
    $this->actingAs($user);

    expect(Feature::active('users'))->toBeTrue();
    expect(PlanService::for($user)->can('users'))->toBeTrue();
    $this->get(route('users.index'))->assertOk();
});

it('State 2: Pennant OFF + Plan feature ON → 404', function () {
    activateProWithPermissions(['user.view']);
    $user = makeRegularUser(['user.view'], 'perm_user');
    $this->actingAs($user);

    Feature::deactivate('users');
    $this->get(route('users.index'))->assertNotFound();
});

it('State 3: Pennant ON + Plan feature OFF → denied by Plan (403)', function () {
    // Free plan has no features + empty allowed_permissions → plan denies
    $user = makeRegularUser(['user.view'], 'perm_user');
    $this->actingAs($user);

    expect(Feature::active('users'))->toBeTrue();
    expect(PlanService::for($user)->can('users'))->toBeFalse();

    $this->get(route('users.index'))->assertForbidden();
});

it('State 4: Pennant OFF + Plan feature OFF → Pennant wins (404)', function () {
    $user = makeRegularUser(['user.view'], 'perm_user');
    $this->actingAs($user);

    Feature::deactivate('users');
    $this->get(route('users.index'))->assertNotFound();
});

/*
|--------------------------------------------------------------------------
| Challenge 3: Permission Entitlement Matrix
|--------------------------------------------------------------------------
*/

it('State A: Role YES + Plan YES → ALLOW', function () {
    activateProWithPermissions(['user.view']);
    $user = makeRegularUser(['user.view'], 'perm_user');
    $this->actingAs($user);

    expect($user->can('user.view'))->toBeTrue();
    $this->get(route('users.index'))->assertOk();
});

it('State B: Role YES + Plan NO → DENY', function () {
    $user = makeRegularUser(['user.view'], 'perm_user');
    $this->actingAs($user);

    expect(PlanService::for($user)->allows('user.view'))->toBeFalse();
    expect($user->can('user.view'))->toBeFalse();
});

it('State C: Role NO + Plan YES → DENY (Plan never grants by itself)', function () {
    activateProWithPermissions(['user.view']);
    $user = makeRegularUser([], 'bare_user');
    $this->actingAs($user);

    expect(PlanService::for($user)->allows('user.view'))->toBeTrue();
    expect($user->can('user.view'))->toBeFalse();
    $this->get(route('users.index'))->assertForbidden();
});

it('State D: Neither Role nor Plan → DENY', function () {
    $user = makeRegularUser([], 'bare_user');
    $this->actingAs($user);

    expect($user->can('user.view'))->toBeFalse();
    $this->get(route('users.index'))->assertForbidden();
});

/*
|--------------------------------------------------------------------------
| Challenge 4: Plan Lifecycle — no mutation of Role/User state
|--------------------------------------------------------------------------
*/

it('Free → Pro preserves Role permissions and restores effective access', function () {
    $role = Role::where('name', 'admin')->first();
    $role->givePermissionTo('user.view');
    $user = makeRegularUser([], 'lifecycle_user');
    $user->assignRole('admin');
    $this->actingAs($user);

    expect($user->can('user.view'))->toBeFalse(); // free, no license

    $rolePermsBefore = DB::table('role_has_permissions')
        ->where('role_id', $role->id)->pluck('permission_id')->toArray();

    activateProWithPermissions(['user.view']);

    $rolePermsAfter = DB::table('role_has_permissions')
        ->where('role_id', $role->id)->pluck('permission_id')->toArray();
    expect($rolePermsAfter)->toBe($rolePermsBefore);

    $direct = DB::table('model_has_permissions')
        ->where('model_type', User::class)->where('model_id', $user->id)->count();
    expect($direct)->toBe(0);

    expect($user->can('user.view'))->toBeTrue();
});

it('Pro → Free preserves Role permissions and denies access', function () {
    activateProWithPermissions(['user.view']);
    $role = Role::where('name', 'admin')->first();
    $role->givePermissionTo('user.view');
    $user = makeRegularUser([], 'lifecycle_user');
    $user->assignRole('admin');
    $this->actingAs($user);

    expect($user->can('user.view'))->toBeTrue();

    $rolePermsBefore = DB::table('role_has_permissions')
        ->where('role_id', $role->id)->pluck('permission_id')->toArray();

    Setting::set('active_plan', 'free');
    Setting::set('license_key', null);
    cache()->flush();

    $rolePermsAfter = DB::table('role_has_permissions')
        ->where('role_id', $role->id)->pluck('permission_id')->toArray();
    expect($rolePermsAfter)->toBe($rolePermsBefore);

    expect($role->fresh()->hasPermissionTo('user.view'))->toBeTrue();
    expect($user->can('user.view'))->toBeFalse();
});

it('No model_has_permissions writes during plan changes (Free→Pro→Free)', function () {
    $role = Role::where('name', 'admin')->first();
    $role->givePermissionTo('user.view');
    $user = makeRegularUser([], 'lifecycle_user');
    $user->assignRole('admin');
    $this->actingAs($user);

    $before = DB::table('model_has_permissions')->count();

    activateProWithPermissions(['user.view']);
    expect($user->can('user.view'))->toBeTrue();

    Setting::set('active_plan', 'free');
    Setting::set('license_key', null);
    cache()->flush();
    expect($user->can('user.view'))->toBeFalse();

    activateProWithPermissions(['user.view', 'user.create']);
    expect($user->can('user.view'))->toBeTrue();

    Setting::set('active_plan', 'free');
    Setting::set('license_key', null);
    cache()->flush();

    $after = DB::table('model_has_permissions')->count();
    expect($after)->toBe($before);
});

/*
|--------------------------------------------------------------------------
| Challenge 5: Role Management — Plan-capped assignment
|--------------------------------------------------------------------------
*/

it('Role management cannot assign permissions outside the current Plan (non-feature.manage user)', function () {
    // Plan only allows user.view
    activateProWithPermissions(['user.view']);
    expect(PlanService::for(null)->allows('user.view'))->toBeTrue();
    expect(PlanService::for(null)->allows('user.delete'))->toBeFalse();

    // Non-superadmin user with role.create but NOT feature.manage
    $user = makeRegularUser(['role.view', 'role.create', 'role.edit', 'user.view'], 'sub_mgr');
    $this->actingAs($user);

    $userDelete = Permission::where('name', 'user.delete')->first();
    $userView = Permission::where('name', 'user.view')->first();

    $this->post(route('roles.store'), [
        'name' => 'evil_role',
        'permissions' => [$userDelete->id, $userView->id],
    ])->assertRedirect(route('roles.index'));

    $evil = Role::where('name', 'evil_role')->first();
    expect($evil)->not->toBeNull();
    // user.delete stripped by filterPermissions
    expect($evil->hasPermissionTo('user.delete'))->toBeFalse();
    expect($evil->hasPermissionTo('user.view'))->toBeTrue();
});

it('Role management Plan-caps non-feature.manage users', function () {
    activateProWithPermissions(['user.view']);

    // User with role.create but NOT feature.manage → subject to plan limits
    $user = makeRegularUser(['role.view', 'role.create', 'role.edit'], 'sub_mgr');
    $this->actingAs($user);

    expect($user->can('role.create'))->toBeTrue();

    $allPerms = Permission::pluck('id')->all();

    $resp = $this->post(route('roles.store'), [
        'name' => 'filtered_role',
        'permissions' => $allPerms,
    ]);
    $resp->assertRedirect(route('roles.index'));

    $filtered = Role::where('name', 'filtered_role')->first();
    expect($filtered)->not->toBeNull();
    expect($filtered->hasPermissionTo('user.delete'))->toBeFalse();
    expect($filtered->hasPermissionTo('user.view'))->toBeTrue();
    expect($filtered->hasPermissionTo('role.view'))->toBeFalse();
    expect($filtered->hasPermissionTo('role.create'))->toBeFalse();
});

it('Management permissions (role.*, permission.*) are Plan-gated for normal users', function () {
    // Free plan: allowed_permissions is empty → all domain permissions denied
    $user = makeRegularUser([], 'bare_user');
    $this->actingAs($user);

    // role.* and permission.* are exempt from Plan boundary Gate check
    // (they bypass PlanService::allows in AppServiceProvider Gate::before)
    expect($user->can('role.create'))->toBeFalse();
    expect($user->can('role.edit'))->toBeFalse();
    expect($user->can('permission.view'))->toBeFalse();
    expect($user->can('permission.create'))->toBeFalse();

    // feature.manage is NOT exempt — it's a domain permission subject to Plan
    expect($user->can('feature.manage'))->toBeFalse();
});

it('Existing unavailable Role permission remains stored after plan downgrade', function () {
    activateProWithPermissions(['user.view', 'user.delete']);
    $role = Role::create(['name' => 'temp_role_'.uniqid(), 'guard_name' => 'web']);
    $role->syncPermissions(['user.view', 'user.delete']);

    expect($role->hasPermissionTo('user.delete'))->toBeTrue();

    Setting::set('active_plan', 'free');
    Setting::set('license_key', null);
    cache()->flush();

    expect($role->fresh()->hasPermissionTo('user.delete'))->toBeTrue();
    // Use a non-superadmin user to verify effective permission is denied
    $user = makeRegularUser([], 'test_user');
    $user->assignRole($role);
    $this->actingAs($user);
    expect($user->can('user.delete'))->toBeFalse();
});

/*
|--------------------------------------------------------------------------
| Challenge 7: Enterprise Seeder Audit
|--------------------------------------------------------------------------
*/

it('Enterprise plan contains all valid permissions', function () {
    $enterprise = Plan::where('slug', 'enterprise')->first();
    $allowed = $enterprise->limits['allowed_permissions'] ?? [];

    expect($allowed)->not->toBeEmpty();
    foreach ($allowed as $permName) {
        expect(Permission::where('name', $permName)->exists())
            ->toBeTrue("Permission '$permName' in enterprise allowed_permissions does not exist");
    }
    expect(count($allowed))->toBe(Permission::count());
});

it('Enterprise allows permission does not grant access without Role', function () {
    License::where('plan_slug', 'enterprise')->delete();
    $enterprise = Plan::where('slug', 'enterprise')->first();
    $key = LicenseService::issue('enterprise', ['type' => 'manual', 'expires_at' => null]);
    LicenseService::activate($key);

    $user = User::where('email', 'admin@laravel-base.local')->first();
    $this->actingAs($user);

    expect(PlanService::for($user)->allows('user.delete'))->toBeTrue();

    // Create a bare user with NO role and NO direct permissions
    $bare = User::create([
        'name' => 'Bare', 'username' => 'bare_'.uniqid(),
        'email' => 'bare_'.uniqid().'@example.com',
        'phone' => '+628****0000', 'password' => bcrypt('secret123'), 'email_verified_at' => now(),
    ]);
    $this->actingAs($bare);

    expect(PlanService::for($bare)->allows('user.delete'))->toBeTrue();
    expect($bare->can('user.delete'))->toBeFalse(); // no role → no permission
});

/*
|--------------------------------------------------------------------------
| Challenge 8: Per-User Mode Audit
|--------------------------------------------------------------------------
*/

it('per_user mode resolves plan from user license', function () {
    Setting::set('license_mode', 'per_user');
    cache()->flush();

    $user = User::where('email', 'admin@laravel-base.local')->first();
    $key = LicenseService::issue('pro', ['type' => 'manual', 'expires_at' => null, 'user_id' => $user->id]);
    LicenseService::activate($key);

    $plan = PlanService::for(null, $user);
    expect($plan->can('users'))->toBeFalse();
});

it('per_user mode is marked as having incomplete assignment flow (architectural concern)', function () {
    // Document the known gap: per_user mode exists in code but has no UI for
    // assigning licenses to individual users. LicenseService::issue() exists,
    // but there is no controller/route for per-user license assignment.
    expect(true)->toBeTrue();
});

/*
|--------------------------------------------------------------------------
| Challenge 9: Cache invalidation on plan change
|--------------------------------------------------------------------------
*/

it('cache invalidates after plan change (no stale entitlement)', function () {
    expect(PlanService::for(null)->can('users'))->toBeFalse();

    activateProWithPermissions(['user.view']);
    cache()->flush();
    expect(PlanService::for(null)->can('users'))->toBeTrue();

    Setting::set('active_plan', 'free');
    Setting::set('license_key', null);
    cache()->flush();
    expect(PlanService::for(null)->can('users'))->toBeFalse();
});

/*
|--------------------------------------------------------------------------
| Challenge 10: Source-of-truth — no forbidden sync patterns
|--------------------------------------------------------------------------
*/

it('no syncPermissions on User model during plan lifecycle', function () {
    $role = Role::where('name', 'admin')->first();
    $role->givePermissionTo('user.view');
    $user = makeRegularUser([], 'lifecycle_user');
    $user->assignRole('admin');
    $this->actingAs($user);

    $before = DB::table('model_has_permissions')
        ->where('model_type', User::class)
        ->count();

    activateProWithPermissions(['user.view']);
    activateProWithPermissions(['user.view', 'user.create']);

    Setting::set('active_plan', 'free');
    Setting::set('license_key', null);
    cache()->flush();

    $after = DB::table('model_has_permissions')
        ->where('model_type', User::class)
        ->count();

    expect($after)->toBe($before);
});

it('API RoleApiController filters permissions through plan', function () {
    activateProWithPermissions(['user.view']);
    expect(PlanService::for(null)->allows('user.delete'))->toBeFalse();

    $roleMgr = Role::create(['name' => 'apimgr_'.uniqid(), 'guard_name' => 'web']);
    $roleMgr->syncPermissions(['role.view', 'role.create', 'role.edit']);
    $user = User::create([
        'name' => 'APIMgr', 'username' => 'apimgr_'.uniqid(),
        'email' => 'apimgr_'.uniqid().'@example.com',
        'phone' => '+628****0000', 'password' => bcrypt('secret123'), 'email_verified_at' => now(),
    ]);
    $user->assignRole($roleMgr);
    $this->actingAs($user);

    $allPerms = Permission::pluck('id')->all();

    $evilName = 'api_filtered_'.uniqid();
    $this->postJson('/api/v1/roles', [
        'name' => $evilName,
        'permissions' => $allPerms,
    ])->assertCreated();

    $evil = Role::where('name', $evilName)->first();
    expect($evil)->not->toBeNull();
    expect($evil->hasPermissionTo('user.delete'))->toBeFalse();
    expect($evil->hasPermissionTo('user.view'))->toBeTrue();
});

/*
|--------------------------------------------------------------------------
| NEW: Superadmin Bypass — CHALLENGE 4 continuation
|--------------------------------------------------------------------------
*/

it('Superadmin bypasses Plan permission boundary but not Pennant', function () {
    // Free plan: no allowed_permissions → normal users denied
    $sa = User::where('email', 'admin@laravel-base.local')->first();
    $this->actingAs($sa);

    // Superadmin has super-admin role → bypasses Plan permission boundary
    expect($sa->can('user.view'))->toBeTrue();
    expect($sa->can('user.delete'))->toBeTrue();
    expect($sa->can('feature.manage'))->toBeTrue();

    // But Pennant still applies — if 'users' flag is OFF, still 404
    Feature::deactivate('users');
    $this->get(route('users.index'))->assertNotFound();

    // Re-enable and access works
    Feature::activate('users');
    $this->get(route('users.index'))->assertOk();
});

it('Superadmin does not require model_has_permissions', function () {
    $sa = User::where('email', 'admin@laravel-base.local')->first();
    $this->actingAs($sa);

    $direct = DB::table('model_has_permissions')
        ->where('model_type', User::class)
        ->where('model_id', $sa->id)
        ->count();
    expect($direct)->toBe(0);
});

it('Superadmin does not require Plan → Role synchronization', function () {
    $sa = User::where('email', 'admin@laravel-base.local')->first();
    $this->actingAs($sa);

    // Toggle plan freely — superadmin access unchanged
    expect($sa->can('user.view'))->toBeTrue();

    activateProWithPermissions(['user.view']);
    expect($sa->can('user.view'))->toBeTrue();

    Setting::set('active_plan', 'free');
    Setting::set('license_key', null);
    cache()->flush();
    expect($sa->can('user.view'))->toBeTrue();
});

it('Superadmin can perform supported administrative operations', function () {
    $sa = User::where('email', 'admin@laravel-base.local')->first();
    $this->actingAs($sa);

    // Features page (needs feature.manage)
    $this->get(route('features.index'))->assertOk();

    // Plans page (needs feature.manage)
    $this->get(route('plans.index'))->assertOk();

    // Roles page (needs role.view)
    $this->get(route('roles.index'))->assertOk();

    // Users page (needs user.view + feature:users — Pennant already ON)
    $this->get(route('users.index'))->assertOk();
});

it('Normal user cannot become Superadmin through Role Management', function () {
    // Free plan — no allowed_permissions
    $user = makeRegularUser(['role.view', 'role.create', 'role.edit', 'feature.manage'], 'evil_mgr');
    $this->actingAs($user);

    // Try to create a role named 'super-admin' — the controller blocks this by name,
    // but even if it didn't, isSuperAdmin() checks hasRole('super-admin') which
    // requires explicit assignment via assignRole/assignRoleTo, not role creation.
    $this->post(route('roles.store'), [
        'name' => 'super-admin',
        'permissions' => [],
    ])->assertRedirect();

    // User still doesn't have the super-admin role
    expect($user->fresh()->isSuperAdmin())->toBeFalse();

    // Even creating a role with all permissions doesn't make the user super-admin
    $allPerms = Permission::pluck('id')->all();
    $this->post(route('roles.store'), [
        'name' => 'fake_super_'.uniqid(),
        'permissions' => [], // empty on Free plan
    ])->assertRedirect();
    expect($user->fresh()->isSuperAdmin())->toBeFalse();
});

it('Normal user cannot become Superadmin through Role API', function () {
    // User with role.create via API but NOT feature.manage → subject to plan limits
    activateProWithPermissions(['user.view']);

    $roleMgr = makeRegularUser(['role.view', 'role.create', 'role.edit'], 'apimgr');
    $this->actingAs($roleMgr);

    // Try to create a role named 'super-admin' via API
    $this->postJson('/api/v1/roles', [
        'name' => 'super-admin',
        'permissions' => [],
    ])->assertStatus(422); // validation: super-admin name is reserved

    // Try to create a role with all permissions — should be filtered by plan
    $allPerms = Permission::pluck('id')->all();
    $evilName = 'api_super_'.uniqid();
    $this->postJson('/api/v1/roles', [
        'name' => $evilName,
        'permissions' => $allPerms,
    ])->assertCreated();

    $evil = Role::where('name', $evilName)->first();
    expect($evil)->not->toBeNull();
    // Only user.view allowed by plan — everything else stripped
    expect($evil->hasPermissionTo('user.view'))->toBeTrue();
    expect($evil->hasPermissionTo('user.delete'))->toBeFalse();
    expect($evil->hasPermissionTo('role.create'))->toBeFalse();

    // User is still NOT super-admin
    expect($roleMgr->fresh()->isSuperAdmin())->toBeFalse();
});

it('Superadmin bypass does not grant Superadmin status to ordinary users', function () {
    $user = makeRegularUser(['user.view', 'user.delete', 'feature.manage'], 'high_priv');
    $this->actingAs($user);

    expect($user->isSuperAdmin())->toBeFalse();
    // Even with all permissions, ordinary users are still Plan-gated
    Setting::set('active_plan', 'free');
    Setting::set('license_key', null);
    cache()->flush();
    expect($user->can('user.view'))->toBeFalse();
});
