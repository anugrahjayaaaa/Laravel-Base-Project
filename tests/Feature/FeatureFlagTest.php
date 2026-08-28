<?php

use App\Models\Feature;
use App\Models\Role;
use App\Models\User;

beforeEach(function () {
    $this->seed();
});

function makeUserWith(array $perms, bool $manager = false): User
{
    $role = Role::create(['name' => 'tmp_'.uniqid(), 'guard_name' => 'web']);
    $role->syncPermissions($perms);
    if ($manager) {
        $role->givePermissionTo('feature.manage');
    }
    $user = User::create([
        'name' => 'Tmp', 'username' => 'tmp_'.uniqid(), 'phone' => '+62800000000',
        'email' => uniqid().'@example.com', 'password' => bcrypt('x'), 'email_verified_at' => now(),
    ]);
    $user->assignRole($role);

    return $user;
}

it('feature() helper returns enabled state and fails closed when missing', function () {
    expect(feature('users'))->toBeTrue();
    expect(feature('does-not-exist'))->toBeFalse();
});

it('lists feature flags (manager only)', function () {
    $this->actingAs(makeUserWith([], true));
    $this->get(route('features.index'))->assertOk()->assertSee('Feature Flags');
});

it('toggles a feature off and back on', function () {
    $this->actingAs(makeUserWith([], true));
    $this->post(route('features.toggle', 'users'), ['enabled' => '0'])
        ->assertRedirect(route('features.index'));
    expect(feature('users'))->toBeFalse();

    $this->post(route('features.toggle', 'users'), ['enabled' => '1'])
        ->assertRedirect(route('features.index'));
    expect(feature('users'))->toBeTrue();
});

it('blocks a non-manager when feature is off, even with permission', function () {
    Feature::where('slug', 'users')->update(['enabled' => false]);
    $user = makeUserWith(['user.view']); // has permission, no feature.manage

    $this->actingAs($user)->get(route('users.index'))->assertNotFound();
});

it('lets a feature.manage holder bypass the off gate', function () {
    Feature::where('slug', 'users')->update(['enabled' => false]);
    $manager = makeUserWith(['user.view'], true); // also holds feature.manage

    $this->actingAs($manager)->get(route('users.index'))->assertOk();
});

it('allows a module route when its feature is on', function () {
    Feature::where('slug', 'users')->update(['enabled' => true]);
    $user = makeUserWith(['user.view']);

    $this->actingAs($user)->get(route('users.index'))->assertOk();
});

it('hides a feature-off menu item from a non-manager sidebar', function () {
    Feature::where('slug', 'users')->update(['enabled' => false]);
    $user = makeUserWith(['user.view']);

    $this->actingAs($user)->get(route('dashboard'))
        ->assertOk()
        ->assertDontSee('/users');
});

it('shows a feature-off menu item to a feature.manage holder', function () {
    Feature::where('slug', 'users')->update(['enabled' => false]);
    $manager = makeUserWith(['user.view'], true);

    $this->actingAs($manager)->get(route('dashboard'))
        ->assertOk()
        ->assertSee('/users');
});
