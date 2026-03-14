<?php

namespace App\Http\Controllers\Api\V1\Provider;

use App\Enums\ProviderVerificationStatus;
use App\Enums\VerificationSessionFlow;
use App\Enums\VerificationSessionStatus;
use App\Http\Controllers\Controller;
use App\Models\VerificationSession;
use App\Support\ProviderIdentityVerificationStatusPresenter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProviderVerificationController extends Controller
{
    public function __construct(
        private readonly ProviderIdentityVerificationStatusPresenter $statusPresenter,
    ) {}

    public function show(Request $request): JsonResponse
    {
        return response()->json([
            'verification' => $this->statusPresenter->forUser(
                $request->user()->load('providerIdentityVerification')
            ),
        ]);
    }

    public function storeSession(Request $request): JsonResponse
    {
        $user = $request->user();
        $profile = $user->providerProfile()->firstOrFail();
        $currentVerification = $user->providerIdentityVerification;

        if ($profile->onboarding_completed_at === null) {
            return response()->json([
                'message' => 'Complete onboarding before verification.',
            ], 422);
        }

        if ($currentVerification?->status === ProviderVerificationStatus::Approved) {
            return response()->json([
                'message' => 'Your provider identity is already verified.',
            ], 422);
        }

        if ($currentVerification?->status === ProviderVerificationStatus::Pending) {
            return response()->json([
                'message' => 'Your verification is already under review.',
            ], 422);
        }

        $session = DB::transaction(function () use ($request, $user) {
            VerificationSession::query()
                ->where('user_id', $user->id)
                ->where('flow', VerificationSessionFlow::ProviderIdentity)
                ->where('status', VerificationSessionStatus::Open)
                ->where('expires_at', '<=', now())
                ->update([
                    'status' => VerificationSessionStatus::Expired,
                    'updated_at' => now(),
                ]);

            $reusableSession = VerificationSession::query()
                ->where('user_id', $user->id)
                ->where('flow', VerificationSessionFlow::ProviderIdentity)
                ->where('status', VerificationSessionStatus::Open)
                ->where('expires_at', '>', now())
                ->latest('created_at')
                ->first();

            if ($reusableSession !== null) {
                VerificationSession::query()
                    ->where('user_id', $user->id)
                    ->where('flow', VerificationSessionFlow::ProviderIdentity)
                    ->where('status', VerificationSessionStatus::Open)
                    ->where('expires_at', '>', now())
                    ->whereKeyNot($reusableSession->id)
                    ->update([
                        'status' => VerificationSessionStatus::Cancelled,
                        'cancelled_at' => now(),
                        'updated_at' => now(),
                    ]);

                return $reusableSession;
            }

            return VerificationSession::query()->create([
                'user_id' => $user->id,
                'session_key' => Str::random(64),
                'flow' => VerificationSessionFlow::ProviderIdentity,
                'status' => VerificationSessionStatus::Open,
                'expires_at' => now()->addMinutes(15),
                'ip_address' => $request->ip(),
                'user_agent' => (string) $request->userAgent(),
            ]);
        });

        return response()->json([
            'message' => 'Provider verification session ready.',
            'verification_url' => $session->signedUrl(),
            'expires_at' => $session->expires_at,
            'verification' => $this->statusPresenter->forUser(
                $user->fresh('providerIdentityVerification')
            ),
        ], 201);
    }
}
