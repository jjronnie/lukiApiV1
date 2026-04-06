<?php

namespace App\Http\Controllers\Api\V1\Provider;

use App\Enums\ProviderServiceApprovalStatus;
use App\Enums\ProviderVerificationStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Provider\ProviderHeartbeatRequest;
use App\Models\ProviderAvailability;
use Illuminate\Http\JsonResponse;

class ProviderAvailabilityController extends Controller
{
    public function online(): JsonResponse
    {
        $profile = auth()->user()->providerProfile()->with('wallet')->firstOrFail();

        if ($profile->onboarding_completed_at === null) {
            return response()->json(['message' => 'Complete onboarding before going online.'], 422);
        }

        if ($profile->verification_status !== ProviderVerificationStatus::Approved) {
            return response()->json(['message' => 'Provider is not approved.'], 422);
        }

        $hasApprovedServices = $profile->providerServices()
            ->where('is_active', true)
            ->where('approval_status', ProviderServiceApprovalStatus::Approved->value)
            ->exists();

        if (! $hasApprovedServices) {
            return response()->json(['message' => 'No approved services are available for dispatch yet.'], 422);
        }

        if ($profile->wallet !== null && ($profile->wallet->balance_amount - $profile->wallet->hold_amount) < $profile->wallet->min_required_amount) {
            return response()->json(['message' => 'Wallet balance is below minimum required amount.'], 422);
        }

        ProviderAvailability::query()->updateOrCreate(
            ['provider_profile_id' => $profile->id],
            [
                'is_online' => true,
                'last_seen_at' => now(),
                'timezone' => 'Africa/Kampala',
            ]
        );

        return response()->json(['message' => 'Provider is online.']);
    }

    public function offline(): JsonResponse
    {
        $profile = auth()->user()->providerProfile()->firstOrFail();

        ProviderAvailability::query()->updateOrCreate(
            ['provider_profile_id' => $profile->id],
            [
                'is_online' => false,
                'last_seen_at' => now(),
                'timezone' => 'Africa/Kampala',
            ]
        );

        return response()->json(['message' => 'Provider is offline.']);
    }

    public function heartbeat(ProviderHeartbeatRequest $request): JsonResponse
    {
        $profile = $request->user()->providerProfile()->firstOrFail();
        $data = $request->validated();

        $availability = ProviderAvailability::query()->firstOrNew([
            'provider_profile_id' => $profile->id,
        ]);

        $availability->fill([
            'last_seen_at' => now(),
            'timezone' => 'Africa/Kampala',
        ]);
        if (! $availability->exists) {
            $availability->is_online = false;
        }
        $availability->save();

        $profile->locations()->create([
            'lat' => $data['lat'],
            'lng' => $data['lng'],
            'accuracy_meters' => $data['accuracy_meters'] ?? null,
            'heading' => $data['heading'] ?? null,
            'speed' => $data['speed'] ?? null,
            'source' => $data['source'] ?? 'app',
            'recorded_at' => now(),
        ]);

        return response()->json(['message' => 'Heartbeat recorded.']);
    }
}
