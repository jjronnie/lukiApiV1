<?php

namespace App\Http\Controllers\Admin;

use App\Enums\AuditAction;
use App\Enums\ProviderVerificationStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SyncProviderServiceEligibilityRequest;
use App\Http\Requests\Admin\UpdateProviderVerificationRequest;
use App\Models\ProviderDocument;
use App\Models\ProviderProfile;
use App\Models\Service;
use App\Services\AuditLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Illuminate\View\View;

class ProviderController extends Controller
{
    public function __construct(private readonly AuditLogService $auditLogService) {}

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
        ]);

        $this->auditLogService->log(
            action: $data['status'] === ProviderVerificationStatus::Approved->value ? AuditAction::ProviderApproved->value : AuditAction::ProviderRejected->value,
            actor: $request->user(),
            auditableType: ProviderProfile::class,
            auditableId: $provider->id,
            meta: ['reason' => $data['reason'] ?? null],
            request: $request,
        );

        return redirect()->route('admin.providers.show', $provider)->with('status', 'Provider verification updated.');
    }

    public function updateServices(
        SyncProviderServiceEligibilityRequest $request,
        ProviderProfile $provider,
    ): RedirectResponse {
        $data = $request->validated();

        $provider->syncServiceEligibility(
            $data['service_ids'] ?? [],
            $data['tiers_by_service'] ?? [],
        );

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
