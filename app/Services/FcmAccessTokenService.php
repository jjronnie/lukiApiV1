<?php

namespace App\Services;

use Google\Client;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class FcmAccessTokenService
{
    private const CACHE_KEY = 'firebase:fcm:oauth-access-token';

    public function isConfigured(): bool
    {
        return trim((string) config('services.firebase.project_id')) !== ''
            && ($this->serviceAccountConfig() !== null);
    }

    public function getAccessToken(): ?string
    {
        if (! $this->isConfigured()) {
            return null;
        }

        return Cache::remember(self::CACHE_KEY, now()->addMinutes(50), function (): ?string {
            try {
                $client = new Client;
                $client->setAuthConfig($this->serviceAccountConfig());
                $client->setScopes(['https://www.googleapis.com/auth/firebase.messaging']);

                $token = $client->fetchAccessTokenWithAssertion();

                return $token['access_token'] ?? null;
            } catch (\Throwable $exception) {
                Log::warning('Unable to obtain Firebase access token.', [
                    'message' => $exception->getMessage(),
                ]);

                return null;
            }
        });
    }

    /**
     * @return array<string, mixed>|string|null
     */
    private function serviceAccountConfig(): array|string|null
    {
        $inlineJson = trim((string) config('services.firebase.service_account_json'));
        if ($inlineJson !== '') {
            $decoded = json_decode($inlineJson, true);

            if (is_array($decoded)) {
                return $decoded;
            }

            throw new RuntimeException('Firebase service account JSON is invalid.');
        }

        $path = trim((string) config('services.firebase.service_account_path'));
        if ($path === '') {
            return null;
        }

        if (! str_starts_with($path, DIRECTORY_SEPARATOR)) {
            $path = base_path($path);
        }

        return $path;
    }
}
