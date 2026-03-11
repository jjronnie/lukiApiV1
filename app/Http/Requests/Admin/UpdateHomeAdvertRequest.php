<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateHomeAdvertRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_active' => $this->boolean('is_active'),
        ]);
    }

    public function authorize(): bool
    {
        return $this->user()?->can('manage home adverts') ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:120'],
            'headline' => ['nullable', 'string', 'max:160'],
            'description' => ['nullable', 'string'],
            'button_text' => [
                Rule::requiredIf($this->input('link_type') !== 'none'),
                'nullable',
                'string',
                'max:60',
            ],
            'link_type' => ['required', Rule::in(['none', 'internal', 'external'])],
            'link_target' => [
                Rule::requiredIf($this->input('link_type') !== 'none'),
                'nullable',
                'string',
                'max:255',
            ],
            'image_url' => ['required', 'url', 'max:255'],
            'is_active' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
        ];
    }
}
