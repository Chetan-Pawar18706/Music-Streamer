<?php
require_once '../includes/db.php';

if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}

$totalUsers = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$totalSongs = $pdo->query("SELECT COUNT(*) FROM user_songs")->fetchColumn();
$totalSearches = $pdo->query("SELECT COUNT(*) FROM user_searches")->fetchColumn();
$searchesToday = $pdo->query("SELECT COUNT(*) FROM user_searches WHERE DATE(searched_at) = CURDATE()")->fetchColumn();

$recentSearches = $pdo->query("
    SELECT us.query, us.searched_at, u.username
    FROM user_searches us
    JOIN users u ON us.user_id = u.id
    ORDER BY us.searched_at DESC
    LIMIT 10
")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Music Streamer</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="admin-wrapper">
        <aside class="admin-sidebar">
            <div class="sidebar-header">
                <div class="sidebar-logo">MS</div>
                <div class="sidebar-title">Music <span>Streamer</span></div>
            </div>
            <nav class="sidebar-nav">
                <div class="sidebar-nav-label">Menu</div>
                <a href="index.php" class="sidebar-link active">
                    <span class="icon">&#9632;</span> Dashboard
                </a>
                <a href="users.php" class="sidebar-link">
                    <span class="icon">&#9786;</span> Users
                    <span class="badge-count"><?php echo $totalUsers; ?></span>
                </a>
                <a href="searches.php" class="sidebar-link">
                    <span class="icon">&#128269;</span> Searches
                    <span class="badge-count"><?php echo $totalSearches; ?></span>
                </a>
                <div class="sidebar-nav-label">Account</div>
                <a href="logout.php" class="sidebar-link">
                    <span class="icon">&#10140;</span> Logout
                </a>
            </nav>
            <div class="sidebar-footer">
                <div class="sidebar-user">
                    <div class="sidebar-user-avatar"><?php echo strtoupper(substr($_SESSION['admin_username'] ?? 'A', 0, 1)); ?></div>
                    <div class="sidebar-user-info">
                        <div class="sidebar-user-name"><?php echo htmlspecialchars($_SESSION['admin_username'] ?? 'Admin'); ?></div>
                        <div class="sidebar-user-role">Administrator</div>
                    </div>
                </div>
            </div>
        </aside>

        <main class="admin-main">
            <div class="admin-header">
                <div class="admin-header-left">
                    <h1>Admin Dashboard</h1>
                    <p>Overview of your platform's activity</p>
                </div>
            </div>

            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-card-icon red">&#9786;</div>
                    <div class="stat-card-number"><?php echo number_format($totalUsers); ?></div>
                    <div class="stat-card-label">Total Users</div>
                </div>
                <div class="stat-card">
                    <div class="stat-card-icon cyan">&#9835;</div>
                    <div class="stat-card-number"><?php echo number_format($totalSongs); ?></div>
                    <div class="stat-card-label">Total Songs Saved</div>
                </div>
                <div class="stat-card">
                    <div class="stat-card-icon green">&#128269;</div>
                    <div class="stat-card-number"><?php echo number_format($totalSearches); ?></div>
                    <div class="stat-card-label">Total Searches</div>
                </div>
                <div class="stat-card">
                    <div class="stat-card-icon muted">&#9200;</div>
                    <div class="stat-card-number"><?php echo number_format($searchesToday); ?></div>
                    <div class="stat-card-label">Searches Today</div>
                </div>
            </div>

            <div class="section-card">
                <div class="section-card-header">
                    <h3>Recent Activity</h3>
                    <a href="searches.php" class="btn btn-ghost btn-sm">View All</a>
                </div>
                <?php if (!empty($recentSearches)): ?>
                <div class="activity-feed">
                    <?php foreach ($recentSearches as $search): ?>
                    <div class="activity-item">
                        <div class="activity-avatar"><?php echo strtoupper(substr($search['username'], 0, 1)); ?></div>
                        <div class="activity-body">
                            <div class="activity-text">
                                <strong><?php echo htmlspecialchars($search['username']); ?></strong> searched for <span class="query">"<?php echo htmlspecialchars($search['query']); ?>"</span>
                            </div>
                            <div class="activity-time"><?php echo date('M j, g:i A', strtotime($search['searched_at'])); ?></div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php else: ?>
                <div class="empty-state">
                    <div class="empty-state-icon">&#128269;</div>
                    <h3>No Recent Activity</h3>
                    <p>There are no searches recorded yet.</p>
                </div>
                <?php endif; ?>
            </div>

            <div class="stats-grid">
                <a href="users.php" class="stat-card" style="cursor:pointer; text-decoration:none;">
                    <div class="stat-card-icon red">&#9786;</div>
                    <div class="stat-card-number" style="font-size:18px;">Manage Users</div>
                    <div class="stat-card-label">View, edit, and manage user accounts</div>
                </a>
                <a href="searches.php" class="stat-card" style="cursor:pointer; text-decoration:none;">
                    <div class="stat-card-icon cyan">&#128269;</div>
                    <div class="stat-card-number" style="font-size:18px;">View Searches</div>
                    <div class="stat-card-label">Browse all user search history</div>
                </a>
            </div>
        </main>
    </div>
</body>
</html>
