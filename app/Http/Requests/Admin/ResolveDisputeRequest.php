<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class ResolveDisputeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('resolve disputes') ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'status' => ['required', 'string', 'in:resolved,rejected'],
            'resolution_notes' => ['required', 'string', 'max:3000'],
            'wallet_adjustment_amount' => ['nullable', 'integer', 'not_in:0'],
        ];
    }
}
