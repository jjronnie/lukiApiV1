<?php

namespace App\Services;

use App\Models\IdempotencyKey;
use App\Models\User;

class IdempotencyService
{
    /**
     * @return array{replay: bool, response_code?: int, response_body?: array<string, mixed>}
     */
    public function check(User $user, string $scope, string $key, string $requestHash): array
    {
        $existing = IdempotencyKey::query()
            ->where('user_id', $user->id)
            ->where('scope', $scope)
            ->where('idempotency_key', $key)
            ->where('expires_at', '>', now())
            ->first();

        if ($existing === null) {
            return ['replay' => false];
        }

        if ($existing->request_hash !== $requestHash) {
            abort(422, 'Idempotency key already used with a different payload.');
        }

        return [
            'replay' => true,
            'response_code' => $existing->response_code,
            'response_body' => $existing->response_body,
        ];
    }

    /**
     * @param  array<string, mixed>  $responseBody
     */
    public function store(User $user, string $scope, string $key, string $requestHash, int $responseCode, array $responseBody, int $ttlMinutes = 60): void
    {
        IdempotencyKey::query()->updateOrCreate(
            [
                'user_id' => $user->id,
                'scope' => $scope,
                'idempotency_key' => $key,
            ],
            [
                'request_hash' => $requestHash,
                'response_code' => $responseCode,
                'response_body' => $responseBody,
                'expires_at' => now()->addMinutes($ttlMinutes),
            ]
        );
    }
}
