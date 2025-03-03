<?php
namespace Summit\Core;

class Auth {
    private static $user = null;
    
    public static function isLoggedIn() {
        return isset($_SESSION['user_id']);
    }
    
    public static function isAdmin() {
        return self::isLoggedIn() && isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
    }
    
    public static function isValidator() {
        return self::isLoggedIn() && isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'validator';
    }
    
    public static function user() {
        if (self::$user === null && self::isLoggedIn()) {
            // Load user from database
            $pdo = Database::getInstance()->getConnection();
            $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
            $stmt->execute([$_SESSION['user_id']]);
            self::$user = $stmt->fetch();
        }
        return self::$user;
    }
    
    public static function login($user) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_role'] = $user['role'];
        self::$user = $user;
        
        // Update last login
        $pdo = Database::getInstance()->getConnection();
        $stmt = $pdo->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
        $stmt->execute([$user['id']]);
    }
    
    public static function logout() {
        session_destroy();
        self::$user = null;
    }
    
    public static function check() {
        if (!self::isLoggedIn()) {
            header('Location: ' . BASE_PATH . '/login');
            exit;
        }
    }
    
    public static function checkAdmin() {
        if (!self::isAdmin()) {
            header('Location: ' . BASE_PATH . '/dashboard');
            exit;
        }
    }
    
    public static function checkValidator() {
        if (!self::isValidator()) {
            header('Location: ' . BASE_PATH . '/dashboard');
            exit;
        }
    }
}
