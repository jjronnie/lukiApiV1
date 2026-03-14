<?php

namespace App\Http\Controllers\Admin;

use App\Enums\AuditAction;
use App\Enums\UserIdentityVerificationStatus;
use App\Enums\VerificationSessionStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ReviewUserIdentityVerificationRequest;
use App\Models\UserIdentityVerification;
use App\Models\VerificationSession;
use App\Services\AuditLogService;
use App\Services\NotificationDispatcher;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Illuminate\View\View;

class UserIdentityVerificationController extends Controller
{
    public function __construct(
        private readonly AuditLogService $auditLogService,
        private readonly NotificationDispatcher $notificationDispatcher,
    ) {}

    public function index(Request $request): View
    {
        $status = trim((string) $request->input('status', ''));

        return view('admin.user-identity-verifications.index', [
            'verifications' => UserIdentityVerification::query()
                ->with(['user', 'reviewer'])
                ->when($status !== '', fn ($query) => $query->where('status', $status))
                ->latest('submitted_at')
                ->paginate(20)
                ->withQueryString(),
            'statusFilter' => $status,
        ]);
    }

    public function show(UserIdentityVerification $verification): View
    {
        return view('admin.user-identity-verifications.show', [
            'verification' => $verification->load(['user', 'reviewer']),
        ]);
    }

    public function review(
        ReviewUserIdentityVerificationRequest $request,
        UserIdentityVerification $verification,
    ): RedirectResponse {
        $data = $request->validated();
        $status = UserIdentityVerificationStatus::from($data['status']);

        $verification->update([
            'status' => $status,
            'reviewed_by' => $request->user()?->id,
            'reviewed_at' => now(),
            'verified_at' => $status === UserIdentityVerificationStatus::Approved ? now() : null,
            'rejection_reason' => $status === UserIdentityVerificationStatus::Rejected
                ? ($data['rejection_reason'] ?? null)
                : null,
        ]);

        VerificationSession::query()
            ->where('user_id', $verification->user_id)
            ->where('status', VerificationSessionStatus::Submitted)
            ->whereNull('completed_at')
            ->latest('submitted_at')
            ->limit(1)
            ->update([
                'status' => VerificationSessionStatus::Completed,
                'completed_at' => now(),
                'updated_at' => now(),
            ]);

        $this->auditLogService->log(
            action: $status === UserIdentityVerificationStatus::Approved
                ? AuditAction::UserIdentityVerificationApproved->value
                : AuditAction::UserIdentityVerificationRejected->value,
            actor: $request->user(),
            auditableType: UserIdentityVerification::class,
            auditableId: $verification->id,
            meta: ['status' => $status->value],
            request: $request,
        );

        $verification->loadMissing('user');
        if ($verification->user !== null) {
            $isApproved = $status === UserIdentityVerificationStatus::Approved;

            $this->notificationDispatcher->sendToUser(
                $verification->user,
                \App\Enums\MobileAppType::Customer,
                $isApproved ? 'verification_approved' : 'verification_rejected',
                $isApproved ? 'Verification approved' : 'Verification rejected',
                $isApproved
                    ? 'Your account verification was approved successfully.'
                    : ($data['rejection_reason'] ?? 'Your verification was rejected. Please review the feedback and try again.'),
                [
                    'screen' => 'verification',
                ],
            );
        }

        return redirect()
            ->route('admin.user-identity-verifications.show', $verification)
            ->with('status', 'User identity verification updated.');
    }

    public function media(UserIdentityVerification $verification, string $collection): BinaryFileResponse
    {
        abort_unless(in_array($collection, ['selfie', 'id_front', 'id_back'], true), 404);

        $media = $verification->getFirstMedia($collection);
        abort_if($media === null, 404);

        return response()->file($media->getPath());
    }
}
