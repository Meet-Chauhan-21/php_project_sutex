<?php
ob_start();
session_start();
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

require_once __DIR__ . '/../config.php';
require_once 'google_config.php';

function sendResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    if (ob_get_length()) { ob_clean(); }
    echo json_encode($data);
    exit;
}

function verifyGoogleJWT($credential) {
    // Verify the JWT token with Google
    $url = 'https://oauth2.googleapis.com/tokeninfo?id_token=' . $credential;
    
    $context = stream_context_create([
        'http' => [
            'timeout' => 10
        ]
    ]);
    
    $response = @file_get_contents($url, false, $context);
    
    if ($response === false && function_exists('curl_init')) {
        // Fallback to cURL
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 10
        ]);
        $response = curl_exec($ch);
        curl_close($ch);
    }
    
    if ($response === false) {
        return false;
    }
    
    $payload = json_decode($response, true);
    
    // Verify the token is for our client
    if (!$payload || $payload['aud'] !== GOOGLE_CLIENT_ID) {
        return false;
    }
    
    return $payload;
}

try {
    // Get JSON input
    $raw = file_get_contents('php://input');
    $input = json_decode($raw, true);
    
    if (!$input || !isset($input['credential'])) {
        sendResponse([
            'success' => false,
            'error' => 'Invalid input - credential required'
        ], 400);
    }
    
    $credential = $input['credential'];
    
    // Verify the JWT token with Google
    $payload = verifyGoogleJWT($credential);
    
    if (!$payload) {
        sendResponse([
            'success' => false,
            'error' => 'Invalid Google credential'
        ], 401);
    }
    
    $google_id = $payload['sub'];
    $email = $payload['email'];
    $name = $payload['name'];
    $given_name = $payload['given_name'] ?? '';
    $family_name = $payload['family_name'] ?? '';
    $picture = $payload['picture'] ?? '';
    
    // Use PDO from config.php ($pdo)
    if (!isset($pdo)) {
        sendResponse([
            'success' => false,
            'error' => 'Database not initialized'
        ], 500);
    }
    
    // Check if user exists with this Google ID or email
    $stmt = $pdo->prepare("SELECT * FROM users WHERE google_id = ? OR email = ?");
    $stmt->execute([$google_id, $email]);
    $existing_user = $stmt->fetch();
    
    $username_current = null;
    $full_name_current = null;
    if ($existing_user) {
        // User exists, update their information
        $user_id = $existing_user['id'];
        $username_current = $existing_user['username'] ?? null;
        $full_name_current = $existing_user['full_name'] ?? trim(($given_name ?: '') . ' ' . ($family_name ?: ''));
        
        $update_stmt = $pdo->prepare("
            UPDATE users 
            SET google_id = ?, given_name = ?, family_name = ?, picture = ?, updated_at = NOW()
            WHERE id = ?
        ");
        $update_stmt->execute([$google_id, $given_name, $family_name, $picture, $user_id]);
        
        $message = 'Welcome back, ' . $name . '!';
    } else {
        // Create new user
        $new_username = strtolower(str_replace(' ', '', trim(($given_name ?: '') . ($family_name ?: ''))));
        if ($new_username === '') { $new_username = 'user'; }
        
        // Make sure username is unique
        $check_stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
        $check_stmt->execute([$new_username]);
        $exists = $check_stmt->fetch();
        if ($exists) {
            $new_username = $new_username . rand(1000, 9999);
        }
        
        // Insert new user
        $insert_stmt = $pdo->prepare("
            INSERT INTO users (username, email, full_name, google_id, given_name, family_name, picture, created_at) 
            VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
        ");
        $full_name_current = trim(($given_name ?: '') . ' ' . ($family_name ?: ''));
        $username_current = $new_username;
        $insert_stmt->execute([$username_current, $email, $full_name_current, $google_id, $given_name, $family_name, $picture]);
        $user_id = (int)$pdo->lastInsertId();
        
        $message = 'Account created successfully! Welcome, ' . $name . '!';
    }

    // Set session variables
    $_SESSION['user_id'] = $user_id;
    $_SESSION['username'] = $username_current ?: '';
    $_SESSION['email'] = $email;
    $_SESSION['full_name'] = $full_name_current ?: '';
    $_SESSION['login_method'] = 'google';
    
    // Log the authentication
    error_log("Google authentication successful for user: $email (ID: $user_id)");
    
    sendResponse([
        'success' => true,
        'message' => $message,
        'user' => [
            'id' => $user_id,
            'email' => $email,
            'first_name' => $given_name,
            'last_name' => $family_name,
            'full_name' => $full_name_current,
            'picture' => $picture
        ]
    ]);
    
} catch (Exception $e) {
    error_log("Error in google_auth.php: " . $e->getMessage());
    sendResponse([
        'success' => false,
        'error' => 'Authentication failed'
    ], 500);
}
?>
