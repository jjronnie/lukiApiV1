<?php

namespace App\Http\Requests\Admin;

use App\Rules\UniqueIdentityNumber;
use App\Support\IdentityValueNormalizer;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ReviewUserIdentityVerificationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('review user identity verifications') ?? false;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'id_number' => filled($this->input('id_number'))
                ? IdentityValueNormalizer::verificationIdNumber($this->input('id_number'))
                : null,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $verification = $this->route('verification');

        return [
            'status' => ['required', Rule::in(['approved', 'rejected'])],
            'rejection_reason' => ['nullable', 'string', 'max:1000', Rule::requiredIf(fn () => $this->input('status') === 'rejected')],
            'id_number' => [
                'nullable',
                'string',
                'max:80',
                Rule::requiredIf(fn () => $this->input('status') === 'approved'),
                new UniqueIdentityNumber($verification?->user_id),
            ],
            'date_of_birth' => ['nullable', 'date', 'before:today', Rule::requiredIf(fn () => $this->input('status') === 'approved')],
            'district_id' => ['nullable', 'string', 'max:32', Rule::requiredIf(fn () => $this->input('status') === 'approved')],
            'district_name' => ['nullable', 'string', 'max:120', Rule::requiredIf(fn () => $this->input('status') === 'approved')],
            'county_id' => ['nullable', 'string', 'max:32', Rule::requiredIf(fn () => $this->input('status') === 'approved')],
            'county_name' => ['nullable', 'string', 'max:120', Rule::requiredIf(fn () => $this->input('status') === 'approved')],
            'sub_county_id' => ['nullable', 'string', 'max:32', Rule::requiredIf(fn () => $this->input('status') === 'approved')],
            'sub_county_name' => ['nullable', 'string', 'max:120', Rule::requiredIf(fn () => $this->input('status') === 'approved')],
            'parish_id' => ['nullable', 'string', 'max:32', Rule::requiredIf(fn () => $this->input('status') === 'approved')],
            'parish_name' => ['nullable', 'string', 'max:120', Rule::requiredIf(fn () => $this->input('status') === 'approved')],
            'village_id' => ['nullable', 'string', 'max:32', Rule::requiredIf(fn () => $this->input('status') === 'approved')],
            'village_name' => ['nullable', 'string', 'max:120', Rule::requiredIf(fn () => $this->input('status') === 'approved')],
        ];
    }

    public function messages(): array
    {
        return [
            'id_number.required' => 'ID number is required before approval.',
        ];
    }
}
