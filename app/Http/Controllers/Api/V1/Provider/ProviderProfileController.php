<?php

namespace App\Http\Controllers\Api\V1\Provider;

use App\Enums\ProviderVerificationStatus;
use App\Enums\WalletStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Provider\UpsertProviderProfileRequest;
use App\Models\ProviderProfile;
use App\Models\Service;
use App\Models\Wallet;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class ProviderProfileController extends Controller
{
    public function upsert(UpsertProviderProfileRequest $request): JsonResponse
    {
        $user = $request->user();
        $data = $request->validated();

        $profile = DB::transaction(function () use ($user, $data) {
            $profile = ProviderProfile::query()->updateOrCreate(
                ['user_id' => $user->id],
                [
                    'provider_type' => $data['provider_type'],
                    'display_name' => $data['display_name'],
                    'legal_name' => $data['legal_name'] ?? null,
                    'bio' => $data['bio'] ?? null,
                    'verification_status' => ProviderVerificationStatus::Pending,
                    'rejection_reason' => null,
                ]
            );

            $servicePublicIds = $data['service_public_ids'] ?? [];
            if ($servicePublicIds !== []) {
                $serviceIds = Service::query()->whereIn('public_id', $servicePublicIds)->pluck('id')->all();
                $profile->syncServiceEligibility($serviceIds);
            }

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

        return response()->json([
            'provider_profile' => [
                'public_id' => $profile->public_id,
                'provider_number' => $profile->provider_number,
                'display_name' => $profile->display_name,
                'avatar_url' => $profile->avatar_url,
                'verification_status' => $profile->verification_status?->value ?? $profile->verification_status,
            ],
        ]);
    }
}
