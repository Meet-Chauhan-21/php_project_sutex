<?php
require_once 'config.php';

session_start();
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'User not logged in']);
    exit;
}

$user_id = $_SESSION['user_id'];
$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid JSON data']);
    exit;
}

try {
    // Get current user data
    $stmt = $mysqli->prepare("SELECT additional_info FROM users WHERE id = ?");
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $currentUser = $result->fetch_assoc();
    $stmt->close();
    
    // Parse existing additional_info
    $additionalInfo = [];
    if ($currentUser['additional_info']) {
        $additionalInfo = json_decode($currentUser['additional_info'], true);
    }
    
    // Update basic fields if provided
    $updateFields = [];
    $updateValues = [];
    $updateTypes = '';
    
    if (isset($input['firstName'])) {
        $updateFields[] = 'first_name = ?';
        $updateValues[] = trim($input['firstName']);
        $updateTypes .= 's';
    }
    
    if (isset($input['lastName'])) {
        $updateFields[] = 'last_name = ?';
        $updateValues[] = trim($input['lastName']);
        $updateTypes .= 's';
    }
    
    if (isset($input['email'])) {
        // Check if email already exists (for other users)
        $emailCheckStmt = $mysqli->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $emailCheckStmt->bind_param('si', $input['email'], $user_id);
        $emailCheckStmt->execute();
        $emailResult = $emailCheckStmt->get_result();
        
        if ($emailResult->num_rows > 0) {
            http_response_code(409);
            echo json_encode(['success' => false, 'error' => 'Email already exists']);
            exit;
        }
        $emailCheckStmt->close();
        
        $updateFields[] = 'email = ?';
        $updateValues[] = trim($input['email']);
        $updateTypes .= 's';
    }
    
    if (isset($input['phone'])) {
        $updateFields[] = 'phone = ?';
        $updateValues[] = trim($input['phone']);
        $updateTypes .= 's';
    }
    
    if (isset($input['program'])) {
        $updateFields[] = 'program = ?';
        $updateValues[] = trim($input['program']);
        $updateTypes .= 's';
    }
    
    // Handle profile picture update
    if (isset($input['profile_picture'])) {
        $updateFields[] = 'profile_picture = ?';
        $updateValues[] = $input['profile_picture']; // This will be base64 encoded image data
        $updateTypes .= 's';
    }
    
    // Update additional_info fields
    if (isset($input['dateOfBirth'])) {
        $additionalInfo['dateOfBirth'] = $input['dateOfBirth'];
    }
    
    if (isset($input['gender'])) {
        $additionalInfo['gender'] = $input['gender'];
    }
    
    if (isset($input['address'])) {
        $additionalInfo['address'] = $input['address'];
    }
    
    if (isset($input['emergencyContact'])) {
        $additionalInfo['emergencyContact'] = $input['emergencyContact'];
    }
    
    if (isset($input['preferences'])) {
        $additionalInfo['preferences'] = $input['preferences'];
    }
    
    // Add additional_info to update
    $updateFields[] = 'additional_info = ?';
    $updateValues[] = json_encode($additionalInfo);
    $updateTypes .= 's';
    
    // Add user_id for WHERE clause
    $updateValues[] = $user_id;
    $updateTypes .= 'i';
    
    // Execute update
    if (!empty($updateFields)) {
        $sql = "UPDATE users SET " . implode(', ', $updateFields) . " WHERE id = ?";
        $stmt = $mysqli->prepare($sql);
        $stmt->bind_param($updateTypes, ...$updateValues);
        
        if ($stmt->execute()) {
            $stmt->close();
            
            echo json_encode([
                'success' => true,
                'message' => 'Profile updated successfully'
            ]);
        } else {
            throw new Exception('Failed to update profile');
        }
    } else {
        echo json_encode([
            'success' => true,
            'message' => 'No changes to update'
        ]);
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
}
?>
