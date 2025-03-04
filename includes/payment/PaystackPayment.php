<?php
namespace Summit\Payment;

use Summit\Core\Database;
use Summit\Core\ErrorHandler;
use Summit\Core\Config;
use Summit\Core\PaymentGatewayInterface;
use Summit\Mailer\Mailer;

class PaystackPayment implements PaymentGatewayInterface {
    private $secretKey;
    private $publicKey;
    private $db;
    private $mailer;

    public function __construct() {
        $config = Config::get('payment.paystack');
        $this->secretKey = $config['secret_key'];
        $this->publicKey = $config['public_key'];
        $this->db = Database::getInstance();
        $this->mailer = new Mailer();
    }

    public function initializePayment($email, $amount, $reference = null) {
        try {
            $reference = $reference ?? 'SUMMIT_' . time() . '_' . uniqid();
            
            $url = "https://api.paystack.co/transaction/initialize";
            $fields = [
                'email' => $email,
                'amount' => $amount * 100, // Convert to kobo
                'reference' => $reference,
                'callback_url' => Config::get('app.url') . '/payment/callback',
                'metadata' => [
                    'custom_fields' => [
                        [
                            'display_name' => "Event",
                            'variable_name' => "event",
                            'value' => Config::get('app.name')
                        ]
                    ]
                ]
            ];

            $response = $this->makeRequest($url, 'POST', $fields);
            
            if ($response && isset($response['status']) && $response['status']) {
                // Save payment attempt
                $sql = "INSERT INTO payments (user_id, amount, reference, status, created_at, updated_at) 
                        VALUES (:user_id, :amount, :reference, 'pending', NOW(), NOW())";
                $stmt = $this->db->prepare($sql);
                $stmt->execute([
                    'user_id' => $_SESSION['user_id'],
                    'amount' => $amount,
                    'reference' => $reference
                ]);
                
                return $response['data'];
            }
            
            throw new \Exception("Payment initialization failed: " . ($response['message'] ?? 'Unknown error'));
        } catch (\Exception $e) {
            ErrorHandler::logError("Paystack payment initialization failed: " . $e->getMessage());
            throw $e;
        }
    }

    public function verifyPayment($reference) {
        try {
            $url = "https://api.paystack.co/transaction/verify/" . urlencode($reference);
            $response = $this->makeRequest($url, 'GET');
            
            if ($response && isset($response['status']) && $response['status']) {
                return $this->processVerificationResponse($response['data']);
            }
            
            throw new \Exception("Payment verification failed: " . ($response['message'] ?? 'Unknown error'));
        } catch (\Exception $e) {
            ErrorHandler::logError("Paystack payment verification failed: " . $e->getMessage());
            throw $e;
        }
    }

    public function handleWebhook($payload) {
        try {
            if (!$this->validateWebhookSignature()) {
                throw new \Exception("Invalid webhook signature");
            }

            if ($payload['event'] === 'charge.success') {
                return $this->processVerificationResponse($payload['data']);
            }

            return true;
        } catch (\Exception $e) {
            ErrorHandler::logError("Paystack webhook processing failed: " . $e->getMessage());
            throw $e;
        }
    }

    private function processVerificationResponse($data) {
        $this->db->beginTransaction();
        
        try {
            // Update payment status
            $sql = "UPDATE payments 
                    SET status = :status, 
                        payment_method = :method,
                        card_type = :card_type,
                        last_four = :last_four,
                        verified_at = NOW(),
                        updated_at = NOW() 
                    WHERE reference = :reference";
            
            $status = $data['status'] === 'success' ? 'completed' : 'failed';
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                'status' => $status,
                'method' => $data['channel'],
                'card_type' => $data['authorization']['card_type'] ?? null,
                'last_four' => $data['authorization']['last4'] ?? null,
                'reference' => $data['reference']
            ]);
            
            if ($status === 'completed') {
                // Generate receipt
                $receiptGenerator = new ReceiptGenerator();
                $receiptPath = $receiptGenerator->generate($data['reference']);
                
                // Send confirmation email
                $this->sendPaymentConfirmation($data['reference']);
                
                // Update user status
                $sql = "UPDATE users SET payment_status = 'paid', updated_at = NOW() WHERE id = (
                        SELECT user_id FROM payments WHERE reference = :reference
                    )";
                $stmt = $this->db->prepare($sql);
                $stmt->execute(['reference' => $data['reference']]);
            }
            
            $this->db->commit();
            return $data;
        } catch (\Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    private function validateWebhookSignature() {
        if (!isset($_SERVER['HTTP_X_PAYSTACK_SIGNATURE'])) {
            return false;
        }

        $input = file_get_contents('php://input');
        $signature = $_SERVER['HTTP_X_PAYSTACK_SIGNATURE'];
        
        return hash_equals(
            $signature,
            hash_hmac('sha512', $input, $this->secretKey)
        );
    }

    private function makeRequest($url, $method, $data = null) {
        $curl = curl_init();
        
        $options = [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_HTTPHEADER => [
                "Authorization: Bearer " . $this->secretKey,
                "Cache-Control: no-cache",
                "Content-Type: application/json"
            ],
            CURLOPT_SSL_VERIFYPEER => true
        ];
        
        if ($data) {
            $options[CURLOPT_POSTFIELDS] = json_encode($data);
        }
        
        curl_setopt_array($curl, $options);
        
        $response = curl_exec($curl);
        $err = curl_error($curl);
        
        if ($err) {
            curl_close($curl);
            throw new \Exception("cURL Error: " . $err);
        }
        
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);
        
        if ($httpCode >= 400) {
            throw new \Exception("HTTP Error: " . $httpCode);
        }
        
        return json_decode($response, true);
    }

    private function sendPaymentConfirmation($reference) {
        try {
            $sql = "SELECT p.*, u.email, u.name 
                    FROM payments p 
                    JOIN users u ON p.user_id = u.id 
                    WHERE p.reference = :reference";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['reference' => $reference]);
            $payment = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            if (!$payment) {
                throw new \Exception("Payment not found");
            }
            
            return $this->mailer->sendPaymentConfirmation(
                $payment['email'],
                [
                    'name' => $payment['name'],
                    'amount' => '₦' . number_format($payment['amount'], 2),
                    'reference' => $payment['reference'],
                    'receipt_path' => $payment['receipt_path']
                ]
            );
        } catch (\Exception $e) {
            ErrorHandler::logError("Failed to send payment confirmation: " . $e->getMessage());
            throw $e;
        }
    }

    public function getPaymentHistory($userId) {
        try {
            $sql = "SELECT p.*, u.email, u.name 
                    FROM payments p 
                    JOIN users u ON p.user_id = u.id 
                    WHERE p.user_id = :user_id 
                    ORDER BY p.created_at DESC";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['user_id' => $userId]);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            ErrorHandler::logError("Failed to get payment history: " . $e->getMessage());
            throw $e;
        }
    }
}
