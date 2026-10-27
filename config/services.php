<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Resend, Postmark, AWS, and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
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

    /*
     | Razorpay payment gateway. Use TEST keys (rzp_test_…) in development and
     | LIVE keys in production. These MUST come from the environment — never
     | commit real keys to source control.
     */
    'razorpay' => [
        'key' => env('RAZORPAY_KEY'),
        'secret' => env('RAZORPAY_SECRET'),
        // Separate secret from the Razorpay webhook settings page. Used only
        // to verify X-Razorpay-Signature on payment.captured / payment.failed.
        'webhook_secret' => env('RAZORPAY_WEBHOOK_SECRET'),
    ],

    /*
     | Social login (OAuth 2.0). Client credentials come from the provider
     | consoles (Google Cloud Console / Meta for Developers). Keep the real
     | values in .env only — never commit secrets. Leave a provider's
     | client_id/client_secret blank to disable its button (the controller
     | fails gracefully). `redirect` may be left blank to derive the callback
     | URL from APP_URL. The key names match Laravel Socialite's conventions,
     | so switching to that package later needs no config changes.
     */
    'google' => [
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect' => env('GOOGLE_REDIRECT_URI'),
    ],

    'facebook' => [
        'client_id' => env('FACEBOOK_CLIENT_ID'),
        'client_secret' => env('FACEBOOK_CLIENT_SECRET'),
        'redirect' => env('FACEBOOK_REDIRECT_URI'),
    ],

    /*
     | Optional web search used by /admin/send-email to find public business
     | emails. DuckDuckGo is always tried; these add more results when set.
     */
    'google_cse' => [
        'key' => env('GOOGLE_CSE_API_KEY'),
        'cx' => env('GOOGLE_CSE_CX'),
    ],

    'openai' => [
        'key' => env('OPENAI_API_KEY'),
    ],

];
