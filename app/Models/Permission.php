<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Spatie\Permission\Models\Permission as SpatiePermission;

/**
 * Application Permission model.
 *
 * Extends spatie's Permission and adds SoftDeletes so permissions can be trashed/restored.
 * Referenced by config/permission.php (models.permission) so all permission queries/mutations
 * route through this class (not the spatie base) — required for the RBAC observers
 * (App\Observers\PermissionObserver) to fire.
 *
 * @property int $id
 * @property string $name
 * @property string $guard_name
 * @property Carbon|null $deleted_at (SoftDeletes)
 */
class Permission extends SpatiePermission
{
    use SoftDeletes;

    /** Feature slug a permission belongs to = the part before the first dot. */
    public static function featureOf(string $name): string
    {
        return explode('.', $name, 2)[0];
    }
}
