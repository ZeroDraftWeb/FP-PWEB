<?php
header('Content-Type: application/json');
session_start();
require_once 'database.php';

// Get input data (handle JSON or form data)
$input = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST' && stripos($_SERVER['CONTENT_TYPE'] ?? '', 'application/json') !== false) {
    $input = json_decode(file_get_contents('php://input'), true) ?? [];
}

// Get action from request
$action = $_GET['action'] ?? $_POST['action'] ?? $input['action'] ?? '';

// For actions that require user identification by email, get the email parameter
$email = $_GET['email'] ?? $_POST['email'] ?? $input['email'] ?? '';

// Get user ID - use different methods based on the action
if ($action === 'getProjectById') {
    // For getProjectById, use the session-based user ID since user is already authenticated
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['success' => false, 'message' => 'User not authenticated']);
        exit;
    }
    $user_id = $_SESSION['user_id'];
} else {
    // For other actions, require email parameter to identify the user
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
}

// Handle different actions
if ($action === 'getUserProfile') {
    try {
        // Get user info
        $stmt = $pdo->prepare("SELECT username, email, created_at FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        $user_info = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Get project count
        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM projects WHERE user_id = ?");
        $stmt->execute([$user_id]);
        $projects_count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
        
        // Get assets count
        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM assets WHERE project_id IN (SELECT id FROM projects WHERE user_id = ?)");
        $stmt->execute([$user_id]);
        $assets_count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
        
        // Get characters count
        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM characters WHERE project_id IN (SELECT id FROM projects WHERE user_id = ?)");
        $stmt->execute([$user_id]);
        $characters_count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
        
        // Get story nodes count
        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM story_nodes WHERE project_id IN (SELECT id FROM projects WHERE user_id = ?)");
        $stmt->execute([$user_id]);
        $storylines_count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
        
        echo json_encode([
            'success' => true,
            'user' => $user_info,
            'stats' => [
                'projects' => $projects_count,
                'assets' => $assets_count + $characters_count,
                'characters' => $characters_count,
                'storylines' => $storylines_count
            ]
        ]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
} elseif ($action === 'updateUserProfile') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    // Validation
    if (empty($username)) {
        echo json_encode(['success' => false, 'message' => 'Full name is required']);
        exit;
    }

    try {
        $updates = ["username = ?"];
        $params = [$username];

        if (!empty($password)) {
            if (strlen($password) < 6) {
                echo json_encode(['success' => false, 'message' => 'Password must be at least 6 characters']);
                exit;
            }
            $updates[] = "password_hash = ?";
            $params[] = password_hash($password, PASSWORD_DEFAULT);
        }

        $params[] = $user_id;
        
        $query = "UPDATE users SET " . implode(", ", $updates) . " WHERE id = ?";
        $stmt = $pdo->prepare($query);
        $stmt->execute($params);

        echo json_encode(['success' => true, 'message' => 'Profile updated successfully']);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
} elseif ($action === 'getProjects') {
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
} elseif ($action === 'getProjectById') {
    $project_id = $_GET['id'] ?? '';

    if (!$project_id) {
        echo json_encode(['success' => false, 'message' => 'Project ID is required']);
        exit;
    }

    try {
        // Verify project belongs to user
        $stmt = $pdo->prepare("SELECT id, title, description, thumbnail, created_at, last_opened FROM projects WHERE id = ? AND user_id = ?");
        $stmt->execute([$project_id, $user_id]);
        $project = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($project) {
            // Fetch Assets
            $stmt = $pdo->prepare("SELECT * FROM assets WHERE project_id = ? ORDER BY uploaded_at DESC");
            $stmt->execute([$project_id]);
            $project['assets'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Fetch Characters
            $stmt = $pdo->prepare("SELECT * FROM characters WHERE project_id = ?");
            $stmt->execute([$project_id]);
            $project['characters'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Fetch Story Nodes
            $stmt = $pdo->prepare("SELECT * FROM story_nodes WHERE project_id = ?");
            $stmt->execute([$project_id]);
            $project['story_nodes'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode([
                'success' => true,
                'project' => $project
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Project not found or unauthorized']);
        }
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
} elseif ($action === 'uploadAsset') {
    $project_id = $_POST['project_id'] ?? '';
    
    if (!$project_id) {
        echo json_encode(['success' => false, 'message' => 'Project ID is required']);
        exit;
    }

    // Verify project ownership (security)
    $stmt = $pdo->prepare("SELECT id FROM projects WHERE id = ? AND user_id = ?");
    $stmt->execute([$project_id, $user_id]);
    if (!$stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Unauthorized']);
        exit;
    }

    if (isset($_FILES['file']) && $_FILES['file']['size'] > 0) {
        $upload_dir = dirname(__DIR__) . '/assets/uploads/';
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);

        $file = $_FILES['file'];
        $file_ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $file_name = uniqid('asset_') . '.' . $file_ext;
        $file_path = $upload_dir . $file_name;
        $relative_path = 'assets/uploads/' . $file_name;
        $original_name = $file['name'];

        if (move_uploaded_file($file['tmp_name'], $file_path)) {
            try {
                $stmt = $pdo->prepare("INSERT INTO assets (project_id, file_path, file_name, category) VALUES (?, ?, ?, 'character')");
                $stmt->execute([$project_id, $relative_path, $original_name]);
                
                echo json_encode(['success' => true, 'message' => 'Asset uploaded', 'asset' => [
                    'id' => $pdo->lastInsertId(),
                    'file_path' => $relative_path,
                    'file_name' => $original_name
                ]]);
            } catch (PDOException $e) {
                echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Upload failed']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'No file uploaded']);
    }
} elseif ($action === 'deleteAsset') {
    $project_id = $_POST['project_id'] ?? '';
    $asset_id = $_POST['asset_id'] ?? '';
    
    if (!$project_id || !$asset_id) {
        echo json_encode(['success' => false, 'message' => 'Project ID and Asset ID are required']);
        exit;
    }

    // Verify project ownership (security)
    $stmt = $pdo->prepare("SELECT id FROM projects WHERE id = ? AND user_id = ?");
    $stmt->execute([$project_id, $user_id]);
    if (!$stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Unauthorized']);
        exit;
    }

    try {
        // Get asset file path before deleting from database
        $stmt = $pdo->prepare("SELECT file_path FROM assets WHERE id = ? AND project_id = ?");
        $stmt->execute([$asset_id, $project_id]);
        $asset = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$asset) {
            echo json_encode(['success' => false, 'message' => 'Asset not found']);
            exit;
        }

        // Delete file from filesystem
        $file_path = dirname(__DIR__) . '/' . $asset['file_path'];
        if (file_exists($file_path)) {
            unlink($file_path);
        }

        // Delete from database
        $stmt = $pdo->prepare("DELETE FROM assets WHERE id = ? AND project_id = ?");
        $stmt->execute([$asset_id, $project_id]);

        echo json_encode(['success' => true, 'message' => 'Asset deleted successfully']);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
} elseif ($action === 'saveCharacter') {
    $project_id = $_POST['project_id'] ?? '';
    $name = $_POST['name'] ?? 'New Character';
    $hp = $_POST['hp'] ?? 100;
    $attack = $_POST['attack'] ?? 50;
    $speed = $_POST['speed'] ?? 50;
    $image_id = !empty($_POST['image_id']) ? $_POST['image_id'] : null;

    $character_id = $_POST['character_id'] ?? null;

    if (!$project_id) {
        echo json_encode(['success' => false, 'message' => 'Project ID required']);
        exit;
    }

    // Verify ownership
    $stmt = $pdo->prepare("SELECT id FROM projects WHERE id = ? AND user_id = ?");
    $stmt->execute([$project_id, $user_id]);
    if (!$stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Unauthorized']);
        exit;
    }

    try {
        if ($character_id) {
            // Update existing character
            $stmt = $pdo->prepare("UPDATE characters SET name = ?, hp = ?, attack = ?, speed = ?, image_id = ? WHERE id = ? AND project_id = ?");
            $stmt->execute([$name, $hp, $attack, $speed, $image_id, $character_id, $project_id]);
            echo json_encode(['success' => true, 'message' => 'Character updated']);
        } else {
            // Insert new character
            $stmt = $pdo->prepare("INSERT INTO characters (project_id, name, hp, attack, speed, image_id) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$project_id, $name, $hp, $attack, $speed, $image_id]);
            echo json_encode(['success' => true, 'message' => 'Character saved']);
        }
        
        // Removed duplicate echo
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
} elseif ($action === 'deleteCharacter') {
    $project_id = $_POST['project_id'] ?? '';
    $character_id = $_POST['character_id'] ?? '';

    if (!$project_id || !$character_id) {
        echo json_encode(['success' => false, 'message' => 'Project ID and Character ID required']);
        exit;
    }

    // Verify ownership
    $stmt = $pdo->prepare("SELECT id FROM projects WHERE id = ? AND user_id = ?");
    $stmt->execute([$project_id, $user_id]);
    if (!$stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Unauthorized']);
        exit;
    }

    try {
        $stmt = $pdo->prepare("DELETE FROM characters WHERE id = ? AND project_id = ?");
        $stmt->execute([$character_id, $project_id]);
        echo json_encode(['success' => true, 'message' => 'Character deleted']);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
} elseif ($action === 'saveStoryNodes') {
    // Expecting a JSON payload because nodes are complex
    // $input is typically parsed at the top of the file now
    if (empty($input)) {
         $input = json_decode(file_get_contents('php://input'), true);
    }
    $project_id = $input['project_id'] ?? '';
    $nodes = $input['nodes'] ?? [];

    if (!$project_id || $project_id != $_GET['id']) { // Check both body and potentially query param
         // Fallback if not in body, check GET (standardize on one)
         $project_id = $_GET['id'] ?? $project_id;
    }

    if (!$project_id) {
        echo json_encode(['success' => false, 'message' => 'Project ID required']);
        exit;
    }

    // Verify ownership
    $stmt = $pdo->prepare("SELECT id FROM projects WHERE id = ? AND user_id = ?");
    $stmt->execute([$project_id, $user_id]);
    if (!$stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Unauthorized']);
        exit;
    }

    try {
        $pdo->beginTransaction();
        
        // Wipe existing nodes for simplicity (full sync)
        $stmt = $pdo->prepare("DELETE FROM story_nodes WHERE project_id = ?");
        $stmt->execute([$project_id]);

        $stmt = $pdo->prepare("INSERT INTO story_nodes (project_id, dom_id, title, content, position_x, position_y, connections) VALUES (?, ?, ?, ?, ?, ?, ?)");
        
        foreach ($nodes as $node) {
            $stmt->execute([
                $project_id,
                $node['dom_id'] ?? null, // Use frontend ID
                $node['title'] ?? 'Node',
                $node['content'] ?? '',
                $node['x'] ?? 0,
                $node['y'] ?? 0,
                json_encode($node['connections'] ?? [])
            ]);
        }
        
        $pdo->commit();
        echo json_encode(['success' => true, 'message' => 'Story saved']);
    } catch (PDOException $e) {
        $pdo->rollBack();
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
