<?php
header('Content-Type: application/json');
session_start();
require_once 'database.php';

// Get user info from request
$action = $_GET['action'] ?? $_POST['action'] ?? '';
$email = $_GET['email'] ?? $_POST['email'] ?? '';

if (!$email) {
    echo json_encode(['success' => false, 'message' => 'Email is required']);
    exit;
}

// Get user ID from email
$stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
$stmt->execute([$email]);
$user = $stmt->fetch();

if (!$user) {
    echo json_encode(['success' => false, 'message' => 'User not found']);
    exit;
}

$user_id = $user['id'];

// Handle different actions
if ($action === 'getProjects') {
    try {
        $stmt = $pdo->prepare("SELECT id, title, description, created_at, last_opened FROM projects WHERE user_id = ? ORDER BY last_opened DESC");
        $stmt->execute([$user_id]);
        $projects = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'success' => true,
            'projects' => $projects
        ]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
} elseif ($action === 'createProject') {
    $title = $_POST['title'] ?? '';
    $description = $_POST['description'] ?? '';
    $icon = $_POST['icon'] ?? 'fa-gamepad';
    
    if (!$title || !$description) {
        echo json_encode(['success' => false, 'message' => 'Title and description are required']);
        exit;
    }
    
    try {
        $stmt = $pdo->prepare("INSERT INTO projects (user_id, title, description) VALUES (?, ?, ?)");
        $stmt->execute([$user_id, $title, $description]);
        
        $project_id = $pdo->lastInsertId();
        
        echo json_encode([
            'success' => true,
            'message' => 'Project created successfully',
            'project_id' => $project_id
        ]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
} elseif ($action === 'deleteProject') {
    $project_id = $_POST['project_id'] ?? '';
    
    if (!$project_id) {
        echo json_encode(['success' => false, 'message' => 'Project ID is required']);
        exit;
    }
    
    try {
        // Verify project belongs to user
        $stmt = $pdo->prepare("SELECT id FROM projects WHERE id = ? AND user_id = ?");
        $stmt->execute([$project_id, $user_id]);
        
        if (!$stmt->fetch()) {
            echo json_encode(['success' => false, 'message' => 'Project not found or unauthorized']);
            exit;
        }
        
        $stmt = $pdo->prepare("DELETE FROM projects WHERE id = ? AND user_id = ?");
        $stmt->execute([$project_id, $user_id]);
        
        echo json_encode(['success' => true, 'message' => 'Project deleted successfully']);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid action']);
}
?>
