<?php
require_once '../config/database.php';

class ReportHandler {
    private $db;
    
    public function __construct() {
        global $conn;
        $this->db = $conn;
    }
    
    public function generateReport($type, $format, $dateFrom, $dateTo) {
        switch ($type) {
            case 'participants':
                return $this->generateParticipantsReport($format, $dateFrom, $dateTo);
            case 'payments':
                return $this->generatePaymentsReport($format, $dateFrom, $dateTo);
            case 'activities':
                return $this->generateActivitiesReport($format, $dateFrom, $dateTo);
            default:
                throw new Exception("Invalid report type");
        }
    }
    
    private function generateParticipantsReport($format, $dateFrom, $dateTo) {
        $sql = "SELECT 
                u.name, u.email, u.phone, u.registration_date,
                p.status as payment_status,
                a.status as attendance_status
                FROM users u
                LEFT JOIN payments p ON u.id = p.user_id
                LEFT JOIN attendance a ON u.id = a.user_id
                WHERE u.registration_date BETWEEN :date_from AND :date_to
                ORDER BY u.registration_date DESC";
                
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'date_from' => $dateFrom,
            'date_to' => $dateTo
        ]);
        
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $this->formatReport($data, $format, 'participants');
    }
    
    private function generatePaymentsReport($format, $dateFrom, $dateTo) {
        $sql = "SELECT 
                p.id as payment_id,
                u.name, u.email,
                p.amount, p.status, p.payment_date,
                p.payment_method
                FROM payments p
                JOIN users u ON p.user_id = u.id
                WHERE p.payment_date BETWEEN :date_from AND :date_to
                ORDER BY p.payment_date DESC";
                
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'date_from' => $dateFrom,
            'date_to' => $dateTo
        ]);
        
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $this->formatReport($data, $format, 'payments');
    }
    
    private function generateActivitiesReport($format, $dateFrom, $dateTo) {
        $sql = "SELECT 
                s.title as session_name,
                COUNT(a.id) as total_attendees,
                SUM(CASE WHEN a.status = 'present' THEN 1 ELSE 0 END) as present_count,
                s.start_time, s.end_time,
                sp.name as speaker_name
                FROM sessions s
                LEFT JOIN attendance a ON s.id = a.session_id
                LEFT JOIN speakers sp ON s.speaker_id = sp.id
                WHERE s.start_time BETWEEN :date_from AND :date_to
                GROUP BY s.id
                ORDER BY s.start_time DESC";
                
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'date_from' => $dateFrom,
            'date_to' => $dateTo
        ]);
        
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $this->formatReport($data, $format, 'activities');
    }
    
    private function formatReport($data, $format, $type) {
        $filename = $type . '_report_' . date('Y-m-d_H-i-s');
        
        switch ($format) {
            case 'pdf':
                return $this->generatePDF($data, $filename, $type);
            case 'csv':
                return $this->generateCSV($data, $filename);
            case 'excel':
                return $this->generateExcel($data, $filename);
            default:
                throw new Exception("Invalid format type");
        }
    }
    
    private function generatePDF($data, $filename, $type) {
        require_once '../vendor/tcpdf/tcpdf.php';
        
        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        $pdf->SetCreator('Education Summit');
        $pdf->SetAuthor('Admin');
        $pdf->SetTitle(ucfirst($type) . ' Report');
        
        $pdf->AddPage();
        
        // Add header
        $pdf->SetFont('helvetica', 'B', 16);
        $pdf->Cell(0, 10, ucfirst($type) . ' Report', 0, 1, 'C');
        $pdf->SetFont('helvetica', '', 10);
        
        // Add table headers
        $pdf->SetFillColor(240, 240, 240);
        $pdf->SetTextColor(0);
        $pdf->SetFont('', 'B');
        
        $headers = array_keys($data[0]);
        foreach ($headers as $header) {
            $pdf->Cell(40, 7, ucfirst(str_replace('_', ' ', $header)), 1, 0, 'C', 1);
        }
        $pdf->Ln();
        
        // Add data
        $pdf->SetFont('', '');
        foreach ($data as $row) {
            foreach ($row as $cell) {
                $pdf->Cell(40, 6, $cell, 1);
            }
            $pdf->Ln();
        }
        
        $filepath = '../uploads/reports/' . $filename . '.pdf';
        $pdf->Output($filepath, 'F');
        return $filename . '.pdf';
    }
    
    private function generateCSV($data, $filename) {
        $filepath = '../uploads/reports/' . $filename . '.csv';
        $fp = fopen($filepath, 'w');
        
        // Add headers
        fputcsv($fp, array_keys($data[0]));
        
        // Add data
        foreach ($data as $row) {
            fputcsv($fp, $row);
        }
        
        fclose($fp);
        return $filename . '.csv';
    }
    
    private function generateExcel($data, $filename) {
        require_once '../vendor/phpspreadsheet/phpspreadsheet.php';
        
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        // Add headers
        $headers = array_keys($data[0]);
        $col = 1;
        foreach ($headers as $header) {
            $sheet->setCellValueByColumnAndRow($col, 1, ucfirst(str_replace('_', ' ', $header)));
            $col++;
        }
        
        // Add data
        $row = 2;
        foreach ($data as $rowData) {
            $col = 1;
            foreach ($rowData as $cell) {
                $sheet->setCellValueByColumnAndRow($col, $row, $cell);
                $col++;
            }
            $row++;
        }
        
        $filepath = '../uploads/reports/' . $filename . '.xlsx';
        $writer = new Xlsx($spreadsheet);
        $writer->save($filepath);
        
        return $filename . '.xlsx';
    }
}

// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $handler = new ReportHandler();
        $report = $handler->generateReport(
            $_POST['type'],
            $_POST['format'],
            $_POST['date_from'],
            $_POST['date_to']
        );
        
        echo json_encode(['success' => true, 'filename' => $report]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
}
