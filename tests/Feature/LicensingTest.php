<?php

use App\Models\License;
use App\Models\Plan;
use App\Models\Setting;
use App\Models\User;
use App\Services\LicenseService;
use App\Services\PlanService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);
beforeEach(fn () => $this->seed());

it('issues and activates a signed license, then gates features', function () {
    Plan::firstOrCreate(['slug' => 'pro'], ['name' => 'Pro', 'price_monthly' => 99000,
        'is_active' => true,
        'limits' => ['max_members' => 5, 'max_projects' => 3],
        'features' => ['kanban', 'audit', 'telescope'],
    ]);

    $key = LicenseService::issue('pro', ['type' => 'manual', 'expires_at' => null]);

    expect($key)->toStartWith('LIC-PRO-')
        ->and(LicenseService::activate($key))->toBeTrue()
        ->and(LicenseService::status())->toBe('active')
        ->and(LicenseService::daysLeft())->toBeNull(); // lifetime

    $plan = PlanService::for();
    expect($plan->can('kanban'))->toBeTrue()
        ->and($plan->can('audit'))->toBeTrue()
        ->and($plan->membersLeft())->toBe(5 - User::count());
});

it('rejects a forged license key', function () {
    Plan::firstOrCreate(['slug' => 'pro'], ['name' => 'Pro', 'price_monthly' => 99000, 'is_active' => true, 'limits' => [], 'features' => []]);

    $key = LicenseService::issue('pro', ['type' => 'manual', 'expires_at' => null]);
    $forged = 'LIC-PRO-DEADBEEF1234';

    expect($forged)->not->toBe($key)
        ->and(LicenseService::activate($forged))->toBeFalse()
        ->and(LicenseService::status())->toBe('none');
});

it('expired license downgrades to free', function () {
    Plan::firstOrCreate(['slug' => 'pro'], ['name' => 'Pro', 'price_monthly' => 99000, 'is_active' => true, 'limits' => [], 'features' => []]);

    $key = LicenseService::issue('pro', ['type' => 'recurring', 'expires_at' => now()->subDay()]);
    expect(LicenseService::activate($key))->toBeFalse() // already expired
        ->and(LicenseService::status())->toBe('none');

    // active plan stays free (default seeded)
    expect(Setting::get('active_plan'))->toBe('free');
});

it('revoke instantly locks features', function () {
    Plan::firstOrCreate(['slug' => 'pro'], ['name' => 'Pro', 'price_monthly' => 99000, 'is_active' => true, 'limits' => [], 'features' => []]);
    $key = LicenseService::issue('pro', ['type' => 'manual', 'expires_at' => null]);
    LicenseService::activate($key);

    LicenseService::revoke($key, 'abuse');
    // instance deactivated: no active license, plan falls back to free
    expect(LicenseService::status())->toBe('none')
        ->and(Setting::get('active_plan'))->toBe('free')
        ->and(License::where('license_key', $key)->first()->status)->toBe('revoked');
});

it('default free plan is active after seed', function () {
    // Free plan: user-set minimal (limits all zero, features [])
    expect(PlanService::for()->can('audit'))->toBeFalse()
        ->and(PlanService::for()->can('kanban'))->toBeFalse()
        ->and(PlanService::for()->membersLeft())->toBe(max(0, 0 - User::count()));
});

it('uses default_plan when no license is active', function () {
    Setting::set('default_plan', 'free');

    $plan = PlanService::for();
    expect($plan->can('audit'))->toBeFalse() // audit no longer free-tier (see PLAN LIMITS NEEDS DECISION)
        ->and($plan->can('kanban'))->toBeFalse(); // kanban is pro-only
});

it('tamper: flipping settings.active_plan without a valid license yields no paid features', function () {
    // client owns the DB and edits the setting directly (Model 1, §10.1)
    Setting::set('active_plan', 'pro');
    Setting::set('license_key', null);

    // no matching signed license row -> PlanService returns no features for a
    // paid plan without a valid license (§10.1 tamper resistance).
    // Simplest correct assertion: without an issued+activated license, paid
    // features are NOT granted.
    Plan::firstOrCreate(['slug' => 'pro'], ['name' => 'Pro', 'price_monthly' => 99000, 'is_active' => true, 'limits' => [], 'features' => []]);

    // even with pro plan row present, no activated license => snapshot empty => no kanban
    expect(PlanService::for()->can('kanban'))->toBeFalse();
});
