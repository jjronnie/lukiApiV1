<?php

namespace App\Http\Controllers\Admin;

use App\Enums\AuditAction;
use App\Enums\MobileAppType;
use App\Enums\ProviderServiceApprovalStatus;
use App\Enums\ProviderVerificationStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SyncProviderServiceEligibilityRequest;
use App\Http\Requests\Admin\UpdateProviderVerificationRequest;
use App\Mail\ProviderServiceEnrollmentDeclinedMail;
use App\Models\ProviderDocument;
use App\Models\ProviderIdentityVerification;
use App\Models\ProviderProfile;
use App\Models\Service;
use App\Services\AuditLogService;
use App\Services\NotificationDispatcher;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Illuminate\View\View;

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
                'user',
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

        ProviderIdentityVerification::query()
            ->where('provider_profile_id', $provider->id)
            ->update([
                'status' => ProviderVerificationStatus::from($data['status']),
                'reviewed_by' => $request->user()->id,
                'reviewed_at' => now(),
                'verified_at' => $data['status'] === ProviderVerificationStatus::Approved->value ? now() : null,
                'rejection_reason' => $data['status'] === ProviderVerificationStatus::Rejected->value ? ($data['reason'] ?? null) : null,
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
        }

        return redirect()->route('admin.providers.show', $provider)->with('status', 'Provider verification updated.');
    }

    public function updateServices(
        SyncProviderServiceEligibilityRequest $request,
        ProviderProfile $provider,
    ): RedirectResponse {
        $data = $request->validated();

        foreach (($data['service_statuses'] ?? []) as $serviceId => $status) {
            if ($status === ProviderServiceApprovalStatus::Declined->value
                && blank($data['service_review_reasons'][$serviceId] ?? null)) {
                throw ValidationException::withMessages([
                    "service_review_reasons.$serviceId" => ['A decline reason is required when declining a service enrollment.'],
                ]);
            }
        }

        $provider->syncServiceEligibility(
            $data['service_ids'] ?? [],
            $data['tiers_by_service'] ?? [],
        );

        $provider->loadMissing('user');
        $managedServiceIds = collect($data['service_ids'] ?? [])
            ->merge(array_keys($data['service_statuses'] ?? []))
            ->map(fn ($value) => (int) $value)
            ->filter(fn (int $value) => $value > 0)
            ->unique()
            ->values();

        $providerServices = $provider->providerServices()
            ->whereIn('service_id', $managedServiceIds->all())
            ->with('service')
            ->get();

        foreach ($providerServices as $providerService) {
            $serviceId = (string) $providerService->service_id;
            $status = $data['service_statuses'][$serviceId]
                ?? $data['service_statuses'][$providerService->service_id]
                ?? ProviderServiceApprovalStatus::Approved->value;
            $reviewReason = $data['service_review_reasons'][$serviceId]
                ?? $data['service_review_reasons'][$providerService->service_id]
                ?? null;
            $previousStatus = $providerService->approval_status?->value ?? $providerService->approval_status;

            $providerService->update([
                'approval_status' => $status,
                'reviewed_by' => $request->user()->id,
                'reviewed_at' => now(),
                'review_reason' => $status === ProviderServiceApprovalStatus::Declined->value ? $reviewReason : null,
            ]);

            if ($status === ProviderServiceApprovalStatus::Declined->value && $provider->user !== null) {
                $this->notificationDispatcher->sendToUser(
                    $provider->user,
                    MobileAppType::Provider,
                    'service_enrollment_declined',
                    'Service enrollment declined',
                    $reviewReason ?: 'One of your service enrollment requests was declined.',
                    [
                        'screen' => 'provider_services',
                        'service_id' => $providerService->service?->public_id,
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
        }

        return redirect()
            ->route('admin.providers.show', $provider)
            ->with('status', 'Provider service eligibility updated.');
    }

    public function documentMedia(ProviderDocument $document): BinaryFileResponse
    {
        $media = $document->getFirstMedia('documents');
        abort_if($media === null, 404);

        return response()->file($media->getPath());
    }
}
