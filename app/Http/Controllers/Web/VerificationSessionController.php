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

class VerificationSessionController extends Controller
{
    public function __construct(
        private readonly UserIdentityVerificationSubmissionService $submissionService,
        private readonly ProviderIdentityVerificationSubmissionService $providerSubmissionService,
    ) {}

    public function show(Request $request, VerificationSession $session)
    {
        if (! $this->hasValidSessionSignature($request, $session)) {
            if ($this->hasExpiredLink($request, $session)) {
                return response()->view('verification.expired', [
                    'title' => 'Session expired',
                    'message' => 'This verification session expired after 15 minutes. Return to the app to start a new one.',
                ], 410);
            }

            return response()->view('verification.invalid', [
                'title' => 'Verification link invalid',
                'message' => 'This verification link is invalid or has been changed. Please return to the app and start a new verification session.',
            ], 403);
        }

        $currentVerification = $session->flow === VerificationSessionFlow::ProviderIdentity
            ? $session->user()->with('providerIdentityVerification')->first()?->providerIdentityVerification
            : $session->user()->with('identityVerification')->first()?->identityVerification;
        $blockedResponse = $this->resolveBlockedState($session, $currentVerification);
        if ($blockedResponse !== null) {
            return $blockedResponse;
        }

        $session->forceFill([
            'started_at' => $session->started_at ?? now(),
            'ip_address' => $request->ip(),
            'user_agent' => (string) $request->userAgent(),
        ])->save();

        return view('verification.form', [
            'session' => $session,
            'expiresAt' => $session->expires_at,
            'submitUrl' => $request->fullUrl(),
            'isProviderFlow' => $session->flow === VerificationSessionFlow::ProviderIdentity,
        ]);
    }

    public function store(
        SubmitVerificationSessionRequest $request,
        VerificationSession $session,
    ) {
        if (! $this->hasValidSessionSignature($request, $session)) {
            if ($this->hasExpiredLink($request, $session)) {
                return response()->view('verification.expired', [
                    'title' => 'Session expired',
                    'message' => 'This verification session expired after 15 minutes. Return to the app to start a new one.',
                ], 410);
            }

            return response()->view('verification.invalid', [
                'title' => 'Verification link invalid',
                'message' => 'This verification link is invalid or has been changed. Please return to the app and start a new verification session.',
            ], 403);
        }

        $currentVerification = $session->flow === VerificationSessionFlow::ProviderIdentity
            ? $session->user()->with('providerIdentityVerification')->first()?->providerIdentityVerification
            : $session->user()->with('identityVerification')->first()?->identityVerification;
        $blockedResponse = $this->resolveBlockedState($session, $currentVerification);
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
            'title' => $session->flow === VerificationSessionFlow::ProviderIdentity
                ? 'Provider verification submitted'
                : 'Verification submitted',
            'message' => $session->flow === VerificationSessionFlow::ProviderIdentity
                ? 'Your provider verification was submitted successfully. Our team will review it and update your status in the app.'
                : 'Your verification was submitted successfully. Our team will review it and update your status in the app.',
            'tone' => 'success',
        ]);
    }

    private function hasValidSessionSignature(Request $request, VerificationSession $session): bool
    {
        return $request->hasValidSignature()
            && hash_equals($session->session_key, (string) $request->query('session_key', ''));
    }

    private function hasExpiredLink(Request $request, VerificationSession $session): bool
    {
        $expiresAt = (int) $request->query('expires', 0);

        return $session->hasExpired()
            || $session->status === VerificationSessionStatus::Expired
            || ($expiresAt > 0 && now()->timestamp > $expiresAt);
    }

    private function resolveBlockedState(
        VerificationSession $session,
        UserIdentityVerification|ProviderIdentityVerification|null $currentVerification,
    ) {
        if ($session->shouldExpire()) {
            $session->markExpired();
        }

        if ($session->status === VerificationSessionStatus::Expired) {
            return response()->view('verification.expired', [
                'title' => 'Session expired',
                'message' => 'This verification session expired after 15 minutes. Return to the app to start a new one.',
            ], 410);
        }

        if ($session->status === VerificationSessionStatus::Cancelled) {
            return response()->view('verification.invalid', [
                'title' => 'Session unavailable',
                'message' => 'This verification session is no longer available. Return to the app and open a fresh session.',
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
                    ? 'Provider already verified'
                    : 'Account already verified',
                'message' => $session->flow === VerificationSessionFlow::ProviderIdentity
                    ? 'Your provider identity has already been approved. You can return to the app.'
                    : 'Your identity has already been approved. You can return to the app.',
                'tone' => 'success',
            ]);
        }

        if (
            $currentStatus === $pendingStatus
            || in_array($session->status, [VerificationSessionStatus::Submitted, VerificationSessionStatus::Completed], true)
        ) {
            return response()->view('verification.result', [
                'title' => $session->flow === VerificationSessionFlow::ProviderIdentity
                    ? 'Provider verification submitted'
                    : 'Verification submitted',
                'message' => $session->flow === VerificationSessionFlow::ProviderIdentity
                    ? 'Your provider verification was submitted successfully. Our team will review it and update your status in the app.'
                    : 'Your verification was submitted successfully. Our team will review it and update your status in the app.',
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
