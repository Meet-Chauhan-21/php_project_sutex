<?php
session_start();

// Database connection
$host = 'localhost';
$user = 'root';
$pass = '';
$db = 'vidhyaguru_db';

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

echo "<h2>🔧 Profile Picture Database Fix</h2>";

// Check if user is logged in, if not, provide quick login
if (!isset($_SESSION['user_id'])) {
    echo "<div style='background: #fff3cd; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
    echo "<p>⚠️ Please login first to test profile pictures</p>";
    echo "<form method='post' style='margin: 10px 0;'>";
    echo "<input type='email' name='email' placeholder='Email' value='test@example.com' required style='padding: 8px; margin: 5px;'>";
    echo "<input type='password' name='password' placeholder='Password' value='testpass' required style='padding: 8px; margin: 5px;'>";
    echo "<button type='submit' name='quick_login' style='padding: 8px 15px; background: #007bff; color: white; border: none; border-radius: 4px;'>Quick Login</button>";
    echo "</form>";
    echo "</div>";
    
    if (isset($_POST['quick_login'])) {
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
                echo "<div style='background: #d4edda; padding: 10px; border-radius: 5px; color: #155724;'>";
                echo "✅ Login successful! Refresh the page to continue.";
                echo "</div>";
            } else {
                echo "<div style='background: #f8d7da; padding: 10px; border-radius: 5px; color: #721c24;'>❌ Invalid password</div>";
            }
        } else {
            echo "<div style='background: #f8d7da; padding: 10px; border-radius: 5px; color: #721c24;'>❌ User not found</div>";
        }
    }
    $conn->close();
    exit;
}

$user_id = $_SESSION['user_id'];
echo "<div style='background: #d4edda; padding: 10px; border-radius: 5px; color: #155724; margin: 10px 0;'>";
echo "✅ Logged in as User ID: $user_id ({$_SESSION['user_email']})";
echo "</div>";

// Step 1: Check and fix database structure
echo "<h3>Step 1: Database Structure Check</h3>";

$result = $conn->query("SHOW COLUMNS FROM users LIKE 'profile_picture'");
if ($result->num_rows == 0) {
    echo "<p style='color: red;'>❌ profile_picture column is missing! Adding it...</p>";
    
    $sql = "ALTER TABLE users ADD COLUMN profile_picture LONGTEXT";
    if ($conn->query($sql)) {
        echo "<p style='color: green;'>✅ profile_picture column added successfully!</p>";
    } else {
        echo "<p style='color: red;'>❌ Error adding column: " . $conn->error . "</p>";
        exit;
    }
} else {
    echo "<p style='color: green;'>✅ profile_picture column exists</p>";
    $row = $result->fetch_assoc();
    echo "<p>Column type: <strong>" . $row['Type'] . "</strong></p>";
}

// Step 2: Check current profile picture
echo "<h3>Step 2: Current Profile Picture Status</h3>";

$stmt = $conn->prepare("SELECT profile_picture FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if ($user['profile_picture']) {
    $picLength = strlen($user['profile_picture']);
    echo "<div style='background: #d4edda; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
    echo "<p style='color: #155724;'>✅ Profile picture found!</p>";
    echo "<p>Size: " . number_format($picLength) . " characters</p>";
    echo "<p>Preview:</p>";
    echo "<img src='{$user['profile_picture']}' style='max-width: 150px; max-height: 150px; border: 2px solid #28a745; border-radius: 8px;'>";
    echo "</div>";
} else {
    echo "<div style='background: #f8d7da; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
    echo "<p style='color: #721c24;'>❌ No profile picture found in database</p>";
    echo "</div>";
}

// Step 3: Add test picture
echo "<h3>Step 3: Add Test Picture</h3>";
echo "<form method='post' style='margin: 10px 0;'>";
echo "<button type='submit' name='add_test_picture' style='padding: 10px 20px; background: #28a745; color: white; border: none; border-radius: 5px; margin: 5px;'>Add Test Picture</button>";
echo "<button type='submit' name='clear_picture' style='padding: 10px 20px; background: #dc3545; color: white; border: none; border-radius: 5px; margin: 5px;'>Clear Picture</button>";
echo "</form>";

if (isset($_POST['add_test_picture'])) {
    // Create a test image with current timestamp
    $timestamp = date('Y-m-d H:i:s');
    $testImage = 'data:image/svg+xml;base64,' . base64_encode("
        <svg width='200' height='200' xmlns='http://www.w3.org/2000/svg'>
            <defs>
                <linearGradient id='grad1' x1='0%' y1='0%' x2='100%' y2='100%'>
                    <stop offset='0%' style='stop-color:#4CAF50;stop-opacity:1' />
                    <stop offset='100%' style='stop-color:#2196F3;stop-opacity:1' />
                </linearGradient>
            </defs>
            <rect width='200' height='200' fill='url(#grad1)' rx='20'/>
            <text x='100' y='70' font-family='Arial' font-size='24' font-weight='bold' fill='white' text-anchor='middle'>TEST</text>
            <text x='100' y='100' font-family='Arial' font-size='16' fill='white' text-anchor='middle'>Profile Picture</text>
            <text x='100' y='130' font-family='Arial' font-size='12' fill='white' text-anchor='middle'>$timestamp</text>
            <text x='100' y='160' font-family='Arial' font-size='10' fill='white' text-anchor='middle'>User ID: $user_id</text>
        </svg>
    ");
    
    $stmt = $conn->prepare("UPDATE users SET profile_picture = ? WHERE id = ?");
    $stmt->bind_param("si", $testImage, $user_id);
    
    if ($stmt->execute()) {
        echo "<div style='background: #d4edda; padding: 10px; border-radius: 5px; color: #155724;'>";
        echo "✅ Test picture added successfully! <a href='javascript:location.reload()'>Refresh</a> to see it.";
        echo "</div>";
    } else {
        echo "<div style='background: #f8d7da; padding: 10px; border-radius: 5px; color: #721c24;'>";
        echo "❌ Failed to add test picture: " . $stmt->error;
        echo "</div>";
    }
}

if (isset($_POST['clear_picture'])) {
    $stmt = $conn->prepare("UPDATE users SET profile_picture = NULL WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    
    if ($stmt->execute()) {
        echo "<div style='background: #d4edda; padding: 10px; border-radius: 5px; color: #155724;'>";
        echo "✅ Picture cleared! <a href='javascript:location.reload()'>Refresh</a> to see changes.";
        echo "</div>";
    } else {
        echo "<div style='background: #f8d7da; padding: 10px; border-radius: 5px; color: #721c24;'>";
        echo "❌ Failed to clear picture: " . $stmt->error;
        echo "</div>";
    }
}

// Step 4: Test API endpoints
echo "<h3>Step 4: Test API Endpoints</h3>";
echo "<button onclick='testGetProfile()' style='padding: 10px 20px; background: #007bff; color: white; border: none; border-radius: 5px; margin: 5px;'>Test Get Profile API</button>";
echo "<button onclick='testUpdateProfile()' style='padding: 10px 20px; background: #ffc107; color: black; border: none; border-radius: 5px; margin: 5px;'>Test Update Profile API</button>";
echo "<div id='apiTestResult' style='margin: 10px 0; padding: 10px; background: #f8f9fa; border-radius: 5px; display: none;'></div>";

$conn->close();
?>

<script>
async function testGetProfile() {
    const resultDiv = document.getElementById('apiTestResult');
    resultDiv.style.display = 'block';
    resultDiv.innerHTML = '<p>🔄 Testing get_user_profile.php...</p>';
    
    try {
        const response = await fetch('php/get_user_profile.php', {
            method: 'GET',
            credentials: 'include'
        });
        
        const data = await response.json();
        
        if (data.success) {
            const user = data.user;
            let result = '<h4>✅ Get Profile API Test Results</h4>';
            result += `<p><strong>Name:</strong> ${user.first_name} ${user.last_name}</p>`;
            result += `<p><strong>Email:</strong> ${user.email}</p>`;
            
            if (user.profile_picture) {
                result += `<p><strong>Profile Picture:</strong> ✅ Found (${user.profile_picture.length} characters)</p>`;
                result += `<p><strong>API Preview:</strong></p>`;
                result += `<img src="${user.profile_picture}" style="max-width: 100px; max-height: 100px; border: 1px solid #ccc; border-radius: 5px;">`;
            } else {
                result += `<p><strong>Profile Picture:</strong> ❌ Not found</p>`;
            }
            
            resultDiv.innerHTML = result;
        } else {
            resultDiv.innerHTML = `<p>❌ API Error: ${data.error}</p>`;
        }
    } catch (error) {
        resultDiv.innerHTML = `<p>❌ JavaScript Error: ${error.message}</p>`;
    }
}

async function testUpdateProfile() {
    const resultDiv = document.getElementById('apiTestResult');
    resultDiv.style.display = 'block';
    resultDiv.innerHTML = '<p>🔄 Testing update_user_profile.php...</p>';
    
    // Create a simple test image
    const canvas = document.createElement('canvas');
    canvas.width = 100;
    canvas.height = 100;
    const ctx = canvas.getContext('2d');
    
    ctx.fillStyle = '#FF5722';
    ctx.fillRect(0, 0, 100, 100);
    ctx.fillStyle = 'white';
    ctx.font = '16px Arial';
    ctx.textAlign = 'center';
    ctx.fillText('API', 50, 45);
    ctx.fillText('TEST', 50, 65);
    
    const base64 = canvas.toDataURL('image/png');
    
    try {
        const response = await fetch('php/update_user_profile.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            credentials: 'include',
            body: JSON.stringify({ profile_picture: base64 })
        });
        
        const data = await response.json();
        
        if (data.success) {
            resultDiv.innerHTML = `
                <h4>✅ Update Profile API Test Results</h4>
                <p>Picture updated successfully via API!</p>
                <p>New test image:</p>
                <img src="${base64}" style="max-width: 100px; border: 1px solid #ccc; border-radius: 5px;">
                <p><a href="javascript:location.reload()">Refresh page</a> to see the updated picture above.</p>
            `;
        } else {
            resultDiv.innerHTML = `<p>❌ API Update Error: ${data.error}</p>`;
        }
    } catch (error) {
        resultDiv.innerHTML = `<p>❌ JavaScript Error: ${error.message}</p>`;
    }
}
</script>

<style>
    body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
    h2, h3 { color: #333; }
    button { cursor: pointer; }
    button:hover { opacity: 0.8; }
</style>

<hr>
<p><strong>Next Steps:</strong></p>
<ol>
    <li>Click "Add Test Picture" to add a sample profile picture</li>
    <li>Test the API endpoints to verify they work</li>
    <li>Go to <a href="profile.html">profile.html</a> and try uploading a real picture</li>
    <li>Go to <a href="simple_picture_test.html">simple_picture_test.html</a> for more testing</li>
</ol>
