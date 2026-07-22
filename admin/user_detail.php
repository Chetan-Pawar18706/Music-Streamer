<?php
require_once '../includes/db.php';
if (!isset($_SESSION['admin_logged_in'])) { header('Location: login.php'); exit; }

$user_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($user_id <= 0) { header('Location: users.php'); exit; }

$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_user'])) {
    try {
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        $_SESSION['message'] = 'User deleted successfully.';
        header('Location: users.php');
        exit;
    } catch (PDOException $e) {
        $message = 'Failed to delete user.';
        $message_type = 'danger';
    }
}

$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) { header('Location: users.php'); exit; }

$stmt = $pdo->prepare("SELECT COUNT(*) FROM user_songs WHERE user_id = ?");
$stmt->execute([$user_id]);
$total_songs = $stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT COUNT(*) FROM playlists WHERE user_id = ?");
$stmt->execute([$user_id]);
$total_playlists = $stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT us.*, (SELECT COUNT(*) FROM playlist_songs ps WHERE ps.song_id = us.id) AS in_playlists FROM user_songs us WHERE us.user_id = ? ORDER BY us.added_at DESC");
$stmt->execute([$user_id]);
$songs = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $pdo->prepare("
    SELECT p.*, (SELECT COUNT(*) FROM playlist_songs ps WHERE ps.playlist_id = p.id) AS song_count
    FROM playlists p
    WHERE p.user_id = ?
    ORDER BY p.created_at DESC
");
$stmt->execute([$user_id]);
$playlists = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $pdo->prepare("SELECT * FROM user_searches WHERE user_id = ? ORDER BY searched_at DESC");
$stmt->execute([$user_id]);
$searches = $stmt->fetchAll(PDO::FETCH_ASSOC);

$active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'songs';
$valid_tabs = ['songs', 'playlists', 'searches'];
if (!in_array($active_tab, $valid_tabs)) { $active_tab = 'songs'; }

$totalUsers = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$totalSearchesAll = $pdo->query("SELECT COUNT(*) FROM user_searches")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($user['username']); ?> - User Detail - Admin</title>
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
                <a href="index.php" class="sidebar-link">
                    <span class="icon">&#9632;</span> Dashboard
                </a>
                <a href="users.php" class="sidebar-link active">
                    <span class="icon">&#9786;</span> Users
                    <span class="badge-count"><?php echo $totalUsers; ?></span>
                </a>
                <a href="searches.php" class="sidebar-link">
                    <span class="icon">&#128269;</span> Searches
                    <span class="badge-count"><?php echo $totalSearchesAll; ?></span>
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
                    <a href="users.php" class="btn btn-outline btn-sm" style="margin-bottom: 12px;">&larr; Back to Users</a>
                    <h1>User Profile</h1>
                    <p>Viewing details for <?php echo htmlspecialchars($user['username']); ?></p>
                </div>
            </div>

            <?php if ($message): ?>
            <div class="alert alert-<?php echo $message_type; ?>"><?php echo $message; ?></div>
            <?php endif; ?>

            <div class="user-profile-card">
                <div class="user-profile-header">
                    <div class="user-profile-avatar"><?php echo strtoupper(substr($user['username'], 0, 1)); ?></div>
                    <div class="user-profile-info">
                        <h2><?php echo htmlspecialchars($user['username']); ?></h2>
                        <p><?php echo htmlspecialchars($user['email']); ?></p>
                        <div class="user-profile-meta">
                            <span>&#128197; Member since <?php echo date('M d, Y', strtotime($user['created_at'])); ?></span>
                        </div>
                    </div>
                </div>
                <div class="user-stats-row">
                    <div class="user-stat">
                        <div class="user-stat-number"><?php echo $total_songs; ?></div>
                        <div class="user-stat-label">Songs Saved</div>
                    </div>
                    <div class="user-stat">
                        <div class="user-stat-number"><?php echo $total_playlists; ?></div>
                        <div class="user-stat-label">Playlists</div>
                    </div>
                    <div class="user-stat">
                        <div class="user-stat-number"><?php echo count($searches); ?></div>
                        <div class="user-stat-label">Searches</div>
                    </div>
                </div>
            </div>

            <div class="admin-tabs">
                <a href="?id=<?php echo $user_id; ?>&tab=songs" class="admin-tab <?php echo $active_tab === 'songs' ? 'active' : ''; ?>">Songs (<?php echo $total_songs; ?>)</a>
                <a href="?id=<?php echo $user_id; ?>&tab=playlists" class="admin-tab <?php echo $active_tab === 'playlists' ? 'active' : ''; ?>">Playlists (<?php echo $total_playlists; ?>)</a>
                <a href="?id=<?php echo $user_id; ?>&tab=searches" class="admin-tab <?php echo $active_tab === 'searches' ? 'active' : ''; ?>">Search History (<?php echo count($searches); ?>)</a>
            </div>

            <?php if ($active_tab === 'songs'): ?>
            <?php if (!empty($songs)): ?>
            <div class="table-wrapper">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Title</th>
                            <th>Artist</th>
                            <th>Added Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($songs as $index => $song): ?>
                        <tr>
                            <td><?php echo $index + 1; ?></td>
                            <td><?php echo htmlspecialchars($song['title']); ?></td>
                            <td><?php echo htmlspecialchars($song['artist']); ?></td>
                            <td><?php echo date('M d, Y', strtotime($song['added_at'])); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
            <div class="empty-state">
                <div class="empty-state-icon">&#9835;</div>
                <h3>No Songs Saved</h3>
                <p>This user hasn't saved any songs yet.</p>
            </div>
            <?php endif; ?>

            <?php elseif ($active_tab === 'playlists'): ?>
            <?php if (!empty($playlists)): ?>
            <div class="table-wrapper">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Playlist Name</th>
                            <th>Songs</th>
                            <th>Created</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($playlists as $index => $playlist): ?>
                        <tr>
                            <td><?php echo $index + 1; ?></td>
                            <td><?php echo htmlspecialchars($playlist['name']); ?></td>
                            <td><span class="badge badge-info"><?php echo (int)$playlist['song_count']; ?></span></td>
                            <td><?php echo date('M d, Y', strtotime($playlist['created_at'])); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
            <div class="empty-state">
                <div class="empty-state-icon">&#9833;</div>
                <h3>No Playlists</h3>
                <p>This user hasn't created any playlists yet.</p>
            </div>
            <?php endif; ?>

            <?php elseif ($active_tab === 'searches'): ?>
            <?php if (!empty($searches)): ?>
            <div class="table-wrapper">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Search Query</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($searches as $index => $search): ?>
                        <tr>
                            <td><?php echo $index + 1; ?></td>
                            <td><?php echo htmlspecialchars($search['query']); ?></td>
                            <td><?php echo date('M d, Y g:i A', strtotime($search['searched_at'])); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
            <div class="empty-state">
                <div class="empty-state-icon">&#128269;</div>
                <h3>No Search History</h3>
                <p>This user hasn't performed any searches yet.</p>
            </div>
            <?php endif; ?>
            <?php endif; ?>

            <div style="margin-top: 40px; padding-top: 24px; border-top: 1px solid var(--border);">
                <h3 style="color: var(--danger); font-size: 15px; margin-bottom: 12px;">Danger Zone</h3>
                <form method="POST" onsubmit="return confirm('Are you sure you want to permanently delete this user and all their data? This action cannot be undone.');">
                    <input type="hidden" name="delete_user" value="1">
                    <button type="submit" class="btn btn-danger">Delete User</button>
                </form>
            </div>
        </main>
    </div>
</body>
</html>
