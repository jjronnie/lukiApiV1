<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ServiceResource extends JsonResource
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
            'is_active' => $this->is_active,
            'is_featured' => $this->is_featured,
            'tiers' => $this->whenLoaded(
                'tiers',
                fn () => ServiceTierResource::collection($this->tiers)
            ),
            'category' => $this->whenLoaded(
                'category',
                fn () => $this->category === null ? null : new ServiceCategoryResource($this->category)
            ),
        ];
    }
}
