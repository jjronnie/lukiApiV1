<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class UpdateServiceRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $tiers = collect((array) $this->input('tiers', []))
            ->map(function ($tier) {
                if (! is_array($tier)) {
                    return null;
                }

                $name = trim((string) ($tier['name'] ?? ''));
                if ($name === '') {
                    return null;
                }

                return [
                    'id' => filled($tier['id'] ?? null) ? (int) $tier['id'] : null,
                    'name' => $name,
                    'slug' => Str::slug((string) ($tier['slug'] ?? $name)),
                    'price_amount' => (int) ($tier['price_amount'] ?? 0),
                    'description' => filled($tier['description'] ?? null) ? (string) $tier['description'] : null,
                    'is_active' => filter_var($tier['is_active'] ?? false, FILTER_VALIDATE_BOOL),
                    'sort_order' => (int) ($tier['sort_order'] ?? 0),
                ];
            })
            ->filter()
            ->values()
            ->all();

        $this->merge([
            'is_active' => $this->boolean('is_active'),
            'is_featured' => $this->boolean('is_featured'),
            'tiers' => $tiers,
        ]);
    }

    public function authorize(): bool
    {
        return $this->user()?->can('manage services') ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $serviceId = $this->route('service')?->id;

        return [
            'service_category_id' => ['required', 'exists:service_categories,id'],
            'slug' => ['required', 'string', 'max:120', Rule::unique('services', 'slug')->ignore($serviceId)],
            'name' => ['required', 'string', 'max:120'],
            'icon_name' => ['required', 'string', 'max:80'],
            'image_url' => ['nullable', 'url', 'max:255'],
            'description' => ['nullable', 'string'],
            'currency' => ['required', 'string', 'size:3'],
            'duration_minutes' => ['required', 'integer', 'min:1'],
            'is_active' => ['nullable', 'boolean'],
            'is_featured' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'tiers' => ['required', 'array', 'min:1'],
            'tiers.*.id' => [
                'nullable',
                'integer',
                Rule::exists('service_tiers', 'id')->where(fn ($query) => $query->where('service_id', $serviceId)),
            ],
            'tiers.*.name' => ['required', 'string', 'max:80'],
            'tiers.*.slug' => ['required', 'string', 'max:120', 'distinct'],
            'tiers.*.price_amount' => ['required', 'integer', 'min:0'],
            'tiers.*.description' => ['nullable', 'string'],
            'tiers.*.is_active' => ['nullable', 'boolean'],
            'tiers.*.sort_order' => ['nullable', 'integer', 'min:0'],
        ];
    }
}
