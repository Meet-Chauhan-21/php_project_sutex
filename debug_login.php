<?php
echo "=== Login Debug Script ===\n";

// Test database connection
echo "1. Testing database connection...\n";
try {
    $mysqli = new mysqli('localhost', 'root', '', 'vidhyaguru_db', 3306);
    if ($mysqli->connect_errno) {
        echo "   ❌ Connection failed: " . $mysqli->connect_error . "\n";
    } else {
        echo "   ✅ Database connection successful\n";
        $mysqli->set_charset('utf8mb4');
        
        // Check if users table exists
        $result = $mysqli->query("SHOW TABLES LIKE 'users'");
        if ($result->num_rows > 0) {
            echo "   ✅ Users table exists\n";
            
            // Count users
            $result = $mysqli->query("SELECT COUNT(*) as count FROM users");
            $row = $result->fetch_assoc();
            echo "   📊 Total users: " . $row['count'] . "\n";
            
            // Show sample users (email only for privacy)
            echo "   📋 Sample users:\n";
            $result = $mysqli->query("SELECT id, email, first_name FROM users LIMIT 3");
            while ($user = $result->fetch_assoc()) {
                echo "      - ID: {$user['id']}, Email: {$user['email']}, Name: {$user['first_name']}\n";
            }
        } else {
            echo "   ❌ Users table does not exist\n";
        }
    }
} catch (Exception $e) {
    echo "   ❌ Database error: " . $e->getMessage() . "\n";
}

echo "\n2. Testing login endpoint directly...\n";

// Test login with a sample request
$test_data = [
    'email' => 'test@example.com',
    'password' => 'testpassword'
];

echo "   📤 Sending test login request...\n";
echo "   📧 Email: " . $test_data['email'] . "\n";

// Simulate the login process
$_SERVER['REQUEST_METHOD'] = 'POST';
$_POST = $test_data;

// Capture output
ob_start();
include 'php/login_user.php';
$output = ob_get_clean();

echo "   📥 Response: " . $output . "\n";

?>
