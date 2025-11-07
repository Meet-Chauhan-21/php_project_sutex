<?php
require_once 'php/config.php';

echo "=== Database Structure Check ===\n";

try {
    // Check if the database exists
    $result = $mysqli->query("SELECT DATABASE()");
    $row = $result->fetch_array();
    echo "Current database: " . $row[0] . "\n";
    
    // Check table structure
    echo "\n=== Users Table Structure ===\n";
    $result = $mysqli->query("DESCRIBE users");
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            echo sprintf("%-20s %-20s %-8s %-8s %-15s\n", 
                $row['Field'], 
                $row['Type'], 
                $row['Null'], 
                $row['Key'], 
                $row['Default'] ?: 'NULL'
            );
        }
    } else {
        echo "Error describing table: " . $mysqli->error . "\n";
    }
    
    // Check if profile_picture column exists
    echo "\n=== Profile Picture Column Check ===\n";
    $result = $mysqli->query("SHOW COLUMNS FROM users LIKE 'profile_picture'");
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        echo "✅ profile_picture column exists!\n";
        echo "Type: " . $row['Type'] . "\n";
        echo "Null: " . $row['Null'] . "\n";
        echo "Default: " . ($row['Default'] ?: 'NULL') . "\n";
    } else {
        echo "❌ profile_picture column does NOT exist!\n";
        echo "Adding profile_picture column...\n";
        
        $alterQuery = "ALTER TABLE users ADD COLUMN profile_picture LONGTEXT";
        if ($mysqli->query($alterQuery)) {
            echo "✅ profile_picture column added successfully!\n";
        } else {
            echo "❌ Error adding column: " . $mysqli->error . "\n";
        }
    }
    
    // Check current users and their profile pictures
    echo "\n=== Current Users and Profile Pictures ===\n";
    $result = $mysqli->query("SELECT id, first_name, last_name, email, 
                              CASE 
                                  WHEN profile_picture IS NULL THEN 'NULL'
                                  WHEN profile_picture = '' THEN 'EMPTY'
                                  ELSE CONCAT('DATA (', CHAR_LENGTH(profile_picture), ' chars)')
                              END as picture_status
                              FROM users");
    
    if ($result) {
        printf("%-5s %-15s %-15s %-30s %-20s\n", "ID", "First Name", "Last Name", "Email", "Picture Status");
        echo str_repeat("-", 85) . "\n";
        
        while ($row = $result->fetch_assoc()) {
            printf("%-5s %-15s %-15s %-30s %-20s\n", 
                $row['id'], 
                $row['first_name'], 
                $row['last_name'], 
                $row['email'], 
                $row['picture_status']
            );
        }
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

$mysqli->close();
?>
