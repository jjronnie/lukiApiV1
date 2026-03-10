<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ReviewUserIdentityVerificationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('review user identity verifications') ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'status' => ['required', Rule::in(['approved', 'rejected'])],
            'rejection_reason' => ['nullable', 'string', 'max:1000', Rule::requiredIf(fn () => $this->input('status') === 'rejected')],
        ];
    }
}
