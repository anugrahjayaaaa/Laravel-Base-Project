<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Laravel\Telescope\IncomingEntry;
use Laravel\Telescope\Telescope;
use Laravel\Telescope\TelescopeApplicationServiceProvider;

class TelescopeServiceProvider extends TelescopeApplicationServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Telescope::night();

        $this->hideSensitiveRequestDetails();

        $isLocal = $this->app->environment('local');

        // ponytail: hard-disable storage outside local — Telescope persists PII
        // (emails, tokens) in DB; keep it local-only in a base project.
        Telescope::filter(function (IncomingEntry $entry) use ($isLocal) {
            return $isLocal;
        });
    }

    /**
     * Prevent sensitive request details from being logged by Telescope.
     */
    protected function hideSensitiveRequestDetails(): void
    {
        if ($this->app->environment('local')) {
            return;
        }

        Telescope::hideRequestParameters(['_token']);

        Telescope::hideRequestHeaders([
            'cookie',
            'x-csrf-token',
            'x-xsrf-token',
        ]);
    }

    /**
     * Register the Telescope gate.
     *
     * This gate determines who can access Telescope in non-local environments.
     */
    protected function gate(): void
    {
        Gate::define('viewTelescope', function (User $user) {
            // ponytail: local is open (storage already local-only); elsewhere
            // require a real role rather than a hardcoded email allowlist.
            if (app()->environment('local')) {
                return true;
            }

            return method_exists($user, 'hasRole')
                ? $user->hasRole('super-admin')
                : in_array($user->email, [
                    // add prod/staging emails here
                ]);
        });
    }
}
