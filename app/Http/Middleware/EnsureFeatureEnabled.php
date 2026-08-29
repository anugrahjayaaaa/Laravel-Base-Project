<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Laravel\Pennant\Feature;
use Symfony\Component\HttpFoundation\Response;

/**
 * Blocks access to a route unless its feature flag is enabled (Laravel Pennant).
 * Pair with `can:` so the full gate is: has permission AND feature on.
 * Unknown feature => 404 (fail-closed), never silently allowed.
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

        if (! Feature::active($slug)) {
            abort(404);
        }

        return $next($request);
    }
}
