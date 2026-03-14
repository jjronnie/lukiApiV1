<?php

namespace App\Http\Requests\Api\V1\User;

use Illuminate\Foundation\Http\FormRequest;

class UpdateEmailPreferencesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'marketing_emails_enabled' => ['nullable', 'boolean'],
            'booking_emails_enabled' => ['nullable', 'boolean'],
        ];
    }
}
