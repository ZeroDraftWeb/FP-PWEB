<?php
require_once 'database.php';

try {
    // Check if thumbnail column exists
    $stmt = $pdo->query("SHOW COLUMNS FROM projects LIKE 'thumbnail'");
    $columnExists = $stmt->fetch();
    
    if (!$columnExists) {
        // Add thumbnail column if it doesn't exist
        $pdo->exec("ALTER TABLE projects ADD COLUMN thumbnail VARCHAR(500) AFTER description");
        echo json_encode(['success' => true, 'message' => 'Thumbnail column added successfully']);
    } else {
        echo json_encode(['success' => true, 'message' => 'Thumbnail column already exists']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?>
