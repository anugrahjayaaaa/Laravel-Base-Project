<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\FeatureResource;
use App\Models\Feature;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @group Features
 *
 * Feature flags (gated by `feature.manage`).
 */
class FeatureApiController extends Controller
{
    /** List all feature flags. */
    public function index(): JsonResponse
    {
        return response()->json(FeatureResource::collection(Feature::orderBy('label')->get()));
    }

    /** Toggle a feature flag. */
    public function toggle(Request $request, string $slug): JsonResponse
    {
        $feature = Feature::findOrFail($slug);
        $feature->update(['enabled' => (bool) $request->boolean('enabled')]);

        return response()->json(new FeatureResource($feature));
    }
}
