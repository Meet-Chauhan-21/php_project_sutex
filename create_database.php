<?php
// Create vidyaguru_db database directly
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>🔧 Creating vidyaguru_db Database</h2>";

try {
    // Connect to MySQL without specifying a database
    $mysqli = new mysqli('localhost', 'root', '');
    
    if ($mysqli->connect_errno) {
        throw new Exception("MySQL connection failed: " . $mysqli->connect_error);
    }
    
    echo "<p style='color: green;'>✅ Connected to MySQL</p>";
    
    // Create the database
    $createDb = "CREATE DATABASE IF NOT EXISTS `vidyaguru_db` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
    
    if ($mysqli->query($createDb)) {
        echo "<p style='color: green;'>✅ Database 'vidyaguru_db' created successfully!</p>";
    } else {
        throw new Exception("Failed to create database: " . $mysqli->error);
    }
    
    // Select the database
    $mysqli->select_db('vidyaguru_db');
    echo "<p style='color: green;'>✅ Selected vidyaguru_db database</p>";
    
    // Show available databases
    echo "<h3>📋 Available Databases:</h3><ul>";
    $result = $mysqli->query("SHOW DATABASES");
    while ($row = $result->fetch_array()) {
        $dbName = $row[0];
        if ($dbName === 'vidyaguru_db') {
            echo "<li style='color: green; font-weight: bold;'>$dbName ✅</li>";
        } else {
            echo "<li>$dbName</li>";
        }
    }
    echo "</ul>";
    
    // Check if camuscore_db exists for copying
    $result = $mysqli->query("SHOW DATABASES LIKE 'camuscore_db'");
    if ($result->num_rows > 0) {
        echo "<h3>🔄 Copying data from camuscore_db...</h3>";
        
        // Get tables from camuscore_db
        $result = $mysqli->query("SHOW TABLES FROM camuscore_db");
        $tables = [];
        
        while ($row = $result->fetch_array()) {
            $tables[] = $row[0];
        }
        
        echo "<p>Found " . count($tables) . " tables in camuscore_db</p>";
        
        // Copy each table
        foreach ($tables as $table) {
            try {
                // Create table structure
                $mysqli->query("CREATE TABLE IF NOT EXISTS `vidyaguru_db`.`$table` LIKE `camuscore_db`.`$table`");
                
                // Copy data
                $mysqli->query("INSERT INTO `vidyaguru_db`.`$table` SELECT * FROM `camuscore_db`.`$table`");
                
                // Get row count
                $countResult = $mysqli->query("SELECT COUNT(*) as count FROM `vidyaguru_db`.`$table`");
                $count = $countResult->fetch_assoc()['count'];
                
                echo "<p style='color: green;'>✅ Copied table '$table' ($count rows)</p>";
                
            } catch (Exception $e) {
                echo "<p style='color: orange;'>⚠️ Failed to copy table '$table': " . $e->getMessage() . "</p>";
            }
        }
        
    } else {
        echo "<p style='color: orange;'>⚠️ camuscore_db not found. Creating empty tables...</p>";
        
        // Create basic tables if camuscore_db doesn't exist
        $createUsers = "
            CREATE TABLE IF NOT EXISTS `users` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `email` varchar(255) NOT NULL,
                `password_hash` varchar(255) NOT NULL,
                `first_name` varchar(100) DEFAULT NULL,
                `last_name` varchar(100) DEFAULT NULL,
                `phone` varchar(20) DEFAULT NULL,
                `program` varchar(100) DEFAULT NULL,
                `profile_picture` LONGTEXT DEFAULT NULL,
                `additional_info` JSON DEFAULT NULL,
                `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                UNIQUE KEY `email` (`email`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ";
        
        if ($mysqli->query($createUsers)) {
            echo "<p style='color: green;'>✅ Created users table</p>";
        }
        
        $createApplications = "
            CREATE TABLE IF NOT EXISTS `applications` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `user_id` int(11) NOT NULL,
                `program` varchar(100) NOT NULL,
                `status` varchar(50) DEFAULT 'pending',
                `application_data` JSON DEFAULT NULL,
                `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ";
        
        if ($mysqli->query($createApplications)) {
            echo "<p style='color: green;'>✅ Created applications table</p>";
        }
        
        $createAdminUsers = "
            CREATE TABLE IF NOT EXISTS `admin_users` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `username` varchar(100) NOT NULL,
                `email` varchar(255) NOT NULL,
                `password_hash` varchar(255) NOT NULL,
                `role` varchar(50) DEFAULT 'admin',
                `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                UNIQUE KEY `username` (`username`),
                UNIQUE KEY `email` (`email`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ";
        
        if ($mysqli->query($createAdminUsers)) {
            echo "<p style='color: green;'>✅ Created admin_users table</p>";
        }
    }
    
    // Test the connection with the new database
    echo "<h3>🧪 Testing new database connection...</h3>";
    $mysqli->close();
    
    // Test with config.php style connection
    $testConnection = new mysqli('localhost', 'root', '', 'vidyaguru_db');
    if ($testConnection->connect_errno) {
        throw new Exception("Test connection failed: " . $testConnection->connect_error);
    }
    
    echo "<p style='color: green;'>✅ Test connection successful!</p>";
    
    // Show tables in new database
    $result = $testConnection->query("SHOW TABLES");
    echo "<h3>📋 Tables in vidyaguru_db:</h3><ul>";
    while ($row = $result->fetch_array()) {
        $table = $row[0];
        
        // Get row count
        $countResult = $testConnection->query("SELECT COUNT(*) as count FROM `$table`");
        $count = $countResult->fetch_assoc()['count'];
        
        echo "<li><strong>$table</strong> - $count rows</li>";
    }
    echo "</ul>";
    
    $testConnection->close();
    
    echo "<h3>🎉 Success!</h3>";
    echo "<p style='color: green; font-weight: bold;'>vidyaguru_db database is ready to use!</p>";
    echo "<p><a href='/status_check.html' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Test Website Now</a></p>";
    
} catch (Exception $e) {
    echo "<p style='color: red; font-weight: bold;'>❌ Error: " . $e->getMessage() . "</p>";
    
    echo "<h3>🔧 Manual Steps:</h3>";
    echo "<ol>";
    echo "<li>Open <a href='http://localhost/phpmyadmin' target='_blank'>phpMyAdmin</a></li>";
    echo "<li>Click 'Databases' tab</li>";
    echo "<li>Create database named 'vidyaguru_db'</li>";
    echo "<li>Set collation to 'utf8mb4_unicode_ci'</li>";
    echo "<li>If you have camuscore_db, export it and import to vidyaguru_db</li>";
    echo "</ol>";
}
?>
