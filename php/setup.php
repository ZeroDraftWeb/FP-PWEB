<?php
// This file sets up the database and tables
// Run this file once to initialize the database

// Database configuration
$host = 'localhost';
$dbname = 'gdd_organizer';
$username = 'root'; // Default for XAMPP
$password = '';     // Default for XAMPP

try {
    // First, connect without specifying database to create it if it doesn't exist
    $pdo = new PDO("mysql:host=$host;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Create database if it doesn't exist
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbname` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "✓ Database '$dbname' created or already exists<br>";
    
    // Now connect to the specific database
    $pdo->exec("USE `$dbname`");
    
    // Create tables
    $queries = [
        "CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(50) UNIQUE NOT NULL,
            email VARCHAR(100) UNIQUE NOT NULL,
            password_hash VARCHAR(255) NOT NULL,
            membership_status ENUM('basic', 'pro', 'enterprise') DEFAULT 'basic',
            membership_expiry DATETIME DEFAULT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )",
        
        "CREATE TABLE IF NOT EXISTS projects (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            title VARCHAR(255) NOT NULL,
            description TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            last_opened TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        )",
        
        "CREATE TABLE IF NOT EXISTS assets (
            id INT AUTO_INCREMENT PRIMARY KEY,
            project_id INT NOT NULL,
            file_path VARCHAR(500) NOT NULL,
            file_name VARCHAR(255) NOT NULL,
            category ENUM('character', 'environment', 'ui', 'item') DEFAULT 'character',
            description TEXT,
            uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE
        )"
    ];
    
    foreach ($queries as $query) {
        $pdo->exec($query);
    }
    
    echo "✓ All tables created successfully<br>";
    echo "<br><strong>Database setup complete!</strong><br>";
    echo "<a href='../pages/signup.html'>Go to Signup Page</a>";
    
} catch(PDOException $e) {
    die("Setup failed: " . $e->getMessage());
}
?>
