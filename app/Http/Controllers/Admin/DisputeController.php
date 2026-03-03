<?php

namespace App\Http\Controllers\Admin;

use App\Enums\AuditAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ResolveDisputeRequest;
use App\Models\Dispute;
use App\Models\Wallet;
use App\Services\AuditLogService;
use App\Services\WalletService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class DisputeController extends Controller
{
    public function __construct(
        private readonly WalletService $walletService,
        private readonly AuditLogService $auditLogService,
    ) {}

    public function index(): View
    {
        return view('admin.disputes.index', [
            'disputes' => Dispute::query()->with(['order', 'user'])->latest()->paginate(20),
        ]);
    }

    public function resolve(ResolveDisputeRequest $request, Dispute $dispute): RedirectResponse
    {
        $data = $request->validated();

        $dispute->update([
            'status' => $data['status'],
            'resolution_notes' => $data['resolution_notes'],
            'resolved_by' => $request->user()->id,
            'resolved_at' => now(),
        ]);

        if (isset($data['wallet_adjustment_amount']) && $dispute->order->provider_profile_id !== null) {
            $wallet = Wallet::query()->where('provider_profile_id', $dispute->order->provider_profile_id)->first();
            if ($wallet !== null) {
                $this->walletService->recordTransaction(
                    wallet: $wallet,
                    type: 'dispute_adjustment',
                    amount: (int) $data['wallet_adjustment_amount'],
                    order: $dispute->order,
                    createdByUserId: $request->user()->id,
                    reference: 'DSP-'.$dispute->id,
                    meta: ['dispute_id' => $dispute->id],
                );
            }
        }

        $this->auditLogService->log(
            action: AuditAction::DisputeResolved->value,
            actor: $request->user(),
            auditableType: Dispute::class,
            auditableId: $dispute->id,
            meta: ['status' => $data['status']],
            request: $request,
        );

        return redirect()->route('admin.disputes.index')->with('status', 'Dispute resolved.');
    }
}
