<?php

namespace App\Http\Requests\Api\V1\User;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CompleteCustomerProfileRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $countryCode = trim((string) $this->input('phone_country_code', '+256'));
        $localNumber = preg_replace('/\D+/', '', (string) $this->input('phone_local_number', '')) ?? '';

        if (strlen($localNumber) === 10 && str_starts_with($localNumber, '0')) {
            $localNumber = substr($localNumber, 1);
        }

        $this->merge([
            'phone_country_code' => $countryCode,
            'phone_local_number' => $localNumber,
            'phone' => $countryCode.$localNumber,
        ]);
    }

    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $userId = $this->user()?->id;

        return [
            'first_name' => ['nullable', 'string', 'min:2', 'max:60', Rule::requiredIf(fn () => $this->filled('last_name'))],
            'last_name' => ['nullable', 'string', 'min:2', 'max:60', Rule::requiredIf(fn () => $this->filled('first_name'))],
            'phone_country_code' => ['required', Rule::in(['+256'])],
            'phone_local_number' => ['required', 'digits:9'],
            'phone' => ['required', 'string', Rule::unique('users', 'phone')->ignore($userId)],
            'heard_about_source' => ['nullable', Rule::in(['friend', 'social_media', 'google', 'radio', 'flyer', 'other'])],
            'heard_about_other' => ['nullable', 'string', 'max:120', Rule::requiredIf(fn () => $this->input('heard_about_source') === 'other')],
        ];
    }
}
