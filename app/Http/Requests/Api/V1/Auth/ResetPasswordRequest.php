<?php

namespace App\Http\Requests\Api\V1\Auth;

use App\Support\IdentityValueNormalizer;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class ResetPasswordRequest extends FormRequest
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
            'email' => ['nullable', 'email', 'required_without:phone'],
            'phone' => ['nullable', 'string', 'regex:/^\+256\d{9}$/', 'required_without:email'],
            'reset_token' => ['required_without:otp_token', 'string', 'min:20'],
            'otp_token' => ['required_without:reset_token', 'string', 'min:20'],
            'code' => ['required_with:otp_token', 'digits:6'],
            'password' => ['required', 'confirmed', Password::min(8)->letters()->numbers()],
        ];
    }

    public function messages(): array
    {
        return [
            'email.required_without' => 'Enter your email address or phone number.',
            'phone.required_without' => 'Enter your phone number or email address.',
            'phone.regex' => 'Enter a valid Uganda phone number.',
        ];
    }
}
