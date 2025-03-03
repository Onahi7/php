<?php
class CSRF {
    private static $tokenLength = 32;
    
    public static function generateToken() {
        if (!isset($_SESSION['csrf_tokens'])) {
            $_SESSION['csrf_tokens'] = [];
        }
        
        $token = bin2hex(random_bytes(self::$tokenLength));
        $_SESSION['csrf_tokens'][$token] = time();
        
        // Clean up old tokens
        self::cleanOldTokens();
        
        return $token;
    }
    
    public static function validateToken($token) {
        if (!isset($_SESSION['csrf_tokens'][$token])) {
            return false;
        }
        
        $tokenTime = $_SESSION['csrf_tokens'][$token];
        unset($_SESSION['csrf_tokens'][$token]);
        
        // Check if token is expired (2 hours)
        return (time() - $tokenTime) <= 7200;
    }
    
    private static function cleanOldTokens() {
        if (!isset($_SESSION['csrf_tokens'])) {
            return;
        }
        
        foreach ($_SESSION['csrf_tokens'] as $token => $time) {
            if ((time() - $time) > 7200) {
                unset($_SESSION['csrf_tokens'][$token]);
            }
        }
    }
}

