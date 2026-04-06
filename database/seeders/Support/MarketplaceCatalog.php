<?php

namespace Database\Seeders\Support;

class MarketplaceCatalog
{
    /**
     * @return array<int, array<string, mixed>>
     */
    public static function categories(): array
    {
        return [
            [
                'name' => 'Beauty & Grooming',
                'slug' => 'beauty-grooming',
                'icon_name' => 'brush_2',
                'image_url' => 'https://images.unsplash.com/photo-1521590832167-7bcbfaa6381f?auto=format&fit=crop&w=900&q=80',
                'services' => [
                    ['name' => "Men's Haircut", 'base_price_amount' => 30000, 'duration_minutes' => 45, 'is_featured' => true],
                    ['name' => 'Signature Silk Press', 'base_price_amount' => 85000, 'duration_minutes' => 120, 'is_featured' => true],
                    ['name' => 'Bridal Makeup Session', 'base_price_amount' => 120000, 'duration_minutes' => 90, 'is_featured' => true],
                    ['name' => 'Luxury Gel Manicure', 'base_price_amount' => 35000, 'duration_minutes' => 60, 'is_featured' => true],
                ],
            ],
        ];
    }
}
