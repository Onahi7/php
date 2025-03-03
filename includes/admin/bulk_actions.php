<?php

class BulkActions {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    public function processAction($action, $ids, $data = []) {
        // Validate action
        if (!in_array($action, ['approve', 'email', 'export', 'update_status'])) {
            throw new Exception("Invalid bulk action");
        }
        
        // Validate IDs
        $ids = array_filter($ids, 'is_numeric');
        if (empty($ids)) {
            throw new Exception("No valid IDs provided");
        }
        
        // Validate data
        if ($action === 'email') {
            if (empty($data['subject']) || empty($data['message'])) {
                throw new Exception("Email subject and message are required");
            }
            $data['subject'] = strip_tags($data['subject']);
            $data['message'] = strip_tags($data['message']);
        }
        
        switch ($action) {
            case 'approve':
                return $this->approveParticipants($ids);
            case 'email':
                return $this->sendBulkEmail($ids, $data['subject'], $data['message']);
            case 'export':
                return $this->exportParticipants($ids, $data['format'] ?? 'pdf');
            case 'update_status':
                return $this->updateStatus($ids, $data['status']);
        }
    }

    private function approveParticipants($ids) {
        $this->conn->begin_transaction();
        
        try {
            $stmt = $this->conn->prepare("
                UPDATE users 
                SET status = 'approved',
                    approved_at = NOW(),
                    approved_by = ?
                WHERE id IN (" . implode(',', array_fill(0, count($ids), '?')) . ")
            ");
            
            $params = array_merge([$_SESSION['admin_id']], $ids);
            $types = str_repeat('i', count($params));
            $stmt->bind_param($types, ...$params);
            
            if (!$stmt->execute()) {
                throw new Exception("Failed to update user status");
            }
            
            $this->sendApprovalEmails($ids);
            $this->conn->commit();
            return true;
            
        } catch (Exception $e) {
            $this->conn->rollback();
            error_log("Bulk approval error: " . $e->getMessage());
            throw new Exception("Failed to approve participants");
        }
    }

    private function sendApprovalEmails($ids) {
        require_once __DIR__ . '/../mailer/email_queue.php';
        $emailQueue = new EmailQueue($this->conn);
    
        $stmt = $this->conn->prepare("
            SELECT email, name 
            FROM users 
            WHERE id IN (" . implode(',', array_fill(0, count($ids), '?')) . ")
        ");
    
        $stmt->bind_param(str_repeat('i', count($ids)), ...$ids);
        $stmt->execute();
        $users = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    
        foreach ($users as $user) {
            $emailQueue->queueEmail(
                $user['email'],
                $user['name'],
                'Registration Approved - North Central Education Summit',
                $this->getApprovalEmailTemplate($user['name'])
            );
        }
    }

    private function sendBulkEmail($ids, $subject, $message) {
        try {
            require_once __DIR__ . '/../mailer/email_queue.php';
            $emailQueue = new EmailQueue($this->conn);
        
            $stmt = $this->conn->prepare("
                SELECT email, name 
                FROM users 
                WHERE id IN (" . implode(',', array_fill(0, count($ids), '?')) . ")
            ");
        
            $stmt->bind_param(str_repeat('i', count($ids)), ...$ids);
            $stmt->execute();
            $users = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        
            foreach ($users as $user) {
                $personalizedMessage = str_replace(
                    ['{name}', '{email}'],
                    [$user['name'], $user['email']],
                    $message
                );
            
                $emailQueue->queueEmail(
                    $user['email'],
                    $user['name'],
                    $subject,
                    $personalizedMessage,
                    null,
                    2 // Higher priority for bulk emails
                );
            }
        
            return true;
        } catch (Exception $e) {
            error_log("Bulk email error: " . $e->getMessage());
            throw new Exception("Failed to send bulk emails");
        }
    }

    private function exportParticipants($ids, $format) {
        try {
            require_once __DIR__ . '/reports.php';
            $reportGenerator = new ReportGenerator($this->conn);
        
            $filters = ['ids' => $ids];
            $reportName = 'participants_export_' . date('Y-m-d_H-i-s');
        
            return $reportGenerator->generateReport($format, 'participants', $filters);
        } catch (Exception $e) {
            error_log("Export error: " . $e->getMessage());
            throw new Exception("Failed to export participants");
        }
    }

    private function updateStatus($ids, $status) {
        try {
            if (!in_array($status, ['active', 'inactive', 'suspended'])) {
                throw new Exception("Invalid status");
            }
        
            $this->conn->begin_transaction();
        
            $stmt = $this->conn->prepare("
                UPDATE users 
                SET status = ?,
                    updated_at = NOW(),
                    updated_by = ?
                WHERE id IN (" . implode(',', array_fill(0, count($ids), '?')) . ")
            ");
        
            $params = array_merge([$status, $_SESSION['admin_id']], $ids);
            $types = 'si' . str_repeat('i', count($ids));
            $stmt->bind_param($types, ...$params);
        
            if (!$stmt->execute()) {
                throw new Exception("Failed to update status");
            }
        
            // Log the status change
            require_once __DIR__ . '/../logging/activity_logger.php';
            $logger = new ActivityLogger($this->conn);
            $logger->log(
                'bulk_status_update',
                'users',
                implode(',', $ids),
                "Status updated to: $status"
            );
        
            $this->conn->commit();
            return true;
        
        } catch (Exception $e) {
            $this->conn->rollback();
            error_log("Status update error: " . $e->getMessage());
            throw new Exception("Failed to update status");
        }
    }

    private function getApprovalEmailTemplate($name) {
        $template = file_get_contents(__DIR__ . '/../../views/emails/registration_approved.php');
        return str_replace('{name}', $name, $template);
    }
}
?>

