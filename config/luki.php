<?php

return [
    'currency' => env('LUKI_CURRENCY', 'UGX'),
    'dispatch' => [
        'offer_batch_size' => (int) env('LUKI_DISPATCH_BATCH_SIZE', 3),
        'offer_expiry_seconds' => (int) env('LUKI_DISPATCH_OFFER_EXPIRY_SECONDS', 15),
    ],
    'cancellation_fee_amount' => (int) env('LUKI_CANCELLATION_FEE_AMOUNT', 2000),
];
