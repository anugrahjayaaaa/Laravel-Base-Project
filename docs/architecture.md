# Architecture

## Stack (locked)
| Layer | Tech |
|-------|------|
| Language | PHP 8.3+ |
| Framework | Laravel 13 (latest) |
| DB | MySQL 8 (InnoDB, utf8mb4_0900_ai_ci) |
| Frontend | Bootstrap 5.3 + AdminLTE 4.9.1 (Blade), Vite |
| Auth | Laravel built-in + spatie/laravel-permission |
| Audit | spatie/laravel-activitylog |
| API auth | Laravel Sanctum |
| Observability | Laravel Log + Sentry + /up |
| Tests | Pest |

## Layered architecture
```
HTTP (web Blade / API JSON)
  → Controller (thin: validate + dispatch)
    → Service / Action (business logic)
      → Eloquent Models (SoftDeletes)
        → MySQL
- Custom models: `App\Models\User`, `App\Models\Role`, `App\Models\Permission` all use `SoftDeletes`;
  `config/permission.php` maps spatie to the custom Role/Permission.
Cross-cutting: auth, rbac(permission), force-json, log-activity middleware;
Exceptions → Sentry + structured log; Policies per resource.
```

## v1 modules
1. Auth (login username|phone, logout, register, reset pwd, failed-login lockout, email verify, profile self-service, session mgmt)
2. RBAC (roles, permissions, assignment UI — dynamic)
3. Audit Trail (read-only, filter by user/action/date)
4. User Management (CRUD, soft delete)
5. Dashboard home
6. API (Sanctum /api/v1)
7. Template reference (sidebar section from zip demo)

## Required v1 features
- Forgot/reset password (hashed token, 60m expiry, single-use)
- Failed-login lockout (5 fails → 15m lock) + auth endpoint rate limit
- Email verification (`email_verified_at`)
- User self-service: profile, avatar, change password, change phone
- Session management: list sessions, logout other devices
- Seed super-admin + first-run setup
- Dashboard home
- Health `/up` + security headers (CSP, HSTS, X-Frame-Options)
