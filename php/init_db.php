<?php
require_once __DIR__ . '/config.php';

header('Content-Type: application/json');

// Create tables if not exists
$queries = [
    "CREATE TABLE IF NOT EXISTS admins (id INT AUTO_INCREMENT PRIMARY KEY, username VARCHAR(50) UNIQUE NOT NULL, password_hash VARCHAR(255) NOT NULL, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP)",
    "CREATE TABLE IF NOT EXISTS users (id INT AUTO_INCREMENT PRIMARY KEY, first_name VARCHAR(100), last_name VARCHAR(100), email VARCHAR(150) UNIQUE, phone VARCHAR(20), program VARCHAR(100), password_hash VARCHAR(255), registration_date DATETIME, additional_info JSON, profile_picture VARCHAR(255), created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP)",
    "CREATE TABLE IF NOT EXISTS applications (id INT AUTO_INCREMENT PRIMARY KEY, user_id INT NULL, full_name VARCHAR(200), email VARCHAR(150), phone VARCHAR(20), program_applied VARCHAR(100), status ENUM('pending','approved','rejected') DEFAULT 'pending', submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP, FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL)",
    "CREATE TABLE IF NOT EXISTS audit_logs (id INT AUTO_INCREMENT PRIMARY KEY, admin_username VARCHAR(50), action VARCHAR(50), target_table VARCHAR(50), target_id INT, details TEXT, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP)"
];

foreach ($queries as $sql) {
    if (!$mysqli->query($sql)) {
        echo json_encode(['success' => false, 'error' => $mysqli->error]);
        exit;
    }
}

// Seed default admin if not exists
$stmt = $mysqli->prepare("SELECT id FROM admins WHERE username = ?");
$username = 'mohit@gmail.com';
$stmt->bind_param('s', $username);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows === 0) {
    $hash = password_hash('Mohit123', PASSWORD_DEFAULT);
    $insert = $mysqli->prepare("INSERT INTO admins (username, password_hash) VALUES (?, ?)");
    $insert->bind_param('ss', $username, $hash);
    $insert->execute();
    $insert->close();
}

$stmt->close();

echo json_encode(['success' => true, 'message' => 'Database initialized and admin ready']);
