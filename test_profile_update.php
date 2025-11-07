<?php
session_start();
require_once 'php/config.php';

echo "=== Profile Update Test ===\n";

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo "❌ User not logged in. Please login first.\n";
    echo "Visit: http://localhost:8081/test_login.php?test_login=1\n";
    exit;
}

echo "✅ User logged in with ID: " . $_SESSION['user_id'] . "\n";

// Get current user data
$stmt = $mysqli->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param('i', $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    echo "❌ User not found in database\n";
    exit;
}

echo "Current user data:\n";
echo "Name: " . $user['first_name'] . " " . $user['last_name'] . "\n";
echo "Email: " . $user['email'] . "\n";
echo "Phone: " . ($user['phone'] ?? 'Not set') . "\n";
echo "Program: " . ($user['program'] ?? 'Not set') . "\n";
echo "Additional info: " . ($user['additional_info'] ?? 'None') . "\n";

// Test profile update
if (isset($_GET['test_update']) && $_GET['test_update'] == '1') {
    echo "\n=== Testing Profile Update ===\n";
    
    // Sample update data
    $updateData = [
        'firstName' => 'John',
        'lastName' => 'Doe Updated',
        'phone' => '1234567890',
        'program' => 'BCA',
        'dateOfBirth' => '1990-01-01',
        'gender' => 'male',
        'address' => '123 Test Street, Test City',
        'emergencyContact' => 'Jane Doe - 0987654321'
    ];
    
    try {
        // Parse existing additional_info
        $additionalInfo = [];
        if ($user['additional_info']) {
            $additionalInfo = json_decode($user['additional_info'], true);
        }
        
        // Update additional_info fields
        if (isset($updateData['dateOfBirth'])) {
            $additionalInfo['dateOfBirth'] = $updateData['dateOfBirth'];
        }
        
        if (isset($updateData['gender'])) {
            $additionalInfo['gender'] = $updateData['gender'];
        }
        
        if (isset($updateData['address'])) {
            $additionalInfo['address'] = $updateData['address'];
        }
        
        if (isset($updateData['emergencyContact'])) {
            $additionalInfo['emergencyContact'] = $updateData['emergencyContact'];
        }
        
        // Update user
        $stmt = $mysqli->prepare("UPDATE users SET first_name = ?, last_name = ?, phone = ?, program = ?, additional_info = ? WHERE id = ?");
        $additionalInfoJson = json_encode($additionalInfo);
        $stmt->bind_param('sssssi', 
            $updateData['firstName'], 
            $updateData['lastName'], 
            $updateData['phone'], 
            $updateData['program'], 
            $additionalInfoJson, 
            $_SESSION['user_id']
        );
        
        if ($stmt->execute()) {
            echo "✅ Profile updated successfully!\n";
            
            // Fetch updated data
            $stmt = $mysqli->prepare("SELECT * FROM users WHERE id = ?");
            $stmt->bind_param('i', $_SESSION['user_id']);
            $stmt->execute();
            $result = $stmt->get_result();
            $updatedUser = $result->fetch_assoc();
            
            echo "\nUpdated user data:\n";
            echo "Name: " . $updatedUser['first_name'] . " " . $updatedUser['last_name'] . "\n";
            echo "Email: " . $updatedUser['email'] . "\n";
            echo "Phone: " . $updatedUser['phone'] . "\n";
            echo "Program: " . $updatedUser['program'] . "\n";
            echo "Additional info: " . $updatedUser['additional_info'] . "\n";
            
        } else {
            echo "❌ Failed to update profile: " . $mysqli->error . "\n";
        }
        
    } catch (Exception $e) {
        echo "❌ Update error: " . $e->getMessage() . "\n";
    }
}

if (!isset($_GET['test_update'])) {
    echo "\nTo test profile update, visit: http://localhost:8081/test_profile_update.php?test_update=1\n";
}
?>
