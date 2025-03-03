<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database configuration
$DB_HOST = 'localhost';
$DB_NAME = 'u633250213_summit';
$DB_USER = 'u633250213_summit';
$DB_PASS = 'Iamhardy_7';

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Get admin details
        $name = $_POST['name'] ?? '';
        $email = $_POST['email'] ?? '';
        $phone = $_POST['phone'] ?? '';
        $password = $_POST['password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
        
        // Validate input
        if (empty($name) || empty($email) || empty($password)) {
            throw new Exception("All fields are required");
        }
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception("Invalid email format");
        }

        if ($password !== $confirm_password) {
            throw new Exception("Passwords do not match");
        }

        if (strlen($password) < 8) {
            throw new Exception("Password must be at least 8 characters long");
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
        
        // Check if admin already exists
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE role = 'admin'");
        $stmt->execute();
        if ($stmt->fetchColumn() > 0) {
            throw new Exception("An admin user already exists");
        }
        
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
                    ?, 'admin_created', 'Admin account created during installation', ?, NOW()
                )
            ");
            
            $stmt->execute([$userId, $_SERVER['REMOTE_ADDR']]);
            
            // Commit transaction
            $pdo->commit();
            
            $success = "Admin user created successfully! You can now log in at /admin";
            
        } catch (Exception $e) {
            $pdo->rollBack();
            throw $e;
        }
        
    } catch (Exception $e) {
        $error = "Failed to create admin: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Admin User - Education Summit</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; margin: 20px; color: #333; }
        h1 { color: #2563eb; }
        .container { max-width: 500px; margin: 0 auto; }
        .success { color: green; padding: 10px; border: 1px solid green; margin: 10px 0; }
        .error { color: red; padding: 10px; border: 1px solid red; margin: 10px 0; }
        form { margin: 20px 0; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; }
        input { width: 100%; padding: 8px; margin-bottom: 10px; border: 1px solid #ddd; border-radius: 4px; }
        button { background: #2563eb; color: white; border: none; padding: 10px 20px; border-radius: 4px; cursor: pointer; }
        button:hover { background: #1d4ed8; }
        .password-requirements { font-size: 0.9em; color: #666; margin: 5px 0; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Create Admin User</h1>
        
        <?php if ($success): ?>
            <div class="success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        
        <?php if (!$success): ?>
            <form method="post">
                <div class="form-group">
                    <label for="name">Full Name:</label>
                    <input type="text" id="name" name="name" required>
                </div>
                
                <div class="form-group">
                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" required>
                </div>
                
                <div class="form-group">
                    <label for="phone">Phone:</label>
                    <input type="tel" id="phone" name="phone" required>
                </div>
                
                <div class="form-group">
                    <label for="password">Password:</label>
                    <input type="password" id="password" name="password" required minlength="8">
                    <div class="password-requirements">Password must be at least 8 characters long</div>
                </div>
                
                <div class="form-group">
                    <label for="confirm_password">Confirm Password:</label>
                    <input type="password" id="confirm_password" name="confirm_password" required minlength="8">
                </div>
                
                <button type="submit">Create Admin User</button>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>
