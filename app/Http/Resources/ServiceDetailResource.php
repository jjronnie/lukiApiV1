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
            'icon_name' => $this->icon_name,
            'image_url' => $this->image_url,
            'description' => $this->description,
            'currency' => $this->currency,
            'base_price_amount' => $this->base_price_amount,
            'starting_price_amount' => $this->base_price_amount,
            'duration_minutes' => $this->duration_minutes,
            'is_featured' => $this->is_featured,
            'category' => $this->category === null ? null : new ServiceCategoryResource($this->category),
            'tiers' => ServiceTierResource::collection($this->tiers),
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
