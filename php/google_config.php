<?php
// Google OAuth 2.0 Configuration
// Credentials from Google Cloud Console
// IMPORTANT: Add your credentials to google_credentials.php (not tracked in git)

// Include credentials file (create this file locally and add to .gitignore)
$credentials_file = __DIR__ . '/google_credentials.php';
if (file_exists($credentials_file)) {
    require_once $credentials_file;
} else {
    // Default/placeholder values - REPLACE THESE IN google_credentials.php
    define('GOOGLE_CLIENT_ID', 'YOUR_GOOGLE_CLIENT_ID');
    define('GOOGLE_CLIENT_SECRET', 'YOUR_GOOGLE_CLIENT_SECRET');
    define('GOOGLE_REDIRECT_URI', 'http://localhost:8081/php/google_callback.php');
}

// Google OAuth 2.0 Endpoints
define('GOOGLE_OAUTH_URL', 'https://accounts.google.com/o/oauth2/auth');
define('GOOGLE_TOKEN_URL', 'https://oauth2.googleapis.com/token');
define('GOOGLE_USERINFO_URL', 'https://www.googleapis.com/oauth2/v2/userinfo');

// Scopes - what information you want to access
define('GOOGLE_SCOPES', 'openid email profile');

// JWT verification endpoint
define('GOOGLE_JWT_VERIFY_URL', 'https://oauth2.googleapis.com/tokeninfo');
?>
