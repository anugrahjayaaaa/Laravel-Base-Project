# Audit Trail

Uses `spatie/laravel-activitylog`. Every user action is logged automatically.

## Must be logged
- Auth: successful login, logout, **failed login**, lockout, reset request,
  reset success, password change, account verification.
- User: create, update, delete (soft), restore.
- RBAC: role create/edit/delete, permission attach/detach, user-role assign.
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
