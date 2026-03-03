<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ServiceDetailResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'public_id' => $this->public_id,
            'slug' => $this->slug,
            'name' => $this->name,
            'description' => $this->description,
            'currency' => $this->currency,
            'base_price_amount' => $this->base_price_amount,
            'duration_minutes' => $this->duration_minutes,
            'addons' => $this->addOns->map(fn ($addOn) => [
                'public_id' => $addOn->public_id,
                'name' => $addOn->name,
                'description' => $addOn->description,
                'price_amount' => $addOn->price_amount,
                'is_active' => $addOn->is_active,
            ])->values(),
            'pricing_rules' => $this->pricingRules->map(fn ($rule) => [
                'type' => $rule->rule_type?->value ?? $rule->rule_type,
                'config' => $rule->config,
                'priority' => $rule->priority,
            ])->values(),
        ];
    }
}
