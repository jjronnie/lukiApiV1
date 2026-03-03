<?php

use App\Enums\RoleName;
use App\Models\ProviderProfile;
use App\Models\User;
use Database\Seeders\ProviderAndCustomerSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;

it('seeds one provider and one customer', function () {
    $this->seed([RolesAndPermissionsSeeder::class, ProviderAndCustomerSeeder::class]);
    $this->seed([ProviderAndCustomerSeeder::class]);

    $provider = User::query()->where('email', 'provider@luki.test')->first();
    $customer = User::query()->where('email', 'customer@luki.test')->first();

    expect($provider)->not->toBeNull();
    expect($customer)->not->toBeNull();
    expect($provider?->hasRole(RoleName::Provider->value))->toBeTrue();
    expect($customer?->hasRole(RoleName::User->value))->toBeTrue();
    expect(User::role(RoleName::Provider->value)->count())->toBe(1);
    expect(User::role(RoleName::User->value)->count())->toBe(1);
    expect(ProviderProfile::query()->where('user_id', $provider?->id)->count())->toBe(1);
});
