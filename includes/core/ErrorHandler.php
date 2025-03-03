<?php
namespace Summit\Core;

class ErrorHandler {
    private static $logPath;
    private static $adminEmail;

    public static function initialize($logPath, $adminEmail) {
        self::$logPath = $logPath;
        self::$adminEmail = $adminEmail;

        // Set error and exception handlers
        set_error_handler([self::class, 'handleError']);
        set_exception_handler([self::class, 'handleException']);
        register_shutdown_function([self::class, 'handleFatalError']);
    }

    public static function logError($error, $type = 'ERROR', $file = '', $line = '') {
        $timestamp = date('Y-m-d H:i:s');
        $logMessage = sprintf(
            "[%s] [%s] %s in %s on line %s\n",
            $timestamp,
            $type,
            $error,
            $file,
            $line
        );

        error_log($logMessage, 3, self::$logPath . '/error.log');
        
        if (defined('DEBUG_MODE') && DEBUG_MODE) {
            throw new \Exception($error);
        }
    }

    public static function handleException($e) {
        self::logError(
            $e->getMessage(),
            'EXCEPTION',
            $e->getFile(),
            $e->getLine()
        );

        // Send notification for critical errors
        if ($e->getCode() >= 500) {
            self::notifyAdmin($e);
        }

        // Show appropriate error page
        if (!defined('DEBUG_MODE') || !DEBUG_MODE) {
            include __DIR__ . '/../../views/errors/500.php';
        } else {
            echo "<h1>Error</h1>";
            echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
            echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
        }
    }

    public static function handleError($errno, $errstr, $errfile, $errline) {
        if (!(error_reporting() & $errno)) {
            return false;
        }

        self::logError($errstr, 'ERROR', $errfile, $errline);
        return true;
    }

    public static function handleFatalError() {
        $error = error_get_last();
        if ($error !== NULL && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
            self::logError(
                $error['message'],
                'FATAL',
                $error['file'],
                $error['line']
            );
        }
    }

    private static function notifyAdmin($e) {
        $subject = 'Critical Error in Education Summit System';
        $message = sprintf(
            "Critical error occurred:\n\nMessage: %s\nFile: %s\nLine: %d\nStack Trace:\n%s",
            $e->getMessage(),
            $e->getFile(),
            $e->getLine(),
            $e->getTraceAsString()
        );

        mail(self::$adminEmail, $subject, $message);
    }
}
