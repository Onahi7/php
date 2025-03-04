<?php
namespace Summit\Core;

use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\RoundBlockSizeMode;
use Summit\Core\Config;

class QRCodeManager {
    private static $instance = null;
    private $uploadPath;

    private function __construct() {
        $this->uploadPath = Config::get('app.upload_path') . '/qrcodes';
        if (!file_exists($this->uploadPath)) {
            mkdir($this->uploadPath, 0777, true);
        }
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Generate a QR code for a participant
     * 
     * @param array $participant Participant data
     * @return string Path to the generated QR code
     */
    public function generateParticipantQR($participant) {
        try {
            // Create unique identifier
            $identifier = $this->generateIdentifier($participant);
            
            // Create QR code
            $qrCode = new QrCode($identifier);
            $qrCode->setSize(300);
            $qrCode->setMargin(10);
            $qrCode->setErrorCorrectionLevel(ErrorCorrectionLevel::High);
            $qrCode->setRoundBlockSizeMode(RoundBlockSizeMode::Margin);
            $qrCode->setForegroundColor(['r' => 0, 'g' => 0, 'b' => 0]);
            $qrCode->setBackgroundColor(['r' => 255, 'g' => 255, 'b' => 255]);
            
            // Generate file name
            $fileName = 'qr_' . $participant['id'] . '_' . time() . '.png';
            $filePath = $this->uploadPath . '/' . $fileName;
            
            // Write QR code to file
            $writer = new PngWriter();
            $result = $writer->write($qrCode);
            $result->saveToFile($filePath);
            
            // Update participant record with QR code path
            $db = Database::getInstance();
            $sql = "UPDATE users SET qr_code_path = :path WHERE id = :id";
            $stmt = $db->prepare($sql);
            $stmt->execute([
                'path' => $fileName,
                'id' => $participant['id']
            ]);
            
            return $fileName;
        } catch (\Exception $e) {
            ErrorHandler::logError("Failed to generate QR code: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Verify a QR code
     * 
     * @param string $identifier QR code content
     * @return array|false Participant data if valid, false otherwise
     */
    public function verifyQRCode($identifier) {
        try {
            // Decode identifier
            $data = $this->decodeIdentifier($identifier);
            if (!$data) {
                return false;
            }
            
            // Verify in database
            $db = Database::getInstance();
            $sql = "SELECT * FROM users WHERE id = :id AND registration_number = :reg_number";
            $stmt = $db->prepare($sql);
            $stmt->execute([
                'id' => $data['id'],
                'reg_number' => $data['reg_number']
            ]);
            
            return $stmt->fetch(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            ErrorHandler::logError("QR code verification failed: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Generate a secure identifier for QR code
     * 
     * @param array $participant Participant data
     * @return string Encoded identifier
     */
    private function generateIdentifier($participant) {
        $data = [
            'id' => $participant['id'],
            'reg_number' => $participant['registration_number'],
            'timestamp' => time()
        ];
        
        // Create a secure hash
        $hash = hash_hmac(
            'sha256',
            json_encode($data),
            Config::get('app.secret_key')
        );
        
        // Combine data with hash
        $identifier = base64_encode(json_encode([
            'data' => $data,
            'hash' => $hash
        ]));
        
        return $identifier;
    }

    /**
     * Decode and verify a QR code identifier
     * 
     * @param string $identifier Encoded identifier
     * @return array|false Decoded data if valid, false otherwise
     */
    private function decodeIdentifier($identifier) {
        try {
            // Decode base64
            $decoded = base64_decode($identifier);
            if (!$decoded) {
                return false;
            }
            
            // Parse JSON
            $data = json_decode($decoded, true);
            if (!isset($data['data']) || !isset($data['hash'])) {
                return false;
            }
            
            // Verify hash
            $expectedHash = hash_hmac(
                'sha256',
                json_encode($data['data']),
                Config::get('app.secret_key')
            );
            
            if (!hash_equals($expectedHash, $data['hash'])) {
                return false;
            }
            
            return $data['data'];
        } catch (\Exception $e) {
            ErrorHandler::logError("Failed to decode QR identifier: " . $e->getMessage());
            return false;
        }
    }
}
