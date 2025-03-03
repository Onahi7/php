<?php
class AdminController {
    private $conn;
    
    public function __construct($conn) {
        $this->conn = $conn;
    }
    
    public function getDashboardStats() {
        $stats = [
            'total_registrations' => $this->getTotalRegistrations(),
            'total_payments' => $this->getTotalPayments(),
            'pending_verifications' => $this->getPendingVerifications(),
            'recent_registrations' => $this->getRecentRegistrations(),
            'recent_payments' => $this->getRecentPayments()
        ];
        
        return $stats;
    }
    
    public function getParticipants($page = 1, $limit = 10, $filters = []) {
        $offset = ($page - 1) * $limit;
        $where = [];
        $params = [];
        $types = "";
        
        if (!empty($filters['search'])) {
            $where[] = "(u.name LIKE ? OR u.email LIKE ?)";
            $search = "%" . $filters['search'] . "%";
            $params[] = $search;
            $params[] = $search;
            $types .= "ss";
        }
        
        if (!empty($filters['status'])) {
            $where[] = "u.status = ?";
            $params[] = $filters['status'];
            $types .= "s";
        }
        
        $whereClause = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";
        
        $query = "
            SELECT u.*, p.status as payment_status, p.amount 
            FROM users u 
            LEFT JOIN payments p ON u.id = p.user_id 
            $whereClause 
            ORDER BY u.created_at DESC 
            LIMIT ?, ?
        ";
        
        $params[] = $offset;
        $params[] = $limit;
        $types .= "ii";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        
        $results = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        
        // Get total count for pagination
        $countQuery = "
            SELECT COUNT(*) as total 
            FROM users u 
            $whereClause
        ";
        
        array_pop($params); // Remove limit
        array_pop($params); // Remove offset
        $types = substr($types, 0, -2);
        
        if (!empty($params)) {
            $stmt = $this->conn->prepare($countQuery);
            $stmt->bind_param($types, ...$params);
            $stmt->execute();
            $total = $stmt->get_result()->fetch_assoc()['total'];
        } else {
            $total = $this->conn->query($countQuery)->fetch_assoc()['total'];
        }
        
        return [
            'data' => $results,
            'total' => $total,
            'pages' => ceil($total / $limit),
            'current_page' => $page
        ];
    }
    
    public function generateReport($type, $filters = []) {
        switch ($type) {
            case 'registrations':
                return $this->generateRegistrationsReport($filters);
            case 'payments':
                return $this->generatePaymentsReport($filters);
            default:
                throw new Exception("Invalid report type");
        }
    }
    
    public function updateSettings($settings) {
        foreach ($settings as $key => $value) {
            $stmt = $this->conn->prepare("
                INSERT INTO settings (name, value) 
                VALUES (?, ?) 
                ON DUPLICATE KEY UPDATE value = ?
            ");
            
            $stmt->bind_param("sss", $key, $value, $value);
            $stmt->execute();
        }
        
        return true;
    }
    
    private function getTotalRegistrations() {
        return $this->conn->query("SELECT COUNT(*) as total FROM users")->fetch_assoc()['total'];
    }
    
    private function getTotalPayments() {
        return $this->conn->query("
            SELECT SUM(amount) as total 
            FROM payments 
            WHERE status = 'completed'
        ")->fetch_assoc()['total'] ?? 0;
    }
    
    private function getPendingVerifications() {
        return $this->conn->query("
            SELECT COUNT(*) as total 
            FROM users 
            WHERE verified_at IS NULL
        ")->fetch_assoc()['total'];
    }
    
    private function getRecentRegistrations($limit = 5) {
        return $this->conn->query("
            SELECT * FROM users 
            ORDER BY created_at DESC 
            LIMIT $limit
        ")->fetch_all(MYSQLI_ASSOC);
    }
    
    private function getRecentPayments($limit = 5) {
        return $this->conn->query("
            SELECT p.*, u.name, u.email 
            FROM payments p 
            JOIN users u ON p.user_id = u.id 
            ORDER BY p.created_at DESC 
            LIMIT $limit
        ")->fetch_all(MYSQLI_ASSOC);
    }
    
    private function generateRegistrationsReport($filters) {
        // Implementation for generating registration report
        // This would typically create a CSV or PDF file
        return "report_implementation";
    }
    
    private function generatePaymentsReport($filters) {
        // Implementation for generating payments report
        // This would typically create a CSV or PDF file
        return "report_implementation";
    }
}

