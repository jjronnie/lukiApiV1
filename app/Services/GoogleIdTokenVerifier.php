<?php

namespace App\Services;

use Google\Client as GoogleClient;
use Throwable;

class GoogleIdTokenVerifier
{
    /**
     * @return array<int, string>
     */
    public function configuredAudiences(): array
    {
        return collect([
            (string) config('services.google.server_client_id'),
            (string) config('services.google.web_client_id'),
            (string) config('services.google.client_id'),
        ])
            ->map(fn (string $audience): string => trim($audience))
            ->filter(fn (string $audience): bool => $audience !== '')
            ->unique()
            ->values()
            ->all();
    }

    /**
     * @param  array<int, string>  $allowedAudiences
     * @return array<string, mixed>|null
     */
    public function verify(string $idToken, array $allowedAudiences): ?array
    {
        try {
            $payload = (new GoogleClient)->verifyIdToken($idToken);
        } catch (Throwable) {
            return null;
        }

        if (! is_array($payload)) {
            return null;
        }

        $audience = $payload['aud'] ?? null;
        if (! is_string($audience) || ! in_array($audience, $allowedAudiences, true)) {
            return null;
        }

        return $payload;
    }
}
