<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'eu-west-2'),
    ],

    'github' => [
        'client_id' => env('GITHUB_CLIENT_ID'),
        'client_secret' => env('GITHUB_CLIENT_SECRET'),
    ],

    'h_captcha' => [
        'site_key' => env('H_CAPTCHA_SITE_KEY'),
        'secret_key' => env('H_CAPTCHA_SECRET_KEY'),
    ],

    're_captcha' => [
        'site_key' => env('RE_CAPTCHA_SITE_KEY'),
        'secret_key' => env('RE_CAPTCHA_SECRET_KEY'),
        'project_id' => env('RE_CAPTCHA_PROJECT_ID', 'sharaforms'),
        'score_threshold' => env('RE_CAPTCHA_SCORE_THRESHOLD', 0),
    ],

    'canny' => [
        'api_key' => env('CANNY_API_KEY'),
    ],

    'notion' => [
        'worker' => env('NOTION_WORKER', 'https://notion-forms-worker.notionforms.workers.dev/v1'),
    ],

    'openai' => [
        'api_key' => env('OPEN_AI_API_KEY'),
    ],

    'ai' => [
        'provider' => env('AI_PROVIDER', 'auto'),
        'timeout' => env('AI_HTTP_TIMEOUT', 10),
        'connect_timeout' => env('AI_HTTP_CONNECT_TIMEOUT', 5),
        'providers' => [
            'openai' => [
                'api_key' => env('OPEN_AI_API_KEY'),
                'base_uri' => env('OPEN_AI_BASE_URI'),
                'models' => [
                    'mini' => env('OPEN_AI_MODEL_MINI', 'gpt-5.4-mini'),
                    'nano' => env('OPEN_AI_MODEL_NANO', 'gpt-5.4-nano'),
                ],
            ],
            'gemini' => [
                'api_key' => env('GEMINI_API_KEY'),
                'base_uri' => env('GEMINI_BASE_URI', 'https://generativelanguage.googleapis.com/v1beta/openai'),
                'models' => [
                    'mini' => env('GEMINI_MODEL_MINI', 'gemini-2.5-flash'),
                    'nano' => env('GEMINI_MODEL_NANO', 'gemini-2.5-flash-lite'),
                ],
            ],
            'groq' => [
                'api_key' => env('GROQ_API_KEY'),
                'base_uri' => env('GROQ_BASE_URI', 'https://api.groq.com/openai/v1'),
                'models' => [
                    'mini' => env('GROQ_MODEL_MINI', 'llama-3.3-70b-versatile'),
                    'nano' => env('GROQ_MODEL_NANO', 'llama-3.1-8b-instant'),
                ],
            ],
        ],
    ],

    'unsplash' => [
        'access_key' => env('UNSPLASH_ACCESS_KEY'),
        'secret_key' => env('UNSPLASH_SECRET_KEY'),
    ],

    'crisp_website_id' => env('CRISP_WEBSITE_ID'),

    'google' => [
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect' => env('GOOGLE_REDIRECT_URL', front_url('/oauth/google/callback')),
        'fonts_api_key' => env('GOOGLE_FONTS_API_KEY'),
        'picker_api_key' => env('GOOGLE_PICKER_API_KEY'),
        'picker_app_id' => env('GOOGLE_PICKER_APP_ID', '775268936981'),
    ],

    'telegram' => [
        'bot_token' => env('TELEGRAM_BOT_TOKEN'),
    ],

    'zapier' => [
        'enabled' => env('ZAPIER_ENABLED', false),
    ],

    'stripe' => [
        'client_id' => env('STRIPE_CLIENT_ID'),
        'client_secret' => env('STRIPE_CLIENT_SECRET', env('STRIPE_SECRET')),
        'redirect' => env('STRIPE_REDIRECT_URL', front_url('/oauth/stripe/callback')),
        'export_lookback_days' => env('STRIPE_EXPORT_LOOKBACK_DAYS', 45),
    ],

    'ipinfo' => [
        'token' => env('IPINFO_TOKEN'),
        'cache_ttl_hours' => env('IPINFO_CACHE_TTL_HOURS', 24),
        'request_timeout' => env('IPINFO_REQUEST_TIMEOUT', 5),
    ],

];
