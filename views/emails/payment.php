<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Confirmation - <?= Config::get('app.name') ?></title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0; background-color: #f9fafb;">
    <div style="max-width: 600px; margin: 0 auto; background-color: #ffffff; padding: 40px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);">
        <div style="text-align: center; margin-bottom: 30px;">
            <img src="<?= Config::get('app.url') ?>/assets/images/logo.png" alt="Logo" style="max-width: 200px;">
        </div>

        <h1 style="color: #2563eb; text-align: center; margin-bottom: 30px;">Payment Confirmation</h1>
        
        <p style="margin-bottom: 20px;">Dear <?= htmlspecialchars($data['name']) ?>,</p>
        
        <p style="margin-bottom: 20px;">Thank you for your payment. Your registration for the North Central Education Summit 2025 has been confirmed.</p>
        
        <div style="background: #f8fafc; padding: 20px; border-radius: 5px; margin: 20px 0;">
            <p style="font-weight: bold; margin-bottom: 15px;">Payment Details:</p>
            <table style="width: 100%; border-collapse: collapse;">
                <tr>
                    <td style="padding: 8px 0;">Amount:</td>
                    <td style="padding: 8px 0; text-align: right;"><?= htmlspecialchars($data['amount']) ?></td>
                </tr>
                <tr>
                    <td style="padding: 8px 0;">Reference:</td>
                    <td style="padding: 8px 0; text-align: right;"><?= htmlspecialchars($data['reference']) ?></td>
                </tr>
                <tr>
                    <td style="padding: 8px 0;">Date:</td>
                    <td style="padding: 8px 0; text-align: right;"><?= date('F j, Y') ?></td>
                </tr>
            </table>
        </div>
        
        <?php if (isset($data['receipt_path'])): ?>
        <div style="text-align: center; margin: 30px 0;">
            <a href="<?= Config::get('app.url') ?>/uploads/receipts/<?= htmlspecialchars($data['receipt_path']) ?>" 
               style="display: inline-block; background-color: #2563eb; color: #ffffff; padding: 12px 24px; text-decoration: none; border-radius: 5px; font-weight: bold;">
                Download Receipt
            </a>
        </div>
        <?php endif; ?>
        
        <div style="margin: 30px 0; padding: 20px; background-color: #f0f9ff; border-radius: 5px;">
            <h2 style="color: #2563eb; margin-bottom: 15px; font-size: 18px;">Next Steps:</h2>
            <ol style="margin: 0; padding-left: 20px;">
                <li style="margin-bottom: 10px;">Save this email for your records</li>
                <li style="margin-bottom: 10px;">Watch for updates about the summit schedule</li>
                <li style="margin-bottom: 10px;">Download your registration badge (available closer to the event)</li>
            </ol>
        </div>
        
        <p style="margin-bottom: 20px;">If you have any questions or concerns, please don't hesitate to contact our support team:</p>
        <ul style="list-style: none; padding: 0; margin-bottom: 30px;">
            <li>Email: <?= Config::get('support.email') ?></li>
            <li>Phone: <?= Config::get('support.phone') ?></li>
        </ul>
        
        <p style="margin-bottom: 20px;">Best regards,<br>The Summit Team</p>
        
        <div style="margin-top: 40px; padding-top: 20px; border-top: 1px solid #e5e7eb; font-size: 12px; color: #6b7280; text-align: center;">
            <p style="margin-bottom: 10px;">This is an automated message, please do not reply to this email.</p>
            <p style="margin: 0;">&copy; <?= date('Y') ?> <?= Config::get('app.name') ?>. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
