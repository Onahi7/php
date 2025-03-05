<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

$steps = [
    1 => [
        'name' => 'System Check',
        'file' => 'hostinger_check.php',
        'description' => 'Check if your server meets the requirements'
    ],
    2 => [
        'name' => 'Database Setup',
        'file' => 'setup.php',
        'description' => 'Configure your database connection'
    ],
    3 => [
        'name' => 'Create Admin User',
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
    <title>Summit Setup</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-50 min-h-screen">
    <div class="container mx-auto px-4 py-12">
        <div class="max-w-3xl mx-auto bg-white rounded-lg shadow-md overflow-hidden">
            <div class="p-6 bg-blue-600 text-white">
                <h1 class="text-2xl font-bold">Summit Installation Wizard</h1>
                <p class="mt-2">Welcome to the Summit installation wizard. Follow the steps below to set up your application.</p>
            </div>
            
            <div class="p-6">
                <ol class="space-y-6">
                    <li class="flex items-start">
                        <div class="flex-shrink-0 flex items-center justify-center rounded-full bg-blue-600 text-white w-8 h-8 font-bold">1</div>
                        <div class="ml-4">
                            <h2 class="text-xl font-semibold">System Check</h2>
                            <p class="text-gray-600 mt-1">Check if your server meets the requirements</p>
                            <a href="<?php echo htmlspecialchars($steps[1]['file']); ?>" class="mt-3 inline-block bg-blue-600 hover:bg-blue-700 text-white py-2 px-4 rounded">Run System Check</a>
                        </div>
                    </li>
                    
                    <li class="flex items-start">
                        <div class="flex-shrink-0 flex items-center justify-center rounded-full bg-blue-600 text-white w-8 h-8 font-bold">2</div>
                        <div class="ml-4">
                            <h2 class="text-xl font-semibold">Database Setup</h2>
                            <p class="text-gray-600 mt-1">Configure your database connection</p>
                            <a href="<?php echo htmlspecialchars($steps[2]['file']); ?>" class="mt-3 inline-block bg-blue-600 hover:bg-blue-700 text-white py-2 px-4 rounded">Setup Database</a>
                        </div>
                    </li>
                    
                    <li class="flex items-start">
                        <div class="flex-shrink-0 flex items-center justify-center rounded-full bg-blue-600 text-white w-8 h-8 font-bold">3</div>
                        <div class="ml-4">
                            <h2 class="text-xl font-semibold">Create Admin User</h2>
                            <p class="text-gray-600 mt-1">Create your administrator account</p>
                            <a href="<?php echo htmlspecialchars($steps[3]['file']); ?>" class="mt-3 inline-block bg-blue-600 hover:bg-blue-700 text-white py-2 px-4 rounded">Create Admin</a>
                        </div>
                    </li>
                    
                    <li class="flex items-start">
                        <div class="flex-shrink-0 flex items-center justify-center rounded-full bg-green-600 text-white w-8 h-8 font-bold">✓</div>
                        <div class="ml-4">
                            <h2 class="text-xl font-semibold">Finish Installation</h2>
                            <p class="text-gray-600 mt-1">Complete the installation and go to your site</p>
                            <a href="../index.php" class="mt-3 inline-block bg-green-600 hover:bg-green-700 text-white py-2 px-4 rounded">Finish Installation</a>
                        </div>
                    </li>
                </ol>
            </div>
            
            <div class="p-6 bg-gray-100 border-t">
                <p class="text-gray-600 text-sm">If you encounter any issues during installation, please check the documentation or contact support.</p>
            </div>
        </div>
    </div>
</body>
</html>
