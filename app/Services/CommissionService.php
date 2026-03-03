<?php

namespace App\Services;

use App\Enums\CommissionType;
use App\Models\CommissionRule;
use App\Models\Order;

class CommissionService
{
    public function calculate(Order $order): int
    {
        $rule = CommissionRule::query()
            ->where('is_active', true)
            ->where(function ($query) use ($order) {
                $query->whereNull('service_id')
                    ->orWhereIn('service_id', $order->items()->pluck('service_id')->filter()->unique()->all());
            })
            ->where(function ($query) {
                $query->whereNull('effective_from')->orWhere('effective_from', '<=', now());
            })
            ->where(function ($query) {
                $query->whereNull('effective_to')->orWhere('effective_to', '>=', now());
            })
            ->orderByRaw('CASE WHEN service_id IS NULL THEN 1 ELSE 0 END')
            ->first();

        if ($rule === null) {
            return 0;
        }

        if ($rule->commission_type === CommissionType::Fixed) {
            $commission = (int) round((float) $rule->value);
        } else {
            $commission = (int) round($order->total_amount * ((float) $rule->value / 100));
        }

        if ($rule->min_commission_amount !== null) {
            $commission = max($commission, $rule->min_commission_amount);
        }

        if ($rule->max_commission_amount !== null) {
            $commission = min($commission, $rule->max_commission_amount);
        }

        return max(0, $commission);
    }
}
