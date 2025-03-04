<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database configuration
$DB_HOST = 'localhost';
$DB_NAME = 'u633250213_summit';
$DB_USER = 'u633250213_summit';
$DB_PASS = 'Iamhardy_7';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Get admin details from POST
        $name = $_POST['name'] ?? '';
        $email = $_POST['email'] ?? '';
        $phone = $_POST['phone'] ?? '';
        $password = $_POST['password'] ?? '';
        
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
                    ?, 'admin_created', 'Admin account created during installation', ?, NOW()
                )
            ");
            
            $stmt->execute([$userId, $_SERVER['REMOTE_ADDR']]);
            
            // Commit transaction
            $pdo->commit();
            
            $success = "Admin user created successfully! You can now login at /admin";
            
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
    <title>Create Admin User - Summit Installation</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; margin: 20px; }
        .container { max-width: 600px; margin: 0 auto; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; }
        input[type="text"],
        input[type="email"],
        input[type="password"] { 
            width: 100%; 
            padding: 8px; 
            border: 1px solid #ddd; 
            border-radius: 4px; 
        }
        .btn { 
            background: #2563eb; 
            color: white; 
            padding: 10px 20px; 
            border: none; 
            border-radius: 4px; 
            cursor: pointer; 
        }
        .btn:hover { background: #1d4ed8; }
        .error { color: red; margin-bottom: 15px; }
        .success { color: green; margin-bottom: 15px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Create Admin User</h1>
        
        <?php if (isset($error)): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <?php if (isset($success)): ?>
            <div class="success"><?php echo htmlspecialchars($success); ?></div>
        <?php else: ?>
            <form method="POST">
                <div class="form-group">
                    <label for="name">Name:</label>
                    <input type="text" id="name" name="name" required>
                </div>
                
                <div class="form-group">
                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" required>
                </div>
                
                <div class="form-group">
                    <label for="phone">Phone:</label>
                    <input type="text" id="phone" name="phone" required>
                </div>
                
                <div class="form-group">
                    <label for="password">Password:</label>
                    <input type="password" id="password" name="password" required>
                </div>
                
                <button type="submit" class="btn">Create Admin User</button>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>
