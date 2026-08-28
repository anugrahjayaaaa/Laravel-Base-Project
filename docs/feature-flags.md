# Feature Flags

A feature flag is a layer **above** RBAC. Access to a module requires BOTH:
`has permission` **AND** `feature enabled`. When a feature is off, access is
denied even to users (including super-admin) who hold the permission — by
product rule: *flag off => inaccessible*.

## Storage
- DB table `features`: `slug` (PK), `label`, `enabled` (bool), timestamps.
- Seeded in `DatabaseSeeder` (`FEATURES` map) — all on by default.
- No config/deploy needed to toggle; done from the UI.

## Checking a flag
```php
feature('users');   // true/false; missing feature => false (fail-closed)
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
- `/features` (gated by `feature.manage` permission; `staff` + `super-admin` get it).
- Lists all flags with an Enable/Disable toggle. Each toggle writes `enabled`.

## Gate
- Every module route is wrapped in `feature:` — RED if a module route lacks it.
- A flag off must block the route AND hide its sidebar entry.
