<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use App\Models\Feature;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    // ponytail: flat permission list; expand per-module as features land
    private const PERMISSIONS = [
        'user.view', 'user.create', 'user.edit', 'user.delete', 'user.restore', 'user.force-delete', 'user.lock',
        'role.view', 'role.create', 'role.edit', 'role.delete', 'role.restore', 'role.force-delete',
        'permission.view', 'permission.create', 'permission.edit', 'permission.delete', 'permission.restore', 'permission.force-delete',
        'audit.view',
        'session.view', 'session.revoke',
        'api-token.view', 'api-token.create', 'api-token.delete',
        'feature.manage',
    ];

    // Module-level feature flags: slug => label. Toggling off blocks the whole module
    // even for users who hold the relevant permission (flag off => inaccessible).
    private const FEATURES = [
        'users' => 'Users',
        'roles' => 'Roles',
        'permissions' => 'Permissions',
        'audit' => 'Audit Log',
        'sessions' => 'Sessions',
        'api-tokens' => 'API Tokens',
    ];

    public function run(): void
    {
        // Permissions
        foreach (self::PERMISSIONS as $perm) {
            Permission::findOrCreate($perm, 'web');
        }

        // Feature flags (all on by default)
        foreach (self::FEATURES as $slug => $label) {
            Feature::updateOrCreate(['slug' => $slug], ['label' => $label, 'enabled' => true]);
        }

        // Roles
        $superAdmin = Role::findOrCreate('super-admin', 'web');
        $admin = Role::findOrCreate('admin', 'web');
        $staff = Role::findOrCreate('staff', 'web');

        $admin->syncPermissions(self::PERMISSIONS);
        $staff->syncPermissions(['user.view', 'audit.view']);
        $superAdmin->syncPermissions(self::PERMISSIONS);

        // Super-admin user (first-run)
        $user = User::updateOrCreate(
            ['email' => 'admin@laravel-base.local'],
            [
                'name' => 'Super Admin',
                'username' => 'superadmin',
                'phone' => '+6281200000001',
                'password' => bcrypt('Admin@base12345'),
                'email_verified_at' => now(),
            ]
        );
        $user->assignRole('super-admin');
    }
}
