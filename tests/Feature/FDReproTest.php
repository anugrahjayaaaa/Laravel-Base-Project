<?php
use App\Models\User;
use App\Models\Role;

beforeEach(fn () => $this->seed());

it('force-delete POST (correct, no _method spoof) redirects', function () {
    $this->actingAs(User::where('email', 'admin@laravel-base.local')->first());
    $role = Role::create(['name' => 'fd_ok', 'guard_name' => 'web']);
    $role->delete();
    $this->post(route('roles.forceDelete', $role->id))->assertRedirect();
    expect(Role::withTrashed()->find($role->id))->toBeNull();
});

it('force-delete with _method=DELETE (buggy modal) is rejected', function () {
    $this->actingAs(User::where('email', 'admin@laravel-base.local')->first());
    $role = Role::create(['name' => 'fd_bug', 'guard_name' => 'web']);
    $role->delete();
    // mirrors old @method('DELETE') modal -> route is POST only
    $this->post(route('roles.forceDelete', $role->id), ['_method' => 'DELETE'])->assertStatus(405);
});
