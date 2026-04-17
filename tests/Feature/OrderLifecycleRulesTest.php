<?php

use App\Enums\OrderStatus;
use App\Enums\RoleName;
use App\Models\Order;
use App\Models\OrderOffer;
use App\Models\ProviderProfile;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
});

it('shows the next provider actions for an ongoing order without repeating pickup', function () {
    $customer = createCustomer();
    ['user' => $providerUser, 'profile' => $providerProfile] = createProvider();

    $order = createOrder($customer, [
        'provider_profile_id' => $providerProfile->id,
        'status' => OrderStatus::OnTheWay,
        'accepted_at' => now()->subMinutes(12),
        'on_the_way_at' => now()->subMinutes(8),
    ]);

    $response = $this->actingAs($providerUser, 'sanctum')
        ->getJson('/api/v1/provider/orders/'.$order->public_id);

    $response->assertOk();

    expect($response->json('data.actions.provider_available_status_updates'))
        ->toBe([OrderStatus::Arrived->value]);
});

it('prevents a provider with an ongoing order from accepting another offer', function () {
    $customer = createCustomer();
    ['user' => $providerUser, 'profile' => $providerProfile] = createProvider();

    createOrder($customer, [
        'provider_profile_id' => $providerProfile->id,
        'status' => OrderStatus::Accepted,
        'accepted_at' => now()->subMinutes(5),
    ]);

    $offeredOrder = createOrder($customer, [
        'status' => OrderStatus::Offering,
        'offering_started_at' => now()->subMinute(),
    ]);

    createOffer($offeredOrder, $providerProfile);

    $response = $this->actingAs($providerUser, 'sanctum')
        ->postJson('/api/v1/provider/offers/'.$offeredOrder->public_id.'/accept', []);

    $response
        ->assertStatus(422)
        ->assertJsonPath('message', 'Finish your ongoing order before accepting a new one.');

    $this->assertDatabaseHas('orders', [
        'id' => $offeredOrder->id,
        'status' => OrderStatus::Offering->value,
        'provider_profile_id' => null,
    ]);
});

it('hides new provider offers while the provider already has an ongoing order', function () {
    $customer = createCustomer();
    ['user' => $providerUser, 'profile' => $providerProfile] = createProvider();

    createOrder($customer, [
        'provider_profile_id' => $providerProfile->id,
        'status' => OrderStatus::Accepted,
        'accepted_at' => now()->subMinutes(4),
    ]);

    $offeredOrder = createOrder($customer, [
        'status' => OrderStatus::Offering,
        'offering_started_at' => now()->subMinute(),
    ]);

    createOffer($offeredOrder, $providerProfile);

    $response = $this->actingAs($providerUser, 'sanctum')
        ->getJson('/api/v1/provider/offers');

    $response
        ->assertOk()
        ->assertJsonCount(0, 'data');
});

it('allows customer cancellation before the service starts', function (OrderStatus $status, array $attributes) {
    $customer = createCustomer();
    ['profile' => $providerProfile] = createProvider();

    $order = createOrder($customer, array_merge([
        'provider_profile_id' => $providerProfile->id,
        'status' => $status,
    ], $attributes));

    $showResponse = $this->actingAs($customer, 'sanctum')
        ->getJson('/api/v1/orders/'.$order->public_id);

    $showResponse
        ->assertOk()
        ->assertJsonPath('data.actions.customer_can_cancel', true);

    $cancelResponse = $this->actingAs($customer, 'sanctum')
        ->postJson('/api/v1/orders/'.$order->public_id.'/cancel', [
            'reason' => 'I changed my mind.',
        ]);

    $cancelResponse
        ->assertOk()
        ->assertJsonPath('message', 'Order cancelled.');

    $this->assertDatabaseHas('orders', [
        'id' => $order->id,
        'status' => OrderStatus::Cancelled->value,
    ]);
})->with([
    'on the way' => [
        OrderStatus::OnTheWay,
        [
            'accepted_at' => now()->subMinutes(12),
            'on_the_way_at' => now()->subMinutes(8),
        ],
    ],
    'arrived' => [
        OrderStatus::Arrived,
        [
            'accepted_at' => now()->subMinutes(14),
            'on_the_way_at' => now()->subMinutes(10),
            'arrived_at' => now()->subMinutes(2),
        ],
    ],
]);

it('allows customer cancellation after the service has started and without a reason', function () {
    $customer = createCustomer();
    ['profile' => $providerProfile] = createProvider();

    $order = createOrder($customer, [
        'provider_profile_id' => $providerProfile->id,
        'status' => OrderStatus::InService,
        'accepted_at' => now()->subMinutes(20),
        'on_the_way_at' => now()->subMinutes(16),
        'arrived_at' => now()->subMinutes(6),
        'in_service_at' => now()->subMinute(),
    ]);

    $showResponse = $this->actingAs($customer, 'sanctum')
        ->getJson('/api/v1/orders/'.$order->public_id);

    $showResponse
        ->assertOk()
        ->assertJsonPath('data.actions.customer_can_cancel', true);

    $cancelResponse = $this->actingAs($customer, 'sanctum')
        ->postJson('/api/v1/orders/'.$order->public_id.'/cancel', []);

    $cancelResponse
        ->assertOk()
        ->assertJsonPath('message', 'Order cancelled.');

    $this->assertDatabaseHas('orders', [
        'id' => $order->id,
        'status' => OrderStatus::Cancelled->value,
        'cancellation_reason' => null,
    ]);
});

it('marks completed rated orders as already rated in the order resource', function () {
    $customer = createCustomer();
    ['profile' => $providerProfile] = createProvider();

    $order = createOrder($customer, [
        'provider_profile_id' => $providerProfile->id,
        'status' => OrderStatus::Completed,
        'completed_at' => now()->subHour(),
        'provider_rating' => 5,
        'provider_review' => 'Excellent service.',
        'rated_at' => now()->subMinutes(30),
    ]);

    $response = $this->actingAs($customer, 'sanctum')
        ->getJson('/api/v1/orders/'.$order->public_id);

    $response
        ->assertOk()
        ->assertJsonPath('data.is_rated', true)
        ->assertJsonPath('data.rating.provider_rating', 5);
});

it('filters provider completed history to today only when requested', function () {
    $customer = createCustomer();
    ['user' => $providerUser, 'profile' => $providerProfile] = createProvider();
    $todayCompletedAt = now()->startOfDay()->addHours(10);
    $yesterdayCompletedAt = (clone $todayCompletedAt)->subDay();

    createOrder($customer, [
        'provider_profile_id' => $providerProfile->id,
        'status' => OrderStatus::Completed,
        'completed_at' => $todayCompletedAt,
    ]);

    createOrder($customer, [
        'provider_profile_id' => $providerProfile->id,
        'status' => OrderStatus::Completed,
        'completed_at' => $yesterdayCompletedAt,
        'created_at' => $yesterdayCompletedAt,
        'updated_at' => $yesterdayCompletedAt,
    ]);

    $response = $this->actingAs($providerUser, 'sanctum')
        ->getJson('/api/v1/provider/orders?scope=completed&today_only=1');

    $response
        ->assertOk()
        ->assertJsonCount(1, 'data');
});

function createCustomer(): User
{
    $customer = User::factory()->create();
    $customer->assignRole(RoleName::User->value);

    return $customer;
}

/**
 * @return array{user: User, profile: ProviderProfile}
 */
function createProvider(string $displayName = 'Provider'): array
{
    $providerUser = User::factory()->create();
    $providerUser->assignRole(RoleName::Provider->value);

    $providerProfile = ProviderProfile::query()->create([
        'user_id' => $providerUser->id,
        'provider_type' => 'individual',
        'display_name' => $displayName,
        'verification_status' => 'approved',
        'onboarding_completed_at' => now(),
    ]);

    return [
        'user' => $providerUser,
        'profile' => $providerProfile,
    ];
}

function createOrder(User $customer, array $attributes = []): Order
{
    return Order::query()->create(array_merge([
        'user_id' => $customer->id,
        'status' => OrderStatus::Created,
        'is_scheduled' => false,
        'address_text' => 'Kampala Road',
        'location_lat' => 0.3476,
        'location_lng' => 32.5825,
        'payment_method' => 'cash',
        'payment_status' => 'unpaid',
        'price_breakdown' => [
            'currency' => 'UGX',
            'total_amount' => 10000,
        ],
        'subtotal_amount' => 10000,
        'distance_fee_amount' => 0,
        'overtime_fee_amount' => 0,
        'peak_fee_amount' => 0,
        'tax_amount' => 0,
        'discount_amount' => 0,
        'total_amount' => 10000,
    ], $attributes));
}

function createOffer(Order $order, ProviderProfile $providerProfile): OrderOffer
{
    return OrderOffer::query()->create([
        'order_id' => $order->id,
        'provider_profile_id' => $providerProfile->id,
        'batch_no' => 1,
        'status' => 'pending',
        'expires_at' => now()->addMinute(),
        'created_at' => now(),
    ]);
}
