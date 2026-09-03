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

### 4. Free plan remains
```php
'limits' => ['max_members' => 2, 'max_projects' => 1, 'max_storage_mb' => 500],
```
> Note: `max_projects` in seed data tetap ada walau belum di LIMIT_KEYS (bisa jadi useful later)
