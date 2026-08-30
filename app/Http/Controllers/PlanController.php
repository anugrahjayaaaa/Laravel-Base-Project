<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use App\Models\Plan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PlanController extends Controller
{
    public function index(): View
    {
        $plans = Plan::orderBy('price_monthly')->get();

        return view('plans.index', compact('plans'));
    }

    public function create(): View
    {
        return view('plans.form', [
            'plan' => null,
            'permissions' => Permission::orderBy('name')->pluck('name'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        Plan::create($data);

        return redirect()->route('plans.index')->with('success', __('messages.plan_created'));
    }

    public function edit(Plan $plan): View
    {
        return view('plans.form', [
            'plan' => $plan,
            'permissions' => Permission::orderBy('name')->pluck('name'),
        ]);
    }

    public function update(Request $request, Plan $plan): RedirectResponse
    {
        $plan->update($this->validated($request));

        return redirect()->route('plans.index')->with('success', __('messages.plan_updated'));
    }

    public function destroy(Plan $plan): RedirectResponse
    {
        // ponytail: never delete 'free' — it is the baseline
        if ($plan->slug === 'free') {
            return redirect()->route('plans.index')->with('error', __('messages.plan_free_protected'));
        }
        $plan->delete();

        return redirect()->route('plans.index')->with('success', __('messages.plan_deleted'));
    }

    /**
     * Parse + authorize the form into a Plan array.
     * Server-side guardrails (cegah bypass client): feature count ≤ max_features,
     * allowed_permissions must belong to an enabled feature.
     */
    private function validated(Request $request): array
    {
        $d = $request->validate([
            'name' => 'required|string|max:120',
            'slug' => 'nullable|string|alpha_dash|unique:plans,slug,'.(optional($request->route('plan'))?->id ?? 'NULL').',id',
            'price_monthly' => 'required|numeric|min:0',
            'billing_period' => 'required|in:monthly,lifetime',
            'is_active' => 'boolean',
            'max_members' => 'nullable|integer|min:0',
            'max_roles' => 'nullable|integer|min:0',
            'max_permissions' => 'nullable|integer|min:0',
            'max_features' => 'nullable|integer|min:0',
            'max_storage_mb' => 'nullable|integer|min:0',
            'can_create_roles' => 'boolean',
            'allowed_permissions' => 'array',
            'allowed_permissions.*' => 'string',
            'features' => 'array',
            'features.*' => 'string',
        ]);

        $features = $request->input('features', []);
        $maxFeatures = (int) $request->input('max_features', 0);

        // guard: feature count must respect max_features (0 = unlimited)
        if ($maxFeatures > 0 && count($features) > $maxFeatures) {
            abort(422, 'Feature count exceeds plan limit (max '.$maxFeatures.').');
        }

        // guard: allowed_permissions must belong to an enabled feature only
        $allowedPerms = $request->input('allowed_permissions', []);
        $validPerms = Permission::whereIn('name', $allowedPerms)
            ->get()
            ->filter(fn ($p) => in_array(Permission::featureOf($p->name), $features, true))
            ->pluck('name')
            ->all();
        if (count($validPerms) !== count($allowedPerms)) {
            abort(422, 'Some allowed permissions do not belong to an enabled feature.');
        }

        // ponytail: slug auto from name; JS pre-fills, this is the server fallback
        $slug = $d['slug'] ?? '' ?: Str::slug($d['name']);

        $limits = [];
        foreach (array_keys(Plan::LIMIT_KEYS) as $key) {
            if ($request->filled($key)) {
                $limits[$key] = (int) $request->input($key);
            }
        }
        $limits['can_create_roles'] = $request->boolean('can_create_roles');
        $limits['allowed_permissions'] = $validPerms;

        return [
            'name' => $d['name'],
            'slug' => $slug,
            'price_monthly' => $d['price_monthly'],
            'billing_period' => $d['billing_period'],
            'is_active' => $request->boolean('is_active'),
            'limits' => $limits,
            'features' => $features,
        ];
    }
}
