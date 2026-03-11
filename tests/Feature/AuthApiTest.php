<?php

use App\Enums\RoleName;
use App\Models\RefreshToken;
use App\Models\User;
use App\Notifications\EmailOtpNotification;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;

it('registers, verifies otp, logs in and refreshes tokens for the customer app', function () {
    $this->seed(RolesAndPermissionsSeeder::class);
    Notification::fake();

    $registerResponse = $this->postJson('/api/v1/auth/register', [
        'app_type' => 'customer',
        'first_name' => 'API',
        'last_name' => 'User',
        'email' => 'api-user@example.com',
        'password' => 'Password123',
        'password_confirmation' => 'Password123',
    ]);

    $registerResponse
        ->assertStatus(202)
        ->assertJsonStructure([
            'otp_token',
            'expires_in',
            'resend_available_in',
            'email',
        ]);

    $user = User::query()->where('email', 'api-user@example.com')->firstOrFail();
    Notification::assertSentTo($user, EmailOtpNotification::class);

    $registerOtpToken = $registerResponse->json('otp_token');
    $registerNotification = Notification::sent($user, EmailOtpNotification::class)->first();

    $verifyResponse = $this->postJson('/api/v1/auth/register/verify', [
        'app_type' => 'customer',
        'email' => $user->email,
        'otp_token' => $registerOtpToken,
        'code' => $registerNotification->code,
    ]);

    $verifyResponse
        ->assertSuccessful()
        ->assertJsonStructure([
            'access_token',
            'refresh_token',
            'user' => ['public_id', 'email', 'profile_completed_at'],
        ]);

    $loginResponse = $this->postJson('/api/v1/auth/login', [
        'app_type' => 'customer',
        'email' => 'api-user@example.com',
        'password' => 'Password123',
    ]);

    $loginResponse->assertStatus(202);

    $loginOtpToken = $loginResponse->json('otp_token');
    $loginNotification = Notification::sent($user, EmailOtpNotification::class)->last();

    $loginVerifyResponse = $this->postJson('/api/v1/auth/login/verify', [
        'app_type' => 'customer',
        'email' => $user->email,
        'otp_token' => $loginOtpToken,
        'code' => $loginNotification->code,
    ]);

    $loginVerifyResponse->assertSuccessful();

    $refreshToken = $loginVerifyResponse->json('refresh_token');

    expect($refreshToken)->not->toBeEmpty();
    expect(RefreshToken::query()->count())->toBeGreaterThan(0);

    $refreshResponse = $this->postJson('/api/v1/auth/refresh', [
        'app_type' => 'customer',
        'refresh_token' => $refreshToken,
    ]);

    $refreshResponse
        ->assertSuccessful()
        ->assertJsonStructure(['access_token', 'refresh_token']);
});

it('blocks admin logins on the api', function () {
    $this->seed(RolesAndPermissionsSeeder::class);

    $admin = User::factory()->create([
        'password' => Hash::make('Password123'),
    ]);
    $admin->assignRole(RoleName::Admin->value);

    $response = $this->postJson('/api/v1/auth/login', [
        'app_type' => 'customer',
        'email' => $admin->email,
        'password' => 'Password123',
    ]);

    $response->assertStatus(403);
});
