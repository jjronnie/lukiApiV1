<?php

use App\Enums\RoleName;
use App\Models\Order;
use App\Models\OrderOffer;
use App\Models\ProviderAvailability;
use App\Models\ProviderLocation;
use App\Models\ProviderProfile;
use App\Models\ProviderService;
use App\Models\Service;
use App\Models\User;
use App\Models\Wallet;
use Database\Seeders\RolesAndPermissionsSeeder;
use Database\Seeders\ServiceSeeder;

it('prevents a second provider from accepting an already accepted offer', function () {
    $this->seed([RolesAndPermissionsSeeder::class, ServiceSeeder::class]);

    $service = Service::query()->firstOrFail();

    $customer = User::factory()->create();
    $customer->assignRole(RoleName::User->value);

    $providerUserA = User::factory()->create();
    $providerUserA->assignRole(RoleName::Provider->value);

    $providerUserB = User::factory()->create();
    $providerUserB->assignRole(RoleName::Provider->value);

    $profileA = ProviderProfile::query()->create([
        'user_id' => $providerUserA->id,
        'provider_type' => 'individual',
        'display_name' => 'Provider A',
        'verification_status' => 'approved',
    ]);

    $profileB = ProviderProfile::query()->create([
        'user_id' => $providerUserB->id,
        'provider_type' => 'individual',
        'display_name' => 'Provider B',
        'verification_status' => 'approved',
    ]);

    ProviderService::query()->create(['provider_profile_id' => $profileA->id, 'service_id' => $service->id, 'is_active' => true]);
    ProviderService::query()->create(['provider_profile_id' => $profileB->id, 'service_id' => $service->id, 'is_active' => true]);

    ProviderAvailability::query()->create(['provider_profile_id' => $profileA->id, 'is_online' => true, 'last_seen_at' => now(), 'timezone' => 'Africa/Kampala']);
    ProviderAvailability::query()->create(['provider_profile_id' => $profileB->id, 'is_online' => true, 'last_seen_at' => now(), 'timezone' => 'Africa/Kampala']);

    ProviderLocation::query()->create(['provider_profile_id' => $profileA->id, 'lat' => 0.3476, 'lng' => 32.5825, 'source' => 'app', 'recorded_at' => now()]);
    ProviderLocation::query()->create(['provider_profile_id' => $profileB->id, 'lat' => 0.35, 'lng' => 32.58, 'source' => 'app', 'recorded_at' => now()]);

    Wallet::query()->create(['provider_profile_id' => $profileA->id, 'currency' => 'UGX', 'balance_amount' => 0, 'hold_amount' => 0, 'min_required_amount' => 0, 'status' => 'active']);
    Wallet::query()->create(['provider_profile_id' => $profileB->id, 'currency' => 'UGX', 'balance_amount' => 0, 'hold_amount' => 0, 'min_required_amount' => 0, 'status' => 'active']);

    $order = Order::query()->create([
        'user_id' => $customer->id,
        'status' => 'offering',
        'is_scheduled' => false,
        'address_text' => 'Main Street',
        'location_lat' => 0.3476,
        'location_lng' => 32.5825,
        'payment_method' => 'cash',
        'payment_status' => 'unpaid',
        'price_breakdown' => ['total_amount' => 10000],
        'subtotal_amount' => 10000,
        'distance_fee_amount' => 0,
        'overtime_fee_amount' => 0,
        'peak_fee_amount' => 0,
        'tax_amount' => 0,
        'discount_amount' => 0,
        'total_amount' => 10000,
    ]);

    $order->items()->create([
        'item_type' => 'service',
        'service_id' => $service->id,
        'name_snapshot' => $service->name,
        'unit_price_amount' => 10000,
        'quantity' => 1,
        'line_total_amount' => 10000,
    ]);

    OrderOffer::query()->create([
        'order_id' => $order->id,
        'provider_profile_id' => $profileA->id,
        'batch_no' => 1,
        'status' => 'pending',
        'expires_at' => now()->addMinutes(1),
        'created_at' => now(),
    ]);

    OrderOffer::query()->create([
        'order_id' => $order->id,
        'provider_profile_id' => $profileB->id,
        'batch_no' => 1,
        'status' => 'pending',
        'expires_at' => now()->addMinutes(1),
        'created_at' => now(),
    ]);

    $acceptA = $this->actingAs($providerUserA, 'sanctum')
        ->postJson('/api/v1/provider/offers/'.$order->public_id.'/accept', []);

    $acceptA->assertSuccessful();

    $acceptB = $this->actingAs($providerUserB, 'sanctum')
        ->postJson('/api/v1/provider/offers/'.$order->public_id.'/accept', []);

    $acceptB->assertStatus(422);

    $this->assertDatabaseHas('orders', [
        'id' => $order->id,
        'provider_profile_id' => $profileA->id,
        'status' => 'accepted',
    ]);
});
