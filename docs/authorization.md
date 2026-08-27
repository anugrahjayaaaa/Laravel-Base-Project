# Authorization (Dynamic RBAC)

Uses `spatie/laravel-permission`. Roles & permissions are created/edited via UI (dynamic).

## Model
- `permissions`: granular actions (`user.view`, `user.create`, `role.edit`).
- `roles`: a set of permissions; a user may have multiple roles.
- Super-admin: bypass (special guard).

## Management pages (required)
1. Roles: list, create, edit, delete, attach/detach permissions.
2. Permissions: list, create, edit, delete (or seed only).
3. User ↔ Role: assign from user management.

## Enforcement
- `permission:` middleware on sensitive routes.
- `Policy` per resource for ownership checks.
- Every role/permission change → audit trail.

## Initial seed
- Roles `super-admin`, `admin`, `staff` + default permissions.
- First seeded user = super-admin.

## Gate
- Every route/action is protected by a permission. RED if any action lacks authz.
