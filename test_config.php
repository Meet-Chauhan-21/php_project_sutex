<?php
// Quick test of the existing config.php connection
require_once 'php/config.php';

header('Content-Type: application/json');

try {
    // Test a simple query
    $result = $mysqli->query("SELECT COUNT(*) as count FROM users");
    
    if ($result) {
        $row = $result->fetch_assoc();
        echo json_encode([
            'success' => true,
            'message' => 'Database connection successful',
            'user_count' => $row['count'],
            'database' => 'vidyaguru_db'
        ]);
    } else {
        throw new Exception('Query failed: ' . $mysqli->error);
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>
