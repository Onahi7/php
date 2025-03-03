<?php
// View helper functions
function view($name, $data = []) {
    // Extract data to make variables available in view
    extract($data);
    
    // Set the view path
    $viewPath = __DIR__ . "/../views/{$name}.php";
    
    // Check if view exists
    if (!file_exists($viewPath)) {
        throw new Exception("View {$name} not found");
    }
    
    // Start output buffering
    ob_start();
    
    // Load the view
    require $viewPath;
    
    // Get the contents and clean the buffer
    $content = ob_get_clean();
    
    // Load the layout with the content
    require __DIR__ . '/../views/layouts/main.php';
}

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
    return CSRF::generateToken();
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
    
    $upload_path = __DIR__ . '/../uploads/' . trim($destination, '/');
    
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
