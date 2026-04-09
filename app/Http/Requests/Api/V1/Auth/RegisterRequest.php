<?php

namespace App\Http\Requests\Api\V1\Auth;

use App\Support\IdentityValueNormalizer;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class RegisterRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $firstName = trim((string) $this->input('first_name', ''));
        $lastName = trim((string) $this->input('last_name', ''));
        $fallbackName = trim((string) $this->input('name', ''));

        if (($firstName === '' || $lastName === '') && $fallbackName !== '') {
            $parts = preg_split('/\s+/', $fallbackName) ?: [];
            $firstName = $firstName !== '' ? $firstName : (array_shift($parts) ?: $fallbackName);
            $derivedLastName = trim(implode(' ', $parts));
            $lastName = $lastName !== '' ? $lastName : $derivedLastName;
        }

        $rawPhone = trim((string) $this->input('phone', ''));

        $this->merge([
            'first_name' => IdentityValueNormalizer::humanName($firstName),
            'last_name' => IdentityValueNormalizer::humanName($lastName),
            'email' => IdentityValueNormalizer::email($this->input('email')),
            'phone' => $rawPhone !== ''
                ? IdentityValueNormalizer::ugandaPhoneE164FromLocalInput($rawPhone)
                : null,
            'auth_method' => trim((string) $this->input('auth_method', '')),
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
            'auth_method' => ['nullable', Rule::in(['email', 'phone'])],
            'first_name' => ['required', 'string', 'min:2', 'max:60', 'regex:/^[\pL][\pL\'\- ]*$/u'],
            'last_name' => ['required', 'string', 'min:2', 'max:60', 'regex:/^[\pL][\pL\'\- ]*$/u'],
            'email' => ['nullable', 'email', 'max:255', 'unique:users,email', 'required_without:phone'],
            'phone' => ['nullable', 'string', 'regex:/^\+256\d{9}$/', 'unique:users,phone', 'required_without:email'],
            'password' => ['required', 'confirmed', Password::min(8)->letters()->numbers()],
        ];
    }

    public function messages(): array
    {
        return [
            'email.unique' => 'This email is taken already.',
            'phone.regex' => 'Enter a valid phone number starting with 0 and 10 digits.',
            'phone.unique' => 'This phone number is taken already.',
            'email.required_without' => 'Email is required when phone number is not provided.',
            'phone.required_without' => 'Phone number is required when email is not provided.',
        ];
    }
}
