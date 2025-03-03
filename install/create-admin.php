<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database configuration
$DB_HOST = 'localhost';
$DB_NAME = 'u633250213_summit';
$DB_USER = 'u633250213_summit';
$DB_PASS = 'Iamhardy_7';

function prompt($message) {
    echo $message . ": ";
    return trim(fgets(STDIN));
}

try {
    // Get admin details
    $name = prompt("Enter admin name");
    $email = prompt("Enter admin email");
    $phone = prompt("Enter admin phone");
    $password = prompt("Enter admin password");
    
    // Validate input
    if (empty($name) || empty($email) || empty($password)) {
        throw new Exception("All fields are required");
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception("Invalid email format");
    }
    
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
    
    // Hash password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    
    // Begin transaction
    $pdo->beginTransaction();
    
    try {
        // Create admin user
        $stmt = $pdo->prepare("
            INSERT INTO users (
                name, email, password, phone, role, status, created_at
            ) VALUES (
                ?, ?, ?, ?, 'admin', 'active', NOW()
            )
        ");
        
        $stmt->execute([$name, $email, $hashedPassword, $phone]);
        $userId = $pdo->lastInsertId();
        
        // Create admin profile
        $stmt = $pdo->prepare("
            INSERT INTO profiles (
                user_id, organization, position, created_at
            ) VALUES (
                ?, 'Education Summit', 'Administrator', NOW()
            )
        ");
        
        $stmt->execute([$userId]);
        
        // Log activity
        $stmt = $pdo->prepare("
            INSERT INTO activity_logs (
                user_id, action, description, ip_address, created_at
            ) VALUES (
                ?, 'admin_created', 'Admin account created during installation', '127.0.0.1', NOW()
            )
        ");
        
        $stmt->execute([$userId]);
        
        // Commit transaction
        $pdo->commit();
        
        echo "\nAdmin user created successfully!\n";
        echo "Login using your email and password at: /admin\n";
        
    } catch (Exception $e) {
        $pdo->rollBack();
        throw $e;
    }
    
} catch (Exception $e) {
    echo "Failed to create admin: " . $e->getMessage() . "\n";
    exit(1);
}
