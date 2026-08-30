<?php

namespace App\Http\Controllers;

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
        return view('plans.form', ['plan' => null]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        Plan::create($data);

        return redirect()->route('plans.index')->with('success', __('messages.plan_created'));
    }

    public function edit(Plan $plan): View
    {
        return view('plans.form', compact('plan'));
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

    /** Parse the form (typed fields + checkbox features) into a Plan array. */
    private function validated(Request $request): array
    {
        $d = $request->validate([
            'name' => 'required|string|max:120',
            'slug' => 'nullable|string|alpha_dash|unique:plans,slug,'.(optional($request->route('plan'))?->id ?? 'NULL').',id',
            'price_monthly' => 'required|numeric|min:0',
            'billing_period' => 'required|in:monthly,lifetime',
            'is_active' => 'boolean',
            'max_members' => 'nullable|integer|min:0',
            'max_projects' => 'nullable|integer|min:0',
            'features' => 'array',
            'features.*' => 'string',
        ]);

        // ponytail: slug auto from name; JS pre-fills, this is the server fallback
        $slug = $d['slug'] ?: Str::slug($d['name']);

        $limits = [];
        foreach (array_keys(Plan::LIMIT_KEYS) as $key) {
            if ($request->filled($key)) {
                $limits[$key] = (int) $request->input($key);
            }
        }

        return [
            'name' => $d['name'],
            'slug' => $slug,
            'price_monthly' => $d['price_monthly'],
            'billing_period' => $d['billing_period'],
            'is_active' => $request->boolean('is_active'),
            'limits' => $limits,
            'features' => $request->input('features', []),
        ];
    }
}
