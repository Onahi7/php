<?php
require_once __DIR__ . '/../vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Writer\Csv;

class ReportGenerator {
    private $conn;
    private $allowedTypes = ['pdf', 'csv', 'excel'];
    
    public function __construct($conn) {
        $this->conn = $conn;
    }
    
    public function generateReport($type, $reportName, $filters = []) {
        if (!in_array($type, $this->allowedTypes)) {
            throw new Exception("Invalid report type");
        }
        
        $data = $this->getReportData($reportName, $filters);
        
        switch ($type) {
            case 'pdf':
                return $this->generatePDFReport($data, $reportName);
            case 'csv':
                return $this->generateCSVReport($data, $reportName);
            case 'excel':
                return $this->generateExcelReport($data, $reportName);
        }
    }
    
    private function getReportData($reportName, $filters) {
        try {
            switch ($reportName) {
                case 'participants':
                    return $this->getParticipantsReport($filters);
                case 'payments':
                    return $this->getPaymentsReport($filters);
                case 'activities':
                    return $this->getActivitiesReport($filters);
                default:
                    throw new Exception("Invalid report name: " . htmlspecialchars($reportName));
            }
        } catch (Exception $e) {
            error_log("Report generation error: " . $e->getMessage());
            throw $e;
        }
    }
    
    private function getParticipantsReport($filters) {
        $where = [];
        $params = [];
        $types = "";
        
        if (!empty($filters['status'])) {
            $where[] = "u.status = ?";
            $params[] = $filters['status'];
            $types .= "s";
        }
        
        if (!empty($filters['date_from'])) {
            $where[] = "u.created_at >= ?";
            $params[] = $filters['date_from'];
            $types .= "s";
        }
        
        if (!empty($filters['date_to'])) {
            $where[] = "u.created_at <= ?";
            $params[] = $filters['date_to'];
            $types .= "s";
        }
        
        $whereClause = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";
        
        $query = "
            SELECT 
                u.name,
                u.email,
                u.phone,
                u.status,
                p.organization,
                p.position,
                COALESCE(pay.status, 'unpaid') as payment_status,
                u.created_at as registration_date
            FROM users u
            LEFT JOIN profiles p ON u.id = p.user_id
            LEFT JOIN payments pay ON u.id = pay.user_id
            $whereClause
            ORDER BY u.created_at DESC
        ";
        
        $stmt = $this->conn->prepare($query);
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
    
    private function getPaymentsReport($filters) {
        $where = [];
        $params = [];
        $types = "";
        
        if (!empty($filters['status'])) {
            $where[] = "p.status = ?";
            $params[] = $filters['status'];
            $types .= "s";
        }
        
        if (!empty($filters['date_from'])) {
            $where[] = "p.created_at >= ?";
            $params[] = $filters['date_from'];
            $types .= "s";
        }
        
        if (!empty($filters['date_to'])) {
            $where[] = "p.created_at <= ?";
            $params[] = $filters['date_to'];
            $types .= "s";
        }
        
        $whereClause = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";
        
        $query = "
            SELECT 
                u.name,
                u.email,
                p.reference,
                p.amount,
                p.status,
                p.payment_method,
                p.created_at as payment_date
            FROM payments p
            JOIN users u ON p.user_id = u.id
            $whereClause
            ORDER BY p.created_at DESC
        ";
        
        $stmt = $this->conn->prepare($query);
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
    
    private function generatePDFReport($data, $reportName) {
        require_once __DIR__ . '/../vendor/tecnickcom/tcpdf/tcpdf.php';
        
        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        
        $pdf->SetCreator(SITE_NAME);
        $pdf->SetAuthor(SITE_NAME);
        $pdf->SetTitle(ucfirst($reportName) . ' Report');
        
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        
        $pdf->AddPage();
        
        $pdf->SetFont('helvetica', '', 12);
        
        // Add title
        $pdf->Cell(0, 10, ucfirst($reportName) . ' Report', 0, 1, 'C');
        $pdf->Ln(10);
        
        // Add table headers
        $headers = array_keys($data[0]);
        foreach ($headers as $header) {
            $pdf->Cell(40, 7, ucfirst(str_replace('_', ' ', $header)), 1);
        }
        $pdf->Ln();
        
        // Add data rows
        foreach ($data as $row) {
            foreach ($row as $cell) {
                $pdf->Cell(40, 6, $cell, 1);
            }
            $pdf->Ln();
        }
        
        $filename = $reportName . '_' . date('Y-m-d_H-i-s') . '.pdf';
        $pdf->Output(__DIR__ . '/../../uploads/reports/' . $filename, 'F');
        
        return $filename;
    }
    
    private function generateCSVReport($data, $reportName) {
        $filename = $reportName . '_' . date('Y-m-d_H-i-s') . '.csv';
        $filepath = __DIR__ . '/../../uploads/reports/' . $filename;
        
        $fp = fopen($filepath, 'w');
        
        // Add headers
        fputcsv($fp, array_keys($data[0]));
        
        // Add data rows
        foreach ($data as $row) {
            fputcsv($fp, $row);
        }
        
        fclose($fp);
        
        return $filename;
    }
    
    private function generateExcelReport($data, $reportName) {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        // Add headers
        $headers = array_keys($data[0]);
        $col = 1;
        foreach ($headers as $header) {
            $sheet->setCellValueByColumnAndRow($col, 1, ucfirst(str_replace('_', ' ', $header)));
            $col++;
        }
        
        // Add data rows
        $row = 2;
        foreach ($data as $rowData) {
            $col = 1;
            foreach ($rowData as $cell) {
                $sheet->setCellValueByColumnAndRow($col, $row, $cell);
                $col++;
            }
            $row++;
        }
        
        $filename = $reportName . '_' . date('Y-m-d_H-i-s') . '.xlsx';
        $filepath = __DIR__ . '/../../uploads/reports/' . $filename;
        
        $writer = new Xlsx($spreadsheet);
        $writer->save($filepath);
        
        return $filename;
    }
}

