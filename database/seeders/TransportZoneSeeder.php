<?php

namespace Database\Seeders;

use App\Models\TransportZone;
use Illuminate\Database\Seeder;

class TransportZoneSeeder extends Seeder
{
    public function run(): void
    {
        $zones = [
            [
                'name' => 'Kampala',
                'slug' => 'kampala',
                'center_lat' => 0.3476,
                'center_lng' => 32.5825,
                'radius_km' => 14,
                'fee_amount' => 5000,
                'is_active' => true,
                'is_fallback' => false,
                'sort_order' => 0,
            ],
            [
                'name' => 'Greater Kampala',
                'slug' => 'greater-kampala',
                'center_lat' => 0.3730,
                'center_lng' => 32.6200,
                'radius_km' => 28,
                'fee_amount' => 10000,
                'is_active' => true,
                'is_fallback' => false,
                'sort_order' => 1,
            ],
            [
                'name' => 'Outside Coverage',
                'slug' => 'outside-coverage',
                'center_lat' => null,
                'center_lng' => null,
                'radius_km' => null,
                'fee_amount' => 10000,
                'is_active' => true,
                'is_fallback' => true,
                'sort_order' => 99,
            ],
        ];

        foreach ($zones as $zone) {
            TransportZone::query()->updateOrCreate(
                ['slug' => $zone['slug']],
                $zone,
            );
        }
    }
}
