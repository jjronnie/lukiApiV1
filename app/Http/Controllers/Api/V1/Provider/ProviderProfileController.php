<?php

namespace App\Http\Controllers\Api\V1\Provider;

use App\Enums\ProviderVerificationStatus;
use App\Enums\WalletStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Provider\UpsertProviderProfileRequest;
use App\Http\Resources\UserResource;
use App\Models\ProviderProfile;
use App\Models\Wallet;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class ProviderProfileController extends Controller
{
    public function upsert(UpsertProviderProfileRequest $request): JsonResponse
    {
        $user = $request->user();
        $data = $request->validated();

        if ($data['provider_type'] === 'business') {
            if (blank($data['business_name'] ?? null) || blank($data['business_address'] ?? null)) {
                throw ValidationException::withMessages([
                    'business_name' => ['Business name and business address are required for a company profile.'],
                ]);
            }
        }

        $profile = DB::transaction(function () use ($user, $data) {
            $profile = ProviderProfile::query()->firstOrNew(['user_id' => $user->id]);
            $displayName = trim((string) ($data['display_name']
                ?? ($data['provider_type'] === 'business'
                    ? ($data['business_name'] ?? null)
                    : $user->name)));

            if (($profile->verification_status?->value ?? $profile->verification_status) === ProviderVerificationStatus::Approved->value
                && ($data['avatar'] ?? null) !== null) {
                throw ValidationException::withMessages([
                    'avatar' => ['Profile photo changes are locked after verification approval.'],
                ]);
            }

            $profile->fill([
                'provider_type' => $data['provider_type'],
                'display_name' => $displayName !== '' ? $displayName : $user->email,
                'legal_name' => $data['legal_name'] ?? null,
                'bio' => $data['bio'] ?? null,
                'phone' => $data['phone'],
                'address_text' => $data['address_text'],
                'business_name' => $data['provider_type'] === 'business' ? ($data['business_name'] ?? null) : null,
                'business_address' => $data['provider_type'] === 'business' ? ($data['business_address'] ?? null) : null,
                'verification_status' => $profile->exists
                    ? ($profile->verification_status ?? ProviderVerificationStatus::Pending)
                    : ProviderVerificationStatus::Pending,
                'rejection_reason' => ($profile->verification_status?->value ?? $profile->verification_status) === ProviderVerificationStatus::Rejected->value
                    ? $profile->rejection_reason
                    : null,
                'onboarding_completed_at' => now(),
            ]);

            if (($data['avatar'] ?? null) !== null) {
                if (filled($profile->avatar_path)) {
                    Storage::disk('public')->delete($profile->avatar_path);
                }

                $profile->avatar_path = $data['avatar']->store('provider-avatars', 'public');
            }

            $profile->save();

            Wallet::query()->firstOrCreate(
                ['provider_profile_id' => $profile->id],
                [
                    'currency' => 'UGX',
                    'balance_amount' => 0,
                    'hold_amount' => 0,
                    'min_required_amount' => 0,
                    'status' => WalletStatus::Active,
                ]
            );

            return $profile;
        });

        $profile->load([
            'availability',
            'providerServices.service.category',
            'providerServices.eligibleTiers',
        ]);

        return response()->json([
            'message' => 'Provider profile updated.',
            'user' => new UserResource($user->fresh([
                'roles',
                'providerProfile.availability',
                'providerProfile.providerServices.service.category',
                'providerProfile.providerServices.eligibleTiers',
                'providerIdentityVerification',
                'emailPreference',
            ])),
        ]);
    }
}
