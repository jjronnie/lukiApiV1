<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use Illuminate\Support\Facades\DB;

class WalletService
{
    /**
     * @param  array<string, mixed>  $meta
     */
    public function recordTransaction(Wallet $wallet, string $type, int $amount, ?Order $order, ?int $createdByUserId, ?string $reference = null, ?array $meta = null): WalletTransaction
    {
        return DB::transaction(function () use ($wallet, $type, $amount, $order, $createdByUserId, $reference, $meta) {
            $lockedWallet = Wallet::query()->whereKey($wallet->id)->lockForUpdate()->firstOrFail();

            $newBalance = $lockedWallet->balance_amount + $amount;
            $lockedWallet->balance_amount = $newBalance;
            $lockedWallet->save();

            return WalletTransaction::query()->create([
                'wallet_id' => $lockedWallet->id,
                'order_id' => $order?->id,
                'type' => $type,
                'amount' => $amount,
                'balance_after' => $newBalance,
                'reference' => $reference,
                'meta' => $meta,
                'created_by_user_id' => $createdByUserId,
                'created_at' => now(),
            ]);
        });
    }

    public function canReceiveOrders(Wallet $wallet): bool
    {
        return ($wallet->status->value ?? $wallet->status) === 'active'
            && ($wallet->balance_amount - $wallet->hold_amount) >= $wallet->min_required_amount;
    }
}
