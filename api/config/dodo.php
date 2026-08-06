<?php

return [
    'api_key' => env('DODO_PAYMENTS_API_KEY'),
    'webhook_key' => env('DODO_PAYMENTS_WEBHOOK_KEY'),
    'environment' => env('DODO_PAYMENTS_ENVIRONMENT', env('APP_ENV') === 'production' ? 'live_mode' : 'test_mode'),
    'base_urls' => [
        'live_mode' => env('DODO_PAYMENTS_LIVE_BASE_URL', 'https://live.dodopayments.com'),
        'test_mode' => env('DODO_PAYMENTS_TEST_BASE_URL', 'https://test.dodopayments.com'),
    ],
    'stripe_secret' => env('STRIPE_SECRET'),

    'products' => [
        'pro' => [
            'monthly' => env('DODO_PAYMENTS_PRO_PRODUCT_ID_MONTHLY'),
            'yearly' => env('DODO_PAYMENTS_PRO_PRODUCT_ID_YEARLY'),
        ],
        'business' => [
            'monthly' => env('DODO_PAYMENTS_BUSINESS_PRODUCT_ID_MONTHLY'),
            'yearly' => env('DODO_PAYMENTS_BUSINESS_PRODUCT_ID_YEARLY'),
        ],
        'enterprise' => [
            'monthly' => env('DODO_PAYMENTS_ENTERPRISE_PRODUCT_ID_MONTHLY'),
            'yearly' => env('DODO_PAYMENTS_ENTERPRISE_PRODUCT_ID_YEARLY'),
        ],
    ],
];
