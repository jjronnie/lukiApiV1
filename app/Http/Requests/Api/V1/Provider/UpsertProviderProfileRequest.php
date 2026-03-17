<?php

namespace App\Http\Requests\Api\V1\Provider;

use App\Support\IdentityValueNormalizer;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpsertProviderProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    protected function prepareForValidation(): void
    {
        $normalizedPhoneLocal = IdentityValueNormalizer::ugandaPhoneLocal($this->input('phone'));
        $normalizedPhoneE164 = IdentityValueNormalizer::ugandaPhoneE164($normalizedPhoneLocal);

        $this->merge([
            'provider_type' => strtolower(trim((string) $this->input('provider_type', 'individual'))),
            'display_name' => trim((string) $this->input('display_name', '')),
            'legal_name' => filled($this->input('legal_name'))
                ? IdentityValueNormalizer::humanName($this->input('legal_name'))
                : null,
            'phone' => $normalizedPhoneLocal,
            'phone_e164' => $normalizedPhoneE164,
            'address_text' => trim((string) $this->input('address_text', '')),
            'business_name' => filled($this->input('business_name'))
                ? trim((string) $this->input('business_name'))
                : null,
            'business_address' => filled($this->input('business_address'))
                ? trim((string) $this->input('business_address'))
                : null,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $userId = $this->user()?->id;

        return [
            'provider_type' => ['required', 'string', 'in:individual,business'],
            'display_name' => ['nullable', 'string', 'max:120'],
            'legal_name' => ['nullable', 'string', 'max:160'],
            'bio' => ['nullable', 'string', 'max:1000'],
            'phone' => ['required', 'string', 'regex:/^07\d{8}$/'],
            'phone_e164' => ['required', 'string', Rule::unique('users', 'phone')->ignore($userId)],
            'address_text' => ['required', 'string', 'max:255'],
            'business_name' => ['nullable', 'string', 'max:160'],
            'business_address' => ['nullable', 'string', 'max:255'],
            'avatar' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ];
    }

    public function messages(): array
    {
        return [
            'phone.regex' => 'Enter a valid Uganda phone number.',
            'phone_e164.unique' => 'This phone number is taken already.',
        ];
    }
}
