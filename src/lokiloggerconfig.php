<?php
return [
    'context' => [
        'application' => env('LOG_APP', env('APP_NAME')),
        'type' => '{level_name}'
    ],
    'format' => env('LOG_FORMAT', '[{level_name}] {message}'),
    'method' => env('LOG_METHOD', 'instant'), //other option is file
    'loki' => [
        'server' => env('LOG_SERVER', 'https://localhost'),
        'username' => env('LOG_USERNAME', null),
        'password' => env('LOG_PASSWORD', null),
    ],
];