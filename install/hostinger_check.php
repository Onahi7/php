<?php
/**
 * System check script for Hostinger
 */

$requirements = [
    'php_version' => [
        'name' => 'PHP Version',
        'required' => '7.4.0',
        'current' => PHP_VERSION,
        'result' => version_compare(PHP_VERSION, '7.4.0', '>='),
        'message' => 'PHP 7.4 or higher is required.'
    ],
    'pdo_mysql' => [
        'name' => 'PDO MySQL Extension',
        'required' => 'Enabled',
        'current' => extension_loaded('pdo_mysql') ? 'Enabled' : 'Disabled',
        'result' => extension_loaded('pdo_mysql'),
        'message' => 'PDO MySQL extension is required.'
    ],
    'mbstring' => [
        'name' => 'Mbstring Extension',
        'required' => 'Enabled',
        'current' => extension_loaded('mbstring') ? 'Enabled' : 'Disabled',
        'result' => extension_loaded('mbstring'),
        'message' => 'Mbstring extension is required for UTF-8 support.'
    ],
    'json' => [
        'name' => 'JSON Extension',
        'required' => 'Enabled',
        'current' => extension_loaded('json') ? 'Enabled' : 'Disabled',
        'result' => extension_loaded('json'),
        'message' => 'JSON extension is required.'
    ],
    'config_writable' => [
        'name' => 'Config Directory Writable',
        'required' => 'Writable',
        'current' => is_writable('../config') ? 'Writable' : 'Not Writable',
        'result' => is_writable('../config'),
        'message' => 'The config directory must be writable.'
    ],
    'file_uploads' => [
        'name' => 'File Uploads',
        'required' => 'Enabled',
        'current' => ini_get('file_uploads') ? 'Enabled' : 'Disabled',
        'result' => ini_get('file_uploads'),
        'message' => 'File uploads must be enabled.'
    ],
    'memory_limit' => [
        'name' => 'Memory Limit',
        'required' => '64M',
        'current' => ini_get('memory_limit'),
        'result' => (int)ini_get('memory_limit') >= 64,
        'message' => 'Memory limit should be at least 64M.'
    ]
];

$all_requirements_met = true;
foreach ($requirements as $req) {
    if (!$req['result']) {
        $all_requirements_met = false;
        break;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>System Check - Summit Setup</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-50 min-h-screen">
    <div class="container mx-auto px-4 py-12">
        <div class="max-w-3xl mx-auto bg-white rounded-lg shadow-md overflow-hidden">
            <div class="p-6 bg-blue-600 text-white">
                <h1 class="text-2xl font-bold">System Requirements Check</h1>
                <p class="mt-2">Checking if your server meets the requirements for Summit.</p>
            </div>
            
            <div class="p-6">
                <div class="overflow-x-auto">
                    <table class="w-full border-collapse">
                        <thead>
                            <tr class="bg-gray-100">
                                <th class="border px-4 py-2 text-left">Requirement</th>
                                <th class="border px-4 py-2 text-left">Required</th>
                                <th class="border px-4 py-2 text-left">Current</th>
                                <th class="border px-4 py-2 text-left">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($requirements as $key => $req): ?>
                                <tr>
                                    <td class="border px-4 py-2"><?php echo htmlspecialchars($req['name']); ?></td>
                                    <td class="border px-4 py-2"><?php echo htmlspecialchars($req['required']); ?></td>
                                    <td class="border px-4 py-2"><?php echo htmlspecialchars($req['current']); ?></td>
                                    <td class="border px-4 py-2">
                                        <?php if ($req['result']): ?>
                                            <span class="inline-block bg-green-100 text-green-800 px-2 py-1 rounded">Pass</span>
                                        <?php else: ?>
                                            <span class="inline-block bg-red-100 text-red-800 px-2 py-1 rounded">Fail</span>
                                            <p class="text-sm text-red-600 mt-1"><?php echo htmlspecialchars($req['message']); ?></p>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <?php if ($all_requirements_met): ?>
                    <div class="mt-6 p-4 bg-green-100 text-green-800 rounded">
                        <p class="font-semibold">All requirements are met! You can proceed with the installation.</p>
                    </div>
                <?php else: ?>
                    <div class="mt-6 p-4 bg-red-100 text-red-800 rounded">
                        <p class="font-semibold">Your server does not meet all requirements. Please fix the issues listed above before proceeding.</p>
                    </div>
                <?php endif; ?>
                
                <div class="mt-6 flex space-x-4">
                    <a href="index.php" class="inline-block bg-gray-500 hover:bg-gray-600 text-white py-2 px-4 rounded">
                        Back to Installation
                    </a>
                    <?php if ($all_requirements_met): ?>
                        <a href="setup.php" class="inline-block bg-blue-600 hover:bg-blue-700 text-white py-2 px-4 rounded">
                            Proceed to Database Setup
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
