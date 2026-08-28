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
 *
 * Bypass: a user holding the `feature.manage` permission is never blocked by
 * a feature flag (they manage flags, so they must reach modules even when off
 * to use/audit them). Super-admin still observes the flag unless it holds
 * feature.manage — flag off blocks everyone else.
 */
class EnsureFeatureEnabled
{
    public function handle(Request $request, Closure $next, string $slug): Response
    {
        if ($request->user()?->can('feature.manage')) {
            return $next($request);
        }

        $enabled = Feature::where('slug', $slug)->where('enabled', true)->exists();
        if (! $enabled) {
            abort(404);
        }

        return $next($request);
    }
}
