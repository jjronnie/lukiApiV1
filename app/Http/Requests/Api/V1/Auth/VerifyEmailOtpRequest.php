<?php

namespace App\Http\Requests\Api\V1\Auth;

use App\Support\IdentityValueNormalizer;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class VerifyEmailOtpRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $rawPhone = trim((string) $this->input('phone', ''));

        $this->merge([
            'email' => trim((string) $this->input('email', '')) !== ''
                ? IdentityValueNormalizer::email($this->input('email'))
                : null,
            'phone' => $rawPhone !== ''
                ? IdentityValueNormalizer::ugandaPhoneE164FromLocalInput($rawPhone)
                : null,
        ]);
    }

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
            'app_type' => ['required', Rule::in(['customer', 'provider'])],
            'email' => ['nullable', 'email'],
            'phone' => ['nullable', 'string', 'regex:/^\+256\d{9}$/'],
            'otp_token' => ['required', 'string', 'min:20'],
            'code' => ['required', 'digits:6'],
        ];
    }
}
