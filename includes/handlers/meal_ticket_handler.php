<?php
session_start();
require_once '../config/database.php';

class MealTicketHandler {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    // Generate meal ticket with unique code
    public function generateMealTicket($userId, $date) {
        $ticketCode = 'MT' . date('Ymd', strtotime($date)) . str_pad($userId, 4, '0', STR_PAD_LEFT);
        $sql = "INSERT INTO meal_tickets (user_id, ticket_code, date_valid, status) 
                VALUES (?, ?, ?, 'unused')";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId, $ticketCode, $date]);
        return $ticketCode;
    }

    // Validate and mark meal ticket as used
    public function useMealTicket($ticketCode) {
        $sql = "UPDATE meal_tickets SET status = 'used', used_at = NOW() 
                WHERE ticket_code = ? AND status = 'unused'";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$ticketCode]);
    }

    // Check if user has unused ticket for the day
    public function hasValidTicket($userId, $date) {
        $sql = "SELECT * FROM meal_tickets 
                WHERE user_id = ? AND date_valid = ? AND status = 'unused'";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId, $date]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
