<?php
namespace Summit\Core;

class MealManager {
    private static $instance = null;
    private $db;

    private function __construct() {
        $this->db = Database::getInstance();
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function validateAttendee($identifier) {
        // Check if it's a phone number (contains only numbers)
        if (preg_match('/^[0-9]+$/', $identifier)) {
            $sql = "SELECT * FROM users WHERE phone = :identifier";
        } else {
            // Treat as barcode
            $sql = "SELECT * FROM users WHERE barcode = :identifier";
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['identifier' => $identifier]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    public function recordMeal($userId, $mealType, $validatorId) {
        try {
            // Check if already had meal today
            if ($this->hasHadMealToday($userId, $mealType)) {
                throw new \Exception("Already had $mealType meal today");
            }

            $sql = "INSERT INTO meal_records (user_id, meal_type, validator_id, served_at) 
                    VALUES (:user_id, :meal_type, :validator_id, NOW())";
            
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                'user_id' => $userId,
                'meal_type' => $mealType,
                'validator_id' => $validatorId
            ]);
        } catch (\Exception $e) {
            ErrorHandler::logError("Failed to record meal: " . $e->getMessage());
            throw $e;
        }
    }

    private function hasHadMealToday($userId, $mealType) {
        $sql = "SELECT COUNT(*) FROM meal_records 
                WHERE user_id = :user_id 
                AND meal_type = :meal_type 
                AND DATE(served_at) = CURDATE()";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'user_id' => $userId,
            'meal_type' => $mealType
        ]);
        
        return $stmt->fetchColumn() > 0;
    }

    public function getMealStats($date = null) {
        $date = $date ?? date('Y-m-d');
        
        $sql = "SELECT 
                meal_type,
                COUNT(*) as total_served,
                COUNT(DISTINCT user_id) as unique_users
                FROM meal_records
                WHERE DATE(served_at) = :date
                GROUP BY meal_type";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['date' => $date]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function getRecentValidations($limit = 10) {
        $sql = "SELECT 
                m.*, 
                u.name as participant_name,
                v.name as validator_name
                FROM meal_records m
                JOIN users u ON m.user_id = u.id
                JOIN users v ON m.validator_id = v.id
                ORDER BY m.served_at DESC
                LIMIT :limit";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue('limit', $limit, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function getTeamStats() {
        $sql = "SELECT 
                v.id,
                v.name,
                COUNT(*) as validations_today,
                SUM(CASE WHEN m.meal_type = 'morning' THEN 1 ELSE 0 END) as morning_validations,
                SUM(CASE WHEN m.meal_type = 'evening' THEN 1 ELSE 0 END) as evening_validations
                FROM users v
                LEFT JOIN meal_records m ON v.id = m.validator_id 
                    AND DATE(m.served_at) = CURDATE()
                WHERE v.role = 'validator'
                GROUP BY v.id, v.name
                ORDER BY validations_today DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function getValidatorStats($validatorId, $date = null) {
        $date = $date ?? date('Y-m-d');
        
        $sql = "SELECT 
                COUNT(*) as total_validations,
                SUM(CASE WHEN meal_type = 'morning' THEN 1 ELSE 0 END) as morning_count,
                SUM(CASE WHEN meal_type = 'evening' THEN 1 ELSE 0 END) as evening_count,
                MIN(served_at) as first_validation,
                MAX(served_at) as last_validation
                FROM meal_records
                WHERE validator_id = :validator_id
                AND DATE(served_at) = :date";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'validator_id' => $validatorId,
            'date' => $date
        ]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }
}
