<?php

namespace App\Http\Requests\Api\V1\Provider;

use Illuminate\Foundation\Http\FormRequest;

class UpsertProviderProfileRequest extends FormRequest
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
            'provider_type' => ['required', 'string', 'in:individual,business'],
            'display_name' => ['nullable', 'string', 'max:120'],
            'legal_name' => ['nullable', 'string', 'max:160'],
            'bio' => ['nullable', 'string', 'max:1000'],
            'phone' => ['required', 'string', 'max:32'],
            'address_text' => ['required', 'string', 'max:255'],
            'business_name' => ['nullable', 'string', 'max:160'],
            'business_address' => ['nullable', 'string', 'max:255'],
            'avatar' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ];
    }
}
