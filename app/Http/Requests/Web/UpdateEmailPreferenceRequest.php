<?php

namespace App\Http\Requests\Web;

use Illuminate\Foundation\Http\FormRequest;

class UpdateEmailPreferenceRequest extends FormRequest
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
            'marketing_emails_enabled' => ['nullable', 'boolean'],
            'booking_emails_enabled' => ['nullable', 'boolean'],
        ];
    }
}
