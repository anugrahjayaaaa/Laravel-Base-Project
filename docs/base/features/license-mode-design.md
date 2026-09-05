---
id: BASE-011
name: License Mode Design
status: design
---

# License Mode Design System

## Overview

`license_mode` allows the instance to operate in two modes:

1. **Global** (`license_mode = global`): One license for the entire instance
2. **Per-User** (`license_mode = per_user`): Each user has their own license

> **Status:** Global mode is the production-supported path. Per-user mode is
> **INCOMPLETE** — `PlanService::for()` references `$user->license` (singular
> accessor) but the `User` model does not provide this accessor, and there is
> no complete assignment flow/UI. Per-user mode is NOT production-ready.

## Schema

```sql
-- Global setting stored in `settings` table
INSERT INTO settings (key, value) VALUES ('license_mode', 'global');

-- Per-user licenses table
CREATE TABLE licenses (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id BIGINT UNSIGNED NOT NULL UNIQUE,
  plan_slug VARCHAR(50) NOT NULL DEFAULT 'free',
  license_key VARCHAR(255) UNIQUE,
  expires_at DATETIME NULL,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP null,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
```

## Models

### App\Models\User

```php
public function license(): HasOne
{
    return $this->hasOne(License::class);
}

public function plan(): string
{
    $mode = Setting::get('license_mode', 'global');
    if ($mode === LicenseMode::PER_USER->value && $this->license) {
        return $this->license->plan_slug;
    }
    return Setting::get('active_plan', 'free');
}
```

### App\Models\License

Fields: `id`, `user_id`, `plan_slug`, `license_key`, `expires_at`

## Enums

### App\Enums\LicenseMode

```php
enum LicenseMode: string {
    case GLOBAL = 'global';
    case PER_USER = 'per_user';
}
```

## Services

### App\Services\PlanService

```php
public function activePlan(User $user = null): string
{
    $mode = Setting::get('license_mode', 'global');
    
    if ($mode === LicenseMode::PER_USER->value && $user) {
        return $user->license?->plan_slug ?? Setting::get('default_plan', 'free');
    }
    
    return Setting::get('active_plan', 'free');
}
```

## Admin UI

Dropdown in `/settings/system`:
```
[Global (Instance)]     → license_mode = global
[Per-User]             → license_mode = per_user
```

Form Request validation:
```php
'license_mode' => ['required', Rule::enum(LicenseMode::class)]
```

## Migrations

1. `add_license_mode_to_settings_seed` (Seeder only)
2. `create_licenses_table`

## Tests

- Global mode returns `settings.active_plan`
- Per-user mode returns user's license plan
- Fallback to `default_plan` if no license
