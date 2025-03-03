<?php
namespace Summit\Core;

class CacheManager {
    private $cache;
    private $ttl;
    private static $instance = null;

    private function __construct($config) {
        $this->ttl = $config['ttl'] ?? 3600;
        $this->initializeCache($config);
    }

    public static function getInstance($config = []) {
        if (self::$instance === null) {
            self::$instance = new self($config);
        }
        return self::$instance;
    }

    private function initializeCache($config) {
        try {
            $this->cache = new \Redis();
            $this->cache->connect(
                $config['host'] ?? '127.0.0.1',
                $config['port'] ?? 6379
            );
            if (isset($config['password'])) {
                $this->cache->auth($config['password']);
            }
        } catch (\Exception $e) {
            ErrorHandler::logError("Redis connection failed: " . $e->getMessage());
            // Fallback to file-based caching
            $this->initializeFileCache();
        }
    }

    private function initializeFileCache() {
        if (!is_dir(__DIR__ . '/../../storage/cache')) {
            mkdir(__DIR__ . '/../../storage/cache', 0755, true);
        }
    }

    public function get($key) {
        try {
            if ($this->cache instanceof \Redis) {
                return $this->cache->get("summit:{$key}");
            }
            return $this->getFromFile($key);
        } catch (\Exception $e) {
            ErrorHandler::logError("Cache get failed: " . $e->getMessage());
            return null;
        }
    }

    public function set($key, $value, $ttl = null) {
        try {
            $ttl = $ttl ?? $this->ttl;
            if ($this->cache instanceof \Redis) {
                return $this->cache->setex("summit:{$key}", $ttl, $value);
            }
            return $this->setToFile($key, $value, $ttl);
        } catch (\Exception $e) {
            ErrorHandler::logError("Cache set failed: " . $e->getMessage());
            return false;
        }
    }

    public function delete($key) {
        try {
            if ($this->cache instanceof \Redis) {
                return $this->cache->del("summit:{$key}");
            }
            return $this->deleteFromFile($key);
        } catch (\Exception $e) {
            ErrorHandler::logError("Cache delete failed: " . $e->getMessage());
            return false;
        }
    }

    public function clear() {
        try {
            if ($this->cache instanceof \Redis) {
                return $this->cache->flushAll();
            }
            return $this->clearFileCache();
        } catch (\Exception $e) {
            ErrorHandler::logError("Cache clear failed: " . $e->getMessage());
            return false;
        }
    }

    private function getFromFile($key) {
        $filename = $this->getCacheFilename($key);
        if (!file_exists($filename)) {
            return null;
        }

        $content = file_get_contents($filename);
        $data = json_decode($content, true);

        if (!$data || $data['expires'] < time()) {
            unlink($filename);
            return null;
        }

        return $data['value'];
    }

    private function setToFile($key, $value, $ttl) {
        $filename = $this->getCacheFilename($key);
        $data = [
            'value' => $value,
            'expires' => time() + $ttl
        ];

        return file_put_contents($filename, json_encode($data)) !== false;
    }

    private function deleteFromFile($key) {
        $filename = $this->getCacheFilename($key);
        if (file_exists($filename)) {
            return unlink($filename);
        }
        return true;
    }

    private function clearFileCache() {
        $files = glob(__DIR__ . '/../../storage/cache/*');
        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
        return true;
    }

    private function getCacheFilename($key) {
        return __DIR__ . '/../../storage/cache/' . md5($key) . '.cache';
    }

    // Specific caching methods for the summit application
    public function cacheStats($stats) {
        return $this->set('dashboard_stats', json_encode($stats));
    }

    public function getStats() {
        $stats = $this->get('dashboard_stats');
        return $stats ? json_decode($stats, true) : null;
    }

    public function cacheUserData($userId, $data) {
        return $this->set("user:{$userId}", json_encode($data), 1800); // 30 minutes
    }

    public function getUserData($userId) {
        $data = $this->get("user:{$userId}");
        return $data ? json_decode($data, true) : null;
    }
}
