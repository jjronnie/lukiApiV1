<?php

// app/Http/Controllers/Api/V1/Auth/AuthController.php
namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Models\RefreshToken;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    private const ACCESS_TOKEN_NAME = 'api';
    private const ACCESS_TOKEN_TTL_MINUTES = 60;
    private const REFRESH_TOKEN_TTL_DAYS = 30;

    public function register(Request $request)
    {
        $data = $request->validate([
            'name' => ['required','string','max:120'],
            'email' => ['required','email','max:255','unique:users,email'],
            'password' => ['required','string','min:8','max:255'],
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => strtolower($data['email']),
            'password' => Hash::make($data['password']),
        ]);

        $user->assignRole('user');

        // Optional: send verification email immediately
        $user->sendEmailVerificationNotification();

        return $this->issueTokens($user, $request);
    }

    public function login(Request $request)
    {
        $data = $request->validate([
            'email' => ['required','email'],
            'password' => ['required','string'],
        ]);

        $user = User::where('email', strtolower($data['email']))->first();

        if (! $user || ! Hash::check($data['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Invalid credentials.'],
            ]);
        }

        if ($user->is_blocked ?? false) {
            throw ValidationException::withMessages([
                'email' => ['Account is blocked.'],
            ]);
        }

        return $this->issueTokens($user, $request);
    }

    public function refresh(Request $request)
    {
        $data = $request->validate([
            'refresh_token' => ['required','string'],
        ]);

        $hash = hash('sha256', $data['refresh_token']);

        $rt = RefreshToken::where('token_hash', $hash)->first();

        if (! $rt) {
            throw ValidationException::withMessages([
                'refresh_token' => ['Invalid refresh token.'],
            ]);
        }

        if ($rt->revoked_at !== null) {
            throw ValidationException::withMessages([
                'refresh_token' => ['Refresh token revoked.'],
            ]);
        }

        if (Carbon::now()->greaterThan($rt->expires_at)) {
            throw ValidationException::withMessages([
                'refresh_token' => ['Refresh token expired.'],
            ]);
        }

        $user = $rt->user;

        // Rotate refresh token: revoke old, create new
        $rt->update([
            'revoked_at' => now(),
            'last_used_at' => now(),
        ]);

        return $this->issueTokens($user, $request);
    }

    public function me(Request $request)
    {
        return response()->json([
            'user' => $request->user(),
        ]);
    }

    public function logout(Request $request)
    {
        $data = $request->validate([
            'refresh_token' => ['nullable','string'],
            'logout_all' => ['nullable','boolean'],
        ]);

        // Revoke current access token
        $request->user()->currentAccessToken()?->delete();

        if (!empty($data['logout_all']) && $data['logout_all'] === true) {
            // Revoke all refresh tokens
            RefreshToken::where('user_id', $request->user()->id)
                ->whereNull('revoked_at')
                ->update(['revoked_at' => now()]);
        } elseif (!empty($data['refresh_token'])) {
            $hash = hash('sha256', $data['refresh_token']);
            RefreshToken::where('user_id', $request->user()->id)
                ->where('token_hash', $hash)
                ->whereNull('revoked_at')
                ->update(['revoked_at' => now()]);
        }

        return response()->json(['message' => 'Logged out.']);
    }

    private function issueTokens(User $user, Request $request)
    {
        // Delete old access tokens if you want 1 device token only.
        // $user->tokens()->delete();

        $accessToken = $user->createToken(self::ACCESS_TOKEN_NAME)->plainTextToken;

        $rawRefresh = Str::random(64);
        RefreshToken::create([
            'user_id' => $user->id,
            'token_hash' => hash('sha256', $rawRefresh),
            'expires_at' => now()->addDays(self::REFRESH_TOKEN_TTL_DAYS),
            'user_agent' => substr((string) $request->userAgent(), 0, 255),
            'ip' => $request->ip(),
        ]);

        return response()->json([
            'access_token' => $accessToken,
            'token_type' => 'Bearer',
            'expires_in' => self::ACCESS_TOKEN_TTL_MINUTES * 60,
            'refresh_token' => $rawRefresh,
            'refresh_expires_in' => self::REFRESH_TOKEN_TTL_DAYS * 24 * 60 * 60,
            'user' => $user,
        ]);
    }
}