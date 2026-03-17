<?php

namespace App\Rules;

use App\Models\ProviderIdentityVerification;
use App\Models\UserIdentityVerification;
use App\Support\IdentityValueNormalizer;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class UniqueIdentityNumber implements ValidationRule
{
    public function __construct(
        private readonly ?int $ignoreUserId = null,
    ) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $normalized = IdentityValueNormalizer::verificationIdNumber((string) $value);
        if ($normalized === '') {
            return;
        }

        $customerExists = UserIdentityVerification::query()
            ->when(
                $this->ignoreUserId !== null,
                fn ($query) => $query->where('user_id', '!=', $this->ignoreUserId)
            )
            ->where('id_number', $normalized)
            ->exists();

        if ($customerExists) {
            $fail('This ID number is taken already.');

            return;
        }

        $providerExists = ProviderIdentityVerification::query()
            ->when(
                $this->ignoreUserId !== null,
                fn ($query) => $query->where('user_id', '!=', $this->ignoreUserId)
            )
            ->where('id_number', $normalized)
            ->exists();

        if ($providerExists) {
            $fail('This ID number is taken already.');
        }
    }
}
