<?php
// Direct database fix for profile pictures
$host = 'localhost';
$user = 'root';
$pass = '';
$db = 'vidhyaguru_db';

echo "=== PROFILE PICTURE FIX SCRIPT ===\n\n";

// Connect to database
$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("❌ Connection failed: " . $conn->connect_error . "\n");
}

echo "✅ Connected to database successfully\n\n";

// Step 1: Check if profile_picture column exists
echo "Step 1: Checking profile_picture column...\n";
$result = $conn->query("SHOW COLUMNS FROM users LIKE 'profile_picture'");

if ($result->num_rows == 0) {
    echo "❌ profile_picture column does NOT exist!\n";
    echo "Adding profile_picture column...\n";
    
    $sql = "ALTER TABLE users ADD COLUMN profile_picture LONGTEXT";
    if ($conn->query($sql)) {
        echo "✅ profile_picture column added successfully!\n";
    } else {
        echo "❌ Error adding column: " . $conn->error . "\n";
        exit;
    }
} else {
    echo "✅ profile_picture column exists\n";
    $row = $result->fetch_assoc();
    echo "Column type: " . $row['Type'] . "\n";
}

// Step 2: Show current table structure
echo "\nStep 2: Current table structure:\n";
$result = $conn->query("DESCRIBE users");
while ($row = $result->fetch_assoc()) {
    echo "- " . $row['Field'] . " (" . $row['Type'] . ")\n";
}

// Step 3: Check current users
echo "\nStep 3: Current users and their profile pictures:\n";
$result = $conn->query("SELECT id, first_name, last_name, email, 
                        CASE 
                            WHEN profile_picture IS NULL THEN 'NULL'
                            WHEN profile_picture = '' THEN 'EMPTY'
                            ELSE CONCAT('HAS DATA (', CHAR_LENGTH(profile_picture), ' chars)')
                        END as pic_status
                        FROM users");

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo "ID: {$row['id']}, Name: {$row['first_name']} {$row['last_name']}, Email: {$row['email']}, Picture: {$row['pic_status']}\n";
    }
} else {
    echo "No users found\n";
}

// Step 4: Test inserting a sample profile picture
echo "\nStep 4: Testing profile picture insertion...\n";

// Create a simple test image (SVG)
$testImage = 'data:image/svg+xml;base64,' . base64_encode('
<svg width="100" height="100" xmlns="http://www.w3.org/2000/svg">
    <rect width="100" height="100" fill="#4CAF50"/>
    <text x="50" y="50" font-family="Arial" font-size="12" fill="white" text-anchor="middle" dominant-baseline="middle">TEST</text>
    <text x="50" y="70" font-family="Arial" font-size="8" fill="white" text-anchor="middle">' . date('H:i:s') . '</text>
</svg>');

// Find the first user to test with
$result = $conn->query("SELECT id FROM users LIMIT 1");
if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $test_user_id = $row['id'];
    
    echo "Testing with user ID: $test_user_id\n";
    echo "Test image size: " . strlen($testImage) . " characters\n";
    
    // Insert the test image
    $stmt = $conn->prepare("UPDATE users SET profile_picture = ? WHERE id = ?");
    $stmt->bind_param("si", $testImage, $test_user_id);
    
    if ($stmt->execute()) {
        echo "✅ Test image inserted successfully!\n";
        
        // Verify the insertion
        $verify_stmt = $conn->prepare("SELECT CHAR_LENGTH(profile_picture) as pic_length, profile_picture FROM users WHERE id = ?");
        $verify_stmt->bind_param("i", $test_user_id);
        $verify_stmt->execute();
        $verify_result = $verify_stmt->get_result();
        $verify_row = $verify_result->fetch_assoc();
        
        if ($verify_row['pic_length'] > 0) {
            echo "✅ Verification successful: " . $verify_row['pic_length'] . " characters stored\n";
            echo "Image preview: " . substr($verify_row['profile_picture'], 0, 50) . "...\n";
        } else {
            echo "❌ Verification failed: No data found\n";
        }
    } else {
        echo "❌ Failed to insert test image: " . $stmt->error . "\n";
    }
} else {
    echo "❌ No users found to test with\n";
}

echo "\n=== FIX COMPLETED ===\n";
echo "Database has been updated. Profile pictures should now work!\n";

$conn->close();
?>
