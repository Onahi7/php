<?php
class PasswordReset {
    private $conn;
    private $mailer;
    private $tokenExpiry = 3600; // 1 hour
    
    public function __construct($conn) {
        $this->conn = $conn;
        require_once __DIR__ . '/../mailer/Mailer.php';
        $this->mailer = new Mailer();
    }
    
    public function requestReset($email) {
        try {
            // Get user details
            $stmt = $this->conn->prepare("
                SELECT id, name 
                FROM users 
                WHERE email = ? 
                AND status = 'active'
            ");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $user = $stmt->get_result()->fetch_assoc();
            
            if (!$user) {
                // Return true even if user not found to prevent email enumeration
                return true;
            }
            
            // Generate reset token
            $token = bin2hex(random_bytes(32));
            $expiry = date('Y-m-d H:i:s', time() + $this->tokenExpiry);
            
            // Save reset token
            $stmt = $this->conn->prepare("
                UPDATE users 
                SET reset_token = ?,
                    reset_expiry = ? 
                WHERE id = ?
            ");
            $stmt->bind_param("ssi", $token, $expiry, $user['id']);
            $stmt->execute();
            
            // Send reset email
            $resetLink = SITE_URL . '/reset-password?token=' . $token;
            return $this->mailer->sendPasswordResetEmail($email, $user['name'], $resetLink);
            
        } catch (Exception $e) {
            error_log("Password reset request error: " . $e->getMessage());
            throw new Exception("Failed to process password reset request");
        }
    }
    
    public function validateToken($token) {
        try {
            $stmt = $this->conn->prepare("
                SELECT id 
                FROM users 
                WHERE reset_token = ? 
                AND reset_expiry > NOW() 
                AND status = 'active'
            ");
            $stmt->bind_param("s", $token);
            $stmt->execute();
            
            return $stmt->get_result()->num_rows > 0;
            
        } catch (Exception $e) {
            error_log("Token validation error: " . $e->getMessage());
            return false;
        }
    }
    
    public function resetPassword($token, $newPassword) {
        try {
            // Get user with valid token
            $stmt = $this->conn->prepare("
                SELECT id 
                FROM users 
                WHERE reset_token = ? 
                AND reset_expiry > NOW() 
                AND status = 'active'
            ");
            $stmt->bind_param("s", $token);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows === 0) {
                throw new Exception("Invalid or expired reset token");
            }
            
            $userId = $result->fetch_assoc()['id'];
            
            // Hash new password
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            
            // Update password and clear reset token
            $stmt = $this->conn->prepare("
                UPDATE users 
                SET password = ?,
                    reset_token = NULL,
                    reset_expiry = NULL 
                WHERE id = ?
            ");
            $stmt->bind_param("si", $hashedPassword, $userId);
            $stmt->execute();
            
            return true;
            
        } catch (Exception $e) {
            error_log("Password reset error: " . $e->getMessage());
            throw new Exception("Failed to reset password");
        }
    }
}

