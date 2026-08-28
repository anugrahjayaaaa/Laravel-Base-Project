<?php

use App\Models\User;
use App\Models\NotificationRead;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Activitylog\Models\Activity;

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

it('marks notifications read for the user on view', function () {
    $u = User::where('email', 'admin@laravel-base.local')->first();

    expect(NotificationRead::where('user_id', $u->id)->count())->toBe(0);

    $this->actingAs($u)->get(route('notifications.index'));

    // all visible activities (up to 30) now have a read row for this user
    expect(NotificationRead::where('user_id', $u->id)->whereNotNull('read_at')->count())->toBeGreaterThan(0);
});

it('denies notifications page to user without audit.view', function () {
    $noPerms = User::create([
        'name' => 'No Perms', 'username' => 'noperms3',
        'email' => 'noperms3@example.com', 'password' => bcrypt('password'),
    ]); // no roles -> no audit.view
    $this->actingAs($noPerms)
        ->get(route('notifications.index'))
        ->assertForbidden();
});
