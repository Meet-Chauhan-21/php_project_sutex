<?php
echo "User Data Synchronization Test\n";
echo "===============================\n\n";

$mysqli = new mysqli('localhost', 'root', '', 'vidhyaguru_db', 3306);

if ($mysqli->connect_errno) {
    die("Connection failed: " . $mysqli->connect_error . "\n");
}

echo "Current users in database:\n";
echo "-------------------------\n";

$result = $mysqli->query("
    SELECT 
        id, 
        first_name, 
        last_name, 
        email, 
        phone,
        program,
        CASE 
            WHEN profile_picture IS NOT NULL AND profile_picture != '' 
            THEN 'Yes' 
            ELSE 'No' 
        END as has_picture,
        additional_info,
        created_at
    FROM users 
    ORDER BY id
");

$userCount = 0;
while ($row = $result->fetch_assoc()) {
    $userCount++;
    $additionalInfo = json_decode($row['additional_info'], true);
    
    echo "User {$userCount}:\n";
    echo "  ID: {$row['id']}\n";
    echo "  Name: {$row['first_name']} {$row['last_name']}\n";
    echo "  Email: {$row['email']}\n";
    echo "  Phone: " . ($row['phone'] ?: 'Not set') . "\n";
    echo "  Program: " . ($row['program'] ?: 'Not set') . "\n";
    echo "  Profile Picture: {$row['has_picture']}\n";
    echo "  Created: {$row['created_at']}\n";
    
    if ($additionalInfo) {
        echo "  Additional Info:\n";
        foreach ($additionalInfo as $key => $value) {
            if (is_array($value)) {
                echo "    {$key}: " . json_encode($value) . "\n";
            } else {
                echo "    {$key}: {$value}\n";
            }
        }
    }
    echo "\n";
}

echo "Total users: {$userCount}\n\n";

// Test profile loading for each user
echo "Testing Profile Loading for Each User:\n";
echo "=====================================\n";

$testResult = $mysqli->query("SELECT id, email FROM users");
while ($user = $testResult->fetch_assoc()) {
    echo "Testing user ID {$user['id']} ({$user['email']}):\n";
    
    // Simulate the get_user_profile.php logic
    $stmt = $mysqli->prepare("
        SELECT 
            id, first_name, last_name, email, phone, program, 
            registration_date, additional_info, profile_picture 
        FROM users 
        WHERE id = ?
    ");
    $stmt->bind_param('i', $user['id']);
    $stmt->execute();
    $result = $stmt->get_result();
    $profileData = $result->fetch_assoc();
    $stmt->close();
    
    if ($profileData) {
        $profileData['additional_info'] = json_decode($profileData['additional_info'], true) ?: [];
        
        echo "  ✅ Profile loaded successfully\n";
        echo "  - Complete name: " . ($profileData['first_name'] && $profileData['last_name'] ? 'Yes' : 'No') . "\n";
        echo "  - Has phone: " . ($profileData['phone'] ? 'Yes' : 'No') . "\n";
        echo "  - Has program: " . ($profileData['program'] ? 'Yes' : 'No') . "\n";
        echo "  - Has picture: " . ($profileData['profile_picture'] ? 'Yes' : 'No') . "\n";
        echo "  - Additional fields: " . count($profileData['additional_info']) . "\n";
    } else {
        echo "  ❌ Failed to load profile\n";
    }
    echo "\n";
}

$mysqli->close();
?>
