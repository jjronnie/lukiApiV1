<?php

namespace Database\Seeders;

use App\Enums\RoleName;
use App\Models\User;
use Illuminate\Database\Seeder;

class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::query()->firstOrCreate(
            ['email' => 'ronaldjjuuko7@gmail.com'],
            [
                'name' => 'Super Admin',
                'password' => '88928892',
                'email_verified_at' => now(),
            ]
        );

        if (! $user->hasRole(RoleName::Superadmin->value)) {
            $user->assignRole(RoleName::Superadmin->value);
        }
    }
}
