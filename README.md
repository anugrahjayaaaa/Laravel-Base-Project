# Laravel Base Project

Base AdminLTE + RBAC starter kit untuk Laravel 13. Berisi autentikasi, role/permission
(spatie), audit trail, soft-delete, dan logging terpusat — siap jadi fondasi aplikasi web internal.

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

## Fitur

- **Autentikasi** — login via **email atau username**, lockout 5× gagal (15 menit),
  lupa/reset password (token di DB), ganti password (wajib password lama).
- **RBAC** — role & permission dinamis (spatie). Super-admin, admin, staff sudah diseed.
  Setiap aksi di-gate via `can:*` (route middleware + Form Request `authorize()`).
- **Manajemen User** — CRUD, soft-delete, restore, permanent-delete (force-delete).
- **Audit Trail** — semua mutasi (create/update/delete/restore/force-delete, login, logout,
  reset) dicatat ke `activity_log` otomatis via observer.
- **Thin controllers** — semua validasi input ada di **Form Request**
  (`app/Http/Requests/<Domain>/`), controller hanya `validated()` + dispatch.
- **Logging error** — 4xx (kecuali 404) otomatis ke log harian via middleware
  `LogHttpErrors`.
- **API** — Sanctum `/api/v1` (login, me, logout, change-password) untuk mobile.

## Instalasi

```bash
# 1. Dependency
composer install
npm install

# 2. Environment
cp .env.example .env
php artisan key:generate

# 3. Database (default sqlite :memory: untuk test; untuk dev pakai MySQL/sqlite file)
# Edit .env: DB_CONNECTION=mysql (atau biarkan sqlite)
php artisan migrate --seed

# 4. Frontend assets
npm run build        # atau npm run dev untuk watch

# 5. Jalankan
php artisan serve    # http://localhost:8000
```

Login default (dari seeder): `admin@laravel-base.local` / `Admin@base12345` (super-admin).

### .env yang perlu diisi

| Variabel | Keterangan |
|----------|-----------|
| `DB_*` | Koneksi database (default sqlite) |
| `CACHE_STORE` | `database` (default) → table `cache` |
| `SESSION_DRIVER` | `database` (default) → table `sessions` |
| `MAIL_*` | Diperlukan agar email reset password benar-benar terkirim |
| `SENTRY_DSN` | Aktifkan monitoring Sentry (opsional, kosong = nonaktif) |

## Perintah Umum

```bash
php artisan serve              # jalankan dev server
php artisan migrate --seed     # migrasi + isi data awal (role/permission/user)
php artisan route:list         # lihat semua route
php artisan test               # jalankan semua test (Pest)
npm run dev                    # Vite watch (frontend)
npm run build                  # build asset produksi
composer test                  # sama dengan php artisan test
```

## Menjalankan Tests

Test pakai **Pest**, database **sqlite `:memory:`** (diisolasi per test, otomatis seed).

```bash
php artisan test                          # semua test
php artisan test --filter="ProfileTest"   # hanya test tertentu
php artisan test tests/Feature/AuthLoginTest.php  # file tertentu
```

Lokasi: `tests/Feature/` (HTTP/controller) dan `tests/Unit/`.
Coverage saat ini: **55 test / 139 assertions** (login, RBAC, profile, audit, logging, API).

## Log, Cache & State — где lihat?

| Yang dicari | Lokasi |
|------------|--------|
| **Error / HTTP log** (file) | `storage/logs/laravel-YYYY-MM-DD.log` (rotasi harian, `LOG_STACK=daily`) |
| **Error (dashboard)** | Sentry (jika `SENTRY_DSN` diset) |
| **Health check** | `/up` → `{"status":"ok"}` |
| **User action audit** | menu **Audit Log** (`/audit`) atau DB table `activity_log` |
| **Rate-limit login** | cache key `login:{ip}:{identifier}` → table `cache` |
| **Session** | table `sessions` (`SESSION_DRIVER=database`) |
| **Reset-password token** | table `password_reset_tokens` |
| **Log viewer web** | belum ada (pakai file log / Sentry) |

### Monitoring error di local

```bash
tail -f storage/logs/laravel-$(date +%Y-%m-%d).log
```

Atau kalau `APP_DEBUG=true`, error 500 langsung tampil di browser.

## Struktur Penting

```
app/
  Http/
    Controllers/        # thin controllers
    Requests/           # Form Request per domain (Auth, User, Rbac, Profile, ApiToken)
    Middleware/
      LogHttpErrors.php # log 4xx ke daily log
  Models/              # User, Role, Permission (SoftDeletes)
  Observers/            # log force-delete ke activity_log
docs/                  # CONTRIBUTING, auth, architecture, audit-trail, observability, dll
```

## Kontribusi

Lihat `docs/CONTRIBUTING.md` — aturan inti: **validation di Form Request**, controller tipis,
setiap PR wajib tests hijau + docs sync.

## License

MIT.
