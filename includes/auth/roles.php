<?php
class RoleManager {
    private $conn;
    private $roles = [
        'user' => 1,
        'moderator' => 2,
        'admin' => 3,
        'super_admin' => 4
    ];
    
    public function __construct($conn) {
        $this->conn = $conn;
        $this->initRolesTable();
    }
    
    private function initRolesTable() {
        // Create roles table if it doesn't exist
        $this->conn->query("
            CREATE TABLE IF NOT EXISTS roles (
                id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(50) NOT NULL UNIQUE,
                level INT NOT NULL,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )
        ");
        
        // Create user_roles table if it doesn't exist
        $this->conn->query("
            CREATE TABLE IF NOT EXISTS user_roles (
                user_id INT NOT NULL,
                role_id INT NOT NULL,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (user_id, role_id),
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
                FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE
            )
        ");
        
        // Create permissions table if it doesn't exist
        $this->conn->query("
            CREATE TABLE IF NOT EXISTS permissions (
                id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(50) NOT NULL UNIQUE,
                description TEXT,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )
        ");
        
        // Create role_permissions table if it doesn't exist
        $this->conn->query("
            CREATE TABLE IF NOT EXISTS role_permissions (
                role_id INT NOT NULL,
                permission_id INT NOT NULL,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (role_id, permission_id),
                FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE,
                FOREIGN KEY (permission_id) REFERENCES permissions(id) ON DELETE CASCADE
            )
        ");
    }
    
    public function assignRole($userId, $roleName) {
        try {
            // Get role ID
            $stmt = $this->conn->prepare("
                SELECT id FROM roles WHERE name = ?
            ");
            $stmt->bind_param("s", $roleName);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows === 0) {
                throw new Exception("Role does not exist");
            }
            
            $roleId = $result->fetch_assoc()['id'];
            
            // Assign role to user
            $stmt = $this->conn->prepare("
                INSERT INTO user_roles (user_id, role_id) 
                VALUES (?, ?)
                ON DUPLICATE KEY UPDATE role_id = VALUES(role_id)
            ");
            $stmt->bind_param("ii", $userId, $roleId);
            return $stmt->execute();
            
        } catch (Exception $e) {
            error_log("Role assignment error: " . $e->getMessage());
            throw new Exception("Failed to assign role");
        }
    }
    
    public function hasPermission($userId, $permission) {
        try {
            $stmt = $this->conn->prepare("
                SELECT COUNT(*) as has_permission 
                FROM user_roles ur 
                JOIN role_permissions rp ON ur.role_id = rp.role_id 
                JOIN permissions p ON rp.permission_id = p.id 
                WHERE ur.user_id = ? AND p.name = ?
            ");
            $stmt->bind_param("is", $userId, $permission);
            $stmt->execute();
            
            return $stmt->get_result()->fetch_assoc()['has_permission'] > 0;
            
        } catch (Exception $e) {
            error_log("Permission check error: " . $e->getMessage());
            return false;
        }
    }
    
    public function getUserRoles($userId) {
        try {
            $stmt = $this->conn->prepare("
                SELECT r.name 
                FROM user_roles ur 
                JOIN roles r ON ur.role_id = r.id 
                WHERE ur.user_id = ?
            ");
            $stmt->bind_param("i", $userId);
            $stmt->execute();
            
            $roles = [];
            $result = $stmt->get_result();
            while ($row = $result->fetch_assoc()) {
                $roles[] = $row['name'];
            }
            
            return $roles;
            
        } catch (Exception $e) {
            error_log("Get user roles error: " . $e->getMessage());
            return [];
        }
    }
    
    public function addPermission($roleName, $permission) {
        try {
            // Get role ID
            $stmt = $this->conn->prepare("
                SELECT id FROM roles WHERE name = ?
            ");
            $stmt->bind_param("s", $roleName);
            $stmt->execute();
            $roleId = $stmt->get_result()->fetch_assoc()['id'];
            
            // Get permission ID
            $stmt = $this->conn->prepare("
                SELECT id FROM permissions WHERE name = ?
            ");
            $stmt->bind_param("s", $permission);
            $stmt->execute();
            $permissionId = $stmt->get_result()->fetch_assoc()['id'];
            
            // Add permission to role
            $stmt = $this->conn->prepare("
                INSERT IGNORE INTO role_permissions (role_id, permission_id) 
                VALUES (?, ?)
            ");
            $stmt->bind_param("ii", $roleId, $permissionId);
            return $stmt->execute();
            
        } catch (Exception $e) {
            error_log("Add permission error: " . $e->getMessage());
            throw new Exception("Failed to add permission");
        }
    }
}

