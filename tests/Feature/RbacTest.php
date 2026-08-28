<?php

use App\Models\User;
use App\Models\Permission;
use App\Models\Role;

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

it('creates and updates a permission', function () {
    $this->post(route('permissions.store'), ['name' => 'report.view'])
        ->assertRedirect(route('permissions.index'));
    expect(Permission::where('name', 'report.view')->exists())->toBeTrue();

    $p = Permission::where('name', 'report.view')->first();
    $this->put(route('permissions.update', $p), ['name' => 'report.export'])
        ->assertRedirect(route('permissions.index'));
    expect(Permission::where('name', 'report.export')->exists())->toBeTrue();
});
