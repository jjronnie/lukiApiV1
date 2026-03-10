<?php

namespace App\Http\Requests\Api\V1\Pricing;

use Illuminate\Foundation\Http\FormRequest;

class PriceEstimateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
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
            'distance_km' => ['nullable', 'numeric', 'min:0'],
            'service_minutes' => ['nullable', 'integer', 'min:1'],
            'promo_code' => ['nullable', 'string', 'max:40'],
        ];
    }
}
