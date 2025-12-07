<?php
header('Content-Type: application/json');
session_start();
require_once 'database.php';
require_once 'oauth_config.php';

function validateUser($username, $password) {
    global $pdo;

    $stmt = $pdo->prepare("SELECT id, username, email, password_hash FROM users WHERE (username = ? OR email = ?) AND password_hash != ''");
    $stmt->execute([$username, $username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password_hash'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['email'] = $user['email'];
        return $user;
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
        error_log("Registration error: " . $e->getMessage());
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

function initiateGoogleAuth() {
    global $oauth_config;

    if (!isset($oauth_config['google'])) {
        error_log('Google config not found');
        header('Location: ../pages/login.html?error=no_google_config');
        exit;
    }

    $config = $oauth_config['google'];

    if (empty($config['client_id']) || $config['client_id'] === 'YOUR_GOOGLE_CLIENT_ID') {
        error_log('Google Client ID not configured');
        header('Location: ../pages/login.html?error=invalid_client_id');
        exit;
    }

    if (empty($config['client_secret']) || $config['client_secret'] === 'YOUR_GOOGLE_CLIENT_SECRET') {
        error_log('Google Client Secret not configured');
        header('Location: ../pages/login.html?error=invalid_client_secret');
        exit;
    }

    $auth_url = $config['auth_url'] . '?' . http_build_query([
        'client_id' => $config['client_id'],
        'redirect_uri' => $config['redirect_uri'],
        'scope' => 'email profile',
        'response_type' => 'code',
        'access_type' => 'online',
        'prompt' => 'consent'
    ]);

    header('Location: ' . $auth_url);
    exit;
}

// Handle login request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    try {
        if ($_POST['action'] === 'login') {
            $username = $_POST['username'] ?? '';
            $password = $_POST['password'] ?? '';

            $user = validateUser($username, $password);
            if ($user) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Login successful',
                    'user' => [
                        'id' => $user['id'],
                        'username' => $user['username'],
                        'email' => $user['email']
                    ]
                ]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Invalid credentials']);
            }
            exit;
        }

        if ($_POST['action'] === 'register') {
            $username = $_POST['username'] ?? '';
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';

            // Validate inputs
            if (empty($username) || empty($email) || empty($password)) {
                echo json_encode(['success' => false, 'message' => 'All fields are required']);
                exit;
            }

            if (strlen($password) < 6) {
                echo json_encode(['success' => false, 'message' => 'Password must be at least 6 characters']);
                exit;
            }

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                echo json_encode(['success' => false, 'message' => 'Invalid email format']);
                exit;
            }

            if (registerUser($username, $email, $password)) {
                echo json_encode(['success' => true, 'message' => 'Registration successful']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Username or email already exists']);
            }
            exit;
        }
    } catch (Exception $e) {
        error_log("Auth error: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Server error occurred']);
        exit;
    }
}

// Handle Google login request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'google_login') {
    initiateGoogleAuth();
}

// Handle GET requests for checking login status
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action'])) {
    if ($_GET['action'] === 'check_login') {
        if (isLoggedIn()) {
            echo json_encode([
                'success' => true,
                'logged_in' => true,
                'user' => [
                    'id' => $_SESSION['user_id'],
                    'username' => $_SESSION['username'],
                    'email' => $_SESSION['email']
                ]
            ]);
        } else {
            echo json_encode(['success' => true, 'logged_in' => false]);
        }
        exit;
    }
}

// If not a POST with proper action, just return JSON error
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // For any other POST request, return a proper response to avoid issues
    echo json_encode(['success' => false, 'message' => 'Invalid action']);
    exit;
}
?>