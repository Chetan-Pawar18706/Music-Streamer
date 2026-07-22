<?php
require_once '../includes/db.php';
if (!isset($_SESSION['admin_logged_in'])) { header('Location: login.php'); exit; }

$stmt = $pdo->query("
    SELECT
        u.id,
        u.username,
        u.email,
        u.created_at,
        COUNT(DISTINCT us.id) AS song_count,
        COUNT(DISTINCT pl.id) AS playlist_count
    FROM users u
    LEFT JOIN user_songs us ON us.user_id = u.id
    LEFT JOIN playlists pl ON pl.user_id = u.id
    GROUP BY u.id
    ORDER BY u.created_at DESC
");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users - Admin</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="admin-wrapper">
        <aside class="admin-sidebar">
            <div class="sidebar-header">
                <h2>Admin Panel</h2>
            </div>
            <nav class="sidebar-nav">
                <a href="index.php">Dashboard</a>
                <a href="users.php" class="active">Users</a>
                <a href="searches.php">Searches</a>
                <a href="logout.php">Logout</a>
            </nav>
        </aside>

        <main class="admin-main">
            <div class="admin-header">
                <h1>Manage Users</h1>
                <p>View and manage all registered users on the platform.</p>
            </div>

            <?php if (count($users) > 0): ?>
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Username</th>
                            <th>Email</th>
                            <th>Songs Saved</th>
                            <th>Playlists</th>
                            <th>Joined Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $index => $user): ?>
                        <tr>
                            <td><?php echo $index + 1; ?></td>
                            <td><?php echo htmlspecialchars($user['username']); ?></td>
                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                            <td>
                                <span class="badge badge-success">
                                    <?php echo (int)$user['song_count']; ?>
                                </span>
                            </td>
                            <td>
                                <span class="badge badge-success">
                                    <?php echo (int)$user['playlist_count']; ?>
                                </span>
                            </td>
                            <td><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                            <td>
                                <a href="user_detail.php?id=<?php echo (int)$user['id']; ?>" class="btn btn-sm btn-primary">View Detail</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
            <div class="empty-state">
                <h3>No Users Found</h3>
                <p>There are no registered users on the platform yet.</p>
            </div>
            <?php endif; ?>
        </main>
    </div>
</body>
</html>
