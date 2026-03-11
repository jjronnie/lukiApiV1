<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreTransportZoneRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_active' => $this->boolean('is_active'),
            'is_fallback' => $this->boolean('is_fallback'),
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
        return [
            'name' => ['required', 'string', 'max:120'],
            'slug' => ['required', 'string', 'max:160', 'unique:transport_zones,slug'],
            'center_lat' => ['nullable', 'numeric', 'between:-90,90'],
            'center_lng' => ['nullable', 'numeric', 'between:-180,180'],
            'radius_km' => ['nullable', 'numeric', 'min:0'],
            'fee_amount' => ['required', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
            'is_fallback' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if ($this->boolean('is_fallback')) {
                return;
            }

            if (! $this->filled('center_lat') || ! $this->filled('center_lng') || ! $this->filled('radius_km')) {
                $validator->errors()->add('center_lat', 'Center coordinates and radius are required for normal zones.');
            }
        });
    }
}
