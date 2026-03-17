<?php

namespace App\Services;

use App\Models\Order;

class CommissionService
{
    public function calculate(Order $order): int
    {
        $baseAmount = max(0, (int) config('luki.commission.base_amount', 1000));
        $percentageRate = max(0, (float) config('luki.commission.percentage_rate', 5));
        $excludeTransport = (bool) config('luki.commission.exclude_transport', true);
        $commissionableAmount = (int) $order->total_amount;

        if ($excludeTransport) {
            $commissionableAmount -= (int) $order->transport_fee_amount;
        }

        $commissionableAmount = max(0, $commissionableAmount);
        $percentageAmount = (int) round($commissionableAmount * ($percentageRate / 100));

        return max(0, $baseAmount + $percentageAmount);
    }
}
