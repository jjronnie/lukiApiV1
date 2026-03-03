<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProviderVerificationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('verify providers') ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'status' => ['required', 'string', 'in:approved,rejected,suspended'],
            'reason' => ['nullable', 'string', 'max:255'],
        ];
    }
}
