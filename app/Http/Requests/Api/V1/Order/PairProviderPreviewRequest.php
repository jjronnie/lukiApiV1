<?php

namespace App\Http\Requests\Api\V1\Order;

use Illuminate\Foundation\Http\FormRequest;

class PairProviderPreviewRequest extends FormRequest
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
            'provider_number' => ['required', 'digits:5'],
        ];
    }
}
