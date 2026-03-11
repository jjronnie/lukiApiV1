<?php

namespace App\Http\Controllers\Api\V1\User;

use App\Enums\UserIdentityVerificationStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\User\CompleteCustomerProfileRequest;
use App\Http\Resources\UserResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class CustomerProfileController extends Controller
{
    public function upsert(CompleteCustomerProfileRequest $request): JsonResponse
    {
        $user = $request->user();
        $data = $request->validated();
        $verificationStatus = $user->identityVerification?->status;
        $nextFirstName = trim((string) ($data['first_name'] ?? $user->first_name ?? ''));
        $nextLastName = trim((string) ($data['last_name'] ?? $user->last_name ?? ''));
        $nextName = \App\Models\User::combineName($nextFirstName, $nextLastName);
        $nameChanged = $nextName !== trim((string) $user->name);
        $heardAboutSourceProvided = array_key_exists('heard_about_source', $data);

        if ($nameChanged && $verificationStatus === UserIdentityVerificationStatus::Approved) {
            throw ValidationException::withMessages([
                'first_name' => ['Verified users cannot change their names.'],
            ]);
        }

        $user->update([
            'first_name' => $nextFirstName !== '' ? $nextFirstName : $user->first_name,
            'last_name' => $nextLastName !== '' ? $nextLastName : $user->last_name,
            'name' => $nextName !== '' ? $nextName : $user->name,
            'phone' => $data['phone'],
            'phone_country_code' => $data['phone_country_code'],
            'phone_local_number' => $data['phone_local_number'],
            'heard_about_source' => $heardAboutSourceProvided
                ? ($data['heard_about_source'] ?? null)
                : $user->heard_about_source,
            'heard_about_other' => $heardAboutSourceProvided
                ? (($data['heard_about_source'] ?? null) === 'other' ? ($data['heard_about_other'] ?? null) : null)
                : $user->heard_about_other,
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
