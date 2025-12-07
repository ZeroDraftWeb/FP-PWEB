<?php
header('Content-Type: application/json');
session_start();
require_once 'database.php';

// Simple auth check - dalam production gunakan proper authentication
// Untuk sekarang, hanya check apakah ada parameter admin key
$admin_key = $_GET['key'] ?? $_POST['key'] ?? '';

if ($admin_key !== 'admin123') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

// Handle different actions
$action = $_GET['action'] ?? $_POST['action'] ?? 'list';

if ($action === 'list') {
    try {
        $stmt = $pdo->prepare("SELECT id, username, email, created_at FROM users ORDER BY created_at DESC");
        $stmt->execute();
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'success' => true,
            'users' => $users,
            'total' => count($users)
        ]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
} elseif ($action === 'delete') {
    $user_id = $_POST['user_id'] ?? '';
    
    if (empty($user_id)) {
        echo json_encode(['success' => false, 'message' => 'User ID is required']);
        exit;
    }
    
    try {
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        
        echo json_encode(['success' => true, 'message' => 'User deleted successfully']);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
}
?>
