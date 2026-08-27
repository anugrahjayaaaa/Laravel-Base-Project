<?php

namespace App\Observers;

use Illuminate\Support\Facades\Request;
use Spatie\Permission\Models\Permission;

class PermissionObserver
{
    public function created(Permission $permission)
    {
        activity()->causedBy(auth()->user())->withProperties([
            'ip' => Request::ip(), 'user_agent' => Request::userAgent(),
        ])->performedOn($permission)->log('permission_created');
    }

    public function updated(Permission $permission)
    {
        activity()->causedBy(auth()->user())->withProperties([
            'ip' => Request::ip(), 'user_agent' => Request::userAgent(),
            'changes' => $permission->getDirty(),
        ])->performedOn($permission)->log('permission_updated');
    }

    public function deleted(Permission $permission)
    {
        activity()->causedBy(auth()->user())->withProperties([
            'ip' => Request::ip(), 'user_agent' => Request::userAgent(),
        ])->performedOn($permission)->log('permission_deleted');
    }
}
