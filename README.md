# Laravel Base Project

A base AdminLTE + RBAC starter kit for Laravel 13. Ships with authentication, dynamic
role/permission (spatie), audit trail, soft-delete, and centralized logging — ready to
use as the foundation for internal web applications.

## Stack

| Layer | Tech |
|-------|------|
| PHP | 8.3+ |
| Framework | Laravel 13 (`laravel/framework` ^13.17) |
| Frontend | Bootstrap 5.3 + Bootstrap Icons + AdminLTE 4.9.1 (Blade), Vite |
| Auth | Laravel built-in + `spatie/laravel-permission` + `laravel/sanctum` (API) |
| Audit | `spatie/laravel-activitylog` |
| Monitoring | Laravel Log (`daily`) + Sentry (`sentry/sentry-laravel`) + `/up` health check |
| Tests | Pest |

## Features

- **Authentication** — login via **email or username**, account lockout after 5 failed attempts
  (15 min, IP-independent, auto-unlock), admin **permanent lock** (`user.lock` permission, clears only via unlock), forgot/reset password (token stored in DB), change-password
  (requires current password).
- **RBAC** — dynamic roles & permissions (spatie). `super-admin`, `admin`, `staff` are
  seeded. Every action is gated via `can:*` (route middleware + Form Request `authorize()`).
- **User management** — CRUD, soft-delete, restore, permanent delete (force-delete), admin lock/unlock.
- **Feature flags** — `features` table + `/features` UI (gated by `feature.manage`). A flag sits **above** RBAC: a module needs both `permission` AND `feature enabled`; when off, the route 404s and its sidebar item hides — even for super-admin.
- **Audit trail** — every mutation (create/update/delete/restore/force-delete, admin lock/unlock, login,
  logout, reset) is automatically recorded into `activity_log` via observers.
- **Thin controllers** — all input validation lives in **Form Requests**
  (`app/Http/Requests/<Domain>/`); controllers only call `validated()` and dispatch.
- **Error logging** — 4xx responses (except 404) are auto-logged to the daily log via the
  `LogHttpErrors` middleware.
- **API** — Sanctum `/api/v1` (login, me, logout, change-password) for mobile clients.

## Installation

```bash
# 1. Dependencies
composer install
npm install

# 2. Environment
cp .env.example .env
php artisan key:generate

# 3. Database (tests use sqlite :memory:; for dev use MySQL or a sqlite file)
# Edit .env: DB_CONNECTION=mysql (or leave it as sqlite)
php artisan migrate --seed

# 4. Frontend assets
npm run build        # or `npm run dev` for watch mode

# 5. Run
php artisan serve    # http://localhost:8000
```

Default login (from seeder): `admin@laravel-base.local` / `Admin@base12345` (super-admin).

### .env values to configure

| Variable | Description |
|----------|-------------|
| `DB_*` | Database connection (default sqlite) |
| `CACHE_STORE` | `database` (default) → `cache` table |
| `SESSION_DRIVER` | `database` (default) → `sessions` table |
| `MAIL_*` | Required so reset-password emails are actually delivered |
| `SENTRY_DSN` | Enable Sentry monitoring (optional; empty = disabled) |

## Common commands

```bash
php artisan serve              # start dev server
php artisan migrate --seed     # migrate + seed initial data (roles/permissions/users)
php artisan route:list         # list all routes
php artisan test               # run all tests (Pest)
npm run dev                    # Vite watch (frontend)
npm run build                  # build production assets
composer test                  # same as php artisan test
```

## Running tests

Tests use **Pest** with an isolated **sqlite `:memory:`** database (auto-seeded per test).

```bash
php artisan test                                  # all tests
php artisan test --filter="ProfileTest"           # a single test
php artisan test tests/Feature/AuthLoginTest.php  # a single file
```

Location: `tests/Feature/` (HTTP/controllers) and `tests/Unit/`.
Current coverage: **73 tests / 206 assertions** (login, RBAC, profile, audit, logging, API, feature flags).

## Logs, cache & state — where to look

| What | Where |
|------|-------|
| **Errors / HTTP log** (file) | `storage/logs/laravel-YYYY-MM-DD.log` (daily rotation, `LOG_STACK=daily`) |
| **Errors (dashboard)** | Sentry (if `SENTRY_DSN` is set) |
| **Health check** | `/up` → `{"status":"ok"}` |
| **User action audit** | **Audit Log** menu (`/audit`) or the `activity_log` DB table |
| **Login rate-limit** | cache key `login:{ip}:{identifier}` → `cache` table |
| **Sessions** | `sessions` table (`SESSION_DRIVER=database`) |
| **Reset-password token** | `password_reset_tokens` table |
| **Web log viewer** | not yet available (use file log / Sentry) |

### Watch errors locally

```bash
tail -f storage/logs/laravel-$(date +%Y-%m-%d).log
```

With `APP_DEBUG=true`, 500 errors are also shown directly in the browser.

## Key structure

```
app/
  Http/
    Controllers/        # thin controllers
    Requests/           # Form Requests per domain (Auth, User, Rbac, Profile, ApiToken)
    Middleware/
      LogHttpErrors.php # logs 4xx to the daily log
  Models/              # User, Role, Permission (SoftDeletes)
  Observers/            # log force-delete into activity_log
docs/                  # CONTRIBUTING, auth, architecture, audit-trail, authorization, feature-flags, observability, etc.
```

## Contributing

See `docs/CONTRIBUTING.md` — core rules: **validation in Form Requests**, thin controllers,
and every PR must keep tests green and docs in sync.

## License

MIT.
