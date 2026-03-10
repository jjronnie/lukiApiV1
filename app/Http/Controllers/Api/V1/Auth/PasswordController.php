<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Enums\MobileAppType;
use App\Enums\RoleName;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Auth\ChangePasswordRequest;
use App\Http\Requests\Api\V1\Auth\ForgotPasswordRequest;
use App\Http\Requests\Api\V1\Auth\ResetPasswordRequest;
use App\Http\Requests\Api\V1\Auth\VerifyPasswordResetCodeRequest;
use App\Models\User;
use App\Services\EmailOtpService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class PasswordController extends Controller
{
    private const PASSWORD_RESET_TOKEN_TTL_MINUTES = 10;

    public function __construct(private readonly EmailOtpService $emailOtpService) {}

    public function forgot(ForgotPasswordRequest $request): JsonResponse
    {
        $appType = MobileAppType::fromRequest($request);
        $email = strtolower($request->validated('email'));
        $user = User::query()->where('email', $email)->first();

        if ($user === null) {
            throw ValidationException::withMessages([
                'email' => ['No account was found with that email address.'],
            ]);
        }

        if ($user->is_blocked) {
            throw ValidationException::withMessages([
                'email' => ['This account is blocked.'],
            ]);
        }

        if ($error = $this->mobileAccessErrorResponse($user, $appType)) {
            return $error;
        }

        $otp = $this->emailOtpService->issue($user, 'password_reset', $appType);

        return response()->json([
            'message' => 'Verification code sent to email.',
            'email' => $email,
            ...$otp,
        ], 202);
    }

    public function verify(VerifyPasswordResetCodeRequest $request): JsonResponse
    {
        $data = $request->validated();
        $appType = MobileAppType::fromRequest($request);
        $email = strtolower((string) ($data['email'] ?? ''));

        $record = $this->emailOtpService->verify(
            $data['otp_token'],
            $data['code'],
            'password_reset',
            $appType,
        );

        if ($record->user === null || ($email !== '' && strtolower($record->email) !== $email)) {
            throw ValidationException::withMessages([
                'code' => ['Invalid or expired verification code.'],
            ]);
        }

        $user = $record->user;

        if ($user->is_blocked) {
            throw ValidationException::withMessages([
                'email' => ['This account is blocked.'],
            ]);
        }

        if ($error = $this->mobileAccessErrorResponse($user, $appType)) {
            return $error;
        }

        $resetToken = Str::random(40);

        Cache::put(
            $this->passwordResetCacheKey($resetToken),
            [
                'user_id' => $user->id,
                'email' => strtolower($user->email),
                'app_type' => $appType->value,
            ],
            now()->addMinutes(self::PASSWORD_RESET_TOKEN_TTL_MINUTES),
        );

        return response()->json([
            'message' => 'Code verified successfully.',
            'email' => strtolower($user->email),
            'reset_token' => $resetToken,
            'expires_in' => self::PASSWORD_RESET_TOKEN_TTL_MINUTES * 60,
        ]);
    }

    public function reset(ResetPasswordRequest $request): JsonResponse
    {
        $data = $request->validated();
        $appType = MobileAppType::fromRequest($request);
        $user = null;

        if (! empty($data['reset_token'])) {
            $payload = Cache::pull($this->passwordResetCacheKey($data['reset_token']));

            if (! is_array($payload) ||
                ($payload['app_type'] ?? null) !== $appType->value ||
                strtolower((string) ($payload['email'] ?? '')) !== strtolower($data['email'])) {
                throw ValidationException::withMessages([
                    'reset_token' => ['Reset session has expired. Please verify your code again.'],
                ]);
            }

            $userId = $payload['user_id'] ?? null;
            $user = $userId === null ? null : User::query()->find($userId);

            if ($user === null) {
                throw ValidationException::withMessages([
                    'reset_token' => ['Reset session has expired. Please verify your code again.'],
                ]);
            }
        } else {
            $record = $this->emailOtpService->verify(
                $data['otp_token'],
                $data['code'],
                'password_reset',
                $appType,
            );

            if ($record->user === null || strtolower($record->email) !== strtolower($data['email'])) {
                throw ValidationException::withMessages([
                    'code' => ['Invalid or expired verification code.'],
                ]);
            }

            $user = $record->user;
        }

        if ($user->is_blocked) {
            throw ValidationException::withMessages([
                'email' => ['This account is blocked.'],
            ]);
        }

        if ($error = $this->mobileAccessErrorResponse($user, $appType)) {
            return $error;
        }

        $user->forceFill([
            'password' => $data['password'],
            'remember_token' => Str::random(60),
            'email_verified_at' => $user->email_verified_at ?? now(),
        ])->save();

        $user->tokens()->delete();
        $user->refreshTokens()->update(['revoked_at' => now()]);

        return response()->json(['message' => 'Password reset successful.']);
    }

    public function change(ChangePasswordRequest $request): JsonResponse
    {
        $data = $request->validated();
        $user = $request->user();

        if (! Hash::check($data['current_password'], $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => ['Current password is incorrect.'],
            ]);
        }

        $user->forceFill([
            'password' => $data['password'],
            'remember_token' => Str::random(60),
        ])->save();

        $user->tokens()->delete();
        $user->refreshTokens()->update(['revoked_at' => now()]);

        return response()->json(['message' => 'Password updated successfully.']);
    }

    private function mobileAccessErrorResponse(User $user, MobileAppType $appType): ?JsonResponse
    {
        if ($user->hasAnyRole([RoleName::Superadmin->value, RoleName::Admin->value])) {
            return response()->json([
                'message' => 'Admin and superadmin accounts can only sign in on the web portal.',
            ], 403);
        }

        if (! $appType->allows($user)) {
            return response()->json([
                'message' => $appType->mismatchMessage(),
            ], 403);
        }

        return null;
    }

    private function passwordResetCacheKey(string $resetToken): string
    {
        return 'password_reset:'.hash('sha256', $resetToken);
    }
}
