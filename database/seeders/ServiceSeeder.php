<?php

namespace Database\Seeders;

use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\ServiceTier;
use Database\Seeders\Support\MarketplaceCatalog;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ServiceSeeder extends Seeder
{
    public function run(): void
    {
        foreach (MarketplaceCatalog::categories() as $categoryIndex => $category) {
            $categoryRecord = ServiceCategory::query()->firstOrCreate(
                ['slug' => $category['slug']],
                [
                    'name' => $category['name'],
                    'icon_name' => $category['icon_name'],
                    'image_url' => $category['image_url'],
                    'is_featured' => $categoryIndex < 10,
                    'is_active' => true,
                    'sort_order' => $categoryIndex,
                ]
            );

            foreach ($category['services'] as $serviceIndex => $service) {
                $serviceRecord = Service::query()->updateOrCreate(
                    ['slug' => $service['slug'] ?? Str::slug($service['name'])],
                    [
                        'service_category_id' => $categoryRecord->id,
                        'name' => $service['name'],
                        'icon_name' => $service['icon_name'] ?? $category['icon_name'],
                        'image_url' => $service['image_url'] ?? $category['image_url'],
                        'description' => $service['description'] ?? sprintf(
                            'Professional %s delivered by vetted %s specialists.',
                            strtolower($service['name']),
                            strtolower($category['name'])
                        ),
                        'currency' => 'UGX',
                        'base_price_amount' => $service['base_price_amount'],
                        'duration_minutes' => $service['duration_minutes'],
                        'is_active' => true,
                        'is_featured' => $service['is_featured'] ?? false,
                        'sort_order' => $serviceIndex,
                    ]
                );

                $basePrice = (int) $service['base_price_amount'];
                $tiers = [
                    [
                        'name' => 'Saver',
                        'slug' => 'saver',
                        'price_amount' => (int) (round(($basePrice * 0.85) / 1000) * 1000),
                        'description' => 'Reliable essentials for customers who want the best entry price.',
                        'sort_order' => 1,
                    ],
                    [
                        'name' => 'Standard',
                        'slug' => 'standard',
                        'price_amount' => $basePrice,
                        'description' => 'Balanced quality, timing, and finish for everyday bookings.',
                        'sort_order' => 2,
                    ],
                    [
                        'name' => 'Premium',
                        'slug' => 'premium',
                        'price_amount' => (int) (round(($basePrice * 1.25) / 1000) * 1000),
                        'description' => 'Extended attention, enhanced finish, and premium experience.',
                        'sort_order' => 3,
                    ],
                ];

                foreach ($tiers as $tier) {
                    ServiceTier::query()->updateOrCreate(
                        [
                            'service_id' => $serviceRecord->id,
                            'slug' => $tier['slug'],
                        ],
                        [
                            'name' => $tier['name'],
                            'price_amount' => $tier['price_amount'],
                            'description' => $tier['description'],
                            'is_active' => true,
                            'sort_order' => $tier['sort_order'],
                        ]
                    );
                }
            }
        }
    }
}
