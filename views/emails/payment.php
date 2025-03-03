<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Payment Confirmation - <?= SITE_NAME ?></title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
    <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
        <h1 style="color: #2563eb;">Payment Confirmation</h1>
        
        <p>Dear {name},</p>
        
        <p>Thank you for your payment. Your registration for the North Central Education Summit 2025 is now confirmed.</p>
        
        <div style="background: #f8fafc; padding: 20px; border-radius: 5px; margin: 20px 0;">
            <p><strong>Payment Details:</strong></p>
            <ul style="list-style: none; padding: 0;">
                <li>Amount: {amount}</li>
                <li>Reference: {reference}</li>
                <li>Date: <?= date('Y-m-d H:i:s') ?></li>
            </ul>
        </div>
        
        <p>Please keep this email for your records. Your registration badge will be available for download closer to the event date.</p>
        
        <p>If you have any questions, please don't hesitate to contact us.</p>
        
        <p>Best regards,<br>The Summit Team</p>
    </div>
</body>
</html>

