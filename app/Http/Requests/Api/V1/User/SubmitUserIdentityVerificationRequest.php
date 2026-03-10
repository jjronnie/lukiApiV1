<?php

namespace App\Http\Requests\Api\V1\User;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SubmitUserIdentityVerificationRequest extends FormRequest
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
            'id_type' => ['required', Rule::in(['passport', 'national_id', 'drivers_license'])],
            'selfie' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'id_front' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'id_back' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ];
    }
}
