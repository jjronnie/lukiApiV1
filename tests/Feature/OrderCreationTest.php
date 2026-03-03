<?php

use App\Enums\RoleName;
use App\Models\Service;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Database\Seeders\ServiceSeeder;

it('creates an order for the authenticated user', function () {
    $this->seed([RolesAndPermissionsSeeder::class, ServiceSeeder::class]);

    $user = User::factory()->create();
    $user->assignRole(RoleName::User->value);

    $service = Service::query()->firstOrFail();

    $response = $this->actingAs($user, 'sanctum')->postJson('/api/v1/orders', [
        'service_public_id' => $service->public_id,
        'is_scheduled' => false,
        'address_text' => 'Kampala Road',
        'location_lat' => 0.3476,
        'location_lng' => 32.5825,
        'payment_method' => 'cash',
        'distance_km' => 3,
        'service_minutes' => 60,
    ]);

    $response
        ->assertCreated()
        ->assertJsonPath('order.status', 'offering');

    $this->assertDatabaseHas('orders', [
        'user_id' => $user->id,
        'status' => 'offering',
    ]);
});
