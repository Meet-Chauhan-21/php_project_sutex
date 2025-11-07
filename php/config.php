<?php
// Database configuration
$DB_HOST = 'localhost';
$DB_USER = 'root';
$DB_PASS = '';
$DB_NAME = 'vidhyaguru_db';
$DB_PORT = 3306;

$mysqli = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME, $DB_PORT);

if ($mysqli->connect_errno) {
    http_response_code(500);
    die(json_encode([
        'success' => false,
        'error' => 'Database connection failed: ' . $mysqli->connect_error
    ]));
}

$mysqli->set_charset('utf8mb4');
?>
