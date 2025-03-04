<?php
/**
 * Core components loader
 * 
 * This file loads all necessary core components and handles compatibility issues
 * for deployment across different PHP environments
 */

// Ensure namespace classes are loaded
foreach ([
    'Core/SessionManager.php',
    'Core/Router.php',
    'Core/Auth.php',
    'Core/Database.php',
    'Core/ErrorHandler.php',
    'Core/RateLimiter.php',
    'Core/Config.php',
    'Core/Logger.php',
    'Core/MealManager.php',
    'security/csrf.php',
    'security/rate_limiter.php',
    'helpers.php',
    'error_handler.php'
] as $file) {
    $fullPath = __DIR__ . '/' . $file;
    if (file_exists($fullPath)) {
        require_once $fullPath;
    }
}

// Create compatibility wrappers if needed
if (!class_exists('Summit\\Core\\SessionManager')) {
    // Create a basic session handler for compatibility
    class SessionManagerCompat {
        public static function getInstance() {
            static $instance = null;
            if ($instance === null) {
                $instance = new self();
            }
            return $instance;
        }
        
        public function __construct() {
            // Start session if not already started
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
        }
        
        public function regenerateSession() {
            session_regenerate_id(true);
        }
        
        public function destroy() {
            session_destroy();
        }
        
        public function get($key, $default = null) {
            return $_SESSION[$key] ?? $default;
        }
        
        public function set($key, $value) {
            $_SESSION[$key] = $value;
        }
        
        public function remove($key) {
            unset($_SESSION[$key]);
        }
        
        public function has($key) {
            return isset($_SESSION[$key]);
        }
        
        public function clear() {
            session_unset();
        }
    }
    
    // Add to the Summit namespace
    class_alias('SessionManagerCompat', 'Summit\\Core\\SessionManager');
}

// Add RateLimiter compatibility if needed
if (!class_exists('Summit\\Core\\RateLimiter')) {
    class RateLimiterCompat {
        public function __construct() {}
        
        public function check($key, $limit = 60, $period = 60) {
            return true; // Always allow in compatibility mode
        }
        
        public function increment($key) {
            return true;
        }
        
        public function reset($key) {
            return true;
        }
    }
    
    class_alias('RateLimiterCompat', 'Summit\\Core\\RateLimiter');
}

// Function to safely check if a constant is defined
function get_config_constant($name, $default = '') {
    return defined($name) ? constant($name) : $default;
}
