<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Database Connection Test</h1>";

try {
    require_once __DIR__ . '/config/database.php';
    
    // Test basic connection
    if ($conn->ping()) {
        echo "<p style='color: green'>✓ Database connection successful</p>";
        
        // Test query execution
        $test_query = "SELECT 1";
        if ($result = $conn->query($test_query)) {
            echo "<p style='color: green'>✓ Query execution successful</p>";
            $result->free();
        } else {
            throw new Exception("Query failed: " . $conn->error);
        }
        
        // Test database configuration
        echo "<h2>Database Configuration:</h2>";
        echo "<pre>";
        echo "Character Set: " . $conn->character_set_name() . "\n";
        echo "Collation: " . $conn->query("SELECT @@collation_database")->fetch_row()[0] . "\n";
        echo "Time Zone: " . $conn->query("SELECT @@time_zone")->fetch_row()[0] . "\n";
        echo "</pre>";
        
        // Test required tables
        $required_tables = ['users', 'states', 'payments', 'settings'];
        echo "<h2>Required Tables:</h2>";
        foreach ($required_tables as $table) {
            $result = $conn->query("SHOW TABLES LIKE '$table'");
            echo "$table: " . ($result->num_rows > 0 ? "✓ Exists" : "✗ Missing") . "<br>";
        }
        
    } else {
        throw new Exception("Connection lost: " . $conn->error);
    }
    
} catch (Exception $e) {
    echo "<p style='color: red'>✗ Error: " . htmlspecialchars($e->getMessage()) . "</p>";
    if (file_exists(__DIR__ . '/logs/error.log')) {
        echo "<h2>Recent Error Log:</h2>";
        echo "<pre>" . htmlspecialchars(tail(__DIR__ . '/logs/error.log', 10)) . "</pre>";
    }
}

// Helper function to get last n lines of file
function tail($filename, $lines = 10) {
    $file = file($filename);
    return implode("", array_slice($file, -$lines));
}
?>
<style>
    body { font-family: Arial, sans-serif; margin: 20px; }
    h1, h2 { color: #2563eb; }
    pre { background: #f1f5f9; padding: 10px; border-radius: 4px; }
</style>

