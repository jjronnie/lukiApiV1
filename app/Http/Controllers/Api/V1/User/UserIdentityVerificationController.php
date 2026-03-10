<?php

namespace App\Http\Controllers\Api\V1\User;

use App\Enums\UserIdentityVerificationStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\User\SubmitUserIdentityVerificationRequest;
use App\Http\Resources\UserIdentityVerificationResource;
use App\Jobs\ProcessIdentityVerificationImage;
use App\Models\UserIdentityVerification;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class UserIdentityVerificationController extends Controller
{
    public function show(): JsonResponse
    {
        $verification = request()->user()->identityVerification;

        if ($verification === null) {
            return response()->json([
                'verification' => [
                    'status' => 'not_submitted',
                    'is_pending' => false,
                    'is_verified' => false,
                    'can_retry' => true,
                    'rejection_reason' => null,
                ],
            ]);
        }

        return response()->json([
            'verification' => new UserIdentityVerificationResource($verification),
        ]);
    }

    public function store(SubmitUserIdentityVerificationRequest $request): JsonResponse
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

        $data = $request->validated();

        $verification = DB::transaction(function () use ($user, $data) {
            /** @var UserIdentityVerification $verification */
            $verification = UserIdentityVerification::query()->updateOrCreate(
                ['user_id' => $user->id],
                [
                    'id_type' => $data['id_type'],
                    'status' => UserIdentityVerificationStatus::Pending,
                    'submitted_at' => now(),
                    'reviewed_by' => null,
                    'reviewed_at' => null,
                    'verified_at' => null,
                    'rejection_reason' => null,
                ]
            );

            foreach (['selfie', 'id_front', 'id_back'] as $collection) {
                $path = $data[$collection]->store('identity-verifications/tmp', 'local');

                ProcessIdentityVerificationImage::dispatch(
                    $verification->id,
                    $path,
                    $collection,
                    $data[$collection]->getClientOriginalName(),
                )->afterCommit();
            }

            return $verification;
        });

        return response()->json([
            'message' => 'Verification submitted successfully.',
            'verification' => new UserIdentityVerificationResource($verification),
        ], 202);
    }
}
