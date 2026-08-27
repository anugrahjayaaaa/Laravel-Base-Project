<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Auth\Events\Verified;
use Illuminate\Events\Dispatcher;
use Illuminate\Support\Facades\Request;

class LogAuthentication
{
    private const LABELS = [
        Login::class => 'login_success',
        Logout::class => 'logout',
        Failed::class => 'login_failed',
        PasswordReset::class => 'password_reset',
        Verified::class => 'email_verified',
    ];

    public function handle(object $event): void
    {
        $action = self::LABELS[$event::class] ?? 'auth';
        $user = $event->user ?? ($event->credentials['identifier'] ?? null);

        activity()
            ->causedBy($event->user ?? null)
            ->withProperties([
                'ip' => Request::ip(),
                'user_agent' => Request::userAgent(),
                'identifier' => is_object($user) ? $user->username ?? $user->email : $user,
            ])
            ->log($action);
    }
}
