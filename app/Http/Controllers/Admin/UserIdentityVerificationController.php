<?php

namespace App\Http\Controllers\Admin;

use App\Enums\AuditAction;
use App\Enums\UserIdentityVerificationStatus;
use App\Enums\VerificationSessionStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ReviewUserIdentityVerificationRequest;
use App\Mail\IdentityVerificationDecisionMail;
use App\Models\UserIdentityVerification;
use App\Models\VerificationSession;
use App\Services\AuditLogService;
use App\Services\NotificationDispatcher;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class UserIdentityVerificationController extends Controller
{
    public function __construct(
        private readonly AuditLogService $auditLogService,
        private readonly NotificationDispatcher $notificationDispatcher,
    ) {}

    public function index(Request $request): View
    {
        $status = trim((string) $request->input('status', ''));
        $search = trim((string) $request->input('search', ''));

        return view('admin.user-identity-verifications.index', [
            'verifications' => UserIdentityVerification::query()
                ->with(['user', 'reviewer'])
                ->when($status !== '', fn ($query) => $query->where('status', $status))
                ->when($search !== '', function ($query) use ($search) {
                    $query->where(function ($nestedQuery) use ($search) {
                        $nestedQuery
                            ->where('id_type', 'like', '%'.$search.'%')
                            ->orWhere('id_number', 'like', '%'.$search.'%')
                            ->orWhereHas('user', function ($userQuery) use ($search) {
                                $userQuery
                                    ->where('name', 'like', '%'.$search.'%')
                                    ->orWhere('email', 'like', '%'.$search.'%');
                            });
                    });
                })
                ->latest('submitted_at')
                ->paginate(20)
                ->withQueryString(),
            'statusFilter' => $status,
            'search' => $search,
            'statusCounts' => [
                'all' => UserIdentityVerification::query()->count(),
                'pending' => UserIdentityVerification::query()->where('status', UserIdentityVerificationStatus::Pending->value)->count(),
                'approved' => UserIdentityVerification::query()->where('status', UserIdentityVerificationStatus::Approved->value)->count(),
                'rejected' => UserIdentityVerification::query()->where('status', UserIdentityVerificationStatus::Rejected->value)->count(),
            ],
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
            'id_number' => $status === UserIdentityVerificationStatus::Approved ? ($data['id_number'] ?? null) : null,
            'date_of_birth' => $status === UserIdentityVerificationStatus::Approved ? ($data['date_of_birth'] ?? null) : null,
            'district_id' => $status === UserIdentityVerificationStatus::Approved ? ($data['district_id'] ?? null) : null,
            'district_name' => $status === UserIdentityVerificationStatus::Approved ? ($data['district_name'] ?? null) : null,
            'county_id' => $status === UserIdentityVerificationStatus::Approved ? ($data['county_id'] ?? null) : null,
            'county_name' => $status === UserIdentityVerificationStatus::Approved ? ($data['county_name'] ?? null) : null,
            'sub_county_id' => $status === UserIdentityVerificationStatus::Approved ? ($data['sub_county_id'] ?? null) : null,
            'sub_county_name' => $status === UserIdentityVerificationStatus::Approved ? ($data['sub_county_name'] ?? null) : null,
            'parish_id' => $status === UserIdentityVerificationStatus::Approved ? ($data['parish_id'] ?? null) : null,
            'parish_name' => $status === UserIdentityVerificationStatus::Approved ? ($data['parish_name'] ?? null) : null,
            'village_id' => $status === UserIdentityVerificationStatus::Approved ? ($data['village_id'] ?? null) : null,
            'village_name' => $status === UserIdentityVerificationStatus::Approved ? ($data['village_name'] ?? null) : null,
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

            Mail::to($verification->user->email)->queue(
                new IdentityVerificationDecisionMail(
                    subjectLine: $isApproved ? 'Your Luki verification was approved' : 'Your Luki verification needs attention',
                    headline: $isApproved ? 'Your details were approved' : 'Your verification was rejected',
                    messageLine: $isApproved
                        ? 'Your identity verification has been approved successfully.'
                        : 'Your submitted verification could not be approved yet.',
                    reason: $isApproved ? null : ($data['rejection_reason'] ?? null),
                )
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

    public function destroyMedia(
        Request $request,
        UserIdentityVerification $verification,
        string $collection,
    ): RedirectResponse {
        abort_unless(in_array($collection, ['id_front', 'id_back'], true), 404);
        abort_unless($verification->canDeleteIdImages(), 422);

        $verification->clearMediaCollection($collection);

        $this->auditLogService->log(
            action: 'user_identity_verification_media_deleted',
            actor: $request->user(),
            auditableType: UserIdentityVerification::class,
            auditableId: $verification->id,
            meta: ['collection' => $collection],
            request: $request,
        );

        return redirect()
            ->route('admin.user-identity-verifications.show', $verification)
            ->with('status', strtoupper(str_replace('_', ' ', $collection)).' removed.');
    }
}
