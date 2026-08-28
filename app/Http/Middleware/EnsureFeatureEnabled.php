<?php

namespace App\Http\Middleware;

use App\Models\Feature;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Blocks access to a route unless its feature flag is enabled.
 * Pair with `can:` so the full gate is: has permission AND feature on.
 * Missing feature row => 404 (fail-closed), never silently allowed.
 * Note: feature gating is independent of RBAC — even super-admin is blocked
 * when a feature is off (per product rule: flag off => inaccessible).
 */
class EnsureFeatureEnabled
{
    public function handle(Request $request, Closure $next, string $slug): Response
    {
        $enabled = Feature::where('slug', $slug)->where('enabled', true)->exists();
        if (! $enabled) {
            abort(404);
        }

        return $next($request);
    }
}
