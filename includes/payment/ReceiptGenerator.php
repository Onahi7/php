<?php
class ReceiptGenerator {
    private $conn;
    
    public function __construct($conn) {
        $this->conn = $conn;
    }
    
    public function generate($reference) {
        // Get payment details
        $stmt = $this->conn->prepare("
            SELECT p.*, u.name, u.email 
            FROM payments p 
            JOIN users u ON p.user_id = u.id 
            WHERE p.reference = ?
        ");
        
        $stmt->bind_param("s", $reference);
        $stmt->execute();
        $payment = $stmt->get_result()->fetch_assoc();
        
        if (!$payment) {
            throw new Exception("Payment not found");
        }
        
        // Generate PDF receipt
        require_once __DIR__ . '/../vendor/tcpdf/tcpdf.php';
        
        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        
        // Set document information
        $pdf->SetCreator(SITE_NAME);
        $pdf->SetAuthor(SITE_NAME);
        $pdf->SetTitle('Payment Receipt - ' . $reference);
        
        // Remove header/footer
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        
        // Add a page
        $pdf->AddPage();
        
        // Set font
        $pdf->SetFont('helvetica', '', 12);
        
        // Add content
        $html = $this->getReceiptTemplate($payment);
        $pdf->writeHTML($html, true, false, true, false, '');
        
        // Generate file name
        $fileName = 'receipt_' . $reference . '.pdf';
        $filePath = __DIR__ . '/../../uploads/receipts/' . $fileName;
        
        // Save file
        $pdf->Output($filePath, 'F');
        
        // Update payment record with receipt path
        $stmt = $this->conn->prepare("
            UPDATE payments 
            SET receipt_path = ? 
            WHERE reference = ?
        ");
        
        $stmt->bind_param("ss", $fileName, $reference);
        $stmt->execute();
        
        return $fileName;
    }
    
    private function getReceiptTemplate($payment) {
        return '
        <style>
            table { width: 100%; border-collapse: collapse; }
            th, td { padding: 8px; border: 1px solid #ddd; }
            th { background-color: #f8f9fa; }
        </style>
        <h1>' . SITE_NAME . ' - Payment Receipt</h1>
        <p>Reference: ' . $payment['reference'] . '</p>
        <p>Date: ' . date('F j, Y', strtotime($payment['created_at'])) . '</p>
        <p>Status: ' . ucfirst($payment['status']) . '</p>
        <br>
        <table>
            <tr>
                <th>Description</th>
                <th>Amount</th>
            </tr>
            <tr>
                <td>Summit Registration Fee</td>
                <td>₦' . number_format($payment['amount'], 2) . '</td>
            </tr>
        </table>
        <br>
        <p><strong>Paid By:</strong></p>
        <p>Name: ' . $payment['name'] . '</p>
        <p>Email: ' . $payment['email'] . '</p>
        ';
    }
}

