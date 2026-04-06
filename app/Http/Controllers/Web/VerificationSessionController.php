<?php

namespace App\Http\Controllers\Web;

use App\Enums\ProviderVerificationStatus;
use App\Enums\UserIdentityVerificationStatus;
use App\Enums\VerificationSessionFlow;
use App\Enums\VerificationSessionStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Web\SubmitVerificationSessionRequest;
use App\Models\ProviderIdentityVerification;
use App\Models\UserIdentityVerification;
use App\Models\VerificationSession;
use App\Services\ProviderIdentityVerificationSubmissionService;
use App\Services\UserIdentityVerificationSubmissionService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class VerificationSessionController extends Controller
{
    public function __construct(
        private readonly UserIdentityVerificationSubmissionService $submissionService,
        private readonly ProviderIdentityVerificationSubmissionService $providerSubmissionService,
    ) {}

    public function show(Request $request, VerificationSession $session): Response
    {
        $currentVerification = $session->flow === VerificationSessionFlow::ProviderIdentity
            ? $session->user()->with('providerIdentityVerification')->first()?->providerIdentityVerification
            : $session->user()->with('identityVerification')->first()?->identityVerification;
        $blockedResponse = $this->resolveBlockedState($session, $currentVerification);

        if (! $this->hasValidSessionSignature($request, $session)) {
            if ($blockedResponse !== null && $this->canAccessClosedSessionResult($request, $session, $currentVerification)) {
                return $blockedResponse;
            }

            if ($this->hasExpiredLink($request, $session)) {
                return $this->expiredLinkResponse();
            }

            return $this->invalidLinkResponse();
        }

        if ($blockedResponse !== null) {
            return $blockedResponse;
        }

        $session->forceFill([
            'started_at' => $session->started_at ?? now(),
            'ip_address' => $request->ip(),
            'user_agent' => (string) $request->userAgent(),
        ])->save();

        return response()->view('verification.form', [
            'session' => $session,
            'expiresAt' => $session->expires_at,
            'submitUrl' => $request->fullUrl(),
            'isProviderFlow' => $session->flow === VerificationSessionFlow::ProviderIdentity,
        ]);
    }

    public function store(
        SubmitVerificationSessionRequest $request,
        VerificationSession $session,
    ): Response {
        $currentVerification = $session->flow === VerificationSessionFlow::ProviderIdentity
            ? $session->user()->with('providerIdentityVerification')->first()?->providerIdentityVerification
            : $session->user()->with('identityVerification')->first()?->identityVerification;
        $blockedResponse = $this->resolveBlockedState($session, $currentVerification);

        if (! $this->hasValidSessionSignature($request, $session)) {
            if ($blockedResponse !== null && $this->canAccessClosedSessionResult($request, $session, $currentVerification)) {
                return $blockedResponse;
            }

            if ($this->hasExpiredLink($request, $session)) {
                return $this->expiredLinkResponse();
            }

            return $this->invalidLinkResponse();
        }

        if ($blockedResponse !== null) {
            return $blockedResponse;
        }

        if ($session->flow === VerificationSessionFlow::ProviderIdentity) {
            $this->providerSubmissionService->submit($session->user, $request->validated());
        } else {
            $this->submissionService->submit($session->user, $request->validated());
        }

        $session->forceFill([
            'status' => VerificationSessionStatus::Submitted,
            'submitted_at' => now(),
            'ip_address' => $request->ip(),
            'user_agent' => (string) $request->userAgent(),
        ])->save();

        return response()->view('verification.result', [
            'title' => 'Documents uploaded successfully for review',
            'message' => 'Please await notification when reviewed.',
            'tone' => 'success',
        ]);
    }

    private function hasValidSessionSignature(Request $request, VerificationSession $session): bool
    {
        return $request->hasValidSignature() && $this->matchesSessionKey($request, $session);
    }

    private function hasExpiredLink(Request $request, VerificationSession $session): bool
    {
        $expiresAt = (int) $request->query('expires', 0);

        return $session->hasExpired()
            || $session->status === VerificationSessionStatus::Expired
            || ($expiresAt > 0 && now()->timestamp > $expiresAt);
    }

    private function matchesSessionKey(Request $request, VerificationSession $session): bool
    {
        return hash_equals($session->session_key, (string) $request->query('session_key', ''));
    }

    private function canAccessClosedSessionResult(
        Request $request,
        VerificationSession $session,
        UserIdentityVerification|ProviderIdentityVerification|null $currentVerification,
    ): bool {
        if (! $this->matchesSessionKey($request, $session)) {
            return false;
        }

        $approvedStatus = $session->flow === VerificationSessionFlow::ProviderIdentity
            ? ProviderVerificationStatus::Approved->value
            : UserIdentityVerificationStatus::Approved->value;
        $pendingStatus = $session->flow === VerificationSessionFlow::ProviderIdentity
            ? ProviderVerificationStatus::Pending->value
            : UserIdentityVerificationStatus::Pending->value;
        $currentStatus = $currentVerification?->status?->value ?? $currentVerification?->status;

        return in_array($currentStatus, [$approvedStatus, $pendingStatus], true)
            || in_array($session->status, [VerificationSessionStatus::Submitted, VerificationSessionStatus::Completed], true);
    }

    private function expiredLinkResponse(): Response
    {
        return response()->view('verification.expired', [
            'title' => 'Verification unavailable',
            'message' => 'This verification page is no longer available. Return to the app if you need a new verification link.',
        ], 410);
    }

    private function invalidLinkResponse(): Response
    {
        return response()->view('verification.invalid', [
            'title' => 'Verification unavailable',
            'message' => 'This verification link could not be confirmed. Return to the app if you need a new verification link.',
        ], 403);
    }

    private function resolveBlockedState(
        VerificationSession $session,
        UserIdentityVerification|ProviderIdentityVerification|null $currentVerification,
    ): ?Response {
        if ($session->shouldExpire()) {
            $session->markExpired();
        }

        if ($session->status === VerificationSessionStatus::Expired) {
            return $this->expiredLinkResponse();
        }

        if ($session->status === VerificationSessionStatus::Cancelled) {
            return response()->view('verification.invalid', [
                'title' => 'Verification unavailable',
                'message' => 'This verification page is no longer available. Return to the app if you need a new verification link.',
            ], 410);
        }

        $approvedStatus = $session->flow === VerificationSessionFlow::ProviderIdentity
            ? ProviderVerificationStatus::Approved->value
            : UserIdentityVerificationStatus::Approved->value;
        $pendingStatus = $session->flow === VerificationSessionFlow::ProviderIdentity
            ? ProviderVerificationStatus::Pending->value
            : UserIdentityVerificationStatus::Pending->value;
        $rejectedStatus = $session->flow === VerificationSessionFlow::ProviderIdentity
            ? ProviderVerificationStatus::Rejected->value
            : UserIdentityVerificationStatus::Rejected->value;
        $currentStatus = $currentVerification?->status?->value ?? $currentVerification?->status;

        if ($currentStatus === $approvedStatus) {
            $this->completeSession($session);

            return response()->view('verification.result', [
                'title' => $session->flow === VerificationSessionFlow::ProviderIdentity
                    ? 'Already verified'
                    : 'Account already verified',
                'message' => $session->flow === VerificationSessionFlow::ProviderIdentity
                    ? 'Your provider verification has already been approved.'
                    : 'Your verification has already been approved.',
                'tone' => 'success',
            ]);
        }

        if (
            $currentStatus === $pendingStatus
            || in_array($session->status, [VerificationSessionStatus::Submitted, VerificationSessionStatus::Completed], true)
        ) {
            return response()->view('verification.result', [
                'title' => 'You have already submitted your documents',
                'message' => 'Please await notification when reviewed.',
                'tone' => 'success',
            ]);
        }

        if ($currentStatus === $rejectedStatus) {
            return null;
        }

        return null;
    }

    private function completeSession(VerificationSession $session): void
    {
        if ($session->status === VerificationSessionStatus::Completed) {
            return;
        }

        $session->forceFill([
            'status' => VerificationSessionStatus::Completed,
            'completed_at' => $session->completed_at ?? now(),
        ])->save();
    }
}
