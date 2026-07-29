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
        'site_verification' => env('GOOGLE_SITE_VERIFICATION'),
    ],

    'ga4' => [
        'measurement_id' => env('GA4_MEASUREMENT_ID'),
    ],

    'ai' => [
        'provider' => env('AI_PROVIDER', env('GEMINI_API_KEY') ? 'gemini' : 'openai'),
    ],

    'openai' => [
        'api_key' => env('OPENAI_API_KEY'),
        'model' => env('OPENAI_MODEL', 'gpt-5.5'),
        'vector_store_id' => env('OPENAI_VECTOR_STORE_ID'),
        'reasoning_effort' => env('OPENAI_REASONING_EFFORT', 'low'),
        'verbosity' => env('OPENAI_VERBOSITY', 'low'),
        'max_output_tokens' => env('OPENAI_MAX_OUTPUT_TOKENS', 700),
        'timeout' => env('OPENAI_TIMEOUT', 30),
    ],

    'gemini' => [
        'api_key' => env('GEMINI_API_KEY'),
        'api_url' => env('GEMINI_API_URL', 'https://generativelanguage.googleapis.com/v1beta'),
        'model' => env('GEMINI_MODEL', 'gemini-2.5-flash'),
        'file_search_store_name' => env('GEMINI_FILE_SEARCH_STORE_NAME'),
        'max_output_tokens' => env('GEMINI_MAX_OUTPUT_TOKENS', 700),
        'temperature' => env('GEMINI_TEMPERATURE', 0.2),
        'timeout' => env('GEMINI_TIMEOUT', 30),
    ],

    'dezestore' => [
        'facebook_url' => env('DEZESTORE_FACEBOOK_URL', 'https://www.facebook.com/61584153819643'),
        'instagram_url' => env('DEZESTORE_INSTAGRAM_URL', 'https://www.instagram.com/dezestore/'),
        'tiktok_url' => env('DEZESTORE_TIKTOK_URL', 'https://www.tiktok.com/@dezestore'),
    ],

];
