<?php

namespace App\Http\Requests\Api\V1\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class ResetPasswordRequest extends FormRequest
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
            'app_type' => ['required', Rule::in(['customer', 'provider'])],
            'email' => ['required', 'email'],
            'reset_token' => ['required_without:otp_token', 'string', 'min:20'],
            'otp_token' => ['required_without:reset_token', 'string', 'min:20'],
            'code' => ['required_with:otp_token', 'digits:6'],
            'password' => ['required', 'confirmed', Password::min(8)->letters()->numbers()],
        ];
    }
}
