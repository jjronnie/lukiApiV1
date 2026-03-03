<?php

namespace App\Http\Controllers\Admin;

use App\Enums\AuditAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AdjustWalletRequest;
use App\Models\Wallet;
use App\Services\AuditLogService;
use App\Services\WalletService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class WalletController extends Controller
{
    public function __construct(
        private readonly WalletService $walletService,
        private readonly AuditLogService $auditLogService,
    ) {}

    public function index(): View
    {
        return view('admin.wallets.index', [
            'wallets' => Wallet::query()->with('providerProfile.user')->paginate(20),
        ]);
    }

    public function show(Wallet $wallet): View
    {
        return view('admin.wallets.show', [
            'wallet' => $wallet->load(['providerProfile.user', 'transactions' => fn ($query) => $query->latest('created_at')->limit(50)]),
        ]);
    }

    public function adjust(AdjustWalletRequest $request, Wallet $wallet): RedirectResponse
    {
        $data = $request->validated();

        $transaction = $this->walletService->recordTransaction(
            wallet: $wallet,
            type: $data['type'],
            amount: (int) $data['amount'],
            order: null,
            createdByUserId: $request->user()->id,
            reference: $data['reference'] ?? null,
            meta: $data['meta'] ?? null,
        );

        $this->auditLogService->log(
            action: AuditAction::WalletAdjusted->value,
            actor: $request->user(),
            auditableType: Wallet::class,
            auditableId: $wallet->id,
            meta: [
                'amount' => $data['amount'],
                'type' => $data['type'],
                'transaction_id' => $transaction->id,
            ],
            request: $request,
        );

        return redirect()->route('admin.wallets.show', $wallet)->with('status', 'Wallet adjusted.');
    }
}
