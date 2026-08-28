<?php

namespace App\Observers;

use Illuminate\Support\Facades\Request;
use App\Models\Role;

class RoleObserver
{
    public function created(Role $role)
    {
        activity()->causedBy(auth()->user())->withProperties([
            'ip' => Request::ip(), 'user_agent' => Request::userAgent(),
        ])->performedOn($role)->log('role_created');
    }

    public function updated(Role $role)
    {
        activity()->causedBy(auth()->user())->withProperties([
            'ip' => Request::ip(), 'user_agent' => Request::userAgent(),
            'changes' => $role->getDirty(),
        ])->performedOn($role)->log('role_updated');
    }

    public function deleted(Role $role)
    {
        activity()->causedBy(auth()->user())->withProperties([
            'ip' => Request::ip(), 'user_agent' => Request::userAgent(),
        ])->performedOn($role)->log('role_deleted');
    }

    public function restored(Role $role)
    {
        activity()->causedBy(auth()->user())->withProperties([
            'ip' => Request::ip(), 'user_agent' => Request::userAgent(),
        ])->performedOn($role)->log('role_restored');
    }

    public function forceDeleted(Role $role)
    {
        activity()->causedBy(auth()->user())->withProperties([
            'ip' => Request::ip(), 'user_agent' => Request::userAgent(),
        ])->performedOn($role)->log('role_force_deleted');
    }
}
