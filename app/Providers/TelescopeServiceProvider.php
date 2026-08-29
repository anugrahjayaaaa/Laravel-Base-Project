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
     * Override parent: make the 'telescope' feature flag authoritative in ALL
     * environments (parent short-circuits with environment('local') and bypasses
     * the gate, so a disabled feature would still be reachable on local).
     */
    protected function authorization(): void
    {
        $this->gate();

        Telescope::auth(function ($request) {
            return Gate::check('viewTelescope', [$request->user()]);
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
        Gate::define('viewTelescope', function (?User $user) {
            // ponytail: typed User throws on null (stateless Telescope API calls
            // have no resolved user) -> Gate silently denies. Resolve from auth().
            $user ??= auth()->user();

            if (! $user instanceof User) {
                return false;
            }

            // ponytail: require BOTH the telescope.view permission AND the
            // enabled 'telescope' feature flag in every environment, so even
            // super-admin is denied unless explicitly granted + enabled.
            return $user->can('telescope.view') && function_exists('feature') && feature('telescope');
        });
    }
}
