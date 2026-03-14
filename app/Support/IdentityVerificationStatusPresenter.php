<?php

namespace App\Support;

use App\Enums\UserIdentityVerificationStatus;
use App\Enums\VerificationSessionFlow;
use App\Enums\VerificationSessionStatus;
use App\Http\Resources\UserIdentityVerificationResource;
use App\Http\Resources\VerificationSessionResource;
use App\Models\User;
use App\Models\VerificationSession;

class IdentityVerificationStatusPresenter
{
    /**
     * @return array<string, mixed>
     */
    public function forUser(User $user): array
    {
        $this->expireStaleSessions($user->id);

        $verification = $user->relationLoaded('identityVerification')
            ? $user->identityVerification
            : $user->identityVerification()->first();

        $activeSession = VerificationSession::query()
            ->where('user_id', $user->id)
            ->where('flow', VerificationSessionFlow::CustomerIdentity)
            ->where('status', VerificationSessionStatus::Open)
            ->where('expires_at', '>', now())
            ->latest('created_at')
            ->first();

        $latestSession = VerificationSession::query()
            ->where('user_id', $user->id)
            ->where('flow', VerificationSessionFlow::CustomerIdentity)
            ->latest('created_at')
            ->first();

        $payload = $verification === null
            ? $this->defaultPayload()
            : (new UserIdentityVerificationResource($verification))->resolve();

        $reviewStatus = $verification?->status?->value ?? $verification?->status;

        $payload['review_status'] = $reviewStatus;
        $payload['active_session'] = $activeSession === null
            ? null
            : (new VerificationSessionResource($activeSession))->resolve();
        $payload['latest_session'] = $latestSession === null
            ? null
            : (new VerificationSessionResource($latestSession))->resolve();
        $payload['status'] = $this->displayStatus($reviewStatus, $activeSession, $latestSession);
        $payload['is_pending'] = $reviewStatus === UserIdentityVerificationStatus::Pending->value;
        $payload['is_verified'] = $reviewStatus === UserIdentityVerificationStatus::Approved->value;
        $payload['can_retry'] = ! in_array($reviewStatus, [
            UserIdentityVerificationStatus::Approved->value,
            UserIdentityVerificationStatus::Pending->value,
        ], true);

        return $payload;
    }

    private function expireStaleSessions(int $userId): void
    {
        VerificationSession::query()
            ->where('user_id', $userId)
            ->where('flow', VerificationSessionFlow::CustomerIdentity)
            ->where('status', VerificationSessionStatus::Open)
            ->where('expires_at', '<=', now())
            ->update([
                'status' => VerificationSessionStatus::Expired,
                'updated_at' => now(),
            ]);
    }

    private function displayStatus(
        ?string $reviewStatus,
        ?VerificationSession $activeSession,
        ?VerificationSession $latestSession,
    ): string {
        if ($reviewStatus === UserIdentityVerificationStatus::Approved->value) {
            return 'verified';
        }

        if ($reviewStatus === UserIdentityVerificationStatus::Pending->value) {
            return 'pending';
        }

        if ($activeSession !== null) {
            return 'open_session';
        }

        if ($reviewStatus === UserIdentityVerificationStatus::Rejected->value) {
            return 'rejected';
        }

        if ($latestSession?->status === VerificationSessionStatus::Expired) {
            return 'expired_session';
        }

        return 'not_started';
    }

    /**
     * @return array<string, mixed>
     */
    private function defaultPayload(): array
    {
        return [
            'public_id' => null,
            'status' => 'not_started',
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
