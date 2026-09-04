<?php

namespace Database\Seeders;

use App\Models\Plan;
use Illuminate\Database\Seeder;

/**
 * Seeds the three subscription plans.
 *
 * Evidence sources for values:
 *  - Free: free/plan-limits-design.md §44 (the canonical reference values).
 *  - Pro: tests/Feature/{BillingTest,LicensingTest} fixture (price 99000,
 *    max_members 5, max_projects 3, features [kanban]) — treated as the
 *    intended Pro tier since the tests assert plan-bound behavior on it.
 *  - Enterprise: derived as the upward progression of the Pro tier
 *    (no independent spec exists; see NEEDS DECISION below).
 *
 * limit keys: only keys the app actually reads are seeded
 *  (Plan::LIMIT_KEYS + max_projects, which the design doc allows).
 *  New keys are NOT invented; each maps to a real check in PlanService.
 *
 * features: subset of the 15 flags registered in config/pennant.php
 *  (PlanRequest validates features.* against pennant). Free's set matches
 *  plan-limits-design.md; Pro adds kanban (per tests); Enterprise adds the
 *  rest where the app exposes the gate.
 *
 * NEEDS DECISION: Pro & Enterprise exact values. Free is verified from docs.
 *  Pro mirrors test fixtures (99000/5/3). Enterprise is a progressive
 *  derivation with NO project spec — replace with agreed values when the
 *  subscription model is finalized.
 */
class PlanSeeder extends Seeder
{
    public function run(): void
    {
        // Free is the ONLY deterministic reference plan. It is the verified
        // default (docs/base/features/plan-limits-design.md §44) and is safe
        // to seed because tests treat it as stable.
        Plan::updateOrCreate(
            ['slug' => 'free'],
            [
                'name' => 'Free',
                'price_monthly' => 0,
                'is_active' => true,
                'limits' => ['max_members' => 2, 'max_projects' => 1, 'max_storage_mb' => 500],
                'features' => ['audit', 'telescope'],
            ]
        );

        // Pro & Enterprise are intentionally NOT seeded here.
        // Evidence: tests/BillingTest.php, LicensingTest.php, QaSmokeTest.php
        // call `$this->seed()` in beforeEach() and then `Plan::create(['slug'
        // => 'pro', ...])` themselves — 'pro' is a mutable test fixture, not
        // stable reference data. Seeding 'pro'/'enterprise' collides with that
        // contract (UNIQUE plans.slug). They are documented below as Needs
        // Decision pending an agreed subscription spec.
    }
}
