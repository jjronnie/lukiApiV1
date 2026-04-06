<?php

use App\Enums\ProviderVerificationStatus;
use App\Enums\RoleName;
use App\Enums\VerificationSessionFlow;
use App\Enums\VerificationSessionStatus;
use App\Models\ProviderIdentityVerification;
use App\Models\ProviderProfile;
use App\Models\User;
use App\Models\VerificationSession;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Support\Facades\URL;

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
});

it('returns an unverified provider status even when a browser session is open', function () {
    ['user' => $providerUser] = createVerificationProvider();

    VerificationSession::query()->create([
        'user_id' => $providerUser->id,
        'session_key' => str_repeat('a', 64),
        'flow' => VerificationSessionFlow::ProviderIdentity,
        'status' => VerificationSessionStatus::Open,
        'expires_at' => now()->addMinutes(15),
    ]);

    $response = $this->actingAs($providerUser, 'sanctum')
        ->getJson('/api/v1/provider/verification');

    $response
        ->assertOk()
        ->assertJsonPath('verification.status', 'unverified')
        ->assertJsonPath('verification.is_pending', false)
        ->assertJsonPath('verification.is_verified', false);
});

it('shows the simplified verification form copy in the browser flow', function () {
    ['user' => $providerUser] = createVerificationProvider();

    $session = VerificationSession::query()->create([
        'user_id' => $providerUser->id,
        'session_key' => str_repeat('b', 64),
        'flow' => VerificationSessionFlow::ProviderIdentity,
        'status' => VerificationSessionStatus::Open,
        'expires_at' => now()->addMinutes(15),
    ]);

    $response = $this->get($session->signedUrl());

    $response
        ->assertOk()
        ->assertSeeText(config('app.name'))
        ->assertSeeText('SUBMIT VERIFICATION DETAILS')
        ->assertSeeText('Upload clear, well-lit documents only.')
        ->assertDontSeeText('Return to the app after submitting to refresh your status.')
        ->assertDontSeeText('this secure session expires');
});

it('shows the already submitted message when a submitted verification link is reopened', function () {
    ['user' => $providerUser] = createVerificationProvider();

    $session = VerificationSession::query()->create([
        'user_id' => $providerUser->id,
        'session_key' => str_repeat('c', 64),
        'flow' => VerificationSessionFlow::ProviderIdentity,
        'status' => VerificationSessionStatus::Submitted,
        'expires_at' => now()->subMinutes(10),
        'submitted_at' => now()->subMinutes(11),
    ]);

    $url = URL::temporarySignedRoute(
        'verification.sessions.show',
        now()->subMinute(),
        [
            'session' => $session,
            'session_key' => $session->session_key,
        ],
    );

    $response = $this->get($url);

    $response
        ->assertOk()
        ->assertSeeText('You have already submitted your documents')
        ->assertSeeText('Please await notification when reviewed.')
        ->assertSeeText('Okay');
});

it('creates a fresh provider verification link after rejection', function () {
    ['user' => $providerUser, 'profile' => $providerProfile] = createVerificationProvider();

    ProviderIdentityVerification::query()->create([
        'user_id' => $providerUser->id,
        'provider_profile_id' => $providerProfile->id,
        'status' => ProviderVerificationStatus::Rejected,
        'id_type' => 'national_id',
        'submitted_at' => now()->subDay(),
        'reviewed_at' => now()->subHour(),
        'rejection_reason' => 'Images were not clear enough.',
    ]);

    VerificationSession::query()->create([
        'user_id' => $providerUser->id,
        'session_key' => str_repeat('d', 64),
        'flow' => VerificationSessionFlow::ProviderIdentity,
        'status' => VerificationSessionStatus::Submitted,
        'expires_at' => now()->subHour(),
        'submitted_at' => now()->subHour(),
    ]);

    $response = $this->actingAs($providerUser, 'sanctum')
        ->postJson('/api/v1/provider/verification/session');

    $response
        ->assertCreated()
        ->assertJsonPath('message', 'Provider verification session ready.')
        ->assertJsonPath('verification.status', 'unverified');

    expect($response->json('verification_url'))->not->toBeEmpty();
    expect(
        VerificationSession::query()
            ->where('user_id', $providerUser->id)
            ->where('flow', VerificationSessionFlow::ProviderIdentity)
            ->where('status', VerificationSessionStatus::Open)
            ->count()
    )->toBe(1);
});

/**
 * @return array{user: User, profile: ProviderProfile}
 */
function createVerificationProvider(): array
{
    $providerUser = User::factory()->create();
    $providerUser->assignRole(RoleName::Provider->value);

    $providerProfile = ProviderProfile::query()->create([
        'user_id' => $providerUser->id,
        'provider_type' => 'individual',
        'display_name' => 'Verification Provider',
        'verification_status' => ProviderVerificationStatus::Approved,
        'onboarding_completed_at' => now(),
    ]);

    return [
        'user' => $providerUser,
        'profile' => $providerProfile,
    ];
}
