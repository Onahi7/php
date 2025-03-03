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

// Test CSRF token validation
echo "<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>CSRF Token Submission Test</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; margin: 20px; color: #333; }
        h1, h2 { color: #2563eb; }
        pre { background: #f5f5f5; padding: 10px; border-radius: 4px; overflow-x: auto; }
        .success { color: green; }
        .error { color: red; }
    </style>
</head>
<body>
    <h1>CSRF Token Submission Test</h1>";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['csrf_token'])) {
        $token = $_POST['csrf_token'];
        
        echo "<h2>Received CSRF Token:</h2>";
        echo "<pre>" . htmlspecialchars($token) . "</pre>";
        
        echo "<h2>Session Contents:</h2>";
        echo "<pre>";
        var_dump($_SESSION);
        echo "</pre>";
        
        echo "<h2>Validation Result:</h2>";
        $valid = CSRF::validateToken($token);
        if ($valid) {
            echo "<p class='success'>✅ Token validation successful!</p>";
        } else {
            echo "<p class='error'>❌ Token validation failed!</p>";
        }
    } else {
        echo "<p class='error'>❌ No CSRF token received!</p>";
    }
} else {
    echo "<p class='error'>❌ This page should be accessed via POST request!</p>";
}

echo "<p><a href='" . BASE_PATH . "/test_csrf.php'>Back to Test Page</a></p>";

echo "</body>
</html>";
