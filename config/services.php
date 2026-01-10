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

    'jitsi' => [
        // For JaaS use 8x8.vc (or a region like frankfurt.8x8.vc)
        'domain' => env('JITSI_DOMAIN', '8x8.vc'),
        // JaaS App ID looks like: vpaas-magic-cookie-xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
        'jaas_app_id' => env('JAAS_APP_ID'),
        // JaaS Key ID header: vpaas-magic-cookie-<APP_ID>/<KEY_ID>
        'jaas_kid' => env('JAAS_KID'),
        // RSA private key PEM (store with \n in .env and we will convert)
        'jaas_private_key' => env('JAAS_PRIVATE_KEY'),
        // Preferred on Windows: path to PEM file (avoids .env multiline/escaping issues)
        'jaas_private_key_path' => env('JAAS_PRIVATE_KEY_PATH'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

];
