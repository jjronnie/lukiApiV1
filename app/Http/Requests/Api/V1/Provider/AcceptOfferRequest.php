<?php

namespace App\Http\Requests\Api\V1\Provider;

use Illuminate\Foundation\Http\FormRequest;

class AcceptOfferRequest extends FormRequest
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
            'provider_lat' => ['nullable', 'numeric', 'between:-90,90'],
            'provider_lng' => ['nullable', 'numeric', 'between:-180,180'],
        ];
    }
}
