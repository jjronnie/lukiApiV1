<?php

namespace App\Http\Requests\Api\V1\Dispute;

use Illuminate\Foundation\Http\FormRequest;

class StoreDisputeRequest extends FormRequest
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
            'order_public_id' => ['required', 'string', 'exists:orders,public_id'],
            'category' => ['required', 'string', 'in:service_quality,overcharge,no_show,safety,other'],
            'description' => ['required', 'string', 'max:2000'],
            'attachments' => ['nullable', 'array'],
            'attachments.*' => ['string', 'max:255'],
        ];
    }
}
