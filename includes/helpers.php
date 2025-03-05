<?php
// Helper functions for Summit application

// Redirect helper
function redirect($path) {
    header('Location: ' . BASE_PATH . $path);
    exit;
}

// Flash message helper
function flash($message, $type = 'success') {
    $_SESSION['flash'] = [
        'message' => $message,
        'type' => $type
    ];
}

// Asset helper
function asset($path) {
    return BASE_PATH . '/assets/' . ltrim($path, '/');
}

// Format date helper
function format_date($date, $format = 'Y-m-d H:i:s') {
    return date($format, strtotime($date));
}

// Format currency helper
function format_currency($amount) {
    return '₦' . number_format($amount, 2);
}

// Generate random string
function generate_random_string($length = 10) {
    return bin2hex(random_bytes($length));
}

// Generate CSRF token
function generate_csrf_token() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// Validate CSRF token
function validate_csrf_token($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// Validate file upload
function validate_file_upload($file, $allowed_types = ['jpg', 'jpeg', 'png'], $max_size = 5242880) {
    if ($file['error'] !== UPLOAD_ERR_OK) {
        throw new Exception("File upload failed");
    }
    
    $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($file_extension, $allowed_types)) {
        throw new Exception("Invalid file type");
    }
    
    if ($file['size'] > $max_size) {
        throw new Exception("File size too large");
    }
    
    return true;
}

// Handle file upload
function handle_file_upload($file, $destination, $filename = null) {
    if ($filename === null) {
        $filename = generate_random_string() . '.' . 
                   strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    }
    
    $upload_path = __DIR__ . '/../storage/uploads/' . trim($destination, '/');
    
    if (!file_exists($upload_path)) {
        mkdir($upload_path, 0755, true);
    }
    
    $file_destination = $upload_path . '/' . $filename;
    
    if (!move_uploaded_file($file['tmp_name'], $file_destination)) {
        throw new Exception("Failed to move uploaded file");
    }
    
    return $filename;
}

// Pagination helper
function paginate($total, $per_page, $current_page = 1) {
    $total_pages = ceil($total / $per_page);
    $current_page = max(1, min($current_page, $total_pages));
    
    return [
        'current_page' => $current_page,
        'per_page' => $per_page,
        'total' => $total,
        'total_pages' => $total_pages,
        'offset' => ($current_page - 1) * $per_page
    ];
}

// Log activity
function log_activity($user_id, $action, $description = '') {
    global $conn;
    
    try {
        $stmt = $conn->prepare("
            INSERT INTO activity_logs 
            (user_id, action, description, ip_address, user_agent, created_at) 
            VALUES (?, ?, ?, ?, ?, NOW())
        ");
        
        $stmt->execute([
            $user_id,
            $action,
            $description,
            $_SERVER['REMOTE_ADDR'] ?? null,
            $_SERVER['HTTP_USER_AGENT'] ?? null
        ]);
        
        return true;
    } catch (Exception $e) {
        error_log("Activity logging failed: " . $e->getMessage());
        return false;
    }
}

// Clean input
function clean_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}
