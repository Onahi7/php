<?php
/**
 * Summit Application Bootstrap File
 * 
 * This file handles core loading without depending on complex autoloading
 */

// Basic error handling
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set proper character encoding
header('Content-Type: text/html; charset=UTF-8');

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Define base path - adjust as needed for your environment
define('BASE_PATH', '/summit');
define('SITE_URL', 'https://conference.nappsnasarawa.com');
define('ROOT_DIR', __DIR__);

// Load compatibility layer
require_once ROOT_DIR . '/includes/load_core.php';

// Check if running in installation mode
$requestPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$installPathPattern = '#^' . BASE_PATH . '/install#';
if (preg_match($installPathPattern, $requestPath)) {
    // Skip database connection for installation pages
    return;
}

// Try to load configuration
$configPath = ROOT_DIR . '/config/config.php';
if (file_exists($configPath)) {
    require_once $configPath;
} else {
    // Redirect to installer if config doesn't exist
    if (!preg_match($installPathPattern, $requestPath)) {
        header('Location: ' . BASE_PATH . '/install/index.php');
        exit;
    }
}

// Load required files directly
$core_files = [
    // Core classes
    'includes/core/Database.php',
    'includes/core/Config.php',
    'includes/core/ErrorHandler.php',
    'includes/core/Logger.php',
    'includes/helpers.php',
    
    // Security
    'includes/security/csrf.php',
    'includes/security/auth.php',
    
    // App specific
    'includes/core/MealManager.php',
];

// Load all core files
foreach ($core_files as $file) {
    $path = ROOT_DIR . '/' . $file;
    if (file_exists($path)) {
        require_once $path;
    } else {
        trigger_error("Core file not found: $file", E_USER_WARNING);
    }
}

// Database connection - only if config exists
if (defined('DB_HOST')) {
    try {
        $conn = new PDO(
            "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
            DB_USER,
            DB_PASS,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false
            ]
        );
    } catch (PDOException $e) {
        die("Database connection failed: " . $e->getMessage());
    }
}

// Simple router function
function route($path, $callback) {
    $request_path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $base_path = BASE_PATH;
    
    // Remove base path from request
    if (strpos($request_path, $base_path) === 0) {
        $request_path = substr($request_path, strlen($base_path));
    }
    
    // Ensure path starts with /
    if ($request_path[0] !== '/') {
        $request_path = '/' . $request_path;
    }
    
    // Remove trailing slash if not root
    if ($request_path !== '/' && substr($request_path, -1) === '/') {
        $request_path = rtrim($request_path, '/');
    }
    
    // Check if route matches
    if ($path === $request_path) {
        call_user_func($callback);
        return true;
    }
    
    return false;
}

// Function to check if user is logged in
function is_logged_in() {
    return isset($_SESSION['user_id']);
}

// Function to check if user has role
function has_role($role) {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === $role;
}

// Function to redirect
function redirect($url) {
    header("Location: $url");
    exit;
}

// Function to render view
function view($view, $data = []) {
    // Add default data
    $data['title'] = $data['title'] ?? 'Education Summit';
    $data['base_path'] = BASE_PATH;
    
    extract($data);
    
    // Use layout if view is not a standalone page
    $viewPath = ROOT_DIR . '/views/' . $view . '.php';
    
    if (file_exists($viewPath)) {
        // If layout.php exists and this isn't already a layout
        $layoutPath = ROOT_DIR . '/views/layout.php';
        if (file_exists($layoutPath) && strpos($view, 'layout') === false) {
            $content = function() use ($viewPath, $data) {
                extract($data);
                require $viewPath;
            };
            require $layoutPath;
        } else {
            require $viewPath;
        }
    } else {
        echo "View not found: $view";
    }
}
