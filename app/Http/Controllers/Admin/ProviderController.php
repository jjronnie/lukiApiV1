<?php

namespace App\Http\Controllers\Admin;

use App\Enums\AuditAction;
use App\Enums\MobileAppType;
use App\Enums\ProviderServiceApprovalStatus;
use App\Enums\ProviderVerificationStatus;
use App\Enums\VerificationSessionFlow;
use App\Enums\VerificationSessionStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SyncProviderServiceEligibilityRequest;
use App\Http\Requests\Admin\UpdateProviderVerificationRequest;
use App\Mail\IdentityVerificationDecisionMail;
use App\Mail\ProviderServiceEnrollmentDeclinedMail;
use App\Models\ProviderDocument;
use App\Models\ProviderIdentityVerification;
use App\Models\ProviderProfile;
use App\Models\Service;
use App\Models\VerificationSession;
use App\Services\AuditLogService;
use App\Services\NotificationDispatcher;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ProviderController extends Controller
{
    public function __construct(
        private readonly AuditLogService $auditLogService,
        private readonly NotificationDispatcher $notificationDispatcher,
    ) {}

    public function index(Request $request): View
    {
        $status = $request->string('status')->toString();
        $search = trim((string) $request->input('search', ''));

        $providers = ProviderProfile::query()
            ->with('user')
            ->when($status !== '', fn ($query) => $query->where('verification_status', $status))
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($nestedQuery) use ($search) {
                    $nestedQuery
                        ->where('display_name', 'like', '%'.$search.'%')
                        ->orWhere('provider_number', 'like', '%'.$search.'%')
                        ->orWhereHas('user', function ($userQuery) use ($search) {
                            $userQuery
                                ->where('name', 'like', '%'.$search.'%')
                                ->orWhere('email', 'like', '%'.$search.'%');
                        });
                });
            })
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.providers.index', [
            'providers' => $providers,
            'statusFilter' => $status,
            'search' => $search,
        ]);
    }

    public function show(ProviderProfile $provider): View
    {
        return view('admin.providers.show', [
            'provider' => $provider->load([
                'user.providerIdentityVerification.reviewer',
                'documents',
                'wallet',
                'availability',
                'providerServices.service.category',
                'providerServices.eligibleTiers',
            ]),
            'services' => Service::query()
                ->with(['category', 'tiers' => fn ($query) => $query->where('is_active', true)->orderBy('sort_order')->orderBy('price_amount')])
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function updateVerification(UpdateProviderVerificationRequest $request, ProviderProfile $provider): RedirectResponse
    {
        $data = $request->validated();

        $provider->update([
            'verification_status' => ProviderVerificationStatus::from($data['status']),
            'verified_at' => $data['status'] === ProviderVerificationStatus::Approved->value ? now() : null,
            'rejection_reason' => $data['status'] === ProviderVerificationStatus::Rejected->value ? ($data['reason'] ?? null) : null,
            'avatar_locked_at' => $data['status'] === ProviderVerificationStatus::Approved->value
                ? ($provider->avatar_locked_at ?? now())
                : null,
        ]);

        $identityVerification = ProviderIdentityVerification::query()->firstOrNew([
            'provider_profile_id' => $provider->id,
        ]);

        $identityVerification->fill([
            'user_id' => $provider->user_id,
            'status' => ProviderVerificationStatus::from($data['status']),
            'id_number' => $data['status'] === ProviderVerificationStatus::Approved->value ? ($data['id_number'] ?? null) : null,
            'date_of_birth' => $data['status'] === ProviderVerificationStatus::Approved->value ? ($data['date_of_birth'] ?? null) : null,
            'district_id' => $data['status'] === ProviderVerificationStatus::Approved->value ? ($data['district_id'] ?? null) : null,
            'district_name' => $data['status'] === ProviderVerificationStatus::Approved->value ? ($data['district_name'] ?? null) : null,
            'county_id' => $data['status'] === ProviderVerificationStatus::Approved->value ? ($data['county_id'] ?? null) : null,
            'county_name' => $data['status'] === ProviderVerificationStatus::Approved->value ? ($data['county_name'] ?? null) : null,
            'sub_county_id' => $data['status'] === ProviderVerificationStatus::Approved->value ? ($data['sub_county_id'] ?? null) : null,
            'sub_county_name' => $data['status'] === ProviderVerificationStatus::Approved->value ? ($data['sub_county_name'] ?? null) : null,
            'parish_id' => $data['status'] === ProviderVerificationStatus::Approved->value ? ($data['parish_id'] ?? null) : null,
            'parish_name' => $data['status'] === ProviderVerificationStatus::Approved->value ? ($data['parish_name'] ?? null) : null,
            'village_id' => $data['status'] === ProviderVerificationStatus::Approved->value ? ($data['village_id'] ?? null) : null,
            'village_name' => $data['status'] === ProviderVerificationStatus::Approved->value ? ($data['village_name'] ?? null) : null,
            'reviewed_by' => $request->user()->id,
            'reviewed_at' => now(),
            'verified_at' => $data['status'] === ProviderVerificationStatus::Approved->value ? now() : null,
            'rejection_reason' => $data['status'] === ProviderVerificationStatus::Rejected->value ? ($data['reason'] ?? null) : null,
        ]);

        if ($identityVerification->submitted_at === null) {
            $identityVerification->submitted_at = now();
        }

        $identityVerification->save();

        VerificationSession::query()
            ->where('user_id', $provider->user_id)
            ->where('flow', VerificationSessionFlow::ProviderIdentity)
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
            action: $data['status'] === ProviderVerificationStatus::Approved->value ? AuditAction::ProviderApproved->value : AuditAction::ProviderRejected->value,
            actor: $request->user(),
            auditableType: ProviderProfile::class,
            auditableId: $provider->id,
            meta: ['reason' => $data['reason'] ?? null],
            request: $request,
        );

        $provider->loadMissing('user');
        if ($provider->user !== null) {
            $isApproved = $data['status'] === ProviderVerificationStatus::Approved->value;

            $this->notificationDispatcher->sendToUser(
                $provider->user,
                MobileAppType::Provider,
                $isApproved ? 'provider_verification_approved' : 'provider_verification_rejected',
                $isApproved ? 'Verification approved' : 'Verification update',
                $isApproved
                    ? 'Your provider verification was approved.'
                    : ($data['reason'] ?? 'Your provider verification was updated.'),
                [
                    'screen' => 'provider_verification',
                ],
                $provider,
            );

            Mail::to($provider->user->email)->queue(
                new IdentityVerificationDecisionMail(
                    subjectLine: $isApproved ? 'Your provider verification was approved' : 'Your provider verification needs attention',
                    headline: $isApproved ? 'Your provider details were approved' : 'Your provider verification was rejected',
                    messageLine: $isApproved
                        ? 'Your provider verification has been approved successfully.'
                        : 'Your provider verification could not be approved yet.',
                    reason: $isApproved ? null : ($data['reason'] ?? null),
                )
            );
        }

        return redirect()->route('admin.providers.show', $provider)->with('status', 'Provider verification updated.');
    }

    public function updateServices(
        SyncProviderServiceEligibilityRequest $request,
        ProviderProfile $provider,
    ): RedirectResponse {
        $data = $request->validated();

        $serviceId = (int) $data['selected_service_id'];
        $status = ProviderServiceApprovalStatus::from($data['service_action']);
        $reviewReason = $data['review_reason'] ?: null;
        $service = Service::query()->with('tiers')->findOrFail($serviceId);

        $providerService = $provider->providerServices()
            ->firstOrCreate(
                ['service_id' => $serviceId],
                [
                    'is_active' => false,
                    'approval_status' => ProviderServiceApprovalStatus::Pending,
                    'requested_at' => now(),
                ],
            );

        $previousStatus = $providerService->approval_status?->value ?? $providerService->approval_status;
        $eligibleTierIds = $service->tiers()
            ->where('is_active', true)
            ->when(
                filled($data['selected_tier_ids'] ?? []),
                fn ($query) => $query->whereIn('id', $data['selected_tier_ids'])
            )
            ->pluck('id')
            ->map(fn ($id) => (int) $id);

        if ($eligibleTierIds->isEmpty()) {
            $eligibleTierIds = $service->tiers()
                ->where('is_active', true)
                ->pluck('id')
                ->map(fn ($id) => (int) $id);
        }

        $providerService->update([
            'is_active' => $status === ProviderServiceApprovalStatus::Approved,
            'approval_status' => $status,
            'reviewed_by' => $request->user()->id,
            'reviewed_at' => now(),
            'review_reason' => in_array(
                $status,
                [ProviderServiceApprovalStatus::Declined, ProviderServiceApprovalStatus::Suspended],
                true
            ) ? $reviewReason : null,
        ]);

        $providerService->eligibleTiers()->sync(
            $eligibleTierIds
                ->mapWithKeys(fn (int $id) => [$id => ['is_active' => true]])
                ->all()
        );

        $provider->loadMissing('user');

        if ($provider->user !== null && $status === ProviderServiceApprovalStatus::Declined) {
            $this->notificationDispatcher->sendToUser(
                $provider->user,
                MobileAppType::Provider,
                'service_enrollment_declined',
                'Service enrollment declined',
                $reviewReason ?: 'One of your service enrollment requests was declined.',
                [
                    'screen' => 'provider_services',
                    'service_id' => $providerService->loadMissing('service')->service?->public_id,
                ],
                $provider,
            );

            if ($previousStatus !== ProviderServiceApprovalStatus::Declined->value || filled($reviewReason)) {
                Mail::to($provider->user->email)->queue(new ProviderServiceEnrollmentDeclinedMail(
                    $provider,
                    $providerService->loadMissing('service')
                ));
            }
        }

        if ($provider->user !== null && $status === ProviderServiceApprovalStatus::Suspended) {
            $this->notificationDispatcher->sendToUser(
                $provider->user,
                MobileAppType::Provider,
                'service_enrollment_suspended',
                'Service enrollment suspended',
                $reviewReason ?: 'One of your approved services was suspended.',
                [
                    'screen' => 'provider_services',
                    'service_id' => $providerService->loadMissing('service')->service?->public_id,
                ],
                $provider,
            );
        }

        return redirect()
            ->route('admin.providers.show', $provider)
            ->with('status', 'Provider service review updated.');
    }

    public function documentMedia(ProviderDocument $document): BinaryFileResponse
    {
        $media = $document->getFirstMedia('documents');
        abort_if($media === null, 404);

        return response()->file($media->getPath());
    }

    public function verificationMedia(ProviderProfile $provider, string $collection): BinaryFileResponse
    {
        abort_unless(in_array($collection, ['selfie', 'id_front', 'id_back', 'business_license'], true), 404);

        $verification = $provider->user?->providerIdentityVerification;
        abort_if($verification === null, 404);

        $media = $verification->getFirstMedia($collection);
        abort_if($media === null, 404);

        return response()->file($media->getPath());
    }

    public function destroyVerificationMedia(
        Request $request,
        ProviderProfile $provider,
        string $collection,
    ): RedirectResponse {
        abort_unless(in_array($collection, ['id_front', 'id_back'], true), 404);

        $verification = $provider->user?->providerIdentityVerification;
        abort_if($verification === null, 404);
        abort_unless($verification->canDeleteIdImages(), 422);

        $verification->clearMediaCollection($collection);

        $this->auditLogService->log(
            action: 'provider_identity_verification_media_deleted',
            actor: $request->user(),
            auditableType: ProviderIdentityVerification::class,
            auditableId: $verification->id,
            meta: ['collection' => $collection],
            request: $request,
        );

        return redirect()
            ->route('admin.providers.show', $provider)
            ->with('status', strtoupper(str_replace('_', ' ', $collection)).' removed.');
    }
}
