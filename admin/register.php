<?php
require_once '../includes/db.php';

$error_message = '';
$success_message = '';
$allow_registration = false;

// Check if there are any admins in the database
try {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'admins'");
    $stmt->execute();
    $table_exists = $stmt->fetchColumn() > 0;
    
    if ($table_exists) {
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM admins");
        $result = $stmt->fetch();
        // Allow registration only if no admins exist (first admin setup)
        $allow_registration = $result['count'] == 0;
    } else {
        // Table doesn't exist, allow registration for first admin setup
        $allow_registration = true;
    }
} catch (PDOException $e) {
    $allow_registration = true;
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!$allow_registration) {
        $error_message = "Admin registration is not available.";
    } else {
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        $password_confirm = $_POST['password_confirm'] ?? '';
        $registration_key = $_POST['registration_key'] ?? '';

        // Validate input
        if (empty($username) || strlen($username) < 3) {
            $error_message = "Username must be at least 3 characters long.";
        } elseif (empty($password) || strlen($password) < 6) {
            $error_message = "Password must be at least 6 characters long.";
        } elseif ($password !== $password_confirm) {
            $error_message = "Passwords do not match.";
        } elseif (empty($registration_key) || $registration_key !== 'admin_setup_2024') {
            $error_message = "Invalid registration key.";
        } else {
            try {
                // Create admins table if it doesn't exist
                $pdo->exec("CREATE TABLE IF NOT EXISTS `admins` (
                    `id` int(11) NOT NULL AUTO_INCREMENT,
                    `username` varchar(50) NOT NULL UNIQUE,
                    `password_hash` varchar(255) NOT NULL,
                    `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
                    PRIMARY KEY (`id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

                // Check if username already exists
                $stmt = $pdo->prepare("SELECT id FROM admins WHERE username = ?");
                $stmt->execute([$username]);
                if ($stmt->fetch()) {
                    $error_message = "Username already exists.";
                } else {
                    // Register the admin
                    $password_hash = password_hash($password, PASSWORD_DEFAULT);
                    $stmt = $pdo->prepare("INSERT INTO admins (username, password_hash) VALUES (?, ?)");
                    if ($stmt->execute([$username, $password_hash])) {
                        $success_message = "Admin account created successfully! You can now log in.";
                    } else {
                        $error_message = "Registration failed. Please try again.";
                    }
                }
            } catch (PDOException $e) {
                $error_message = "Database error: " . $e->getMessage();
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Register</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .info-box {
            background-color: #e3f2fd;
            border-left: 4px solid #2196F3;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        .info-box p {
            margin: 0;
            color: #1565c0;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <form action="register.php" method="post" class="login-form">
            <h2>Admin Registration</h2>
            
            <?php if (!$allow_registration): ?>
                <div class="info-box">
                    <p>Admin registration is currently closed. An admin account has already been created.</p>
                </div>
                <p><a href="login.php">Go to Admin Login</a></p>
            <?php else: ?>
                <div class="info-box">
                    <p>This page is for creating the first admin account. A registration key is required.</p>
                </div>

                <?php if ($error_message): ?>
                    <p class="error"><?php echo htmlspecialchars($error_message); ?></p>
                <?php endif; ?>

                <?php if ($success_message): ?>
                    <p class="success"><?php echo htmlspecialchars($success_message); ?></p>
                    <p><a href="login.php">Proceed to Admin Login</a></p>
                <?php else: ?>
                    <div class="form-group">
                        <label for="username">Admin Username</label>
                        <input type="text" id="username" name="username" required minlength="3" placeholder="Enter admin username">
                    </div>

                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" required minlength="6" placeholder="At least 6 characters">
                    </div>

                    <div class="form-group">
                        <label for="password_confirm">Confirm Password</label>
                        <input type="password" id="password_confirm" name="password_confirm" required minlength="6" placeholder="Confirm your password">
                    </div>

                    <div class="form-group">
                        <label for="registration_key">Registration Key</label>
                        <input type="password" id="registration_key" name="registration_key" required placeholder="Enter the registration key">
                    </div>

                    <button type="submit" class="btn">Create Admin Account</button>
                <?php endif; ?>
            <?php endif; ?>
        </form>
    </div>
</body>
</html>
