<?php
echo "Creating vidhyaguru_db database...\n";

// Connect to MySQL without specifying a database
$mysqli = new mysqli('localhost', 'root', '', '', 3306);

if ($mysqli->connect_errno) {
    die("Connection failed: " . $mysqli->connect_error . "\n");
}

echo "✅ Connected to MySQL\n";

// Create database if it doesn't exist
$sql = "CREATE DATABASE IF NOT EXISTS vidhyaguru_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";

if ($mysqli->query($sql)) {
    echo "✅ Database 'vidhyaguru_db' created or already exists\n";
} else {
    die("❌ Error creating database: " . $mysqli->error . "\n");
}

// Select the database
$mysqli->select_db('vidhyaguru_db');

// Create users table
$sql = "CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    date_of_birth DATE,
    gender ENUM('male', 'female', 'other'),
    address TEXT,
    city VARCHAR(100),
    state VARCHAR(100),
    pincode VARCHAR(10),
    profile_image VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email)
)";

if ($mysqli->query($sql)) {
    echo "✅ Users table created or already exists\n";
} else {
    echo "❌ Error creating users table: " . $mysqli->error . "\n";
}

// Create applications table
$sql = "CREATE TABLE IF NOT EXISTS applications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    program VARCHAR(100) NOT NULL,
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    documents JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_status (status)
)";

if ($mysqli->query($sql)) {
    echo "✅ Applications table created or already exists\n";
} else {
    echo "❌ Error creating applications table: " . $mysqli->error . "\n";
}

// Create admin_users table
$sql = "CREATE TABLE IF NOT EXISTS admin_users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    role ENUM('admin', 'super_admin') DEFAULT 'admin',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_username (username),
    INDEX idx_email (email)
)";

if ($mysqli->query($sql)) {
    echo "✅ Admin users table created or already exists\n";
} else {
    echo "❌ Error creating admin_users table: " . $mysqli->error . "\n";
}

// Create a test user account
$email = 'test@example.com';
$password = 'testpassword';
$password_hash = password_hash($password, PASSWORD_DEFAULT);

$stmt = $mysqli->prepare("INSERT IGNORE INTO users (first_name, last_name, email, password_hash) VALUES (?, ?, ?, ?)");
$first_name = 'Test';
$last_name = 'User';
$stmt->bind_param('ssss', $first_name, $last_name, $email, $password_hash);

if ($stmt->execute()) {
    if ($stmt->affected_rows > 0) {
        echo "✅ Test user created (email: test@example.com, password: testpassword)\n";
    } else {
        echo "ℹ️ Test user already exists\n";
    }
} else {
    echo "❌ Error creating test user: " . $stmt->error . "\n";
}

$stmt->close();

// Show final status
$result = $mysqli->query("SELECT COUNT(*) as count FROM users");
$row = $result->fetch_assoc();
echo "\n📊 Total users in database: " . $row['count'] . "\n";

echo "\n✅ Database setup complete!\n";
echo "Database name: vidhyaguru_db\n";
echo "Test login: test@example.com / testpassword\n";

$mysqli->close();
?>
