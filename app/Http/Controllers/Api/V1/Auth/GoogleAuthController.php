<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Enums\RoleName;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Auth\GoogleAuthRequest;
use App\Models\User;
use App\Services\EmailOtpService;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;

class GoogleAuthController extends Controller
{
    public function __construct(
        private readonly EmailOtpService $emailOtpService,
    ) {}

    /**
     * @throws ConnectionException
     */
    public function login(GoogleAuthRequest $request): JsonResponse
    {
        $idToken = $request->validated('id_token');

        $googleResponse = Http::timeout(10)
            ->acceptJson()
            ->get('https://oauth2.googleapis.com/tokeninfo', ['id_token' => $idToken]);

        if (! $googleResponse->successful()) {
            throw ValidationException::withMessages([
                'id_token' => ['Google token could not be verified.'],
            ]);
        }

        $payload = $googleResponse->json();
        $email = strtolower((string) ($payload['email'] ?? ''));

        if ($email === '' || (string) ($payload['email_verified'] ?? '') !== 'true') {
            throw ValidationException::withMessages([
                'id_token' => ['Google account email is not verified.'],
            ]);
        }

        $user = User::query()->firstOrCreate(
            ['email' => $email],
            [
                'name' => (string) ($payload['name'] ?? explode('@', $email)[0]),
                'password' => Hash::make(bin2hex(random_bytes(20))),
                'email_verified_at' => now(),
            ]
        );

        if ($user->hasAnyRole([RoleName::Superadmin->value, RoleName::Admin->value])) {
            return response()->json([
                'message' => 'Admin accounts can only sign in on the web portal.',
            ], 403);
        }

        if (! $user->hasRole(RoleName::User->value)) {
            $user->assignRole(RoleName::User->value);
        }

        if (! $user->hasVerifiedEmail()) {
            $user->forceFill(['email_verified_at' => now()])->save();
        }

        $otp = $this->emailOtpService->issue($user, 'login');

        return response()->json([
            'message' => 'Verification code sent to email.',
            'email' => $user->email,
            ...$otp,
        ], 202);
    }
}
