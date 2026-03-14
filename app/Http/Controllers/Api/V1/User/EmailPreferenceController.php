<?php

namespace App\Http\Controllers\Api\V1\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\User\UpdateEmailPreferencesRequest;
use App\Services\UserEmailPreferenceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EmailPreferenceController extends Controller
{
    public function __construct(
        private readonly UserEmailPreferenceService $preferenceService,
    ) {}

    public function show(Request $request): JsonResponse
    {
        $preference = $this->preferenceService->ensureForUser($request->user());

        return response()->json([
            'preferences' => [
                'marketing_emails_enabled' => $preference->marketing_emails_enabled,
                'booking_emails_enabled' => $preference->booking_emails_enabled,
                'authentication_emails_enabled' => true,
            ],
        ]);
    }

    public function update(
        UpdateEmailPreferencesRequest $request,
    ): JsonResponse {
        $preference = $this->preferenceService->ensureForUser($request->user());
        $data = $request->validated();

        $preference->update([
            'marketing_emails_enabled' => $data['marketing_emails_enabled'] ?? $preference->marketing_emails_enabled,
            'booking_emails_enabled' => $data['booking_emails_enabled'] ?? $preference->booking_emails_enabled,
            'authentication_emails_enabled' => true,
        ]);

        return response()->json([
            'message' => 'Email preferences updated.',
            'preferences' => [
                'marketing_emails_enabled' => $preference->marketing_emails_enabled,
                'booking_emails_enabled' => $preference->booking_emails_enabled,
                'authentication_emails_enabled' => true,
            ],
        ]);
    }
}
