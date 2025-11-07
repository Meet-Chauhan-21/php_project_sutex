<?php
// Simple database check
$host = 'localhost';
$user = 'root';
$pass = '';
$db = 'vidhyaguru_db';

echo "Connecting to database...\n";

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error . "\n");
}

echo "✅ Connected successfully!\n";

// Check if profile_picture column exists
echo "Checking profile_picture column...\n";

$result = $conn->query("SHOW COLUMNS FROM users LIKE 'profile_picture'");

if ($result->num_rows == 0) {
    echo "❌ profile_picture column missing! Adding it...\n";
    
    $alterSQL = "ALTER TABLE users ADD COLUMN profile_picture LONGTEXT";
    if ($conn->query($alterSQL)) {
        echo "✅ profile_picture column added successfully!\n";
    } else {
        echo "❌ Error adding column: " . $conn->error . "\n";
    }
} else {
    echo "✅ profile_picture column exists!\n";
    $row = $result->fetch_assoc();
    echo "Column type: " . $row['Type'] . "\n";
}

// Show all columns
echo "\nAll columns in users table:\n";
$result = $conn->query("DESCRIBE users");
while ($row = $result->fetch_assoc()) {
    echo "- " . $row['Field'] . " (" . $row['Type'] . ")\n";
}

// Check current users
echo "\nCurrent users:\n";
$result = $conn->query("SELECT id, first_name, last_name, email, 
                        CASE 
                            WHEN profile_picture IS NULL THEN 'NULL'
                            WHEN profile_picture = '' THEN 'EMPTY'
                            ELSE CONCAT('HAS DATA (', CHAR_LENGTH(profile_picture), ' chars)')
                        END as pic_status
                        FROM users LIMIT 5");

while ($row = $result->fetch_assoc()) {
    echo "ID: {$row['id']}, Name: {$row['first_name']} {$row['last_name']}, Email: {$row['email']}, Picture: {$row['pic_status']}\n";
}

$conn->close();
echo "\nDone!\n";
?>
