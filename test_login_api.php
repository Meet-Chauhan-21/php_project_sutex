<?php
echo "Testing Login API Endpoint\n";
echo "=========================\n\n";

// Test the login endpoint directly
$url = 'http://localhost:8081/php/login_user.php';
$data = [
    'email' => 'test@example.com',
    'password' => 'testpassword'
];

$options = [
    'http' => [
        'header' => "Content-type: application/json\r\n",
        'method' => 'POST',
        'content' => json_encode($data)
    ]
];

$context = stream_context_create($options);
$result = file_get_contents($url, false, $context);

echo "Request URL: $url\n";
echo "Request Data: " . json_encode($data) . "\n\n";

if ($result === FALSE) {
    echo "❌ Request failed\n";
    $error = error_get_last();
    echo "Error: " . $error['message'] . "\n";
} else {
    echo "✅ Response received:\n";
    echo $result . "\n\n";
    
    // Parse response
    $response = json_decode($result, true);
    if ($response) {
        if ($response['success']) {
            echo "✅ Login successful!\n";
            echo "User: " . $response['user']['name'] . "\n";
            echo "Email: " . $response['user']['email'] . "\n";
        } else {
            echo "❌ Login failed: " . $response['error'] . "\n";
        }
    } else {
        echo "❌ Invalid JSON response\n";
    }
}
?>
