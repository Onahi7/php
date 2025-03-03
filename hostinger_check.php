<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Hostinger Deployment Check</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; margin: 20px; color: #333; }
        h1, h2 { color: #2563eb; }
        .success { color: green; }
        .error { color: red; }
        .warning { color: orange; }
        pre { background: #f5f5f5; padding: 10px; border-radius: 4px; overflow-x: auto; }
        .container { max-width: 800px; margin: 0 auto; }
        .check-item { margin-bottom: 10px; padding: 10px; border: 1px solid #ddd; border-radius: 4px; }
        .check-item.success { border-color: #d1e7dd; background-color: #f8fff9; }
        .check-item.error { border-color: #f8d7da; background-color: #fff8f8; }
        .check-item.warning { border-color: #fff3cd; background-color: #fffbf0; }
    </style>
</head>
<body>
    <div class='container'>
        <h1>Hostinger Deployment Check</h1>";

// Load configuration
require_once __DIR__ . '/config/config.php';

// Load helper functions
require_once __DIR__ . '/includes/helpers.php';

// Initialize session management
require_once __DIR__ . '/includes/security/session.php';
$sessionManager = new SessionManager();

// Initialize CSRF protection
require_once __DIR__ . '/includes/security/csrf.php';

// Function to check and display status
function check_item($title, $result, $message, $status = 'success') {
    echo "<div class='check-item $status'>";
    echo "<strong>$title:</strong> $result ";
    echo "<span class='$status'>$message</span>";
    echo "</div>";
    
    return $status !== 'error';
}

// Check BASE_PATH setting
$base_path_correct = BASE_PATH === '/summit';
check_item(
    "BASE_PATH Configuration", 
    "Current value: " . BASE_PATH, 
    $base_path_correct ? "✓ Correct for Hostinger subdirectory" : "✗ Should be '/summit' for Hostinger subdirectory",
    $base_path_correct ? 'success' : 'error'
);

// Check .htaccess RewriteBase
$htaccess_content = file_get_contents(__DIR__ . '/.htaccess');
$rewrite_base_correct = strpos($htaccess_content, 'RewriteBase /summit/') !== false;
check_item(
    ".htaccess RewriteBase", 
    "RewriteBase setting", 
    $rewrite_base_correct ? "✓ Correctly set to /summit/" : "✗ Should be set to /summit/",
    $rewrite_base_correct ? 'success' : 'error'
);

// Check for environment variables support
$env_loader_exists = file_exists(__DIR__ . '/includes/env_loader.php');
check_item(
    "Environment Variables Support", 
    "EnvLoader", 
    $env_loader_exists ? "✓ Environment loader is available" : "✗ Environment loader is missing",
    $env_loader_exists ? 'success' : 'error'
);

// Check for .env.example file
$env_example_exists = file_exists(__DIR__ . '/.env.example');
check_item(
    ".env.example File", 
    "File status", 
    $env_example_exists ? "✓ Example environment file exists" : "✗ Example environment file is missing",
    $env_example_exists ? 'success' : 'warning'
);

// Check database connection
$db_connection_ok = false;
try {
    $test_query = $conn->query("SELECT 1");
    $db_connection_ok = true;
    check_item(
        "Database Connection", 
        "Connection test", 
        "✓ Successfully connected to database",
        'success'
    );
} catch (Exception $e) {
    check_item(
        "Database Connection", 
        "Connection test", 
        "✗ Failed to connect: " . $e->getMessage(),
        'error'
    );
}

// Check required directories
$directories = [
    'uploads/passports' => UPLOAD_PATH . '/passports',
    'uploads/receipts' => UPLOAD_PATH . '/receipts',
    'logs' => LOGS_PATH,
    'views/error_pages' => ERROR_PAGES_PATH
];

$all_dirs_ok = true;
foreach ($directories as $name => $dir) {
    $exists = file_exists($dir);
    $writable = is_writable($dir);
    
    if (!$exists || !$writable) {
        $all_dirs_ok = false;
    }
    
    check_item(
        "Directory: $name", 
        "Path: $dir", 
        $exists ? ($writable ? "✓ Exists and is writable" : "✗ Exists but is not writable") : "✗ Directory does not exist",
        $exists && $writable ? 'success' : 'error'
    );
}

// Check error reporting settings
$error_reporting_ok = ENVIRONMENT === 'production' && ini_get('display_errors') == 0;
check_item(
    "Error Reporting", 
    "Environment: " . ENVIRONMENT, 
    $error_reporting_ok ? "✓ Error display is disabled in production" : "✗ Error display should be disabled in production",
    $error_reporting_ok ? 'success' : 'warning'
);

// Check API keys
$api_keys_set = PAYSTACK_PUBLIC_KEY !== 'pk_test_your_actual_public_key_here' && 
                PAYSTACK_SECRET_KEY !== 'sk_test_your_actual_secret_key_here';
check_item(
    "API Keys", 
    "Paystack API Keys", 
    $api_keys_set ? "✓ API keys appear to be set" : "✗ API keys are still using placeholder values",
    $api_keys_set ? 'success' : 'warning'
);

// Check SMTP settings
$smtp_password_set = SMTP_PASS !== 'your_actual_smtp_password_here';
check_item(
    "SMTP Settings", 
    "SMTP Password", 
    $smtp_password_set ? "✓ SMTP password appears to be set" : "✗ SMTP password is still using placeholder value",
    $smtp_password_set ? 'success' : 'warning'
);

// Check CSRF functionality
$csrf_ok = false;
try {
    $token = generate_csrf_token();
    $csrf_ok = !empty($token) && CSRF::validateToken($token);
    check_item(
        "CSRF Protection", 
        "Token generation and validation", 
        $csrf_ok ? "✓ CSRF token generation and validation working correctly" : "✗ CSRF token validation failed",
        $csrf_ok ? 'success' : 'error'
    );
} catch (Exception $e) {
    check_item(
        "CSRF Protection", 
        "Token generation", 
        "✗ CSRF token generation failed: " . $e->getMessage(),
        'error'
    );
}

// Check helpers.php inclusion
$helpers_included = function_exists('generate_csrf_token');
check_item(
    "Helper Functions", 
    "generate_csrf_token()", 
    $helpers_included ? "✓ Helper functions are properly included" : "✗ Helper functions are not properly included",
    $helpers_included ? 'success' : 'error'
);

// Overall readiness
$is_ready = $base_path_correct && $rewrite_base_correct && $db_connection_ok && $all_dirs_ok && $csrf_ok && $helpers_included;

echo "<h2>Deployment Readiness</h2>";
if ($is_ready) {
    echo "<div class='check-item success'><strong>Overall Status:</strong> ✓ Your application appears to be ready for Hostinger deployment!</div>";
} else {
    echo "<div class='check-item error'><strong>Overall Status:</strong> ✗ Your application has issues that need to be fixed before deployment.</div>";
}

// Recommendations
echo "<h2>Recommendations</h2>";
echo "<ul>";

if (!$base_path_correct) {
    echo "<li>Update BASE_PATH in config.php to '/summit'</li>";
}

if (!$rewrite_base_correct) {
    echo "<li>Update RewriteBase in .htaccess to '/summit/'</li>";
}

if (!$db_connection_ok) {
    echo "<li>Check your database credentials and ensure they match your Hostinger database settings</li>";
}

if (!$all_dirs_ok) {
    echo "<li>Ensure all required directories exist and are writable</li>";
}

if (!$error_reporting_ok) {
    echo "<li>Set ENVIRONMENT to 'production' in config.php to disable error display</li>";
}

if (!$api_keys_set) {
    echo "<li>Update Paystack API keys with your actual values</li>";
}

if (!$smtp_password_set) {
    echo "<li>Update SMTP password with your actual value</li>";
}

if (!$csrf_ok || !$helpers_included) {
    echo "<li>Fix CSRF token generation and validation by ensuring helpers.php is properly included</li>";
}

echo "<li>After uploading to Hostinger, run the installation script (install.php) to set up the database</li>";
echo "<li>Use the fix_permissions.php script to ensure proper file permissions</li>";
echo "<li>Consider creating a .env file based on .env.example for sensitive information</li>";
echo "</ul>";

echo "<h2>Testing Tools</h2>";
echo "<ul>";
echo "<li><a href='" . BASE_PATH . "/test_csrf.php'>Test CSRF Token Generation</a> - Verify that CSRF tokens are working correctly</li>";
echo "<li><a href='" . BASE_PATH . "/verify.php'>Run System Verification</a> - Check system requirements and configuration</li>";
echo "<li><a href='" . BASE_PATH . "/fix_permissions.php'>Fix Permissions</a> - Set correct permissions for directories and files</li>";
echo "</ul>";

echo "</div>
</body>
</html>";
