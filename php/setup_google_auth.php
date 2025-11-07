<?php
require_once 'config.php';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "Adding Google authentication support to database...\n";
    
    // Add Google ID column to users table
    $pdo->exec("ALTER TABLE users ADD COLUMN IF NOT EXISTS google_id VARCHAR(255) UNIQUE");
    echo "✓ Added google_id column\n";
    
    // Add name columns for Google users
    $pdo->exec("ALTER TABLE users ADD COLUMN IF NOT EXISTS given_name VARCHAR(100)");
    $pdo->exec("ALTER TABLE users ADD COLUMN IF NOT EXISTS family_name VARCHAR(100)");
    echo "✓ Added name columns\n";
    
    // Add last login tracking
    $pdo->exec("ALTER TABLE users ADD COLUMN IF NOT EXISTS last_login TIMESTAMP NULL");
    echo "✓ Added last_login column\n";
    
    // Create user sessions table for tracking login sessions
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS user_sessions (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            session_token VARCHAR(255) UNIQUE NOT NULL,
            login_method ENUM('password', 'google', 'facebook') DEFAULT 'password',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            expires_at TIMESTAMP NULL,
            is_active BOOLEAN DEFAULT TRUE,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            INDEX idx_session_token (session_token),
            INDEX idx_user_id (user_id),
            INDEX idx_expires_at (expires_at)
        )
    ");
    echo "✓ Created user_sessions table\n";
    
    // Make password nullable for Google users
    $pdo->exec("ALTER TABLE users MODIFY COLUMN password VARCHAR(255) NULL");
    echo "✓ Made password nullable for Google users\n";
    
    // Add index on google_id for faster lookups
    $pdo->exec("ALTER TABLE users ADD INDEX IF NOT EXISTS idx_google_id (google_id)");
    echo "✓ Added index on google_id\n";
    
    echo "\n✅ Database schema updated successfully for Google authentication!\n";
    echo "Now you can use Google Sign-In on your website.\n\n";
    echo "⚠️  IMPORTANT: Don't forget to:\n";
    echo "1. Replace 'YOUR_GOOGLE_CLIENT_ID' in login.html with your actual Google Client ID\n";
    echo "2. Set up Google OAuth 2.0 credentials in Google Cloud Console\n";
    echo "3. Add your domain to authorized origins in Google Console\n";
    
} catch (PDOException $e) {
    echo "❌ Database error: " . $e->getMessage() . "\n";
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>
