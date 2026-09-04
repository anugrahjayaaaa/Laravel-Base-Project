# Changelog: feature/general-fixes

All changes for use in Issue Tracker (fork Laravel-Base-Project).

## License Mode Support

### `config/pennant.php`
- Added `features` flag to feature list (Features Management module)
- Without this, `@feature('features')` always fails → sidebar menu hidden even for super-admin

### `app/Enums/LicenseMode.php` (NEW)
- Enum: `global` | `per_user`
- Used in SettingsController to render dropdown

### `database/migrations/2025_01_01_000000_create_licenses_table.php` (DELETED)
- **Conflict**: Project already has `licenses` table migrations
- Do NOT create duplicate — use existing table which has: `id`, `plan_slug`, `license_key`, `type` (recurring|lifetime|manual), `status` (active|revoked|expired), `issued_to` (instance binding), `expires_at`, `snapshot` (JSON, versioned catalog), `user_id` (nullable, added later)

### `app/Models/License.php`
- Model already has `user_id` in fillable + `user()` relation
- Scopes: `active()` (status=active AND not expired), `isActiveAndValid()`
- Do NOT overwrite — revert if changed

### `app/Services/PlanService.php`
- Added `?User $user = null` param to `for()` method
- Per-user mode: resolves plan from `$user->license?->plan_slug`
- Global mode: uses `settings.active_plan` or `settings.default_plan` (unchanged)
- Added `syncPermissionsForPlan(Plan $plan)` — sync permission ke user berdasarkan plan features

### `app/Http/Controllers/SettingsController.php`
- Added `license_mode` field to validation + setting persistence
- Pass `$licenseMode` to view

### `resources/views/settings/system.blade.php`
- Added dropdown for license mode (global/per_user)
- Must be inside `<form>` that posts to `settings.system.update` route
- Validation: `'license_mode' => ['required', 'string', 'in:global,per_user']`

### `database/seeders/DatabaseSeeder.php`
- Added `Setting::updateOrCreate(['key' => 'license_mode'], ['value' => 'global'])`
- Staff role: `syncPermissions([])` — no direct permission assignment
- Permissions come from plan sync, NOT role assignment
- Free plan seed: `features: ['audit', 'telescope']`, `limits: ['max_members' => 2, 'max_projects' => 1, 'max_storage_mb' => 500]`

## Plan Form & Validation

### `app/Http/Requests/Plan/PlanRequest.php`
- `is_active` rule: keep `'boolean'` (NOT nullable) — but form uses hidden input + checkbox value trick
- `features` rule: `'nullable|array'` — allows empty features array
- `max_permissions` rule: `'nullable|integer|min:0'`

### `resources/views/plans/form.blade.php`
- `is_active` checkbox: add hidden input `value=0` + checkbox `value=1`
  ```html
  <input type="hidden" name="is_active" value="0">
  <input type="checkbox" name="is_active" value="1" ...>
  ```
- Capacity section: wrap `max_members`, `max_storage_mb`, `max_roles` in `@if(license_mode === 'global')` — per-user mode hides these, keeps `max_permissions` + `max_features`
- Features section: counter badge only shown in global mode
- Permission checkboxes: add class `perm-toggle` for reliable JS selector
- JS IIFE: handle `max_features=0` → disable all feature checkboxes; `max_permissions=0` → disable permission checkboxes (global mode only)

### `app/Models/Plan.php`
- LIMIT_KEYS: 5 standard keys — NO `max_projects` (base project doesn't use it yet)
  ```php
  'max_members', 'max_roles', 'max_permissions', 'max_features', 'max_storage_mb'
  ```

### `app/Http/Controllers/PlanController.php`
- Call `PlanService::syncPermissionsForPlan($plan)` after `store()` + `update()`

## Permission Sync

### `PlanService::syncPermissionsForPlan(Plan $plan)` (NEW method)
- Derives permission names from plan `features` + `limits.allowed_permissions`
- Global mode: syncs to ALL users via `$user->syncPermissions([...])`
- Per-user mode: skipped — sync happens via LicenseService when license assigned
- Uses `Permission::featureOf($perm->name)` to map permission → feature slug

## Sidebar & Navigation

### `resources/views/partials/layout/sidebar.blade.php`
- Features menu: wrapped in `@can('feature.manage')` + `@feature('features')`
- All nav items already gated by `@can` + `@feature` combination

## Auth Form Validation & Accessibility Fixes

### Error display pattern (semua auth form konsisten)
Pattern: `@if($errors->any())` alert div (general error) + `@error('field')` invalid-feedback (field-level)

### `resources/views/auth/login.blade.php`
- Hapus `@error('identifier')` block — LoginController already keys error to `identifier` (caused duplicate display)
- Password error: ganti `invalid-feedback d-block` → `invalid-feedback` tanpa `d-block` (di luar input-group)
- Tambah `id="identifier"` + `aria-describedby` + `aria-invalid` di semua input
- Tambah `sr-only` label untuk accessibility (WCAG 2.1)
- Error block pindah ke luar input-group (setelah icon) — mencegah layout shift

### `resources/views/auth/register.blade.php`
- Semua `@error` block pindah ke luar input-group (setelah icon) — mencegah icon pindah kebawah
- Password toggle icon: kembalikan `<i class="bi bi-eye" id="password-confirm-icon">` — sebelumnya hilang
- Konsistenkan `invalid-feedback d-block w-100` untuk server-side errors
- Tambah `id`, `aria-describedby`, `aria-invalid` di semua input
- Tambah `sr-only` label untuk semua field (name, username, email, phone, password, password_confirmation)

### `resources/views/auth/forgot-password.blade.php`
- Tambah `id="email"` + `aria-describedby` + `aria-invalid` di input
- Error: pindah ke luar input-group, pakai `invalid-feedback d-block w-100` + `role="alert"` + `aria-live="polite"`
- Tambah `sr-only` label

### `resources/views/auth/reset-password.blade.php`
- Tambah `id` di semua input + `aria-describedby` + `aria-invalid`
- Error: pindah ke luar input-group, konsisten dengan pattern
- Tambah `sr-only` label untuk email, password, password_confirmation

## Testing
- All 140 tests pass
- Key test files: `LicensingTest.php`, `PlanTest.php` (7 tests), `RbacTest.php`, `QaSmokeTest.php`
