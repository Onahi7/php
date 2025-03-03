<?php
namespace Summit\Core;

class SystemMonitor {
    private $cache;
    private static $instance = null;
    private $alertThresholds = [
        'disk_space' => 90, // Alert if disk usage > 90%
        'memory_usage' => 85, // Alert if memory usage > 85%
        'queue_size' => 1000, // Alert if any queue has > 1000 items
        'error_rate' => 50, // Alert if > 50 errors in 5 minutes
        'response_time' => 2000 // Alert if average response time > 2000ms
    ];

    private function __construct() {
        $this->cache = CacheManager::getInstance();
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function checkSystemHealth() {
        $health = [
            'status' => 'operational',
            'timestamp' => time(),
            'checks' => [
                'database' => $this->checkDatabaseConnection(),
                'disk_space' => $this->checkDiskSpace(),
                'memory_usage' => $this->checkMemoryUsage(),
                'queue_status' => $this->checkQueueStatus(),
                'cache' => $this->checkCacheConnection(),
                'error_rate' => $this->checkErrorRate(),
                'response_time' => $this->checkAverageResponseTime()
            ]
        ];

        // Determine overall system status
        foreach ($health['checks'] as $check) {
            if ($check['status'] === 'critical') {
                $health['status'] = 'critical';
                break;
            } elseif ($check['status'] === 'warning' && $health['status'] !== 'critical') {
                $health['status'] = 'warning';
            }
        }

        // Cache health status
        $this->cache->set('system_health', json_encode($health), 300); // Cache for 5 minutes
        return $health;
    }

    private function checkDatabaseConnection() {
        try {
            $db = Database::getInstance();
            $start = microtime(true);
            $db->query('SELECT 1');
            $responseTime = (microtime(true) - $start) * 1000;

            return [
                'status' => $responseTime < 100 ? 'operational' : 'warning',
                'response_time' => round($responseTime, 2),
                'message' => 'Database connection successful'
            ];
        } catch (\Exception $e) {
            ErrorHandler::logError("Database health check failed: " . $e->getMessage());
            return [
                'status' => 'critical',
                'message' => 'Database connection failed'
            ];
        }
    }

    private function checkDiskSpace() {
        $path = __DIR__;
        $total = disk_total_space($path);
        $free = disk_free_space($path);
        $used = $total - $free;
        $usedPercentage = ($used / $total) * 100;

        return [
            'status' => $usedPercentage > $this->alertThresholds['disk_space'] ? 'critical' : 'operational',
            'used_percentage' => round($usedPercentage, 2),
            'free_space' => $this->formatBytes($free),
            'total_space' => $this->formatBytes($total)
        ];
    }

    private function checkMemoryUsage() {
        $memoryLimit = ini_get('memory_limit');
        $memoryUsage = memory_get_usage(true);
        $memoryPeak = memory_get_peak_usage(true);
        $usedPercentage = ($memoryUsage / $this->convertToBytes($memoryLimit)) * 100;

        return [
            'status' => $usedPercentage > $this->alertThresholds['memory_usage'] ? 'warning' : 'operational',
            'current_usage' => $this->formatBytes($memoryUsage),
            'peak_usage' => $this->formatBytes($memoryPeak),
            'limit' => $memoryLimit
        ];
    }

    private function checkQueueStatus() {
        try {
            $queueManager = QueueManager::getInstance();
            $queues = ['email', 'export', 'notification'];
            $status = [];

            foreach ($queues as $queue) {
                $size = $this->cache->cache->lLen("summit:queue:{$queue}");
                $status[$queue] = [
                    'size' => $size,
                    'status' => $size > $this->alertThresholds['queue_size'] ? 'warning' : 'operational'
                ];
            }

            return [
                'status' => 'operational',
                'queues' => $status
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'warning',
                'message' => 'Queue check failed'
            ];
        }
    }

    private function checkCacheConnection() {
        try {
            $start = microtime(true);
            $this->cache->set('health_check', '1', 10);
            $value = $this->cache->get('health_check');
            $responseTime = (microtime(true) - $start) * 1000;

            return [
                'status' => ($value === '1' && $responseTime < 100) ? 'operational' : 'warning',
                'response_time' => round($responseTime, 2)
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'critical',
                'message' => 'Cache connection failed'
            ];
        }
    }

    private function checkErrorRate() {
        try {
            $lastCheck = time() - 300; // Last 5 minutes
            $errorCount = $this->countRecentErrors($lastCheck);

            return [
                'status' => $errorCount > $this->alertThresholds['error_rate'] ? 'critical' : 'operational',
                'error_count' => $errorCount,
                'time_window' => '5 minutes'
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'warning',
                'message' => 'Error rate check failed'
            ];
        }
    }

    private function checkAverageResponseTime() {
        try {
            $times = $this->cache->get('response_times') ?: [];
            if (empty($times)) {
                return [
                    'status' => 'operational',
                    'message' => 'No response time data available'
                ];
            }

            $average = array_sum($times) / count($times);
            return [
                'status' => $average > $this->alertThresholds['response_time'] ? 'warning' : 'operational',
                'average_time' => round($average, 2),
                'sample_size' => count($times)
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'warning',
                'message' => 'Response time check failed'
            ];
        }
    }

    public function recordResponseTime($time) {
        try {
            $times = $this->cache->get('response_times') ?: [];
            array_push($times, $time);
            // Keep only last 100 responses
            if (count($times) > 100) {
                array_shift($times);
            }
            $this->cache->set('response_times', $times, 3600);
        } catch (\Exception $e) {
            ErrorHandler::logError("Failed to record response time: " . $e->getMessage());
        }
    }

    private function countRecentErrors($since) {
        // Implementation depends on your error logging system
        $logFile = __DIR__ . '/../../storage/logs/error.log';
        if (!file_exists($logFile)) {
            return 0;
        }

        $count = 0;
        $handle = fopen($logFile, 'r');
        while (($line = fgets($handle)) !== false) {
            if (strtotime(substr($line, 1, 19)) >= $since) {
                $count++;
            }
        }
        fclose($handle);
        return $count;
    }

    private function formatBytes($bytes) {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);
        return round($bytes, 2) . ' ' . $units[$pow];
    }

    private function convertToBytes($value) {
        $unit = strtolower(substr($value, -1));
        $value = (int) $value;
        switch ($unit) {
            case 'g':
                $value *= 1024;
            case 'm':
                $value *= 1024;
            case 'k':
                $value *= 1024;
        }
        return $value;
    }

    public function alertIfIssuesFound($healthData) {
        if ($healthData['status'] !== 'operational') {
            $criticalChecks = array_filter($healthData['checks'], function($check) {
                return $check['status'] === 'critical';
            });

            if (!empty($criticalChecks)) {
                $this->sendAlerts($criticalChecks);
            }
        }
    }

    private function sendAlerts($issues) {
        $message = "Critical system issues detected:\n\n";
        foreach ($issues as $component => $data) {
            $message .= "- {$component}: {$data['message']}\n";
        }

        // Send email alert
        mail(
            getenv('ADMIN_EMAIL'),
            'System Alert - Critical Issues Detected',
            $message
        );

        // Log alert
        ErrorHandler::logError("System Alert: " . $message);
    }
}
