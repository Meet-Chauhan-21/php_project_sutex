<?php
echo "Database Structure Check\n";
echo "========================\n\n";

$mysqli = new mysqli('localhost', 'root', '', 'vidhyaguru_db', 3306);

if ($mysqli->connect_errno) {
    die("Connection failed: " . $mysqli->connect_error . "\n");
}

echo "✅ Connected to vidhyaguru_db\n\n";

// Check users table structure
echo "Users table structure:\n";
$result = $mysqli->query("DESCRIBE users");
$columns = [];
while ($row = $result->fetch_assoc()) {
    $columns[] = $row['Field'];
    echo "- " . $row['Field'] . " (" . $row['Type'] . ")\n";
}

// Check if profile_picture column exists
if (!in_array('profile_picture', $columns)) {
    echo "\n❌ profile_picture column missing! Adding it...\n";
    
    $addColumnSql = "ALTER TABLE users ADD COLUMN profile_picture LONGTEXT";
    if ($mysqli->query($addColumnSql)) {
        echo "✅ profile_picture column added successfully!\n";
    } else {
        echo "❌ Failed to add profile_picture column: " . $mysqli->error . "\n";
    }
} else {
    echo "\n✅ profile_picture column exists\n";
}

// Check if program column exists
if (!in_array('program', $columns)) {
    echo "\n❌ program column missing! Adding it...\n";
    
    $addProgramSql = "ALTER TABLE users ADD COLUMN program VARCHAR(100)";
    if ($mysqli->query($addProgramSql)) {
        echo "✅ program column added successfully!\n";
    } else {
        echo "❌ Failed to add program column: " . $mysqli->error . "\n";
    }
} else {
    echo "✅ program column exists\n";
}

echo "\nSample user data:\n";
$result = $mysqli->query("SELECT id, first_name, last_name, email, phone, program, 
                         CASE WHEN profile_picture IS NOT NULL AND profile_picture != '' 
                              THEN CONCAT(SUBSTRING(profile_picture, 1, 50), '...') 
                              ELSE 'None' END as pic_preview
                         FROM users LIMIT 5");

while ($row = $result->fetch_assoc()) {
    echo "- ID: {$row['id']}, Name: {$row['first_name']} {$row['last_name']}, Email: {$row['email']}, Phone: " . ($row['phone'] ?: 'None') . ", Program: " . ($row['program'] ?: 'None') . ", Picture: {$row['pic_preview']}\n";
}

$mysqli->close();
echo "\nDatabase check completed!\n";
?>
