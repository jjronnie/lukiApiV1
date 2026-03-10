<?php

use App\Enums\RoleName;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Support\Facades\Hash;

it('rejects provider accounts on the customer app', function () {
    $this->seed(RolesAndPermissionsSeeder::class);

    $provider = User::factory()->create([
        'password' => Hash::make('Password123'),
    ]);
    $provider->assignRole(RoleName::Provider->value);

    $response = $this->postJson('/api/v1/auth/login', [
        'app_type' => 'customer',
        'email' => $provider->email,
        'password' => 'Password123',
    ]);

    $response
        ->assertStatus(403)
        ->assertJson(['message' => 'This account can only sign in on the provider app.']);
});

it('rejects customer accounts on the provider app', function () {
    $this->seed(RolesAndPermissionsSeeder::class);

    $customer = User::factory()->create([
        'password' => Hash::make('Password123'),
    ]);
    $customer->assignRole(RoleName::User->value);

    $response = $this->postJson('/api/v1/auth/login', [
        'app_type' => 'provider',
        'email' => $customer->email,
        'password' => 'Password123',
    ]);

    $response
        ->assertStatus(403)
        ->assertJson(['message' => 'This account can only sign in on the customer app.']);
});
