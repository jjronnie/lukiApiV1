<?php

namespace App\Services;

use App\Models\RefreshToken;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AuthTokenService
{
    private const ACCESS_TOKEN_NAME = 'api';

    private const REFRESH_TOKEN_TTL_DAYS = 30;

    /**
     * @return array{access_token:string, token_type:string, expires_in:int, refresh_token:string, refresh_expires_in:int}
     */
    public function issue(User $user, Request $request): array
    {
        $accessToken = $user->createToken(self::ACCESS_TOKEN_NAME)->plainTextToken;

        $rawRefreshToken = Str::random(64);

        RefreshToken::create([
            'user_id' => $user->id,
            'token_hash' => hash('sha256', $rawRefreshToken),
            'expires_at' => now()->addDays(self::REFRESH_TOKEN_TTL_DAYS),
            'user_agent' => substr((string) $request->userAgent(), 0, 255),
            'ip' => $request->ip(),
        ]);

        return [
            'access_token' => $accessToken,
            'token_type' => 'Bearer',
            'expires_in' => (int) (config('sanctum.expiration', 60) * 60),
            'refresh_token' => $rawRefreshToken,
            'refresh_expires_in' => self::REFRESH_TOKEN_TTL_DAYS * 24 * 60 * 60,
        ];
    }
}
