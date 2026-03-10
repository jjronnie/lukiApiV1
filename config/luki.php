<?php

return [
    'currency' => env('LUKI_CURRENCY', 'UGX'),
    'auth' => [
        'access_token_ttl_minutes' => (int) env('LUKI_ACCESS_TOKEN_TTL_MINUTES', 43_200),
        'refresh_token_ttl_days' => (int) env('LUKI_REFRESH_TOKEN_TTL_DAYS', 365),
        'otp_ttl_minutes' => (int) env('LUKI_OTP_TTL_MINUTES', 10),
        'otp_resend_cooldown_seconds' => (int) env('LUKI_OTP_RESEND_COOLDOWN_SECONDS', 60),
        'otp_max_resends_per_hour' => (int) env('LUKI_OTP_MAX_RESENDS_PER_HOUR', 5),
        'otp_max_attempts' => (int) env('LUKI_OTP_MAX_ATTEMPTS', 5),
    ],
    'dispatch' => [
        'offer_batch_size' => (int) env('LUKI_DISPATCH_BATCH_SIZE', 3),
        'offer_expiry_seconds' => (int) env('LUKI_DISPATCH_OFFER_EXPIRY_SECONDS', 15),
    ],
    'cancellation_fee_amount' => (int) env('LUKI_CANCELLATION_FEE_AMOUNT', 2000),
];
