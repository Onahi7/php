<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Fixing Permissions</h1>";

// Load configuration to get the correct paths
require_once __DIR__ . '/config/config.php';

// Base directory
$base_dir = __DIR__;

// Directory permissions to set
$directories = [
    '.' => 0755,
    './assets' => 0755,
    './assets/css' => 0755,
    './assets/js' => 0755,
    './assets/images' => 0755,
    './includes' => 0755,
    './includes/security' => 0755,
    './views' => 0755,
    './views/error_pages' => 0755,
    './uploads' => 0755,
    './uploads/passports' => 0777,
    './uploads/receipts' => 0777,
    './logs' => 0777,
    './config' => 0755
];

// File permissions to set
$files = [
    './.htaccess' => 0644,
    './index.php' => 0644,
    './config/config.php' => 0644,
    './config/database.php' => 0644
];

// Fix directory permissions
foreach ($directories as $dir => $perm) {
    $full_path = $base_dir . '/' . $dir;
    echo "Setting permissions for $dir: ";
    
    if (!file_exists($full_path)) {
        if (mkdir($full_path, $perm, true)) {
            echo "Created directory. ";
        } else {
            echo "Failed to create directory. ";
            continue;
        }
    }
    
    if (chmod($full_path, $perm)) {
        echo "✅ Set to " . decoct($perm) . "<br>";
    } else {
        echo "❌ Failed to set permissions<br>";
    }
}

// Fix file permissions
foreach ($files as $file => $perm) {
    $full_path = $base_dir . '/' . $file;
    echo "Setting permissions for $file: ";
    
    if (file_exists($full_path)) {
        if (chmod($full_path, $perm)) {
            echo "✅ Set to " . decoct($perm) . "<br>";
        } else {
            echo "❌ Failed to set permissions<br>";
        }
    } else {
        echo "❌ File not found<br>";
    }
}

// Display current user and group
echo "<h2>Process Information:</h2>";
echo "Current User: " . get_current_user() . "<br>";
echo "Script Owner: " . fileowner(__FILE__) . "<br>";

// Test file creation
echo "<h2>Testing File Creation:</h2>";
$test_file = LOGS_PATH . '/test.txt';
if (file_put_contents($test_file, "Test write at " . date('Y-m-d H:i:s'))) {
    echo "✅ Successfully created test file<br>";
    unlink($test_file);
} else {
    echo "❌ Failed to create test file<br>";
}
?>
<style>
    body { font-family: Arial, sans-serif; margin: 20px; }
    h1, h2 { color: #2563eb; }
</style>
