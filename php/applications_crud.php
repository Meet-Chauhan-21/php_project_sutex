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
    $res = $mysqli->query("SELECT * FROM applications ORDER BY submitted_at DESC");
    while ($row = $res->fetch_assoc()) { $data[] = $row; }
    echo json_encode(['success' => true, 'data' => $data]);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

if ($method === 'POST') {
    $stmt = $mysqli->prepare("INSERT INTO applications (user_id, full_name, email, phone, program_applied, status) VALUES (?, ?, ?, ?, ?, ?)");
    $user_id = $input['user_id'] ?? null;
    $full_name = $input['full_name'] ?? '';
    $email = $input['email'] ?? '';
    $phone = $input['phone'] ?? '';
    $program = $input['program_applied'] ?? '';
    $status = $input['status'] ?? 'pending';
    $stmt->bind_param('isssss', $user_id, $full_name, $email, $phone, $program, $status);
    $stmt->execute();
    $id = $stmt->insert_id;
    $stmt->close();

    log_action($mysqli, $admin, 'insert', 'applications', $id, json_encode($input));

    echo json_encode(['success' => true, 'id' => $id]);
    exit;
}

if ($method === 'PUT') {
    parse_str($_SERVER['QUERY_STRING'] ?? '', $query);
    $id = (int)($query['id'] ?? 0);
    if ($id <= 0) { http_response_code(400); echo json_encode(['success' => false, 'error' => 'Missing id']); exit; }

    $fields = ['user_id','full_name','email','phone','program_applied','status'];
    $set = [];
    $values = [];
    $types = '';
    foreach ($fields as $f) {
        if (isset($input[$f])) {
            $set[] = "$f = ?";
            $values[] = $input[$f];
            $types .= is_int($input[$f]) ? 'i' : 's';
        }
    }
    if (empty($set)) { echo json_encode(['success' => false, 'error' => 'No fields to update']); exit; }
    $sql = "UPDATE applications SET " . implode(', ', $set) . " WHERE id = ?";
    $types .= 'i';
    $values[] = $id;

    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param($types, ...$values);
    $stmt->execute();
    $stmt->close();

    // If status is being updated to 'approved', update the user's program field
    if (isset($input['status']) && $input['status'] === 'approved') {
        // Get the application details to find user_id and program
        $app_stmt = $mysqli->prepare("SELECT user_id, program_applied FROM applications WHERE id = ?");
        $app_stmt->bind_param('i', $id);
        $app_stmt->execute();
        $app_result = $app_stmt->get_result();
        
        if ($app_row = $app_result->fetch_assoc()) {
            $user_id = $app_row['user_id'];
            $program_applied = $app_row['program_applied'];
            
            if ($user_id) {
                // Update user's program field
                $user_stmt = $mysqli->prepare("UPDATE users SET program = ? WHERE id = ?");
                $user_stmt->bind_param('si', $program_applied, $user_id);
                $user_stmt->execute();
                $user_stmt->close();
            }
        }
        $app_stmt->close();
    }

    log_action($mysqli, $admin, 'update', 'applications', $id, json_encode($input));

    echo json_encode(['success' => true]);
    exit;
}

if ($method === 'DELETE') {
    parse_str($_SERVER['QUERY_STRING'] ?? '', $query);
    $id = (int)($query['id'] ?? 0);
    if ($id <= 0) { http_response_code(400); echo json_encode(['success' => false, 'error' => 'Missing id']); exit; }

    $stmt = $mysqli->prepare("DELETE FROM applications WHERE id = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $stmt->close();

    log_action($mysqli, $admin, 'delete', 'applications', $id, '');

    echo json_encode(['success' => true]);
    exit;
}

http_response_code(405);
echo json_encode(['success' => false, 'error' => 'Method not allowed']);
