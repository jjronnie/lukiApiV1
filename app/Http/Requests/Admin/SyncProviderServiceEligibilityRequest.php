<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class SyncProviderServiceEligibilityRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $selectedServiceId = (int) $this->input('selected_service_id');
        $selectedTierIds = collect((array) data_get(
            $this->input('service_configurations', []),
            $selectedServiceId.'.tier_ids',
            []
        ))
            ->filter(fn ($value) => $value !== null && $value !== '')
            ->map(fn ($value) => (int) $value)
            ->values()
            ->all();

        $this->merge([
            'selected_service_id' => $selectedServiceId > 0 ? $selectedServiceId : null,
            'service_action' => trim((string) $this->input('service_action', '')),
            'selected_tier_ids' => $selectedTierIds,
            'review_reason' => trim((string) data_get(
                $this->input('service_configurations', []),
                $selectedServiceId.'.review_reason',
                ''
            )),
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
            'selected_service_id' => ['required', 'integer', 'exists:services,id'],
            'service_action' => ['required', 'string', 'in:approved,declined,suspended'],
            'selected_tier_ids' => ['nullable', 'array'],
            'selected_tier_ids.*' => ['integer', 'exists:service_tiers,id'],
            'review_reason' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            if (in_array($this->input('service_action'), ['declined', 'suspended'], true)
                && blank($this->input('review_reason'))) {
                $validator->errors()->add('review_reason', 'A reason is required when rejecting or suspending a service.');
            }
        });
    }
}
