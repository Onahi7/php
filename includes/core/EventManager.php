<?php
namespace Summit\Core;

class EventManager {
    private static $instance = null;
    private $db;
    private $cache;

    private function __construct() {
        $this->db = Database::getInstance();
        $this->cache = CacheManager::getInstance();
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    // Session Management
    public function createSession($data) {
        try {
            $sql = "INSERT INTO sessions (title, description, speaker_id, start_time, end_time, venue, capacity, track) 
                    VALUES (:title, :description, :speaker_id, :start_time, :end_time, :venue, :capacity, :track)";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute($data);
            
            $sessionId = $this->db->lastInsertId();
            $this->cache->delete('sessions_list');
            
            return $sessionId;
        } catch (\Exception $e) {
            ErrorHandler::logError("Failed to create session: " . $e->getMessage());
            throw $e;
        }
    }

    public function updateSession($sessionId, $data) {
        try {
            $sql = "UPDATE sessions SET 
                    title = :title, 
                    description = :description,
                    speaker_id = :speaker_id,
                    start_time = :start_time,
                    end_time = :end_time,
                    venue = :venue,
                    capacity = :capacity,
                    track = :track
                    WHERE id = :id";
            
            $data['id'] = $sessionId;
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute($data);
            
            $this->cache->delete('sessions_list');
            $this->cache->delete("session:{$sessionId}");
            
            return $result;
        } catch (\Exception $e) {
            ErrorHandler::logError("Failed to update session: " . $e->getMessage());
            throw $e;
        }
    }

    // Speaker Management
    public function addSpeaker($data) {
        try {
            $sql = "INSERT INTO speakers (name, bio, expertise, photo, email, phone, social_links) 
                    VALUES (:name, :bio, :expertise, :photo, :email, :phone, :social_links)";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute($data);
            
            $speakerId = $this->db->lastInsertId();
            $this->cache->delete('speakers_list');
            
            return $speakerId;
        } catch (\Exception $e) {
            ErrorHandler::logError("Failed to add speaker: " . $e->getMessage());
            throw $e;
        }
    }

    public function updateSpeaker($speakerId, $data) {
        try {
            $sql = "UPDATE speakers SET 
                    name = :name,
                    bio = :bio,
                    expertise = :expertise,
                    photo = :photo,
                    email = :email,
                    phone = :phone,
                    social_links = :social_links
                    WHERE id = :id";
            
            $data['id'] = $speakerId;
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute($data);
            
            $this->cache->delete('speakers_list');
            $this->cache->delete("speaker:{$speakerId}");
            
            return $result;
        } catch (\Exception $e) {
            ErrorHandler::logError("Failed to update speaker: " . $e->getMessage());
            throw $e;
        }
    }

    // Attendance Tracking
    public function recordAttendance($sessionId, $userId, $status = 'present') {
        try {
            $sql = "INSERT INTO attendance (session_id, user_id, status, check_in_time) 
                    VALUES (:session_id, :user_id, :status, NOW())";
            
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([
                'session_id' => $sessionId,
                'user_id' => $userId,
                'status' => $status
            ]);
            
            // Update session statistics
            $this->updateSessionStats($sessionId);
            
            return $result;
        } catch (\Exception $e) {
            ErrorHandler::logError("Failed to record attendance: " . $e->getMessage());
            throw $e;
        }
    }

    public function getSessionAttendance($sessionId) {
        try {
            $sql = "SELECT a.*, u.name, u.email 
                    FROM attendance a 
                    JOIN users u ON a.user_id = u.id 
                    WHERE a.session_id = :session_id";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['session_id' => $sessionId]);
            
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            ErrorHandler::logError("Failed to get session attendance: " . $e->getMessage());
            throw $e;
        }
    }

    // Schedule Management
    public function getSchedule($date = null, $track = null) {
        $cacheKey = "schedule:{$date}:{$track}";
        $schedule = $this->cache->get($cacheKey);
        
        if (!$schedule) {
            try {
                $sql = "SELECT s.*, sp.name as speaker_name, sp.photo as speaker_photo 
                        FROM sessions s 
                        LEFT JOIN speakers sp ON s.speaker_id = sp.id 
                        WHERE (:date IS NULL OR DATE(s.start_time) = :date)
                        AND (:track IS NULL OR s.track = :track)
                        ORDER BY s.start_time";
                
                $stmt = $this->db->prepare($sql);
                $stmt->execute([
                    'date' => $date,
                    'track' => $track
                ]);
                
                $schedule = $stmt->fetchAll(\PDO::FETCH_ASSOC);
                $this->cache->set($cacheKey, json_encode($schedule), 3600);
            } catch (\Exception $e) {
                ErrorHandler::logError("Failed to get schedule: " . $e->getMessage());
                throw $e;
            }
        } else {
            $schedule = json_decode($schedule, true);
        }
        
        return $schedule;
    }

    private function updateSessionStats($sessionId) {
        try {
            $sql = "UPDATE sessions s 
                    SET attendance_count = (
                        SELECT COUNT(*) FROM attendance 
                        WHERE session_id = s.id AND status = 'present'
                    ) 
                    WHERE id = :session_id";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['session_id' => $sessionId]);
            
            $this->cache->delete("session:{$sessionId}");
        } catch (\Exception $e) {
            ErrorHandler::logError("Failed to update session stats: " . $e->getMessage());
        }
    }

    // Analytics
    public function getSessionAnalytics($sessionId) {
        try {
            $sql = "SELECT 
                    COUNT(*) as total_attendance,
                    SUM(CASE WHEN status = 'present' THEN 1 ELSE 0 END) as present_count,
                    SUM(CASE WHEN status = 'absent' THEN 1 ELSE 0 END) as absent_count,
                    AVG(CASE WHEN feedback_rating IS NOT NULL THEN feedback_rating ELSE 0 END) as avg_rating
                    FROM attendance 
                    WHERE session_id = :session_id";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['session_id' => $sessionId]);
            
            return $stmt->fetch(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            ErrorHandler::logError("Failed to get session analytics: " . $e->getMessage());
            throw $e;
        }
    }
}
