# Contributing & Conventions

## Dev setup
```bash
composer install
cp .env.example .env && php artisan key:generate
php artisan migrate --seed
npm install && npm run dev   # or build
```
- PHP 8.3+, Node 20+, MySQL 8.
- AdminLTE lives in `public/vendor/adminlte/` (from dist zip 4.9.1).

## Code conventions
- PHP: PSR-12, `declare(strict_types=1)`, type hints required.
- Naming: `snake_case` DB, `camelCase` JS, `PascalCase` class.
- Thin controllers: validate → dispatch to Service/Action class.
- Core models use `SoftDeletes`; mutations logged via observer/activitylog.
- Don't reinvent: use spatie/permission, activitylog, sanctum.

## Definition of Done (per PR)
- [ ] Scope + acceptance met
- [ ] Tests green & cover the change (Pest)
- [ ] Security checklist green (boundary validation, authz, SQL param, no secret)
- [ ] Code review passed (not just "LGTM")
- [ ] Docs match code (update OpenAPI/ADR if needed)
- [ ] CI green + staging smoke OK

## Git
- Branches: `feature/*`, `fix/*`, `chore/*`. PR into `main`.
- Commits imperative: "Add login lockout", not "fixing".
- Separate refactor from feature.

## Open items (not v1)
- MFA/2FA, SMS OTP, in-app notifications, CSV export, file-upload module, i18n switch.
