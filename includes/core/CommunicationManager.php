<?php
namespace Summit\Core;

class CommunicationManager {
    private static $instance = null;
    private $db;
    private $cache;
    private $emailConfig;
    private $whatsappConfig;

    private function __construct() {
        $this->db = Database::getInstance();
        $this->cache = CacheManager::getInstance();
        $this->loadConfigurations();
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function loadConfigurations() {
        $config = include __DIR__ . '/../../config/communication.php';
        $this->emailConfig = $config['email'];
        $this->whatsappConfig = $config['whatsapp'];
    }

    // Email Communication
    public function sendEmail($to, $subject, $template, $data = []) {
        try {
            // Get template content
            $templateContent = $this->getEmailTemplate($template);
            
            // Replace placeholders with actual data
            $content = $this->parseTemplate($templateContent, $data);
            
            // Configure email headers
            $headers = [
                'MIME-Version: 1.0',
                'Content-type: text/html; charset=utf-8',
                'From: ' . $this->emailConfig['from_email'],
                'Reply-To: ' . $this->emailConfig['reply_to']
            ];

            // Send email using configured method
            switch ($this->emailConfig['driver']) {
                case 'smtp':
                    return $this->sendSmtpEmail($to, $subject, $content, $headers);
                case 'sendgrid':
                    return $this->sendSendgridEmail($to, $subject, $content);
                case 'mailgun':
                    return $this->sendMailgunEmail($to, $subject, $content);
                default:
                    return mail($to, $subject, $content, implode("\r\n", $headers));
            }
        } catch (\Exception $e) {
            ErrorHandler::logError("Email sending failed: " . $e->getMessage());
            throw $e;
        }
    }

    private function getEmailTemplate($template) {
        $templatePath = __DIR__ . "/../../resources/email_templates/{$template}.html";
        if (!file_exists($templatePath)) {
            throw new \Exception("Email template not found: {$template}");
        }
        return file_get_contents($templatePath);
    }

    private function parseTemplate($content, $data) {
        foreach ($data as $key => $value) {
            $content = str_replace("{{" . $key . "}}", $value, $content);
        }
        return $content;
    }

    private function sendSmtpEmail($to, $subject, $content, $headers) {
        $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
        
        try {
            $mail->isSMTP();
            $mail->Host = $this->emailConfig['smtp']['host'];
            $mail->SMTPAuth = true;
            $mail->Username = $this->emailConfig['smtp']['username'];
            $mail->Password = $this->emailConfig['smtp']['password'];
            $mail->SMTPSecure = \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = $this->emailConfig['smtp']['port'];

            $mail->setFrom($this->emailConfig['from_email']);
            $mail->addAddress($to);
            $mail->Subject = $subject;
            $mail->Body = $content;
            $mail->isHTML(true);

            return $mail->send();
        } catch (\Exception $e) {
            ErrorHandler::logError("SMTP email sending failed: " . $e->getMessage());
            throw $e;
        }
    }

    // WhatsApp Communication
    public function sendWhatsAppMessage($to, $template, $data = []) {
        try {
            // Format phone number
            $to = $this->formatPhoneNumber($to);
            
            // Get template content
            $message = $this->getWhatsAppTemplate($template, $data);
            
            // Send via WhatsApp Business API
            $response = $this->sendToWhatsApp($to, $message);
            
            // Log the communication
            $this->logCommunication('whatsapp', $to, $template, $response);
            
            return $response;
        } catch (\Exception $e) {
            ErrorHandler::logError("WhatsApp message sending failed: " . $e->getMessage());
            throw $e;
        }
    }

    private function formatPhoneNumber($number) {
        // Remove any non-numeric characters
        $number = preg_replace('/[^0-9]/', '', $number);
        
        // Add country code if not present
        if (substr($number, 0, 1) === '0') {
            $number = '234' . substr($number, 1);
        }
        
        return $number;
    }

    private function getWhatsAppTemplate($template, $data) {
        $templatePath = __DIR__ . "/../../resources/whatsapp_templates/{$template}.json";
        if (!file_exists($templatePath)) {
            throw new \Exception("WhatsApp template not found: {$template}");
        }
        
        $template = json_decode(file_get_contents($templatePath), true);
        
        // Replace placeholders in template
        $message = $template['message'];
        foreach ($data as $key => $value) {
            $message = str_replace("{{" . $key . "}}", $value, $message);
        }
        
        return [
            'template_name' => $template['name'],
            'language' => $template['language'],
            'components' => [
                [
                    'type' => 'body',
                    'parameters' => $this->formatTemplateParams($data)
                ]
            ]
        ];
    }

    private function formatTemplateParams($data) {
        $params = [];
        foreach ($data as $value) {
            $params[] = [
                'type' => 'text',
                'text' => $value
            ];
        }
        return $params;
    }

    private function sendToWhatsApp($to, $message) {
        $url = "https://graph.facebook.com/v12.0/{$this->whatsappConfig['phone_number_id']}/messages";
        
        $headers = [
            'Authorization: Bearer ' . $this->whatsappConfig['access_token'],
            'Content-Type: application/json'
        ];
        
        $data = [
            'messaging_product' => 'whatsapp',
            'to' => $to,
            'type' => 'template',
            'template' => $message
        ];
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode !== 200) {
            throw new \Exception("WhatsApp API request failed with code {$httpCode}: {$response}");
        }
        
        return json_decode($response, true);
    }

    private function logCommunication($type, $recipient, $template, $response) {
        try {
            $sql = "INSERT INTO communication_logs (type, recipient, template, response, created_at) 
                    VALUES (:type, :recipient, :template, :response, NOW())";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                'type' => $type,
                'recipient' => $recipient,
                'template' => $template,
                'response' => json_encode($response)
            ]);
        } catch (\Exception $e) {
            ErrorHandler::logError("Failed to log communication: " . $e->getMessage());
        }
    }

    // Bulk Communication
    public function sendBulkMessages($recipients, $template, $data = [], $channels = ['email', 'whatsapp']) {
        $queueManager = QueueManager::getInstance();
        
        foreach ($recipients as $recipient) {
            foreach ($channels as $channel) {
                $messageData = array_merge($data, [
                    'recipient' => $recipient,
                    'channel' => $channel,
                    'template' => $template
                ]);
                
                $queueManager->addToQueue('communication', 'send_message', $messageData);
            }
        }
    }

    // Communication Analytics
    public function getCommunicationAnalytics($startDate = null, $endDate = null) {
        try {
            $sql = "SELECT 
                    type,
                    COUNT(*) as total_sent,
                    SUM(CASE WHEN response LIKE '%success%' THEN 1 ELSE 0 END) as successful,
                    SUM(CASE WHEN response LIKE '%error%' OR response LIKE '%fail%' THEN 1 ELSE 0 END) as failed
                    FROM communication_logs
                    WHERE (:start_date IS NULL OR created_at >= :start_date)
                    AND (:end_date IS NULL OR created_at <= :end_date)
                    GROUP BY type";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                'start_date' => $startDate,
                'end_date' => $endDate
            ]);
            
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            ErrorHandler::logError("Failed to get communication analytics: " . $e->getMessage());
            throw $e;
        }
    }
}
