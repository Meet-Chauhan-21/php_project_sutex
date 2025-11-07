<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/auth_middleware.php';
require_admin();

header('Content-Type: application/json');

$summary = [
    'users' => 0,
    'applications' => 0,
    'pending_applications' => 0,
    'approved_applications' => 0,
    'rejected_applications' => 0
];

$res = $mysqli->query("SELECT COUNT(*) FROM users");
$summary['users'] = (int) $res->fetch_row()[0];

$res = $mysqli->query("SELECT COUNT(*) FROM applications");
$summary['applications'] = (int) $res->fetch_row()[0];

$res = $mysqli->query("SELECT SUM(status='pending'), SUM(status='approved'), SUM(status='rejected') FROM applications");
list($pending, $approved, $rejected) = $res->fetch_row();
$summary['pending_applications'] = (int)$pending;
$summary['approved_applications'] = (int)$approved;
$summary['rejected_applications'] = (int)$rejected;

// Latest 20 applications
$apps = [];
$res = $mysqli->query("SELECT id, full_name, email, phone, program_applied, status, submitted_at FROM applications ORDER BY submitted_at DESC LIMIT 20");
while ($row = $res->fetch_assoc()) { $apps[] = $row; }

// Latest 20 users
$users = [];
$res = $mysqli->query("SELECT id, first_name, last_name, email, phone, program, registration_date, password_hash IS NOT NULL as has_password FROM users ORDER BY created_at DESC LIMIT 20");
while ($row = $res->fetch_assoc()) { 
    $row['has_password'] = $row['has_password'] ? 'Yes' : 'No';
    $users[] = $row; 
}

// Audit logs
$logs = [];
$res = $mysqli->query("SELECT id, admin_username, action, target_table, target_id, details, created_at FROM audit_logs ORDER BY created_at DESC LIMIT 50");
while ($row = $res->fetch_assoc()) { $logs[] = $row; }


echo json_encode([
    'success' => true,
    'summary' => $summary,
    'latestApplications' => $apps,
    'latestUsers' => $users,
    'auditLogs' => $logs
]);
