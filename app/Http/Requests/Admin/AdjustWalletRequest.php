<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class AdjustWalletRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('manage wallets') ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'amount' => ['required', 'integer', 'not_in:0'],
            'type' => ['required', 'string', 'in:topup,adjustment,penalty,payout'],
            'reference' => ['nullable', 'string', 'max:80'],
            'meta' => ['nullable', 'array'],
        ];
    }
}
