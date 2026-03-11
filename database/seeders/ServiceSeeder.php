<?php

namespace Database\Seeders;

use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\ServiceTier;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ServiceSeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Hair Styling',
                'slug' => 'hair-styling',
                'icon_name' => 'scissor',
                'image_url' => 'https://images.unsplash.com/photo-1521590832167-7bcbfaa6381f?auto=format&fit=crop&w=900&q=80',
                'sort_order' => 1,
            ],
            [
                'name' => 'Nails & Makeup',
                'slug' => 'nails-makeup',
                'icon_name' => 'brush_2',
                'image_url' => 'https://images.unsplash.com/photo-1487412947147-5cebf100ffc2?auto=format&fit=crop&w=900&q=80',
                'sort_order' => 2,
            ],
            [
                'name' => 'Barbering',
                'slug' => 'barbering',
                'icon_name' => 'profile_circle',
                'image_url' => 'https://images.unsplash.com/photo-1517832606299-7ae9b720a186?auto=format&fit=crop&w=900&q=80',
                'sort_order' => 3,
            ],
            [
                'name' => 'Braids & Natural Hair',
                'slug' => 'braids-natural-hair',
                'icon_name' => 'magicpen',
                'image_url' => 'https://images.unsplash.com/photo-1515377905703-c4788e51af15?auto=format&fit=crop&w=900&q=80',
                'sort_order' => 4,
            ],
            [
                'name' => 'Massage & Spa',
                'slug' => 'massage-spa',
                'icon_name' => 'heart',
                'image_url' => 'https://images.unsplash.com/photo-1519823551278-64ac92734fb1?auto=format&fit=crop&w=900&q=80',
                'sort_order' => 5,
            ],
        ];

        $categoryLookup = [];

        foreach ($categories as $category) {
            $record = ServiceCategory::query()->updateOrCreate(
                ['slug' => $category['slug']],
                [
                    'name' => $category['name'],
                    'icon_name' => $category['icon_name'],
                    'image_url' => $category['image_url'],
                    'is_active' => true,
                    'sort_order' => $category['sort_order'],
                ]
            );

            $categoryLookup[$category['slug']] = $record->id;
        }

        $services = [
            [
                'name' => 'Signature Silk Press',
                'icon_name' => 'scissor',
                'image_url' => 'https://images.unsplash.com/photo-1517832606299-7ae9b720a186?auto=format&fit=crop&w=900&q=80',
                'description' => 'Smooth wash, treatment, blow dry, and silk press for natural hair.',
                'category_slug' => 'hair-styling',
                'base_price_amount' => 85000,
                'duration_minutes' => 120,
                'sort_order' => 1,
                'is_featured' => true,
            ],
            [
                'name' => 'Bridal Makeup Session',
                'icon_name' => 'brush_2',
                'image_url' => 'https://images.unsplash.com/photo-1487412720507-e7ab37603c6f?auto=format&fit=crop&w=900&q=80',
                'description' => 'Soft glam or full glam bridal makeup with lashes.',
                'category_slug' => 'nails-makeup',
                'base_price_amount' => 120000,
                'duration_minutes' => 90,
                'sort_order' => 2,
                'is_featured' => true,
            ],
            [
                'name' => 'Executive Barber Cut',
                'icon_name' => 'profile_circle',
                'image_url' => 'https://images.unsplash.com/photo-1622287162716-f311baa1a2b8?auto=format&fit=crop&w=900&q=80',
                'description' => 'Precision cut, beard lining, and finishing spray.',
                'category_slug' => 'barbering',
                'base_price_amount' => 30000,
                'duration_minutes' => 45,
                'sort_order' => 3,
                'is_featured' => true,
            ],
            [
                'name' => 'Medium Knotless Braids',
                'icon_name' => 'magicpen',
                'image_url' => 'https://images.unsplash.com/photo-1595475038784-bbe439ff41e6?auto=format&fit=crop&w=900&q=80',
                'description' => 'Protective knotless braids with neat parting and finishing mousse.',
                'category_slug' => 'braids-natural-hair',
                'base_price_amount' => 160000,
                'duration_minutes' => 240,
                'sort_order' => 4,
                'is_featured' => true,
            ],
            [
                'name' => 'Luxury Gel Manicure',
                'icon_name' => 'brush_2',
                'image_url' => 'https://images.unsplash.com/photo-1604654894610-df63bc536371?auto=format&fit=crop&w=900&q=80',
                'description' => 'Cuticle care, gel polish, and hand massage.',
                'category_slug' => 'nails-makeup',
                'base_price_amount' => 35000,
                'duration_minutes' => 60,
                'sort_order' => 5,
                'is_featured' => false,
            ],
            [
                'name' => 'Therapeutic Body Massage',
                'icon_name' => 'heart',
                'image_url' => 'https://images.unsplash.com/photo-1544161515-4ab6ce6db874?auto=format&fit=crop&w=900&q=80',
                'description' => 'Relaxing body massage focused on stress relief and muscle recovery.',
                'category_slug' => 'massage-spa',
                'base_price_amount' => 95000,
                'duration_minutes' => 90,
                'sort_order' => 6,
                'is_featured' => false,
            ],
            [
                'name' => 'Children Hair Braiding',
                'icon_name' => 'magicpen',
                'image_url' => 'https://images.unsplash.com/photo-1515377905703-c4788e51af15?auto=format&fit=crop&w=900&q=80',
                'description' => 'Kid-friendly cornrows or braid styling.',
                'category_slug' => 'braids-natural-hair',
                'base_price_amount' => 55000,
                'duration_minutes' => 110,
                'sort_order' => 7,
                'is_featured' => false,
            ],
            [
                'name' => 'Classic Facial Grooming',
                'icon_name' => 'profile_circle',
                'image_url' => 'https://images.unsplash.com/photo-1512496015851-a90fb38ba796?auto=format&fit=crop&w=900&q=80',
                'description' => 'Beard treatment, facial steam, and hot towel finish.',
                'category_slug' => 'barbering',
                'base_price_amount' => 45000,
                'duration_minutes' => 50,
                'sort_order' => 8,
                'is_featured' => false,
            ],
        ];

        foreach ($services as $service) {
            $serviceRecord = Service::query()->updateOrCreate(
                ['slug' => Str::slug($service['name'])],
                [
                    'service_category_id' => $categoryLookup[$service['category_slug']],
                    'name' => $service['name'],
                    'icon_name' => $service['icon_name'],
                    'image_url' => $service['image_url'] ?? null,
                    'description' => $service['description'],
                    'currency' => 'UGX',
                    'base_price_amount' => $service['base_price_amount'],
                    'duration_minutes' => $service['duration_minutes'],
                    'is_active' => true,
                    'is_featured' => $service['is_featured'],
                    'sort_order' => $service['sort_order'],
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
