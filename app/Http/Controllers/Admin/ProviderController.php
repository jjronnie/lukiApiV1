<?php

namespace App\Http\Controllers\Admin;

use App\Enums\AuditAction;
use App\Enums\ProviderVerificationStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateProviderVerificationRequest;
use App\Models\ProviderProfile;
use App\Services\AuditLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProviderController extends Controller
{
    public function __construct(private readonly AuditLogService $auditLogService) {}

    public function index(Request $request): View
    {
        $status = $request->string('status')->toString();

        $providers = ProviderProfile::query()
            ->with('user')
            ->when($status !== '', fn ($query) => $query->where('verification_status', $status))
            ->latest()
            ->paginate(20);

        return view('admin.providers.index', [
            'providers' => $providers,
            'statusFilter' => $status,
        ]);
    }

    public function show(ProviderProfile $provider): View
    {
        return view('admin.providers.show', [
            'provider' => $provider->load(['user', 'services', 'documents', 'wallet', 'availability']),
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
}
