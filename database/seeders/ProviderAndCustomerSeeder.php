<?php

namespace Database\Seeders;

use App\Enums\ProviderVerificationStatus;
use App\Enums\RoleName;
use App\Enums\UserIdentityVerificationStatus;
use App\Enums\WalletStatus;
use App\Models\ProviderAvailability;
use App\Models\ProviderProfile;
use App\Models\Service;
use App\Models\User;
use App\Models\UserIdentityVerification;
use App\Models\Wallet;
use Illuminate\Database\Seeder;

class ProviderAndCustomerSeeder extends Seeder
{
    public function run(): void
    {
        $customers = [
            [
                'email' => 'customer@luki.test',
                'first_name' => 'Demo',
                'last_name' => 'Customer',
                'phone_country_code' => '+256',
                'phone_local_number' => '700111111',
                'heard_about_source' => 'friend',
                'profile_completed_at' => now(),
                'verification' => ['status' => UserIdentityVerificationStatus::Approved, 'id_type' => 'national_id', 'verified_at' => now()],
            ],
            [
                'email' => 'sarah.customer@luki.test',
                'first_name' => 'Sarah',
                'last_name' => 'Customer',
                'phone_country_code' => '+256',
                'phone_local_number' => '700222222',
                'heard_about_source' => 'social_media',
                'profile_completed_at' => now(),
                'verification' => ['status' => UserIdentityVerificationStatus::Pending, 'id_type' => 'passport', 'submitted_at' => now()],
            ],
            [
                'email' => 'james.customer@luki.test',
                'first_name' => 'James',
                'last_name' => 'Customer',
                'phone_country_code' => '+256',
                'phone_local_number' => '700333333',
                'heard_about_source' => 'other',
                'heard_about_other' => 'Event activation booth',
                'profile_completed_at' => now(),
                'verification' => [
                    'status' => UserIdentityVerificationStatus::Rejected,
                    'id_type' => 'drivers_license',
                    'submitted_at' => now()->subDays(3),
                    'reviewed_at' => now()->subDays(2),
                    'rejection_reason' => 'The uploaded images were blurry. Please retake them.',
                ],
            ],
        ];

        foreach ($customers as $customerData) {
            $customer = User::query()->updateOrCreate(
                ['email' => $customerData['email']],
                [
                    'first_name' => $customerData['first_name'],
                    'last_name' => $customerData['last_name'],
                    'name' => trim($customerData['first_name'].' '.$customerData['last_name']),
                    'password' => 'Password123',
                    'email_verified_at' => now(),
                    'phone_country_code' => $customerData['phone_country_code'],
                    'phone_local_number' => $customerData['phone_local_number'],
                    'phone' => $customerData['phone_country_code'].$customerData['phone_local_number'],
                    'heard_about_source' => $customerData['heard_about_source'] ?? null,
                    'heard_about_other' => $customerData['heard_about_other'] ?? null,
                    'profile_completed_at' => $customerData['profile_completed_at'],
                ]
            );

            $customer->syncRoles([RoleName::User->value]);

            UserIdentityVerification::query()->updateOrCreate(
                ['user_id' => $customer->id],
                [
                    'status' => $customerData['verification']['status'],
                    'id_type' => $customerData['verification']['id_type'],
                    'submitted_at' => $customerData['verification']['submitted_at'] ?? now()->subDay(),
                    'reviewed_at' => $customerData['verification']['reviewed_at'] ?? null,
                    'verified_at' => $customerData['verification']['verified_at'] ?? null,
                    'rejection_reason' => $customerData['verification']['rejection_reason'] ?? null,
                ]
            );
        }

        $providers = [
            [
                'email' => 'provider@luki.test',
                'first_name' => 'Demo',
                'last_name' => 'Provider',
                'profile' => [
                    'provider_type' => 'individual',
                    'display_name' => 'Luki Demo Provider',
                    'avatar_path' => 'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?auto=format&fit=crop&w=600&q=80',
                    'verification_status' => ProviderVerificationStatus::Approved,
                    'verified_at' => now(),
                ],
                'is_online' => true,
                'service_slugs' => ['signature-silk-press', 'bridal-makeup-session', 'luxury-gel-manicure'],
                'wallet_balance' => 185000,
            ],
            [
                'email' => 'stylist.provider@luki.test',
                'first_name' => 'Grace',
                'last_name' => 'Stylist',
                'profile' => [
                    'provider_type' => 'individual',
                    'display_name' => 'Grace Signature Styles',
                    'avatar_path' => 'https://images.unsplash.com/photo-1494790108377-be9c29b29330?auto=format&fit=crop&w=600&q=80',
                    'verification_status' => ProviderVerificationStatus::Pending,
                ],
                'is_online' => false,
                'service_slugs' => ['medium-knotless-braids', 'children-hair-braiding'],
                'wallet_balance' => 0,
            ],
            [
                'email' => 'spa.provider@luki.test',
                'first_name' => 'Calm',
                'last_name' => 'Spa Provider',
                'profile' => [
                    'provider_type' => 'business',
                    'display_name' => 'Calm Spa Studio',
                    'avatar_path' => 'https://images.unsplash.com/photo-1524504388940-b1c1722653e1?auto=format&fit=crop&w=600&q=80',
                    'verification_status' => ProviderVerificationStatus::Rejected,
                    'rejection_reason' => 'Business registration document was expired.',
                ],
                'is_online' => false,
                'service_slugs' => ['therapeutic-body-massage', 'bridal-makeup-session'],
                'wallet_balance' => 45000,
            ],
        ];

        foreach ($providers as $providerData) {
            $provider = User::query()->updateOrCreate(
                ['email' => $providerData['email']],
                [
                    'first_name' => $providerData['first_name'],
                    'last_name' => $providerData['last_name'],
                    'name' => trim($providerData['first_name'].' '.$providerData['last_name']),
                    'password' => 'Password123',
                    'email_verified_at' => now(),
                ]
            );

            $provider->syncRoles([RoleName::Provider->value]);

            $profile = ProviderProfile::query()->updateOrCreate(
                ['user_id' => $provider->id],
                [
                    'provider_type' => $providerData['profile']['provider_type'],
                    'display_name' => $providerData['profile']['display_name'],
                    'avatar_path' => $providerData['profile']['avatar_path'] ?? null,
                    'verification_status' => $providerData['profile']['verification_status'],
                    'verified_at' => $providerData['profile']['verified_at'] ?? null,
                    'rejection_reason' => $providerData['profile']['rejection_reason'] ?? null,
                ]
            );

            Wallet::query()->firstOrCreate(
                ['provider_profile_id' => $profile->id],
                [
                    'currency' => 'UGX',
                    'balance_amount' => $providerData['wallet_balance'],
                    'hold_amount' => 0,
                    'min_required_amount' => 0,
                    'status' => WalletStatus::Active,
                ]
            );

            ProviderAvailability::query()->updateOrCreate(
                ['provider_profile_id' => $profile->id],
                [
                    'is_online' => $providerData['is_online'],
                    'last_seen_at' => $providerData['is_online'] ? now() : now()->subMinutes(5),
                    'timezone' => 'Africa/Kampala',
                ]
            );

            $serviceIds = Service::query()
                ->whereIn('slug', $providerData['service_slugs'])
                ->pluck('id')
                ->all();

            $profile->syncServiceEligibility($serviceIds);
        }
    }
}
