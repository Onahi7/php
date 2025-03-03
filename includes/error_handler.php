<?php
// Custom error handler
function custom_error_handler($errno, $errstr, $errfile, $errline) {
    $error_message = date('[Y-m-d H:i:s]') . " Error: [$errno] $errstr in $errfile on line $errline\n";
    error_log($error_message, 3, LOGS_PATH . '/error.log');
    
    if (defined('ENVIRONMENT') && ENVIRONMENT === 'development') {
        printf("<div style='color:red;'>ERROR: %s</div>", htmlspecialchars($errstr));
    } else {
        include ERROR_PAGES_PATH . '/500.php';
    }
    
    return true;
}

// Custom exception handler
function custom_exception_handler($exception) {
    $error_message = date('[Y-m-d H:i:s]') . " Exception: " . $exception->getMessage() . 
                    " in " . $exception->getFile() . " on line " . $exception->getLine() . "\n";
    error_log($error_message, 3, LOGS_PATH . '/error.log');
    
    if (defined('ENVIRONMENT') && ENVIRONMENT === 'development') {
        printf("<div style='color:red;'>EXCEPTION: %s</div>", htmlspecialchars($exception->getMessage()));
    } else {
        include ERROR_PAGES_PATH . '/500.php';
    }
}

// Function to log custom messages
function log_message($level, $message) {
    $log_message = date('[Y-m-d H:i:s]') . " $level: $message\n";
    error_log($log_message, 3, LOGS_PATH . '/app.log');
}

// Register error and exception handlers
set_error_handler('custom_error_handler');
set_exception_handler('custom_exception_handler');
