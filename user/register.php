<?php
require_once '../includes/db.php';
require_once '../includes/auth_functions.php';
 $error_message = ''; $success_message = '';
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if ($_POST['password'] !== $_POST['password_confirm']) { $error_message = "Passwords do not match."; }
    else { $result = register_user($pdo, $_POST['username'], $_POST['email'], $_POST['password']);
        if ($result['success']) { $success_message = $result['message']; }
        else { $error_message = $result['message']; }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="auth-container">
        <form action="register.php" method="post" class="auth-form">
            <h2>Create an Account</h2>
            <?php if ($error_message): ?><p class="error"><?php echo $error_message; ?></p><?php endif; ?>
            <?php if ($success_message): ?>
                <p class="success"><?php echo $success_message; ?></p>
                <p><a href="login.php">Click here to login</a></p>
            <?php else: ?>
            <div class="form-group"><label for="username">Username</label><input type="text" id="username" name="username" required></div>
            <div class="form-group"><label for="email">Email</label><input type="email" id="email" name="email" required></div>
            <div class="form-group"><label for="password">Password</label><input type="password" id="password" name="password" required></div>
            <div class="form-group"><label for="password_confirm">Confirm Password</label><input type="password" id="password_confirm" name="password_confirm" required></div>
            <button type="submit" class="btn">Register</button>
            <p>Already have an account? <a href="login.php">Login here</a></p>
            <?php endif; ?>
        </form>
    </div>
</body>
</html>