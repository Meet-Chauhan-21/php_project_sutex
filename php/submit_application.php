<?php
require_once 'config.php';
require_once 'auth_middleware.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

// Require user authentication for admission applications
$user_id = require_user_auth();

$input = json_decode(file_get_contents('php://input'), true);

// Validate required fields
$required_fields = ['firstName', 'lastName', 'email', 'phone', 'program'];
foreach ($required_fields as $field) {
    if (empty($input[$field])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => "Missing required field: $field"]);
        exit;
    }
}

try {
    
    // Insert into applications table
    $stmt = $mysqli->prepare("INSERT INTO applications (user_id, full_name, email, phone, program_applied, status) VALUES (?, ?, ?, ?, ?, 'pending')");
    $full_name = trim($input['firstName']) . ' ' . trim($input['lastName']);
    $email = trim($input['email']);
    $phone = trim($input['phone']);
    $program = trim($input['program']);
    
    $stmt->bind_param('issss', $user_id, $full_name, $email, $phone, $program);
    
    if ($stmt->execute()) {
        $application_id = $stmt->insert_id;
        $stmt->close();
        
        // Update the user's profile with the applied program
        $update_stmt = $mysqli->prepare("UPDATE users SET program = ? WHERE id = ?");
        $update_stmt->bind_param('si', $program, $user_id);
        $update_stmt->execute();
        $update_stmt->close();
        
        // If user is not logged in, create a user record without password
        if (!$user_id) {
            $user_stmt = $mysqli->prepare("INSERT IGNORE INTO users (first_name, last_name, email, phone, program, registration_date) VALUES (?, ?, ?, ?, ?, NOW())");
            $user_stmt->bind_param('sssss', $input['firstName'], $input['lastName'], $email, $phone, $program);
            $user_stmt->execute();
            $user_stmt->close();
        }
        
        echo json_encode([
            'success' => true, 
            'message' => 'Application submitted successfully! Your profile has been updated with the selected program.',
            'application_id' => $application_id
        ]);
    } else {
        throw new Exception('Failed to insert application');
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
}
?>
