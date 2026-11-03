<?php

return [
    // Coturn static-auth-secret. Never prefix this variable with VITE_.
    'secret' => env('TURN_SHARED_SECRET'),
    'ttl' => 7200,
    'urls' => [
        'turn:169.58.251.224:3478?transport=udp',
        'turn:169.58.251.224:3478?transport=tcp',
    ],
];
