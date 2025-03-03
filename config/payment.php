<?php
return [
    'gateway' => 'paystack', // default payment gateway

    'paystack' => [
        'secret_key' => getenv('PAYSTACK_SECRET_KEY'),
        'public_key' => getenv('PAYSTACK_PUBLIC_KEY'),
        'test_mode' => getenv('APP_ENV') !== 'production',
    ],

    'stripe' => [
        'secret_key' => getenv('STRIPE_SECRET_KEY'),
        'public_key' => getenv('STRIPE_PUBLIC_KEY'),
        'webhook_secret' => getenv('STRIPE_WEBHOOK_SECRET'),
        'test_mode' => getenv('APP_ENV') !== 'production',
    ],

    'flutterwave' => [
        'secret_key' => getenv('FLUTTERWAVE_SECRET_KEY'),
        'public_key' => getenv('FLUTTERWAVE_PUBLIC_KEY'),
        'encryption_key' => getenv('FLUTTERWAVE_ENCRYPTION_KEY'),
        'test_mode' => getenv('APP_ENV') !== 'production',
    ],

    'settings' => [
        'currency' => 'NGN',
        'registration_fee' => 5000,
        'late_registration_fee' => 7500,
        'late_registration_date' => '2025-03-15',
        'payment_deadline' => '2025-03-20',
    ],
];
