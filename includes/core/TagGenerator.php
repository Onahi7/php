<?php
namespace Summit\Core;

class TagGenerator {
    private static $instance = null;
    private $outputDir;

    private function __construct() {
        $this->outputDir = __DIR__ . '/../../uploads/tags/';
        if (!file_exists($this->outputDir)) {
            mkdir($this->outputDir, 0777, true);
        }
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function generateTag($userData) {
        try {
            // Generate unique barcode
            $barcode = $this->generateBarcode($userData['id']);
            
            // Create image
            $image = imagecreatetruecolor(400, 600);
            $white = imagecolorallocate($image, 255, 255, 255);
            $black = imagecolorallocate($image, 0, 0, 0);
            $blue = imagecolorallocate($image, 0, 114, 187);
            
            // Fill background
            imagefill($image, 0, 0, $white);
            
            // Add header
            $this->addText($image, 'EDUCATION SUMMIT 2025', 20, 50, $blue, 20);
            
            // Add participant info
            $this->addText($image, $userData['name'], 20, 120, $black, 16);
            $this->addText($image, $userData['role'] ?? 'Participant', 20, 150, $black, 14);
            
            // Add barcode image
            $barcodeImage = $this->createBarcodeImage($barcode);
            imagecopy($image, $barcodeImage, 50, 200, 0, 0, imagesx($barcodeImage), imagesy($barcodeImage));
            imagedestroy($barcodeImage);
            
            // Add barcode number
            $this->addText($image, $barcode, 20, 350, $black, 12);
            
            // Save image
            $filename = $this->outputDir . 'tag_' . $userData['id'] . '.png';
            imagepng($image, $filename);
            imagedestroy($image);
            
            // Update user record with barcode
            $this->updateUserBarcode($userData['id'], $barcode);
            
            return basename($filename);
        } catch (\Exception $e) {
            ErrorHandler::logError("Failed to generate tag: " . $e->getMessage());
            throw $e;
        }
    }

    private function generateBarcode($userId) {
        // Generate a unique 12-digit barcode
        $prefix = '25'; // Year prefix
        $padded = str_pad($userId, 10, '0', STR_PAD_LEFT);
        return $prefix . $padded;
    }

    private function createBarcodeImage($code) {
        // Using Code 128 barcode format
        $generator = new \Picqer\Barcode\BarcodeGeneratorPNG();
        $barcodeData = $generator->getBarcode($code, $generator::TYPE_CODE_128);
        
        // Convert to GD image
        $barcode = imagecreatefromstring($barcodeData);
        
        // Resize if needed
        $resized = imagecreatetruecolor(300, 100);
        imagecopyresampled($resized, $barcode, 0, 0, 0, 0, 300, 100, imagesx($barcode), imagesy($barcode));
        
        imagedestroy($barcode);
        return $resized;
    }

    private function addText($image, $text, $x, $y, $color, $size) {
        // Using default font
        $font = __DIR__ . '/../../assets/fonts/arial.ttf';
        imagettftext($image, $size, 0, $x, $y, $color, $font, $text);
    }

    private function updateUserBarcode($userId, $barcode) {
        $db = Database::getInstance();
        $sql = "UPDATE users SET barcode = :barcode WHERE id = :id";
        
        $stmt = $db->prepare($sql);
        $stmt->execute([
            'id' => $userId,
            'barcode' => $barcode
        ]);
    }
}
