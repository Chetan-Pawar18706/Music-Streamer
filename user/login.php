<?php
require_once '../includes/db.php';
require_once '../includes/auth_functions.php';
 $error_message = '';
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $result = login_user($pdo, $_POST['username'], $_POST['password']);
    if (!$result['success']) {
        $error_message = $result['message'];
    } else {
        header('Location: dashboard.php');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="auth-container">
        <form action="login.php" method="post" class="auth-form">
            <h2>Login to Your Account</h2>
            <?php if ($error_message): ?><p class="error"><?php echo $error_message; ?></p><?php endif; ?>
            <div class="form-group"><label for="username">Username</label><input type="text" id="username" name="username" required></div>
            <div class="form-group"><label for="password">Password</label><input type="password" id="password" name="password" required></div>
            <button type="submit" class="btn">Login</button>
            <p>Don't have an account? <a href="register.php">Register here</a></p>
        </form>
    </div>
</body>
</html>