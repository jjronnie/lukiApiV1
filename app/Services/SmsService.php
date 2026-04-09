<?php

namespace App\Services;

use AfricasTalking\SDK\AfricasTalking;
use RuntimeException;

class SmsService
{
    public function send(string $to, string $message): void
    {
        $provider = strtolower((string) config('services.sms.provider', ''));

        if ($provider !== 'africastalking') {
            throw new RuntimeException('SMS provider is not configured.');
        }

        $username = trim((string) config('services.sms.username'));
        $apiKey = trim((string) config('services.sms.api_key'));
        $senderId = trim((string) config('services.sms.sender_id'));

        if ($username === '' || $apiKey === '') {
            throw new RuntimeException('SMS credentials are missing.');
        }

        $africasTalking = new AfricasTalking($username, $apiKey);
        $sms = $africasTalking->sms();

        $payload = [
            'to' => [$to],
            'message' => $message,
        ];

        if ($senderId !== '') {
            $payload['from'] = $senderId;
        }

        $sms->send($payload);
    }
}
