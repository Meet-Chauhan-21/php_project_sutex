<?php
session_start();

echo "=== Login Test ===\n";

// First, let's check if there are any users in the database
require_once 'php/config.php';

try {
    // Check database connection
    $stmt = $mysqli->prepare("SELECT COUNT(*) as user_count FROM users");
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    echo "Total users in database: " . $row['user_count'] . "\n";
    
    // Show all users (for debugging only)
    $stmt = $mysqli->prepare("SELECT id, first_name, last_name, email, program FROM users");
    $stmt->execute();
    $result = $stmt->get_result();
    
    echo "\nExisting users:\n";
    if ($result->num_rows > 0) {
        while ($user = $result->fetch_assoc()) {
            echo "ID: {$user['id']}, Name: {$user['first_name']} {$user['last_name']}, Email: {$user['email']}, Program: {$user['program']}\n";
        }
    } else {
        echo "No users found.\n";
        echo "You need to register first!\n";
    }
    
    // Test manual login (for debugging)
    if (isset($_GET['test_login']) && $_GET['test_login'] == '1') {
        if ($result->num_rows > 0) {
            $result = $mysqli->query("SELECT * FROM users LIMIT 1");
            $user = $result->fetch_assoc();
            
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];
            
            echo "\n✅ Test login successful for user: " . $_SESSION['user_name'] . "\n";
            echo "Session ID: " . session_id() . "\n";
            echo "Now try accessing the profile page!\n";
        }
    }
    
} catch (Exception $e) {
    echo "❌ Database error: " . $e->getMessage() . "\n";
}

if (!isset($_GET['test_login'])) {
    echo "\nTo test login, visit: http://localhost:8081/test_login.php?test_login=1\n";
}

echo "\nCurrent session:\n";
var_dump($_SESSION);
?>
