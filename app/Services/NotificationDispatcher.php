<?php

namespace App\Services;

use App\Enums\MobileAppType;
use App\Models\NotificationRecord;
use App\Models\ProviderProfile;
use App\Models\User;
use App\Models\UserDeviceToken;
use Illuminate\Support\Collection;

class NotificationDispatcher
{
    public function __construct(
        private readonly FcmNotificationService $fcmNotificationService,
    ) {}

    /**
     * @param  array<string, scalar|null>  $payload
     * @return array{record: NotificationRecord, sent: int, failed: int}
     */
    public function sendToUser(
        User $user,
        MobileAppType $appType,
        string $type,
        string $title,
        string $body,
        array $payload = [],
        ?ProviderProfile $providerProfile = null,
    ): array {
        $record = NotificationRecord::query()->create([
            'user_id' => $user->id,
            'provider_profile_id' => $providerProfile?->id,
            'type' => $type,
            'title' => $title,
            'body' => $body,
            'payload' => $payload,
        ]);

        if (! $this->fcmNotificationService->isConfigured()) {
            return [
                'record' => $record,
                'sent' => 0,
                'failed' => 0,
            ];
        }

        $tokens = $user->deviceTokens()
            ->active()
            ->forAppType($appType->value)
            ->get();
        $sent = $this->deliverToTokens(
            $tokens,
            $title,
            $body,
            [
                'notification_id' => $record->id,
                'type' => $type,
                ...$payload,
            ]
        );

        return [
            'record' => $record,
            'sent' => $sent,
            'failed' => max(0, $tokens->count() - $sent),
        ];
    }

    /**
     * @param  Collection<int, UserDeviceToken>  $tokens
     * @param  array<string, scalar|null>  $payload
     */
    private function deliverToTokens(
        Collection $tokens,
        string $title,
        string $body,
        array $payload,
    ): int {
        $sent = 0;

        foreach ($tokens as $token) {
            $result = $this->fcmNotificationService->sendToDevice(
                $token,
                $title,
                $body,
                $payload,
            );

            if ($result['success']) {
                $sent++;
                $token->forceFill([
                    'is_active' => true,
                    'last_seen_at' => now(),
                ])->save();

                continue;
            }

            if ($result['deactivated']) {
                $token->forceFill(['is_active' => false])->save();
            }
        }

        return $sent;
    }
}
