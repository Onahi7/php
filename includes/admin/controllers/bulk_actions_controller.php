<?php
require_once __DIR__ . '/../bulk_actions.php';
require_once __DIR__ . '/../helpers/bulk_validator.php';

class BulkActionsController {
    private $bulkActions;
    
    public function __construct($conn) {
        $this->bulkActions = new BulkActions($conn);
    }
    
    public function handle() {
        try {
            if (!isset($_POST['csrf_token']) || !CSRF::validateToken($_POST['csrf_token'])) {
                throw new Exception("Invalid CSRF token");
            }
            
            $ids = BulkValidator::validateIds($_POST['selected_ids'] ?? null);
            if (!$ids) {
                throw new Exception("No valid IDs provided");
            }
            
            $action = $_POST['action'] ?? '';
            if (!BulkValidator::validateAction($action)) {
                throw new Exception("Invalid action");
            }
            
            $data = [];
            switch ($action) {
                case 'email':
                    $emailData = BulkValidator::validateEmailData(
                        $_POST['subject'] ?? '',
                        $_POST['message'] ?? ''
                    );
                    if (!$emailData) {
                        throw new Exception("Invalid email data");
                    }
                    $data = $emailData;
                    break;
                    
                case 'update_status':
                    $status = BulkValidator::validateStatus($_POST['status'] ?? '');
                    if (!$status) {
                        throw new Exception("Invalid status");
                    }
                    $data['status'] = $status;
                    break;
                    
                case 'export':
                    $data['format'] = BulkValidator::validateExportFormat($_POST['format'] ?? 'pdf');
                    break;
            }
            
            $result = $this->bulkActions->processAction($action, $ids, $data);
            
            return [
                'success' => true,
                'message' => 'Bulk action processed successfully',
                'data' => $result
            ];
            
        } catch (Exception $e) {
            error_log("Bulk action error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
}

