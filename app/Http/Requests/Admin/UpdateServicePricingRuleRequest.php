<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateServicePricingRuleRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $config = $this->input('config');

        if (is_string($config)) {
            $decoded = json_decode($config, true);

            if (json_last_error() === JSON_ERROR_NONE) {
                $this->merge([
                    'config' => $decoded,
                    'is_active' => $this->boolean('is_active'),
                ]);
            }
        }

        if (! is_string($config)) {
            $this->merge([
                'is_active' => $this->boolean('is_active'),
            ]);
        }
    }

    public function authorize(): bool
    {
        return $this->user()?->can('manage pricing rules') ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'service_id' => ['nullable', 'integer', 'exists:services,id'],
            'rule_type' => ['required', 'string', 'in:distance_per_km,distance_band,tax_percentage,peak_hours,overtime'],
            'config' => ['required', 'array'],
            'is_active' => ['nullable', 'boolean'],
            'priority' => ['nullable', 'integer', 'min:1'],
        ];
    }
}
