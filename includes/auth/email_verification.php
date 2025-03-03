<?php
class EmailVerification {
    private $conn;
    private $mailer;
    
    public function __construct($conn) {
        $this->conn = $conn;
        require_once __DIR__ . '/../mailer/Mailer.php';
        $this->mailer = new Mailer();
    }
    
    public function sendVerificationEmail($userId) {
        try {
            // Get user details
            $stmt = $this->conn->prepare("
                SELECT name, email, verification_token 
                FROM users 
                WHERE id = ?
            ");
            $stmt->bind_param("i", $userId);
            $stmt->execute();
            $user = $stmt->get_result()->fetch_assoc();
            
            if (!$user) {
                throw new Exception("User not found");
            }
            
            // Generate new token if none exists
            if (!$user['verification_token']) {
                $token = bin2hex(random_bytes(32));
                $stmt = $this->conn->prepare("
                    UPDATE users 
                    SET verification_token = ? 
                    WHERE id = ?
                ");
                $stmt->bind_param("si", $token, $userId);
                $stmt->execute();
            } else {
                $token = $user['verification_token'];
            }
            
            // Send verification email
            $verificationLink = SITE_URL . '/verify-email?token=' . $token;
            return $this->mailer->sendVerificationEmail($user['email'], $user['name'], $verificationLink);
            
        } catch (Exception $e) {
            error_log("Email verification error: " . $e->getMessage());
            throw new Exception("Failed to send verification email");
        }
    }
    
    public function verifyEmail($token) {
        try {
            $stmt = $this->conn->prepare("
                SELECT id 
                FROM users 
                WHERE verification_token = ?
            ");
            $stmt->bind_param("s", $token);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows === 0) {
                throw new Exception("Invalid verification token");
            }
            
            $userId = $result->fetch_assoc()['id'];
            
            // Update user verification status
            $stmt = $this->conn->prepare("
                UPDATE users 
                SET verified_at = NOW(),
                    verification_token = NULL 
                WHERE id = ?
            ");
            $stmt->bind_param("i", $userId);
            $stmt->execute();
            
            return true;
            
        } catch (Exception $e) {
            error_log("Email verification error: " . $e->getMessage());
            throw new Exception("Failed to verify email");
        }
    }
}

