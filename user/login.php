<?php
require_once '../includes/db.php';
require_once '../includes/auth_functions.php';
$error_message = '';
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $result = login_user($pdo, $_POST['username'], $_POST['password']);
    if (!$result['success']) {
        $error_message = $result['message'];
    } else {
        header('Location: search.php');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Music Stream</title>
    <link rel="stylesheet" href="style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <div class="auth-wrapper">
        <div class="auth-card">
            <div class="auth-logo">&#9835; Music Stream</div>
            <h2>Welcome Back</h2>
            <?php if ($error_message): ?>
                <div class="alert alert-error"><?php echo $error_message; ?></div>
            <?php endif; ?>
            <form action="login.php" method="post">
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" placeholder="Enter your username" required>
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" placeholder="Enter your password" required>
                </div>
                <button type="submit" class="btn btn-primary btn-block">Login</button>
            </form>
            <p class="auth-link">Don't have an account? <a href="register.php">Sign Up Free</a></p>
            <p class="auth-link"><a href="../index.php">Back to Home</a></p>
        </div>
    </div>
</body>
</html>
