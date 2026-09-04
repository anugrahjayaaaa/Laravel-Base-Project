<?php

namespace App\Services;

use App\Models\License;
use App\Models\Permission;
use App\Models\Plan;
use App\Models\Setting;
use App\Models\User;

/**
 * Entitlement gate. Single point every access/limit check routes through
 * (doc §4). Reads the SNAPSHOT from the active license, not the live plans
 * row (catalog versioning, §10.8) — so a mid-term plan change never narrows a
 * paying client's entitlement.
 *
 * Seam: for($scope) — Model 1 $scope=null (instance), Model 2 $scope=Tenant.
 */
final class PlanService
{
    private Plan $plan;

    private ?License $license;

    public function __construct(Plan $plan, ?License $license)
    {
        $this->plan = $plan;
        $this->license = $license;
    }

    /** Model 1: $scope = null. Model 2: $scope = Tenant. */
    public static function for(?object $scope = null, ?User $user = null): self
    {
        // ponytail: Model 2 seam — read plan_slug from tenant when present
        // Default plan (no license) vs active_plan (license runtime). See docs/licensing-and-billing.md §3.
        $mode = Setting::get('license_mode', 'global');

        // Per-user mode: resolve plan from user's own license
        if ($mode === 'per_user' && $user) {
            $slug = $user->license?->plan_slug ?? Setting::get('default_plan', 'free');
            $license = $user->license;
            $plan = Plan::where('slug', $slug)->firstOrFail();

            return new self($plan, $license);
        }

        $licenseKey = Setting::get('license_key');
        $slug = $scope->plan_slug
            ?? ($licenseKey ? Setting::get('active_plan', 'free') : Setting::get('default_plan', 'free'));
        $plan = Plan::where('slug', $slug)->firstOrFail();

        // §10.1 tamper resistance: re-verify the ACTIVATED license on every
        // check. A client owning the DB could edit settings.active_plan, but
        // without a matching valid+signed license row we fall back to free.
        $license = null;
        if ($key = Setting::get('license_key')) {
            $license = License::where('license_key', $key)->first();
            if (! $license || ! LicenseService::verify($key, $license) || ! $license->isActiveAndValid()) {
                $license = null; // tampered/expired/forged -> ignore, use plan row defaults
            }
        }

        return new self($plan, $license);
    }

    /** Whether a feature is enabled for the active plan (from license snapshot). */
    public function can(string $feature): bool
    {
        // §10.1 tamper resistance: only the 'free' plan is grantable without a
        // valid activated license. Any paid plan REQUIRES a matching signed,
        // active license — a client cannot create a 'pro' plan row + flip the
        // setting to unlock features.
        if (! $this->plan->is_active) {
            return false;
        }
        if ($this->plan->slug !== 'free' && ! $this->license) {
            return false;
        }

        $features = $this->license?->snapshot['features']
            ?? $this->plan->features
            ?? [];

        return in_array($feature, $features, true);
    }

    public function membersLeft(): int
    {
        $max = $this->limit('max_members', 0);

        return max(0, $max - User::count());
    }

    private function limit(string $key, int $default): int
    {
        // paid plan without a valid license => no headroom (tamper-safe, §10.1)
        if ($this->plan->slug !== 'free' && ! $this->license) {
            return 0;
        }

        return (int) ($this->limits()[$key] ?? $default);
    }

    /** Snapshot limits from the license (or plan row), respecting tamper guard. */
    private function limits(): array
    {
        if ($this->plan->slug !== 'free' && ! $this->license) {
            return [];
        }

        return $this->license?->snapshot['limits']
            ?? $this->plan->limits
            ?? [];
    }

    /** Permission names a subscriber may assign when creating/editing roles.
     *  Empty array = no permission assigned (deny) unless explicitly listed.
     *  Role creation itself is gated by the `role.create` permission (Form
     *  Request authz), not by a separate plan flag.
     */
    public function allowedPermissions(): array
    {
        $allowed = $this->limits()['allowed_permissions'] ?? [];

        return is_array($allowed) ? $allowed : [];
    }

    /** Sync permissions to all users based on plan features + allowed_permissions.
     *  Called when plan is created/updated. Maps feature slugs → permissions via
     *  Permission::featureOf(). Users without explicit permission keep only their
     *  role-derived base permissions.
     */
    public static function syncPermissionsForPlan(Plan $plan): void
    {
        // ponytail: derive permission names from plan features + allowed_permissions limits
        $features = (array) ($plan->features ?? []);
        $limits = (array) ($plan->limits ?? []);
        $explicit = (array) ($limits['allowed_permissions'] ?? []);

        // Map: feature slug → permission names (permission.* belong to 'permissions' feature, etc.)
        // Only sync permissions that belong to a feature enabled in this plan.
        $syncable = [];
        foreach ($features as $featureSlug) {
            // ponytail: feature slug maps to permission names via Permission::featureOf()
            foreach (Permission::all() as $perm) {
                if (Permission::featureOf($perm->name) === $featureSlug) {
                    $syncable[$perm->name] = $perm->name;
                }
            }
        }

        // Explicit allowed_permissions overrides (e.g. role.create even if 'roles' feature off)
        foreach ($explicit as $permName) {
            $syncable[$permName] = $permName;
        }

        // ponytail: in global mode sync to ALL users; per-user syncs via LicenseService
        if (Setting::get('license_mode', 'global') === 'per_user') {
            return; // per-user: permission sync happens when license assigned
        }

        foreach (User::all() as $user) {
            $user->syncPermissions(array_values($syncable));
        }
    }
}
