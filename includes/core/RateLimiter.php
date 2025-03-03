<?php
namespace Summit\Core;

class RateLimiter {
    private $cache;
    private $defaultLimit = 60;
    private $defaultWindow = 60;

    public function __construct() {
        $this->cache = CacheManager::getInstance();
    }

    public function checkLimit($ip, $endpoint, $limit = null, $window = null) {
        $limit = $limit ?? $this->defaultLimit;
        $window = $window ?? $this->defaultWindow;
        
        $key = "rate_limit:{$ip}:{$endpoint}";
        
        try {
            $current = $this->incrementCounter($key);
            
            if ($current === 1) {
                $this->cache->set($key, $current, $window);
            }
            
            if ($current > $limit) {
                ErrorHandler::logError("Rate limit exceeded for IP: {$ip} on endpoint: {$endpoint}");
                return false;
            }
            
            return true;
        } catch (\Exception $e) {
            ErrorHandler::logError("Rate limiting failed: " . $e->getMessage());
            return true; // Fail open to prevent blocking all requests if cache fails
        }
    }

    private function incrementCounter($key) {
        $current = $this->cache->get($key);
        if ($current === null) {
            return 1;
        }
        return intval($current) + 1;
    }

    public function resetLimit($ip, $endpoint) {
        $key = "rate_limit:{$ip}:{$endpoint}";
        return $this->cache->delete($key);
    }

    public function getRemainingAttempts($ip, $endpoint, $limit = null) {
        $limit = $limit ?? $this->defaultLimit;
        $key = "rate_limit:{$ip}:{$endpoint}";
        
        $current = $this->cache->get($key);
        if ($current === null) {
            return $limit;
        }
        
        return max(0, $limit - intval($current));
    }

    public function setDefaultLimit($limit) {
        $this->defaultLimit = $limit;
    }

    public function setDefaultWindow($window) {
        $this->defaultWindow = $window;
    }
}
