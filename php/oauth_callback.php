<?php
session_start();
require_once 'database.php';
require_once 'oauth_config.php';

// Debug: Log incoming parameters
error_log("OAuth callback called with provider: " . ($_GET['provider'] ?? 'none') . " and code: " . (isset($_GET['code']) ? 'yes' : 'no'));

$code = $_GET['code'] ?? '';

if (!$code) {
    error_log("OAuth failed: Missing code parameter");
    header('Location: ../pages/login.html?error=oauth_failed&details=missing_code');
    exit;
}

$provider = 'google'; // Since we only support Google for OAuth

try {
    $config = $oauth_config[$provider];

    // Validate config exists
    if (empty($config['client_id']) || empty($config['client_secret']) || empty($config['redirect_uri'])) {
        error_log("OAuth failed: Missing config values");
        header('Location: ../pages/login.html?error=config_error');
        exit;
    }

    // Exchange code for access token
    $token_data = [
        'client_id' => $config['client_id'],
        'client_secret' => $config['client_secret'],
        'redirect_uri' => $config['redirect_uri'],
        'grant_type' => 'authorization_code',
        'code' => $code
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $config['token_url']);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($token_data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/x-www-form-urlencoded']);

    $response = curl_exec($ch);
    $error = curl_error($ch);
    curl_close($ch);

    if ($error) {
        error_log("CURL Error: $error");
        header('Location: ../pages/login.html?error=curl_error');
        exit;
    }

    $token_response = json_decode($response, true);

    if (!$token_response) {
        error_log("OAuth failed: Invalid token response: $response");
        header('Location: ../pages/login.html?error=token_decode_error');
        exit;
    }

    if (isset($token_response['access_token'])) {
        // Get user info
        $user_info_url = $config['user_info_url'] . '?access_token=' . $token_response['access_token'];
        $user_response = file_get_contents($user_info_url);

        if ($user_response === false) {
            error_log("OAuth failed: Could not get user info");
            header('Location: ../pages/login.html?error=user_info_error');
            exit;
        }

        $user_data = json_decode($user_response, true);

        if ($user_data) {
            error_log("User data received: " . print_r($user_data, true));

            // Check if user already exists
            $stmt = $pdo->prepare("SELECT id, username, email FROM users WHERE email = ?");
            $stmt->execute([$user_data['email']]);
            $existing_user = $stmt->fetch();

            if ($existing_user) {
                // User already exists, log them in
                $_SESSION['user_id'] = $existing_user['id'];
                $_SESSION['username'] = $existing_user['username'];
                $_SESSION['email'] = $existing_user['email'];

                error_log("Existing user logged in: " . $user_data['email']);
            } else {
                // Create new user
                $username = $user_data['name'] ?? $user_data['email'];

                // Make sure username is unique
                $original_username = $username;
                $counter = 1;
                while (true) {
                    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
                    $stmt->execute([$username]);
                    if (!$stmt->fetch()) break; // Username is available

                    $username = $original_username . '_' . $counter;
                    $counter++;
                }

                $stmt = $pdo->prepare("INSERT INTO users (username, email, password_hash) VALUES (?, ?, ?)");
                $stmt->execute([$username, $user_data['email'], '']); // Empty password for OAuth users

                $new_user_id = $pdo->lastInsertId();

                $_SESSION['user_id'] = $new_user_id;
                $_SESSION['username'] = $username;
                $_SESSION['email'] = $user_data['email'];

                error_log("New user created: " . $user_data['email']);
            }

            // Redirect to success page that will handle localStorage
            header('Location: ../oauth_success.html');
            exit;
        } else {
            error_log("OAuth failed: Could not decode user response: $user_response");
        }
    } else {
        error_log("OAuth failed: No access token in response: " . print_r($token_response, true));
    }
} catch (Exception $e) {
    error_log('OAuth error: ' . $e->getMessage());
    error_log('OAuth error trace: ' . $e->getTraceAsString());
}

header('Location: ../pages/login.html?error=oauth_failed&details=general_error');
exit;
?>