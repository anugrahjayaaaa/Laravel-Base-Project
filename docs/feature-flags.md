# Feature Flags

A feature flag is a layer **above** RBAC. Access to a module requires BOTH:
`has permission` **AND** `feature enabled`. When a feature is off, access is
denied to everyone except holders of the `feature.manage` permission — by
product rule: *flag off => inaccessible to normal users, but managers stay in*
so they can operate/audit modules while disabled.

Implemented with **Laravel Pennant** (`laravel/pennant`).

## Storage
- DB table `features` (Pennant's own migration) stores resolved flag values.
- Flag declarations + labels live in `config/pennant.php` (`features` map).
- Declared in `AppServiceProvider::boot()` via `Feature::define($slug, fn () => true)`.
- Default state is ON; toggling writes the resolved value to the DB store.
- No config/deploy needed to toggle; done from the UI.

## Checking a flag
```php
feature('users');   // true/false; missing feature => false (fail-closed)
Feature::active('users');
```

## Enforcement
- Route middleware `feature:{slug}` (`App\Http\Middleware\EnsureFeatureEnabled`),
  stacked with `can:{perm}`:
  ```php
  Route::resource('users', ...)->middleware(['can:user.view', 'feature:users']);
  ```
- Off flag => `404` (fail-closed), never silently allowed.
- Sidebar hides the menu item when its feature is off.

## Management UI
- `/features` lives **under the Settings submenu** (gated by `feature.manage` permission; `staff` + `super-admin` get it).
- Lists all flags with a **toggle switch** (auto-submits on change) for enable/disable.
- A `feature.manage` holder is exempt from the off-gate (see Bypass) so they can still
  use modules while a flag is off.

## Known flags
- `users`, `roles`, `permissions`, `audit`, `sessions`, `api-tokens`, `translations`, `logs`, `telescope`, `periscope`
  (declared in `config/pennant.php`).

## Gate
- Every module route is wrapped in `feature:` — RED if a module route lacks it.
- A flag off must block the route AND hide its sidebar entry.
