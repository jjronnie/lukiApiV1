<?php

namespace Database\Seeders;

use App\Models\ServiceCategory;
use Database\Seeders\Support\MarketplaceCatalog;
use Illuminate\Database\Seeder;

class ServiceCategorySeeder extends Seeder
{
    public function run(): void
    {
        foreach (MarketplaceCatalog::categories() as $index => $category) {
            ServiceCategory::query()->updateOrCreate(
                ['slug' => $category['slug']],
                [
                    'name' => $category['name'],
                    'icon_name' => $category['icon_name'],
                    'image_url' => $category['image_url'],
                    'is_featured' => $index < 10,
                    'is_active' => true,
                    'sort_order' => $index,
                ]
            );
        }
    }
}
