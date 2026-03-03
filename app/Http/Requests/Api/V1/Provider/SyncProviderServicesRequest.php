<?php

namespace App\Http\Requests\Api\V1\Provider;

use Illuminate\Foundation\Http\FormRequest;

class SyncProviderServicesRequest extends FormRequest
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
            'service_public_ids' => ['required', 'array', 'min:1'],
            'service_public_ids.*' => ['required', 'string', 'exists:services,public_id'],
        ];
    }
}
