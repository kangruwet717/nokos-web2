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

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'dompetx' => [
        'base_url' => env('DOMPETX_BASE_URL', 'https://api.dompetx.com'),
        'api_key' => env('DOMPETX_API_KEY'),
        'webhook_secret' => env('DOMPETX_WEBHOOK_SECRET'),
        'webhook_tolerance_seconds' => (int) env('DOMPETX_WEBHOOK_TOLERANCE_SECONDS', 300),
        'timeout' => (int) env('DOMPETX_TIMEOUT_SECONDS', 20),
    ],

    'smsbower' => [
        'base_url' => env('SMSBOWER_BASE_URL', 'https://smsbower.app/stubs/handler_api.php'),
        'fallback_base_urls' => array_values(array_filter(array_map('trim', explode(',', (string) env('SMSBOWER_FALLBACK_BASE_URLS', 'https://smsbower.page/stubs/handler_api.php'))))),
        'api_key' => env('SMSBOWER_API_KEY'),
        'webhook_secret' => env('SMSBOWER_WEBHOOK_SECRET'),
        'timeout' => (int) env('SMSBOWER_TIMEOUT_SECONDS', 60),
        'connect_timeout' => (int) env('SMSBOWER_CONNECT_TIMEOUT_SECONDS', 5),
        'retry_attempts' => (int) env('SMSBOWER_RETRY_ATTEMPTS', 2),
        'refresh_price_before_order' => (bool) env('SMSBOWER_REFRESH_PRICE_BEFORE_ORDER', true),
        'prices_action' => env('SMSBOWER_PRICES_ACTION', 'getPricesV3'),
        'default_margin_type' => env('SMSBOWER_DEFAULT_MARGIN_TYPE', 'percent'),
        'default_margin_value' => env('SMSBOWER_DEFAULT_MARGIN_VALUE', 30),
        'usd_to_idr_rate' => env('SMSBOWER_USD_TO_IDR_RATE', 16000),
        'minimum_selling_price_idr' => env('SMSBOWER_MINIMUM_SELLING_PRICE_IDR', 1000),
        'minimum_profit_idr' => env('SMSBOWER_MIN_PROFIT_IDR', env('DEFAULT_MIN_PROFIT', 0)),
        'rounding_idr' => env('SMSBOWER_ROUNDING_IDR', 100),
        'scope_sync_cooldown_seconds' => (int) env('SMSBOWER_SCOPE_SYNC_COOLDOWN_SECONDS', 120),
    ],

];
