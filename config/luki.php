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
        'offer_batch_size' => (int) env('LUKI_DISPATCH_BATCH_SIZE', 5),
        'offer_expiry_seconds' => (int) env('LUKI_DISPATCH_OFFER_EXPIRY_SECONDS', 30),
        'taken_visibility_seconds' => (int) env('LUKI_DISPATCH_TAKEN_VISIBILITY_SECONDS', 45),
        'location_freshness_seconds' => (int) env('LUKI_PROVIDER_LOCATION_FRESHNESS_SECONDS', 180),
        'scheduled_lead_hours' => (int) env('LUKI_SCHEDULED_DISPATCH_LEAD_HOURS', 5),
    ],
    'cancellation_fee_amount' => (int) env('LUKI_CANCELLATION_FEE_AMOUNT', 2000),
    'commission' => [
        'base_amount' => (int) env('LUKI_COMMISSION_BASE_AMOUNT', 1000),
        'percentage_rate' => (float) env('LUKI_COMMISSION_PERCENTAGE_RATE', 5),
        'exclude_transport' => env('LUKI_COMMISSION_EXCLUDE_TRANSPORT', true),
    ],
    'notifications' => [
        'retention_days' => (int) env('LUKI_NOTIFICATION_RETENTION_DAYS', 30),
    ],
    'tracking' => [
        'default_travel_speed_kph' => (int) env('LUKI_DEFAULT_TRAVEL_SPEED_KPH', 25),
    ],
    'email_preferences' => [
        'signed_url_days' => (int) env('LUKI_EMAIL_PREFERENCES_SIGNED_URL_DAYS', 30),
    ],
];
