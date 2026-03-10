<?php

use App\Enums\RoleName;
use App\Models\ServiceCategory;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;

it('creates a service category from the admin portal', function () {
    $this->seed([RolesAndPermissionsSeeder::class]);

    $admin = User::factory()->create();
    $admin->assignRole(RoleName::Admin->value);

    $response = $this->actingAs($admin)->post(route('admin.service-categories.store'), [
        'name' => 'Mobile Grooming',
        'slug' => 'mobile-grooming',
        'icon_name' => 'category',
        'is_active' => true,
        'sort_order' => 2,
    ]);

    $response->assertRedirect(route('admin.service-categories.index'));

    expect(ServiceCategory::query()->where('slug', 'mobile-grooming')->exists())->toBeTrue();
});
