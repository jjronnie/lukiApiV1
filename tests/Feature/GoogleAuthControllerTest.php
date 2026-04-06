<?php

use App\Services\GoogleIdTokenVerifier;
use Database\Seeders\RolesAndPermissionsSeeder;

it('accepts google tokens issued for the configured web client audience', function () {
    $this->seed(RolesAndPermissionsSeeder::class);
    $idToken = 'web-token-1234567890';

    config()->set('services.google.server_client_id', 'server-client-id');
    config()->set('services.google.web_client_id', 'web-client-id');
    config()->set('services.google.client_id', 'mobile-client-id');

    $verifier = new class extends GoogleIdTokenVerifier
    {
        /** @var array<int, string> */
        public array $receivedAudiences = [];

        /**
         * @return array<int, string>
         */
        public function configuredAudiences(): array
        {
            return ['server-client-id', 'web-client-id', 'mobile-client-id'];
        }

        /**
         * @param  array<int, string>  $allowedAudiences
         * @return array<string, mixed>|null
         */
        public function verify(string $idToken, array $allowedAudiences): ?array
        {
            $this->receivedAudiences = $allowedAudiences;

            if ($idToken !== 'web-token-1234567890') {
                return null;
            }

            return [
                'aud' => 'web-client-id',
                'sub' => 'google-user-1',
                'email' => 'customer@example.com',
                'email_verified' => true,
                'name' => 'Google Customer',
            ];
        }
    };

    $this->app->instance(GoogleIdTokenVerifier::class, $verifier);

    $response = $this->postJson('/api/v1/auth/google', [
        'app_type' => 'customer',
        'id_token' => $idToken,
    ]);

    $response
        ->assertSuccessful()
        ->assertJsonPath('user.email', 'customer@example.com');

    expect($verifier->receivedAudiences)->toBe([
        'server-client-id',
        'web-client-id',
        'mobile-client-id',
    ]);

    $this->assertDatabaseHas('users', [
        'email' => 'customer@example.com',
        'google_id' => 'google-user-1',
        'signup_method' => 'google',
    ]);
});
