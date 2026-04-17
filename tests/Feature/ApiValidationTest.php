<?php

use App\Enums\RoleName;
use App\Models\Service;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Database\Seeders\ServiceSeeder;

it('validates auth register requires password confirmation', function () {
    $this->seed(RolesAndPermissionsSeeder::class);

    $response = $this->postJson('/api/v1/auth/register', [
        'app_type' => 'customer',
        'first_name' => 'Test',
        'last_name' => 'User',
        'email' => 'test-user@example.com',
        'password' => 'Password123',
    ]);

    $response
        ->assertStatus(422)
        ->assertJsonValidationErrors(['password']);
});

it('validates auth register requires app_type', function () {
    $this->seed(RolesAndPermissionsSeeder::class);

    $response = $this->postJson('/api/v1/auth/register', [
        'first_name' => 'Test',
        'last_name' => 'User',
        'email' => 'test-user@example.com',
        'password' => 'Password123',
        'password_confirmation' => 'Password123',
    ]);

    $response
        ->assertStatus(422)
        ->assertJsonValidationErrors(['app_type']);
});

it('validates auth login requires an identifier and password', function () {
    $response = $this->postJson('/api/v1/auth/login', [
        'app_type' => 'customer',
        'password' => 'Password123',
    ]);

    $response
        ->assertStatus(422)
        ->assertJsonValidationErrors(['email', 'phone']);
});

it('validates auth refresh requires refresh token', function () {
    $response = $this->postJson('/api/v1/auth/refresh', [
        'app_type' => 'customer',
    ]);

    $response
        ->assertStatus(422)
        ->assertJsonValidationErrors(['refresh_token']);
});

it('validates auth forgot password requires an identifier', function () {
    $response = $this->postJson('/api/v1/auth/password/forgot', [
        'app_type' => 'customer',
    ]);

    $response
        ->assertStatus(422)
        ->assertJsonValidationErrors(['email', 'phone']);
});

it('validates auth reset requires token and confirmation', function () {
    $response = $this->postJson('/api/v1/auth/password/reset', [
        'app_type' => 'customer',
        'email' => 'test-user@example.com',
        'password' => 'Password123',
    ]);

    $response
        ->assertStatus(422)
        ->assertJsonValidationErrors(['reset_token', 'otp_token', 'password']);
});

it('validates auth google login requires id token', function () {
    $response = $this->postJson('/api/v1/auth/google', [
        'app_type' => 'customer',
    ]);

    $response
        ->assertStatus(422)
        ->assertJsonValidationErrors(['id_token']);
});

it('validates auth logout payload types', function () {
    $this->seed(RolesAndPermissionsSeeder::class);

    $user = User::factory()->create();
    $user->assignRole(RoleName::User->value);

    $response = $this->actingAs($user, 'sanctum')->postJson('/api/v1/auth/logout', [
        'logout_all' => 'nope',
    ]);

    $response
        ->assertStatus(422)
        ->assertJsonValidationErrors(['logout_all']);
});

it('validates change password request requires current password', function () {
    $this->seed(RolesAndPermissionsSeeder::class);

    $user = User::factory()->create();
    $user->assignRole(RoleName::User->value);

    $response = $this->actingAs($user, 'sanctum')->postJson('/api/v1/auth/password/change/request', [
        'app_type' => 'customer',
    ]);

    $response
        ->assertStatus(422)
        ->assertJsonValidationErrors(['current_password']);
});

it('validates auth register otp verification fields', function () {
    $response = $this->postJson('/api/v1/auth/register/verify', []);

    $response
        ->assertStatus(422)
        ->assertJsonValidationErrors(['app_type', 'email', 'phone', 'otp_token', 'code']);
});

it('validates auth login otp verification fields', function () {
    $response = $this->postJson('/api/v1/auth/login/verify', []);

    $response
        ->assertStatus(422)
        ->assertJsonValidationErrors(['app_type', 'email', 'phone', 'otp_token', 'code']);
});

it('validates auth resend otp fields', function () {
    $response = $this->postJson('/api/v1/auth/otp/resend', []);

    $response
        ->assertStatus(422)
        ->assertJsonValidationErrors(['app_type', 'email', 'phone', 'otp_token', 'purpose']);
});

it('validates order creation required fields', function () {
    $this->seed(RolesAndPermissionsSeeder::class);

    $user = User::factory()->create();
    $user->assignRole(RoleName::User->value);

    $response = $this->actingAs($user, 'sanctum')->postJson('/api/v1/orders', []);

    $response
        ->assertStatus(422)
        ->assertJsonValidationErrors([
            'service_public_id',
            'service_tier_public_id',
            'is_scheduled',
            'address_text',
            'location_lat',
            'location_lng',
            'payment_method',
            'booking_mode',
        ]);
});

it('does not require payment method when the customer has a saved default', function () {
    $this->seed([RolesAndPermissionsSeeder::class, ServiceSeeder::class]);

    $user = User::factory()->create([
        'default_payment_method' => 'mtn',
    ]);
    $user->assignRole(RoleName::User->value);

    $service = Service::query()->firstOrFail();
    $tier = $service->tiers()->where('is_active', true)->firstOrFail();

    $response = $this->actingAs($user, 'sanctum')->postJson('/api/v1/orders', [
        'service_public_id' => $service->public_id,
        'service_tier_public_id' => $tier->public_id,
        'booking_mode' => 'normal',
        'is_scheduled' => false,
        'address_text' => 'Kampala Road',
        'location_lat' => 0.3476,
        'location_lng' => 32.5825,
    ]);

    $response
        ->assertCreated()
        ->assertJsonPath('order.payment.method', 'mtn');
});

it('validates order creation payment method enum', function () {
    $this->seed([RolesAndPermissionsSeeder::class, ServiceSeeder::class]);

    $user = User::factory()->create();
    $user->assignRole(RoleName::User->value);

    $service = Service::query()->firstOrFail();
    $tier = $service->tiers()->where('is_active', true)->firstOrFail();

    $response = $this->actingAs($user, 'sanctum')->postJson('/api/v1/orders', [
        'service_public_id' => $service->public_id,
        'service_tier_public_id' => $tier->public_id,
        'booking_mode' => 'normal',
        'is_scheduled' => false,
        'address_text' => 'Kampala Road',
        'location_lat' => 0.3476,
        'location_lng' => 32.5825,
        'payment_method' => 'mobile_money',
    ]);

    $response
        ->assertStatus(422)
        ->assertJsonValidationErrors(['payment_method']);
});

it('validates order cancel reason type', function () {
    $this->seed(RolesAndPermissionsSeeder::class);

    $user = User::factory()->create();
    $user->assignRole(RoleName::User->value);

    $response = $this->actingAs($user, 'sanctum')->postJson('/api/v1/orders/sample/cancel', [
        'reason' => ['not', 'a', 'string'],
    ]);

    $response
        ->assertStatus(422)
        ->assertJsonValidationErrors(['reason']);
});

it('validates order rating requires rating', function () {
    $this->seed(RolesAndPermissionsSeeder::class);

    $user = User::factory()->create();
    $user->assignRole(RoleName::User->value);

    $response = $this->actingAs($user, 'sanctum')->postJson('/api/v1/orders/sample/rate', []);

    $response
        ->assertStatus(422)
        ->assertJsonValidationErrors(['rating']);
});

it('validates provider profile requires provider type and display name', function () {
    $this->seed(RolesAndPermissionsSeeder::class);

    $user = User::factory()->create();
    $user->assignRole(RoleName::Provider->value);

    $response = $this->actingAs($user, 'sanctum')->postJson('/api/v1/provider/profile', []);

    $response
        ->assertStatus(422)
        ->assertJsonValidationErrors(['provider_type', 'display_name']);
});

it('validates customer profile completion fields', function () {
    $this->seed(RolesAndPermissionsSeeder::class);

    $user = User::factory()->create();
    $user->assignRole(RoleName::User->value);

    $response = $this->actingAs($user, 'sanctum')->postJson('/api/v1/customer/profile', []);

    $response
        ->assertStatus(422)
        ->assertJsonValidationErrors(['phone_country_code', 'phone_local_number']);
});

it('allows customer profile updates that only change the default payment method', function () {
    $this->seed(RolesAndPermissionsSeeder::class);

    $user = User::factory()->create([
        'phone' => '+256700000111',
        'phone_country_code' => '+256',
        'phone_local_number' => '700000111',
    ]);
    $user->assignRole(RoleName::User->value);

    $response = $this->actingAs($user, 'sanctum')->postJson('/api/v1/customer/profile', [
        'default_payment_method' => 'airtel',
    ]);

    $response
        ->assertOk()
        ->assertJsonPath('message', 'Profile updated successfully.')
        ->assertJsonPath('user.default_payment_method', 'airtel');
});

it('validates provider documents require file and document type', function () {
    $this->seed(RolesAndPermissionsSeeder::class);

    $user = User::factory()->create();
    $user->assignRole(RoleName::Provider->value);

    $response = $this->actingAs($user, 'sanctum')->postJson('/api/v1/provider/documents', []);

    $response
        ->assertStatus(422)
        ->assertJsonValidationErrors(['document_type', 'file']);
});

it('validates provider services require at least one service', function () {
    $this->seed(RolesAndPermissionsSeeder::class);

    $user = User::factory()->create();
    $user->assignRole(RoleName::Provider->value);

    $response = $this->actingAs($user, 'sanctum')->postJson('/api/v1/provider/services', [
        'service_public_ids' => [],
    ]);

    $response
        ->assertStatus(422)
        ->assertJsonValidationErrors(['service_public_ids']);
});

it('validates provider heartbeat requires coordinates', function () {
    $this->seed(RolesAndPermissionsSeeder::class);

    $user = User::factory()->create();
    $user->assignRole(RoleName::Provider->value);

    $response = $this->actingAs($user, 'sanctum')->postJson('/api/v1/provider/heartbeat', []);

    $response
        ->assertStatus(422)
        ->assertJsonValidationErrors(['lat', 'lng']);
});

it('validates provider order status requires allowed status', function () {
    $this->seed(RolesAndPermissionsSeeder::class);

    $user = User::factory()->create();
    $user->assignRole(RoleName::Provider->value);

    $response = $this->actingAs($user, 'sanctum')->postJson('/api/v1/provider/orders/sample/status', [
        'status' => 'unknown_status',
    ]);

    $response
        ->assertStatus(422)
        ->assertJsonValidationErrors(['status']);
});

it('validates disputes require core fields', function () {
    $this->seed(RolesAndPermissionsSeeder::class);

    $user = User::factory()->create();
    $user->assignRole(RoleName::User->value);

    $response = $this->actingAs($user, 'sanctum')->postJson('/api/v1/disputes', []);

    $response
        ->assertStatus(422)
        ->assertJsonValidationErrors(['order_public_id', 'category', 'description']);
});

it('validates price estimate requires service public id', function () {
    $response = $this->postJson('/api/v1/price/estimate', []);

    $response
        ->assertStatus(422)
        ->assertJsonValidationErrors(['service_public_id']);
});
