<?php
namespace Summit\Core;

class PaymentManager {
    private static $instance = null;
    private $db;
    private $cache;
    private $paymentGateway;

    private function __construct() {
        $this->db = Database::getInstance();
        $this->cache = CacheManager::getInstance();
        $this->initializePaymentGateway();
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function initializePaymentGateway() {
        $config = include __DIR__ . '/../../config/payment.php';
        
        switch ($config['gateway']) {
            case 'stripe':
                $this->paymentGateway = new StripeGateway($config['stripe']);
                break;
            case 'paystack':
                $this->paymentGateway = new PaystackGateway($config['paystack']);
                break;
            case 'flutterwave':
                $this->paymentGateway = new FlutterwaveGateway($config['flutterwave']);
                break;
            default:
                throw new \Exception("Unsupported payment gateway");
        }
    }

    public function processPayment($userId, $amount, $currency = 'NGN') {
        try {
            // Create payment record
            $paymentId = $this->createPaymentRecord($userId, $amount, $currency);
            
            // Process payment through gateway
            $response = $this->paymentGateway->processPayment([
                'amount' => $amount,
                'currency' => $currency,
                'payment_id' => $paymentId,
                'user_id' => $userId
            ]);
            
            // Update payment record
            $this->updatePaymentStatus($paymentId, $response);
            
            // Generate invoice
            if ($response['status'] === 'success') {
                $this->generateInvoice($paymentId);
            }
            
            return $response;
        } catch (\Exception $e) {
            ErrorHandler::logError("Payment processing failed: " . $e->getMessage());
            throw $e;
        }
    }

    private function createPaymentRecord($userId, $amount, $currency) {
        try {
            $sql = "INSERT INTO payments (user_id, amount, currency, status, created_at) 
                    VALUES (:user_id, :amount, :currency, 'pending', NOW())";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                'user_id' => $userId,
                'amount' => $amount,
                'currency' => $currency
            ]);
            
            return $this->db->lastInsertId();
        } catch (\Exception $e) {
            ErrorHandler::logError("Failed to create payment record: " . $e->getMessage());
            throw $e;
        }
    }

    private function updatePaymentStatus($paymentId, $response) {
        try {
            $sql = "UPDATE payments SET 
                    status = :status,
                    transaction_id = :transaction_id,
                    payment_method = :payment_method,
                    updated_at = NOW()
                    WHERE id = :id";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                'id' => $paymentId,
                'status' => $response['status'],
                'transaction_id' => $response['transaction_id'],
                'payment_method' => $response['payment_method']
            ]);
            
            $this->cache->delete("payment:{$paymentId}");
        } catch (\Exception $e) {
            ErrorHandler::logError("Failed to update payment status: " . $e->getMessage());
            throw $e;
        }
    }

    public function generateInvoice($paymentId) {
        try {
            // Get payment details
            $sql = "SELECT p.*, u.name, u.email, u.address 
                    FROM payments p 
                    JOIN users u ON p.user_id = u.id 
                    WHERE p.id = :id";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['id' => $paymentId]);
            $payment = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            // Generate invoice number
            $invoiceNumber = 'INV-' . date('Y') . str_pad($paymentId, 6, '0', STR_PAD_LEFT);
            
            // Create invoice record
            $sql = "INSERT INTO invoices (payment_id, invoice_number, created_at) 
                    VALUES (:payment_id, :invoice_number, NOW())";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                'payment_id' => $paymentId,
                'invoice_number' => $invoiceNumber
            ]);
            
            // Generate PDF invoice
            $invoice = new InvoiceGenerator($payment, $invoiceNumber);
            $pdfPath = $invoice->generate();
            
            // Send invoice to user
            $this->sendInvoiceEmail($payment['email'], $pdfPath);
            
            return $invoiceNumber;
        } catch (\Exception $e) {
            ErrorHandler::logError("Failed to generate invoice: " . $e->getMessage());
            throw $e;
        }
    }

    public function processRefund($paymentId, $amount = null, $reason = '') {
        try {
            // Get payment details
            $sql = "SELECT * FROM payments WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['id' => $paymentId]);
            $payment = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            if (!$payment) {
                throw new \Exception("Payment not found");
            }
            
            // Process refund through gateway
            $refundAmount = $amount ?? $payment['amount'];
            $response = $this->paymentGateway->processRefund([
                'payment_id' => $payment['transaction_id'],
                'amount' => $refundAmount,
                'reason' => $reason
            ]);
            
            // Record refund
            $this->recordRefund($paymentId, $refundAmount, $response);
            
            return $response;
        } catch (\Exception $e) {
            ErrorHandler::logError("Refund processing failed: " . $e->getMessage());
            throw $e;
        }
    }

    private function recordRefund($paymentId, $amount, $response) {
        try {
            $sql = "INSERT INTO refunds (payment_id, amount, status, refund_id, reason, created_at) 
                    VALUES (:payment_id, :amount, :status, :refund_id, :reason, NOW())";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                'payment_id' => $paymentId,
                'amount' => $amount,
                'status' => $response['status'],
                'refund_id' => $response['refund_id'],
                'reason' => $response['reason']
            ]);
            
            // Update payment status
            $sql = "UPDATE payments SET 
                    refunded_amount = COALESCE(refunded_amount, 0) + :amount,
                    status = CASE 
                        WHEN amount = COALESCE(refunded_amount, 0) + :amount THEN 'refunded'
                        ELSE 'partially_refunded'
                    END
                    WHERE id = :payment_id";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                'amount' => $amount,
                'payment_id' => $paymentId
            ]);
            
            $this->cache->delete("payment:{$paymentId}");
        } catch (\Exception $e) {
            ErrorHandler::logError("Failed to record refund: " . $e->getMessage());
            throw $e;
        }
    }

    public function getPaymentAnalytics($startDate = null, $endDate = null) {
        try {
            $sql = "SELECT 
                    COUNT(*) as total_transactions,
                    SUM(amount) as total_amount,
                    SUM(CASE WHEN status = 'success' THEN amount ELSE 0 END) as successful_amount,
                    SUM(CASE WHEN status = 'failed' THEN 1 ELSE 0 END) as failed_count,
                    AVG(amount) as average_amount,
                    SUM(refunded_amount) as total_refunded
                    FROM payments
                    WHERE (:start_date IS NULL OR created_at >= :start_date)
                    AND (:end_date IS NULL OR created_at <= :end_date)";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                'start_date' => $startDate,
                'end_date' => $endDate
            ]);
            
            return $stmt->fetch(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            ErrorHandler::logError("Failed to get payment analytics: " . $e->getMessage());
            throw $e;
        }
    }
}
