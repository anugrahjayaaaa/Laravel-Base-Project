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
            // require the telescope.view permission AND the 'telescope' feature flag,
            // so even super-admin is denied unless explicitly granted + enabled.
            if (app()->environment('local')) {
                return true;
            }

            return $user->can('telescope.view') && function_exists('feature') && feature('telescope');
        });
    }
}
