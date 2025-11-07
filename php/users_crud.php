<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/auth_middleware.php';
require_admin();

header('Content-Type: application/json');
$method = $_SERVER['REQUEST_METHOD'];

function log_action($mysqli, $admin, $action, $table, $id, $details) {
    $stmt = $mysqli->prepare("INSERT INTO audit_logs (admin_username, action, target_table, target_id, details) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param('sssis', $admin, $action, $table, $id, $details);
    $stmt->execute();
    $stmt->close();
}

$admin = $_SESSION['admin']['username'];

if ($method === 'GET') {
    $data = [];
    $res = $mysqli->query("SELECT id, first_name, last_name, email, phone, program, registration_date FROM users ORDER BY registration_date DESC");
    while ($row = $res->fetch_assoc()) { $data[] = $row; }
    echo json_encode(['success' => true, 'data' => $data]);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

if ($method === 'POST') {
    $stmt = $mysqli->prepare("INSERT INTO users (first_name, last_name, email, phone, program, registration_date) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param('ssssss', $input['first_name'], $input['last_name'], $input['email'], $input['phone'], $input['program'], $input['registration_date']);
    $stmt->execute();
    $id = $stmt->insert_id;
    $stmt->close();

    log_action($mysqli, $admin, 'insert', 'users', $id, json_encode($input));

    echo json_encode(['success' => true, 'id' => $id]);
    exit;
}

if ($method === 'PUT') {
    parse_str($_SERVER['QUERY_STRING'] ?? '', $query);
    $id = (int)($query['id'] ?? 0);
    if ($id <= 0) { http_response_code(400); echo json_encode(['success' => false, 'error' => 'Missing id']); exit; }

    $fields = ['first_name','last_name','email','phone','program','registration_date'];
    $set = [];
    $values = [];
    $types = '';
    foreach ($fields as $f) {
        if (isset($input[$f])) {
            $set[] = "$f = ?";
            $values[] = $input[$f];
            $types .= 's';
        }
    }
    if (empty($set)) { echo json_encode(['success' => false, 'error' => 'No fields to update']); exit; }
    $sql = "UPDATE users SET " . implode(', ', $set) . " WHERE id = ?";
    $types .= 'i';
    $values[] = $id;

    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param($types, ...$values);
    $stmt->execute();
    $stmt->close();

    log_action($mysqli, $admin, 'update', 'users', $id, json_encode($input));

    echo json_encode(['success' => true]);
    exit;
}

if ($method === 'DELETE') {
    parse_str($_SERVER['QUERY_STRING'] ?? '', $query);
    $id = (int)($query['id'] ?? 0);
    if ($id <= 0) { http_response_code(400); echo json_encode(['success' => false, 'error' => 'Missing id']); exit; }

    $stmt = $mysqli->prepare("DELETE FROM users WHERE id = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $stmt->close();

    log_action($mysqli, $admin, 'delete', 'users', $id, '');

    echo json_encode(['success' => true]);
    exit;
}

http_response_code(405);
echo json_encode(['success' => false, 'error' => 'Method not allowed']);
