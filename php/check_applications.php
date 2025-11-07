<?php
require_once 'config.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

try {
    // Get all applications
    $stmt = $mysqli->prepare("SELECT id, user_id, full_name, email, phone, program_applied, status, submitted_at FROM applications ORDER BY submitted_at DESC");
    $stmt->execute();
    $result = $stmt->get_result();
    
    $applications = [];
    while ($row = $result->fetch_assoc()) {
        $applications[] = $row;
    }
    
    $stmt->close();
    
    // Get application count
    $countResult = $mysqli->query("SELECT COUNT(*) as total FROM applications");
    $total = $countResult->fetch_assoc()['total'];
    
    echo json_encode([
        'success' => true,
        'total_applications' => $total,
        'applications' => $applications
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Database error: ' . $e->getMessage()
    ]);
}
?>
