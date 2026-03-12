<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RolesAndPermissionsSeeder::class,
            SuperAdminSeeder::class,
            ServiceCategorySeeder::class,
            ServiceSeeder::class,
            TransportZoneSeeder::class,
            HomeAdvertSeeder::class,
            ProviderAndCustomerSeeder::class,
        ]);
    }
}
