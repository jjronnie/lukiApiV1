<?php

use App\Enums\RoleName;
use App\Models\RefreshToken;
use App\Models\User;
use App\Notifications\EmailOtpNotification;
use App\Services\SmsService;
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

it('registers and logs in using phone number otp', function () {
    $this->seed(RolesAndPermissionsSeeder::class);
    Notification::fake();

    $codes = [];
    $smsService = new class($codes) extends SmsService
    {
        /**
         * @var array<int, string>
         */
        private array $codes;

        /**
         * @param  array<int, string>  $codes
         */
        public function __construct(array &$codes)
        {
            $this->codes = &$codes;
        }

        public function send(string $to, string $message): void
        {
            preg_match('/(\d{6})/', $message, $matches);
            $this->codes[] = $matches[1] ?? '';
        }
    };
    $this->app->instance(SmsService::class, $smsService);

    $registerResponse = $this->postJson('/api/v1/auth/register', [
        'app_type' => 'customer',
        'auth_method' => 'phone',
        'first_name' => 'Phone',
        'last_name' => 'User',
        'phone' => '0703283529',
        'password' => 'Password123',
        'password_confirmation' => 'Password123',
    ]);

    $registerResponse
        ->assertStatus(202)
        ->assertJson([
            'phone' => '+256703283529',
        ]);

    $user = User::query()->where('phone', '+256703283529')->firstOrFail();

    $registerOtpToken = $registerResponse->json('otp_token');

    $registerVerifyResponse = $this->postJson('/api/v1/auth/register/verify', [
        'app_type' => 'customer',
        'phone' => $registerResponse->json('phone'),
        'otp_token' => $registerOtpToken,
        'code' => $codes[0],
    ]);

    $registerVerifyResponse->assertSuccessful();

    $loginResponse = $this->postJson('/api/v1/auth/login', [
        'app_type' => 'customer',
        'auth_method' => 'phone',
        'phone' => '0703283529',
        'password' => 'Password123',
    ])->assertStatus(202);

    $loginVerifyResponse = $this->postJson('/api/v1/auth/login/verify', [
        'app_type' => 'customer',
        'phone' => $loginResponse->json('phone'),
        'otp_token' => $loginResponse->json('otp_token'),
        'code' => $codes[1],
    ]);

    $loginVerifyResponse
        ->assertSuccessful()
        ->assertJsonStructure(['access_token', 'refresh_token', 'user']);

    expect($user->refresh()->phone_verified_at)->not->toBeNull();
    Notification::assertNothingSent();
});

it('sends login verification to email when email is chosen', function () {
    $this->seed(RolesAndPermissionsSeeder::class);
    Notification::fake();

    $smsService = new class extends SmsService
    {
        public int $sendCount = 0;

        public function send(string $to, string $message): void
        {
            $this->sendCount++;
        }
    };
    $this->app->instance(SmsService::class, $smsService);

    $user = User::factory()->create([
        'email' => 'email-login@example.com',
        'phone' => '+256703283529',
        'password' => Hash::make('Password123'),
    ]);
    $user->assignRole(RoleName::User->value);

    $response = $this->postJson('/api/v1/auth/login', [
        'app_type' => 'customer',
        'auth_method' => 'email',
        'email' => 'email-login@example.com',
        'password' => 'Password123',
    ]);

    $response
        ->assertStatus(202)
        ->assertJson([
            'email' => 'email-login@example.com',
            'phone' => null,
        ]);

    $notification = Notification::sent($user, EmailOtpNotification::class)->last();

    $verifyResponse = $this->postJson('/api/v1/auth/login/verify', [
        'app_type' => 'customer',
        'email' => 'email-login@example.com',
        'otp_token' => $response->json('otp_token'),
        'code' => $notification->code,
    ]);

    $verifyResponse->assertSuccessful();

    Notification::assertSentTo($user, EmailOtpNotification::class);
    expect($smsService->sendCount)->toBe(0);
    expect($user->refresh()->email_verified_at)->not->toBeNull();
    expect($user->phone_verified_at)->toBeNull();
});

it('resets password using phone verification for the customer app', function () {
    $this->seed(RolesAndPermissionsSeeder::class);

    $codes = [];
    $smsService = new class($codes) extends SmsService
    {
        /**
         * @var array<int, string>
         */
        private array $codes;

        /**
         * @param  array<int, string>  $codes
         */
        public function __construct(array &$codes)
        {
            $this->codes = &$codes;
        }

        public function send(string $to, string $message): void
        {
            preg_match('/(\d{6})/', $message, $matches);
            $this->codes[] = $matches[1] ?? '';
        }
    };
    $this->app->instance(SmsService::class, $smsService);

    $user = User::factory()->create([
        'email' => 'phone-reset@example.com',
        'phone' => '+256703283529',
        'password' => Hash::make('Password123'),
    ]);
    $user->assignRole(RoleName::User->value);

    $forgotResponse = $this->postJson('/api/v1/auth/password/forgot', [
        'app_type' => 'customer',
        'phone' => '0703283529',
    ]);

    $forgotResponse
        ->assertStatus(202)
        ->assertJson([
            'phone' => '+256703283529',
            'email' => null,
        ]);

    $verifyResponse = $this->postJson('/api/v1/auth/password/verify', [
        'app_type' => 'customer',
        'phone' => $forgotResponse->json('phone'),
        'otp_token' => $forgotResponse->json('otp_token'),
        'code' => $codes[0],
    ]);

    $verifyResponse
        ->assertSuccessful()
        ->assertJson([
            'phone' => '+256703283529',
            'email' => null,
        ]);

    $resetResponse = $this->postJson('/api/v1/auth/password/reset', [
        'app_type' => 'customer',
        'phone' => $verifyResponse->json('phone'),
        'reset_token' => $verifyResponse->json('reset_token'),
        'password' => 'Password456',
        'password_confirmation' => 'Password456',
    ]);

    $resetResponse->assertSuccessful();

    expect(Hash::check('Password456', $user->refresh()->password))->toBeTrue();
    expect($user->phone_verified_at)->not->toBeNull();
});

it('sends password reset verification to email when email is chosen', function () {
    $this->seed(RolesAndPermissionsSeeder::class);
    Notification::fake();

    $smsService = new class extends SmsService
    {
        public int $sendCount = 0;

        public function send(string $to, string $message): void
        {
            $this->sendCount++;
        }
    };
    $this->app->instance(SmsService::class, $smsService);

    $user = User::factory()->create([
        'email' => 'email-reset@example.com',
        'phone' => '+256703283529',
        'password' => Hash::make('Password123'),
    ]);
    $user->assignRole(RoleName::User->value);

    $response = $this->postJson('/api/v1/auth/password/forgot', [
        'app_type' => 'customer',
        'email' => 'email-reset@example.com',
    ]);

    $response
        ->assertStatus(202)
        ->assertJson([
            'email' => 'email-reset@example.com',
            'phone' => null,
        ]);

    Notification::assertSentTo($user, EmailOtpNotification::class);
    expect($smsService->sendCount)->toBe(0);
});
