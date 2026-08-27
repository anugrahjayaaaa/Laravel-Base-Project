<?php

namespace App\Observers;

use Illuminate\Support\Facades\Request;

class UserObserver
{
    public function created($user)
    {
        activity()->causedBy($user)->withProperties([
            'ip' => Request::ip(), 'user_agent' => Request::userAgent(),
        ])->performedOn($user)->log('user_created');
    }

    public function updated($user)
    {
        activity()->causedBy(auth()->user())->withProperties([
            'ip' => Request::ip(), 'user_agent' => Request::userAgent(),
            'changes' => $user->getDirty(),
        ])->performedOn($user)->log('user_updated');
    }

    public function deleted($user)
    {
        activity()->causedBy(auth()->user())->withProperties([
            'ip' => Request::ip(), 'user_agent' => Request::userAgent(),
        ])->performedOn($user)->log('user_deleted');
    }

    public function restored($user)
    {
        activity()->causedBy(auth()->user())->withProperties([
            'ip' => Request::ip(), 'user_agent' => Request::userAgent(),
        ])->performedOn($user)->log('user_restored');
    }
}
