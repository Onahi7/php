<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>System Access Test</h1>";

// Test file access
echo "<h2>File Access:</h2>";
$test_files = [
    'index.php',
    'assets/css/styles.css',
    'pages/home.php'
];

foreach ($test_files as $file) {
    echo "Testing $file: ";
    if (file_exists(__DIR__ . '/' . $file)) {
        echo "✅ File exists and is readable<br>";
    } else {
        echo "❌ File not accessible<br>";
    }
}

// Test directory permissions
echo "<h2>Directory Permissions:</h2>";
$test_dirs = [
    '.',
    'assets',
    'pages',
    'includes'
];

foreach ($test_dirs as $dir) {
    echo "Testing $dir: ";
    $full_path = __DIR__ . '/' . $dir;
    if (is_dir($full_path)) {
        echo "Directory exists. ";
        if (is_readable($full_path)) {
            echo "✅ Readable ";
        } else {
            echo "❌ Not readable ";
        }
        echo "Permissions: " . substr(sprintf('%o', fileperms($full_path)), -4);
        echo "<br>";
    } else {
        echo "❌ Directory not found<br>";
    }
}

// Test PHP configuration
echo "<h2>PHP Configuration:</h2>";
echo "<pre>";
echo "PHP Version: " . phpversion() . "\n";
echo "display_errors: " . ini_get('display_errors') . "\n";
echo "error_reporting: " . ini_get('error_reporting') . "\n";
echo "Server Software: " . $_SERVER['SERVER_SOFTWARE'] . "\n";
echo "</pre>";

// Test routing
echo "<h2>Route Test:</h2>";
echo "Current URI: " . $_SERVER['REQUEST_URI'] . "<br>";
echo "Script Name: " . $_SERVER['SCRIPT_NAME'] . "<br>";
?>
<style>
    body { font-family: Arial, sans-serif; margin: 20px; }
    h1, h2 { color: #2563eb; }
    pre { background: #f5f5f5; padding: 10px; border-radius: 4px; }
</style>

