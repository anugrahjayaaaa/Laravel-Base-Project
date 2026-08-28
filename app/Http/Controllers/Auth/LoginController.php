<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
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
     * @param  LoginRequest  $request  Validated: identifier (email|username), password, optional remember (bool)
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
    public function store(LoginRequest $request): RedirectResponse
    {
        $identifier = (string) $request->input('identifier');
        $throttleKey = 'login:' . $request->ip() . ':' . strtolower($identifier);
        $userKey = 'login:user:' . strtolower($identifier); // ponytail: account-centric; survives IP rotation

        if (RateLimiter::tooManyAttempts($userKey, 5) || RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = max(RateLimiter::availableIn($userKey), RateLimiter::availableIn($throttleKey));
            throw ValidationException::withMessages([
                'identifier' => "Too many attempts. Try again in {$seconds}s.",
            ]);
        }

        // Resolve login field: email OR username
        $field = filter_var($identifier, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        $user = User::where($field, $identifier)->first();

        // Account-level lockout (survives IP rotation; auto-unlocks when locked_until passes)
        if ($user && $user->isLocked()) {
            $message = $user->isPermanentlyLocked()
                ? 'Account permanently locked. Contact an administrator.'
                : 'Account locked. Try again in ' . $user->locked_until->diffInSeconds(now()) . 's.';
            throw ValidationException::withMessages([
                'identifier' => $message,
            ]);
        }

        if (! $user || ! Auth::guard('web')->attempt([$field => $identifier, 'password' => $request->password], $request->boolean('remember'))) {
            // Failed attempt -> increment lock window in CACHE (table `cache`), 900s
            RateLimiter::hit($userKey, 900);
            RateLimiter::hit($throttleKey, 900);

            // On the 5th failed attempt, lock the account for 15 minutes (DB-persisted)
            if (RateLimiter::attempts($userKey) >= 5 && $user) {
                $user->update(['locked_until' => now()->addMinutes(15)]);
            }

            throw ValidationException::withMessages([
                'identifier' => 'These credentials do not match our records.',
            ]);
        }

        // ponytail: attempt() may not fire Login event under test session guard; dispatch explicitly so audit is consistent
        event(new \Illuminate\Auth\Events\Login('web', $user, $request->boolean('remember')));

        RateLimiter::clear($userKey);
        RateLimiter::clear($throttleKey);
        // Clear any expired lock marker on successful login
        if ($user->locked_until !== null) {
            $user->update(['locked_until' => null]);
        }
        // Regenerate SESSION (driver='database' -> table `sessions`) to prevent fixation
        $request->session()->regenerate();

        return redirect()->intended(route('dashboard'));
    }

    /**
     * Log the current user out and destroy the session.
     *
     * @param  LoginRequest  $request  (only CSRF/abort; no body validated)
     * @return RedirectResponse  Redirect to '/'
     *
     * @details Invalidates SESSION (driver='database' -> table `sessions`) and
     * regenerates the CSRF token.
     */
    public function destroy(LoginRequest $request): RedirectResponse
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
