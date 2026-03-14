<?php

namespace App\Http\Controllers\Api\V1\User;

use App\Enums\UserIdentityVerificationStatus;
use App\Enums\VerificationSessionFlow;
use App\Enums\VerificationSessionStatus;
use App\Http\Controllers\Controller;
use App\Models\VerificationSession;
use App\Support\IdentityVerificationStatusPresenter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class UserIdentityVerificationSessionController extends Controller
{
    public function __construct(
        private readonly IdentityVerificationStatusPresenter $statusPresenter,
    ) {}

    public function store(Request $request): JsonResponse
    {
        $user = $request->user();
        $currentVerification = $user->identityVerification;

        if ($currentVerification?->status === UserIdentityVerificationStatus::Approved) {
            return response()->json([
                'message' => 'Your identity is already verified.',
            ], 422);
        }

        if ($currentVerification?->status === UserIdentityVerificationStatus::Pending) {
            return response()->json([
                'message' => 'Your verification is already under review.',
            ], 422);
        }

        $session = DB::transaction(function () use ($request, $user) {
            VerificationSession::query()
                ->where('user_id', $user->id)
                ->where('flow', VerificationSessionFlow::CustomerIdentity)
                ->where('status', VerificationSessionStatus::Open)
                ->where('expires_at', '<=', now())
                ->update([
                    'status' => VerificationSessionStatus::Expired,
                    'updated_at' => now(),
                ]);

            $reusableSession = VerificationSession::query()
                ->where('user_id', $user->id)
                ->where('flow', VerificationSessionFlow::CustomerIdentity)
                ->where('status', VerificationSessionStatus::Open)
                ->where('expires_at', '>', now())
                ->latest('created_at')
                ->first();

            if ($reusableSession !== null) {
                VerificationSession::query()
                    ->where('user_id', $user->id)
                    ->where('flow', VerificationSessionFlow::CustomerIdentity)
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
                'flow' => VerificationSessionFlow::CustomerIdentity,
                'status' => VerificationSessionStatus::Open,
                'expires_at' => now()->addMinutes(15),
                'ip_address' => $request->ip(),
                'user_agent' => (string) $request->userAgent(),
            ]);
        });

        return response()->json([
            'message' => 'Verification session ready.',
            'verification_url' => $session->signedUrl(),
            'expires_at' => $session->expires_at,
            'verification' => $this->statusPresenter->forUser($user->fresh('identityVerification')),
        ], 201);
    }
}
