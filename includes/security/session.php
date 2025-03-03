<?php
namespace Summit\Core;

class SessionManager {
    private $lifetime;
    private $path;
    private $domain;
    private $secure;
    private $httponly;
    
    public function __construct() {
        $this->lifetime = 3600; // 1 hour
        $this->path = '/';
        $this->domain = '';
        $this->secure = isset($_SERVER['HTTPS']);
        $this->httponly = true;
        
        $this->initialize();
    }
    
    private function initialize() {
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
        // Regenerate session ID and update timestamp
        session_regenerate_id(true);
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
    
    public function get($key) {
        return $_SESSION[$key] ?? null;
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
}
