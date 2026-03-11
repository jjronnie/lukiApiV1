<?php

namespace Database\Seeders;

use App\Enums\RoleName;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permissions = [
            'manage service categories',
            'manage home adverts',
            'manage services',
            'manage pricing rules',
            'manage users',
            'verify providers',
            'review user identity verifications',
            'manage wallets',
            'manage commission rules',
            'resolve disputes',
        ];

        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission, 'web');
        }

        $superadminRole = Role::findOrCreate(RoleName::Superadmin->value, 'web');
        $adminRole = Role::findOrCreate(RoleName::Admin->value, 'web');
        Role::findOrCreate(RoleName::Provider->value, 'web');
        Role::findOrCreate(RoleName::User->value, 'web');

        $superadminRole->syncPermissions($permissions);
        $adminRole->syncPermissions($permissions);
    }
}
