<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Auth\ChangePasswordRequest;
use App\Http\Requests\Api\V1\Auth\ForgotPasswordRequest;
use App\Http\Requests\Api\V1\Auth\ResetPasswordRequest;
use App\Models\User;
use App\Services\EmailOtpService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class PasswordController extends Controller
{
    public function __construct(private readonly EmailOtpService $emailOtpService) {}

    public function forgot(ForgotPasswordRequest $request): JsonResponse
    {
        $email = strtolower($request->validated('email'));
        $user = User::query()->where('email', $email)->first();

        if ($user !== null && ! $user->is_blocked) {
            $otp = $this->emailOtpService->issue($user, 'password_reset');
        } else {
            $otp = [
                'otp_token' => Str::random(40),
                'expires_in' => 600,
            ];
        }

        return response()->json([
            'message' => 'If the email exists, a verification code was sent.',
            'email' => $email,
            ...$otp,
        ], 202);
    }

    public function reset(ResetPasswordRequest $request): JsonResponse
    {
        $data = $request->validated();

        $record = $this->emailOtpService->verify($data['otp_token'], $data['code'], 'password_reset');

        if ($record->user === null || strtolower($record->email) !== strtolower($data['email'])) {
            throw ValidationException::withMessages([
                'code' => ['Invalid or expired verification code.'],
            ]);
        }

        $user = $record->user;

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
}
