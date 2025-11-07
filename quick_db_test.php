<?php
// Quick database connection test
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Database Connection Test</h2>";

try {
    $mysqli = new mysqli('localhost', 'root', '', 'vidyaguru_db');
    
    if ($mysqli->connect_errno) {
        throw new Exception("Connection failed: " . $mysqli->connect_error);
    }
    
    echo "<p style='color: green;'>✅ Database connection successful!</p>";
    
    // Test if tables exist
    $result = $mysqli->query("SHOW TABLES");
    echo "<h3>Tables found:</h3>";
    echo "<ul>";
    while ($row = $result->fetch_array()) {
        echo "<li>" . $row[0] . "</li>";
    }
    echo "</ul>";
    
    // Test users table
    if ($mysqli->query("SELECT 1 FROM users LIMIT 1")) {
        $result = $mysqli->query("SELECT COUNT(*) as count FROM users");
        $row = $result->fetch_assoc();
        echo "<p>Users table: " . $row['count'] . " records</p>";
    } else {
        echo "<p style='color: red;'>❌ Users table not accessible</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
    
    // Try to connect without database name to see if MySQL is running
    try {
        $mysqli = new mysqli('localhost', 'root', '');
        echo "<p style='color: orange;'>⚠️ MySQL is running but vidyaguru_db database may not exist</p>";
        
        // Show available databases
        $result = $mysqli->query("SHOW DATABASES");
        echo "<h3>Available databases:</h3><ul>";
        while ($row = $result->fetch_array()) {
            echo "<li>" . $row[0] . "</li>";
        }
        echo "</ul>";
        
    } catch (Exception $e2) {
        echo "<p style='color: red;'>❌ MySQL connection failed: " . $e2->getMessage() . "</p>";
    }
}
?>
