<?php

namespace App\Http\Controllers\Api\V1\User;

use App\Enums\UserIdentityVerificationStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\User\SubmitUserIdentityVerificationRequest;
use App\Services\UserIdentityVerificationSubmissionService;
use App\Support\IdentityVerificationStatusPresenter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserIdentityVerificationController extends Controller
{
    public function __construct(
        private readonly UserIdentityVerificationSubmissionService $submissionService,
        private readonly IdentityVerificationStatusPresenter $statusPresenter,
    ) {}

    public function show(Request $request): JsonResponse
    {
        return response()->json([
            'verification' => $this->statusPresenter->forUser(
                $request->user()->loadMissing('identityVerification')
            ),
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

        $this->submissionService->submit($user, $request->validated());

        return response()->json([
            'message' => 'Verification submitted successfully.',
            'verification' => $this->statusPresenter->forUser(
                $user->fresh('identityVerification')
            ),
        ], 202);
    }
}
