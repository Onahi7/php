<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class Mailer {
    private $mail;

    public function __construct() {
        $this->mail = new PHPMailer(true);
        
        $this->mail->isSMTP();
        $this->mail->Host = SMTP_HOST;
        $this->mail->SMTPAuth = true;
        $this->mail->Username = SMTP_USER;
        $this->mail->Password = SMTP_PASS;
        $this->mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $this->mail->Port = SMTP_PORT;
        
        $this->mail->isHTML(true);
        $this->mail->setFrom(SMTP_FROM, SITE_NAME);
    }

    public function sendWelcomeEmail($email, $name) {
        try {
            $this->mail->addAddress($email, $name);
            $this->mail->Subject = "Welcome to " . SITE_NAME;
            
            $body = file_get_contents(__DIR__ . '/../views/emails/welcome.php');
            $body = str_replace('{name}', $name, $body);
            $this->mail->Body = $body;
            
            return $this->mail->send();
        } catch (Exception $e) {
            error_log("Email error: " . $e->getMessage());
            return false;
        }
    }

    public function sendPaymentConfirmation($email, $name, $amount, $reference) {
        try {
            $this->mail->addAddress($email, $name);
            $this->mail->Subject = "Payment Confirmation - " . SITE_NAME;
            
            $body = file_get_contents(__DIR__ . '/../views/emails/payment.php');
            $body = str_replace(
                ['{name}', '{amount}', '{reference}'], 
                [$name, format_currency($amount), $reference], 
                $body
            );
            $this->mail->Body = $body;
            
            return $this->mail->send();
        } catch (Exception $e) {
            error_log("Email error: " . $e->getMessage());
            return false;
        }
    }
}

