<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Logs 4xx client errors (except 404 noise) so bad requests like a 405
 * from a wrong HTTP method are visible in monitoring. 5xx are already
 * logged by Laravel's exception handler.
 */
class LogHttpErrors
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        $status = $response->getStatusCode();
        if ($status >= 400 && $status < 500 && $status !== 404) {
            Log::warning('HTTP '.$status, [
                'url' => $request->fullUrl(),
                'method' => $request->method(),
                'ip' => $request->ip(),
                'user_id' => $request->user()?->id,
            ]);
        }

        return $response;
    }
}
