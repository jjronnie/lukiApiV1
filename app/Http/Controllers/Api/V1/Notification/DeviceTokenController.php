<?php

namespace App\Http\Controllers\Api\V1\Notification;

use App\Enums\MobileAppType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Notification\DeleteDeviceTokenRequest;
use App\Http\Requests\Api\V1\Notification\RegisterDeviceTokenRequest;
use App\Models\UserDeviceToken;
use Illuminate\Http\JsonResponse;

class DeviceTokenController extends Controller
{
    public function store(RegisterDeviceTokenRequest $request): JsonResponse
    {
        $appType = MobileAppType::from($request->validated('app_type'));
        $token = $request->validated('token');

        if (! $appType->allows($request->user())) {
            return response()->json([
                'message' => $appType->mismatchMessage(),
            ], 403);
        }

        $deviceToken = UserDeviceToken::query()->updateOrCreate(
            ['token_hash' => UserDeviceToken::hashToken($token)],
            [
                'user_id' => $request->user()->id,
                'app_type' => $appType->value,
                'platform' => $request->validated('platform'),
                'token' => $token,
                'is_active' => true,
                'last_seen_at' => now(),
            ]
        );

        return response()->json([
            'message' => 'FCM token registered.',
            'device_token_id' => $deviceToken->id,
        ]);
    }

    public function destroy(DeleteDeviceTokenRequest $request): JsonResponse
    {
        $appType = MobileAppType::from($request->validated('app_type'));
        $token = $request->validated('token');

        if (! $appType->allows($request->user())) {
            return response()->json([
                'message' => $appType->mismatchMessage(),
            ], 403);
        }

        UserDeviceToken::query()
            ->where('user_id', $request->user()->id)
            ->where('app_type', $appType->value)
            ->where('token_hash', UserDeviceToken::hashToken($token))
            ->update([
                'is_active' => false,
                'updated_at' => now(),
            ]);

        return response()->json([
            'message' => 'FCM token deactivated.',
        ]);
    }
}
