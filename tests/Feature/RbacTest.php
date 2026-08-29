<?php

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;

beforeEach(function () {
    $this->seed();
    $this->user = User::where('email', 'admin@laravel-base.local')->first();
    $this->actingAs($this->user);
});

it('lists roles', function () {
    $this->get(route('roles.index'))->assertOk();
});

it('creates a role with permissions', function () {
    $perm = Permission::first();
    $this->post(route('roles.store'), [
        'name' => 'editor',
        'permissions' => [$perm->id],
    ])->assertRedirect(route('roles.index'));

    $role = Role::where('name', 'editor')->first();
    expect($role)->not->toBeNull();
    expect($role->hasPermissionTo($perm->name))->toBeTrue();
});

it('prevents deleting super-admin', function () {
    $sa = Role::where('name', 'super-admin')->first();
    $this->delete(route('roles.destroy', $sa))->assertRedirect();
    expect(Role::where('name', 'super-admin')->exists())->toBeTrue();
});

it('searches roles by name', function () {
    Role::create(['name' => 'zztemp_role_alpha', 'guard_name' => 'web']);
    $this->get(route('roles.index', ['q' => 'zztemp_role_alpha']))
        ->assertOk()
        ->assertSee('zztemp_role_alpha')
        ->assertDontSee('super-admin');
});

it('searches permissions by name', function () {
    Permission::create(['name' => 'zztemp_perm_alpha', 'guard_name' => 'web']);
    $this->get(route('permissions.index', ['q' => 'zztemp_perm_alpha']))
        ->assertOk()
        ->assertSee('zztemp_perm_alpha');
});
