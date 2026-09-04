<?php

namespace Database\Seeders;

use App\Models\Plan;
use Illuminate\Database\Seeder;

/**
 * Seeds the three subscription plans: free, pro, enterprise.
 *
 * Evidence sources:
 *  - Free: free/plan-limits-design.md §44 (verified reference values) + user
 *    override (limits all minimal/zero, features []).
 *  - Pro: tests/Feature/{BillingTest,LicensingTest} fixture (price 99000,
 *    max_members 5, max_projects 3, features [kanban]).
 *  - Enterprise: progressive derivation of Pro (no independent spec).
 *
 * limit keys: only keys the app actually consumes:
 *  - Plan::LIMIT_KEYS (max_members, max_roles, max_permissions, max_features,
 *    max_storage_mb) — all read via PlanService::limit().
 *  - max_projects — read via PlanService::canCreateProject (not in LIMIT_KEYS
 *    enum, but design doc allows it).
 *  - can_create_roles (bool) + allowed_permissions (array) — both read by
 *    PlanService::canCreateRoles()/allowedPermissions().
 *  New keys are NOT invented; each maps to a real PlanService check.
 *
 * features: subset of the 15 pennant flags in config/pennant.php (PlanRequest
 *  validates features.* against pennant features).
 *
 * idempotency: updateOrCreate keyed on slug → rerun updates, never duplicates.
 */
class PlanSeeder extends Seeder
{
    public function run(): void
    {
        $free = [
            'max_features' => 0,
            'max_members' => 0,
            'max_projects' => 0,
            'max_storage_mb' => 0,
            'max_permissions' => 0,
            'max_roles' => 0,
            'can_create_roles' => false,
            'allowed_permissions' => [],
        ];
        $pro = [
            'max_features' => 3,
            'max_members' => 5,
            'max_projects' => 3,
            'max_storage_mb' => 2000,
            'max_permissions' => 10,
            'max_roles' => 3,
            'can_create_roles' => true,
            'allowed_permissions' => [],
        ];
        $enterprise = [
            'max_features' => 0,
            'max_members' => 0,
            'max_projects' => 0,
            'max_storage_mb' => 0,
            'max_permissions' => 0,
            'max_roles' => 0,
            'can_create_roles' => true,
            'allowed_permissions' => [],
        ];

        Plan::updateOrCreate(
            ['slug' => 'free'],
            [
                'name' => 'Free',
                'price_monthly' => 0,
                'is_active' => true,
                'limits' => $free,
                'features' => [],
            ]
        );

        Plan::updateOrCreate(
            ['slug' => 'pro'],
            [
                'name' => 'Pro',
                'price_monthly' => 99000,
                'is_active' => true,
                'limits' => $pro,
                'features' => ['kanban', 'audit', 'telescope'],
            ]
        );

        Plan::updateOrCreate(
            ['slug' => 'enterprise'],
            [
                'name' => 'Enterprise',
                'price_monthly' => 499000,
                'is_active' => true,
                'limits' => $enterprise,
                'features' => ['users', 'roles', 'permissions', 'kanban',
                    'audit', 'logs', 'telescope', 'periscope',
                    'sessions', 'api-tokens', 'translations',
                    'features', 'plans', 'billing'],
            ]
        );
    }
}
