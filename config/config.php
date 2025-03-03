<?php
// Load environment variables if available
require_once __DIR__ . '/../includes/env_loader.php';

// Base configuration
define('SITE_NAME', 'North Central Education Summit');
define('SITE_URL', 'https://conference.nappsnasarawa.com');
define('BASE_PATH', EnvLoader::get('BASE_PATH', '/summit'));  // Set to /summit for Hostinger subdirectory installation

// Environment detection
define('ENVIRONMENT', EnvLoader::get('ENVIRONMENT', 'production')); // Change to 'development' for debugging

// Database configuration
define('DB_HOST', EnvLoader::get('DB_HOST', 'localhost'));
define('DB_USER', EnvLoader::get('DB_USER', 'u633250213_summit'));
define('DB_PASS', EnvLoader::get('DB_PASS', 'Iamhardy_7'));
define('DB_NAME', EnvLoader::get('DB_NAME', 'u633250213_summit'));

// Path configurations
define('ROOT_PATH', __DIR__ . '/..');
define('UPLOAD_PATH', ROOT_PATH . '/uploads');
define('LOGS_PATH', ROOT_PATH . '/logs');
define('ASSETS_PATH', ROOT_PATH . '/assets');
define('ERROR_PAGES_PATH', ROOT_PATH . '/views/error_pages');

// Summit specific settings
define('SUMMIT_DATE', '2025-03-25');
define('SUMMIT_FEE', 25000);
define('MAX_PARTICIPANTS', 500);

// Payment Configuration (Paystack)
define('PAYSTACK_PUBLIC_KEY', EnvLoader::get('PAYSTACK_PUBLIC_KEY', 'pk_test_your_actual_public_key_here'));
define('PAYSTACK_SECRET_KEY', EnvLoader::get('PAYSTACK_SECRET_KEY', 'sk_test_your_actual_secret_key_here'));

// Email Configuration
define('SMTP_HOST', EnvLoader::get('SMTP_HOST', 'smtp.hostinger.com'));
define('SMTP_PORT', EnvLoader::get('SMTP_PORT', 587));
define('SMTP_USER', EnvLoader::get('SMTP_USER', 'noreply@conference.nappsnasarawa.com'));
define('SMTP_PASS', EnvLoader::get('SMTP_PASS', 'your_actual_smtp_password_here'));
define('SMTP_FROM', EnvLoader::get('SMTP_FROM', 'noreply@conference.nappsnasarawa.com'));

// Security Configuration
define('PASSWORD_MIN_LENGTH', 8);
define('SESSION_LIFETIME', 7200);
define('CSRF_TIMEOUT', 7200);

// Rate Limiting
define('RATE_LIMIT_LOGIN', 5);
define('RATE_LIMIT_PERIOD', 300);

// Error reporting based on environment
if (ENVIRONMENT === 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// Create database connection
try {
    $conn = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );
} catch (PDOException $e) {
    error_log("Database Error: " . $e->getMessage());
    die("Database connection failed. Please check the error log.");
}

// Initialize required directories
$required_dirs = [
    UPLOAD_PATH . '/passports',
    UPLOAD_PATH . '/receipts',
    LOGS_PATH,
    ASSETS_PATH . '/tags',
    ERROR_PAGES_PATH
];

foreach ($required_dirs as $dir) {
    if (!file_exists($dir)) {
        mkdir($dir, 0755, true);
    }
}

// Create a sample .env file if it doesn't exist
if (!file_exists(__DIR__ . '/../.env') && !file_exists(__DIR__ . '/../.env.example')) {
    EnvLoader::createSampleEnvFile(__DIR__ . '/../.env.example');
}
