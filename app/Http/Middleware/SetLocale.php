<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;

/**
 * Sets the app locale from the session (fallback to config app.locale).
 */
class SetLocale
{
    public function handle(Request $request, Closure $next)
    {
        $locale = Session::get('locale', config('app.locale'));
        if (! in_array($locale, ['en', 'id'])) {
            $locale = config('app.locale');
        }
        App::setLocale($locale);

        return $next($request);
    }
}
