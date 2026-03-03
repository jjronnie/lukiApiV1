<?php

namespace App\Services;

use App\Models\EmailOtp;
use App\Models\User;
use App\Notifications\EmailOtpNotification;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class EmailOtpService
{
    private const OTP_TTL_MINUTES = 10;

    private const MAX_ATTEMPTS = 5;

    /**
     * @return array{otp_token:string, expires_in:int}
     */
    public function issue(User $user, string $purpose): array
    {
        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $token = Str::random(40);

        EmailOtp::query()
            ->where('user_id', $user->id)
            ->where('purpose', $purpose)
            ->whereNull('consumed_at')
            ->update(['consumed_at' => now()]);

        EmailOtp::query()->create([
            'user_id' => $user->id,
            'email' => strtolower($user->email),
            'purpose' => $purpose,
            'otp_hash' => $this->hashCode($code),
            'token_hash' => hash('sha256', $token),
            'expires_at' => now()->addMinutes(self::OTP_TTL_MINUTES),
        ]);

        $user->notify(new EmailOtpNotification($code, $purpose, self::OTP_TTL_MINUTES));

        return [
            'otp_token' => $token,
            'expires_in' => self::OTP_TTL_MINUTES * 60,
        ];
    }

    public function verify(string $otpToken, string $code, string $purpose): EmailOtp
    {
        $record = EmailOtp::query()
            ->where('token_hash', hash('sha256', $otpToken))
            ->where('purpose', $purpose)
            ->first();

        if ($record === null || $record->consumed_at !== null || $record->expires_at->isPast()) {
            throw ValidationException::withMessages([
                'code' => ['Invalid or expired verification code.'],
            ]);
        }

        if ($record->attempts >= self::MAX_ATTEMPTS) {
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
}
