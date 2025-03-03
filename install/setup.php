<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database configuration
$DB_HOST = 'localhost';
$DB_NAME = 'u633250213_summit';
$DB_USER = 'u633250213_summit';
$DB_PASS = 'Iamhardy_7';

try {
    // Create database connection
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
    
    echo "Database connection successful!\n";
    
    // Create required directories
    $directories = [
        __DIR__ . '/../storage/logs',
        __DIR__ . '/../storage/uploads',
        __DIR__ . '/../storage/cache',
        __DIR__ . '/../storage/uploads/passports',
        __DIR__ . '/../storage/uploads/receipts',
    ];
    
    foreach ($directories as $dir) {
        if (!file_exists($dir)) {
            if (mkdir($dir, 0777, true)) {
                echo "Created directory: $dir\n";
                chmod($dir, 0777);
            } else {
                echo "Failed to create directory: $dir\n";
            }
        } else {
            echo "Directory already exists: $dir\n";
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
            validator_id INT NOT NULL,
            meal_type ENUM('morning', 'evening') NOT NULL,
            served_at DATETIME NOT NULL,
            created_at DATETIME NOT NULL,
            FOREIGN KEY (user_id) REFERENCES users(id),
            FOREIGN KEY (validator_id) REFERENCES users(id),
            UNIQUE KEY unique_meal (user_id, meal_type, DATE(served_at))
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
    
    foreach ($tables as $sql) {
        try {
            $pdo->exec($sql);
            echo "Created table successfully!\n";
        } catch (PDOException $e) {
            echo "Error creating table: " . $e->getMessage() . "\n";
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
    
    $stmt = $pdo->prepare("INSERT INTO settings (name, value, created_at) VALUES (?, ?, NOW())");
    
    foreach ($settings as $name => $value) {
        try {
            $stmt->execute([$name, is_bool($value) ? (int)$value : $value]);
            echo "Added setting: $name\n";
        } catch (PDOException $e) {
            if ($e->getCode() != 23000) { // Ignore duplicate entry errors
                echo "Error adding setting $name: " . $e->getMessage() . "\n";
            }
        }
    }
    
    echo "\nInstallation completed successfully!\n";
    echo "Please create an admin user using: php install/create-admin.php\n";
    
} catch (PDOException $e) {
    echo "Database Error: " . $e->getMessage() . "\n";
    echo "Database connection failed. Please check the error log.\n";
    exit(1);
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
