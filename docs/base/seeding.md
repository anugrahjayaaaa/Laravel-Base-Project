# Seeding

## Overview

The project ships with a domain-oriented seeder set. Each seeder owns ONE
reference-data domain; `DatabaseSeeder` only orchestrates execution order.
All seeders use `updateOrCreate`/`findOrCreate`/`syncPermissions` so they are
safe to rerun (idempotent).

## Structure

```text
database/seeders/
├── DatabaseSeeder.php    orchestration only — no logic
├── PermissionSeeder.php  spatie permissions (33, web guard)
├── RoleSeeder.php        roles + permission assignments
├── SettingSeeder.php     system settings defaults
├── PlanSeeder.php        Free (only deterministic reference plan)
├── AdminUserSeeder.php   first-run super-admin
└── LanguageLineSeeder.php spatie lang lines
```

## Execution order

```
PermissionSeeder → RoleSeeder → PlanSeeder → SettingSeeder → AdminUserSeeder → LanguageLineSeeder
```

Dependencies:
1. **PermissionSeeder** — permissions (referenced by RoleSeeder).
2. **RoleSeeder** — roles; `super-admin` + `admin` get all perms, `staff`
   gets none (staff entitlement resolves via plan features — see auth.md
   §Authorization). `syncPermissions` is idempotent.
3. **PlanSeeder** — plans referenced by SettingSeeder's `active_plan`/`default_plan`.
4. **SettingSeeder** — global settings (`active_plan`, `default_role`,
   `license_mode`). `license_secret` is deliberately NOT seeded (env-only).
5. **AdminUserSeeder** — seeds admin user + assigns `super-admin` role
   (depends on RoleSeeder).
6. **LanguageLineSeeder** — spatie translatable strings (last; no deps).

## What each owns

| Seeder            | Owns                              | Values                         | Source |
| ----------------- | --------------------------------- | ------------------------------ | ------ |
| PermissionSeeder  | spatie `permissions`              | 33 permissions (web guard)     | `docs/base/modules/backend.md` §RBAC |
| RoleSeeder        | spatie `roles` + assignment       | super-admin, admin, staff      | `docs/authorization.md` §Roles |
| SettingSeeder     | `settings` key/value              | active_plan=free, default_plan=free, default_role=staff, license_mode=global | `docs/base/features/licensing-and-billing.md` §46-49 |
| PlanSeeder        | `plans`                           | Free only (see below)          | `docs/base/features/plan-limits-design.md` §44 |
| AdminUserSeeder   | `users` first-run admin           | admin@laravel-base.local       | dev/first-run only |
| LanguageLineSeeder| spatie `language_lines`           | from `lang/{en,id}/`           | `docs/i18n.md` |

## Plans

Only **Free** is seeded as deterministic reference data.
Free values (verified):

| slug   | price | members | projects | storage_mb | features      |
| ------ | ----- | ------- | -------- | ---------- | ------------- |
| free   | 0     | 2       | 1        | 500        | [audit, telescope] |

Pro and Enterprise are **NOT seeded**. They are created on-demand per
subscription context. Intended Pro baseline (from test fixtures:
`tests/Feature/BillingTest.php`, `LicensingTest.php`):

| slug   | price  | members | projects | storage_mb | features |
| ------ | ------ | ------- | -------- | ---------- | -------- |
| pro    | 99000  | 5       | 3        | 2000       | [kanban,…] |
| enterprise | 499000 | 0 (unlimited) | 0 | 0 | all pennant flags |

### needs-decision / plans

`Plan::firstOrCreate('pro')` + `enterprise` values have **no project spec**.
Test fixtures give a Pro baseline (99000 / 5 / 3 / kanban). Enterprise is a
progressive derivation with no independent evidence. These are intentionally
kept out of `PlanSeeder` to avoid inventing subscription business rules
(see `docs/base/features/plan-limits-design.md` §51 "needs decision").

## Admin user (security)

- **Development / first-run seed data only.**
- email `admin@laravel-base.local` is a reserved placeholder (not a real inbox).
- Password is seeded as `Hash::make(...)` (bcrypt) — never plaintext in repo.
- Phone is masked (`+628****0001`) — no real PII.
- In production this credential is replaced by env-driven provisioning.

## Idempotency

- `updateOrCreate` keyed on `slug`/`email`/`key` — reruns update, never duplicate.
- `Permission::findOrCreate` (spatie) — reruns are no-ops.
- `Role::syncPermissions` — replaces the permission set per run.
