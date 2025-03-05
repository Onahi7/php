<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$error = '';
$success = '';

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get database configuration from form
    $DB_HOST = $_POST['db_host'] ?? 'localhost';
    $DB_NAME = $_POST['db_name'] ?? '';
    $DB_USER = $_POST['db_user'] ?? '';
    $DB_PASS = $_POST['db_pass'] ?? '';
    
    // Validate inputs
    if (empty($DB_NAME) || empty($DB_USER)) {
        $error = "Database name and username are required";
    } else {
        try {
            // Test database connection
            $pdo = new PDO(
                "mysql:host=$DB_HOST;dbname=$DB_NAME;charset=utf8mb4",
                $DB_USER,
                $DB_PASS,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ]
            );
            
            // Set timezone
            $pdo->exec("SET time_zone = '+01:00'");
            
            // Create required directories
            $directories = [
                __DIR__ . '/../storage/logs',
                __DIR__ . '/../storage/uploads',
                __DIR__ . '/../storage/cache',
                __DIR__ . '/../storage/uploads/passports',
                __DIR__ . '/../storage/uploads/receipts',
            ];
            
            $dirResults = [];
            
            foreach ($directories as $dir) {
                if (!file_exists($dir)) {
                    if (mkdir($dir, 0777, true)) {
                        $dirResults[] = "Created directory: $dir";
                        chmod($dir, 0777);
                    } else {
                        $dirResults[] = "Failed to create directory: $dir";
                    }
                } else {
                    $dirResults[] = "Directory already exists: $dir";
                }
            }
            
            // Create tables
            $tables = [
                "CREATE TABLE IF NOT EXISTS users (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    name VARCHAR(255) NOT NULL,
                    email VARCHAR(255) NOT NULL UNIQUE,
                    password VARCHAR(255) NOT NULL,
                    phone VARCHAR(20),
                    role ENUM('user', 'admin', 'validator') DEFAULT 'user',
                    status ENUM('active', 'inactive', 'suspended') DEFAULT 'active',
                    reset_token VARCHAR(64),
                    reset_expiry DATETIME,
                    last_login DATETIME,
                    created_at DATETIME NOT NULL,
                    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
                )",
                
                "CREATE TABLE IF NOT EXISTS profiles (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    user_id INT NOT NULL,
                    passport VARCHAR(255),
                    organization VARCHAR(255),
                    position VARCHAR(255),
                    barcode VARCHAR(255) UNIQUE,
                    created_at DATETIME NOT NULL,
                    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
                )",
                
                "CREATE TABLE IF NOT EXISTS payments (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    user_id INT NOT NULL,
                    amount DECIMAL(10,2) NOT NULL,
                    reference VARCHAR(255) NOT NULL UNIQUE,
                    status ENUM('pending', 'completed', 'failed', 'refunded', 'partially_refunded') DEFAULT 'pending',
                    payment_method VARCHAR(50),
                    refunded_amount DECIMAL(10,2) DEFAULT 0,
                    verified_at DATETIME,
                    created_at DATETIME NOT NULL,
                    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    FOREIGN KEY (user_id) REFERENCES users(id)
                )",
                
                "CREATE TABLE IF NOT EXISTS meal_records (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    user_id INT NOT NULL,
                    validated_by INT NOT NULL,
                    meal_type ENUM('morning', 'evening') NOT NULL,
                    created_at DATETIME NOT NULL,
                    FOREIGN KEY (user_id) REFERENCES users(id),
                    FOREIGN KEY (validated_by) REFERENCES users(id),
                    UNIQUE KEY unique_meal (user_id, meal_type, DATE(created_at))
                )",
                
                "CREATE TABLE IF NOT EXISTS settings (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    name VARCHAR(255) NOT NULL UNIQUE,
                    value TEXT,
                    created_at DATETIME NOT NULL,
                    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
                )",

                "CREATE TABLE IF NOT EXISTS activity_logs (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    user_id INT,
                    action VARCHAR(255) NOT NULL,
                    description TEXT,
                    ip_address VARCHAR(45),
                    user_agent VARCHAR(255),
                    created_at DATETIME NOT NULL,
                    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
                )"
            ];
            
            $tableResults = [];
            
            foreach ($tables as $sql) {
                try {
                    $pdo->exec($sql);
                    $tableResults[] = "Created table successfully!";
                } catch (PDOException $e) {
                    $tableResults[] = "Error creating table: " . $e->getMessage();
                }
            }
            
            // Add default settings
            $settings = [
                'site_name' => 'Education Summit',
                'registration_open' => true,
                'max_participants' => 500,
                'registration_fee' => 5000,
                'late_registration_fee' => 7500,
                'late_registration_date' => '2025-03-15',
                'payment_deadline' => '2025-03-20',
            ];
            
            $settingResults = [];
            $stmt = $pdo->prepare("INSERT INTO settings (name, value, created_at) VALUES (?, ?, NOW())");
            
            foreach ($settings as $name => $value) {
                try {
                    $stmt->execute([$name, is_bool($value) ? (int)$value : $value]);
                    $settingResults[] = "Added setting: $name";
                } catch (PDOException $e) {
                    if ($e->getCode() != 23000) { // Ignore duplicate entry errors
                        $settingResults[] = "Error adding setting $name: " . $e->getMessage();
                    }
                }
            }
            
            // Create config file
            $configContent = <<<EOD
<?php
// Database Configuration
define('DB_HOST', '$DB_HOST');
define('DB_NAME', '$DB_NAME');
define('DB_USER', '$DB_USER');
define('DB_PASS', '$DB_PASS');

// Site Configuration
define('BASE_PATH', '');
define('SITE_URL', 'https://' . \$_SERVER['HTTP_HOST']);
define('ROOT_DIR', __DIR__);

// Storage Paths
define('STORAGE_PATH', ROOT_DIR . '/storage');
define('LOGS_PATH', STORAGE_PATH . '/logs');
define('UPLOADS_PATH', STORAGE_PATH . '/uploads');
define('CACHE_PATH', STORAGE_PATH . '/cache');

// Error Settings
define('DEBUG_MODE', false);

// Session Settings
define('SESSION_LIFETIME', 7200); // 2 hours
define('SESSION_NAME', 'summit_session');

// Security Settings
define('PASSWORD_COST', 12);

// Meal Types
define('MEAL_MORNING', 'morning');
define('MEAL_EVENING', 'evening');

// Error Pages Path
define('ERROR_PAGES_PATH', ROOT_DIR . '/views/errors');

// TZ
date_default_timezone_set('Africa/Lagos');
EOD;

            $configDir = __DIR__ . '/../config';
            if (!file_exists($configDir)) {
                mkdir($configDir, 0777, true);
            }
            
            $configFile = $configDir . '/config.php';
            if (file_put_contents($configFile, $configContent)) {
                $success = "Database setup completed successfully! Configuration file created.";
            } else {
                $error = "Failed to create configuration file.";
            }
            
        } catch (PDOException $e) {
            $error = "Database Error: " . $e->getMessage();
        } catch (Exception $e) {
            $error = "Error: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Setup - Summit Installation</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-50 min-h-screen">
    <div class="container mx-auto px-4 py-12">
        <div class="max-w-3xl mx-auto bg-white rounded-lg shadow-md overflow-hidden">
            <div class="p-6 bg-blue-600 text-white">
                <h1 class="text-2xl font-bold">Database Setup</h1>
                <p class="mt-2">Configure your database connection and create the required tables.</p>
            </div>
            
            <div class="p-6">
                <?php if ($error): ?>
                    <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6" role="alert">
                        <p><?php echo htmlspecialchars($error); ?></p>
                    </div>
                <?php endif; ?>
                
                <?php if ($success): ?>
                    <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6" role="alert">
                        <p><?php echo htmlspecialchars($success); ?></p>
                        <div class="mt-4">
                            <a href="create-admin.php" class="inline-block bg-green-600 hover:bg-green-700 text-white py-2 px-4 rounded">
                                Proceed to Create Admin User
                            </a>
                        </div>
                    </div>
                <?php else: ?>
                    <form method="POST" action="" class="space-y-6">
                        <div>
                            <label for="db_host" class="block text-sm font-medium text-gray-700">Database Host</label>
                            <input type="text" name="db_host" id="db_host" value="localhost" required 
                                   class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                        </div>
                        
                        <div>
                            <label for="db_name" class="block text-sm font-medium text-gray-700">Database Name</label>
                            <input type="text" name="db_name" id="db_name" required 
                                   class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                        </div>
                        
                        <div>
                            <label for="db_user" class="block text-sm font-medium text-gray-700">Database Username</label>
                            <input type="text" name="db_user" id="db_user" required 
                                   class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                        </div>
                        
                        <div>
                            <label for="db_pass" class="block text-sm font-medium text-gray-700">Database Password</label>
                            <input type="password" name="db_pass" id="db_pass" 
                                   class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                        </div>
                        
                        <div class="flex justify-between pt-4">
                            <a href="index.php" class="inline-block bg-gray-500 hover:bg-gray-600 text-white py-2 px-4 rounded">
                                Back
                            </a>
                            <button type="submit" class="inline-block bg-blue-600 hover:bg-blue-700 text-white py-2 px-4 rounded">
                                Setup Database
                            </button>
                        </div>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
