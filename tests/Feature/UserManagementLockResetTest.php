<?php

use App\Models\User;
use App\Models\Role;

beforeEach(fn () => $this->seed());

it('updates roles and password in one request', function () {
    $admin = User::where('email', 'admin@laravel-base.local')->first();
    $this->actingAs($admin);
    $u = User::factory()->create(['username' => 'upd'.time()]);
    $role = Role::where('name', 'staff')->first();

    $this->put(route('users.update', $u), [
        'name' => $u->name,
        'username' => $u->username,
        'email' => $u->email,
        'phone' => $u->phone,
        'password' => 'NewPass@12345',
        'password_confirmation' => 'NewPass@12345',
        'roles' => [$role->id],
    ])->assertRedirect(route('users.index'));

    $u->refresh();
    expect($u->hasRole('staff'))->toBeTrue();
    expect(\Illuminate\Support\Facades\Hash::check('NewPass@12345', $u->password))->toBeTrue();
});

it('unlocks a locked account', function () {
    $admin = User::where('email', 'admin@laravel-base.local')->first();
    $this->actingAs($admin);
    $u = User::factory()->create(['username' => 'lock'.time(), 'locked_until' => now()->addMinutes(15)]);

    expect($u->isLocked())->toBeTrue();

    $this->post(route('users.unlock', $u))
        ->assertRedirect(route('users.index'))
        ->assertSessionHas('success');

    expect($u->fresh()->isLocked())->toBeFalse();
});

it('sends a reset link (broker returns sent)', function () {
    $admin = User::where('email', 'admin@laravel-base.local')->first();
    $this->actingAs($admin);
    $u = User::factory()->create(['username' => 'reset'.time()]);

    // MAIL_MAILER=log in test env -> broker still returns RESET_LINK_SENT
    $this->post(route('users.reset-link', $u))
        ->assertRedirect(route('users.index'))
        ->assertSessionHas('success');
});

it('logs unlock and reset-link actions to audit', function () {
    $admin = User::where('email', 'admin@laravel-base.local')->first();
    $this->actingAs($admin);
    $u = User::factory()->create(['username' => 'aud'.time(), 'locked_until' => now()->addMinutes(5)]);

    $this->post(route('users.unlock', $u));
    $this->post(route('users.reset-link', $u));

    $descs = \Spatie\Activitylog\Models\Activity::where('subject_id', $u->id)
        ->pluck('description')->toArray();
    expect($descs)->toContain('user_unlocked');
    expect($descs)->toContain('user_reset_link_sent');
});
