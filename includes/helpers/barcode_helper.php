<?php
require_once __DIR__ . '/../../vendor/autoload.php';

use Picqer\Barcode\BarcodeGeneratorPNG;
use Picqer\Barcode\BarcodeGeneratorSVG;

class BarcodeHelper {
    private $generator;
    private $svgGenerator;

    public function __construct() {
        $this->generator = new BarcodeGeneratorPNG();
        $this->svgGenerator = new BarcodeGeneratorSVG();
    }

    public function generateParticipantBarcode($userId) {
        // Generate a unique barcode number (prefix 'PS' for participant summit)
        $barcodeNumber = 'PS' . str_pad($userId, 8, '0', STR_PAD_LEFT);
        
        // Generate PNG barcode
        $barcode = $this->generator->getBarcode($barcodeNumber, $this->generator::TYPE_CODE_128);
        
        return [
            'barcode_number' => $barcodeNumber,
            'barcode_image' => base64_encode($barcode)
        ];
    }

    public function generateParticipantTag($user, $barcodeNumber) {
        // Generate SVG barcode for better print quality
        $barcodeSvg = $this->svgGenerator->getBarcode($barcodeNumber, $this->svgGenerator::TYPE_CODE_128);
        
        // Generate HTML for the tag
        $html = '
        <div class="participant-tag" style="width: 3.5in; height: 2in; border: 1px solid #ccc; padding: 0.2in; font-family: Arial, sans-serif;">
            <div style="text-align: center; margin-bottom: 10px;">
                <h2 style="margin: 0; color: #2563eb;">' . SITE_NAME . '</h2>
                <p style="margin: 5px 0; color: #666;">March 25th, 2025</p>
            </div>
            <div style="display: flex; justify-content: space-between; margin-top: 15px;">
                <div>
                    <h3 style="margin: 0; font-size: 16px;">' . htmlspecialchars($user['name']) . '</h3>
                    <p style="margin: 5px 0; color: #666; font-size: 14px;">' . htmlspecialchars($user['organization'] ?? '') . '</p>
                </div>
                <div style="text-align: right;">
                    <div style="margin-bottom: 5px;">' . $barcodeSvg . '</div>
                    <small style="font-size: 10px;">' . $barcodeNumber . '</small>
                </div>
            </div>
        </div>';
        
        return $html;
    }
}
