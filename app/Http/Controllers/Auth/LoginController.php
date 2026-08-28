<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    public function show()
    {
        return view('auth.login');
    }

    /**
     * Authenticate a user via email or username and start a session.
     *
     * @param  Request  $request  Must contain: identifier (email|username), password, optional remember (bool)
     * @return RedirectResponse  Redirect to intended page (dashboard) on success,
     *                            or back with 'identifier' error on failure / lockout.
     *
     * @throws ValidationException  On bad credentials or too many attempts (429-style lockout)
     *
     * @details
     * - Login field resolved: email if value looks like an email, else username (phone removed).
     * - Rate limiter key: "login:{ip}:{identifier}" stored in CACHE (driver=config('cache.default'),
     *   currently 'database' -> table `cache`, key column). Max 5 hits; on fail adds a 900s (15m) window.
     * - On success: clears the rate-limiter key, regenerates SESSION (driver='database' -> table `sessions`).
     */
    public function store(Request $request): RedirectResponse
    {
        // Throttle key stored in CACHE (table `cache`): login:{ip}:{identifier}
        $identifier = (string) $request->input('identifier');
        $throttleKey = 'login:' . $request->ip() . ':' . strtolower($identifier);

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            throw ValidationException::withMessages([
                'identifier' => "Too many attempts. Try again in {$seconds}s.",
            ]);
        }

        // Resolve login field: email OR username
        $field = filter_var($identifier, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        $user = User::where($field, $identifier)->first();

        if (! $user || ! Auth::guard('web')->attempt([$field => $identifier, 'password' => $request->password], $request->boolean('remember'))) {
            // Failed attempt -> increment lock window in CACHE (table `cache`), 900s
            RateLimiter::hit($throttleKey, 900);
            throw ValidationException::withMessages([
                'identifier' => 'These credentials do not match our records.',
            ]);
        }

        // ponytail: attempt() may not fire Login event under test session guard; dispatch explicitly so audit is consistent
        event(new \Illuminate\Auth\Events\Login('web', $user, $request->boolean('remember')));

        RateLimiter::clear($throttleKey);
        // Regenerate SESSION (driver='database' -> table `sessions`) to prevent fixation
        $request->session()->regenerate();

        return redirect()->intended(route('dashboard'));
    }

    /**
     * Log the current user out and destroy the session.
     *
     * @param  Request  $request
     * @return RedirectResponse  Redirect to '/'
     *
     * @details Invalidates SESSION (driver='database' -> table `sessions`) and
     * regenerates the CSRF token.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
