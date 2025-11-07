<?php
session_start();
require_once 'php/config.php';

// Simple test page to verify profile picture persistence
echo "<h2>🧪 Profile Picture Persistence Test</h2>";

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo "<p style='color: red;'>❌ Please login first</p>";
    echo "<form method='post'>";
    echo "<input type='email' name='email' placeholder='Email' required>";
    echo "<input type='password' name='password' placeholder='Password' required>";
    echo "<button type='submit' name='login'>Login</button>";
    echo "</form>";
    
    if (isset($_POST['login'])) {
        $email = $_POST['email'];
        $password = $_POST['password'];
        
        $stmt = $mysqli->prepare("SELECT id, first_name, last_name, email, password FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];
                $_SESSION['user_email'] = $user['email'];
                echo "<p style='color: green;'>✅ Login successful! Refresh page.</p>";
            } else {
                echo "<p style='color: red;'>❌ Invalid password</p>";
            }
        } else {
            echo "<p style='color: red;'>❌ User not found</p>";
        }
    }
    exit;
}

$user_id = $_SESSION['user_id'];
echo "<p style='color: green;'>✅ Logged in as User ID: $user_id</p>";

// Get current profile picture from database
$stmt = $mysqli->prepare("SELECT profile_picture FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

echo "<h3>Current Database Status</h3>";
if ($user['profile_picture']) {
    $picLength = strlen($user['profile_picture']);
    echo "<div style='background: #d4edda; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
    echo "<p>✅ <strong>Profile picture found in database!</strong></p>";
    echo "<p>📏 Size: " . number_format($picLength) . " characters</p>";
    echo "<p>🎯 Preview:</p>";
    echo "<img src='{$user['profile_picture']}' style='max-width: 150px; max-height: 150px; border: 2px solid #ccc; border-radius: 8px;'>";
    echo "</div>";
} else {
    echo "<div style='background: #f8d7da; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
    echo "<p>❌ <strong>No profile picture in database</strong></p>";
    echo "</div>";
}

// Test actions
echo "<h3>Test Actions</h3>";
echo "<form method='post' style='margin: 10px 0;'>";
echo "<button type='submit' name='test_upload' style='background: #007bff; color: white; padding: 10px; border: none; border-radius: 5px; margin: 5px;'>Upload Test Image</button>";
echo "<button type='submit' name='clear_picture' style='background: #dc3545; color: white; padding: 10px; border: none; border-radius: 5px; margin: 5px;'>Clear Picture</button>";
echo "<button type='submit' name='test_api' style='background: #28a745; color: white; padding: 10px; border: none; border-radius: 5px; margin: 5px;'>Test API Endpoints</button>";
echo "</form>";

if (isset($_POST['test_upload'])) {
    // Create a test image
    $testImage = 'data:image/svg+xml;base64,' . base64_encode('
        <svg width="100" height="100" xmlns="http://www.w3.org/2000/svg">
            <defs>
                <linearGradient id="grad1" x1="0%" y1="0%" x2="100%" y2="100%">
                    <stop offset="0%" style="stop-color:#ff6b6b;stop-opacity:1" />
                    <stop offset="100%" style="stop-color:#4ecdc4;stop-opacity:1" />
                </linearGradient>
            </defs>
            <rect width="100" height="100" fill="url(#grad1)" rx="10"/>
            <text x="50" y="55" font-family="Arial" font-size="12" fill="white" text-anchor="middle">TEST</text>
            <text x="50" y="75" font-family="Arial" font-size="8" fill="white" text-anchor="middle">' . date('H:i:s') . '</text>
        </svg>
    ');
    
    $stmt = $mysqli->prepare("UPDATE users SET profile_picture = ? WHERE id = ?");
    $stmt->bind_param("si", $testImage, $user_id);
    
    if ($stmt->execute()) {
        echo "<p style='color: green;'>✅ Test image uploaded successfully! <a href='javascript:location.reload()'>Refresh</a> to see changes.</p>";
    } else {
        echo "<p style='color: red;'>❌ Failed to upload test image: " . $mysqli->error . "</p>";
    }
}

if (isset($_POST['clear_picture'])) {
    $stmt = $mysqli->prepare("UPDATE users SET profile_picture = NULL WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    
    if ($stmt->execute()) {
        echo "<p style='color: green;'>✅ Picture cleared! <a href='javascript:location.reload()'>Refresh</a> to see changes.</p>";
    } else {
        echo "<p style='color: red;'>❌ Failed to clear picture: " . $mysqli->error . "</p>";
    }
}

if (isset($_POST['test_api'])) {
    echo "<h4>API Endpoint Tests</h4>";
    
    // Test get_user_profile.php
    echo "<div style='background: #f8f9fa; padding: 10px; border-radius: 5px; margin: 10px 0;'>";
    echo "<strong>Testing get_user_profile.php:</strong><br>";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'http://localhost:8081/php/get_user_profile.php');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_COOKIE, session_name() . '=' . session_id());
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    echo "HTTP Code: $httpCode<br>";
    
    if ($httpCode == 200) {
        $data = json_decode($response, true);
        if ($data['success']) {
            echo "✅ API Response: Success<br>";
            echo "📊 User Data: " . $data['user']['first_name'] . " " . $data['user']['last_name'] . "<br>";
            echo "🖼️ Has Picture: " . (isset($data['user']['profile_picture']) && $data['user']['profile_picture'] ? 'Yes (' . strlen($data['user']['profile_picture']) . ' chars)' : 'No') . "<br>";
        } else {
            echo "❌ API Error: " . $data['error'] . "<br>";
        }
    } else {
        echo "❌ HTTP Error: $httpCode<br>";
    }
    echo "</div>";
}

// JavaScript test
echo "<h3>JavaScript Test</h3>";
echo "<button onclick='testJavaScript()' style='background: #6f42c1; color: white; padding: 10px; border: none; border-radius: 5px;'>Test JavaScript Profile Loading</button>";
echo "<div id='jsTestResult' style='margin: 10px 0; padding: 10px; background: #f8f9fa; border-radius: 5px; display: none;'></div>";

?>

<script>
async function testJavaScript() {
    const resultDiv = document.getElementById('jsTestResult');
    resultDiv.style.display = 'block';
    resultDiv.innerHTML = '<p>🔄 Testing JavaScript profile loading...</p>';
    
    try {
        const response = await fetch('php/get_user_profile.php', {
            method: 'GET',
            credentials: 'include'
        });
        
        const data = await response.json();
        
        if (data.success) {
            const user = data.user;
            let result = '<h4>✅ JavaScript Test Results</h4>';
            result += `<p><strong>Name:</strong> ${user.first_name} ${user.last_name}</p>`;
            result += `<p><strong>Email:</strong> ${user.email}</p>`;
            result += `<p><strong>Phone:</strong> ${user.phone || 'Not set'}</p>`;
            result += `<p><strong>Program:</strong> ${user.program || 'Not set'}</p>`;
            
            if (user.profile_picture) {
                result += `<p><strong>Profile Picture:</strong> ✅ Found (${user.profile_picture.length} characters)</p>`;
                result += `<p><strong>Preview:</strong></p>`;
                result += `<img src="${user.profile_picture}" style="max-width: 100px; max-height: 100px; border: 1px solid #ccc; border-radius: 5px;">`;
            } else {
                result += `<p><strong>Profile Picture:</strong> ❌ Not found</p>`;
            }
            
            resultDiv.innerHTML = result;
        } else {
            resultDiv.innerHTML = `<p>❌ Error: ${data.error}</p>`;
        }
    } catch (error) {
        resultDiv.innerHTML = `<p>❌ JavaScript Error: ${error.message}</p>`;
    }
}
</script>

<style>
    body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
    h2, h3, h4 { color: #333; }
    form { margin: 10px 0; }
    input, button { padding: 8px; margin: 5px; border: 1px solid #ddd; border-radius: 4px; }
    button { cursor: pointer; }
    button:hover { opacity: 0.8; }
</style>

<hr>
<p><em>Generated at: <?php echo date('Y-m-d H:i:s'); ?></em></p>
<p><a href="profile.html">🔗 Go to Profile Page</a> | <a href="comprehensive_profile_test.html">🧪 Comprehensive Test Suite</a></p>
