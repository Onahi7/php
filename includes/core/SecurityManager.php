<?php
namespace Summit\Core;

class SecurityManager {
    private static $instance = null;
    private $db;
    private $rateLimiter;

    private function __construct() {
        $this->db = Database::getInstance();
        $this->rateLimiter = new RateLimiter();
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    // CSRF Protection
    public function generateToken() {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    public function validateToken($token) {
        if (empty($_SESSION['csrf_token']) || $token !== $_SESSION['csrf_token']) {
            throw new \Exception('Invalid CSRF token');
        }
        return true;
    }

    // XSS Prevention
    public function sanitizeOutput($data) {
        if (is_array($data)) {
            return array_map([$this, 'sanitizeOutput'], $data);
        }
        return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    }

    // SQL Injection Prevention
    public function prepareQuery($sql, $params = []) {
        try {
            $stmt = $this->db->prepare($sql);
            foreach ($params as $key => $value) {
                $stmt->bindValue(
                    $key,
                    $value,
                    is_int($value) ? \PDO::PARAM_INT : \PDO::PARAM_STR
                );
            }
            return $stmt;
        } catch (\PDOException $e) {
            ErrorHandler::logError("Database query preparation failed: " . $e->getMessage());
            throw $e;
        }
    }

    // Password Hashing
    public function hashPassword($password) {
        return password_hash($password, PASSWORD_ARGON2ID, [
            'memory_cost' => 65536,
            'time_cost' => 4,
            'threads' => 3
        ]);
    }

    public function verifyPassword($password, $hash) {
        return password_verify($password, $hash);
    }

    // Input Validation
    public function validateInput($data, $rules) {
        $errors = [];
        foreach ($rules as $field => $rule) {
            if (!isset($data[$field])) {
                if (strpos($rule, 'required') !== false) {
                    $errors[$field] = "The {$field} field is required.";
                }
                continue;
            }

            $value = $data[$field];
            $rulesParts = explode('|', $rule);

            foreach ($rulesParts as $rulePart) {
                if ($rulePart === 'required' && empty($value)) {
                    $errors[$field] = "The {$field} field is required.";
                } elseif ($rulePart === 'email' && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    $errors[$field] = "The {$field} must be a valid email address.";
                } elseif (strpos($rulePart, 'min:') === 0) {
                    $min = substr($rulePart, 4);
                    if (strlen($value) < $min) {
                        $errors[$field] = "The {$field} must be at least {$min} characters.";
                    }
                } elseif (strpos($rulePart, 'max:') === 0) {
                    $max = substr($rulePart, 4);
                    if (strlen($value) > $max) {
                        $errors[$field] = "The {$field} must not exceed {$max} characters.";
                    }
                }
            }
        }

        if (!empty($errors)) {
            throw new \Exception(json_encode($errors));
        }

        return true;
    }

    // Rate Limiting
    public function checkRateLimit($ip, $endpoint) {
        return $this->rateLimiter->checkLimit($ip, $endpoint);
    }

    // Session Security
    public function secureSession() {
        if (session_status() === PHP_SESSION_NONE) {
            ini_set('session.cookie_httponly', 1);
            ini_set('session.cookie_secure', 1);
            ini_set('session.use_only_cookies', 1);
            session_start();
        }

        if (!isset($_SESSION['last_regeneration'])) {
            $this->regenerateSession();
        } elseif (time() - $_SESSION['last_regeneration'] > 1800) {
            $this->regenerateSession();
        }
    }

    private function regenerateSession() {
        session_regenerate_id(true);
        $_SESSION['last_regeneration'] = time();
    }

    // File Upload Security
    public function validateFile($file, $allowedTypes, $maxSize) {
        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new \Exception('File upload failed');
        }

        if ($file['size'] > $maxSize) {
            throw new \Exception('File size exceeds limit');
        }

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if (!in_array($mimeType, $allowedTypes)) {
            throw new \Exception('Invalid file type');
        }

        return true;
    }

    // Secure Headers
    public function setSecureHeaders() {
        header('X-Frame-Options: DENY');
        header('X-XSS-Protection: 1; mode=block');
        header('X-Content-Type-Options: nosniff');
        header('Referrer-Policy: strict-origin-when-cross-origin');
        header("Content-Security-Policy: default-src 'self'");
        header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
    }
}
