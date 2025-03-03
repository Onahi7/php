<?php
class ActivityLogger {
    private $conn;
    
    public function __construct($conn) {
        $this->conn = $conn;
        $this->initTable();
    }
    
    private function initTable() {
        $this->conn->query("
            CREATE TABLE IF NOT EXISTS activity_logs (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT,
                action VARCHAR(255) NOT NULL,
                entity_type VARCHAR(50),
                entity_id VARCHAR(50),
                details TEXT,
                ip_address VARCHAR(45),
                user_agent VARCHAR(255),
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_user (user_id),
                INDEX idx_action (action),
                INDEX idx_entity (entity_type, entity_id),
                INDEX idx_created_at (created_at),
                INDEX idx_ip (ip_address)
            )
        ");
    }
    
    public function log($action, $entityType = null, $entityId = null, $details = null) {
        try {
            $userId = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
            $ipAddress = $_SERVER['REMOTE_ADDR'];
            $userAgent = $_SERVER['HTTP_USER_AGENT'];
            
            $stmt = $this->conn->prepare("
                INSERT INTO activity_logs 
                (user_id, action, entity_type, entity_id, details, ip_address, user_agent) 
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            
            $stmt->bind_param(
                "issssss",
                $userId,
                $action,
                $entityType,
                $entityId,
                $details,
                $ipAddress,
                $userAgent
            );
            
            return $stmt->execute();
        } catch (Exception $e) {
            error_log("Activity logging error: " . $e->getMessage());
            throw new Exception("Failed to log activity");
        }
    }
    
    public function getActivities($filters = [], $page = 1, $limit = 50) {
        $offset = ($page - 1) * $limit;
        $where = [];
        $params = [];
        $types = "";
        
        if (!empty($filters['user_id'])) {
            $where[] = "user_id = ?";
            $params[] = $filters['user_id'];
            $types .= "i";
        }
        
        if (!empty($filters['action'])) {
            $where[] = "action = ?";
            $params[] = $filters['action'];
            $types .= "s";
        }
        
        if (!empty($filters['entity_type'])) {
            $where[] = "entity_type = ?";
            $params[] = $filters['entity_type'];
            $types .= "s";
        }
        
        if (!empty($filters['date_from'])) {
            $where[] = "created_at >= ?";
            $params[] = $filters['date_from'];
            $types .= "s";
        }
        
        if (!empty($filters['date_to'])) {
            $where[] = "created_at <= ?";
            $params[] = $filters['date_to'];
            $types .= "s";
        }
        
        $whereClause = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";
        
        $query = "
            SELECT 
                al.*,
                u.name as user_name,
                u.email as user_email
            FROM activity_logs al
            LEFT JOIN users u ON al.user_id = u.id
            $whereClause
            ORDER BY al.created_at DESC
            LIMIT ?, ?
        ";
        
        $params[] = $offset;
        $params[] = $limit;
        $types .= "ii";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
    
    public function purgeOldLogs($days = 90) {
        $date = date('Y-m-d H:i:s', strtotime("-$days days"));
        
        $stmt = $this->conn->prepare("
            DELETE FROM activity_logs 
            WHERE created_at < ?
        ");
        
        $stmt->bind_param("s", $date);
        return $stmt->execute();
    }
}

