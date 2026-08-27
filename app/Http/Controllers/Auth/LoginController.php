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

    public function store(Request $request): RedirectResponse
    {
        // ponytail: throttle key = ip + identifier (matches docs lockout rule)
        $identifier = (string) $request->input('identifier');
        $throttleKey = 'login:' . $request->ip() . ':' . strtolower($identifier);

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            throw ValidationException::withMessages([
                'identifier' => "Too many attempts. Try again in {$seconds}s.",
            ]);
        }

        // Resolve login field: username OR phone OR email
        $field = filter_var($identifier, FILTER_VALIDATE_EMAIL) ? 'email'
            : (preg_match('/^\+?\d{8,15}$/', $identifier) ? 'phone' : 'username');

        $user = User::where($field, $identifier)->first();

        if (! $user || ! Auth::guard('web')->attempt([$field => $identifier, 'password' => $request->password], $request->boolean('remember'))) {
            RateLimiter::hit($throttleKey, 900); // 15m lock window
            throw ValidationException::withMessages([
                'identifier' => 'These credentials do not match our records.',
            ]);
        }

        // ponytail: attempt() may not fire Login event under test session guard; dispatch explicitly so audit is consistent
        event(new \Illuminate\Auth\Events\Login('web', $user, $request->boolean('remember')));

        RateLimiter::clear($throttleKey);
        $request->session()->regenerate();

        return redirect()->intended(route('dashboard'));
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
