<?php
// Test admission submission system
header('Content-Type: text/html; charset=utf-8');

echo "<h1>🧪 Admission Submission Test</h1>";

// Check config
echo "<h2>1. Checking Configuration</h2>";
if (file_exists('config.php')) {
    echo "✅ config.php found<br>";
    require_once 'config.php';
    
    if (isset($mysqli) && !$mysqli->connect_error) {
        echo "✅ Database connection successful<br>";
        echo "Database: " . $mysqli->get_server_info() . "<br>";
    } else {
        echo "❌ Database connection failed<br>";
        exit;
    }
} else {
    echo "❌ config.php not found<br>";
    exit;
}

// Check applications table structure
echo "<h2>2. Checking Applications Table</h2>";
$result = $mysqli->query("DESCRIBE applications");
if ($result) {
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>";
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>{$row['Field']}</td>";
        echo "<td>{$row['Type']}</td>";
        echo "<td>{$row['Null']}</td>";
        echo "<td>{$row['Key']}</td>";
        echo "<td>{$row['Default']}</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "❌ Error: " . $mysqli->error . "<br>";
}

// Check if users table exists and has data
echo "<h2>3. Checking Users Table</h2>";
$result = $mysqli->query("SELECT COUNT(*) as count FROM users");
if ($result) {
    $row = $result->fetch_assoc();
    echo "✅ Users table exists with {$row['count']} users<br>";
    
    // Show sample user
    $result = $mysqli->query("SELECT id, email, CONCAT(first_name, ' ', last_name) as name FROM users LIMIT 1");
    if ($result && $result->num_rows > 0) {
        $user = $result->fetch_assoc();
        echo "Sample user: {$user['name']} ({$user['email']}) - ID: {$user['id']}<br>";
    }
} else {
    echo "❌ Error checking users: " . $mysqli->error . "<br>";
}

// Check existing applications
echo "<h2>4. Existing Applications</h2>";
$result = $mysqli->query("SELECT * FROM applications ORDER BY created_at DESC LIMIT 5");
if ($result) {
    if ($result->num_rows > 0) {
        echo "<table border='1' cellpadding='5'>";
        echo "<tr><th>ID</th><th>User ID</th><th>Program</th><th>Status</th><th>Application Data</th><th>Created</th></tr>";
        while ($row = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>{$row['id']}</td>";
            echo "<td>{$row['user_id']}</td>";
            echo "<td>{$row['program']}</td>";
            echo "<td>{$row['status']}</td>";
            echo "<td><pre>" . htmlspecialchars(print_r(json_decode($row['application_data'], true), true)) . "</pre></td>";
            echo "<td>{$row['created_at']}</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "No applications found yet.<br>";
    }
} else {
    echo "❌ Error: " . $mysqli->error . "<br>";
}

// Test insertion
echo "<h2>5. Test Form Submission</h2>";
echo "<form action='submit_admission.php' method='POST' style='border: 1px solid #ccc; padding: 20px; max-width: 600px;'>";
echo "<h3>Test Admission Form</h3>";
echo "User ID: <input type='text' name='userId' value='1' style='width: 100%; padding: 5px; margin: 5px 0;'><br>";
echo "First Name: <input type='text' name='firstName' value='Test' required style='width: 100%; padding: 5px; margin: 5px 0;'><br>";
echo "Last Name: <input type='text' name='lastName' value='Student' required style='width: 100%; padding: 5px; margin: 5px 0;'><br>";
echo "Email: <input type='email' name='email' value='test@student.com' required style='width: 100%; padding: 5px; margin: 5px 0;'><br>";
echo "Phone: <input type='text' name='phone' value='1234567890' required style='width: 100%; padding: 5px; margin: 5px 0;'><br>";
echo "DOB: <input type='date' name='dob' value='2000-01-01' required style='width: 100%; padding: 5px; margin: 5px 0;'><br>";
echo "Gender: <select name='gender' required style='width: 100%; padding: 5px; margin: 5px 0;'><option value='Male'>Male</option><option value='Female'>Female</option></select><br>";
echo "Program: <select name='program' required style='width: 100%; padding: 5px; margin: 5px 0;'><option value='BCA'>BCA</option><option value='BBA'>BBA</option><option value='BCom'>BCom</option></select><br>";
echo "Session: <input type='text' name='session' value='2024-2025' required style='width: 100%; padding: 5px; margin: 5px 0;'><br>";
echo "Last Qualification: <input type='text' name='lastQualification' value='12th' required style='width: 100%; padding: 5px; margin: 5px 0;'><br>";
echo "Percentage: <input type='text' name='percentage' value='85' required style='width: 100%; padding: 5px; margin: 5px 0;'><br>";
echo "Passing Year: <input type='number' name='passingYear' value='2023' required style='width: 100%; padding: 5px; margin: 5px 0;'><br>";
echo "Board: <input type='text' name='board' value='CBSE' required style='width: 100%; padding: 5px; margin: 5px 0;'><br>";
echo "Address: <input type='text' name='address' value='123 Test St' required style='width: 100%; padding: 5px; margin: 5px 0;'><br>";
echo "City: <input type='text' name='city' value='Test City' required style='width: 100%; padding: 5px; margin: 5px 0;'><br>";
echo "State: <input type='text' name='state' value='Test State' required style='width: 100%; padding: 5px; margin: 5px 0;'><br>";
echo "Pincode: <input type='text' name='pincode' value='123456' required style='width: 100%; padding: 5px; margin: 5px 0;'><br>";
echo "Guardian Name: <input type='text' name='guardianName' value='Test Guardian' required style='width: 100%; padding: 5px; margin: 5px 0;'><br>";
echo "Guardian Phone: <input type='text' name='guardianPhone' value='0987654321' required style='width: 100%; padding: 5px; margin: 5px 0;'><br>";
echo "<button type='submit' style='background: #007bff; color: white; padding: 10px 20px; border: none; cursor: pointer; margin-top: 10px;'>Submit Test Application</button>";
echo "</form>";

echo "<br><a href='test_admission_submit.php'>Refresh Page</a>";

$mysqli->close();
?>
