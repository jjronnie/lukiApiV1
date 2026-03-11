<?php

namespace Database\Seeders;

use App\Models\HomeAdvert;
use Illuminate\Database\Seeder;

class HomeAdvertSeeder extends Seeder
{
    public function run(): void
    {
        $adverts = [
            [
                'title' => 'Save time and money',
                'headline' => 'Book your next beauty session faster',
                'description' => 'Browse popular categories, compare tiers, and lock in your next appointment without the back-and-forth.',
                'button_text' => 'Explore services',
                'link_type' => 'internal',
                'link_target' => '/services',
                'image_url' => 'https://images.unsplash.com/photo-1524504388940-b1c1722653e1?auto=format&fit=crop&w=1400&q=80',
                'sort_order' => 1,
                'is_active' => true,
            ],
            [
                'title' => 'Need a trusted repeat provider?',
                'headline' => 'Use Pair booking',
                'description' => 'If you already know your preferred provider, use their provider number during booking and request them directly.',
                'button_text' => 'How it works',
                'link_type' => 'internal',
                'link_target' => '/services',
                'image_url' => 'https://images.unsplash.com/photo-1522337660859-02fbefca4702?auto=format&fit=crop&w=1400&q=80',
                'sort_order' => 2,
                'is_active' => true,
            ],
            [
                'title' => 'See what Luki is about',
                'headline' => 'Visit our website',
                'description' => 'Learn more about the platform, support channels, and what is new outside the app.',
                'button_text' => 'Open website',
                'link_type' => 'external',
                'link_target' => 'https://luki.ug',
                'image_url' => 'https://images.unsplash.com/photo-1487412720507-e7ab37603c6f?auto=format&fit=crop&w=1400&q=80',
                'sort_order' => 3,
                'is_active' => true,
            ],
        ];

        foreach ($adverts as $advert) {
            HomeAdvert::query()->updateOrCreate(
                ['title' => $advert['title']],
                $advert,
            );
        }
    }
}
