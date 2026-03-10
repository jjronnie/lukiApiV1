<?php

namespace App\Http\Controllers\Api\V1\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\User\CompleteCustomerProfileRequest;
use App\Http\Resources\UserResource;
use Illuminate\Http\JsonResponse;

class CustomerProfileController extends Controller
{
    public function upsert(CompleteCustomerProfileRequest $request): JsonResponse
    {
        $user = $request->user();
        $data = $request->validated();

        $user->update([
            'phone' => $data['phone'],
            'phone_country_code' => $data['phone_country_code'],
            'phone_local_number' => $data['phone_local_number'],
            'heard_about_source' => $data['heard_about_source'] ?? null,
            'heard_about_other' => $data['heard_about_source'] === 'other' ? ($data['heard_about_other'] ?? null) : null,
            'profile_completed_at' => now(),
        ]);

        return response()->json([
            'message' => 'Profile completed successfully.',
            'user' => new UserResource($user->load([
                'roles',
                'identityVerification',
                'providerProfile',
                'providerProfile.providerServices.service.category',
                'providerProfile.providerServices.eligibleTiers',
            ])),
        ]);
    }
}
