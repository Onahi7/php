<?php
namespace Summit\Core;

class SessionManager {
    private $lifetime;
    private $path;
    private $domain;
    private $secure;
    private $httponly;
    private static $instance = null;
    
    private function __construct() {
        $this->lifetime = SESSION_LIFETIME;
        $this->path = '/';
        $this->domain = '';
        $this->secure = isset($_SERVER['HTTPS']);
        $this->httponly = true;
        
        $this->initialize();
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function initialize() {
        // Set secure session parameters
        ini_set('session.cookie_httponly', 1);
        ini_set('session.use_only_cookies', 1);
        ini_set('session.cookie_secure', $this->secure);
        
        // Set session cookie parameters
        session_set_cookie_params(
            $this->lifetime,
            $this->path,
            $this->domain,
            $this->secure,
            $this->httponly
        );
        
        // Start session if not already started
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Regenerate session ID periodically
        if (!isset($_SESSION['_last_regeneration'])) {
            $this->regenerateSession();
        } elseif (time() - $_SESSION['_last_regeneration'] > 300) {
            $this->regenerateSession();
        }
        
        // Check for session hijacking
        if (isset($_SESSION['_client_ip']) && $_SESSION['_client_ip'] !== $_SERVER['REMOTE_ADDR']) {
            $this->destroy();
            throw new \Exception('Session hijacking detected');
        }
        
        // Set client fingerprint
        $_SESSION['_client_ip'] = $_SERVER['REMOTE_ADDR'];
        $_SESSION['_user_agent'] = $_SERVER['HTTP_USER_AGENT'];
    }
    
    public function regenerateSession() {
        // Save old session data
        $old_session_data = $_SESSION;
        
        // Generate new session ID
        session_regenerate_id(true);
        
        // Restore old session data
        $_SESSION = $old_session_data;
        
        // Update last regeneration time
        $_SESSION['_last_regeneration'] = time();
    }
    
    public function destroy() {
        // Unset all session variables
        $_SESSION = array();
        
        // Destroy the session cookie
        if (isset($_COOKIE[session_name()])) {
            setcookie(
                session_name(),
                '',
                time() - 3600,
                $this->path,
                $this->domain,
                $this->secure,
                $this->httponly
            );
        }
        
        // Destroy the session
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
