<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Enums\MobileAppType;
use App\Enums\ProviderVerificationStatus;
use App\Enums\RoleName;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Auth\LoginRequest;
use App\Http\Requests\Api\V1\Auth\RefreshTokenRequest;
use App\Http\Requests\Api\V1\Auth\RegisterRequest;
use App\Http\Requests\Api\V1\Auth\ResendOtpRequest;
use App\Http\Requests\Api\V1\Auth\VerifyEmailOtpRequest;
use App\Http\Requests\Api\V1\Auth\VerifyLoginOtpRequest;
use App\Http\Resources\UserResource;
use App\Models\ProviderProfile;
use App\Models\RefreshToken;
use App\Models\User;
use App\Services\AuthTokenService;
use App\Services\EmailOtpService;
use App\Services\UserEmailPreferenceService;
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
        private readonly UserEmailPreferenceService $userEmailPreferenceService,
    ) {}

    public function register(RegisterRequest $request): JsonResponse
    {
        $data = $request->validated();
        $appType = MobileAppType::fromRequest($request);

        $firstName = trim((string) ($data['first_name'] ?? ''));
        $lastName = trim((string) ($data['last_name'] ?? ''));
        $name = User::combineName($firstName, $lastName);

        if ($name === '') {
            [$firstName, $lastName] = User::splitName(
                Str::of($data['email'])->before('@')->replace('.', ' ')->title()->value()
            );
            $name = User::combineName($firstName, $lastName);
        }

        $user = User::query()->create([
            'first_name' => $firstName,
            'last_name' => $lastName,
            'name' => $name,
            'email' => strtolower($data['email']),
            'phone' => $data['phone'] ?? null,
            'password' => $data['password'],
        ]);

        $user->assignRole($appType->registrationRole());

        $otp = $this->emailOtpService->issue($user, 'email_verification', $appType);

        return response()->json([
            'message' => 'Verification code sent to email.',
            'email' => $user->email,
            ...$otp,
        ], 202);
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $data = $request->validated();
        $appType = MobileAppType::fromRequest($request);

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

        if ($error = $this->mobileAccessErrorResponse($user, $appType)) {
            return $error;
        }

        $otp = $this->emailOtpService->issue($user, 'login', $appType);

        return response()->json([
            'message' => 'Verification code sent to email.',
            'email' => $user->email,
            ...$otp,
        ], 202);
    }

    public function refresh(RefreshTokenRequest $request): JsonResponse
    {
        $appType = MobileAppType::fromRequest($request);
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

        if ($error = $this->mobileAccessErrorResponse($user, $appType)) {
            return $error;
        }

        $user = $this->prepareUserForMobileResponse($user, $appType);

        $tokens = $this->authTokenService->issue($user, $request);

        $user = $this->prepareUserForMobileResponse($user, $appType);

        return response()->json([
            ...$tokens,
            'user' => new UserResource($user->load($this->userRelations())),
        ]);
    }

    public function verifyEmailOtp(VerifyEmailOtpRequest $request): JsonResponse
    {
        $data = $request->validated();
        $appType = MobileAppType::fromRequest($request);
        $email = strtolower((string) ($data['email'] ?? ''));

        $record = $this->emailOtpService->verify(
            $data['otp_token'],
            $data['code'],
            'email_verification',
            $appType,
        );
        $user = $record->user;

        if ($user === null || ($email !== '' && $email !== strtolower($record->email))) {
            throw ValidationException::withMessages([
                'code' => ['Invalid or expired verification code.'],
            ]);
        }

        if ($error = $this->mobileAccessErrorResponse($user, $appType)) {
            return $error;
        }

        if (! $user->hasVerifiedEmail()) {
            $user->forceFill(['email_verified_at' => now()])->save();
        }

        $user->forceFill(['last_seen_at' => now()])->save();

        $tokens = $this->authTokenService->issue($user, $request);

        $user = $this->prepareUserForMobileResponse($user, $appType);

        return response()->json([
            ...$tokens,
            'user' => new UserResource($user->load($this->userRelations())),
        ]);
    }

    public function verifyLoginOtp(VerifyLoginOtpRequest $request): JsonResponse
    {
        $data = $request->validated();
        $appType = MobileAppType::fromRequest($request);
        $email = strtolower((string) ($data['email'] ?? ''));

        $record = $this->emailOtpService->verify(
            $data['otp_token'],
            $data['code'],
            'login',
            $appType,
        );
        $user = $record->user;

        if ($user === null || ($email !== '' && $email !== strtolower($record->email))) {
            throw ValidationException::withMessages([
                'code' => ['Invalid or expired verification code.'],
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

        if (! $user->hasVerifiedEmail()) {
            $user->forceFill(['email_verified_at' => now()])->save();
        }

        $user->forceFill(['last_seen_at' => now()])->save();

        $tokens = $this->authTokenService->issue($user, $request);

        return response()->json([
            ...$tokens,
            'user' => new UserResource($user->load($this->userRelations())),
        ]);
    }

    public function resendOtp(ResendOtpRequest $request): JsonResponse
    {
        $data = $request->validated();
        $appType = MobileAppType::fromRequest($request);

        $purpose = match ($data['purpose']) {
            'register' => 'email_verification',
            'login' => 'login',
            'password_change' => 'password_change',
            default => 'password_reset',
        };

        $payload = $this->emailOtpService->resend(
            $data['otp_token'],
            $data['email'],
            $purpose,
            $appType,
        );

        return response()->json([
            'message' => 'A new verification code was sent to your email.',
            'email' => strtolower($data['email']),
            ...$payload,
        ], 202);
    }

    public function me(Request $request): JsonResponse
    {
        $user = $request->user();
        $appType = MobileAppType::fromRequest($request);
        $user = $this->prepareUserForMobileResponse($user, $appType);

        return response()->json([
            'user' => new UserResource($user->load($this->userRelations())),
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

    /**
     * @return array<int, string>
     */
    private function userRelations(): array
    {
        return [
            'roles',
            'identityVerification',
            'providerIdentityVerification',
            'emailPreference',
            'providerProfile',
            'providerProfile.availability',
            'providerProfile.wallet',
            'providerProfile.providerServices.service.category',
            'providerProfile.providerServices.eligibleTiers',
        ];
    }

    private function prepareUserForMobileResponse(User $user, MobileAppType $appType): User
    {
        $this->userEmailPreferenceService->ensureForUser($user);

        if ($appType === MobileAppType::Provider) {
            ProviderProfile::query()->firstOrCreate(
                ['user_id' => $user->id],
                [
                    'provider_type' => 'individual',
                    'display_name' => $user->name !== '' ? $user->name : $user->email,
                    'verification_status' => ProviderVerificationStatus::Pending,
                ],
            );
        }

        return $user->fresh() ?? $user;
    }
}
