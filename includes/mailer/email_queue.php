<?php
class EmailQueue {
    private $conn;
    private $mailer;
    private $maxRetries = 3;
    private $retryDelay = 300; // 5 minutes
    
    public function __construct($conn) {
        $this->conn = $conn;
        $this->initTable();
        require_once __DIR__ . '/Mailer.php';
        $this->mailer = new Mailer();
    }
    
    private function initTable() {
        $this->conn->query("
            CREATE TABLE IF NOT EXISTS email_queue (
                id INT AUTO_INCREMENT PRIMARY KEY,
                to_email VARCHAR(255) NOT NULL,
                to_name VARCHAR(255),
                subject VARCHAR(255) NOT NULL,
                body TEXT NOT NULL,
                attachments TEXT,
                priority TINYINT DEFAULT 1,
                status ENUM('pending', 'processing', 'sent', 'failed') DEFAULT 'pending',
                retry_count INT DEFAULT 0,
                last_attempt DATETIME,
                next_attempt DATETIME,
                error_message TEXT,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_status_priority (status, priority),
                INDEX idx_next_attempt (next_attempt)
            )
        ");
    }
    
    public function queueEmail($toEmail, $toName, $subject, $body, $attachments = null, $priority = 1) {
        try {
            $stmt = $this->conn->prepare("
                INSERT INTO email_queue 
                (to_email, to_name, subject, body, attachments, priority, next_attempt) 
                VALUES (?, ?, ?, ?, ?, ?, NOW())
            ");
            
            $attachmentsJson = $attachments ? json_encode($attachments) : null;
            
            $stmt->bind_param(
                "sssssi",
                $toEmail,
                $toName,
                $subject,
                $body,
                $attachmentsJson,
                $priority
            );
            
            if (!$stmt->execute()) {
                throw new Exception("Failed to queue email: " . $stmt->error);
            }
            
            return true;
        } catch (Exception $e) {
            error_log("Email queue error: " . $e->getMessage());
            throw new Exception("Failed to queue email");
        }
    }
    
    public function processQueue($limit = 50) {
        $stmt = $this->conn->prepare("
            SELECT * FROM email_queue 
            WHERE status IN ('pending', 'failed') 
            AND retry_count < ? 
            AND next_attempt <= NOW() 
            ORDER BY priority DESC, created_at ASC 
            LIMIT ?
        ");
        
        $stmt->bind_param("ii", $this->maxRetries, $limit);
        $stmt->execute();
        $emails = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        
        foreach ($emails as $email) {
            $this->processEmail($email);
        }
    }
    
    private function processEmail($email) {
        if (empty($email['to_email']) || empty($email['subject']) || empty($email['body'])) {
            throw new Exception("Invalid email data");
        }
        
        // Update status to processing
        $stmt = $this->conn->prepare("
            UPDATE email_queue 
            SET status = 'processing', 
                last_attempt = NOW() 
            WHERE id = ?
        ");
        $stmt->bind_param("i", $email['id']);
        $stmt->execute();
        
        try {
            // Send email
            $attachments = $email['attachments'] ? json_decode($email['attachments'], true) : [];
            $result = $this->mailer->send(
                $email['to_email'],
                $email['to_name'],
                $email['subject'],
                $email['body'],
                $attachments
            );
            
            if ($result) {
                // Mark as sent
                $stmt = $this->conn->prepare("
                    UPDATE email_queue 
                    SET status = 'sent', 
                        updated_at = NOW() 
                    WHERE id = ?
                ");
                $stmt->bind_param("i", $email['id']);
                $stmt->execute();
            } else {
                throw new Exception("Failed to send email");
            }
            
        } catch (Exception $e) {
            // Update retry count and next attempt
            $retryCount = $email['retry_count'] + 1;
            $nextAttempt = date('Y-m-d H:i:s', time() + ($this->retryDelay * $retryCount));
            
            $stmt = $this->conn->prepare("
                UPDATE email_queue 
                SET status = 'failed',
                    retry_count = ?,
                    next_attempt = ?,
                    error_message = ?,
                    updated_at = NOW()
                WHERE id = ?
            ");
            
            $errorMessage = $e->getMessage();
            $stmt->bind_param("issi", $retryCount, $nextAttempt, $errorMessage, $email['id']);
            $stmt->execute();
        }
    }
    
    public function cleanupQueue($days = 30) {
        $date = date('Y-m-d H:i:s', strtotime("-$days days"));
        
        $stmt = $this->conn->prepare("
            DELETE FROM email_queue 
            WHERE (status = 'sent' AND created_at < ?) 
            OR (status = 'failed' AND retry_count >= ? AND created_at < ?)
        ");
        
        $stmt->bind_param("sis", $date, $this->maxRetries, $date);
        return $stmt->execute();
    }
    
    public function getQueueStats() {
        $query = "
            SELECT 
                status,
                COUNT(*) as count,
                AVG(retry_count) as avg_retries
            FROM email_queue
            GROUP BY status
        ";
        
        return $this->conn->query($query)->fetch_all(MYSQLI_ASSOC);
    }
}

