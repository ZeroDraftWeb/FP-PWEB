<?php
require_once 'database.php';

try {
    // Add dom_id column to story_nodes if it doesn't exist
    $sql = "SHOW COLUMNS FROM story_nodes LIKE 'dom_id'";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    
    if (!$stmt->fetch()) {
        $sql = "ALTER TABLE story_nodes ADD COLUMN dom_id VARCHAR(255) DEFAULT NULL";
        $pdo->exec($sql);
        echo "Successfully added 'dom_id' column to 'story_nodes' table.\n";
    } else {
        echo "'dom_id' column already exists in 'story_nodes' table.\n";
    }

} catch (PDOException $e) {
    echo "Error updating database: " . $e->getMessage() . "\n";
}
?>
