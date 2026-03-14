<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserEmailPreference;

class UserEmailPreferenceService
{
    public function ensureForUser(User $user): UserEmailPreference
    {
        return $user->emailPreference()->firstOrCreate(
            ['user_id' => $user->id],
            [
                'marketing_emails_enabled' => true,
                'booking_emails_enabled' => true,
                'authentication_emails_enabled' => true,
            ],
        );
    }

    public function bookingEmailsEnabled(User $user): bool
    {
        return $this->ensureForUser($user)->booking_emails_enabled;
    }
}
