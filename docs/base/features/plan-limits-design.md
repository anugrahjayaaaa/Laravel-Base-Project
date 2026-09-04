---
id: BASE-013
name: Plan Limits Design
status: design
---

# License Mode: Global vs Per-User

## Problem
- `license_mode` setting ada (global/per_user), tapi tidak memengaruhi UI plan form
- Free plan limits tidak konsisten (missing `max_projects`)
- Per-user mode perlu display logic berbeda: hanya feature access, tidak capacity limits

## Solution

### 1. Conditional display logic (blade)
Di `plans/form.blade.php`, capacity limits section tetap tampil untuk semua mode (bisa diperti. later untuk per-user-only access control).

Untuk license_mode per-user, tambahkan conditional:
```blade
@if (Setting::get('license_mode', 'global') === 'per_user')
    <div class="alert alert-info">Per-user mode: limits apply per-user via individual licenses.</div>
@endif
```

### 2. Keep existing LIMIT_KEYS (no max_projects — base project belum support)
```php
public const LIMIT_KEYS = [
    'max_members' => 'limit_max_members',
    'max_roles' => 'limit_max_roles',
    'max_permissions' => 'limit_max_permissions',
    'max_features' => 'limit_max_features',
    'max_storage_mb' => 'limit_max_storage_mb',
];
```

### 3. PlanService handles per-user license
Sudah ada di `PlanService::for()` — resolve user license plan_slug saat mode per_user.

### 4. Seeded plans (reference data)

`database/seeders/PlanSeeder.php` seeds all three tiers. Free is the verified
default; Pro/Enterprise derived from test fixtures + progression (see
`docs/base/seeding.md`).

```php
// free — minimal: all limits 0, no features
'limits' => ['max_features'=>0,'max_members'=>0,'max_projects'=>0,'max_storage_mb'=>0,'max_permissions'=>0,'max_roles'=>0,'can_create_roles'=>false,'allowed_permissions'=>[]],
'features' => [],

// pro — 99000, 5 members, kanban+audit+telescope (test fixture baseline)
'limits' => ['max_members'=>5,'max_projects'=>3,'max_storage_mb'=>2000,'max_permissions'=>10,'max_roles'=>3,'can_create_roles'=>true,'allowed_permissions'=>[],'max_features'=>3],
'features' => ['kanban','audit','telescope'],

// enterprise — 499000, effectively unlimited (0), all pennant features
'limits' => ['max_members'=>0,'max_projects'=>0,'max_storage_mb'=>0,'max_features'=>0,'max_permissions'=>0,'max_roles'=>0,'can_create_roles'=>true,'allowed_permissions'=>[]],
'features' => ['users','roles','permissions','kanban','audit','logs','telescope','periscope','sessions','api-tokens','translations','features','plans','billing'],
```

