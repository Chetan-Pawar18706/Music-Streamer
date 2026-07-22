<?php
require_once '../includes/db.php';

$error_message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    try {
        $stmt = $pdo->prepare("SELECT id, password_hash FROM admins WHERE username = ?");
        $stmt->execute([$username]);
        $admin = $stmt->fetch();

        if ($admin && password_verify($password, $admin['password_hash'])) {
            session_regenerate_id(true);
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_username'] = $username;
            header('Location: index.php');
            exit;
        } else {
            $error_message = 'Invalid username or password.';
        }
    } catch (PDOException $e) {
        $error_message = 'Database error.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="login-container">
        <form action="login.php" method="post" class="login-form">
            <h2>&#9835; Admin Panel</h2>
            <p style="color: #aaa; text-align: center; margin-bottom: 20px;">Music Stream Administration</p>
            <?php if ($error_message): ?>
                <div class="error"><?php echo $error_message; ?></div>
            <?php endif; ?>
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" placeholder="Admin username" required>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" placeholder="Admin password" required>
            </div>
            <button type="submit" class="btn btn-submit">Login</button>
            <p style="text-align: center; margin-top: 15px;"><a href="../index.php" style="color: #aaa;">Back to Home</a></p>
        </form>
    </div>
</body>
</html>
