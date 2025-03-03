<?php
return [
    'name' => 'Education Summit',
    'env' => getenv('APP_ENV', 'production'),
    'debug' => getenv('APP_DEBUG', false),
    'url' => getenv('APP_URL', 'http://localhost/summit'),
    'timezone' => 'Africa/Lagos',
    'locale' => 'en',

    'security' => [
        'csrf_token_lifetime' => 7200, // 2 hours
        'session_lifetime' => 7200,    // 2 hours
        'password_timeout' => 10800,   // 3 hours
    ],

    'mail' => [
        'from_address' => getenv('MAIL_FROM_ADDRESS'),
        'from_name' => getenv('MAIL_FROM_NAME', 'Education Summit'),
    ],

    'registration' => [
        'max_participants' => 500,
        'start_date' => '2025-02-01',
        'end_date' => '2025-03-20',
        'verification_required' => true,
    ],

    'meals' => [
        'morning' => [
            'start_time' => '06:00',
            'end_time' => '10:00',
        ],
        'evening' => [
            'start_time' => '17:00',
            'end_time' => '21:00',
        ],
    ],

    'admin' => [
        'email' => getenv('ADMIN_EMAIL'),
        'notification_email' => getenv('ADMIN_NOTIFICATION_EMAIL'),
    ],

    'logging' => [
        'path' => __DIR__ . '/../storage/logs',
        'level' => getenv('APP_ENV') === 'production' ? 'error' : 'debug',
    ],

    'upload' => [
        'max_size' => 10 * 1024 * 1024, // 10MB
        'allowed_types' => ['jpg', 'jpeg', 'png', 'pdf'],
        'path' => __DIR__ . '/../storage/uploads',
    ],
];
