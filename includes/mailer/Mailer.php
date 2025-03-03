<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class Mailer {
    private $mailer;
    private $conn;
    
    public function __construct() {
        require_once __DIR__ . '/../vendor/phpmailer/phpmailer/src/Exception.php';
        require_once __DIR__ . '/../vendor/phpmailer/phpmailer/src/PHPMailer.php';
        require_once __DIR__ . '/../vendor/phpmailer/phpmailer/src/SMTP.php';
        
        $this->mailer = new PHPMailer(true);
        
        // Server settings
        $this->mailer->isSMTP();
        $this->mailer->Host = SMTP_HOST;
        $this->mailer->SMTPAuth = true;
        $this->mailer->Username = SMTP_USER;
        $this->mailer->Password = SMTP_PASS;
        $this->mailer->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $this->mailer->Port = SMTP_PORT;
        
        // Default settings
        $this->mailer->isHTML(true);
        $this->mailer->setFrom(SMTP_FROM, SITE_NAME);
    }
    
    public function sendWelcomeEmail($userId) {
        try {
            $user = $this->getUserData($userId);
            
            $this->mailer->addAddress($user['email'], $user['name']);
            $this->mailer->Subject = "Welcome to " . SITE_NAME;
            
            $body = $this->getEmailTemplate('welcome', [
                'name' => $user['name'],
                'verificationLink' => $this->generateVerificationLink($userId)
            ]);
            
            $this->mailer->Body = $body;
            
            return $this->mailer->send();
        } catch (Exception $e) {
            error_log("Email error: " . $e->getMessage());
            return false;
        }
    }
    
    public function sendPaymentConfirmation($reference) {
        try {
            $payment = $this->getPaymentData($reference);
            
            $this->mailer->addAddress($payment['email'], $payment['name']);
            $this->mailer->Subject = "Payment Confirmation - " . SITE_NAME;
            
            $body = $this->getEmailTemplate('payment', [
                'name' => $payment['name'],
                'amount' => number_format($payment['amount'], 2),
                'reference' => $payment['reference'],
                'date' => date('F j, Y', strtotime($payment['created_at']))
            ]);
            
            // Attach receipt if available
            if ($payment['receipt_path']) {
                $this->mailer->addAttachment(
                    __DIR__ . '/../../uploads/receipts/' . $payment['receipt_path'],
                    'receipt.pdf'
                );
            }
            
            $this->mailer->Body = $body;
            
            return $this->mailer->send();
        } catch (Exception $e) {
            error_log("Email error: " . $e->getMessage());
            return false;
        }
    }
    
    public function sendPasswordReset($email) {
        try {
            $user = $this->getUserByEmail($email);
            if (!$user) {
                return false;
            }
            
            $token = bin2hex(random_bytes(32));
            $this->saveResetToken($user['id'], $token);
            
            $this->mailer->addAddress($email, $user['name']);
            $this->mailer->Subject = "Password Reset - " . SITE_NAME;
            
            $body = $this->getEmailTemplate('reset-password', [
                'name' => $user['name'],
                'resetLink' => SITE_URL . '/reset-password?token=' . $token
            ]);
            
            $this->mailer->Body = $body;
            
            return $this->mailer->send();
        } catch (Exception $e) {
            error_log("Email error: " . $e->getMessage());
            return false;
        }
    }
    
    private function getEmailTemplate($template, $data) {
        $file = __DIR__ . '/../../views/emails/' . $template . '.php';
        if (!file_exists($file)) {
            throw new Exception("Email template not found");
        }
        
        ob_start();
        extract($data);
        include $file;
        return ob_get_clean();
    }
    
    private function getUserData($userId) {
        $stmt = $this->conn->prepare("
            SELECT name, email 
            FROM users 
            WHERE id = ?
        ");
        
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }
    
    private function getPaymentData($reference) {
        $stmt = $this->conn->prepare("
            SELECT p.*, u.name, u.email 
            FROM payments p 
            JOIN users u ON p.user_id = u.id 
            WHERE p.reference = ?
        ");
        
        $stmt->bind_param("s", $reference);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }
    
    private function generateVerificationLink($userId) {
        $token = bin2hex(random_bytes(32));
        
        $stmt = $this->conn->prepare("
            UPDATE users 
            SET verification_token = ? 
            WHERE id = ?
        ");
        
        $stmt->bind_param("si", $token, $userId);
        $stmt->execute();
        
        return SITE_URL . '/verify-email?token=' . $token;
    }
    
    private function saveResetToken($userId, $token) {
        $stmt = $this->conn->prepare("
            UPDATE users 
            SET reset_token = ?, 
                reset_token_expires = DATE_ADD(NOW(), INTERVAL 1 HOUR) 
            WHERE id = ?
        ");
        
        $stmt->bind_param("si", $token, $userId);
        $stmt->execute();
    }
}

