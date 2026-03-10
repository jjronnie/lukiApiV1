<?php

namespace App\Services;

use App\Enums\PricingRuleType;
use App\Models\Promotion;
use App\Models\Service;
use App\Models\ServicePricingRule;
use App\Models\ServiceTier;

class PriceEstimateService
{
    /**
     * @param  array<int, int>  $addOnIds
     * @return array<string, mixed>
     */
    public function estimate(
        Service $service,
        ServiceTier $serviceTier,
        array $addOnIds,
        float $distanceKm,
        int $serviceMinutes,
        ?string $promoCode = null,
    ): array
    {
        $addOns = $service->addOns()->whereIn('id', $addOnIds)->where('is_active', true)->get();

        $baseAmount = $serviceTier->price_amount;
        $addOnsAmount = (int) $addOns->sum('price_amount');

        $distanceFee = 0;
        $peakFee = 0;
        $overtimeFee = 0;
        $taxAmount = 0;

        $rules = ServicePricingRule::query()
            ->where('is_active', true)
            ->where(function ($query) use ($service): void {
                $query->whereNull('service_id')->orWhere('service_id', $service->id);
            })
            ->orderBy('priority')
            ->get();

        foreach ($rules as $rule) {
            if ($rule->rule_type === PricingRuleType::DistancePerKm) {
                $distanceFee += (int) round($distanceKm * (int) ($rule->config['amount_per_km'] ?? 0));
            }

            if ($rule->rule_type === PricingRuleType::DistanceBand) {
                $bands = (array) ($rule->config['bands'] ?? []);
                foreach ($bands as $band) {
                    $min = (float) ($band['min_km'] ?? 0);
                    $max = (float) ($band['max_km'] ?? INF);
                    if ($distanceKm >= $min && $distanceKm < $max) {
                        $distanceFee += (int) ($band['fee_amount'] ?? 0);
                        break;
                    }
                }
            }

            if ($rule->rule_type === PricingRuleType::PeakHours && $this->isPeakHour((array) $rule->config)) {
                $peakFee += (int) ($rule->config['fee_amount'] ?? 0);
            }

            if ($rule->rule_type === PricingRuleType::Overtime && $serviceMinutes > (int) ($rule->config['threshold_minutes'] ?? 60)) {
                $extraMinutes = $serviceMinutes - (int) ($rule->config['threshold_minutes'] ?? 60);
                $blockMinutes = max((int) ($rule->config['block_minutes'] ?? 15), 1);
                $blocks = (int) ceil($extraMinutes / $blockMinutes);
                $overtimeFee += $blocks * (int) ($rule->config['amount_per_block'] ?? 0);
            }
        }

        $subtotal = $baseAmount + $addOnsAmount + $distanceFee + $peakFee + $overtimeFee;

        $taxRule = $rules->first(fn ($rule) => $rule->rule_type === PricingRuleType::TaxPercentage);
        if ($taxRule !== null) {
            $taxAmount = (int) round($subtotal * ((float) ($taxRule->config['percent'] ?? 0) / 100));
        }

        $discountAmount = 0;
        if ($promoCode !== null) {
            $discountAmount = $this->computePromoDiscount($promoCode, $subtotal + $taxAmount);
        }

        $total = max(0, $subtotal + $taxAmount - $discountAmount);

        return [
            'service_public_id' => $service->public_id,
            'service_tier_public_id' => $serviceTier->public_id,
            'subtotal_amount' => $subtotal,
            'base_service_amount' => $baseAmount,
            'addons_amount' => $addOnsAmount,
            'distance_fee_amount' => $distanceFee,
            'peak_fee_amount' => $peakFee,
            'overtime_fee_amount' => $overtimeFee,
            'tax_amount' => $taxAmount,
            'discount_amount' => $discountAmount,
            'total_amount' => $total,
            'currency' => $service->currency,
            'tier' => [
                'name' => $serviceTier->name,
                'amount' => $baseAmount,
            ],
            'items' => [
                'base' => [
                    'label' => $service->name,
                    'tier_name' => $serviceTier->name,
                    'amount' => $baseAmount,
                ],
                'addons' => $addOns->map(fn ($addOn) => [
                    'id' => $addOn->id,
                    'name' => $addOn->name,
                    'amount' => $addOn->price_amount,
                ])->values()->all(),
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $config
     */
    private function isPeakHour(array $config): bool
    {
        $timezone = (string) ($config['timezone'] ?? 'Africa/Kampala');
        $currentHour = (int) now()->setTimezone($timezone)->format('H');

        $start = (int) ($config['start_hour'] ?? 17);
        $end = (int) ($config['end_hour'] ?? 22);

        return $currentHour >= $start && $currentHour < $end;
    }

    private function computePromoDiscount(string $promoCode, int $grossAmount): int
    {
        $promo = Promotion::query()
            ->where('code', strtoupper($promoCode))
            ->where('is_active', true)
            ->where(function ($query) {
                $query->whereNull('starts_at')->orWhere('starts_at', '<=', now());
            })
            ->where(function ($query) {
                $query->whereNull('ends_at')->orWhere('ends_at', '>=', now());
            })
            ->first();

        if ($promo === null) {
            return 0;
        }

        if ($promo->usage_limit !== null && $promo->used_count >= $promo->usage_limit) {
            return 0;
        }

        if ($promo->discount_type === 'fixed') {
            return min($grossAmount, (int) round((float) $promo->value));
        }

        return (int) round($grossAmount * ((float) $promo->value / 100));
    }
}
