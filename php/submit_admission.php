<?php
// Start with minimal error handling and clean output
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// Start session and set headers
session_start();
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Start output buffering to prevent any unwanted output
ob_start();

// Function to send JSON response and exit
function sendResponse($data) {
    ob_clean();
    echo json_encode($data);
    ob_end_flush();
    exit;
}

// Check request method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendResponse([
        'success' => false,
        'error' => 'Method not allowed. Please use POST request.'
    ]);
}

// Include config file with error handling
try {
    if (!file_exists('config.php')) {
        throw new Exception('Configuration file not found');
    }
    require_once 'config.php';
    
    if (!isset($mysqli) || $mysqli->connect_error) {
        throw new Exception('Database connection failed');
    }
} catch (Exception $e) {
    sendResponse([
        'success' => false,
        'error' => 'Database connection error. Please try again later.'
    ]);
}

// Check authentication - Allow submission without login
$user_id = null;
$user_email = null;

// Try to get user info from session first
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
}
if (isset($_SESSION['email'])) {
    $user_email = $_SESSION['email'];
}

// If not in session, try POST data
if (!$user_id && isset($_POST['userId'])) {
    $user_id = $_POST['userId'];
}
if (!$user_email && isset($_POST['userEmail'])) {
    $user_email = $_POST['userEmail'];
}

try {
    // Get form data with defaults
    $user_id = $_SESSION['user_id'] ?? ($_POST['userId'] ?? null);
    $user_email = $_SESSION['email'] ?? ($_POST['userEmail'] ?? null);
    
    // Get form fields with validation
    $required_fields = [
        'firstName' => $_POST['firstName'] ?? '',
        'lastName' => $_POST['lastName'] ?? '',
        'email' => $_POST['email'] ?? '',
        'phone' => $_POST['phone'] ?? '',
        'dob' => $_POST['dob'] ?? '',
        'gender' => $_POST['gender'] ?? '',
        'program' => $_POST['program'] ?? '',
        'session' => $_POST['session'] ?? '',
        'lastQualification' => $_POST['lastQualification'] ?? '',
        'percentage' => $_POST['percentage'] ?? '',
        'passingYear' => $_POST['passingYear'] ?? '',
        'board' => $_POST['board'] ?? '',
        'address' => $_POST['address'] ?? '',
        'city' => $_POST['city'] ?? '',
        'state' => $_POST['state'] ?? '',
        'pincode' => $_POST['pincode'] ?? '',
        'guardianName' => $_POST['guardianName'] ?? '',
        'guardianPhone' => $_POST['guardianPhone'] ?? ''
    ];
    
    // Check for missing required fields
    $missing = [];
    foreach ($required_fields as $field => $value) {
        if (empty(trim($value))) {
            $missing[] = $field;
        }
    }
    
    if (!empty($missing)) {
        sendResponse([
            'success' => false,
            'error' => 'Please fill in all required fields: ' . implode(', ', $missing)
        ]);
    }
    
    // Extract clean values
    $first_name = trim($required_fields['firstName']);
    $last_name = trim($required_fields['lastName']);
    $email = trim($required_fields['email']);
    $phone = trim($required_fields['phone']);
    $dob = $required_fields['dob'];
    $gender = strtolower(trim($required_fields['gender'])); // Convert to lowercase for database enum
    $program = $required_fields['program'];
    $session = $required_fields['session'];
    $last_qualification = $required_fields['lastQualification'];
    $percentage = trim($required_fields['percentage']);
    $passing_year = intval($required_fields['passingYear']);
    $board = trim($required_fields['board']);
    $address = trim($required_fields['address']);
    $city = trim($required_fields['city']);
    $state = trim($required_fields['state']);
    $pincode = trim($required_fields['pincode']);
    $guardian_name = trim($required_fields['guardianName']);
    $guardian_phone = trim($required_fields['guardianPhone']);
    
    $full_name = trim($first_name . ' ' . $last_name);

    // Simple validation
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        sendResponse([
            'success' => false,
            'error' => 'Please enter a valid email address.'
        ]);
    }
    
    if (!preg_match('/^[0-9]{6}$/', $pincode)) {
        sendResponse([
            'success' => false,
            'error' => 'Please enter a valid 6-digit PIN code.'
        ]);
    }
    
    // If we have email but no user_id, try to find user in database
    if (!$user_id && $user_email) {
        $user_stmt = $mysqli->prepare("SELECT id FROM users WHERE email = ?");
        if ($user_stmt) {
            $user_stmt->bind_param("s", $user_email);
            $user_stmt->execute();
            $user_result = $user_stmt->get_result();
            if ($user_result->num_rows > 0) {
                $user_data = $user_result->fetch_assoc();
                $user_id = $user_data['id'];
                $_SESSION['user_id'] = $user_id;
            }
            $user_stmt->close();
        }
    }
    
    // If still no user_id, check if user exists by form email
    if (!$user_id && $email) {
        $user_stmt = $mysqli->prepare("SELECT id FROM users WHERE email = ?");
        if ($user_stmt) {
            $user_stmt->bind_param("s", $email);
            $user_stmt->execute();
            $user_result = $user_stmt->get_result();
            if ($user_result->num_rows > 0) {
                $user_data = $user_result->fetch_assoc();
                $user_id = $user_data['id'];
            }
            $user_stmt->close();
        }
    }
    
    // If no user found, create a temporary user record or use NULL for user_id
    // For now, we'll allow NULL user_id for guest applications

    // Check for existing application (only if user_id exists)
    if ($user_id) {
        $check_stmt = $mysqli->prepare("SELECT id, status FROM applications WHERE user_id = ? AND program = ? AND status IN ('pending', 'approved')");
        if ($check_stmt) {
            $check_stmt->bind_param("is", $user_id, $program);
            $check_stmt->execute();
            $result = $check_stmt->get_result();
            
            if ($result->num_rows > 0) {
                $existing = $result->fetch_assoc();
                $check_stmt->close();
                sendResponse([
                    'success' => false,
                    'error' => "You already have a {$existing['status']} application for this program."
                ]);
            }
            $check_stmt->close();
        }
    } else {
        // For guest applications, check by email
        $check_stmt = $mysqli->prepare("SELECT id, status FROM applications WHERE email = ? AND program = ? AND status IN ('pending', 'approved')");
        if ($check_stmt) {
            $check_stmt->bind_param("ss", $email, $program);
            $check_stmt->execute();
            $result = $check_stmt->get_result();
            
            if ($result->num_rows > 0) {
                $existing = $result->fetch_assoc();
                $check_stmt->close();
                sendResponse([
                    'success' => false,
                    'error' => "An application with this email already exists for this program."
                ]);
            }
            $check_stmt->close();
        }
    }

    // Insert the application into the database
    // Log the data being inserted for debugging
    error_log("Attempting to insert application: user_id=$user_id, program=$program, email=$email, name=$full_name");
    
    $insert_stmt = $mysqli->prepare("
        INSERT INTO applications (
            user_id, first_name, last_name, full_name, email, phone, dob, gender,
            program_applied, program, session, last_qualification, percentage, 
            passing_year, board, address, city, state, pincode, 
            guardian_name, guardian_phone, status, submitted_at
        ) VALUES (
            ?, ?, ?, ?, ?, ?, ?, ?, 
            ?, ?, ?, ?, ?, 
            ?, ?, ?, ?, ?, ?, 
            ?, ?, 'pending', NOW()
        )
    ");
    
    if (!$insert_stmt) {
        error_log("MySQL prepare error: " . $mysqli->error);
        sendResponse([
            'success' => false,
            'error' => 'Database preparation error: ' . $mysqli->error
        ]);
    }

    $insert_stmt->bind_param(
        "issssssssssssisssssss",
        $user_id, $first_name, $last_name, $full_name, $email, $phone, $dob, $gender,
        $program, $program, $session, $last_qualification, $percentage,
        $passing_year, $board, $address, $city, $state, $pincode,
        $guardian_name, $guardian_phone
    );

    if ($insert_stmt->execute()) {
        $application_id = $mysqli->insert_id;
        $insert_stmt->close();

        // Log the successful submission
        error_log("Admission application submitted successfully - ID: $application_id, User: $user_id, Program: $program");

        sendResponse([
            'success' => true,
            'message' => 'Your admission application has been submitted successfully!',
            'application_id' => $application_id,
            'data' => [
                'id' => $application_id,
                'program' => $program,
                'session' => $session,
                'status' => 'pending',
                'full_name' => $full_name,
                'email' => $email
            ]
        ]);
    } else {
        $error_msg = $insert_stmt->error;
        error_log("MySQL execute error: " . $error_msg);
        $insert_stmt->close();
        sendResponse([
            'success' => false,
            'error' => 'Failed to save application: ' . $error_msg
        ]);
    }

} catch (Exception $e) {
    error_log("Error in submit_admission.php: " . $e->getMessage());
    sendResponse([
        'success' => false,
        'error' => 'An error occurred while processing your application. Please try again later.'
    ]);
}

?>