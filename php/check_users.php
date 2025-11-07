<?php
require_once 'config.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

try {
    // Get all users
    $stmt = $mysqli->prepare("SELECT id, first_name, last_name, email, phone, registration_date, password_hash IS NOT NULL as has_password FROM users ORDER BY id DESC");
    $stmt->execute();
    $result = $stmt->get_result();
    
    $users = [];
    while ($row = $result->fetch_assoc()) {
        $users[] = $row;
    }
    
    $stmt->close();
    
    // Get user count
    $countResult = $mysqli->query("SELECT COUNT(*) as total FROM users");
    $total = $countResult->fetch_assoc()['total'];
    
    echo json_encode([
        'success' => true,
        'total_users' => $total,
        'users' => $users
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Database error: ' . $e->getMessage()
    ]);
}
?>
