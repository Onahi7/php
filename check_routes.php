<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Route Checker</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; margin: 20px; color: #333; }
        h1, h2 { color: #2563eb; }
        .success { color: green; }
        .error { color: red; }
        .warning { color: orange; }
        pre { background: #f5f5f5; padding: 10px; border-radius: 4px; overflow-x: auto; }
        .container { max-width: 800px; margin: 0 auto; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        th, td { padding: 8px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background-color: #f2f2f2; }
        tr:hover { background-color: #f5f5f5; }
    </style>
</head>
<body>
    <div class='container'>
        <h1>Route Checker</h1>";

// Get base URL
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
$host = $_SERVER['HTTP_HOST'];
$base_url = "$protocol://$host/summit";

// Define routes to check
$routes = [
    '/' => 'Home Page',
    '/login' => 'Login Page',
    '/register' => 'Registration Page',
    '/assets/css/styles.css' => 'CSS File',
    '/assets/js/main.js' => 'JavaScript File',
    '/nonexistent-page' => '404 Page (should return 404)',
];

// Function to check route
function check_route($url, $description) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HEADER, true);
    curl_setopt($ch, CURLOPT_NOBODY, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $content_type = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
    $size = curl_getinfo($ch, CURLINFO_SIZE_DOWNLOAD);
    $error = curl_error($ch);
    
    curl_close($ch);
    
    return [
        'url' => $url,
        'description' => $description,
        'http_code' => $http_code,
        'content_type' => $content_type,
        'size' => $size,
        'error' => $error,
        'status' => $error ? 'error' : ($http_code >= 200 && $http_code < 400 ? 'success' : 'error')
    ];
}

// Check all routes
echo "<h2>Checking Routes</h2>";
echo "<table>
    <tr>
        <th>Route</th>
        <th>Description</th>
        <th>Status</th>
        <th>HTTP Code</th>
        <th>Content Type</th>
        <th>Size</th>
    </tr>";

$all_routes_ok = true;
foreach ($routes as $route => $description) {
    $url = $base_url . $route;
    $result = check_route($url, $description);
    
    if ($result['status'] === 'error' && $route !== '/nonexistent-page') {
        $all_routes_ok = false;
    }
    
    // Special case for 404 page
    if ($route === '/nonexistent-page' && $result['http_code'] !== 404) {
        $all_routes_ok = false;
    }
    
    echo "<tr>
        <td>" . htmlspecialchars($route) . "</td>
        <td>" . htmlspecialchars($description) . "</td>
        <td class='" . $result['status'] . "'>" . 
            ($route === '/nonexistent-page' ? 
                ($result['http_code'] === 404 ? 'success (404 as expected)' : 'error (should be 404)') : 
                $result['status']) . 
        "</td>
        <td>" . $result['http_code'] . "</td>
        <td>" . htmlspecialchars($result['content_type']) . "</td>
        <td>" . number_format($result['size']) . " bytes</td>
    </tr>";
    
    if ($result['error']) {
        echo "<tr>
            <td colspan='6' class='error'>Error: " . htmlspecialchars($result['error']) . "</td>
        </tr>";
    }
}

echo "</table>";

// Check .htaccess file
echo "<h2>Checking .htaccess Configuration</h2>";

$htaccess_file = __DIR__ . '/.htaccess';
if (file_exists($htaccess_file)) {
    $htaccess_content = file_get_contents($htaccess_file);
    echo "<p class='success'>.htaccess file exists.</p>";
    
    // Check for common issues
    $issues = [];
    
    if (strpos($htaccess_content, 'RewriteEngine On') === false) {
        $issues[] = 'RewriteEngine On directive is missing';
    }
    
    if (strpos($htaccess_content, 'RewriteBase /summit/') === false) {
        $issues[] = 'RewriteBase /summit/ directive is missing or incorrect';
    }
    
    if (strpos($htaccess_content, 'RewriteRule ^(.*)$ index.php [QSA,L]') === false) {
        $issues[] = 'Main rewrite rule to index.php is missing or incorrect';
    }
    
    if (!empty($issues)) {
        echo "<p class='error'>Issues found in .htaccess:</p>";
        echo "<ul>";
        foreach ($issues as $issue) {
            echo "<li class='error'>" . htmlspecialchars($issue) . "</li>";
        }
        echo "</ul>";
    } else {
        echo "<p class='success'>No issues found in .htaccess configuration.</p>";
    }
    
    echo "<h3>.htaccess Content</h3>";
    echo "<pre>" . htmlspecialchars($htaccess_content) . "</pre>";
} else {
    echo "<p class='error'>.htaccess file does not exist!</p>";
}

// Check server configuration
echo "<h2>Server Configuration</h2>";

echo "<table>
    <tr>
        <th>Setting</th>
        <th>Value</th>
    </tr>
    <tr>
        <td>PHP Version</td>
        <td>" . phpversion() . "</td>
    </tr>
    <tr>
        <td>Server Software</td>
        <td>" . htmlspecialchars($_SERVER['SERVER_SOFTWARE']) . "</td>
    </tr>
    <tr>
        <td>Document Root</td>
        <td>" . htmlspecialchars($_SERVER['DOCUMENT_ROOT']) . "</td>
    </tr>
    <tr>
        <td>mod_rewrite Loaded</td>
        <td>" . (function_exists('apache_get_modules') && in_array('mod_rewrite', apache_get_modules()) ? 
            "<span class='success'>Yes</span>" : 
            "<span class='warning'>Unknown (can't detect)</span>") . "</td>
    </tr>
</table>";

// Recommendations
echo "<h2>Recommendations</h2>";

if ($all_routes_ok) {
    echo "<p class='success'>All routes are working correctly!</p>";
} else {
    echo "<p class='error'>Some routes are not working correctly. Here are some recommendations:</p>";
    echo "<ul>
        <li>Make sure mod_rewrite is enabled in Apache</li>
        <li>Check that AllowOverride is set to All in your Apache configuration</li>
        <li>Verify that the .htaccess file is properly configured</li>
        <li>Check file and directory permissions</li>
        <li>Look for errors in your Apache error log</li>
    </ul>";
}

echo "</div>
</body>
</html>";
?>

