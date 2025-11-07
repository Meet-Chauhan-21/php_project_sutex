<?php
session_start();

function require_admin() {
    if (!isset($_SESSION['admin'])) {
        http_response_code(401);
        echo json_encode(['success' => false, 'error' => 'Admin authorization required']);
        exit;
    }
}

function require_user_auth() {
    if (!isset($_SESSION['user_id'])) {
        http_response_code(401);
        echo json_encode(['success' => false, 'error' => 'User authentication required']);
        exit;
    }
    return $_SESSION['user_id'];
}

function check_user_auth() {
    return isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
}

function get_current_user_id() {
    return $_SESSION['user_id'] ?? null;
}

function is_user_logged_in() {
    return isset($_SESSION['user_id']);
}

function is_admin_logged_in() {
    return isset($_SESSION['admin']);
}
