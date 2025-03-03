<?php
class BulkValidator {
    public static function validateIds($ids) {
        if (!is_array($ids)) {
            if (is_string($ids)) {
                $ids = explode(',', $ids);
            } else {
                return false;
            }
        }
        
        $ids = array_filter($ids, function($id) {
            return is_numeric($id) && $id > 0;
        });
        
        return !empty($ids) ? $ids : false;
    }
    
    public static function validateAction($action) {
        $validActions = ['approve', 'email', 'export', 'update_status'];
        return in_array($action, $validActions);
    }
    
    public static function validateEmailData($subject, $message) {
        $subject = trim($subject);
        $message = trim($message);
        
        if (empty($subject) || empty($message)) {
            return false;
        }
        
        if (strlen($subject) > 255) {
            return false;
        }
        
        // Basic HTML tag stripping for security
        $subject = strip_tags($subject);
        $message = strip_tags($message, '<p><br><strong><em><ul><li><ol>');
        
        return [
            'subject' => $subject,
            'message' => $message
        ];
    }
    
    public static function validateStatus($status) {
        $validStatuses = ['active', 'inactive', 'suspended'];
        return in_array($status, $validStatuses) ? $status : false;
    }
    
    public static function validateExportFormat($format) {
        $validFormats = ['pdf', 'csv', 'excel'];
        return in_array($format, $validFormats) ? $format : 'pdf';
    }
}

