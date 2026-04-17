<?php

namespace App\Services;

use App\Enums\MobileAppType;
use App\Models\EmailOtp;
use App\Models\User;
use App\Notifications\EmailOtpNotification;
use App\Support\IdentityValueNormalizer;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class EmailOtpService
{
    public function __construct(private readonly SmsService $smsService) {}

    /**
     * @return array{otp_token:string, expires_in:int, resend_available_in:int, resends_remaining:int, max_resends_per_hour:int, channel:string, destination:string}
     */
    public function issue(
        User $user,
        string $purpose,
        MobileAppType $appType,
        ?string $preferredChannel = null,
        ?string $preferredIdentifier = null,
    ): array {
        $destination = $this->otpDestinationForUser($user, $preferredChannel, $preferredIdentifier);
        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $token = Str::random(40);

        EmailOtp::query()
            ->where('user_id', $user->id)
            ->where('purpose', $purpose)
            ->where('app_type', $appType->value)
            ->whereNull('consumed_at')
            ->update(['consumed_at' => now()]);

        EmailOtp::query()->create([
            'user_id' => $user->id,
            'email' => $destination['value'],
            'purpose' => $purpose,
            'app_type' => $appType->value,
            'otp_hash' => $this->hashCode($code),
            'token_hash' => hash('sha256', $token),
            'last_sent_at' => now(),
            'resend_window_started_at' => now(),
            'expires_at' => now()->addMinutes($this->otpTtlMinutes()),
        ]);

        $this->dispatchOtp($user, $destination['channel'], $destination['value'], $code, $purpose);

        return [
            'otp_token' => $token,
            'expires_in' => $this->otpTtlMinutes() * 60,
            'resend_available_in' => $this->resendCooldownSeconds(),
            'resends_remaining' => $this->maxResendsPerHour(),
            'max_resends_per_hour' => $this->maxResendsPerHour(),
            'channel' => $destination['channel'],
            'destination' => $destination['value'],
        ];
    }

    /**
     * @return array{otp_token:string, expires_in:int, resend_available_in:int, resends_remaining:int, max_resends_per_hour:int, channel:string, destination:string}
     */
    public function resend(
        string $otpToken,
        string $identifier,
        string $purpose,
        MobileAppType $appType,
    ): array {
        $record = EmailOtp::query()
            ->where('token_hash', hash('sha256', $otpToken))
            ->where('purpose', $purpose)
            ->where('app_type', $appType->value)
            ->whereNull('consumed_at')
            ->first();

        $normalizedIdentifier = $this->normalizeIdentifier($identifier);

        if ($record === null || $this->normalizeIdentifier($record->email) !== $normalizedIdentifier) {
            throw ValidationException::withMessages([
                'otp_token' => ['Unable to resend the code for this request.'],
            ]);
        }

        $cooldownEndsAt = $record->last_sent_at?->copy()->addSeconds($this->resendCooldownSeconds());
        if ($cooldownEndsAt !== null && $cooldownEndsAt->isFuture()) {
            throw ValidationException::withMessages([
                'otp_token' => ['Please wait 1 minute before requesting another code.'],
            ]);
        }

        $windowStartedAt = $record->resend_window_started_at ?? now();
        $resendCount = (int) $record->resend_count;
        if ($windowStartedAt->copy()->addHour()->isPast()) {
            $windowStartedAt = now();
            $resendCount = 0;
        }

        if ($resendCount >= $this->maxResendsPerHour()) {
            throw ValidationException::withMessages([
                'otp_token' => ['You have reached the resend limit. Please wait 1 hour before requesting another code.'],
            ]);
        }

        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        $record->update([
            'otp_hash' => $this->hashCode($code),
            'attempts' => 0,
            'resend_count' => $resendCount + 1,
            'last_sent_at' => now(),
            'resend_window_started_at' => $windowStartedAt,
            'expires_at' => now()->addMinutes($this->otpTtlMinutes()),
        ]);

        $channel = $this->channelForIdentifier($record->email);
        $destination = $record->email;

        if ($record->user !== null) {
            $this->dispatchOtp($record->user, $channel, $destination, $code, $purpose);
        }

        return [
            'otp_token' => $otpToken,
            'expires_in' => $this->otpTtlMinutes() * 60,
            'resend_available_in' => $this->resendCooldownSeconds(),
            'resends_remaining' => max(0, $this->maxResendsPerHour() - (int) $record->resend_count),
            'max_resends_per_hour' => $this->maxResendsPerHour(),
            'channel' => $channel,
            'destination' => $destination,
        ];
    }

    public function verify(string $otpToken, string $code, string $purpose, MobileAppType $appType): EmailOtp
    {
        $record = EmailOtp::query()
            ->where('token_hash', hash('sha256', $otpToken))
            ->where('purpose', $purpose)
            ->where('app_type', $appType->value)
            ->first();

        if ($record === null || $record->consumed_at !== null || $record->expires_at->isPast()) {
            throw ValidationException::withMessages([
                'code' => ['Invalid or expired verification code.'],
            ]);
        }

        if ($record->attempts >= $this->maxAttempts()) {
            throw ValidationException::withMessages([
                'code' => ['Too many attempts. Please request a new code.'],
            ]);
        }

        if (! hash_equals($record->otp_hash, $this->hashCode($code))) {
            $record->increment('attempts');
            throw ValidationException::withMessages([
                'code' => ['Invalid or expired verification code.'],
            ]);
        }

        $record->update(['consumed_at' => now()]);

        return $record;
    }

    private function hashCode(string $code): string
    {
        return hash_hmac('sha256', $code, (string) config('app.key'));
    }

    private function otpTtlMinutes(): int
    {
        return max(1, (int) config('luki.auth.otp_ttl_minutes', 10));
    }

    private function maxAttempts(): int
    {
        return max(1, (int) config('luki.auth.otp_max_attempts', 5));
    }

    private function resendCooldownSeconds(): int
    {
        return max(1, (int) config('luki.auth.otp_resend_cooldown_seconds', 60));
    }

    private function maxResendsPerHour(): int
    {
        return max(1, (int) config('luki.auth.otp_max_resends_per_hour', 5));
    }

    /**
     * @return array{channel:string,value:string}
     */
    private function otpDestinationForUser(
        User $user,
        ?string $preferredChannel = null,
        ?string $preferredIdentifier = null,
    ): array {
        $preferredChannel = in_array($preferredChannel, ['email', 'phone'], true)
            ? $preferredChannel
            : null;

        if ($preferredChannel === 'phone') {
            $phone = $this->matchingPhoneDestination($user, $preferredIdentifier);
            if ($phone !== null) {
                return [
                    'channel' => 'phone',
                    'value' => $phone,
                ];
            }
        }

        if ($preferredChannel === 'email') {
            $email = $this->matchingEmailDestination($user, $preferredIdentifier);
            if ($email !== null) {
                return [
                    'channel' => 'email',
                    'value' => $email,
                ];
            }
        }

        if ($preferredChannel === null && filled($preferredIdentifier)) {
            $phone = $this->matchingPhoneDestination($user, $preferredIdentifier);
            if ($phone !== null) {
                return [
                    'channel' => 'phone',
                    'value' => $phone,
                ];
            }

            $email = $this->matchingEmailDestination($user, $preferredIdentifier);
            if ($email !== null) {
                return [
                    'channel' => 'email',
                    'value' => $email,
                ];
            }
        }

        if (filled($user->phone)) {
            return [
                'channel' => 'phone',
                'value' => (string) $user->phone,
            ];
        }

        $email = IdentityValueNormalizer::email($user->email);
        if ($email === '') {
            throw ValidationException::withMessages([
                'email' => ['No valid email or phone is available for OTP delivery.'],
            ]);
        }

        return [
            'channel' => 'email',
            'value' => $email,
        ];
    }

    private function matchingPhoneDestination(User $user, ?string $preferredIdentifier): ?string
    {
        $userPhone = trim((string) $user->phone);
        if ($userPhone === '') {
            return null;
        }

        if (! filled($preferredIdentifier)) {
            return $userPhone;
        }

        return IdentityValueNormalizer::ugandaPhoneE164FromLocalInput($preferredIdentifier) === $userPhone
            ? $userPhone
            : null;
    }

    private function matchingEmailDestination(User $user, ?string $preferredIdentifier): ?string
    {
        $userEmail = IdentityValueNormalizer::email($user->email);
        if ($userEmail === '') {
            return null;
        }

        if (! filled($preferredIdentifier)) {
            return $userEmail;
        }

        return IdentityValueNormalizer::email($preferredIdentifier) === $userEmail
            ? $userEmail
            : null;
    }

    private function dispatchOtp(
        User $user,
        string $channel,
        string $destination,
        string $code,
        string $purpose,
    ): void {
        if ($channel === 'phone') {
            $this->smsService->send(
                $destination,
                sprintf(
                    'Your Luki verification code is %s. It expires in %d minutes.',
                    $code,
                    $this->otpTtlMinutes(),
                ),
            );

            return;
        }

        $user->notify(new EmailOtpNotification($code, $purpose, $this->otpTtlMinutes()));
    }

    private function normalizeIdentifier(?string $value): string
    {
        $email = IdentityValueNormalizer::email($value);

        if ($email !== '') {
            return $email;
        }

        $phone = IdentityValueNormalizer::ugandaPhoneE164FromLocalInput($value);

        return $phone !== '' ? $phone : trim((string) $value);
    }

    private function channelForIdentifier(string $identifier): string
    {
        return preg_match('/^\+256\d{9}$/', $identifier) === 1 ? 'phone' : 'email';
    }
}
