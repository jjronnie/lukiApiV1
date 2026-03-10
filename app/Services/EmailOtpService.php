<?php

namespace App\Services;

use App\Enums\MobileAppType;
use App\Models\EmailOtp;
use App\Models\User;
use App\Notifications\EmailOtpNotification;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class EmailOtpService
{
    /**
     * @return array{otp_token:string, expires_in:int, resend_available_in:int, resends_remaining:int, max_resends_per_hour:int}
     */
    public function issue(User $user, string $purpose, MobileAppType $appType): array
    {
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
            'email' => strtolower($user->email),
            'purpose' => $purpose,
            'app_type' => $appType->value,
            'otp_hash' => $this->hashCode($code),
            'token_hash' => hash('sha256', $token),
            'last_sent_at' => now(),
            'resend_window_started_at' => now(),
            'expires_at' => now()->addMinutes($this->otpTtlMinutes()),
        ]);

        $user->notify(new EmailOtpNotification($code, $purpose, $this->otpTtlMinutes()));

        return [
            'otp_token' => $token,
            'expires_in' => $this->otpTtlMinutes() * 60,
            'resend_available_in' => $this->resendCooldownSeconds(),
            'resends_remaining' => $this->maxResendsPerHour(),
            'max_resends_per_hour' => $this->maxResendsPerHour(),
        ];
    }

    /**
     * @return array{otp_token:string, expires_in:int, resend_available_in:int, resends_remaining:int, max_resends_per_hour:int}
     */
    public function resend(
        string $otpToken,
        string $email,
        string $purpose,
        MobileAppType $appType,
    ): array {
        $record = EmailOtp::query()
            ->where('token_hash', hash('sha256', $otpToken))
            ->where('purpose', $purpose)
            ->where('app_type', $appType->value)
            ->whereNull('consumed_at')
            ->first();

        if ($record === null || strtolower($record->email) !== strtolower($email)) {
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

        $record->user?->notify(new EmailOtpNotification($code, $purpose, $this->otpTtlMinutes()));

        return [
            'otp_token' => $otpToken,
            'expires_in' => $this->otpTtlMinutes() * 60,
            'resend_available_in' => $this->resendCooldownSeconds(),
            'resends_remaining' => max(0, $this->maxResendsPerHour() - (int) $record->resend_count),
            'max_resends_per_hour' => $this->maxResendsPerHour(),
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
}
