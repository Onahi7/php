<?php
class RateLimiter {
    private $conn;
    private $table = 'rate_limits';
    
    public function __construct($conn) {
        $this->conn = $conn;
        $this->initTable();
    }
    
    public function check($key, $limit = 60, $period = 60) {
        $ip = $_SERVER['REMOTE_ADDR'];
        $now = time();
        
        $this->cleanup();
        
        $stmt = $this->conn->prepare("
            SELECT COUNT(*) as attempts 
            FROM {$this->table} 
            WHERE ip = ? AND action_key = ? AND timestamp > ?
        ");
        
        $timestamp = $now - $period;
        $stmt->bind_param("ssi", $ip, $key, $timestamp);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        
        if ($result['attempts'] >= $limit) {
            return false;
        }
        
        $stmt = $this->conn->prepare("
            INSERT INTO {$this->table} (ip, action_key, timestamp) 
            VALUES (?, ?, ?)
        ");
        
        $stmt->bind_param("ssi", $ip, $key, $now);
        $stmt->execute();
        
        return true;
    }
    
    private function cleanup() {
        $expire = time() - 3600;
        $this->conn->query("DELETE FROM {$this->table} WHERE timestamp < {$expire}");
    }
    
    private function initTable() {
        $this->conn->query("
            CREATE TABLE IF NOT EXISTS {$this->table} (
                id INT AUTO_INCREMENT PRIMARY KEY,
                ip VARCHAR(45) NOT NULL,
                action_key VARCHAR(64) NOT NULL,
                timestamp INT NOT NULL,
                INDEX idx_ip_key (ip, action_key),
                INDEX idx_timestamp (timestamp)
            )
        ");
    }
}

