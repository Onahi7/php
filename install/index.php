<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

$steps = [
    1 => [
        'name' => 'System Check',
        'file' => '../hostinger_check.php',
        'description' => 'Check if your server meets all requirements'
    ],
    2 => [
        'name' => 'Database Setup',
        'file' => 'setup.php',
        'description' => 'Set up database and initial configuration'
    ],
    3 => [
        'name' => 'Create Admin',
        'file' => 'create-admin.php',
        'description' => 'Create your administrator account'
    ]
];

$currentStep = isset($_GET['step']) ? (int)$_GET['step'] : 1;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Summit Installation</title>
    <style>
        body { 
            font-family: Arial, sans-serif; 
            line-height: 1.6; 
            margin: 0;
            padding: 20px;
            background: #f5f5f5;
        }
        .container { 
            max-width: 800px; 
            margin: 0 auto;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .steps {
            display: flex;
            margin-bottom: 30px;
            border-bottom: 2px solid #eee;
            padding-bottom: 20px;
        }
        .step {
            flex: 1;
            text-align: center;
            position: relative;
        }
        .step-number {
            width: 30px;
            height: 30px;
            background: #2563eb;
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 10px;
        }
        .current .step-number {
            background: #059669;
        }
        .completed .step-number {
            background: #059669;
        }
        .step-name {
            font-size: 14px;
            color: #666;
        }
        .current .step-name {
            color: #059669;
            font-weight: bold;
        }
        .step-content {
            background: white;
            padding: 20px;
            border-radius: 4px;
        }
        .btn {
            display: inline-block;
            background: #2563eb;
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 4px;
            margin: 10px 0;
        }
        .btn:hover {
            background: #1d4ed8;
        }
        .success { color: #059669; }
        .error { color: #dc2626; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Summit Installation</h1>
        
        <div class="steps">
            <?php foreach ($steps as $stepNum => $step): ?>
                <div class="step <?php 
                    echo $stepNum === $currentStep ? 'current' : '';
                    echo $stepNum < $currentStep ? 'completed' : '';
                ?>">
                    <div class="step-number"><?php echo $stepNum; ?></div>
                    <div class="step-name"><?php echo htmlspecialchars($step['name']); ?></div>
                </div>
            <?php endforeach; ?>
        </div>
        
        <div class="step-content">
            <h2><?php echo htmlspecialchars($steps[$currentStep]['name']); ?></h2>
            <p><?php echo htmlspecialchars($steps[$currentStep]['description']); ?></p>
            
            <a href="<?php echo htmlspecialchars($steps[$currentStep]['file']); ?>" class="btn">
                Start <?php echo htmlspecialchars($steps[$currentStep]['name']); ?>
            </a>
            
            <?php if ($currentStep > 1): ?>
                <a href="?step=<?php echo $currentStep - 1; ?>" class="btn" style="background: #6b7280;">
                    Previous Step
                </a>
            <?php endif; ?>
            
            <?php if ($currentStep < count($steps)): ?>
                <a href="?step=<?php echo $currentStep + 1; ?>" class="btn">
                    Next Step
                </a>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
