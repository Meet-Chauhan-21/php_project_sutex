<?php
try {
    $mysqli = new mysqli('localhost', 'root', '', 'vidyaguru_db', 3306);
    if ($mysqli->connect_errno) {
        echo "Connection failed: " . $mysqli->connect_error;
    } else {
        echo "Database connection successful\n";
        
        // Check if tables exist
        $tables = ['users', 'applications', 'admins', 'audit_logs'];
        foreach ($tables as $table) {
            $result = $mysqli->query("SHOW TABLES LIKE '$table'");
            if ($result->num_rows > 0) {
                echo "Table '$table' exists\n";
                $count = $mysqli->query("SELECT COUNT(*) FROM $table")->fetch_row()[0];
                echo "  Records in $table: $count\n";
            } else {
                echo "Table '$table' does NOT exist\n";
            }
        }
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
