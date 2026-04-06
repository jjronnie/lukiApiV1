<?php

namespace App\Support;

use App\Enums\ProviderVerificationStatus;
use App\Enums\VerificationSessionFlow;
use App\Enums\VerificationSessionStatus;
use App\Http\Resources\ProviderIdentityVerificationResource;
use App\Http\Resources\VerificationSessionResource;
use App\Models\User;
use App\Models\VerificationSession;

class ProviderIdentityVerificationStatusPresenter
{
    /**
     * @return array<string, mixed>
     */
    public function forUser(User $user): array
    {
        $this->expireStaleSessions($user->id);

        $verification = $user->relationLoaded('providerIdentityVerification')
            ? $user->providerIdentityVerification
            : $user->providerIdentityVerification()->first();

        $activeSession = VerificationSession::query()
            ->where('user_id', $user->id)
            ->where('flow', VerificationSessionFlow::ProviderIdentity)
            ->where('status', VerificationSessionStatus::Open)
            ->where('expires_at', '>', now())
            ->latest('created_at')
            ->first();

        $latestSession = VerificationSession::query()
            ->where('user_id', $user->id)
            ->where('flow', VerificationSessionFlow::ProviderIdentity)
            ->latest('created_at')
            ->first();

        $payload = $verification === null
            ? $this->defaultPayload()
            : (new ProviderIdentityVerificationResource($verification))->resolve();

        $reviewStatus = $verification?->status?->value ?? $verification?->status;

        $payload['review_status'] = $reviewStatus;
        $payload['active_session'] = $activeSession === null
            ? null
            : (new VerificationSessionResource($activeSession))->resolve();
        $payload['latest_session'] = $latestSession === null
            ? null
            : (new VerificationSessionResource($latestSession))->resolve();
        $payload['status'] = $this->displayStatus($reviewStatus);
        $payload['is_pending'] = $reviewStatus === ProviderVerificationStatus::Pending->value;
        $payload['is_verified'] = $reviewStatus === ProviderVerificationStatus::Approved->value;
        $payload['can_retry'] = ! in_array($reviewStatus, [
            ProviderVerificationStatus::Approved->value,
            ProviderVerificationStatus::Pending->value,
        ], true);

        return $payload;
    }

    private function expireStaleSessions(int $userId): void
    {
        VerificationSession::query()
            ->where('user_id', $userId)
            ->where('flow', VerificationSessionFlow::ProviderIdentity)
            ->where('status', VerificationSessionStatus::Open)
            ->where('expires_at', '<=', now())
            ->update([
                'status' => VerificationSessionStatus::Expired,
                'updated_at' => now(),
            ]);
    }

    private function displayStatus(?string $reviewStatus): string
    {
        if ($reviewStatus === ProviderVerificationStatus::Approved->value) {
            return 'verified';
        }

        if ($reviewStatus === ProviderVerificationStatus::Pending->value) {
            return 'pending';
        }

        return 'unverified';
    }

    /**
     * @return array<string, mixed>
     */
    private function defaultPayload(): array
    {
        return [
            'public_id' => null,
            'status' => 'unverified',
            'id_type' => null,
            'submitted_at' => null,
            'reviewed_at' => null,
            'verified_at' => null,
            'rejection_reason' => null,
            'is_pending' => false,
            'is_verified' => false,
            'can_retry' => true,
        ];
    }
}
