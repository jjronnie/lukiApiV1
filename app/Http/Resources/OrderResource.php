<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'public_id' => $this->public_id,
            'status' => $this->status?->value ?? $this->status,
            'booking_mode' => $this->booking_mode?->value ?? $this->booking_mode,
            'pair_provider_number' => $this->pair_provider_number,
            'is_scheduled' => $this->is_scheduled,
            'scheduled_at' => $this->scheduled_at,
            'address_text' => $this->address_text,
            'location' => [
                'lat' => $this->location_lat,
                'lng' => $this->location_lng,
                'place_id' => $this->place_id,
                'notes' => $this->location_notes,
            ],
            'payment' => [
                'method' => $this->payment_method?->value ?? $this->payment_method,
                'status' => $this->payment_status?->value ?? $this->payment_status,
                'paid_at' => $this->paid_at,
            ],
            'amounts' => [
                'currency' => data_get($this->price_breakdown, 'currency', $this->service?->currency ?? 'UGX'),
                'base_amount' => data_get($this->price_breakdown, 'base_service_amount', 0),
                'addons_amount' => data_get($this->price_breakdown, 'addons_amount', 0),
                'subtotal_amount' => $this->subtotal_amount,
                'distance_fee_amount' => $this->distance_fee_amount,
                'overtime_fee_amount' => $this->overtime_fee_amount,
                'peak_fee_amount' => $this->peak_fee_amount,
                'tax_amount' => $this->tax_amount,
                'discount_amount' => $this->discount_amount,
                'cancellation_fee_amount' => $this->cancellation_fee_amount,
                'total_amount' => $this->total_amount,
            ],
            'price_breakdown' => $this->price_breakdown,
            'service' => [
                'public_id' => $this->service?->public_id,
                'name' => $this->service_name_snapshot ?? $this->service?->name,
                'icon_name' => $this->service?->icon_name,
                'category_name' => $this->service?->category?->name,
            ],
            'service_tier' => $this->service_tier_name_snapshot === null && $this->serviceTier === null ? null : [
                'public_id' => $this->serviceTier?->public_id,
                'name' => $this->service_tier_name_snapshot ?? $this->serviceTier?->name,
                'price_amount' => $this->serviceTier?->price_amount ?? data_get($this->price_breakdown, 'base_service_amount'),
            ],
            'provider' => $this->providerProfile !== null ? [
                'public_id' => $this->providerProfile->public_id,
                'display_name' => $this->providerProfile->display_name,
                'provider_number' => $this->providerProfile->provider_number,
                'rating_avg' => (float) $this->providerProfile->rating_avg,
            ] : null,
            'items' => $this->whenLoaded('items', fn () => $this->items->map(fn ($item) => [
                'item_type' => $item->item_type,
                'name' => $item->name_snapshot,
                'tier_name' => $item->tier_name_snapshot,
                'unit_price_amount' => $item->unit_price_amount,
                'quantity' => $item->quantity,
                'line_total_amount' => $item->line_total_amount,
            ])->values()),
            'status_history' => $this->whenLoaded('statusHistories', fn () => $this->statusHistories->map(fn ($history) => [
                'from_status' => $history->from_status,
                'to_status' => $history->to_status,
                'changed_at' => $history->created_at,
                'meta' => $history->meta,
            ])->values()),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
