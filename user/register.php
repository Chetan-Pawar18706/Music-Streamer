<?php
require_once '../includes/db.php';
require_once '../includes/auth_functions.php';
$error_message = '';
$success_message = '';
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if ($_POST['password'] !== $_POST['password_confirm']) {
        $error_message = "Passwords do not match.";
    } else {
        $result = register_user($pdo, $_POST['username'], $_POST['email'], $_POST['password']);
        if ($result['success']) {
            $success_message = $result['message'];
        } else {
            $error_message = $result['message'];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Music Stream</title>
    <link rel="stylesheet" href="style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <div class="auth-wrapper">
        <div class="auth-card">
            <div class="auth-logo">&#9835; Music Stream</div>
            <h2>Create Account</h2>
            <?php if ($error_message): ?>
                <div class="alert alert-error"><?php echo $error_message; ?></div>
            <?php endif; ?>
            <?php if ($success_message): ?>
                <div class="alert alert-success"><?php echo $success_message; ?></div>
                <p class="auth-link"><a href="login.php">Click here to login</a></p>
            <?php else: ?>
            <form action="register.php" method="post">
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" placeholder="Choose a username" required>
                </div>
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" placeholder="Enter your email" required>
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" placeholder="Create a password" required>
                </div>
                <div class="form-group">
                    <label for="password_confirm">Confirm Password</label>
                    <input type="password" id="password_confirm" name="password_confirm" placeholder="Confirm your password" required>
                </div>
                <button type="submit" class="btn btn-primary btn-block">Create Account</button>
            </form>
            <p class="auth-link">Already have an account? <a href="login.php">Login</a></p>
            <?php endif; ?>
            <p class="auth-link"><a href="../index.php">Back to Home</a></p>
        </div>
    </div>
</body>
</html>
