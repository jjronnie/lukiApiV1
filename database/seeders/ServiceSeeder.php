<?php

namespace Database\Seeders;

use App\Models\Service;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ServiceSeeder extends Seeder
{
    public function run(): void
    {
        $services = [
            ['name' => 'Barbering', 'base_price_amount' => 20000, 'duration_minutes' => 45, 'sort_order' => 1],
            ['name' => 'Manicure', 'base_price_amount' => 18000, 'duration_minutes' => 40, 'sort_order' => 2],
            ['name' => 'Pedicure', 'base_price_amount' => 22000, 'duration_minutes' => 50, 'sort_order' => 3],
            ['name' => 'Hair Plaiting', 'base_price_amount' => 45000, 'duration_minutes' => 120, 'sort_order' => 4],
        ];

        foreach ($services as $service) {
            Service::query()->updateOrCreate(
                ['slug' => Str::slug($service['name'])],
                [
                    'name' => $service['name'],
                    'description' => $service['name'].' service',
                    'currency' => 'UGX',
                    'base_price_amount' => $service['base_price_amount'],
                    'duration_minutes' => $service['duration_minutes'],
                    'is_active' => true,
                    'sort_order' => $service['sort_order'],
                ]
            );
        }
    }
}
