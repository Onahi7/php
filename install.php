<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/includes/core/Config.php';
require_once __DIR__ . '/includes/core/Database.php';
require_once __DIR__ . '/includes/core/ErrorHandler.php';

use Summit\Core\Config;
use Summit\Core\Database;
use Summit\Core\ErrorHandler;

echo "<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Education Summit - Installation</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; margin: 20px; color: #333; }
        h1, h2 { color: #2563eb; }
        .success { color: green; }
        .error { color: red; }
        .warning { color: orange; }
        pre { background: #f5f5f5; padding: 10px; border-radius: 4px; overflow-x: auto; }
        .container { max-width: 800px; margin: 0 auto; }
        .step { margin-bottom: 20px; padding: 15px; border: 1px solid #ddd; border-radius: 4px; }
        .step-header { display: flex; justify-content: space-between; align-items: center; }
        .step-content { margin-top: 10px; }
        button { background: #2563eb; color: white; border: none; padding: 8px 16px; border-radius: 4px; cursor: pointer; }
        button:hover { background: #1d4ed8; }
        .env-form { margin: 20px 0; }
        .env-form label { display: block; margin: 10px 0 5px; }
        .env-form input { width: 100%; padding: 8px; margin-bottom: 10px; }
    </style>
</head>
<body>
    <div class='container'>
        <h1>Education Summit - Installation</h1>";

// Function to check and create directory
function check_and_create_directory($dir, $permissions = 0755) {
    echo "<div>Checking directory: <strong>$dir</strong> ... ";
    
    if (!file_exists($dir)) {
        if (mkdir($dir, $permissions, true)) {
            echo "<span class='success'>Created successfully</span></div>";
        } else {
            echo "<span class='error'>Failed to create</span></div>";
            return false;
        }
    } else {
        echo "<span class='success'>Already exists</span></div>";
        
        // Check permissions
        $current_perms = substr(sprintf('%o', fileperms($dir)), -4);
        if ($current_perms != sprintf('%04o', $permissions)) {
            if (chmod($dir, $permissions)) {
                echo "<span class='success'>Permissions updated</span></div>";
            } else {
                echo "<span class='error'>Failed to update permissions</span></div>";
            }
        }
    }
    
    return true;
}

// Step 1: Check Requirements
echo "<div class='step'>
    <div class='step-header'>
        <h2>Step 1: System Requirements</h2>
    </div>
    <div class='step-content'>";

$requirements = [
    'PHP Version >= 7.4' => version_compare(PHP_VERSION, '7.4.0', '>='),
    'PDO Extension' => extension_loaded('pdo'),
    'MySQL Extension' => extension_loaded('pdo_mysql'),
    'Curl Extension' => extension_loaded('curl'),
    'GD Extension' => extension_loaded('gd'),
    'JSON Extension' => extension_loaded('json'),
    'OpenSSL Extension' => extension_loaded('openssl'),
    'Fileinfo Extension' => extension_loaded('fileinfo'),
];

$all_requirements_met = true;
foreach ($requirements as $requirement => $met) {
    echo "<div>$requirement ... ";
    if ($met) {
        echo "<span class='success'>✓ Met</span></div>";
    } else {
        echo "<span class='error'>✗ Not Met</span></div>";
        $all_requirements_met = false;
    }
}

if (!$all_requirements_met) {
    echo "<p class='error'>Please install all required extensions before continuing.</p>";
    exit;
}

echo "</div></div>";

// Step 2: Directory Structure
echo "<div class='step'>
    <div class='step-header'>
        <h2>Step 2: Directory Structure</h2>
    </div>
    <div class='step-content'>";

$base_dir = __DIR__;
$directories = [
    $base_dir . '/public/assets/css',
    $base_dir . '/public/assets/js',
    $base_dir . '/public/assets/images',
    $base_dir . '/config',
    $base_dir . '/includes/core',
    $base_dir . '/includes/auth',
    $base_dir . '/includes/payment',
    $base_dir . '/storage/logs',
    $base_dir . '/storage/uploads/passports',
    $base_dir . '/storage/uploads/receipts',
    $base_dir . '/storage/cache',
    $base_dir . '/views/layouts',
    $base_dir . '/views/components',
    $base_dir . '/views/auth',
    $base_dir . '/views/admin',
    $base_dir . '/views/user',
    $base_dir . '/views/payment',
    $base_dir . '/views/emails',
    $base_dir . '/views/errors'
];

$all_dirs_created = true;
foreach ($directories as $dir) {
    if (!check_and_create_directory($dir)) {
        $all_dirs_created = false;
    }
}

if ($all_dirs_created) {
    echo "<p class='success'>All directories created successfully!</p>";
} else {
    echo "<p class='error'>Some directories could not be created. Please check permissions.</p>";
    exit;
}

echo "</div></div>";

// Step 3: Environment Configuration
echo "<div class='step'>
    <div class='step-header'>
        <h2>Step 3: Environment Configuration</h2>
    </div>
    <div class='step-content'>";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_env'])) {
    $env_content = "# Application
APP_ENV=" . $_POST['app_env'] . "
APP_DEBUG=" . ($_POST['app_debug'] ? 'true' : 'false') . "
APP_URL=" . $_POST['app_url'] . "
APP_NAME=\"Education Summit\"

# Database
DB_HOST=" . $_POST['db_host'] . "
DB_NAME=" . $_POST['db_name'] . "
DB_USER=" . $_POST['db_user'] . "
DB_PASSWORD=" . $_POST['db_password'] . "

# Payment Gateway - Paystack
PAYSTACK_SECRET_KEY=" . $_POST['paystack_secret'] . "
PAYSTACK_PUBLIC_KEY=" . $_POST['paystack_public'] . "

# Mail
MAIL_FROM_ADDRESS=" . $_POST['mail_from'] . "
MAIL_FROM_NAME=\"Education Summit\"

# Admin
ADMIN_EMAIL=" . $_POST['admin_email'] . "
ADMIN_NOTIFICATION_EMAIL=" . $_POST['admin_notification'] . "";

    if (file_put_contents($base_dir . '/.env', $env_content)) {
        echo "<p class='success'>Environment file created successfully!</p>";
        
        // Initialize the database
        try {
            $db = Database::getInstance();
            $db->initializeDatabase();
            echo "<p class='success'>Database initialized successfully!</p>";
        } catch (Exception $e) {
            echo "<p class='error'>Database initialization failed: " . htmlspecialchars($e->getMessage()) . "</p>";
            exit;
        }
    } else {
        echo "<p class='error'>Failed to create environment file!</p>";
        exit;
    }
} else {
    echo "<form method='post' class='env-form'>
        <h3>Application Settings</h3>
        <label>Environment:</label>
        <select name='app_env'>
            <option value='development'>Development</option>
            <option value='production'>Production</option>
        </select>
        
        <label>Debug Mode:</label>
        <input type='checkbox' name='app_debug' value='1'>
        
        <label>App URL:</label>
        <input type='text' name='app_url' value='http://localhost/summit' required>
        
        <h3>Database Settings</h3>
        <label>Host:</label>
        <input type='text' name='db_host' value='localhost' required>
        
        <label>Database Name:</label>
        <input type='text' name='db_name' required>
        
        <label>Username:</label>
        <input type='text' name='db_user' required>
        
        <label>Password:</label>
        <input type='password' name='db_password' required>
        
        <h3>Payment Settings</h3>
        <label>Paystack Secret Key:</label>
        <input type='text' name='paystack_secret' required>
        
        <label>Paystack Public Key:</label>
        <input type='text' name='paystack_public' required>
        
        <h3>Mail Settings</h3>
        <label>From Address:</label>
        <input type='email' name='mail_from' required>
        
        <h3>Admin Settings</h3>
        <label>Admin Email:</label>
        <input type='email' name='admin_email' required>
        
        <label>Notification Email:</label>
        <input type='email' name='admin_notification' required>
        
        <button type='submit' name='create_env'>Create Configuration</button>
    </form>";
}

echo "</div></div>";

// Final step: Installation status
echo "<div class='step'>
    <div class='step-header'>
        <h2>Final Step</h2>
    </div>
    <div class='step-content'>";

if (file_exists($base_dir . '/.env') && $all_dirs_created) {
    echo "<p class='success'>Installation completed successfully!</p>
          <p>Next steps:</p>
          <ol>
              <li>Create an admin user by running: <pre>php install/create-admin.php</pre></li>
              <li>Remove the install directory and install.php for security</li>
              <li>Configure your web server (Apache/Nginx)</li>
              <li>Start using your application!</li>
          </ol>";
} else {
    echo "<p class='warning'>Installation is not complete. Please complete all steps above.</p>";
}

echo "</div></div>
    </div>
</body>
</html>";
