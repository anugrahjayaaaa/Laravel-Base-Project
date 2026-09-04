# Permission Sync Design (Global License Mode)

## Problem
- Staff users still see Access Management / Monitoring menus despite plan having limited features
- Permissions come from role assignment, not plan — disconnected

## Solution
When a plan is created/updated, sync permissions to ALL users based on the plan's
- `features` array → derive permission names via `Permission::featureOf()`
- `limits.allowed_permissions` → explicit permission overrides

### Flow
```
PlanController::update()
  → PlanService::syncPermissionsForPlan($plan)
    → foreach User: $user->syncPermissions($derivedPermissions)
```

### Rules
- Global mode: sync to ALL users (one instance plan)
- Per-user mode: per-user sync happens via LicenseService when license assigned
- Permission names derived from `Permission::featureOf($perm->name)` — matches feature slug
- Free plan (`features: ['audit','telescope']`) → users get `audit.view`, `telescope.view` only

### Example
Plan free:
```json
{
  "features": ["audit", "telescope"],
  "limits": {"max_members": 2, "max_storage_mb": 500, "allowed_permissions": []}
}
```
→ Sync permissions: `audit.view`, `telescope.view`
→ Sidebar menu: only Audit Log visible, not Users/Roles

## Files
- `app/Services/PlanService.php` — `syncPermissionsForPlan()`
- `app/Http/Controllers/PlanController.php` — call after save
- `resources/views/plans/form.blade.php` — JS guard `max_features=0` + `max_permissions=0`
