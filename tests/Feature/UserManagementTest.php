<?php

use App\Models\User;
use App\Models\Role;

beforeEach(function () {
    $this->seed();
    $this->user = User::where('email', 'admin@laravel-base.local')->first();
    $this->actingAs($this->user);
});

it('lists users', function () {
    $this->get(route('users.index'))->assertOk();
});

it('creates a user with role', function () {
    $role = Role::first();
    $this->post(route('users.store'), [
        'name' => 'Jane Doe',
        'username' => 'jane',
        'email' => 'jane@example.com',
        'password' => 'Secret@123456',
        'password_confirmation' => 'Secret@123456',
        'roles' => [$role->id],
    ])->assertRedirect(route('users.index'));

    $u = User::where('username', 'jane')->first();
    expect($u)->not->toBeNull();
    expect($u->hasRole($role->name))->toBeTrue();
});

it('soft deletes and restores a user', function () {
    $u = User::create([
        'name' => 'Temp User', 'username' => 'temp', 'email' => 'temp@example.com',
        'password' => bcrypt('Secret@123456'),
    ]);
    $this->delete(route('users.destroy', $u))->assertRedirect();
    expect(User::find($u->id))->toBeNull();
    expect(User::withTrashed()->find($u->id))->not->toBeNull();

    $this->post(route('users.restore', $u->id))->assertRedirect();
    expect(User::find($u->id))->not->toBeNull();
});

it('updates own profile', function () {
    $this->post(route('profile.update'), [
        'name' => 'Updated Name',
        'phone' => '+6281299998888',
    ])->assertRedirect();
    expect($this->user->fresh()->name)->toBe('Updated Name');
});

it('changes own password', function () {
    $this->post(route('profile.password'), [
        'current_password' => 'Admin@base12345',
        'password' => 'NewSecret@123456',
        'password_confirmation' => 'NewSecret@123456',
    ])->assertRedirect();
    expect(Auth::attempt(['username' => 'superadmin', 'password' => 'NewSecret@123456']))->toBeTrue();
});
