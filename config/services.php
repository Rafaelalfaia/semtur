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

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'google' => [
    'maps_key' => env('GOOGLE_MAPS_KEY'),
    'client_id' => env('GOOGLE_CLIENT_ID'),
    'client_secret' => env('GOOGLE_CLIENT_SECRET'),
    'redirect' => env('GOOGLE_REDIRECT_URI'),
    ],

    'instagram' => [
    'worker_url' => env('INSTAGRAM_WORKER_URL', ''), // ex.: https://semtur.seu-worker.workers.dev
    'feed_ttl'   => env('IG_FEED_TTL', 1800),
    ],

    'site_translation' => [
        'provider' => env('SITE_TRANSLATION_PROVIDER', 'null'),
        'queue' => env('SITE_TRANSLATION_QUEUE', 'default'),
        'timeout' => (int) env('SITE_TRANSLATION_TIMEOUT', 30),
        'target_locales' => array_values(array_filter(array_map(
            'trim',
            explode(',', env('SITE_TRANSLATION_TARGET_LOCALES', 'en,es'))
        ))),
        'openai' => [
            'base_url' => env('OPENAI_BASE_URL', 'https://api.openai.com/v1'),
            'api_key' => env('OPENAI_API_KEY'),
            'model' => env('OPENAI_TRANSLATION_MODEL', 'gpt-4.1-mini'),
        ],
    ],







];
