<?php
session_start();
require_once 'google_config.php';
require_once 'config.php';

// Function to make HTTP requests
function makeHttpRequest($url, $data = null) {
    $context = stream_context_create([
        'http' => [
            'method' => $data ? 'POST' : 'GET',
            'header' => 'Content-Type: application/x-www-form-urlencoded',
            'content' => $data ? http_build_query($data) : null,
            'timeout' => 30
        ]
    ]);
    
    $result = file_get_contents($url, false, $context);
    if ($result === false) {
        return false;
    }
    
    return json_decode($result, true);
}

// Check if we have an authorization code
if (isset($_GET['code'])) {
    $code = $_GET['code'];
    
    // Exchange authorization code for access token
    $token_data = [
        'client_id' => GOOGLE_CLIENT_ID,
        'client_secret' => GOOGLE_CLIENT_SECRET,
        'code' => $code,
        'grant_type' => 'authorization_code',
        'redirect_uri' => GOOGLE_REDIRECT_URI
    ];
    
    $token_response = makeHttpRequest(GOOGLE_TOKEN_URL, $token_data);
    
    if ($token_response && isset($token_response['access_token'])) {
        $access_token = $token_response['access_token'];
        
        // Get user information from Google
        $user_info_url = GOOGLE_USERINFO_URL . '?access_token=' . $access_token;
        $user_info = makeHttpRequest($user_info_url);
        
        if ($user_info && isset($user_info['email'])) {
            // Database connection
            $conn = new mysqli($servername, $username, $password, $dbname);
            
            if ($conn->connect_error) {
                die("Connection failed: " . $conn->connect_error);
            }
            
            $email = $user_info['email'];
            $google_id = $user_info['id'];
            $given_name = isset($user_info['given_name']) ? $user_info['given_name'] : '';
            $family_name = isset($user_info['family_name']) ? $user_info['family_name'] : '';
            $picture = isset($user_info['picture']) ? $user_info['picture'] : '';
            $full_name = trim($given_name . ' ' . $family_name);
            
            // Check if user exists
            $stmt = $conn->prepare("SELECT * FROM users WHERE email = ? OR google_id = ?");
            $stmt->bind_param("ss", $email, $google_id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                // User exists, update their information and log them in
                $user = $result->fetch_assoc();
                $user_id = $user['id'];
                
                // Update Google information
                $update_stmt = $conn->prepare("UPDATE users SET google_id = ?, given_name = ?, family_name = ?, picture = ? WHERE id = ?");
                $update_stmt->bind_param("ssssi", $google_id, $given_name, $family_name, $picture, $user_id);
                $update_stmt->execute();
                $update_stmt->close();
                
                // Set session variables
                $_SESSION['user_id'] = $user_id;
                $_SESSION['username'] = $user['username'];
                $_SESSION['email'] = $email;
                $_SESSION['full_name'] = $user['full_name'] ?: $full_name;
                $_SESSION['login_method'] = 'google';
                
            } else {
                // New user, create account
                $username = strtolower(str_replace(' ', '', $given_name . $family_name));
                
                // Make sure username is unique
                $check_stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
                $check_stmt->bind_param("s", $username);
                $check_stmt->execute();
                $check_result = $check_stmt->get_result();
                
                if ($check_result->num_rows > 0) {
                    $username = $username . rand(1000, 9999);
                }
                $check_stmt->close();
                
                // Insert new user
                $insert_stmt = $conn->prepare("INSERT INTO users (username, email, full_name, google_id, given_name, family_name, picture, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())");
                $insert_stmt->bind_param("sssssss", $username, $email, $full_name, $google_id, $given_name, $family_name, $picture);
                
                if ($insert_stmt->execute()) {
                    $user_id = $conn->insert_id;
                    
                    // Set session variables
                    $_SESSION['user_id'] = $user_id;
                    $_SESSION['username'] = $username;
                    $_SESSION['email'] = $email;
                    $_SESSION['full_name'] = $full_name;
                    $_SESSION['login_method'] = 'google';
                } else {
                    echo "Error creating account: " . $conn->error;
                    exit;
                }
                $insert_stmt->close();
            }
            
            $stmt->close();
            $conn->close();
            
            // Redirect to profile page
            header("Location: ../profile.html");
            exit;
            
        } else {
            echo "Error: Unable to get user information from Google";
            exit;
        }
    } else {
        echo "Error: Unable to get access token";
        exit;
    }
} else if (isset($_GET['error'])) {
    echo "Error: " . $_GET['error'];
    if (isset($_GET['error_description'])) {
        echo " - " . $_GET['error_description'];
    }
    exit;
} else {
    echo "Error: No authorization code received";
    exit;
}
?>
