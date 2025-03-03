<?php
return [
    'default' => 'mysql',

    'connections' => [
        'mysql' => [
            'driver' => 'mysql',
            'host' => getenv('DB_HOST', 'localhost'),
            'database' => getenv('DB_NAME'),
            'username' => getenv('DB_USER'),
            'password' => getenv('DB_PASSWORD'),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'timezone' => '+01:00',
        ],
    ],

    'schema' => [
        'version' => '1.0.0',
        'tables' => [
            'users' => "CREATE TABLE IF NOT EXISTS users (
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
            
            'profiles' => "CREATE TABLE IF NOT EXISTS profiles (
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
            
            'payments' => "CREATE TABLE IF NOT EXISTS payments (
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
            
            'meal_records' => "CREATE TABLE IF NOT EXISTS meal_records (
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
            
            'settings' => "CREATE TABLE IF NOT EXISTS settings (
                id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(255) NOT NULL UNIQUE,
                value TEXT,
                created_at DATETIME NOT NULL,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            )",

            'activity_logs' => "CREATE TABLE IF NOT EXISTS activity_logs (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT,
                action VARCHAR(255) NOT NULL,
                description TEXT,
                ip_address VARCHAR(45),
                user_agent VARCHAR(255),
                created_at DATETIME NOT NULL,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
            )"
        ]
    ],
];
