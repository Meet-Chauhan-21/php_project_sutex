<?php
// Quick database fix - run this ONCE
$conn = new mysqli('localhost', 'root', '', 'vidhyaguru_db');

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Add profile_picture column if it doesn't exist
$result = $conn->query("SHOW COLUMNS FROM users LIKE 'profile_picture'");
if ($result->num_rows == 0) {
    $sql = "ALTER TABLE users ADD COLUMN profile_picture LONGTEXT";
    if ($conn->query($sql)) {
        echo "✅ profile_picture column added!<br>";
    } else {
        echo "❌ Error: " . $conn->error . "<br>";
    }
} else {
    echo "✅ profile_picture column already exists<br>";
}

// Add a test picture for the first user
$result = $conn->query("SELECT id FROM users LIMIT 1");
if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $user_id = $row['id'];
    
    $testImage = 'data:image/svg+xml;base64,' . base64_encode('<svg width="100" height="100"><rect width="100" height="100" fill="#4CAF50"/><text x="50" y="55" text-anchor="middle" fill="white" font-size="16">TEST</text></svg>');
    
    $stmt = $conn->prepare("UPDATE users SET profile_picture = ? WHERE id = ?");
    $stmt->bind_param("si", $testImage, $user_id);
    
    if ($stmt->execute()) {
        echo "✅ Test picture added for user ID $user_id<br>";
    } else {
        echo "❌ Failed to add test picture<br>";
    }
}

echo "<br><a href='profile.html'>Go to Profile Page</a>";
$conn->close();
?>
