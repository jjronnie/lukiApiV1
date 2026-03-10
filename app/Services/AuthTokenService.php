<?php

namespace App\Services;

use App\Models\RefreshToken;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AuthTokenService
{
    private const ACCESS_TOKEN_NAME = 'api';

    /**
     * @return array{access_token:string, token_type:string, expires_in:int, refresh_token:string, refresh_expires_in:int}
     */
    public function issue(User $user, Request $request): array
    {
        $accessToken = $user->createToken(
            self::ACCESS_TOKEN_NAME,
            ['*'],
            now()->addMinutes($this->accessTokenTtlMinutes())
        )->plainTextToken;

        $rawRefreshToken = Str::random(64);

        RefreshToken::create([
            'user_id' => $user->id,
            'token_hash' => hash('sha256', $rawRefreshToken),
            'expires_at' => now()->addDays($this->refreshTokenTtlDays()),
            'user_agent' => substr((string) $request->userAgent(), 0, 255),
            'ip' => $request->ip(),
        ]);

        return [
            'access_token' => $accessToken,
            'token_type' => 'Bearer',
            'expires_in' => $this->accessTokenTtlMinutes() * 60,
            'refresh_token' => $rawRefreshToken,
            'refresh_expires_in' => $this->refreshTokenTtlDays() * 24 * 60 * 60,
        ];
    }

    private function accessTokenTtlMinutes(): int
    {
        return max(1, (int) config('luki.auth.access_token_ttl_minutes', 43_200));
    }

    private function refreshTokenTtlDays(): int
    {
        return max(1, (int) config('luki.auth.refresh_token_ttl_days', 365));
    }
}
