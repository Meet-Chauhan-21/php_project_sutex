<?php
echo "Change Password End-to-End Test\n";
echo "===============================\n\n";

// Step 1: Test login
echo "1. Testing login...\n";
$loginData = [
    'email' => 'test@example.com',
    'password' => 'testpassword'
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'http://localhost:8081/php/login_user.php');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($loginData));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_COOKIEJAR, 'cookie.txt');
curl_setopt($ch, CURLOPT_COOKIEFILE, 'cookie.txt');

$loginResponse = curl_exec($ch);
$loginResult = json_decode($loginResponse, true);

if ($loginResult && $loginResult['success']) {
    echo "✅ Login successful: " . $loginResult['user']['name'] . "\n";
    
    // Step 2: Test change password
    echo "\n2. Testing change password...\n";
    $changePasswordData = [
        'currentPassword' => 'testpassword',
        'newPassword' => 'newpassword123'
    ];
    
    curl_setopt($ch, CURLOPT_URL, 'http://localhost:8081/php/change_password.php');
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($changePasswordData));
    
    $changeResponse = curl_exec($ch);
    $changeResult = json_decode($changeResponse, true);
    
    if ($changeResult && $changeResult['success']) {
        echo "✅ Password changed successfully!\n";
        
        // Step 3: Test login with new password
        echo "\n3. Testing login with new password...\n";
        $newLoginData = [
            'email' => 'test@example.com',
            'password' => 'newpassword123'
        ];
        
        curl_setopt($ch, CURLOPT_URL, 'http://localhost:8081/php/login_user.php');
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($newLoginData));
        
        $newLoginResponse = curl_exec($ch);
        $newLoginResult = json_decode($newLoginResponse, true);
        
        if ($newLoginResult && $newLoginResult['success']) {
            echo "✅ New password login successful!\n";
            
            // Step 4: Change password back to original
            echo "\n4. Changing password back to original...\n";
            $revertPasswordData = [
                'currentPassword' => 'newpassword123',
                'newPassword' => 'testpassword'
            ];
            
            curl_setopt($ch, CURLOPT_URL, 'http://localhost:8081/php/change_password.php');
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($revertPasswordData));
            
            $revertResponse = curl_exec($ch);
            $revertResult = json_decode($revertResponse, true);
            
            if ($revertResult && $revertResult['success']) {
                echo "✅ Password reverted to original successfully!\n";
                echo "\n🎉 All tests passed! Change password functionality is working properly.\n";
            } else {
                echo "❌ Failed to revert password: " . ($revertResult['error'] ?? 'Unknown error') . "\n";
            }
        } else {
            echo "❌ New password login failed: " . ($newLoginResult['error'] ?? 'Unknown error') . "\n";
        }
    } else {
        echo "❌ Password change failed: " . ($changeResult['error'] ?? 'Unknown error') . "\n";
        echo "Response: " . $changeResponse . "\n";
    }
} else {
    echo "❌ Login failed: " . ($loginResult['error'] ?? 'Unknown error') . "\n";
    echo "Response: " . $loginResponse . "\n";
}

curl_close($ch);

// Clean up cookie file
if (file_exists('cookie.txt')) {
    unlink('cookie.txt');
}
?>
