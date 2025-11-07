<?php
session_start();
require_once 'php/config.php';

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

echo "<h2>🔍 Profile Picture & Data Debug Report</h2>";
echo "<hr>";

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo "<p style='color: orange;'>⚠️ No active session. Please login first.</p>";
    echo "<form method='post' style='margin: 20px 0;'>";
    echo "<input type='email' name='email' placeholder='Email' required>";
    echo "<input type='password' name='password' placeholder='Password' required>";
    echo "<button type='submit' name='login'>Quick Login</button>";
    echo "</form>";
    
    if (isset($_POST['login'])) {
        $email = $_POST['email'];
        $password = $_POST['password'];
        
        $stmt = $conn->prepare("SELECT id, first_name, last_name, email, password FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];
                $_SESSION['user_email'] = $user['email'];
                echo "<p style='color: green;'>✅ Login successful! Refresh to see data.</p>";
            } else {
                echo "<p style='color: red;'>❌ Invalid password</p>";
            }
        } else {
            echo "<p style='color: red;'>❌ User not found</p>";
        }
    }
} else {
    echo "<p style='color: green;'>✅ Session active: User ID {$_SESSION['user_id']}</p>";
    
    // Get current user data
    $user_id = $_SESSION['user_id'];
    $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        
        echo "<h3>📊 Current User Data</h3>";
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>Field</th><th>Value</th><th>Status</th></tr>";
        
        foreach ($user as $field => $value) {
            if ($field == 'password') {
                $displayValue = "***HASHED***";
                $status = "🔒 Secure";
            } elseif ($field == 'profile_picture') {
                if ($value) {
                    $displayValue = "Base64 data (" . strlen($value) . " chars)";
                    $status = "✅ Present";
                } else {
                    $displayValue = "NULL";
                    $status = "❌ Missing";
                }
            } else {
                $displayValue = $value ?: "NULL";
                $status = $value ? "✅ Set" : "⚠️ Empty";
            }
            
            echo "<tr><td><strong>$field</strong></td><td>$displayValue</td><td>$status</td></tr>";
        }
        echo "</table>";
        
        // Profile picture analysis
        echo "<h3>🖼️ Profile Picture Analysis</h3>";
        if ($user['profile_picture']) {
            $picData = $user['profile_picture'];
            $picLength = strlen($picData);
            
            echo "<div style='background: #e8f5e8; padding: 15px; border-radius: 8px;'>";
            echo "<p>✅ <strong>Profile picture found in database!</strong></p>";
            echo "<p>📏 Size: " . number_format($picLength) . " characters</p>";
            echo "<p>🔍 Format: " . (strpos($picData, 'data:image/') === 0 ? 'Valid Base64' : 'Unknown') . "</p>";
            
            if (strpos($picData, 'data:image/') === 0) {
                echo "<p>🎯 Preview:</p>";
                echo "<img src='$picData' style='max-width: 150px; max-height: 150px; border: 2px solid #ccc; border-radius: 8px;'>";
            }
            echo "</div>";
        } else {
            echo "<div style='background: #fff3cd; padding: 15px; border-radius: 8px;'>";
            echo "<p>⚠️ <strong>No profile picture in database</strong></p>";
            echo "</div>";
        }
        
        // Test profile picture upload
        echo "<h3>🧪 Test Profile Picture Upload</h3>";
        echo "<form method='post' style='background: #f8f9fa; padding: 15px; border-radius: 8px;'>";
        echo "<p>Upload a test image or generate a sample:</p>";
        echo "<button type='submit' name='generate_sample'>Generate Sample Image</button>";
        echo "<button type='submit' name='clear_picture'>Clear Picture</button>";
        echo "</form>";
        
        if (isset($_POST['generate_sample'])) {
            // Generate a sample base64 image
            $sampleImage = 'data:image/svg+xml;base64,' . base64_encode('
                <svg width="100" height="100" xmlns="http://www.w3.org/2000/svg">
                    <defs>
                        <linearGradient id="grad1" x1="0%" y1="0%" x2="100%" y2="100%">
                            <stop offset="0%" style="stop-color:#ff6b6b;stop-opacity:1" />
                            <stop offset="100%" style="stop-color:#4ecdc4;stop-opacity:1" />
                        </linearGradient>
                    </defs>
                    <rect width="100" height="100" fill="url(#grad1)" rx="10"/>
                    <text x="50" y="55" font-family="Arial" font-size="16" fill="white" text-anchor="middle">TEST</text>
                </svg>
            ');
            
            $stmt = $conn->prepare("UPDATE users SET profile_picture = ? WHERE id = ?");
            $stmt->bind_param("si", $sampleImage, $user_id);
            
            if ($stmt->execute()) {
                echo "<p style='color: green;'>✅ Sample image uploaded! Refresh to see changes.</p>";
            } else {
                echo "<p style='color: red;'>❌ Failed to upload sample image: " . $conn->error . "</p>";
            }
        }
        
        if (isset($_POST['clear_picture'])) {
            $stmt = $conn->prepare("UPDATE users SET profile_picture = NULL WHERE id = ?");
            $stmt->bind_param("i", $user_id);
            
            if ($stmt->execute()) {
                echo "<p style='color: green;'>✅ Picture cleared! Refresh to see changes.</p>";
            } else {
                echo "<p style='color: red;'>❌ Failed to clear picture: " . $conn->error . "</p>";
            }
        }
    }
}

// Show all users summary
echo "<h3>👥 All Users Summary</h3>";
$result = $conn->query("SELECT id, first_name, last_name, email, phone, program, 
                        CASE 
                            WHEN profile_picture IS NOT NULL THEN 'Yes' 
                            ELSE 'No' 
                        END as has_picture,
                        CHAR_LENGTH(profile_picture) as picture_size
                        FROM users");

if ($result->num_rows > 0) {
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>ID</th><th>Name</th><th>Email</th><th>Phone</th><th>Program</th><th>Has Picture</th><th>Picture Size</th></tr>";
    
    while ($row = $result->fetch_assoc()) {
        $pictureStatus = $row['has_picture'] == 'Yes' ? 
            "✅ " . number_format($row['picture_size']) . " chars" : 
            "❌ No picture";
            
        echo "<tr>";
        echo "<td>{$row['id']}</td>";
        echo "<td>{$row['first_name']} {$row['last_name']}</td>";
        echo "<td>{$row['email']}</td>";
        echo "<td>" . ($row['phone'] ?: 'Not set') . "</td>";
        echo "<td>" . ($row['program'] ?: 'Not set') . "</td>";
        echo "<td>{$row['has_picture']}</td>";
        echo "<td>$pictureStatus</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p>No users found.</p>";
}

// Database structure check
echo "<h3>🗄️ Database Structure</h3>";
$result = $conn->query("DESCRIBE users");
if ($result) {
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td><strong>{$row['Field']}</strong></td>";
        echo "<td>{$row['Type']}</td>";
        echo "<td>{$row['Null']}</td>";
        echo "<td>{$row['Key']}</td>";
        echo "<td>{$row['Default']}</td>";
        echo "<td>{$row['Extra']}</td>";
        echo "</tr>";
    }
    echo "</table>";
}

echo "<hr>";
echo "<p><em>Generated at: " . date('Y-m-d H:i:s') . "</em></p>";

$conn->close();
?>

<style>
    body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
    table { background: white; margin: 10px 0; }
    th { background: #3b82f6; color: white; padding: 8px; }
    td { padding: 8px; }
    h2, h3 { color: #333; }
    hr { margin: 20px 0; }
    form { margin: 10px 0; }
    input, button { padding: 8px; margin: 5px; border: 1px solid #ddd; border-radius: 4px; }
    button { background: #3b82f6; color: white; border: none; cursor: pointer; }
    button:hover { background: #2563eb; }
</style>
