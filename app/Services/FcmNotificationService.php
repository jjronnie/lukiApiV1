<?php

namespace App\Services;

use App\Models\UserDeviceToken;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FcmNotificationService
{
    public function __construct(
        private readonly FcmAccessTokenService $accessTokenService,
    ) {}

    public function isConfigured(): bool
    {
        return $this->accessTokenService->isConfigured();
    }

    /**
     * @param  array<string, scalar|null>  $data
     * @return array{success: bool, deactivated: bool, response: array<string, mixed>}
     */
    public function sendToDevice(
        UserDeviceToken $deviceToken,
        string $title,
        string $body,
        array $data = [],
    ): array {
        $accessToken = $this->accessTokenService->getAccessToken();
        $projectId = trim((string) config('services.firebase.project_id'));

        if ($accessToken === null || $projectId === '') {
            return [
                'success' => false,
                'deactivated' => false,
                'response' => ['message' => 'Firebase is not configured.'],
            ];
        }

        $response = Http::withToken($accessToken)
            ->acceptJson()
            ->post(
                sprintf('https://fcm.googleapis.com/v1/projects/%s/messages:send', $projectId),
                [
                    'message' => [
                        'token' => $deviceToken->token,
                        'notification' => [
                            'title' => $title,
                            'body' => $body,
                        ],
                        'data' => $this->stringifyPayload($data),
                        'android' => [
                            'priority' => 'high',
                            'notification' => [
                                'channel_id' => 'luki_general',
                                'icon' => 'ic_launcher',
                                'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                            ],
                        ],
                        'apns' => [
                            'headers' => [
                                'apns-priority' => '10',
                            ],
                            'payload' => [
                                'aps' => [
                                    'sound' => 'default',
                                ],
                            ],
                        ],
                    ],
                ]
            );

        if ($response->successful()) {
            return [
                'success' => true,
                'deactivated' => false,
                'response' => $response->json() ?? [],
            ];
        }

        $payload = $response->json() ?? [];
        $deactivate = $this->shouldDeactivateToken($payload);

        Log::warning('Firebase notification send failed.', [
            'device_token_id' => $deviceToken->id,
            'status' => $response->status(),
            'response' => $payload,
        ]);

        return [
            'success' => false,
            'deactivated' => $deactivate,
            'response' => $payload,
        ];
    }

    /**
     * @param  array<string, scalar|null>  $payload
     * @return array<string, string>
     */
    private function stringifyPayload(array $payload): array
    {
        $stringified = [];

        foreach ($payload as $key => $value) {
            if ($value === null) {
                continue;
            }

            $stringified[$key] = (string) $value;
        }

        return $stringified;
    }

    /**
     * @param  array<string, mixed>  $responsePayload
     */
    private function shouldDeactivateToken(array $responsePayload): bool
    {
        $details = data_get($responsePayload, 'error.details', []);
        if (! is_array($details)) {
            return false;
        }

        foreach ($details as $detail) {
            if (! is_array($detail)) {
                continue;
            }

            $errorCode = strtoupper((string) ($detail['errorCode'] ?? ''));
            if (in_array($errorCode, ['UNREGISTERED', 'INVALID_ARGUMENT'], true)) {
                return true;
            }
        }

        return false;
    }
}
