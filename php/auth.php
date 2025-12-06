<?php
session_start();
require_once 'database.php';

function validateUser($username, $password) {
    global $pdo;
    
    $stmt = $pdo->prepare("SELECT id, username, password_hash, membership_status FROM users WHERE username = ? OR email = ?");
    $stmt->execute([$username, $username]);
    $user = $stmt->fetch();
    
    if ($user && password_verify($password, $user['password_hash'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['membership_status'] = $user['membership_status'];
        return true;
    }
    
    return false;
}

function registerUser($username, $email, $password) {
    global $pdo;
    
    $password_hash = password_hash($password, PASSWORD_DEFAULT);
    
    try {
        $stmt = $pdo->prepare("INSERT INTO users (username, email, password_hash) VALUES (?, ?, ?)");
        $stmt->execute([$username, $email, $password_hash]);
        return true;
    } catch (PDOException $e) {
        // Username or email already exists
        return false;
    }
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function logout() {
    session_destroy();
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: ../pages/login.html');
        exit();
    }
}

// Handle login request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'login') {
        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';
        
        if (validateUser($username, $password)) {
            echo json_encode(['success' => true, 'message' => 'Login successful']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Invalid credentials']);
        }
        exit;
    }
    
    if ($_POST['action'] === 'register') {
        $username = $_POST['username'] ?? '';
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        
        if (registerUser($username, $email, $password)) {
            echo json_encode(['success' => true, 'message' => 'Registration successful']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Username or email already exists']);
        }
        exit;
    }
}
?>