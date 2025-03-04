<?php
require_once '../core/Auth.php';
require_once '../core/Logger.php';
require_once '../core/CSRF.php';

// Ensure user is admin
if (!Auth::isAdmin()) {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

// Handle POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    
    // Verify CSRF token
    if (!isset($input['csrf_token']) || !CSRF::verifyToken($input['csrf_token'])) {
        http_response_code(403);
        echo json_encode(['error' => 'Invalid CSRF token']);
        exit;
    }
    
    $action = $input['action'] ?? '';
    
    switch ($action) {
        case 'clear_old_logs':
            try {
                $logger = Logger::getInstance();
                $logger->cleanOldLogs();
                echo json_encode(['success' => true]);
            } catch (Exception $e) {
                http_response_code(500);
                echo json_encode(['error' => $e->getMessage()]);
            }
            break;
            
        default:
            http_response_code(400);
            echo json_encode(['error' => 'Invalid action']);
    }
    exit;
}

// Handle GET requests
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $action = $_GET['action'] ?? '';
    
    switch ($action) {
        case 'download':
            try {
                $level = $_GET['level'] ?? 'error';
                $startDate = $_GET['start_date'] ?? date('Y-m-d', strtotime('-7 days'));
                $endDate = $_GET['end_date'] ?? date('Y-m-d');
                $limit = min((int)($_GET['limit'] ?? 1000), 1000);
                
                $logger = Logger::getInstance();
                $logs = $logger->getLogs($level, $startDate, $endDate, $limit);
                
                // Set headers for download
                header('Content-Type: text/plain');
                header('Content-Disposition: attachment; filename="logs_' . $level . '_' . date('Y-m-d') . '.txt"');
                
                // Output logs
                foreach ($logs as $log) {
                    echo $log;
                }
            } catch (Exception $e) {
                http_response_code(500);
                echo "Error downloading logs: " . $e->getMessage();
            }
            break;
            
        default:
            http_response_code(400);
            echo json_encode(['error' => 'Invalid action']);
    }
    exit;
}

// Invalid request method
http_response_code(405);
echo json_encode(['error' => 'Method not allowed']);
