<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Enums\RoleName;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Auth\LoginRequest;
use App\Http\Requests\Api\V1\Auth\RefreshTokenRequest;
use App\Http\Requests\Api\V1\Auth\RegisterRequest;
use App\Http\Requests\Api\V1\Auth\VerifyEmailOtpRequest;
use App\Http\Requests\Api\V1\Auth\VerifyLoginOtpRequest;
use App\Http\Resources\UserResource;
use App\Models\RefreshToken;
use App\Models\User;
use App\Services\AuthTokenService;
use App\Services\EmailOtpService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function __construct(
        private readonly AuthTokenService $authTokenService,
        private readonly EmailOtpService $emailOtpService,
    ) {}

    public function register(RegisterRequest $request): JsonResponse
    {
        $data = $request->validated();

        $name = $data['name']
            ?? Str::of($data['email'])->before('@')->replace('.', ' ')->title()->value();

        $user = User::query()->create([
            'name' => $name,
            'email' => strtolower($data['email']),
            'phone' => $data['phone'] ?? null,
            'password' => $data['password'],
        ]);

        $role = $data['register_as'] === 'provider'
            ? RoleName::Provider->value
            : RoleName::User->value;

        $user->assignRole($role);

        $otp = $this->emailOtpService->issue($user, 'email_verification');

        return response()->json([
            'message' => 'Verification code sent to email.',
            'email' => $user->email,
            ...$otp,
        ], 202);
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $data = $request->validated();

        $user = User::query()->where('email', strtolower($data['email']))->first();

        if ($user === null || ! Hash::check($data['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Invalid credentials.'],
            ]);
        }

        if ($user->is_blocked) {
            throw ValidationException::withMessages([
                'email' => ['This account is blocked.'],
            ]);
        }

        if ($user->hasAnyRole([RoleName::Superadmin->value, RoleName::Admin->value])) {
            return response()->json([
                'message' => 'Admin accounts can only sign in on the web portal.',
            ], 403);
        }

        $otp = $this->emailOtpService->issue($user, 'login');

        return response()->json([
            'message' => 'Verification code sent to email.',
            'email' => $user->email,
            ...$otp,
        ], 202);
    }

    public function refresh(RefreshTokenRequest $request): JsonResponse
    {
        $hash = hash('sha256', $request->validated('refresh_token'));

        $refreshToken = RefreshToken::query()->where('token_hash', $hash)->first();

        if ($refreshToken === null) {
            throw ValidationException::withMessages([
                'refresh_token' => ['Refresh token is invalid.'],
            ]);
        }

        if ($refreshToken->revoked_at !== null || $refreshToken->expires_at->isPast()) {
            throw ValidationException::withMessages([
                'refresh_token' => ['Refresh token is no longer valid.'],
            ]);
        }

        $refreshToken->update([
            'revoked_at' => now(),
            'last_used_at' => now(),
        ]);

        $user = $refreshToken->user;

        if ($user->hasAnyRole([RoleName::Superadmin->value, RoleName::Admin->value])) {
            return response()->json([
                'message' => 'Admin accounts can only sign in on the web portal.',
            ], 403);
        }

        $tokens = $this->authTokenService->issue($user, $request);

        return response()->json([
            ...$tokens,
            'user' => new UserResource($user->load('roles')),
        ]);
    }

    public function verifyEmailOtp(VerifyEmailOtpRequest $request): JsonResponse
    {
        $data = $request->validated();

        $record = $this->emailOtpService->verify($data['otp_token'], $data['code'], 'email_verification');
        $user = $record->user;

        if ($user === null || strtolower($data['email']) !== strtolower($record->email)) {
            throw ValidationException::withMessages([
                'code' => ['Invalid or expired verification code.'],
            ]);
        }

        if ($user->hasAnyRole([RoleName::Superadmin->value, RoleName::Admin->value])) {
            return response()->json([
                'message' => 'Admin accounts can only sign in on the web portal.',
            ], 403);
        }

        if (! $user->hasVerifiedEmail()) {
            $user->forceFill(['email_verified_at' => now()])->save();
        }

        $user->forceFill(['last_seen_at' => now()])->save();

        $tokens = $this->authTokenService->issue($user, $request);

        return response()->json([
            ...$tokens,
            'user' => new UserResource($user->load('roles')),
        ]);
    }

    public function verifyLoginOtp(VerifyLoginOtpRequest $request): JsonResponse
    {
        $data = $request->validated();

        $record = $this->emailOtpService->verify($data['otp_token'], $data['code'], 'login');
        $user = $record->user;

        if ($user === null || strtolower($data['email']) !== strtolower($record->email)) {
            throw ValidationException::withMessages([
                'code' => ['Invalid or expired verification code.'],
            ]);
        }

        if ($user->is_blocked) {
            throw ValidationException::withMessages([
                'email' => ['This account is blocked.'],
            ]);
        }

        if ($user->hasAnyRole([RoleName::Superadmin->value, RoleName::Admin->value])) {
            return response()->json([
                'message' => 'Admin accounts can only sign in on the web portal.',
            ], 403);
        }

        if (! $user->hasVerifiedEmail()) {
            $user->forceFill(['email_verified_at' => now()])->save();
        }

        $user->forceFill(['last_seen_at' => now()])->save();

        $tokens = $this->authTokenService->issue($user, $request);

        return response()->json([
            ...$tokens,
            'user' => new UserResource($user->load('roles')),
        ]);
    }

    public function me(Request $request): JsonResponse
    {
        return response()->json([
            'user' => new UserResource($request->user()->load('roles')),
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $data = $request->validate([
            'refresh_token' => ['nullable', 'string'],
            'logout_all' => ['nullable', 'boolean'],
        ]);

        $request->user()->currentAccessToken()?->delete();

        if (($data['logout_all'] ?? false) === true) {
            RefreshToken::query()
                ->where('user_id', $request->user()->id)
                ->whereNull('revoked_at')
                ->update(['revoked_at' => now()]);

            $request->user()->tokens()->delete();
        }

        if (! empty($data['refresh_token'])) {
            RefreshToken::query()
                ->where('user_id', $request->user()->id)
                ->where('token_hash', hash('sha256', $data['refresh_token']))
                ->whereNull('revoked_at')
                ->update(['revoked_at' => now()]);
        }

        return response()->json(['message' => 'Logged out successfully.']);
    }
}
