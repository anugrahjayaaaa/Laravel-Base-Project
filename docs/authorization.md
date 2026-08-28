# Authorization (Dynamic RBAC)

Uses `spatie/laravel-permission`. Roles & permissions are created/edited via UI (dynamic).

## Model
- `permissions`: granular actions (`user.view`, `user.create`, `role.edit`).
- `roles`: a set of permissions; a user may have multiple roles.
- Super-admin: bypass (special guard).
- Role & Permission are **custom models** (`App\Models\Role`, `App\Models\Permission`)
  extending spatie with `SoftDeletes`; `config/permission.php` points spatie to them.
  Always import the custom models, never `Spatie\Permission\Models\*` directly.

## Actions & permissions
- Each resource has: `*.view`, `*.create`, `*.edit`, `*.delete`,
  `*.restore` (soft-deleted → active), `*.force-delete` (permanent, trashed only).
- Restore/force-delete buttons appear only when the row is trashed AND the caller
  holds `*.restore` / `*.force-delete`.

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
