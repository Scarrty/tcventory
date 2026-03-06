<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * @var array<string>
     */
    private const PERMISSIONS = [
        'catalog.view',
        'catalog.create',
        'catalog.update',
        'catalog.delete',
        'inventory.view',
        'inventory.create',
        'inventory.update',
        'inventory.delete',
        'finance.view',
        'finance.create',
        'finance.update',
        'finance.delete',
        'audit.view',
        'admin.access',
    ];

    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach (self::PERMISSIONS as $permission) {
            Permission::findOrCreate($permission, 'web');
        }

        $admin = Role::findOrCreate('admin', 'web');
        $operator = Role::findOrCreate('operator', 'web');
        $accounting = Role::findOrCreate('accounting', 'web');
        $viewer = Role::findOrCreate('viewer', 'web');

        $admin->syncPermissions(self::PERMISSIONS);

        $operator->syncPermissions([
            'catalog.view',
            'catalog.create',
            'catalog.update',
            'inventory.view',
            'inventory.create',
            'inventory.update',
            'audit.view',
        ]);

        $accounting->syncPermissions([
            'finance.view',
            'finance.create',
            'finance.update',
            'inventory.view',
            'audit.view',
        ]);

        $viewer->syncPermissions([
            'catalog.view',
            'inventory.view',
            'finance.view',
            'audit.view',
        ]);
    }
}
