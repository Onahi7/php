<?php
namespace Summit\Core;

class ErrorHandler {
    private static $logger;
    private static $adminEmail;

    public static function initialize($logPath, $adminEmail) {
        self::$adminEmail = $adminEmail;
        self::$logger = Logger::getInstance();

        // Set error and exception handlers
        set_error_handler([self::class, 'handleError']);
        set_exception_handler([self::class, 'handleException']);
        register_shutdown_function([self::class, 'handleFatalError']);

        // Set up error reporting
        error_reporting(E_ALL);
        ini_set('display_errors', Config::get('app.debug') ? '1' : '0');
        ini_set('log_errors', '1');
        ini_set('error_log', $logPath . '/php_errors.log');
    }

    public static function logError($error, $type = 'ERROR', $file = '', $line = '', array $context = []) {
        $context['file'] = $file ?: 'unknown';
        $context['line'] = $line ?: 'unknown';
        $context['type'] = $type;
        $context['url'] = $_SERVER['REQUEST_URI'] ?? 'unknown';
        $context['method'] = $_SERVER['REQUEST_METHOD'] ?? 'unknown';
        $context['ip'] = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        
        switch (strtoupper($type)) {
            case 'EMERGENCY':
                self::$logger->emergency($error, $context);
                break;
            case 'ALERT':
                self::$logger->alert($error, $context);
                break;
            case 'CRITICAL':
                self::$logger->critical($error, $context);
                break;
            case 'ERROR':
                self::$logger->error($error, $context);
                break;
            case 'WARNING':
                self::$logger->warning($error, $context);
                break;
            case 'NOTICE':
                self::$logger->notice($error, $context);
                break;
            case 'INFO':
                self::$logger->info($error, $context);
                break;
            default:
                self::$logger->debug($error, $context);
        }
    }

    public static function handleException($e) {
        $context = [
            'code' => $e->getCode(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString()
        ];

        self::logError(
            $e->getMessage(),
            'CRITICAL',
            $e->getFile(),
            $e->getLine(),
            $context
        );

        if (!headers_sent()) {
            http_response_code(500);
        }

        if (Config::get('app.debug')) {
            echo self::renderExceptionPage($e);
        } else {
            include Config::get('app.views_path') . '/errors/500.php';
        }
    }

    public static function handleError($errno, $errstr, $errfile, $errline) {
        if (!(error_reporting() & $errno)) {
            return false;
        }

        $context = [
            'errno' => $errno,
            'file' => $errfile,
            'line' => $errline
        ];

        switch ($errno) {
            case E_USER_ERROR:
                self::logError($errstr, 'ERROR', $errfile, $errline, $context);
                break;
            case E_USER_WARNING:
                self::logError($errstr, 'WARNING', $errfile, $errline, $context);
                break;
            case E_USER_NOTICE:
                self::logError($errstr, 'NOTICE', $errfile, $errline, $context);
                break;
            default:
                self::logError($errstr, 'WARNING', $errfile, $errline, $context);
        }

        return true;
    }

    public static function handleFatalError() {
        $error = error_get_last();
        if ($error !== NULL && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
            $context = [
                'type' => $error['type'],
                'file' => $error['file'],
                'line' => $error['line']
            ];

            self::logError(
                $error['message'],
                'EMERGENCY',
                $error['file'],
                $error['line'],
                $context
            );

            if (!headers_sent()) {
                http_response_code(500);
            }

            if (!Config::get('app.debug')) {
                include Config::get('app.views_path') . '/errors/500.php';
            }
        }
    }

    private static function renderExceptionPage($e) {
        $title = get_class($e);
        $message = $e->getMessage();
        $file = $e->getFile();
        $line = $e->getLine();
        $trace = $e->getTraceAsString();
        
        return <<<HTML
        <!DOCTYPE html>
        <html>
        <head>
            <title>Error: $title</title>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; padding: 20px; }
                .error-container { max-width: 1200px; margin: 0 auto; }
                .error-title { color: #e53e3e; margin-bottom: 20px; }
                .error-message { background: #fff5f5; border-left: 4px solid #e53e3e; padding: 15px; margin-bottom: 20px; }
                .error-details { background: #f7fafc; padding: 15px; border-radius: 4px; }
                .error-trace { background: #2d3748; color: #e2e8f0; padding: 15px; border-radius: 4px; overflow-x: auto; }
                code { font-family: monospace; }
            </style>
        </head>
        <body>
            <div class="error-container">
                <h1 class="error-title">$title</h1>
                <div class="error-message">
                    <strong>Message:</strong> $message
                </div>
                <div class="error-details">
                    <p><strong>File:</strong> $file</p>
                    <p><strong>Line:</strong> $line</p>
                </div>
                <h2>Stack Trace:</h2>
                <pre class="error-trace"><code>$trace</code></pre>
            </div>
        </body>
        </html>
        HTML;
    }
}
