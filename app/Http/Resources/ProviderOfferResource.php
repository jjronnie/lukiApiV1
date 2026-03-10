<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProviderOfferResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'order_public_id' => $this->order->public_id,
            'status' => $this->status,
            'batch_no' => $this->batch_no,
            'booking_mode' => $this->order->booking_mode?->value ?? $this->order->booking_mode,
            'pair_provider_number' => $this->order->pair_provider_number,
            'service' => [
                'name' => $this->order->service_name_snapshot ?? $this->order->service?->name,
                'icon_name' => $this->order->service?->icon_name,
            ],
            'service_tier' => $this->order->service_tier_name_snapshot === null && $this->order->serviceTier === null ? null : [
                'name' => $this->order->service_tier_name_snapshot ?? $this->order->serviceTier?->name,
                'price_amount' => $this->order->serviceTier?->price_amount ?? data_get($this->order->price_breakdown, 'base_service_amount'),
            ],
            'customer' => [
                'name' => $this->order->user?->name,
                'address_text' => $this->order->address_text,
            ],
            'amounts' => [
                'currency' => data_get($this->order->price_breakdown, 'currency', $this->order->service?->currency ?? 'UGX'),
                'total_amount' => $this->order->total_amount,
            ],
            'expires_at' => $this->expires_at,
            'responded_at' => $this->responded_at,
            'meta' => $this->meta,
        ];
    }
}
