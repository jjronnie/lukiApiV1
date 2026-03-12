<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'public_id' => $this->public_id,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'name' => $this->name,
            'email' => $this->email,
            'signup_method' => $this->signup_method,
            'phone' => $this->phone,
            'phone_country_code' => $this->phone_country_code,
            'phone_local_number' => $this->phone_local_number,
            'email_verified_at' => $this->email_verified_at,
            'phone_verified_at' => $this->phone_verified_at,
            'profile_completed_at' => $this->profile_completed_at,
            'created_at' => $this->created_at,
            'roles' => $this->whenLoaded('roles', fn () => $this->roles->pluck('name')->values()),
            'provider_profile' => $this->whenLoaded('providerProfile', function () {
                if ($this->providerProfile === null) {
                    return null;
                }

                return [
                    'public_id' => $this->providerProfile->public_id,
                    'provider_number' => $this->providerProfile->provider_number,
                    'display_name' => $this->providerProfile->display_name,
                    'avatar_url' => $this->providerProfile->avatar_url,
                    'provider_type' => $this->providerProfile->provider_type,
                    'verification_status' => $this->providerProfile->verification_status?->value ?? $this->providerProfile->verification_status,
                    'rejection_reason' => $this->providerProfile->rejection_reason,
                    'rating' => $this->providerProfile->rating_avg,
                    'reviews_count' => $this->providerProfile->rating_count,
                    'offered_services' => $this->when(
                        $this->providerProfile->relationLoaded('providerServices'),
                        fn () => $this->providerProfile->providerServices->map(function ($providerService) {
                            return [
                                'service' => $providerService->relationLoaded('service') && $providerService->service !== null ? [
                                    'public_id' => $providerService->service->public_id,
                                    'name' => $providerService->service->name,
                                    'icon_name' => $providerService->service->icon_name,
                                    'category_name' => $providerService->service->category?->name,
                                ] : null,
                                'eligible_tiers' => $providerService->relationLoaded('eligibleTiers')
                                    ? ServiceTierResource::collection($providerService->eligibleTiers)->resolve()
                                    : [],
                            ];
                        })->values()
                    ),
                ];
            }),
            'identity_verification' => $this->whenLoaded(
                'identityVerification',
                fn () => $this->identityVerification === null ? null : new UserIdentityVerificationResource($this->identityVerification)
            ),
        ];
    }
}
