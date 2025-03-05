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

// Add Router compatibility if needed
if (!class_exists('Summit\\Core\\Router')) {
    class RouterCompat {
        private static $instance = null;
        private $routes = [];
        private $notFoundCallback;
        
        private function __construct() {}
        
        public static function getInstance() {
            if (self::$instance === null) {
                self::$instance = new self();
            }
            return self::$instance;
        }
        
        public function get($path, $callback) {
            return $this->addRoute('GET', $path, $callback);
        }
        
        public function post($path, $callback) {
            return $this->addRoute('POST', $path, $callback);
        }
        
        private function addRoute($method, $path, $callback) {
            $route = [
                'method' => $method,
                'path' => $path,
                'callback' => $callback,
                'middleware' => []
            ];
            $this->routes[] = $route;
            return $this;
        }
        
        public function middleware($callback) {
            if (!empty($this->routes)) {
                $lastRoute = &$this->routes[count($this->routes) - 1];
                $lastRoute['middleware'][] = $callback;
            }
            return $this;
        }
        
        public function notFound($callback) {
            $this->notFoundCallback = $callback;
            return $this;
        }
        
        public function run() {
            $requestMethod = $_SERVER['REQUEST_METHOD'];
            $requestPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
            
            // Remove base path from request
            $basePath = dirname($_SERVER['SCRIPT_NAME']);
            if ($basePath !== '/') {
                $requestPath = substr($requestPath, strlen($basePath));
            }
            
            // Remove trailing slash if present
            $requestPath = rtrim($requestPath, '/');
            if (empty($requestPath)) {
                $requestPath = '/';
            }
            
            foreach ($this->routes as $route) {
                if ($route['method'] !== $requestMethod) {
                    continue;
                }
                
                $pattern = $this->convertRouteToRegex($route['path']);
                if (preg_match($pattern, $requestPath, $matches)) {
                    // Remove the full match
                    array_shift($matches);
                    
                    // Run middleware
                    foreach ($route['middleware'] as $middleware) {
                        if ($middleware() === false) {
                            return;
                        }
                    }
                    
                    // Handle the callback
                    if (is_callable($route['callback'])) {
                        call_user_func_array($route['callback'], $matches);
                    } else if (is_string($route['callback']) && strpos($route['callback'], '/') !== false) {
                        list($controller, $method) = explode('/', $route['callback']);
                        $controllerClass = 'Summit\\Controllers\\' . ucfirst($controller) . 'Controller';
                        
                        if (class_exists($controllerClass)) {
                            $controllerInstance = new $controllerClass();
                            if (method_exists($controllerInstance, $method)) {
                                call_user_func_array([$controllerInstance, $method], $matches);
                            } else {
                                $this->handleNotFound();
                            }
                        } else {
                            $this->handleNotFound();
                        }
                    } else {
                        $this->handleNotFound();
                    }
                    return;
                }
            }
            
            $this->handleNotFound();
        }
        
        private function handleNotFound() {
            if ($this->notFoundCallback && is_callable($this->notFoundCallback)) {
                call_user_func($this->notFoundCallback);
            } else {
                header("HTTP/1.0 404 Not Found");
                echo "404 Not Found";
            }
        }
        
        private function convertRouteToRegex($route) {
            return "#^" . preg_replace_callback("#\{([a-zA-Z0-9_]+)\}#", function($matches) {
                return "([^/]+)";
            }, $route) . "$#";
        }
    }
    
    // Add to the Summit namespace
    class_alias('RouterCompat', 'Summit\\Core\\Router');
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
