<?php

namespace App\Http\Requests\Web;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SubmitVerificationSessionRequest extends FormRequest
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
            'id_type' => ['required', Rule::in(['passport', 'national_id', 'drivers_license'])],
            'selfie' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:10240'],
            'id_front' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:10240'],
            'id_back' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:10240'],
            'business_license' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:10240'],
        ];
    }
}
