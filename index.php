<?php
/**
 * Summit Application Main Entry Point
 */

// Load bootstrap file
require_once __DIR__ . '/bootstrap.php';

// Create router instance
$router = \Summit\Core\Router::getInstance();

// Define routes
try {
    // Home page
    $router->get('/', function() {
        view('home');
    });
    
    // Admin dashboard
    $router->get('/admin', function() {
        if (!is_logged_in() || !has_role('admin')) {
            redirect(BASE_PATH . '/login');
        }
        view('admin/dashboard');
    });
    
    // Admin validation dashboard
    $router->get('/admin/validation', function() {
        if (!is_logged_in() || !has_role('admin') && !has_role('validation')) {
            redirect(BASE_PATH . '/login');
        }
        view('admin/validation-dashboard');
    });
    
    // Login page
    $router->get('/login', function() {
        if (is_logged_in()) {
            $role = $_SESSION['user_role'];
            if ($role === 'admin') {
                redirect(BASE_PATH . '/admin');
            } else {
                redirect(BASE_PATH . '/dashboard');
            }
        }
        view('auth/login');
    });
    
    // Login process
    $router->post('/login-process', function() {
        global $conn;
        
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        
        // Check CSRF token
        if (!isset($_POST['csrf_token']) || !isset($_SESSION['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            $_SESSION['login_error'] = 'Security token mismatch. Please try again.';
            redirect(BASE_PATH . '/login');
        }
        
        // Simple authentication
        try {
            $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();
            
            if ($user && password_verify($password, $user['password'])) {
                // Update last login time
                $updateStmt = $conn->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
                $updateStmt->execute([$user['id']]);
                
                // Set session data
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_role'] = $user['role'];
                
                // Log activity
                $logStmt = $conn->prepare("INSERT INTO activity_logs (user_id, action, description, ip_address, user_agent, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
                $logStmt->execute([
                    $user['id'],
                    'login',
                    'User logged in successfully',
                    $_SERVER['REMOTE_ADDR'] ?? null,
                    $_SERVER['HTTP_USER_AGENT'] ?? null
                ]);
                
                // Redirect based on role
                if ($user['role'] === 'admin') {
                    redirect(BASE_PATH . '/admin');
                } else {
                    redirect(BASE_PATH . '/dashboard');
                }
            } else {
                $_SESSION['login_error'] = 'Invalid email or password';
                redirect(BASE_PATH . '/login');
            }
        } catch (PDOException $e) {
            error_log("Login error: " . $e->getMessage());
            $_SESSION['login_error'] = 'An error occurred during login. Please try again.';
            redirect(BASE_PATH . '/login');
        }
    });
    
    // Logout
    $router->get('/logout', function() {
        // Log the logout activity if user is logged in
        if (isset($_SESSION['user_id']) && isset($conn)) {
            try {
                $logStmt = $conn->prepare("INSERT INTO activity_logs (user_id, action, description, ip_address, user_agent, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
                $logStmt->execute([
                    $_SESSION['user_id'],
                    'logout',
                    'User logged out',
                    $_SERVER['REMOTE_ADDR'] ?? null,
                    $_SERVER['HTTP_USER_AGENT'] ?? null
                ]);
            } catch (Exception $e) {
                error_log("Logout logging error: " . $e->getMessage());
            }
        }
        
        // Destroy session
        session_destroy();
        redirect(BASE_PATH . '/login');
    });
    
    // API endpoints
    $router->post('/api/validate-meal', function() {
        global $conn;
        
        header('Content-Type: application/json');
        
        // Check if user is authorized
        if (!is_logged_in() || (!has_role('admin') && !has_role('validator'))) {
            echo json_encode(['error' => 'Unauthorized']);
            exit;
        }
        
        $identifier = $_POST['identifier'] ?? '';
        $mealType = $_POST['meal_type'] ?? '';
        
        if (empty($identifier) || empty($mealType)) {
            echo json_encode(['error' => 'Missing required fields']);
            exit;
        }
        
        try {
            $mealManager = new MealManager($conn);
            $attendee = $mealManager->validateAttendee($identifier);
            $result = $mealManager->recordMeal($attendee['id'], $_SESSION['user_id'], $mealType);
            
            echo json_encode([
                'success' => true,
                'message' => 'Meal validated successfully',
                'attendee' => [
                    'name' => $attendee['name'],
                    'email' => $attendee['email']
                ]
            ]);
        } catch (Exception $e) {
            echo json_encode(['error' => $e->getMessage()]);
        }
        exit;
    });
    
    // 404 Not Found handler
    $router->notFound(function() {
        header("HTTP/1.0 404 Not Found");
        view('errors/404', ['title' => 'Page Not Found']);
    });
    
    // Run the router
    $router->run();
    
} catch (Exception $e) {
    // Log error
    error_log($e->getMessage());
    
    // Show error page
    header('HTTP/1.1 500 Internal Server Error');
    view('errors/500', [
        'title' => 'Internal Server Error',
        'message' => DEBUG_MODE ? $e->getMessage() : 'An unexpected error occurred. Please try again later.'
    ]);
}
