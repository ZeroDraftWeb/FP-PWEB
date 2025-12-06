<?php
require_once 'database.php';
require_once 'auth.php';

// Require user to be logged in
requireLogin();

// Handle different API requests
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action'])) {
    // Get projects for user
    if ($_GET['action'] === 'get_projects') {
        $stmt = $pdo->prepare("SELECT * FROM projects WHERE user_id = ? ORDER BY last_opened DESC");
        $stmt->execute([$_SESSION['user_id']]);
        $projects = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode(['success' => true, 'projects' => $projects]);
        exit;
    }
    
    // Get assets for project
    if ($_GET['action'] === 'get_assets' && isset($_GET['project_id'])) {
        $stmt = $pdo->prepare("SELECT * FROM assets WHERE project_id = ? ORDER BY uploaded_at DESC");
        $stmt->execute([$_GET['project_id']]);
        $assets = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode(['success' => true, 'assets' => $assets]);
        exit;
    }
    
    // Get characters for project
    if ($_GET['action'] === 'get_characters' && isset($_GET['project_id'])) {
        $stmt = $pdo->prepare("SELECT * FROM characters WHERE project_id = ? ORDER BY created_at DESC");
        $stmt->execute([$_GET['project_id']]);
        $characters = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode(['success' => true, 'characters' => $characters]);
        exit;
    }
    
    // Get story nodes for project
    if ($_GET['action'] === 'get_story_nodes' && isset($_GET['project_id'])) {
        $stmt = $pdo->prepare("SELECT * FROM story_nodes WHERE project_id = ? ORDER BY created_at DESC");
        $stmt->execute([$_GET['project_id']]);
        $nodes = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode(['success' => true, 'nodes' => $nodes]);
        exit;
    }
}

// Handle POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    // Create project
    if ($_POST['action'] === 'create_project') {
        $title = $_POST['title'] ?? '';
        $description = $_POST['description'] ?? '';
        
        if (!empty($title)) {
            $stmt = $pdo->prepare("INSERT INTO projects (user_id, title, description) VALUES (?, ?, ?)");
            $stmt->execute([$_SESSION['user_id'], $title, $description]);
            
            echo json_encode(['success' => true, 'message' => 'Project created successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Title is required']);
        }
        exit;
    }
    
    // Update character
    if ($_POST['action'] === 'update_character') {
        $name = $_POST['name'] ?? '';
        $hp = $_POST['hp'] ?? 100;
        $attack = $_POST['attack'] ?? 50;
        $speed = $_POST['speed'] ?? 50;
        $projectId = $_POST['project_id'] ?? 1;
        
        // Check if character already exists
        $stmt = $pdo->prepare("SELECT id FROM characters WHERE project_id = ? AND name = ?");
        $stmt->execute([$projectId, $name]);
        $existingChar = $stmt->fetch();
        
        if ($existingChar) {
            // Update existing character
            $stmt = $pdo->prepare("UPDATE characters SET hp = ?, attack = ?, speed = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
            $stmt->execute([$hp, $attack, $speed, $existingChar['id']]);
        } else {
            // Create new character
            $stmt = $pdo->prepare("INSERT INTO characters (project_id, name, hp, attack, speed) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$projectId, $name, $hp, $attack, $speed]);
        }
        
        echo json_encode(['success' => true, 'message' => 'Character saved successfully']);
        exit;
    }
    
    // Create story node
    if ($_POST['action'] === 'create_node') {
        $title = $_POST['title'] ?? '';
        $content = $_POST['content'] ?? '';
        $projectId = $_POST['project_id'] ?? 1;
        
        if (!empty($title) && !empty($content)) {
            $stmt = $pdo->prepare("INSERT INTO story_nodes (project_id, title, content) VALUES (?, ?, ?)");
            $stmt->execute([$projectId, $title, $content]);
            
            echo json_encode(['success' => true, 'message' => 'Story node created successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Title and content are required']);
        }
        exit;
    }
}
?>