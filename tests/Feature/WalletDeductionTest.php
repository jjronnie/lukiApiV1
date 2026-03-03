<?php

use App\Enums\RoleName;
use App\Models\CommissionRule;
use App\Models\Order;
use App\Models\ProviderAvailability;
use App\Models\ProviderProfile;
use App\Models\ProviderService;
use App\Models\Service;
use App\Models\User;
use App\Models\Wallet;
use Database\Seeders\RolesAndPermissionsSeeder;
use Database\Seeders\ServiceSeeder;

it('deducts provider wallet commission when order is completed', function () {
    $this->seed([RolesAndPermissionsSeeder::class, ServiceSeeder::class]);

    $service = Service::query()->firstOrFail();

    $customer = User::factory()->create();
    $customer->assignRole(RoleName::User->value);

    $providerUser = User::factory()->create();
    $providerUser->assignRole(RoleName::Provider->value);

    $profile = ProviderProfile::query()->create([
        'user_id' => $providerUser->id,
        'provider_type' => 'individual',
        'display_name' => 'Wallet Provider',
        'verification_status' => 'approved',
    ]);

    ProviderService::query()->create(['provider_profile_id' => $profile->id, 'service_id' => $service->id, 'is_active' => true]);
    ProviderAvailability::query()->create(['provider_profile_id' => $profile->id, 'is_online' => true, 'last_seen_at' => now(), 'timezone' => 'Africa/Kampala']);

    $wallet = Wallet::query()->create([
        'provider_profile_id' => $profile->id,
        'currency' => 'UGX',
        'balance_amount' => 10000,
        'hold_amount' => 0,
        'min_required_amount' => 0,
        'status' => 'active',
    ]);

    CommissionRule::query()->create([
        'service_id' => $service->id,
        'commission_type' => 'fixed',
        'value' => 3000,
        'is_active' => true,
    ]);

    $order = Order::query()->create([
        'user_id' => $customer->id,
        'provider_profile_id' => $profile->id,
        'status' => 'in_service',
        'is_scheduled' => false,
        'address_text' => 'Main Street',
        'location_lat' => 0.3476,
        'location_lng' => 32.5825,
        'payment_method' => 'cash',
        'payment_status' => 'unpaid',
        'price_breakdown' => ['total_amount' => 30000],
        'subtotal_amount' => 30000,
        'distance_fee_amount' => 0,
        'overtime_fee_amount' => 0,
        'peak_fee_amount' => 0,
        'tax_amount' => 0,
        'discount_amount' => 0,
        'total_amount' => 30000,
        'in_service_at' => now(),
    ]);

    $order->items()->create([
        'item_type' => 'service',
        'service_id' => $service->id,
        'name_snapshot' => $service->name,
        'unit_price_amount' => 30000,
        'quantity' => 1,
        'line_total_amount' => 30000,
    ]);

    $response = $this->actingAs($providerUser, 'sanctum')
        ->postJson('/api/v1/provider/orders/'.$order->public_id.'/status', [
            'status' => 'completed',
            'mark_paid' => true,
        ]);

    $response->assertSuccessful();

    $wallet->refresh();
    expect($wallet->balance_amount)->toBe(7000);

    $this->assertDatabaseHas('wallet_transactions', [
        'wallet_id' => $wallet->id,
        'type' => 'commission_deduction',
        'amount' => -3000,
    ]);
});
