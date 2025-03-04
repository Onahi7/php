<?php
namespace Summit\Core;

use Summit\Core\Config;

class Logger {
    private static $instance = null;
    private $logPath;
    private $logLevels = [
        'DEBUG' => 100,
        'INFO' => 200,
        'NOTICE' => 250,
        'WARNING' => 300,
        'ERROR' => 400,
        'CRITICAL' => 500,
        'ALERT' => 550,
        'EMERGENCY' => 600
    ];
    private $rotationSize = 10485760; // 10MB
    private $maxFiles = 5;

    private function __construct() {
        $this->logPath = Config::get('app.log_path');
        if (!file_exists($this->logPath)) {
            mkdir($this->logPath, 0777, true);
        }
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Log a message with context
     */
    public function log($level, $message, array $context = []) {
        if (!isset($this->logLevels[strtoupper($level)])) {
            throw new \InvalidArgumentException("Invalid log level: $level");
        }

        $logFile = $this->logPath . '/' . strtolower($level) . '.log';
        
        // Check if rotation is needed
        $this->checkRotation($logFile);

        // Format message
        $timestamp = date('Y-m-d H:i:s');
        $requestId = $_SERVER['REQUEST_ID'] ?? '-';
        $userId = $_SESSION['user_id'] ?? '-';
        
        $formattedMessage = sprintf(
            "[%s] [%s] [%s] [User:%s] %s %s\n",
            $timestamp,
            $level,
            $requestId,
            $userId,
            $this->interpolate($message, $context),
            $context ? json_encode($context) : ''
        );

        // Write to log file
        if (!file_put_contents($logFile, $formattedMessage, FILE_APPEND | LOCK_EX)) {
            throw new \RuntimeException("Could not write to log file: $logFile");
        }

        // For critical errors, also notify admin
        if ($this->logLevels[strtoupper($level)] >= 500) {
            $this->notifyAdmin($level, $message, $context);
        }
    }

    /**
     * Convenience methods for different log levels
     */
    public function emergency($message, array $context = []) {
        $this->log('EMERGENCY', $message, $context);
    }

    public function alert($message, array $context = []) {
        $this->log('ALERT', $message, $context);
    }

    public function critical($message, array $context = []) {
        $this->log('CRITICAL', $message, $context);
    }

    public function error($message, array $context = []) {
        $this->log('ERROR', $message, $context);
    }

    public function warning($message, array $context = []) {
        $this->log('WARNING', $message, $context);
    }

    public function notice($message, array $context = []) {
        $this->log('NOTICE', $message, $context);
    }

    public function info($message, array $context = []) {
        $this->log('INFO', $message, $context);
    }

    public function debug($message, array $context = []) {
        $this->log('DEBUG', $message, $context);
    }

    /**
     * Replace placeholders in the message with context values
     */
    private function interpolate($message, array $context = []) {
        $replace = [];
        foreach ($context as $key => $val) {
            if (is_string($val) || method_exists($val, '__toString')) {
                $replace['{' . $key . '}'] = $val;
            }
        }
        return strtr($message, $replace);
    }

    /**
     * Check if log file needs rotation
     */
    private function checkRotation($logFile) {
        if (!file_exists($logFile)) {
            return;
        }

        if (filesize($logFile) < $this->rotationSize) {
            return;
        }

        // Rotate existing files
        for ($i = $this->maxFiles - 1; $i >= 0; $i--) {
            $oldFile = $logFile . ($i > 0 ? '.' . $i : '');
            $newFile = $logFile . '.' . ($i + 1);
            
            if (file_exists($oldFile)) {
                if ($i == $this->maxFiles - 1) {
                    unlink($oldFile);
                } else {
                    rename($oldFile, $newFile);
                }
            }
        }

        // Create new empty log file
        file_put_contents($logFile, '');
    }

    /**
     * Get all logs for a specific level within a date range
     */
    public function getLogs($level, $startDate = null, $endDate = null, $limit = 100) {
        $logFile = $this->logPath . '/' . strtolower($level) . '.log';
        if (!file_exists($logFile)) {
            return [];
        }

        $logs = [];
        $handle = fopen($logFile, 'r');
        if ($handle) {
            while (($line = fgets($handle)) !== false) {
                if (preg_match('/^\[(.*?)\]/', $line, $matches)) {
                    $timestamp = strtotime($matches[1]);
                    if ((!$startDate || $timestamp >= strtotime($startDate)) &&
                        (!$endDate || $timestamp <= strtotime($endDate))) {
                        $logs[] = $line;
                        if (count($logs) >= $limit) {
                            break;
                        }
                    }
                }
            }
            fclose($handle);
        }

        return $logs;
    }

    /**
     * Clean old log files
     */
    public function cleanOldLogs($days = 30) {
        $threshold = time() - ($days * 86400);
        
        foreach (glob($this->logPath . '/*.log*') as $file) {
            if (filemtime($file) < $threshold) {
                unlink($file);
            }
        }
    }

    /**
     * Notify admin about critical errors
     */
    private function notifyAdmin($level, $message, array $context = []) {
        $adminEmail = Config::get('app.admin_email');
        if (!$adminEmail) {
            return;
        }

        $subject = "[$level] Critical Error in Education Summit System";
        $emailBody = sprintf(
            "A critical error occurred:\n\nLevel: %s\nMessage: %s\nContext: %s\nTimestamp: %s",
            $level,
            $message,
            json_encode($context, JSON_PRETTY_PRINT),
            date('Y-m-d H:i:s')
        );

        mail($adminEmail, $subject, $emailBody);
    }
}
