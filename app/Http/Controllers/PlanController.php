<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PlanController extends Controller
{
    // ponytail: plans are fully custom (CRUD, doc §9b); gated by feature.manage
    public function __construct()
    {
        $this->middleware('can:feature.manage');
    }

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

    /** Parse the limits/features textareas into arrays. */
    private function validated(Request $request): array
    {
        $d = $request->validate([
            'slug' => 'required|string|alpha_dash|unique:plans,slug,'.(optional($request->route('plan'))?->id ?? 'NULL').',id',
            'name' => 'required|string|max:120',
            'price_monthly' => 'required|numeric|min:0',
            'is_active' => 'boolean',
            'limits' => 'nullable|string',
            'features' => 'nullable|string',
        ]);

        $lines = fn ($s) => collect(explode("\n", $s))
            ->map(fn ($x) => trim($x))->filter()->values()->all();

        $limits = [];
        foreach ($lines($request->input('limits', '')) as $row) {
            [$k, $v] = array_pad(explode(':', $row, 2), 2, 0);
            $limits[trim($k)] = (int) trim($v);
        }

        return [
            'slug' => $d['slug'],
            'name' => $d['name'],
            'price_monthly' => $d['price_monthly'],
            'is_active' => $request->boolean('is_active'),
            'limits' => $limits,
            'features' => $lines($request->input('features', '')),
        ];
    }
}
