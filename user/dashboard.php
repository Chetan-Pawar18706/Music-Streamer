<?php
require_once '../includes/db.php';
if (!isset($_SESSION['user_logged_in'])) { header('Location: login.php'); exit; }
 $user_id = $_SESSION['user_id'];
 $stmt = $pdo->prepare("SELECT username, email, created_at FROM users WHERE id = ?");
 $stmt->execute([$user_id]);
 $user = $stmt->fetch(PDO::FETCH_ASSOC);
 $stmt = $pdo->prepare("SELECT COUNT(*) FROM playlists WHERE user_id = ?");
 $stmt->execute([$user_id]);
 $playlist_count = $stmt->fetchColumn();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Dashboard</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="dashboard-container">
        <header class="dashboard-header">
            <h1>My Dashboard</h1>
            <a href="logout.php" class="btn btn-logout">Logout</a>
        </header>
        <main class="dashboard-main">
            <div class="welcome-card">
                <h2>Welcome back, <?php echo htmlspecialchars($user['username']); ?>!</h2>
                <p>Member since: <?php echo date('F j, Y', strtotime($user['created_at'])); ?></p>
                <p>Email: <?php echo htmlspecialchars($user['email']); ?></p>
            </div>
            <div class="stats-cards">
                <div class="stat-card">
                    <h3><?php echo $playlist_count; ?></h3>
                    <p>Playlists Created</p>
                    <a href="playlists.php" class="btn">Manage Playlists</a>
                </div>
                <div class="stat-card">
                    <h3>&#9835;</h3>
                    <p>Browse Music</p>
                    <a href="../player.php" class="btn">Open Player</a>
                </div>
            </div>
        </main>
    </div>
</body>
</html>