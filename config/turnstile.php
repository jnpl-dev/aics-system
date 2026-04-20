<?php

return [
    'key' => env('TURNSTILE_SITE_KEY'),
    'secret' => env('TURNSTILE_SECRET_KEY'),
    'threshold' => 0.5,
    'timeout' => 30,

    'services' => [
        'turnstile' => [
            'key' => env('TURNSTILE_SITE_KEY'),
            'secret' => env('TURNSTILE_SECRET_KEY'),
        ],
    ],
];
