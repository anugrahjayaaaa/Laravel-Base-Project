# Audit Trail

Uses `spatie/laravel-activitylog`. Every user action is logged automatically.

## Must be logged
- Auth: successful login, logout, **failed login**, lockout, reset request,
  reset success, password change, account verification.
- User: create, update, delete (soft), restore, force-delete (permanent).
- RBAC: role create/edit/delete, role restore, role force-delete, permission create/edit/delete, permission restore, permission force-delete, permission attach/detach, user-role assign.
- Business modules: main CRUD (added as modules are built).

## Format
```
id | causer_id | causer_name | action | subject_type | subject_id
| description | properties(json) | ip | user_agent | created_at
```
- `properties` holds before/after delta for important updates.
- IP & user_agent required; never log password/token.

## Access
- "Audit Log" page (read-only) for specific roles.
- Filters: user, action, date range. Keyset pagination.
- Log is permanent; purge via retention job (not user soft-deletable).

## Gate
- Every state mutation is logged. RED if any write path lacks a log.

---

## Implementation Reference (functions)

Observers live in `app/Observers/` and are registered in `AppServiceProvider`.
All write a row to **DB table `activity_log`** (spatie/laravel-activitylog).

### `UserObserver`, `RoleObserver`, `PermissionObserver`
Each has: `created`, `updated`, `deleted` (soft), `restored`, `forceDeleted`.

**`forceDeleted($model)`** (added for permanent-delete compliance)
- Purpose: log an unrecoverable (hard) delete to the audit trail.
- Input: the soft-deleted-then-force-deleted model.
- Output: `void`.
- State: writes one row to **DB table `activity_log`**:
  - `UserObserver` → action `user_force_deleted`
  - `RoleObserver` → action `role_force_deleted`
  - `PermissionObserver` → action `permission_force_deleted`
  - `properties` = `{ ip, user_agent }`; `causer` = currently auth'd user.
- Unlike `deleted()` (soft delete, recoverable), this is permanent.
