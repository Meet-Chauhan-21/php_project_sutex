<?php
// Script to check tables in camuscore_db and create copy script for vidhyaguru_db
// Run this to see what tables exist in camuscore_db

$mysqli = new mysqli('localhost', 'root', '', 'camuscore_db');

if ($mysqli->connect_errno) {
    die("Connection failed: " . $mysqli->connect_error);
}

echo "<h2>Tables in camuscore_db:</h2>\n";
echo "<pre>\n";

// Get all tables
$result = $mysqli->query("SHOW TABLES");
$tables = [];

if ($result) {
    while ($row = $result->fetch_array()) {
        $tableName = $row[0];
        $tables[] = $tableName;
        echo "Table: $tableName\n";
        
        // Show table structure
        $structureResult = $mysqli->query("DESCRIBE $tableName");
        if ($structureResult) {
            while ($column = $structureResult->fetch_assoc()) {
                echo "  - {$column['Field']} ({$column['Type']})\n";
            }
        }
        
        // Show row count
        $countResult = $mysqli->query("SELECT COUNT(*) as count FROM $tableName");
        if ($countResult) {
            $count = $countResult->fetch_assoc();
            echo "  Rows: {$count['count']}\n";
        }
        echo "\n";
    }
}

echo "</pre>\n";

// Generate SQL script
echo "<h2>Generated SQL Script:</h2>\n";
echo "<textarea rows='20' cols='80'>\n";
echo "-- Create vidhyaguru_db database and copy data from camuscore_db\n";
echo "CREATE DATABASE IF NOT EXISTS `vidhyaguru_db` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;\n";
echo "USE `vidhyaguru_db`;\n\n";

foreach ($tables as $table) {
    echo "-- Copy $table table\n";
    echo "CREATE TABLE IF NOT EXISTS `$table` LIKE `camuscore_db`.`$table`;\n";
    echo "INSERT INTO `$table` SELECT * FROM `camuscore_db`.`$table`;\n\n";
}

echo "-- Verification\n";
foreach ($tables as $table) {
    echo "SELECT COUNT(*) as {$table}_count FROM $table;\n";
}

echo "</textarea>\n";

$mysqli->close();
?>
