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
        'scheme' => 'https',
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'clictopay' => [
        // Test base: https://test.clictopay.com/payment/rest
        'base_url' => env('CLICTOPAY_BASE_URL', 'https://test.clictopay.com/payment/rest'),
        'username' => env('CLICTOPAY_USERNAME'),
        'password' => env('CLICTOPAY_PASSWORD'),
        'language' => env('CLICTOPAY_LANGUAGE', 'en'),
        'ca_bundle' => env('CLICTOPAY_CA_BUNDLE'),
        'ssl_verify' => env('CLICTOPAY_SSL_VERIFY', true),
        'disable_redirect' => env('CLICTOPAY_DISABLE_REDIRECT', false),
    ],

];
