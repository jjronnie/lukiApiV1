<?php

namespace App\Http\Requests\Api\V1\Provider;

use Illuminate\Foundation\Http\FormRequest;

class UpdateOrderStatusRequest extends FormRequest
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
            'status' => ['required', 'string', 'in:on_the_way,arrived,in_service,started_working,completed'],
            'mark_paid' => ['nullable', 'boolean'],
            'idempotency_key' => ['nullable', 'string', 'max:120'],
        ];
    }
}
