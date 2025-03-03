<?php
namespace Summit\Core;

class Router {
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
                if (is_string($route['callback'])) {
                    list($controller, $method) = explode('/', $route['callback']);
                    $controllerClass = 'Summit\\Controllers\\' . ucfirst($controller) . 'Controller';
                    
                    if (!class_exists($controllerClass)) {
                        throw new \Exception("Controller {$controllerClass} not found");
                    }
                    
                    $controllerInstance = new $controllerClass();
                    if (!method_exists($controllerInstance, $method)) {
                        throw new \Exception("Method {$method} not found in controller {$controllerClass}");
                    }
                    
                    call_user_func_array([$controllerInstance, $method], $matches);
                } else {
                    call_user_func_array($route['callback'], $matches);
                }
                return;
            }
        }
        
        if ($this->notFoundCallback) {
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
