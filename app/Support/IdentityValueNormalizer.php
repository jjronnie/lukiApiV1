<?php

namespace App\Support;

use Illuminate\Support\Str;

class IdentityValueNormalizer
{
    public static function humanName(?string $value): string
    {
        $normalized = preg_replace('/\s+/', ' ', trim((string) $value)) ?? '';
        if ($normalized === '') {
            return '';
        }

        $words = preg_split('/\s+/', mb_strtolower($normalized)) ?: [];

        return implode(' ', array_map(
            static fn (string $word): string => preg_replace_callback(
                "/(^|[-'])\p{L}/u",
                static fn (array $matches): string => mb_strtoupper($matches[0]),
                $word
            ) ?? $word,
            $words
        ));
    }

    public static function email(?string $value): string
    {
        return Str::lower(trim((string) $value));
    }

    public static function ugandaPhoneLocal(?string $value): string
    {
        $digits = preg_replace('/\D+/', '', trim((string) $value)) ?? '';
        if ($digits === '') {
            return '';
        }

        if (str_starts_with($digits, '256') && strlen($digits) === 12) {
            $digits = '0'.substr($digits, 3);
        }

        if (str_starts_with($digits, '7') && strlen($digits) === 9) {
            $digits = '0'.$digits;
        }

        return $digits;
    }

    public static function ugandaPhoneE164(?string $value, string $countryCode = '+256'): string
    {
        $local = self::ugandaPhoneLocal($value);

        return preg_match('/^07\d{8}$/', $local) === 1
            ? $countryCode.substr($local, 1)
            : '';
    }

    public static function ugandaPhoneE164FromLocalInput(?string $value, string $countryCode = '+256'): string
    {
        $raw = trim((string) $value);

        if (preg_match('/^\d{9}$/', $raw) === 1) {
            return $countryCode.$raw;
        }

        if (preg_match('/^0\d{9}$/', $raw) === 1) {
            return $countryCode.substr($raw, 1);
        }

        return '';
    }

    public static function verificationIdNumber(?string $value): string
    {
        return Str::upper(preg_replace('/\s+/', '', trim((string) $value)) ?? '');
    }
}
