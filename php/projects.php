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
        $stmt = $pdo->prepare("SELECT id, title, description, thumbnail, created_at, last_opened FROM projects WHERE user_id = ? ORDER BY last_opened DESC");
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
    $thumbnail = null;
    
    if (!$title || !$description) {
        echo json_encode(['success' => false, 'message' => 'Title and description are required']);
        exit;
    }
    
    // Handle image upload if provided
    if (isset($_FILES['thumbnail']) && $_FILES['thumbnail']['size'] > 0) {
        $upload_dir = dirname(__DIR__) . '/assets/uploads/';
        
        // Create uploads directory if it doesn't exist
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        
        $file = $_FILES['thumbnail'];
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        
        // Validate file
        if (!in_array($file['type'], $allowed_types)) {
            echo json_encode(['success' => false, 'message' => 'Invalid file type. Only JPEG, PNG, GIF, and WebP are allowed.']);
            exit;
        }
        
        if ($file['size'] > 5 * 1024 * 1024) { // 5MB limit
            echo json_encode(['success' => false, 'message' => 'File size exceeds 5MB limit.']);
            exit;
        }
        
        $file_ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $file_name = 'project_' . uniqid() . '.' . $file_ext;
        $file_path = $upload_dir . $file_name;
        
        if (move_uploaded_file($file['tmp_name'], $file_path)) {
            $thumbnail = 'assets/uploads/' . $file_name;
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to upload file']);
            exit;
        }
    }
    
    try {
        $stmt = $pdo->prepare("INSERT INTO projects (user_id, title, description, thumbnail) VALUES (?, ?, ?, ?)");
        $stmt->execute([$user_id, $title, $description, $thumbnail]);
        
        $project_id = $pdo->lastInsertId();
        
        echo json_encode([
            'success' => true,
            'message' => 'Project created successfully',
            'project_id' => $project_id
        ]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
} elseif ($action === 'updateProject') {
    $project_id = $_POST['project_id'] ?? '';
    $title = $_POST['title'] ?? null;
    $description = $_POST['description'] ?? null;
    $thumbnail = null;
    
    if (!$project_id) {
        echo json_encode(['success' => false, 'message' => 'Project ID is required']);
        exit;
    }
    
    try {
        // Verify project belongs to user
        $stmt = $pdo->prepare("SELECT thumbnail FROM projects WHERE id = ? AND user_id = ?");
        $stmt->execute([$project_id, $user_id]);
        $project = $stmt->fetch();
        
        if (!$project) {
            echo json_encode(['success' => false, 'message' => 'Project not found or unauthorized']);
            exit;
        }
        
        // Handle thumbnail update if file is provided
        if (isset($_FILES['thumbnail']) && $_FILES['thumbnail']['size'] > 0) {
            $upload_dir = dirname(__DIR__) . '/assets/uploads/';
            
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }
            
            $file = $_FILES['thumbnail'];
            $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            
            if (!in_array($file['type'], $allowed_types)) {
                echo json_encode(['success' => false, 'message' => 'Invalid file type. Only JPEG, PNG, GIF, and WebP are allowed.']);
                exit;
            }
            
            if ($file['size'] > 5 * 1024 * 1024) {
                echo json_encode(['success' => false, 'message' => 'File size exceeds 5MB limit.']);
                exit;
            }
            
            $file_ext = pathinfo($file['name'], PATHINFO_EXTENSION);
            $file_name = 'project_' . uniqid() . '.' . $file_ext;
            $file_path = $upload_dir . $file_name;
            
            if (move_uploaded_file($file['tmp_name'], $file_path)) {
                // Delete old thumbnail if exists
                if ($project['thumbnail'] && file_exists(dirname(__DIR__) . '/' . $project['thumbnail'])) {
                    unlink(dirname(__DIR__) . '/' . $project['thumbnail']);
                }
                $thumbnail = 'assets/uploads/' . $file_name;
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to upload file']);
                exit;
            }
        }
        
        // Build update query
        $updates = [];
        $params = [];
        
        if ($title !== null) {
            $updates[] = "title = ?";
            $params[] = $title;
        }
        
        if ($description !== null) {
            $updates[] = "description = ?";
            $params[] = $description;
        }
        
        if ($thumbnail !== null) {
            $updates[] = "thumbnail = ?";
            $params[] = $thumbnail;
        }
        
        if (empty($updates)) {
            echo json_encode(['success' => false, 'message' => 'No data to update']);
            exit;
        }
        
        $params[] = $project_id;
        $params[] = $user_id;
        
        $query = "UPDATE projects SET " . implode(", ", $updates) . " WHERE id = ? AND user_id = ?";
        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
        
        echo json_encode(['success' => true, 'message' => 'Project updated successfully']);
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
