<?php
require_once '../includes/db.php';
if (!isset($_SESSION['admin_logged_in'])) { header('Location: login.php'); exit; }

$userId = isset($_GET['user_id']) ? (int)$_GET['user_id'] : 0;

if ($userId > 0) {
    $stmt = $pdo->prepare("
        SELECT s.id, s.query, s.searched_at, u.username
        FROM user_searches s
        JOIN users u ON u.id = s.user_id
        WHERE s.user_id = ?
        ORDER BY s.searched_at DESC
    ");
    $stmt->execute([$userId]);
} else {
    $stmt = $pdo->query("
        SELECT s.id, s.query, s.searched_at, u.username
        FROM user_searches s
        JOIN users u ON u.id = s.user_id
        ORDER BY s.searched_at DESC
    ");
}
$searches = $stmt->fetchAll(PDO::FETCH_ASSOC);

$count = count($searches);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Searches - Admin</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="admin-wrapper">
        <aside class="admin-sidebar">
            <div class="sidebar-header">
                <div class="sidebar-logo">MS</div>
                <div class="sidebar-title">Music<span>Streamer</span></div>
            </div>
            <nav class="sidebar-nav">
                <div class="sidebar-nav-label">Menu</div>
                <a href="index.php" class="sidebar-link">
                    <span class="icon">&#9776;</span> Dashboard
                </a>
                <a href="users.php" class="sidebar-link">
                    <span class="icon">&#9787;</span> Users
                </a>
                <a href="searches.php" class="sidebar-link active">
                    <span class="icon">&#128269;</span> Searches
                </a>
                <a href="logout.php" class="sidebar-link">
                    <span class="icon">&#10140;</span> Logout
                </a>
            </nav>
        </aside>

        <main class="admin-main">
            <div class="admin-header">
                <div class="admin-header-left">
                    <h1>Search Queries</h1>
                    <p><?php echo $count; ?> search<?php echo $count !== 1 ? 'es' : ''; ?> recorded</p>
                </div>
                <div class="admin-header-actions">
                    <?php if ($userId > 0): ?>
                        <a href="searches.php" class="btn btn-outline btn-sm">Clear Filter</a>
                    <?php endif; ?>
                </div>
            </div>

            <?php if ($count > 0): ?>
            <div class="table-wrapper">
                <div class="table-toolbar">
                    <div class="table-toolbar-left">
                        <span class="badge badge-info"><?php echo $count; ?> total</span>
                        <?php if ($userId > 0): ?>
                            <span class="badge badge-muted">Filtered by user #<?php echo $userId; ?></span>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>User</th>
                                <th>Search Query</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($searches as $index => $search): ?>
                            <tr>
                                <td><?php echo $index + 1; ?></td>
                                <td>
                                    <div class="table-user">
                                        <div class="table-user-avatar">
                                            <?php echo strtoupper(substr($search['username'], 0, 1)); ?>
                                        </div>
                                        <div>
                                            <div class="table-user-name"><?php echo htmlspecialchars($search['username']); ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge badge-info"><?php echo htmlspecialchars($search['query']); ?></span>
                                </td>
                                <td><?php echo date('M d, Y H:i', strtotime($search['searched_at'])); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php else: ?>
            <div class="empty-state">
                <div class="empty-state-icon">&#128269;</div>
                <h3>No Searches Found</h3>
                <p>There are no search queries recorded yet.</p>
            </div>
            <?php endif; ?>
        </main>
    </div>
</body>
</html>
