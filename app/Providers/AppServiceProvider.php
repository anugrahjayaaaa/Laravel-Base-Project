<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        \Illuminate\Pagination\Paginator::useBootstrap();
        \Illuminate\Pagination\LengthAwarePaginator::useBootstrap();

        \App\Models\User::observe(\App\Observers\UserObserver::class);
        \Spatie\Permission\Models\Role::observe(\App\Observers\RoleObserver::class);
        \Spatie\Permission\Models\Permission::observe(\App\Observers\PermissionObserver::class);

        // ponytail: Sentry DSN-gated; no-op locally when DSN empty
        if (class_exists(\Sentry\Sentry::class) && config('sentry.dsn')) {
            \Sentry\Sentry::init([
                'dsn' => config('sentry.dsn'),
                'release' => config('app.version'),
                'traces_sample_rate' => (float) config('sentry.traces_sample_rate', 0.2),
            ]);
        }

        // Recent activity for header notifications dropdown
        \Illuminate\Support\Facades\View::composer('layouts.app', function ($view) {
            if (auth()->check() && class_exists(\Spatie\Activitylog\Models\Activity::class)) {
                $items = \Spatie\Activitylog\Models\Activity::latest()->limit(5)->get();
                $view->with('notifications', ['items' => $items, 'unread' => $items->count()]);
            } else {
                $view->with('notifications', ['items' => collect(), 'unread' => 0]);
            }
        });
    }
}
