<?php
/**
 * MealManager class - Handles meal validation and tracking
 */
class MealManager {
    private $conn;
    
    public function __construct($conn) {
        $this->conn = $conn;
    }
    
    /**
     * Validate an attendee by phone number or barcode
     */
    public function validateAttendee($identifier) {
        // Check if identifier is a phone number or barcode
        if (preg_match('/^[0-9]+$/', $identifier)) {
            $sql = "SELECT * FROM users WHERE phone = :identifier";
        } else {
            $sql = "SELECT * FROM users WHERE barcode = :identifier";
        }
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':identifier', $identifier);
        $stmt->execute();
        
        $attendee = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$attendee) {
            throw new Exception("Attendee not found");
        }
        
        return $attendee;
    }
    
    /**
     * Record a meal for an attendee
     */
    public function recordMeal($userId, $mealType) {
        // Check if already had this meal today
        if ($this->hasHadMealToday($userId, $mealType)) {
            throw new Exception("Already had $mealType meal today");
        }
        
        // Record the meal
        $sql = "INSERT INTO meal_records (user_id, meal_type, validated_by, created_at) 
                VALUES (:user_id, :meal_type, :validated_by, NOW())";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':user_id', $userId);
        $stmt->bindParam(':meal_type', $mealType);
        $stmt->bindParam(':validated_by', $_SESSION['user_id']);
        
        if (!$stmt->execute()) {
            throw new Exception("Failed to record meal");
        }
        
        return true;
    }
    
    /**
     * Check if attendee has already had this meal today
     */
    private function hasHadMealToday($userId, $mealType) {
        $sql = "SELECT COUNT(*) FROM meal_records 
                WHERE user_id = :user_id 
                AND meal_type = :meal_type 
                AND DATE(created_at) = CURDATE()";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':user_id', $userId);
        $stmt->bindParam(':meal_type', $mealType);
        $stmt->execute();
        
        return (int)$stmt->fetchColumn() > 0;
    }
    
    /**
     * Get meal statistics
     */
    public function getMealStats() {
        $stats = [
            'today' => [
                'morning' => 0,
                'evening' => 0,
                'total' => 0
            ],
            'total' => [
                'morning' => 0,
                'evening' => 0,
                'total' => 0
            ]
        ];
        
        // Today stats
        $sql = "SELECT meal_type, COUNT(*) as count FROM meal_records 
                WHERE DATE(created_at) = CURDATE() 
                GROUP BY meal_type";
        
        $stmt = $this->conn->query($sql);
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $stats['today'][$row['meal_type']] = (int)$row['count'];
            $stats['today']['total'] += (int)$row['count'];
        }
        
        // Total stats
        $sql = "SELECT meal_type, COUNT(*) as count FROM meal_records GROUP BY meal_type";
        
        $stmt = $this->conn->query($sql);
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $stats['total'][$row['meal_type']] = (int)$row['count'];
            $stats['total']['total'] += (int)$row['count'];
        }
        
        return $stats;
    }
    
    /**
     * Get recent meal validations
     */
    public function getRecentValidations($limit = 10) {
        $sql = "SELECT m.*, u.name, u.phone, u.email, v.name as validated_by_name
                FROM meal_records m
                JOIN users u ON m.user_id = u.id
                JOIN users v ON m.validated_by = v.id
                ORDER BY m.created_at DESC
                LIMIT :limit";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
