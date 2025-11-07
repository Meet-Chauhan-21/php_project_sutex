<?php
// Database connection test for vidhyaguru_db
header('Content-Type: text/html; charset=utf-8');

echo "<h2>🔌 Testing Connection to vidhyaguru_db Database</h2>\n";
echo "<div style='font-family: Arial, sans-serif; max-width: 800px; margin: 20px;'>\n";

// Database configuration
$DB_HOST = 'localhost';
$DB_USER = 'root';
$DB_PASS = '';
$DB_NAME = 'vidyaguru_db';
$DB_PORT = 3306;

echo "<h3>📋 Connection Details:</h3>\n";
echo "<ul>\n";
echo "<li><strong>Host:</strong> $DB_HOST</li>\n";
echo "<li><strong>Database:</strong> $DB_NAME</li>\n";
echo "<li><strong>Port:</strong> $DB_PORT</li>\n";
echo "<li><strong>User:</strong> $DB_USER</li>\n";
echo "</ul>\n";

try {
    // Test connection
    echo "<h3>🔄 Testing Connection...</h3>\n";
    
    $mysqli = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME, $DB_PORT);
    
    if ($mysqli->connect_errno) {
        throw new Exception("Connection failed: " . $mysqli->connect_error);
    }
    
    echo "<p style='color: green; font-weight: bold;'>✅ Successfully connected to vidhyaguru_db!</p>\n";
    
    // Set charset
    $mysqli->set_charset('utf8mb4');
    echo "<p>✅ Character set set to utf8mb4</p>\n";
    
    // Test database info
    echo "<h3>📊 Database Information:</h3>\n";
    
    // Get MySQL version
    $version = $mysqli->server_info;
    echo "<p><strong>MySQL Version:</strong> $version</p>\n";
    
    // Get database name
    $result = $mysqli->query("SELECT DATABASE() as db_name");
    if ($result) {
        $row = $result->fetch_assoc();
        echo "<p><strong>Current Database:</strong> " . $row['db_name'] . "</p>\n";
    }
    
    // List all tables
    echo "<h3>📋 Tables in Database:</h3>\n";
    $result = $mysqli->query("SHOW TABLES");
    
    if ($result && $result->num_rows > 0) {
        echo "<table border='1' cellpadding='10' cellspacing='0' style='border-collapse: collapse; width: 100%;'>\n";
        echo "<tr style='background-color: #f0f0f0;'><th>Table Name</th><th>Row Count</th><th>Status</th></tr>\n";
        
        while ($row = $result->fetch_array()) {
            $tableName = $row[0];
            
            // Get row count for each table
            $countResult = $mysqli->query("SELECT COUNT(*) as count FROM `$tableName`");
            $rowCount = 0;
            if ($countResult) {
                $countRow = $countResult->fetch_assoc();
                $rowCount = $countRow['count'];
            }
            
            echo "<tr>\n";
            echo "<td><strong>$tableName</strong></td>\n";
            echo "<td>$rowCount rows</td>\n";
            echo "<td style='color: green;'>✅ Available</td>\n";
            echo "</tr>\n";
        }
        echo "</table>\n";
    } else {
        echo "<p style='color: orange;'>⚠️ No tables found in the database.</p>\n";
    }
    
    // Test specific tables that should exist
    echo "<h3>🔍 Testing Required Tables:</h3>\n";
    $requiredTables = ['users', 'applications', 'admin_users'];
    
    foreach ($requiredTables as $table) {
        $result = $mysqli->query("SHOW TABLES LIKE '$table'");
        if ($result && $result->num_rows > 0) {
            // Get table structure
            $structResult = $mysqli->query("DESCRIBE `$table`");
            echo "<h4>Table: $table</h4>\n";
            
            if ($structResult) {
                echo "<table border='1' cellpadding='5' cellspacing='0' style='border-collapse: collapse; margin-left: 20px;'>\n";
                echo "<tr style='background-color: #f9f9f9;'><th>Field</th><th>Type</th><th>Null</th><th>Key</th></tr>\n";
                
                while ($field = $structResult->fetch_assoc()) {
                    echo "<tr>\n";
                    echo "<td>{$field['Field']}</td>\n";
                    echo "<td>{$field['Type']}</td>\n";
                    echo "<td>{$field['Null']}</td>\n";
                    echo "<td>{$field['Key']}</td>\n";
                    echo "</tr>\n";
                }
                echo "</table>\n";
                
                // Show sample data (first 3 rows)
                $sampleResult = $mysqli->query("SELECT * FROM `$table` LIMIT 3");
                if ($sampleResult && $sampleResult->num_rows > 0) {
                    echo "<p><strong>Sample Data (first 3 rows):</strong></p>\n";
                    echo "<table border='1' cellpadding='5' cellspacing='0' style='border-collapse: collapse; margin-left: 20px; font-size: 12px;'>\n";
                    
                    // Headers
                    echo "<tr style='background-color: #e9e9e9;'>\n";
                    $fields = $sampleResult->fetch_fields();
                    foreach ($fields as $field) {
                        echo "<th>{$field->name}</th>\n";
                    }
                    echo "</tr>\n";
                    
                    // Data rows
                    $sampleResult->data_seek(0);
                    while ($row = $sampleResult->fetch_assoc()) {
                        echo "<tr>\n";
                        foreach ($row as $value) {
                            $displayValue = strlen($value) > 30 ? substr($value, 0, 30) . '...' : $value;
                            echo "<td>" . htmlspecialchars($displayValue) . "</td>\n";
                        }
                        echo "</tr>\n";
                    }
                    echo "</table>\n";
                }
            }
            echo "<p style='color: green;'>✅ Table '$table' exists and is accessible</p>\n";
        } else {
            echo "<p style='color: red;'>❌ Table '$table' not found</p>\n";
        }
    }
    
    // Test a simple query
    echo "<h3>🧪 Testing Sample Query:</h3>\n";
    $testQuery = "SELECT COUNT(*) as total_users FROM users";
    $result = $mysqli->query($testQuery);
    
    if ($result) {
        $row = $result->fetch_assoc();
        echo "<p>✅ Query executed successfully: <code>$testQuery</code></p>\n";
        echo "<p><strong>Result:</strong> {$row['total_users']} total users in database</p>\n";
    } else {
        echo "<p style='color: red;'>❌ Query failed: " . $mysqli->error . "</p>\n";
    }
    
    $mysqli->close();
    
} catch (Exception $e) {
    echo "<p style='color: red; font-weight: bold;'>❌ Connection Error: " . $e->getMessage() . "</p>\n";
    
    echo "<h3>🔧 Troubleshooting:</h3>\n";
    echo "<ul>\n";
    echo "<li>Make sure XAMPP MySQL service is running</li>\n";
    echo "<li>Check if the database 'vidyaguru_db' exists in phpMyAdmin</li>\n";
    echo "<li>Verify database credentials in config.php</li>\n";
    echo "<li>Check MySQL error logs in XAMPP</li>\n";
    echo "</ul>\n";
}

echo "</div>\n";
?>

<style>
body { 
    font-family: Arial, sans-serif; 
    background-color: #f5f5f5; 
    margin: 20px; 
}
table { 
    background-color: white; 
    border-radius: 5px; 
    box-shadow: 0 2px 5px rgba(0,0,0,0.1); 
    margin: 10px 0; 
}
th { 
    background-color: #4CAF50; 
    color: white; 
    font-weight: bold; 
}
code { 
    background-color: #f0f0f0; 
    padding: 2px 5px; 
    border-radius: 3px; 
    font-family: monospace; 
}
</style>
