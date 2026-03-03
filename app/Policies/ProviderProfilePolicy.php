<?php

namespace App\Policies;

use App\Models\ProviderProfile;
use App\Models\User;

class ProviderProfilePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['provider', 'admin', 'superadmin']);
    }

    public function view(User $user, ProviderProfile $providerProfile): bool
    {
        if ($user->hasAnyRole(['admin', 'superadmin'])) {
            return true;
        }

        return $providerProfile->user_id === $user->id;
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(['user', 'provider', 'admin', 'superadmin']);
    }

    public function update(User $user, ProviderProfile $providerProfile): bool
    {
        if ($user->hasAnyRole(['admin', 'superadmin'])) {
            return true;
        }

        return $providerProfile->user_id === $user->id;
    }
}
