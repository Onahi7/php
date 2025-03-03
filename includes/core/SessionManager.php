<?php
namespace Summit\Core;

class SessionManager {
    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) {
            // Set secure session parameters
            ini_set('session.cookie_httponly', 1);
            ini_set('session.use_only_cookies', 1);
            ini_set('session.cookie_secure', 1);
            
            session_start();
        }
        
        // Regenerate session ID periodically
        if (!isset($_SESSION['last_regeneration'])) {
            $this->regenerateSession();
        } else {
            $regeneration_time = $_SESSION['last_regeneration'];
            if (time() - $regeneration_time > 1800) { // 30 minutes
                $this->regenerateSession();
            }
        }
    }
    
    private function regenerateSession() {
        // Save old session data
        $old_session_data = $_SESSION;
        
        // Generate new session ID
        session_regenerate_id(true);
        
        // Restore old session data
        $_SESSION = $old_session_data;
        
        // Update last regeneration time
        $_SESSION['last_regeneration'] = time();
    }
    
    public function set($key, $value) {
        $_SESSION[$key] = $value;
    }
    
    public function get($key, $default = null) {
        return $_SESSION[$key] ?? $default;
    }
    
    public function remove($key) {
        unset($_SESSION[$key]);
    }
    
    public function clear() {
        session_unset();
    }
    
    public function destroy() {
        session_destroy();
    }
}
