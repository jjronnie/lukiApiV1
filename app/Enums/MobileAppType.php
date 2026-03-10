<?php

namespace App\Enums;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

enum MobileAppType: string
{
    case Customer = 'customer';
    case Provider = 'provider';

    public static function fromRequest(Request $request): self
    {
        $value = strtolower((string) ($request->input('app_type') ?: $request->header('X-Mobile-App')));

        return self::tryFrom($value)
            ?? throw ValidationException::withMessages([
                'app_type' => ['App type must be either customer or provider.'],
            ]);
    }

    public function registrationRole(): string
    {
        return $this === self::Provider
            ? RoleName::Provider->value
            : RoleName::User->value;
    }

    public function allows(User $user): bool
    {
        if ($user->hasAnyRole([RoleName::Admin->value, RoleName::Superadmin->value])) {
            return false;
        }

        return $this === self::Provider
            ? $user->hasRole(RoleName::Provider->value)
            : $user->hasRole(RoleName::User->value);
    }

    public function mismatchMessage(): string
    {
        return $this === self::Provider
            ? 'This account can only sign in on the customer app.'
            : 'This account can only sign in on the provider app.';
    }
}
