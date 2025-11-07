<?php
require_once __DIR__ . '/config.php';

header('Content-Type: application/json');

try {
    // Update the profile_picture column to handle larger base64 images
    $sql = "ALTER TABLE users MODIFY profile_picture LONGTEXT";
    
    if ($mysqli->query($sql)) {
        echo json_encode([
            'success' => true, 
            'message' => 'Profile picture column updated to LONGTEXT for larger images'
        ]);
    } else {
        echo json_encode([
            'success' => false, 
            'error' => 'Failed to update column: ' . $mysqli->error
        ]);
    }
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false, 
        'error' => 'Database error: ' . $e->getMessage()
    ]);
}
?>
