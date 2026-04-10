<?php
function register_user($pdo, $username, $email, $password) {
    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
    $stmt->execute([$username, $email]);
    if ($stmt->fetch()) {
        return ['success' => false, 'message' => 'Username or email already exists.'];
    }
    $password_hash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("INSERT INTO users (username, email, password_hash) VALUES (?, ?, ?)");
    if ($stmt->execute([$username, $email, $password_hash])) {
        return ['success' => true, 'message' => 'Registration successful! Please log in.'];
    } else {
        return ['success' => false, 'message' => 'Registration failed. Please try again.'];
    }
}

function login_user($pdo, $username, $password) {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();
    if ($user && password_verify($password, $user['password_hash'])) {
        session_regenerate_id();
        $_SESSION['user_logged_in'] = true;
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        return ['success' => true];
    } else {
        return ['success' => false, 'message' => 'Incorrect username or password.'];
    }
}
?>