<?php
/**
 * Environment Variable Loader
 * 
 * This file loads environment variables from a .env file if it exists
 * and provides fallback to the config.php constants if not.
 */

class EnvLoader {
    private static $variables = [];
    
    /**
     * Initialize the environment loader
     * 
     * @param string $envFile Path to the .env file
     * @return void
     */
    public static function init($envFile = null) {
        if ($envFile === null) {
            $envFile = __DIR__ . '/../.env';
        }
        
        // Load from .env file if it exists
        if (file_exists($envFile)) {
            $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($lines as $line) {
                // Skip comments
                if (strpos(trim($line), '#') === 0) {
                    continue;
                }
                
                // Parse line
                list($name, $value) = explode('=', $line, 2);
                $name = trim($name);
                $value = trim($value);
                
                // Remove quotes if present
                if (strpos($value, '"') === 0 && strrpos($value, '"') === strlen($value) - 1) {
                    $value = substr($value, 1, -1);
                } elseif (strpos($value, "'") === 0 && strrpos($value, "'") === strlen($value) - 1) {
                    $value = substr($value, 1, -1);
                }
                
                // Store in our array
                self::$variables[$name] = $value;
                
                // Also set as environment variable
                putenv("{$name}={$value}");
                $_ENV[$name] = $value;
            }
        }
    }
    
    /**
     * Get an environment variable with fallback
     * 
     * @param string $name Variable name
     * @param mixed $default Default value if not found
     * @return mixed
     */
    public static function get($name, $default = null) {
        // Check our loaded variables
        if (isset(self::$variables[$name])) {
            return self::$variables[$name];
        }
        
        // Check environment variables
        $value = getenv($name);
        if ($value !== false) {
            return $value;
        }
        
        // Check if a constant with this name exists
        if (defined($name)) {
            return constant($name);
        }
        
        // Return default
        return $default;
    }
    
    /**
     * Create a sample .env file with current configuration
     * 
     * @param string $outputFile Path to output file
     * @return bool
     */
    public static function createSampleEnvFile($outputFile) {
        $content = "# Environment Configuration\n";
        $content .= "# Copy this file to .env and update with your settings\n\n";
        
        // Database settings
        $content .= "# Database Configuration\n";
        $content .= "DB_HOST=" . (defined('DB_HOST') ? DB_HOST : "localhost") . "\n";
        $content .= "DB_USER=" . (defined('DB_USER') ? DB_USER : "database_user") . "\n";
        $content .= "DB_PASS=" . (defined('DB_PASS') ? DB_PASS : "database_password") . "\n";
        $content .= "DB_NAME=" . (defined('DB_NAME') ? DB_NAME : "database_name") . "\n\n";
        
        // Payment settings
        $content .= "# Payment Configuration\n";
        $content .= "PAYSTACK_PUBLIC_KEY=" . (defined('PAYSTACK_PUBLIC_KEY') ? PAYSTACK_PUBLIC_KEY : "your_public_key") . "\n";
        $content .= "PAYSTACK_SECRET_KEY=" . (defined('PAYSTACK_SECRET_KEY') ? PAYSTACK_SECRET_KEY : "your_secret_key") . "\n\n";
        
        // Email settings
        $content .= "# Email Configuration\n";
        $content .= "SMTP_HOST=" . (defined('SMTP_HOST') ? SMTP_HOST : "smtp.example.com") . "\n";
        $content .= "SMTP_PORT=" . (defined('SMTP_PORT') ? SMTP_PORT : "587") . "\n";
        $content .= "SMTP_USER=" . (defined('SMTP_USER') ? SMTP_USER : "user@example.com") . "\n";
        $content .= "SMTP_PASS=" . (defined('SMTP_PASS') ? SMTP_PASS : "smtp_password") . "\n";
        $content .= "SMTP_FROM=" . (defined('SMTP_FROM') ? SMTP_FROM : "noreply@example.com") . "\n\n";
        
        // Application settings
        $content .= "# Application Settings\n";
        $content .= "ENVIRONMENT=" . (defined('ENVIRONMENT') ? ENVIRONMENT : "production") . "\n";
        $content .= "BASE_PATH=" . (defined('BASE_PATH') ? BASE_PATH : "/summit") . "\n";
        
        return file_put_contents($outputFile, $content) !== false;
    }
}

// Initialize the environment loader
EnvLoader::init();
