<?php
require_once 'config.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

// Validate required fields
if (empty($input['email']) || empty($input['password'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Email and password are required']);
    exit;
}

$email = trim($input['email']);
$password = $input['password'];
$firstName = trim($input['firstName'] ?? '');
$lastName = trim($input['lastName'] ?? '');
$phone = trim($input['phone'] ?? '');

// Validate email format
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid email format']);
    exit;
}

try {
    // Check if user already exists
    $stmt = $mysqli->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        http_response_code(409);
        echo json_encode(['success' => false, 'error' => 'User with this email already exists']);
        exit;
    }
    $stmt->close();
    
    // Hash password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    
    // Insert new user
    $stmt = $mysqli->prepare("INSERT INTO users (first_name, last_name, email, phone, password_hash, registration_date) VALUES (?, ?, ?, ?, ?, NOW())");
    $stmt->bind_param('sssss', $firstName, $lastName, $email, $phone, $hashedPassword);
    
    if ($stmt->execute()) {
        $user_id = $stmt->insert_id;
        $stmt->close();
        
        echo json_encode([
            'success' => true, 
            'message' => 'User registered successfully!',
            'user_id' => $user_id
        ]);
    } else {
        throw new Exception('Failed to register user');
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
}
?>
