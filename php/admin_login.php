<?php
require_once __DIR__ . '/config.php';
session_start();

header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);
$username = trim($input['username'] ?? '');
$password = $input['password'] ?? '';

if ($username === '' || $password === '') {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Username and password are required']);
    exit;
}

$stmt = $mysqli->prepare("SELECT id, password_hash FROM admins WHERE username = ?");
$stmt->bind_param('s', $username);
$stmt->execute();
$stmt->bind_result($id, $password_hash);

if ($stmt->fetch() && password_verify($password, $password_hash)) {
    $_SESSION['admin'] = [
        'id' => $id,
        'username' => $username,
        'login_time' => time()
    ];
    echo json_encode(['success' => true, 'message' => 'Login successful']);
} else {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Invalid credentials']);
}

$stmt->close();
