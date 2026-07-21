<?php

return [
    'site_name' => env('APP_NAME', 'Laravel'),
    'title' => env('SEO_TITLE', env('APP_NAME', 'Laravel')),
    'description' => env('SEO_DESCRIPTION', 'Professional mental health support and online appointments.'),
    'image' => env('SEO_IMAGE', '/images/aenhance-og.png'),
    'twitter' => [
        'site' => env('SEO_TWITTER_HANDLE', ''),
    ],
];
