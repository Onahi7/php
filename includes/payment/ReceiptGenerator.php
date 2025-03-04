<?php
namespace Summit\Payment;

use Summit\Core\Database;
use Summit\Core\Config;
use TCPDF;

class ReceiptGenerator {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    public function generate($reference) {
        // Get payment details
        $sql = "SELECT p.*, u.name, u.email 
                FROM payments p 
                JOIN users u ON p.user_id = u.id 
                WHERE p.reference = :reference";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['reference' => $reference]);
        $payment = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        if (!$payment) {
            throw new \Exception("Payment not found");
        }
        
        // Generate PDF receipt
        require_once Config::get('app.vendor_path') . '/tcpdf/tcpdf.php';
        
        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        
        // Set document information
        $pdf->SetCreator(Config::get('app.name'));
        $pdf->SetAuthor(Config::get('app.name'));
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
        $filePath = Config::get('app.upload_path') . '/receipts/' . $fileName;
        
        // Create receipts directory if it doesn't exist
        if (!file_exists(dirname($filePath))) {
            mkdir(dirname($filePath), 0777, true);
        }
        
        // Save file
        $pdf->Output($filePath, 'F');
        
        // Update payment record with receipt path
        $sql = "UPDATE payments 
                SET receipt_path = :receipt_path,
                    updated_at = NOW() 
                WHERE reference = :reference";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'receipt_path' => $fileName,
            'reference' => $reference
        ]);
        
        return $fileName;
    }
    
    private function getReceiptTemplate($payment) {
        $siteName = Config::get('app.name');
        return '
        <style>
            table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
            th, td { padding: 12px; border: 1px solid #ddd; }
            th { background-color: #f8f9fa; }
            .receipt-header { text-align: center; margin-bottom: 30px; }
            .receipt-logo { max-width: 200px; margin-bottom: 20px; }
            .payment-details { margin: 20px 0; }
            .customer-details { margin-top: 30px; }
        </style>
        <div class="receipt-header">
            <h1>' . $siteName . ' - Payment Receipt</h1>
        </div>
        <div class="payment-details">
            <p><strong>Reference:</strong> ' . htmlspecialchars($payment['reference']) . '</p>
            <p><strong>Date:</strong> ' . date('F j, Y', strtotime($payment['created_at'])) . '</p>
            <p><strong>Status:</strong> ' . ucfirst(htmlspecialchars($payment['status'])) . '</p>
        </div>
        <table>
            <tr>
                <th>Description</th>
                <th>Amount</th>
            </tr>
            <tr>
                <td>Summit Registration Fee</td>
                <td>₦' . number_format($payment['amount'], 2) . '</td>
            </tr>
            <tr>
                <th>Total</th>
                <td><strong>₦' . number_format($payment['amount'], 2) . '</strong></td>
            </tr>
        </table>
        <div class="customer-details">
            <p><strong>Paid By:</strong></p>
            <p>Name: ' . htmlspecialchars($payment['name']) . '</p>
            <p>Email: ' . htmlspecialchars($payment['email']) . '</p>
        </div>
        <div style="margin-top: 40px; font-size: 10px; text-align: center;">
            <p>This is a computer-generated receipt and does not require a signature.</p>
        </div>
        ';
    }
}
