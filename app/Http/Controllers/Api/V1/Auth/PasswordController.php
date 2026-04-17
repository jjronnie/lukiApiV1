<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Enums\MobileAppType;
use App\Enums\RoleName;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Auth\CompletePasswordChangeRequest;
use App\Http\Requests\Api\V1\Auth\ForgotPasswordRequest;
use App\Http\Requests\Api\V1\Auth\RequestPasswordChangeOtpRequest;
use App\Http\Requests\Api\V1\Auth\ResetPasswordRequest;
use App\Http\Requests\Api\V1\Auth\VerifyChangePasswordOtpRequest;
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

    private const PASSWORD_CHANGE_TOKEN_TTL_MINUTES = 10;

    public function __construct(private readonly EmailOtpService $emailOtpService) {}

    public function forgot(ForgotPasswordRequest $request): JsonResponse
    {
        $data = $request->validated();
        $appType = MobileAppType::fromRequest($request);
        $identityField = $this->resolveIdentityField($data);
        $identifier = (string) $data[$identityField];
        $user = User::query()->where($identityField, $identifier)->first();

        if ($user === null) {
            throw ValidationException::withMessages([
                $identityField => ['We could not start password recovery for those details.'],
            ]);
        }

        if ($user->is_blocked) {
            throw ValidationException::withMessages([
                $identityField => ['This account is blocked.'],
            ]);
        }

        if ($error = $this->mobileAccessErrorResponse($user, $appType)) {
            return $error;
        }

        $otp = $this->emailOtpService->issue(
            $user,
            'password_reset',
            $appType,
            $identityField,
            $identifier,
        );

        return response()->json([
            'message' => $this->otpDispatchMessage($otp['channel']),
            ...$this->otpChallengePayload($otp),
            ...$otp,
        ], 202);
    }

    public function verify(VerifyPasswordResetCodeRequest $request): JsonResponse
    {
        $data = $request->validated();
        $appType = MobileAppType::fromRequest($request);
        $email = strtolower((string) ($data['email'] ?? ''));
        $phone = (string) ($data['phone'] ?? '');

        $record = $this->emailOtpService->verify(
            $data['otp_token'],
            $data['code'],
            'password_reset',
            $appType,
        );

        if ($record->user === null || ! $this->matchesOtpIdentifier($record->email, $email, $phone)) {
            throw ValidationException::withMessages([
                'code' => ['Invalid or expired verification code.'],
            ]);
        }

        $user = $record->user;

        if ($user->is_blocked) {
            throw ValidationException::withMessages([
                $this->resolveIdentityField($data) => ['This account is blocked.'],
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
                'identifier' => $record->email,
                'channel' => $this->channelForIdentifier($record->email),
                'app_type' => $appType->value,
            ],
            now()->addMinutes(self::PASSWORD_RESET_TOKEN_TTL_MINUTES),
        );

        return response()->json([
            'message' => 'Code verified successfully.',
            ...$this->identifierPayload($record->email),
            'reset_token' => $resetToken,
            'expires_in' => self::PASSWORD_RESET_TOKEN_TTL_MINUTES * 60,
        ]);
    }

    public function reset(ResetPasswordRequest $request): JsonResponse
    {
        $data = $request->validated();
        $appType = MobileAppType::fromRequest($request);
        $identityField = $this->resolveIdentityField($data);
        $identifier = (string) $data[$identityField];
        $user = null;

        if (! empty($data['reset_token'])) {
            $payload = Cache::pull($this->passwordResetCacheKey($data['reset_token']));

            if (! is_array($payload) ||
                ($payload['app_type'] ?? null) !== $appType->value ||
                ($payload['channel'] ?? null) !== $identityField ||
                (string) ($payload['identifier'] ?? '') !== $identifier) {
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

            if ($record->user === null || ! $this->matchesOtpIdentifier(
                $record->email,
                strtolower((string) ($data['email'] ?? '')),
                (string) ($data['phone'] ?? ''),
            )) {
                throw ValidationException::withMessages([
                    'code' => ['Invalid or expired verification code.'],
                ]);
            }

            $user = $record->user;
        }

        if ($user->is_blocked) {
            throw ValidationException::withMessages([
                $identityField => ['This account is blocked.'],
            ]);
        }

        if ($error = $this->mobileAccessErrorResponse($user, $appType)) {
            return $error;
        }

        $updates = [
            'password' => $data['password'],
            'remember_token' => Str::random(60),
        ];

        if ($identityField === 'email' && $user->email_verified_at === null) {
            $updates['email_verified_at'] = now();
        }

        if ($identityField === 'phone' && $user->phone_verified_at === null) {
            $updates['phone_verified_at'] = now();
        }

        $user->forceFill($updates)->save();

        $user->tokens()->delete();
        $user->refreshTokens()->update(['revoked_at' => now()]);

        return response()->json(['message' => 'Password reset successful.']);
    }

    public function requestChange(RequestPasswordChangeOtpRequest $request): JsonResponse
    {
        $data = $request->validated();
        $appType = MobileAppType::fromRequest($request);
        $user = $request->user();

        if ($user->is_blocked) {
            throw ValidationException::withMessages([
                'current_password' => ['This account is blocked.'],
            ]);
        }

        if ($error = $this->mobileAccessErrorResponse($user, $appType)) {
            return $error;
        }

        if (! Hash::check($data['current_password'], $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => ['Current password is incorrect.'],
            ]);
        }

        $otp = $this->emailOtpService->issue($user, 'password_change', $appType);

        return response()->json([
            'message' => $this->otpDispatchMessage($otp['channel']),
            ...$this->otpChallengePayload($otp),
            ...$otp,
        ], 202);
    }

    public function verifyChange(VerifyChangePasswordOtpRequest $request): JsonResponse
    {
        $data = $request->validated();
        $appType = MobileAppType::fromRequest($request);
        $user = $request->user();

        if ($user->is_blocked) {
            throw ValidationException::withMessages([
                'code' => ['This account is blocked.'],
            ]);
        }

        if ($error = $this->mobileAccessErrorResponse($user, $appType)) {
            return $error;
        }

        $record = $this->emailOtpService->verify(
            $data['otp_token'],
            $data['code'],
            'password_change',
            $appType,
        );

        if ($record->user === null || $record->user->isNot($user)) {
            throw ValidationException::withMessages([
                'code' => ['Invalid or expired verification code.'],
            ]);
        }

        $changeToken = Str::random(40);

        Cache::put(
            $this->passwordChangeCacheKey($changeToken),
            [
                'user_id' => $user->id,
                'email' => strtolower($user->email),
                'app_type' => $appType->value,
            ],
            now()->addMinutes(self::PASSWORD_CHANGE_TOKEN_TTL_MINUTES),
        );

        return response()->json([
            'message' => 'Code verified successfully.',
            ...$this->identifierPayload($record->email),
            'change_token' => $changeToken,
            'expires_in' => self::PASSWORD_CHANGE_TOKEN_TTL_MINUTES * 60,
        ]);
    }

    public function change(CompletePasswordChangeRequest $request): JsonResponse
    {
        $data = $request->validated();
        $appType = MobileAppType::fromRequest($request);
        $user = $request->user();

        if ($user->is_blocked) {
            throw ValidationException::withMessages([
                'password' => ['This account is blocked.'],
            ]);
        }

        if ($error = $this->mobileAccessErrorResponse($user, $appType)) {
            return $error;
        }

        $payload = Cache::pull($this->passwordChangeCacheKey($data['change_token']));

        if (! is_array($payload) ||
            ($payload['app_type'] ?? null) !== $appType->value ||
            ($payload['user_id'] ?? null) !== $user->id ||
            strtolower((string) ($payload['email'] ?? '')) !== strtolower($user->email)) {
            throw ValidationException::withMessages([
                'change_token' => ['Password change session has expired. Please verify your code again.'],
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

    private function passwordChangeCacheKey(string $changeToken): string
    {
        return 'password_change:'.hash('sha256', $changeToken);
    }

    /**
     * @param  array<string, mixed>  $otp
     * @return array{email:?string,phone:?string}
     */
    private function otpChallengePayload(array $otp): array
    {
        return [
            'email' => $otp['channel'] === 'email' ? $otp['destination'] : null,
            'phone' => $otp['channel'] === 'phone' ? $otp['destination'] : null,
        ];
    }

    /**
     * @return array{email:?string,phone:?string}
     */
    private function identifierPayload(string $identifier): array
    {
        return [
            'email' => $this->channelForIdentifier($identifier) === 'email' ? $identifier : null,
            'phone' => $this->channelForIdentifier($identifier) === 'phone' ? $identifier : null,
        ];
    }

    private function otpDispatchMessage(string $channel): string
    {
        return $channel === 'phone'
            ? 'Verification code sent to phone number.'
            : 'Verification code sent to email address.';
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function resolveIdentityField(array $data): string
    {
        return filled($data['phone'] ?? null) ? 'phone' : 'email';
    }

    private function matchesOtpIdentifier(string $storedIdentifier, string $email, string $phone): bool
    {
        return ($email !== '' && $storedIdentifier === $email)
            || ($phone !== '' && $storedIdentifier === $phone);
    }

    private function channelForIdentifier(string $identifier): string
    {
        return preg_match('/^\+256\d{9}$/', $identifier) === 1 ? 'phone' : 'email';
    }
}
