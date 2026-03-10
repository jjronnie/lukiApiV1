<?php

namespace App\Http\Requests\Api\V1\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ResendOtpRequest extends FormRequest
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
            'otp_token' => ['required', 'string', 'min:20'],
            'purpose' => ['required', Rule::in(['register', 'login', 'password_reset'])],
        ];
    }
}
