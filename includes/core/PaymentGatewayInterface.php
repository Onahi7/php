<?php
namespace Summit\Core;

interface PaymentGatewayInterface {
    /**
     * Initialize a payment transaction
     * 
     * @param string $email User's email
     * @param float $amount Payment amount
     * @param string|null $reference Optional payment reference
     * @return array Payment initialization data
     * @throws \Exception
     */
    public function initializePayment($email, $amount, $reference = null);

    /**
     * Verify a payment transaction
     * 
     * @param string $reference Payment reference to verify
     * @return array Payment verification data
     * @throws \Exception
     */
    public function verifyPayment($reference);

    /**
     * Get payment history for a user
     * 
     * @param int $userId User ID
     * @return array Payment history records
     * @throws \Exception
     */
    public function getPaymentHistory($userId);
}
