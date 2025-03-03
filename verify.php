<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>System Verification</h1>";

function checkDirectory($dir) {
    if (!file_exists($dir)) {
        mkdir($dir, 0755, true);
    }
    return [
        'exists' => file_exists($dir),
        'writable' => is_writable($dir),
        'permissions' => substr(sprintf('%o', fileperms($dir)), -4)
    ];
}

// Test database connection
try {
    require_once __DIR__ . '/config/database.php';
    $test_query = $conn->query("SELECT 1");
    echo "<p style='color: green'>✓ Database connection successful</p>";
} catch (Exception $e) {
    echo "<p style='color: red'>✗ Database connection failed: " . htmlspecialchars($e->getMessage()) . "</p>";
}

// Check directories
$directories = [
    'assets/css',
    'assets/js',
    'uploads/passports',
    'uploads/receipts',
    'logs',
    'tags'
];

echo "<h2>Directory Check:</h2>";
foreach ($directories as $dir) {
    $status = checkDirectory($dir);
    echo "<p>";
    echo $dir . ": ";
    if ($status['exists'] && $status['writable']) {
        echo "<span style='color: green'>✓ OK</span>";
    } else {
        echo "<span style='color: red'>✗ Issue</span>";
    }
    echo " (Permissions: " . $status['permissions'] . ")";
    echo "</p>";
}

// Check required files
$required_files = [
    '.htaccess',
    'config/config.php',
    'includes/init.php',
    'index.php'
];

echo "<h2>File Check:</h2>";
foreach ($required_files as $file) {
    echo "<p>";
    echo $file . ": ";
    if (file_exists($file)) {
        echo "<span style='color: green'>✓ Found</span>";
    } else {
        echo "<span style='color: red'>✗ Missing</span>";
    }
    echo "</p>";
}

// Display PHP Configuration
echo "<h2>PHP Configuration:</h2>";
echo "<pre>";
echo "PHP Version: " . phpversion() . "\n";
echo "Display Errors: " . ini_get('display_errors') . "\n";
echo "Upload Max Filesize: " . ini_get('upload_max_filesize') . "\n";
echo "Post Max Size: " . ini_get('post_max_size') . "\n";
echo "Session Save Path: " . session_save_path() . "\n";
echo "</pre>";
?>
<style>
    body { font-family: Arial, sans-serif; margin: 20px; }
    h1, h2 { color: #2563eb; }
    pre { background: #f5f5f5; padding: 10px; border-radius: 4px; }
</style>

