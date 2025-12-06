<?php
require_once 'database.php';
require_once 'auth.php';

// Require user to be logged in
requireLogin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_FILES['file'])) {
        $uploadDir = '../assets/uploads/';
        $uploadFile = $uploadDir . basename($_FILES['file']['name']);
        
        // Validate file type
        $imageFileType = strtolower(pathinfo($uploadFile, PATHINFO_EXTENSION));
        $allowedTypes = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        
        if (!in_array($imageFileType, $allowedTypes)) {
            echo json_encode(['success' => false, 'message' => 'Only image files are allowed']);
            exit;
        }
        
        // Check file size (max 5MB)
        if ($_FILES['file']['size'] > 5000000) {
            echo json_encode(['success' => false, 'message' => 'File size exceeds 5MB limit']);
            exit;
        }
        
        // Generate unique filename
        $fileName = uniqid() . '.' . $imageFileType;
        $uploadFile = $uploadDir . $fileName;
        
        if (move_uploaded_file($_FILES['file']['tmp_name'], $uploadFile)) {
            // Save to database
            $stmt = $pdo->prepare("INSERT INTO assets (project_id, file_path, file_name, category, description) VALUES (?, ?, ?, ?, ?)");
            $projectId = $_POST['project_id'] ?? 1; // Default to first project for demo
            $category = $_POST['category'] ?? 'character';
            $description = $_POST['description'] ?? '';
            
            $stmt->execute([$projectId, $uploadFile, $_FILES['file']['name'], $category, $description]);
            
            echo json_encode([
                'success' => true, 
                'message' => 'File uploaded successfully', 
                'file_path' => $uploadFile,
                'file_name' => $_FILES['file']['name']
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'There was an error uploading your file']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'No file was uploaded']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>