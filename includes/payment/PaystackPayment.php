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

    public function __construct() {
        $config = Config::get('payment.paystack');
        $this->secretKey = $config['secret_key'];
        $this->publicKey = $config['public_key'];
        $this->db = Database::getInstance();
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
                $sql = "INSERT INTO payments (user_id, amount, reference, status) VALUES (:user_id, :amount, :reference, 'pending')";
                $stmt = $this->db->prepare($sql);
                $stmt->execute([
                    'user_id' => $_SESSION['user_id'],
                    'amount' => $amount,
                    'reference' => $reference
                ]);
                
                return $response['data'];
            }
            
            throw new \Exception("Payment initialization failed");
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
                $data = $response['data'];
                
                // Update payment status
                $sql = "UPDATE payments 
                        SET status = :status, 
                            payment_method = :method,
                            verified_at = NOW(),
                            updated_at = NOW() 
                        WHERE reference = :reference";
                
                $status = $data['status'] === 'success' ? 'completed' : 'failed';
                $stmt = $this->db->prepare($sql);
                $stmt->execute([
                    'status' => $status,
                    'method' => $data['channel'],
                    'reference' => $reference
                ]);
                
                if ($status === 'completed') {
                    $this->generateReceipt($reference);
                    $this->sendPaymentConfirmation($reference);
                }
                
                return $data;
            }
            
            throw new \Exception("Payment verification failed");
        } catch (\Exception $e) {
            ErrorHandler::logError("Paystack payment verification failed: " . $e->getMessage());
            throw $e;
        }
    }

    private function makeRequest($url, $method, $data = null) {
        $curl = curl_init();
        
        curl_setopt_array($curl, [
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
        ]);
        
        if ($data) {
            curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
        }
        
        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);
        
        if ($err) {
            throw new \Exception("cURL Error: " . $err);
        }
        
        return json_decode($response, true);
    }

    private function generateReceipt($reference) {
        $generator = new ReceiptGenerator();
        return $generator->generate($reference);
    }

    private function sendPaymentConfirmation($reference) {
        $mailer = new Mailer();
        return $mailer->sendPaymentConfirmation($reference);
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
