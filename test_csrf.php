<?php
// Load configuration
require_once __DIR__ . '/config/config.php';

// Initialize session management
require_once __DIR__ . '/includes/security/session.php';
$sessionManager = new SessionManager();

// Initialize CSRF protection
require_once __DIR__ . '/includes/security/csrf.php';

// Load helper functions
require_once __DIR__ . '/includes/helpers.php';

// Test CSRF token generation
echo "<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>CSRF Token Test</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; margin: 20px; color: #333; }
        h1, h2 { color: #2563eb; }
        pre { background: #f5f5f5; padding: 10px; border-radius: 4px; overflow-x: auto; }
        .success { color: green; }
        .error { color: red; }
    </style>
</head>
<body>
    <h1>CSRF Token Test</h1>";

// Test the generate_csrf_token function
try {
    $token = generate_csrf_token();
    echo "<h2>Generated CSRF Token:</h2>";
    echo "<pre>" . htmlspecialchars($token) . "</pre>";
    
    echo "<h2>Session Contents:</h2>";
    echo "<pre>";
    var_dump($_SESSION);
    echo "</pre>";
    
    echo "<h2>Validation Test:</h2>";
    $valid = CSRF::validateToken($token);
    if ($valid) {
        echo "<p class='success'>✅ Token validation successful!</p>";
    } else {
        echo "<p class='error'>❌ Token validation failed!</p>";
    }
    
    echo "<h2>Form Test:</h2>";
    echo "<form method='post' action='" . BASE_PATH . "/test_csrf_submit.php'>";
    echo "<input type='hidden' name='csrf_token' value='" . htmlspecialchars(generate_csrf_token()) . "'>";
    echo "<button type='submit'>Test Form Submission</button>";
    echo "</form>";
    
} catch (Exception $e) {
    echo "<h2 class='error'>Error:</h2>";
    echo "<pre>" . htmlspecialchars($e->getMessage()) . "</pre>";
    
    echo "<h2>Debug Information:</h2>";
    echo "<pre>";
    echo "PHP Version: " . phpversion() . "\n";
    echo "Session Status: " . session_status() . "\n";
    echo "Session ID: " . session_id() . "\n";
    echo "CSRF Class Exists: " . (class_exists('CSRF') ? 'Yes' : 'No') . "\n";
    echo "generate_csrf_token Function Exists: " . (function_exists('generate_csrf_token') ? 'Yes' : 'No') . "\n";
    echo "</pre>";
}

echo "</body>
</html>";
