<?php

namespace App\Http\Requests\Api\V1\User;

use App\Support\IdentityValueNormalizer;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CompleteCustomerProfileRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $countryCode = trim((string) $this->input('phone_country_code', '+256'));
        $normalizedLocal = IdentityValueNormalizer::ugandaPhoneLocal(
            $this->input('phone_local_number', '')
        );
        $localNumber = str_starts_with($normalizedLocal, '0')
            ? substr($normalizedLocal, 1)
            : $normalizedLocal;

        $this->merge([
            'first_name' => filled($this->input('first_name'))
                ? IdentityValueNormalizer::humanName($this->input('first_name'))
                : null,
            'last_name' => filled($this->input('last_name'))
                ? IdentityValueNormalizer::humanName($this->input('last_name'))
                : null,
            'phone_country_code' => $countryCode,
            'phone_local_number' => $localNumber,
            'phone' => IdentityValueNormalizer::ugandaPhoneE164($normalizedLocal, $countryCode),
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
            'first_name' => ['nullable', 'string', 'min:2', 'max:60', 'regex:/^[\pL][\pL\'\- ]*$/u', Rule::requiredIf(fn () => $this->filled('last_name'))],
            'last_name' => ['nullable', 'string', 'min:2', 'max:60', 'regex:/^[\pL][\pL\'\- ]*$/u', Rule::requiredIf(fn () => $this->filled('first_name'))],
            'phone_country_code' => ['required', Rule::in(['+256'])],
            'phone_local_number' => ['required', 'digits:9'],
            'phone' => ['required', 'string', Rule::unique('users', 'phone')->ignore($userId)],
        ];
    }

    public function messages(): array
    {
        return [
            'phone.unique' => 'This phone number is taken already.',
        ];
    }
}
