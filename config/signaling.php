<?php

return [
    // Dedicated signing key shared with Node; never use a VITE_ variable.
    'secret' => env('SIGNALING_AUTH_SECRET'),
];
