<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);
beforeEach(fn () => $this->seed());

it('denies logs page to unauthenticated', function () {
    $this->get('/logs')->assertRedirect('/login');
});

it('shows logs page to user with logs.view', function () {
    $u = User::where('email', 'admin@laravel-base.local')->first();
    $this->actingAs($u)
        ->get('/logs')
        ->assertOk()
        ->assertSee('System Logs'); // our AdminLTE view title
});

it('forbids logs page to user without logs.view', function () {
    $staff = User::where('username', 'staff')->first()
        ?? User::factory()->create(['username' => 'staff2', 'email' => 'staff2@laravel-base.local']);
    $staff->assignRole('staff'); // staff has only user.view + audit.view
    $this->actingAs($staff)->get('/logs')->assertForbidden();
});
