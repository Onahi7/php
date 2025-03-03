<?php
session_start();
require_once '../config/database.php';

class AccreditationHandler {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    // Verify agent credentials
    public function verifyAgent($username, $password) {
        $sql = "SELECT * FROM agents WHERE username = ? AND status = 'active'";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$username]);
        $agent = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($agent && password_verify($password, $agent['password'])) {
            return $agent;
        }
        return false;
    }

    // Record meal ticket validation
    public function recordValidation($ticketCode, $agentId) {
        $sql = "INSERT INTO ticket_validations (ticket_code, agent_id, validated_at) 
                VALUES (?, ?, NOW())";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$ticketCode, $agentId]);
    }

    // Get agent's validation history
    public function getAgentHistory($agentId) {
        $sql = "SELECT * FROM ticket_validations WHERE agent_id = ? 
                ORDER BY validated_at DESC LIMIT 50";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$agentId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
