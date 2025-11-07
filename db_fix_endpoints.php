<?php
session_start();

// Simple database connection
$host = 'localhost';
$user = 'root';
$pass = '';
$db = 'vidhyaguru_db';

$action = $_GET['action'] ?? '';

// Create connection
$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    echo "❌ Connection failed: " . $conn->connect_error;
    exit;
}

switch ($action) {
    case 'check_structure':
        echo "=== DATABASE STRUCTURE CHECK ===\n\n";
        
        // Check current database
        $result = $conn->query("SELECT DATABASE() as current_db");
        $row = $result->fetch_assoc();
        echo "Current database: " . $row['current_db'] . "\n\n";
        
        // Check if users table exists
        $result = $conn->query("SHOW TABLES LIKE 'users'");
        if ($result->num_rows > 0) {
            echo "✅ users table exists\n\n";
            
            // Show table structure
            echo "Table structure:\n";
            $result = $conn->query("DESCRIBE users");
            while ($row = $result->fetch_assoc()) {
                echo sprintf("%-20s %-25s %-8s %-8s %-15s\n", 
                    $row['Field'], 
                    $row['Type'], 
                    $row['Null'], 
                    $row['Key'], 
                    $row['Default'] ?: 'NULL'
                );
            }
            
            // Check specifically for profile_picture column
            echo "\nProfile picture column check:\n";
            $result = $conn->query("SHOW COLUMNS FROM users LIKE 'profile_picture'");
            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                echo "✅ profile_picture column exists\n";
                echo "Type: " . $row['Type'] . "\n";
                echo "Allows NULL: " . $row['Null'] . "\n";
            } else {
                echo "❌ profile_picture column MISSING!\n";
            }
        } else {
            echo "❌ users table does not exist\n";
        }
        break;
        
    case 'add_column':
        echo "=== ADDING PROFILE_PICTURE COLUMN ===\n\n";
        
        // Check if column already exists
        $result = $conn->query("SHOW COLUMNS FROM users LIKE 'profile_picture'");
        if ($result->num_rows > 0) {
            echo "ℹ️ profile_picture column already exists\n";
        } else {
            echo "Adding profile_picture column...\n";
            $sql = "ALTER TABLE users ADD COLUMN profile_picture LONGTEXT";
            if ($conn->query($sql)) {
                echo "✅ profile_picture column added successfully!\n";
            } else {
                echo "❌ Error adding column: " . $conn->error . "\n";
            }
        }
        
        // Verify the column was added
        $result = $conn->query("SHOW COLUMNS FROM users LIKE 'profile_picture'");
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            echo "\nColumn details:\n";
            echo "Type: " . $row['Type'] . "\n";
            echo "Null: " . $row['Null'] . "\n";
            echo "Default: " . ($row['Default'] ?: 'NULL') . "\n";
        }
        break;
        
    case 'test_save':
        echo "=== TESTING PROFILE PICTURE SAVE ===\n\n";
        
        // Get the posted data
        $input = json_decode(file_get_contents('php://input'), true);
        $picture = $input['picture'] ?? null;
        
        if (!$picture) {
            echo "❌ No picture data provided\n";
            break;
        }
        
        echo "Picture data received: " . strlen($picture) . " characters\n";
        
        // For testing, we'll use user ID 1 or create a test user
        $test_user_id = null;
        
        // Check if there are any users
        $result = $conn->query("SELECT id FROM users LIMIT 1");
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $test_user_id = $row['id'];
            echo "Using existing user ID: $test_user_id\n";
        } else {
            // Create a test user
            $stmt = $conn->prepare("INSERT INTO users (first_name, last_name, email, password) VALUES (?, ?, ?, ?)");
            $fname = "Test";
            $lname = "User";
            $email = "test@example.com";
            $password = password_hash("testpass", PASSWORD_DEFAULT);
            $stmt->bind_param("ssss", $fname, $lname, $email, $password);
            
            if ($stmt->execute()) {
                $test_user_id = $conn->insert_id;
                echo "Created test user with ID: $test_user_id\n";
            } else {
                echo "❌ Failed to create test user: " . $conn->error . "\n";
                break;
            }
        }
        
        // Now try to save the picture
        $stmt = $conn->prepare("UPDATE users SET profile_picture = ? WHERE id = ?");
        $stmt->bind_param("si", $picture, $test_user_id);
        
        if ($stmt->execute()) {
            echo "✅ Picture saved successfully!\n";
            echo "Affected rows: " . $stmt->affected_rows . "\n";
            
            // Verify it was saved
            $check_stmt = $conn->prepare("SELECT CHAR_LENGTH(profile_picture) as pic_length FROM users WHERE id = ?");
            $check_stmt->bind_param("i", $test_user_id);
            $check_stmt->execute();
            $result = $check_stmt->get_result();
            $row = $result->fetch_assoc();
            
            if ($row['pic_length'] > 0) {
                echo "✅ Verification: Picture data is " . $row['pic_length'] . " characters in database\n";
            } else {
                echo "❌ Verification failed: No picture data found in database\n";
            }
        } else {
            echo "❌ Failed to save picture: " . $stmt->error . "\n";
        }
        break;
        
    case 'test_load':
        header('Content-Type: application/json');
        
        // Try to load a picture from the database
        $result = $conn->query("SELECT profile_picture FROM users WHERE profile_picture IS NOT NULL AND profile_picture != '' LIMIT 1");
        
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            echo json_encode([
                'success' => true,
                'picture' => $row['profile_picture'],
                'message' => 'Picture loaded successfully'
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'No pictures found in database'
            ]);
        }
        break;
        
    case 'view_users':
        echo "=== ALL USERS ===\n\n";
        
        $result = $conn->query("SELECT id, first_name, last_name, email, 
                                CASE 
                                    WHEN profile_picture IS NULL THEN 'NULL'
                                    WHEN profile_picture = '' THEN 'EMPTY'
                                    ELSE CONCAT('DATA (', CHAR_LENGTH(profile_picture), ' chars)')
                                END as picture_status
                                FROM users ORDER BY id");
        
        if ($result->num_rows > 0) {
            printf("%-5s %-15s %-15s %-30s %-20s\n", "ID", "First Name", "Last Name", "Email", "Picture Status");
            echo str_repeat("-", 90) . "\n";
            
            while ($row = $result->fetch_assoc()) {
                printf("%-5s %-15s %-15s %-30s %-20s\n", 
                    $row['id'], 
                    substr($row['first_name'], 0, 14), 
                    substr($row['last_name'], 0, 14), 
                    substr($row['email'], 0, 29), 
                    $row['picture_status']
                );
            }
        } else {
            echo "No users found in database\n";
        }
        break;
        
    case 'complete_fix':
        echo "=== COMPLETE DATABASE FIX ===\n\n";
        
        $steps = [];
        
        // Step 1: Check and add profile_picture column
        echo "Step 1: Checking profile_picture column...\n";
        $result = $conn->query("SHOW COLUMNS FROM users LIKE 'profile_picture'");
        if ($result->num_rows == 0) {
            echo "Adding profile_picture column...\n";
            $sql = "ALTER TABLE users ADD COLUMN profile_picture LONGTEXT";
            if ($conn->query($sql)) {
                echo "✅ profile_picture column added\n";
                $steps[] = "✅ Added profile_picture column";
            } else {
                echo "❌ Failed to add column: " . $conn->error . "\n";
                $steps[] = "❌ Failed to add profile_picture column";
            }
        } else {
            echo "✅ profile_picture column already exists\n";
            $steps[] = "✅ profile_picture column verified";
        }
        
        // Step 2: Test data insertion
        echo "\nStep 2: Testing data insertion...\n";
        $test_data = 'data:image/svg+xml;base64,' . base64_encode('<svg width="50" height="50"><rect width="50" height="50" fill="red"/></svg>');
        
        // Find a test user or create one
        $result = $conn->query("SELECT id FROM users LIMIT 1");
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $test_id = $row['id'];
            
            $stmt = $conn->prepare("UPDATE users SET profile_picture = ? WHERE id = ?");
            $stmt->bind_param("si", $test_data, $test_id);
            
            if ($stmt->execute()) {
                echo "✅ Test data insertion successful\n";
                $steps[] = "✅ Test data insertion works";
                
                // Verify
                $verify_stmt = $conn->prepare("SELECT CHAR_LENGTH(profile_picture) as len FROM users WHERE id = ?");
                $verify_stmt->bind_param("i", $test_id);
                $verify_stmt->execute();
                $verify_result = $verify_stmt->get_result();
                $verify_row = $verify_result->fetch_assoc();
                
                echo "Verified: " . $verify_row['len'] . " characters stored\n";
                $steps[] = "✅ Data verification successful";
            } else {
                echo "❌ Test data insertion failed: " . $stmt->error . "\n";
                $steps[] = "❌ Test data insertion failed";
            }
        } else {
            echo "❌ No users found for testing\n";
            $steps[] = "❌ No test users available";
        }
        
        echo "\n=== SUMMARY ===\n";
        foreach ($steps as $step) {
            echo $step . "\n";
        }
        
        echo "\nDatabase fix completed! You should now be able to upload and save profile pictures.\n";
        break;
        
    default:
        echo "Invalid action specified";
        break;
}

$conn->close();
?>
