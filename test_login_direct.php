<?php
echo "Direct Login Test\n";
echo "================\n\n";

// Simulate POST request data
$_SERVER['REQUEST_METHOD'] = 'POST';
$_SERVER['CONTENT_TYPE'] = 'application/json';

// Test data
$test_email = 'test@example.com';
$test_password = 'testpassword';

echo "Testing login with:\n";
echo "Email: $test_email\n";
echo "Password: $test_password\n\n";

// Mock the input
$input_data = json_encode([
    'email' => $test_email,
    'password' => $test_password
]);

// Temporarily override php://input
file_put_contents('php://memory', $input_data);

// Start output buffering to capture the response
ob_start();

// Capture any headers that would be sent
$headers_sent = [];
function capture_header($string, $replace = true) {
    global $headers_sent;
    $headers_sent[] = $string;
    return $string;
}

// Override header function temporarily
header_register_callback('capture_header');

try {
    // Include the login script with proper input simulation
    $_POST = []; // Clear POST data
    
    // Set up the input stream
    $temp_file = tempnam(sys_get_temp_dir(), 'php_input');
    file_put_contents($temp_file, $input_data);
    
    // Mock file_get_contents('php://input')
    function mock_file_get_contents($filename) {
        global $input_data;
        if ($filename === 'php://input') {
            return $input_data;
        }
        return file_get_contents($filename);
    }
    
    // Let's just test the core logic directly
    require_once 'php/config.php';
    
    echo "✅ Config loaded successfully\n";
    echo "Database: " . $DB_NAME . "\n\n";
    
    // Test database connection
    if ($mysqli->connect_errno) {
        echo "❌ Database connection failed: " . $mysqli->connect_error . "\n";
        exit;
    } else {
        echo "✅ Database connected successfully\n";
    }
    
    // Test user lookup
    $stmt = $mysqli->prepare("SELECT id, first_name, last_name, email, password_hash FROM users WHERE email = ?");
    $stmt->bind_param('s', $test_email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        echo "❌ No user found with email: $test_email\n";
        
        // List all users for debugging
        echo "\nAll users in database:\n";
        $all_users = $mysqli->query("SELECT id, email, first_name FROM users");
        while ($user = $all_users->fetch_assoc()) {
            echo "  - ID: {$user['id']}, Email: {$user['email']}, Name: {$user['first_name']}\n";
        }
    } else {
        $user = $result->fetch_assoc();
        echo "✅ User found: {$user['first_name']} {$user['last_name']}\n";
        
        // Test password verification
        if (password_verify($test_password, $user['password_hash'])) {
            echo "✅ Password verification successful\n";
            echo "✅ Login would succeed\n";
        } else {
            echo "❌ Password verification failed\n";
            echo "Stored hash: " . substr($user['password_hash'], 0, 30) . "...\n";
        }
    }
    
    $stmt->close();
    
} catch (Exception $e) {
    echo "❌ Exception: " . $e->getMessage() . "\n";
}

$output = ob_get_clean();
echo $output;
?>
