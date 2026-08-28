<?php

namespace App\Observers;

use Illuminate\Support\Facades\Request;
use App\Models\Permission;

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

    public function restored(Permission $permission)
    {
        activity()->causedBy(auth()->user())->withProperties([
            'ip' => Request::ip(), 'user_agent' => Request::userAgent(),
        ])->performedOn($permission)->log('permission_restored');
    }

    /**
     * Log a permanent (hard) delete of a permission to the audit trail.
     *
     * @param  \App\Models\Permission  $permission
     * @return void
     *
     * @details Writes a 'permission_force_deleted' row into DB table `activity_log`
     * (spatie/activitylog). Unrecoverable, unlike `deleted()` (soft delete).
     */
    public function forceDeleted(Permission $permission)
    {
        activity()->causedBy(auth()->user())->withProperties([
            'ip' => Request::ip(), 'user_agent' => Request::userAgent(),
        ])->performedOn($permission)->log('permission_force_deleted');
    }
}
