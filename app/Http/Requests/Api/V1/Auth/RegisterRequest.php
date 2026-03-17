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

        $this->merge([
            'first_name' => IdentityValueNormalizer::humanName($firstName),
            'last_name' => IdentityValueNormalizer::humanName($lastName),
            'email' => IdentityValueNormalizer::email($this->input('email')),
            'phone' => filled($this->input('phone'))
                ? IdentityValueNormalizer::ugandaPhoneE164($this->input('phone'))
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
            'first_name' => ['required', 'string', 'min:2', 'max:60', 'regex:/^[\pL][\pL\'\- ]*$/u'],
            'last_name' => ['required', 'string', 'min:2', 'max:60', 'regex:/^[\pL][\pL\'\- ]*$/u'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'regex:/^\+2567\d{8}$/', 'unique:users,phone'],
            'password' => ['required', 'confirmed', Password::min(8)->letters()->numbers()],
        ];
    }

    public function messages(): array
    {
        return [
            'email.unique' => 'This email is taken already.',
            'phone.regex' => 'Enter a valid Uganda phone number.',
            'phone.unique' => 'This phone number is taken already.',
        ];
    }
}
