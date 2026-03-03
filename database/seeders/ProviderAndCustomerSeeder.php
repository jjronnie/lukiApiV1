<?php

namespace Database\Seeders;

use App\Enums\ProviderVerificationStatus;
use App\Enums\RoleName;
use App\Models\ProviderProfile;
use App\Models\User;
use Illuminate\Database\Seeder;

class ProviderAndCustomerSeeder extends Seeder
{
    public function run(): void
    {
        $provider = User::query()->firstOrCreate(
            ['email' => 'provider@luki.test'],
            [
                'name' => 'Demo Provider',
                'password' => 'password',
                'email_verified_at' => now(),
            ]
        );

        if (! $provider->hasRole(RoleName::Provider->value)) {
            $provider->assignRole(RoleName::Provider->value);
        }

        ProviderProfile::query()->firstOrCreate(
            ['user_id' => $provider->id],
            [
                'provider_type' => 'individual',
                'display_name' => 'Demo Provider',
                'verification_status' => ProviderVerificationStatus::Approved->value,
            ]
        );

        $customer = User::query()->firstOrCreate(
            ['email' => 'customer@luki.test'],
            [
                'name' => 'Demo Customer',
                'password' => 'password',
                'email_verified_at' => now(),
            ]
        );

        if (! $customer->hasRole(RoleName::User->value)) {
            $customer->assignRole(RoleName::User->value);
        }
    }
}
