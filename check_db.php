<?php
echo "Database Check Script\n";
echo "====================\n\n";

// Check connection without specifying database
$mysqli = new mysqli('localhost', 'root', '', '', 3306);
if ($mysqli->connect_errno) {
    echo "❌ MySQL connection failed: " . $mysqli->connect_error . "\n";
    exit;
}

echo "✅ MySQL connection successful\n\n";

// List all databases
echo "Available databases:\n";
$result = $mysqli->query("SHOW DATABASES");
$databases = [];
while ($row = $result->fetch_assoc()) {
    $databases[] = $row['Database'];
    echo "- " . $row['Database'] . "\n";
}

echo "\n";

// Check for vidhyaguru_db specifically
if (in_array('vidhyaguru_db', $databases)) {
    echo "✅ vidhyaguru_db database found\n";
    
    // Connect to the specific database
    $mysqli->select_db('vidhyaguru_db');
    
    // Check tables
    echo "\nTables in vidhyaguru_db:\n";
    $result = $mysqli->query("SHOW TABLES");
    $tables = [];
    while ($row = $result->fetch_array()) {
        $tables[] = $row[0];
        echo "- " . $row[0] . "\n";
    }
    
    if (in_array('users', $tables)) {
        echo "\n✅ Users table found\n";
        $result = $mysqli->query("SELECT COUNT(*) as count FROM users");
        $row = $result->fetch_assoc();
        echo "Total users: " . $row['count'] . "\n";
    } else {
        echo "\n❌ Users table not found\n";
    }
    
} else {
    echo "❌ vidhyaguru_db database not found\n";
    echo "Need to create the database\n";
}

$mysqli->close();
?>
