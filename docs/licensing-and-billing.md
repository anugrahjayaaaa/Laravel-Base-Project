# Licensing, Plans & Billing — Design & Flow

Status: DESIGN (not yet implemented). Current model = **Model 1 (per-client license)**,
with a seam for migration to **Model 2 (hub SaaS multi-tenant)** later.

## 1. Three concerns, kept separate (do not merge)

| Concern | Responsibility | Read by |
|---|---|---|
| **Entitlement** | Limits (max members/projects) + which features are active | App on every access check |
| **License** | Key that unlocks a paid plan (code) | Instance activation |
| **Billing** | Collecting money (PG webhook, invoice, recurring) | At payment time |

Separate these from the start. Entitlement does NOT know where the plan came
from (license key or payment). This is what makes the Model 1 → Model 2
migration cheap.

## 2. Distribution models

### Model 1 — License per client (NOW)
- 1 company = 1 instance. Source stays with the developer (not handed to client).
- Client gets a `license_key` → unlocks plan X. Active plan stored in `settings`.
- Entitlement scope = **instance** (singleton). `Plan::for(null)`.

### Model 2 — Hub SaaS (LATER)
- 1 site, many companies log in, each company = tenant, data scoped by `workspace_id`.
- Entitlement scope = **tenant**. `Plan::for($tenant)`.
- The expensive part of Model 2 is NOT the plan system, but row-level
  `workspace_id` scoping across all tables + a Tenant resolver. That is what
  must be deferred, not the plan system.

**The seam (core):** every check goes through `Plan::for($scope)`. Model 1
`$scope=null`, Model 2 `Plan::for($tenant)`. The plan-check logic is identical,
only the scope source changes.

## 3. Data model (Model 1)

```sql
plans
  id, slug (free|basic|pro|enterprise), name,
  price_monthly decimal(12,2), is_active bool,
  limits  json,   -- {"max_members":2,"max_projects":1,"max_storage_mb":500}
  features json    -- ["kanban","audit","telescope","api","sso"]

licenses
  id, plan_slug, license_key (unique), type (recurring|lifetime|manual),
  status (active|revoked|expired), issued_to,
  expires_at nullable,            -- NULL = never expires (lifetime/manual)
  created_at

settings
  key='active_plan' -> slug          -- plan in effect for this instance
  key='license_key' -> string        -- activated license

-- when billing (Model 1 extension / Model 2):
subscriptions
  id, plan_slug, status (active|canceled|past_due),
  gateway (midtrans|xendit), gateway_subscription_id,
  current_period_start, current_period_end, scope_id nullable  -- tenant_id in Model 2
invoices
  id, subscription_id, amount, status (paid|pending|failed), gateway_ref
webhook_events
  id, gateway, event_id unique, payload json, processed_at  -- idempotency
```

## 3b. License shape & issuance

**Shape:** a signed string code, e.g. `LIC-BASIC-8F3K2QX9P1`.
`license_key = "LIC-" . planSlug . "-" . HASH(planSlug . secret . expires_at)`.

**Who generates it:** ONE method, `LicenseService::issue(planSlug, $attrs)`,
called from two places (no second system):
- **A. Auto (recurring):** PG webhook `payment.success` → `issue(planSlug,
  ['type' => 'recurring', 'expires_at' => $periodEnd])`. Auto-extended on each
  renewal webhook.
- **B. Manual (off-market / lifetime):** admin enters it via console or UI →
  `issue(planSlug, ['type' => 'manual'|'lifetime', 'expires_at' => null])`.
  Use for: a company pays you directly (bank transfer), or you grant a
  lifetime license. `expires_at = null` ⇒ never expires.

**Why signed, not random:** verification re-hashes with `config('app.license_secret')`
and checks the hash. A client cannot forge `LIC-ENTERPRISE-XXXX` without the
secret (which ships in your code, not theirs). DB row is the record; the
signature is the tamper check.

**Status & days-left (single gate, root-cause style):**
```
status():
  if revoked                      -> 'revoked'
  if expires_at === null          -> 'active'          // lifetime / manual
  if expires_at < now()           -> 'expired'
  else                            -> 'active'
daysLeft() = expires_at?->diffInDays(now())            // null => INF (lifetime)
```
Expired or revoked ⇒ active plan falls back to `free`, paid features lock.
UI shows: "License: 12 days left" / "Expired — downgraded to Free" /
"Lifetime". Checked at activation + on request entry (cached in `settings`),
not a per-second cron.

## 4. Entitlement service (seam `for($scope)`)

```php
// app/Services/PlanService.php
final class PlanService
{
    // Model 1: $scope = null. Model 2: $scope = Tenant.
    public static function for(?Model $scope = null): self
    {
        $slug = $scope
            ? $scope->plan_slug                       // Model 2: plan per tenant
            : Setting::get('active_plan', 'free');   // Model 1: instance setting
        return new self(Plan::where('slug', $slug)->firstOrFail());
    }

    public function can(string $feature): bool
    {
        return in_array($feature, $this->plan->features, true)
            && $this->plan->is_active;
    }

    public function membersLeft(): int
    {
        $used = User::count();                        // Model 1: global. Model 2: scoped where tenant
        return max(0, ($this->plan->limits['max_members'] ?? 0) - $used);
    }

    public function projectsLeft(): int { /* same pattern */ }
}
```

Usage: `Plan::for()->can('kanban')`, `Plan::for()->membersLeft() === 0`.
One gateway → root-cause style, not checking inside every controller.

## 5. Flow

### License activation (Model 1, no PG)
```
admin enters license_key in UI/console
  -> LicenseService::activate(key)
       -> verify signature (re-hash with license_secret) + row exists
       -> settings.active_plan = plan_slug
       -> settings.license_key = key
  -> plan applies instantly, features gated via Plan::for()
```

### License issuance — two paths, one method
```
-- A. Auto (recurring): PG webhook payment.success
   BillingService::handleWebhook()
     -> verify signature + idempotency
     -> LicenseService::issue(planSlug, ['type'=>'recurring','expires_at'=>$periodEnd])
     -> settings.active_plan = planSlug

-- B. Manual (off-market / lifetime): admin console or UI
   LicenseService::issue(planSlug, ['type'=>'manual'|'lifetime','expires_at'=>null])
     -> returns signed code, admin gives it to client
     -> client activates via path above
```
Both callers use the same `issue()` — no duplicate system. Lifetime/manual
just set `expires_at = null`.

### Upgrade via billing (Model 1 extension / Model 2)
```
user picks plan -> BillingService::checkout(plan)
  -> call PG (Midtrans Snap) -> get snap_token
  -> redirect to PG
  -> PG webhook -> BillingService::handleWebhook(payload)
       -> verify signature
       -> idempotent via webhook_events.event_id
       -> create/renew subscription + invoice paid
       -> settings.active_plan = plan (or tenant.plan_slug in Model 2)
```

### Limit check on create
```
controller create member/project:
  if (Plan::for()->membersLeft() === 0) abort(402, 'Limit reached');
```

## 6. Payment gateway — recommendation (Indonesia, data Jul–Aug 2026)

MDR per method (excl. 11% VAT):

| Gateway | VA | QRIS | E-wallet | Card | Monthly | Notes |
|---|---|---|---|---|---|---|
| **Midtrans** | Rp4.000 flat | 0.7% | ~1.5% | 2.9%+Rp2.000 | – | Native GoPay, Snap drop-in, PCI-DSS, settle H+3 |
| **Xendit** | ~0.5%+flat | 0.7% | 2.70% | 2.9%+Rp2.000 | Rp25.000/sub-acct | Clean API, top-tier disbursement, settle 7d |
| **iPaymu** | Rp3.5K (cheapest) | 0.7% | ~ | ~ | – | KTP-only, SME, settle H+0–H+3 |
| **Tripay** | low | 0.7% | | | – | KTP-only, fast verify |
| **Doku** | custom | 0.7% | | custom | – | Enterprise, 6 BI licenses |

**(All QRIS = 0.7% because regulated by Bank Indonesia — identical across gateways.)**

### Choice
- **Primary: Midtrans.** Cheapest for collect-only, most popular in Indonesia,
  secure (PCI-DSS, part of GoTo), native GoPay (Indonesian buyers expect it),
  Snap drop-in makes integration fast, no setup/monthly fee. Recurring/
  subscription supported → fits a subscription SaaS.
- **Secondary (if needed): Xendit.** Cleaner API and dev-friendly docs, and
  first-class disbursement (paying out) if the business model ever needs
  payouts. Pick this if the team prefers a custom API over Snap.
- **Never** capture card data yourself. Always use the Snap redirect / PG page.

### Laravel integration
- Midtrans has no official Laravel Cashier driver (Cashier = Stripe/Paddle).
  → use the official SDK `midtrans/midtrans-php` or REST directly, wrapped in
  **1 BillingService** + **1 signed webhook controller**. No extra billing
  package needed. (Ponytail: don't pull in Cashier just for Stripe when the
  Indonesian market uses Midtrans.)

## 7. Security (required)
- Webhook: verify PG signature/HMAC, reject on failure.
- Idempotency: `webhook_events.event_id` unique → no double-processing.
- Do not store card data. Midtrans/Snap holds it.
- License key: hash in DB (not plaintext), validate via signature, not just
  string match.
- Enforce limits on the server (controller), not only in the UI.

## 8. Migration to Model 2 (notes, not yet built)
1. Add `tenants` + `workspace_id` to tables needing isolation.
2. Tenant resolver (middleware/subdomain) → fills `$scope`.
3. `Plan::for($tenant)` reads `tenant.plan_slug` (not instance setting).
4. Billing `subscriptions.scope_id = tenant_id`.
Plan-check logic & Entitlement service UNCHANGED.

## 9. Decisions still pending from user (not locked)
- Limit ENFORCE hard (reject create) vs SOFT warning?
- PG needed now, or start Model 1 without PG (manual/lifetime license only)?
- Which plans & exact limits/features per tier (free/basic/pro/enterprise)?

## 10. Open concerns (best-practice, business-variable)

These are NOT blockers for the design, but must be locked before building
`LicenseService` / `BillingService`. Business designs differ per client, so
each is left as a hook, not hardcoded.

1. **License tamper resistance (REQUIRED before LicenseService).**
   In Model 1 the client owns the DB, so they can edit `settings.active_plan`
   directly. Mitigation: `Plan::for()` must re-verify the license signature
   (re-hash with `license_secret`) on every check, not just read the setting.
   Store the license hash in `settings`; if it does not match a valid,
   non-expired, non-revoked `licenses` row → fall back to `free`. Never trust
   the raw `active_plan` string.

2. **Grace period & downgrade experience.**
   On expiry: hard-lock paid features immediately, or grant a grace window
   (e.g. 7 days, banner only)? On downgrade below current usage: freeze the
   excess data (projects/members), do NOT auto-delete it. Best practice =
   freeze, never delete customer data silently.

3. **Trial.**
   Is `free` itself the trial, or is there a separate time-boxed full-feature
   trial? If separate, add `trial_ends_at` to `licenses` and gate on it. Not
   covered by the current `plans` model — add only when a trial is needed.

4. **Plan change & proration.**
   Mid-cycle upgrade: charge full or prorated? Downgrade: immediate or at
   period end? Midtrans does not prorate automatically — the math lives in
   `BillingService`. Expose an `onPlanChange(Plan $old, Plan $new)` hook; do
   not hardcode pricing rules in the controller.

5. **Per-instance vs per-seat pricing.**
   Entitlement limits are per-instance (`max_members`). Billing may instead be
   per active seat. This does NOT change the entitlement seam — only the
   billing math in `BillingService` (count active users × seat price). The
   `Plan::for($scope)` seam already covers both; decide the pricing model
   when wiring billing, not in the entitlement layer.
