<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
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

    /**
     * @var array<string, array<int, string>>
     */
    private const ROLE_PERMISSIONS = [
        'admin' => self::PERMISSIONS,
        'operator' => [
            'catalog.view',
            'catalog.create',
            'catalog.update',
            'inventory.view',
            'inventory.create',
            'inventory.update',
            'audit.view',
        ],
        'accounting' => [
            'finance.view',
            'finance.create',
            'finance.update',
            'inventory.view',
            'audit.view',
        ],
        'viewer' => [
            'catalog.view',
            'inventory.view',
            'finance.view',
            'audit.view',
        ],
    ];

    public function run(): void
    {
        $permissionRegistrar = app(PermissionRegistrar::class);
        $permissionRegistrar->forgetCachedPermissions();

        DB::transaction(function (): void {
            $this->seedPermissions();
            $this->seedRoles();
        });

        $permissionRegistrar->forgetCachedPermissions();
    }

    private function seedPermissions(): void
    {
        $timestamp = now();

        Permission::query()->upsert(
            array_map(static fn (string $name): array => [
                'name' => $name,
                'guard_name' => 'web',
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ], self::PERMISSIONS),
            ['name', 'guard_name'],
            ['updated_at']
        );
    }

    private function seedRoles(): void
    {
        foreach (self::ROLE_PERMISSIONS as $roleName => $permissions) {
            $role = Role::findOrCreate($roleName, 'web');
            $role->syncPermissions($permissions);
        }
    }
}
