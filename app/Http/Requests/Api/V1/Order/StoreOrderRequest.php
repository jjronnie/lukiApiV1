<?php

namespace App\Http\Requests\Api\V1\Order;

use Illuminate\Foundation\Http\FormRequest;

class StoreOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'service_public_id' => ['required', 'string', 'exists:services,public_id'],
            'service_tier_public_id' => ['required', 'string', 'exists:service_tiers,public_id'],
            'add_on_public_ids' => ['nullable', 'array'],
            'add_on_public_ids.*' => ['string', 'exists:service_add_ons,public_id'],
            'is_scheduled' => ['required', 'boolean'],
            'scheduled_at' => ['nullable', 'date', 'after:now'],
            'address_text' => ['required', 'string', 'max:255'],
            'location_lat' => ['nullable', 'numeric', 'between:-90,90'],
            'location_lng' => ['nullable', 'numeric', 'between:-180,180'],
            'place_id' => ['nullable', 'string', 'max:120'],
            'location_notes' => ['nullable', 'string', 'max:255'],
            'payment_method' => ['required', 'string', 'in:cash,card,mtn,airtel'],
            'distance_km' => ['nullable', 'numeric', 'min:0'],
            'service_minutes' => ['nullable', 'integer', 'min:1'],
            'promo_code' => ['nullable', 'string', 'max:40'],
            'booking_mode' => ['required', 'string', 'in:normal,pair'],
            'pair_provider_number' => ['nullable', 'required_if:booking_mode,pair', 'digits:5'],
            'idempotency_key' => ['nullable', 'string', 'max:120'],
        ];
    }
}
