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
            'description' => $this->description,
            'currency' => $this->currency,
            'base_price_amount' => $this->base_price_amount,
            'duration_minutes' => $this->duration_minutes,
            'is_active' => $this->is_active,
        ];
    }
}
