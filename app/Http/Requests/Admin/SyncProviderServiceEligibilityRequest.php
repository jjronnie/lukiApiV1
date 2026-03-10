<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class SyncProviderServiceEligibilityRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $serviceIds = collect((array) $this->input('service_ids', []))
            ->filter(fn ($value) => $value !== null && $value !== '')
            ->map(fn ($value) => (int) $value)
            ->values()
            ->all();

        $tiersByService = collect((array) $this->input('tiers_by_service', []))
            ->map(function ($tierIds) {
                return collect(is_array($tierIds) ? $tierIds : [])
                    ->filter(fn ($value) => $value !== null && $value !== '')
                    ->map(fn ($value) => (int) $value)
                    ->values()
                    ->all();
            })
            ->all();

        $this->merge([
            'service_ids' => $serviceIds,
            'tiers_by_service' => $tiersByService,
        ]);
    }

    public function authorize(): bool
    {
        return ($this->user()?->can('manage users') ?? false)
            || ($this->user()?->can('verify providers') ?? false);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'service_ids' => ['nullable', 'array'],
            'service_ids.*' => ['integer', 'exists:services,id'],
            'tiers_by_service' => ['nullable', 'array'],
            'tiers_by_service.*' => ['nullable', 'array'],
            'tiers_by_service.*.*' => ['integer', 'exists:service_tiers,id'],
        ];
    }
}
