<?php

namespace App\Http\Requests\Api\V1\Auth;

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
            'first_name' => $firstName,
            'last_name' => $lastName,
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
            'first_name' => ['required', 'string', 'min:2', 'max:60'],
            'last_name' => ['required', 'string', 'min:2', 'max:60'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:32', 'unique:users,phone'],
            'password' => ['required', 'confirmed', Password::min(8)->letters()->numbers()],
        ];
    }
}
