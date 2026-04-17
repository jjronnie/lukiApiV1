<?php

namespace App\Http\Requests\Api\V1\User;

use App\Enums\PaymentMethod;
use App\Support\IdentityValueNormalizer;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CompleteCustomerProfileRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $hasPhoneInput = $this->filled('phone_local_number') || $this->filled('phone_country_code');
        $countryCode = trim((string) $this->input('phone_country_code', '+256'));
        $normalizedLocal = $hasPhoneInput
            ? IdentityValueNormalizer::ugandaPhoneLocal($this->input('phone_local_number', ''))
            : '';
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
            'phone_country_code' => $hasPhoneInput ? $countryCode : null,
            'phone_local_number' => $hasPhoneInput ? $localNumber : null,
            'phone' => $hasPhoneInput
                ? IdentityValueNormalizer::ugandaPhoneE164($normalizedLocal, $countryCode)
                : null,
            'default_payment_method' => filled($this->input('default_payment_method'))
                ? strtolower(trim((string) $this->input('default_payment_method')))
                : null,
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
        $requiresPhone = blank($this->user()?->phone);
        $paymentMethods = array_map(static fn (PaymentMethod $method): string => $method->value, PaymentMethod::cases());

        return [
            'first_name' => ['nullable', 'string', 'min:2', 'max:60', 'regex:/^[\pL][\pL\'\- ]*$/u', Rule::requiredIf(fn () => $this->filled('last_name'))],
            'last_name' => ['nullable', 'string', 'min:2', 'max:60', 'regex:/^[\pL][\pL\'\- ]*$/u', Rule::requiredIf(fn () => $this->filled('first_name'))],
            'phone_country_code' => [
                'nullable',
                Rule::requiredIf(fn () => $requiresPhone || $this->filled('phone_local_number')),
                Rule::in(['+256']),
            ],
            'phone_local_number' => [
                'nullable',
                Rule::requiredIf(fn () => $requiresPhone || $this->filled('phone_country_code')),
                'digits:9',
            ],
            'phone' => [
                'nullable',
                Rule::requiredIf(fn () => $requiresPhone || $this->filled('phone_local_number') || $this->filled('phone_country_code')),
                'string',
                Rule::unique('users', 'phone')->ignore($userId),
            ],
            'default_payment_method' => ['nullable', 'string', Rule::in($paymentMethods)],
        ];
    }

    public function messages(): array
    {
        return [
            'phone.unique' => 'This phone number is taken already.',
        ];
    }
}
