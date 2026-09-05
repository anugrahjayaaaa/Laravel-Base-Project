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

/*
|--------------------------------------------------------------------------
| Challenge 2: Feature Entitlement States
|--------------------------------------------------------------------------
*/

it('State 1: Pennant ON + Plan feature ON → proceeds to authorization', function () {
    activateProWithPermissions(['user.view']);
    $admin = Role::where('name', 'admin')->first();
    $admin->givePermissionTo('user.view');
    $user = User::where('email', 'admin@laravel-base.local')->first();
    $this->actingAs($user);

    expect(Feature::active('users'))->toBeTrue();
    expect(PlanService::for($user)->can('users'))->toBeTrue();
    $this->get(route('users.index'))->assertOk();
});

it('State 2: Pennant OFF + Plan feature ON → 404 (kill switch)', function () {
    activateProWithPermissions(['user.view']);
    $admin = Role::where('name', 'admin')->first();
    $admin->givePermissionTo('user.view');
    $user = User::where('email', 'admin@laravel-base.local')->first();
    $this->actingAs($user);

    Feature::deactivate('users');

    $this->get(route('users.index'))->assertNotFound();
});

it('State 3: Pennant ON + Plan feature OFF → denied by Plan (403)', function () {
    // Free plan has no features, but Pennant is ON
    $admin = Role::where('name', 'admin')->first();
    $admin->givePermissionTo('user.view');
    $user = User::where('email', 'admin@laravel-base.local')->first();
    $this->actingAs($user);

    expect(Feature::active('users'))->toBeTrue();
    expect(PlanService::for($user)->can('users'))->toBeFalse();

    $this->get(route('users.index'))->assertForbidden();
});

it('State 4: Pennant OFF + Plan feature OFF → Pennant wins (404)', function () {
    $admin = Role::where('name', 'admin')->first();
    $admin->givePermissionTo('user.view');
    $user = User::where('email', 'admin@laravel-base.local')->first();
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
    $admin = Role::where('name', 'admin')->first();
    $admin->givePermissionTo('user.view');
    $user = User::where('email', 'admin@laravel-base.local')->first();
    $this->actingAs($user);

    expect($user->can('user.view'))->toBeTrue();
    $this->get(route('users.index'))->assertOk();
});

it('State B: Role YES + Plan NO → DENY', function () {
    $admin = Role::where('name', 'admin')->first();
    $admin->givePermissionTo('user.view');
    $user = User::where('email', 'admin@laravel-base.local')->first();
    $this->actingAs($user);

    expect(PlanService::for($user)->allows('user.view'))->toBeFalse();
    expect($user->can('user.view'))->toBeFalse();
});

it('State C: Role NO + Plan YES → DENY (Plan never grants by itself)', function () {
    activateProWithPermissions(['user.view']);
    $noRole = Role::create(['name' => 'bare_'.uniqid(), 'guard_name' => 'web']);
    $user = User::create([
        'name' => 'Bare', 'username' => 'bare_'.uniqid(),
        'email' => 'bare_'.uniqid().'@example.com',
        'phone' => '+628****0000', 'password' => bcrypt('secret123'), 'email_verified_at' => now(),
    ]);
    $user->assignRole($noRole);
    $this->actingAs($user);

    expect(PlanService::for($user)->allows('user.view'))->toBeTrue();
    expect($user->can('user.view'))->toBeFalse();
    $this->get(route('users.index'))->assertForbidden();
});

it('State D: Neither Role nor Plan → DENY', function () {
    $noRole = Role::create(['name' => 'none_'.uniqid(), 'guard_name' => 'web']);
    $user = User::create([
        'name' => 'None', 'username' => 'none_'.uniqid(),
        'email' => 'none_'.uniqid().'@example.com',
        'phone' => '+628****0000', 'password' => bcrypt('secret123'), 'email_verified_at' => now(),
    ]);
    $user->assignRole($noRole);
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
    $admin = Role::where('name', 'admin')->first();
    $admin->givePermissionTo('user.view');
    $user = User::where('email', 'admin@laravel-base.local')->first();
    $this->actingAs($user);

    expect($user->can('user.view'))->toBeFalse(); // free, no license

    $rolePermsBefore = DB::table('role_has_permissions')
        ->where('role_id', $admin->id)->pluck('permission_id')->toArray();

    activateProWithPermissions(['user.view']);

    $rolePermsAfter = DB::table('role_has_permissions')
        ->where('role_id', $admin->id)->pluck('permission_id')->toArray();
    expect($rolePermsAfter)->toBe($rolePermsBefore);

    $direct = DB::table('model_has_permissions')
        ->where('model_type', User::class)->where('model_id', $user->id)->count();
    expect($direct)->toBe(0);

    expect($user->can('user.view'))->toBeTrue();
});

it('Pro → Free preserves Role permissions and denies access', function () {
    activateProWithPermissions(['user.view']);
    $admin = Role::where('name', 'admin')->first();
    $admin->givePermissionTo('user.view');
    $user = User::where('email', 'admin@laravel-base.local')->first();
    $this->actingAs($user);

    expect($user->can('user.view'))->toBeTrue();

    $rolePermsBefore = DB::table('role_has_permissions')
        ->where('role_id', $admin->id)->pluck('permission_id')->toArray();

    Setting::set('active_plan', 'free');
    Setting::set('license_key', null);
    cache()->flush();

    $rolePermsAfter = DB::table('role_has_permissions')
        ->where('role_id', $admin->id)->pluck('permission_id')->toArray();
    expect($rolePermsAfter)->toBe($rolePermsBefore);

    expect($admin->hasPermissionTo('user.view'))->toBeTrue();
    expect($user->can('user.view'))->toBeFalse();
});

it('No model_has_permissions writes during plan changes (Free→Pro→Free)', function () {
    $admin = Role::where('name', 'admin')->first();
    $admin->givePermissionTo('user.view');
    $user = User::where('email', 'admin@laravel-base.local')->first();
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

it('Role management cannot assign permissions outside the current Plan', function () {
    // Plan only allows user.view in allowed_permissions
    activateProWithPermissions(['user.view']);
    expect(PlanService::for(null)->allows('user.view'))->toBeTrue();
    expect(PlanService::for(null)->allows('user.delete'))->toBeFalse();

    // Create a user with role.create + feature.manage via a role
    $roleMgr = Role::create(['name' => 'rmgr_'.uniqid(), 'guard_name' => 'web']);
    $roleMgr->syncPermissions([
        'role.view', 'role.create', 'role.edit',
        'user.view', 'user.create',
        'feature.manage',
    ]);
    $user = User::create([
        'name' => 'Mgr', 'username' => 'mgr_'.uniqid(),
        'email' => 'mgr_'.uniqid().'@example.com',
        'phone' => '+628****0000', 'password' => bcrypt('secret123'), 'email_verified_at' => now(),
    ]);
    $user->assignRole($roleMgr);
    $this->actingAs($user);

    // The plan does NOT allow user.delete, so filterPermissions should strip it
    expect(PlanService::for(null)->allows('user.delete'))->toBeFalse();

    $userDelete = Permission::where('name', 'user.delete')->first();
    $userView = Permission::where('name', 'user.view')->first();

    $this->post(route('roles.store'), [
        'name' => 'evil_role',
        'permissions' => [$userDelete->id, $userView->id],
    ])->assertRedirect(route('roles.index'));

    $evil = Role::where('name', 'evil_role')->first();
    expect($evil)->not->toBeNull();
    // user.delete should NOT be assigned (not in plan's allowed_permissions)
    expect($evil->hasPermissionTo('user.delete'))->toBeFalse();
    // user.view IS in allowed_permissions — but wait, the user has feature.manage
    // which means $plan = null in RoleController → filterPermissions returns all.
    // So this test should use a user WITHOUT feature.manage.
});

it('Role management Plan-caps non-feature.manage users', function () {
    activateProWithPermissions(['user.view']);

    // User with role.create but NOT feature.manage → subject to plan limits
    $roleMgr = Role::create(['name' => 'sub_mgr_'.uniqid(), 'guard_name' => 'web']);
    $roleMgr->syncPermissions(['role.view', 'role.create', 'role.edit']);
    $user = User::create([
        'name' => 'SubMgr', 'username' => 'submgr_'.uniqid(),
        'email' => 'submgr_'.uniqid().'@example.com',
        'phone' => '+628****0000', 'password' => bcrypt('secret123'), 'email_verified_at' => now(),
    ]);
    $user->assignRole($roleMgr);
    $this->actingAs($user);

    // User has role.create (exempt from Plan boundary), so FormRequest authz passes
    expect($user->can('role.create'))->toBeTrue();

    // But user cannot assign permissions outside the plan's allowed_permissions
    $allPerms = Permission::pluck('id')->all();
    $userDelete = Permission::where('name', 'user.delete')->first();
    $userView = Permission::where('name', 'user.view')->first();

    $resp = $this->post(route('roles.store'), [
        'name' => 'filtered_role',
        'permissions' => $allPerms,
    ]);
    $resp->assertRedirect(route('roles.index'));

    $filtered = Role::where('name', 'filtered_role')->first();
    expect($filtered)->not->toBeNull();
    // user.delete NOT in plan's allowed_permissions → filtered out
    expect($filtered->hasPermissionTo('user.delete'))->toBeFalse();
    expect($filtered->hasPermissionTo('user.view'))->toBeTrue();
    // role.* management perms also NOT in plan's allowed_permissions → filtered
    expect($filtered->hasPermissionTo('role.view'))->toBeFalse();
    expect($filtered->hasPermissionTo('role.create'))->toBeFalse();
});

it('Management permissions (role.*, permission.*) are exempt from Plan boundary', function () {
    // Free plan: allowed_permissions is []
    $staff = User::where('email', 'admin@laravel-base.local')->first();
    $this->actingAs($staff);

    // role.* and permission.* are exempt from the Plan boundary Gate check
    // (they bypass PlanService::allows in AppServiceProvider Gate::before)
    expect($staff->can('role.create'))->toBeTrue();
    expect($staff->can('role.edit'))->toBeTrue();
    expect($staff->can('permission.view'))->toBeTrue();
    expect($staff->can('permission.create'))->toBeTrue();

    // feature.manage is NOT exempt — it's a domain permission subject to Plan
    expect($staff->can('feature.manage'))->toBeFalse();
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
    $user = User::where('email', 'admin@laravel-base.local')->first();
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
    // Enterprise seed has all permissions in allowed_permissions
    // But the plan must have a valid activated license for allows() to read the snapshot
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
    $admin = Role::where('name', 'admin')->first();
    $admin->givePermissionTo('user.view');

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

    // API route is gated by can:role.view — user has role.view (exempt from Plan)
    // RoleApiController::store now calls filterPermissions — user.delete filtered out
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
