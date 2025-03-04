<?php
return [
    'app' => [
        'name' => 'Summit',
        'env' => 'production',
        'debug' => false,
        'url' => 'https://your-domain.com', // Replace with your actual domain
        'timezone' => 'Africa/Lagos',
        'log_path' => '/home/username/logs', // Replace username with your Hostinger username
        'views_path' => '/home/username/public_html/views',
        'upload_path' => '/home/username/public_html/uploads',
    ],
    
    'database' => [
        'host' => 'localhost',
        'name' => 'your_db_name', // Replace with your database name
        'user' => 'your_db_user', // Replace with your database user
        'pass' => 'your_db_pass', // Replace with your database password
        'charset' => 'utf8mb4',
        'collation' => 'utf8mb4_unicode_ci',
    ],
    
    'paystack' => [
        'secret_key' => 'your_paystack_secret_key',
        'public_key' => 'your_paystack_public_key',
        'webhook_secret' => 'your_webhook_secret'
    ],
    
    'mail' => [
        'host' => 'smtp.hostinger.com',
        'port' => 465,
        'username' => 'your-email@your-domain.com',
        'password' => 'your-email-password',
        'encryption' => 'ssl',
        'from_address' => 'noreply@your-domain.com',
        'from_name' => 'Summit Event'
    ]
];
