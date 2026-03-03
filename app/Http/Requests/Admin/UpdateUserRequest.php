<?php

namespace App\Http\Requests\Admin;

use App\Enums\ProviderVerificationStatus;
use App\Enums\RoleName;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_blocked' => $this->boolean('is_blocked'),
        ]);
    }

    public function authorize(): bool
    {
        return $this->user()?->can('manage users') ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $userId = $this->route('user')?->id;

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($userId)],
            'phone' => ['nullable', 'string', 'max:32', Rule::unique('users', 'phone')->ignore($userId)],
            'referral_code' => ['nullable', 'string', 'max:24', Rule::unique('users', 'referral_code')->ignore($userId)],
            'is_blocked' => ['nullable', 'boolean'],
            'role' => ['required', 'string', Rule::in(collect(RoleName::cases())->map(fn (RoleName $role) => $role->value)->all())],
            'provider_display_name' => ['nullable', 'string', 'max:120'],
            'provider_type' => ['nullable', 'string', 'in:individual,business'],
            'provider_verification_status' => [
                'nullable',
                'string',
                Rule::in(collect(ProviderVerificationStatus::cases())->map(fn (ProviderVerificationStatus $status) => $status->value)->all()),
            ],
        ];
    }
}
