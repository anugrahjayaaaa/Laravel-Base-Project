<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Feature;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FeatureController extends Controller
{
    public function index(): View
    {
        $features = Feature::orderBy('label')->get();

        return view('features.index', compact('features'));
    }

    public function toggle(Request $request, string $slug): RedirectResponse
    {
        $feature = Feature::findOrFail($slug);
        $feature->update(['enabled' => (bool) $request->boolean('enabled')]);

        return redirect()->route('features.index')->with('success', $feature->enabled ? __('messages.feature_enabled', ['label' => $feature->label]) : __('messages.feature_disabled', ['label' => $feature->label]));
    }
}
