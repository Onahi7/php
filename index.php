<?php
// Set proper character encoding
header('Content-Type: text/html; charset=UTF-8');

// Load configuration
require_once __DIR__ . '/config/config.php';

// Load core components and compatibility layer
require_once __DIR__ . '/includes/load_core.php';

// Namespace declarations
use Summit\Core\Router;
use Summit\Core\Auth;
use Summit\Core\SessionManager;
use Summit\Core\RateLimiter;

try {
    // Initialize error handling
    require_once __DIR__ . '/includes/error_handler.php';
    
    // Initialize session management
    $sessionManager = SessionManager::getInstance();
    
    // Initialize rate limiter
    $rateLimiter = new RateLimiter($conn);
    
    // Load helper functions
    require_once __DIR__ . '/includes/helpers.php';

    // Initialize router
    $router = Router::getInstance();

    // Middleware to check authentication
    $authMiddleware = function() {
        if (!Auth::isLoggedIn()) {
            header('Location: ' . BASE_PATH . '/login.php');
            return false;
        }
    };

    // Middleware to check admin access
    $adminMiddleware = function() {
        if (!Auth::isAdmin()) {
            header('Location: ' . BASE_PATH . '/dashboard.php');
            return false;
        }
    };

    // Public routes
    $router->get('/', 'home/index');
    $router->get('/login', 'auth/login');
    $router->post('/login', 'auth/authenticate');
    $router->get('/register', 'auth/register');
    $router->post('/register', 'auth/store');
    $router->get('/forgot-password', 'auth/forgot_password');
    $router->post('/reset-password', 'auth/reset_password');

    // User routes
    $router->get('/dashboard', 'user/dashboard')->middleware($authMiddleware);
    $router->get('/profile', 'user/profile')->middleware($authMiddleware);
    $router->post('/profile/update', 'user/update_profile')->middleware($authMiddleware);
    $router->get('/payments', 'user/payments')->middleware($authMiddleware);
    $router->post('/payments/process', 'user/process_payment')->middleware($authMiddleware);

    // Admin routes
    $router->get('/admin', 'admin/dashboard')->middleware($adminMiddleware);
    $router->get('/admin/registrations', 'admin/registrations')->middleware($adminMiddleware);
    $router->get('/admin/verify-registrations', 'admin/verify_registrations')->middleware($adminMiddleware);
    $router->post('/admin/verify-registration/{id}', 'admin/verify_registration')->middleware($adminMiddleware);
    $router->get('/admin/registration-settings', 'admin/registration_settings')->middleware($adminMiddleware);

    // Meal validation routes
    $router->get('/admin/validation-dashboard', 'admin/validation_dashboard')->middleware($adminMiddleware);
    $router->get('/admin/meal-stats', 'admin/meal_stats')->middleware($adminMiddleware);
    $router->get('/admin/validation-team', 'admin/validation_team')->middleware($adminMiddleware);
    $router->post('/admin/validate-meal', 'admin/validate_meal')->middleware($adminMiddleware);

    // Payment management routes
    $router->get('/admin/payments', 'admin/payments')->middleware($adminMiddleware);
    $router->get('/admin/payment-settings', 'admin/payment_settings')->middleware($adminMiddleware);
    $router->get('/admin/invoices', 'admin/invoices')->middleware($adminMiddleware);
    $router->post('/admin/generate-invoice/{id}', 'admin/generate_invoice')->middleware($adminMiddleware);

    // Report routes
    $router->get('/admin/reports', 'admin/reports')->middleware($adminMiddleware);
    $router->get('/admin/custom-reports', 'admin/custom_reports')->middleware($adminMiddleware);
    $router->get('/admin/scheduled-reports', 'admin/scheduled_reports')->middleware($adminMiddleware);
    $router->post('/admin/generate-report', 'admin/generate_report')->middleware($adminMiddleware);

    // Settings routes
    $router->get('/admin/users', 'admin/users')->middleware($adminMiddleware);
    $router->get('/admin/roles', 'admin/roles')->middleware($adminMiddleware);
    $router->get('/admin/settings', 'admin/settings')->middleware($adminMiddleware);
    $router->post('/admin/update-settings', 'admin/update_settings')->middleware($adminMiddleware);

    // API routes
    $router->post('/api/validate-participant', 'api/validate_participant')->middleware($adminMiddleware);
    $router->get('/api/meal-stats', 'api/meal_stats')->middleware($adminMiddleware);
    $router->get('/api/payment-stats', 'api/payment_stats')->middleware($adminMiddleware);

    // Error handling
    $router->notFound(function() {
        header("HTTP/1.0 404 Not Found");
        require 'views/errors/404.php';
    });

    // Run the router
    $router->run();
    
} catch (Exception $e) {
    // Log the error
    error_log("Critical Error: " . $e->getMessage());
    
    // Display error page
    http_response_code(500);
    require ERROR_PAGES_PATH . '/500.php';
}
