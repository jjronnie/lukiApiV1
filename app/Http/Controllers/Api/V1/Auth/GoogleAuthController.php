<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Enums\MobileAppType;
use App\Enums\RoleName;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Auth\GoogleAuthRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Services\AuthTokenService;
use Google\Client as GoogleClient;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;
use Throwable;
use Illuminate\Validation\ValidationException;

class GoogleAuthController extends Controller
{
    public function __construct(
        private readonly AuthTokenService $authTokenService,
    ) {}

    public function login(GoogleAuthRequest $request): JsonResponse
    {
        $appType = MobileAppType::fromRequest($request);
        $idToken = $request->validated('id_token');
        $clientId = trim((string) config('services.google.server_client_id'));

        if ($clientId === '') {
            return response()->json([
                'message' => 'Google sign-in is not configured on the server.',
            ], 500);
        }

        try {
            $payload = (new GoogleClient(['client_id' => $clientId]))->verifyIdToken($idToken);
        } catch (Throwable) {
            $payload = null;
        }

        if (! is_array($payload) || ($payload['aud'] ?? null) !== $clientId) {
            throw ValidationException::withMessages([
                'id_token' => ['The Google sign-in token is invalid.'],
            ]);
        }

        $email = strtolower((string) ($payload['email'] ?? ''));
        $googleId = trim((string) ($payload['sub'] ?? ''));
        $emailVerified = $payload['email_verified'] ?? false;

        if ($googleId === '' || $email === '') {
            throw ValidationException::withMessages([
                'id_token' => ['The Google sign-in token is invalid.'],
            ]);
        }

        if ($emailVerified !== true && (string) $emailVerified !== 'true') {
            throw ValidationException::withMessages([
                'id_token' => ['Google account email is not verified.'],
            ]);
        }

        $name = trim((string) ($payload['name'] ?? ''));
        if ($name === '') {
            $name = Str::of($email)->before('@')->replace('.', ' ')->title()->value();
        }
        [$firstName, $lastName] = User::splitName($name);

        $user = User::query()->where('google_id', $googleId)->first();

        if ($user === null) {
            $emailMatchedUser = User::query()->where('email', $email)->first();

            if ($emailMatchedUser !== null) {
                if ($emailMatchedUser->is_blocked) {
                    throw ValidationException::withMessages([
                        'email' => ['This account is blocked.'],
                    ]);
                }

                if ($error = $this->mobileAccessErrorResponse($emailMatchedUser, $appType)) {
                    return $error;
                }
            }

            $user = $emailMatchedUser;

            if ($user !== null) {
                $user->forceFill([
                    'first_name' => $user->first_name ?: $firstName,
                    'last_name' => $user->last_name ?: $lastName,
                    'name' => $user->name ?: User::combineName($firstName, $lastName),
                    'google_id' => $googleId,
                    'signup_method' => 'google',
                    'email_verified_at' => $user->email_verified_at ?? now(),
                ])->save();
            }
        }

        if ($user === null) {
            $user = User::query()->create([
                'first_name' => $firstName,
                'last_name' => $lastName,
                'name' => $name,
                'email' => $email,
                'google_id' => $googleId,
                'signup_method' => 'google',
                'email_verified_at' => now(),
                'password' => Str::random(15),
            ]);
            $user->assignRole($appType->registrationRole());
        } elseif (! $user->hasAnyRole([
            RoleName::User->value,
            RoleName::Provider->value,
            RoleName::Admin->value,
            RoleName::Superadmin->value,
        ])) {
            $user->assignRole($appType->registrationRole());
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
            'first_name' => $user->first_name ?: $firstName,
            'last_name' => $user->last_name ?: $lastName,
            'name' => User::combineName(
                $user->first_name ?: $firstName,
                $user->last_name ?: $lastName,
            ),
            'google_id' => $user->google_id ?: $googleId,
            'signup_method' => 'google',
            'email_verified_at' => $user->email_verified_at ?? now(),
            'last_seen_at' => now(),
        ])->save();

        $tokens = $this->authTokenService->issue($user, $request);
        $user->load($this->userRelations());

        return response()->json([
            ...$tokens,
            'user' => new UserResource($user),
            'roles' => $user->roles->pluck('name')->values(),
        ]);
    }

    private function mobileAccessErrorResponse(User $user, MobileAppType $appType): ?JsonResponse
    {
        if (! $appType->allows($user)) {
            return response()->json([
                'message' => 'This account is not allowed in this application.',
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
            'providerProfile',
            'providerProfile.providerServices.service.category',
            'providerProfile.providerServices.eligibleTiers',
        ];
    }
}
