<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);
beforeEach(fn () => $this->seed());

it('shows notifications page with recent activity', function () {
    $u = User::where('email', 'admin@laravel-base.local')->first();
    $this->actingAs($u)
        ->get(route('notifications.index'))
        ->assertOk()
        ->assertSee('Notifications')
        ->assertSee('permission_created'); // guaranteed seeded audit row
});

it('denies notifications page to user without audit.view', function () {
    $noPerms = User::create([
        'name' => 'No Perms',
        'username' => 'noperms',
        'email' => 'noperms@example.com',
        'password' => bcrypt('password'),
    ]); // no roles -> no audit.view
    $this->actingAs($noPerms)
        ->get(route('notifications.index'))
        ->assertForbidden();
});
