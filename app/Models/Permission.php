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

    /**
     * Feature slug a permission belongs to. The DB permission prefix is singular
     * (user.*) while feature flags are plural (users) — map explicitly so the
     * plan UI can filter permissions by the enabled features without hardcoding
     * the relationship in Blade.
     */
    public static function featureOf(string $name): ?string
    {
        $prefix = explode('.', $name, 2)[0];

        $map = [
            'user' => 'users',
            'role' => 'roles',
            'permission' => 'permissions',
            'audit' => 'audit',
            'session' => 'sessions',
            'api-token' => 'api-tokens',
            'translation' => 'translations',
            'logs' => 'logs',
            'telescope' => 'telescope',
            'periscope' => 'periscope',
            'feature' => 'features',
        ];

        return $map[$prefix] ?? $prefix;
    }
}
