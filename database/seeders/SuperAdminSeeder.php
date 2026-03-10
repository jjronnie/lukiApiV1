<?php

namespace Database\Seeders;

use App\Enums\RoleName;
use App\Models\User;
use Illuminate\Database\Seeder;

class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        $superadmin = User::query()->updateOrCreate(
            ['email' => 'ronaldjjuuko7@gmail.com'],
            [
                'name' => 'Luki Superadmin',
                'password' => '88928892',
                'email_verified_at' => now(),
            ]
        );

        $superadmin->syncRoles([RoleName::Superadmin->value]);

        $admin = User::query()->updateOrCreate(
            ['email' => 'admin@luki.test'],
            [
                'name' => 'Luki Admin',
                'password' => '88928892',
                'email_verified_at' => now(),
            ]
        );

        $admin->syncRoles([RoleName::Admin->value]);
    }
}
