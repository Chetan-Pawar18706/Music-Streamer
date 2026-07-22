<?php
require_once '../includes/db.php';
if (!isset($_SESSION['user_logged_in'])) { header('Location: login.php'); exit; }

$userId = $_SESSION['user_id'];

if (isset($_GET['fetch_as_json'])) {
    $stmt = $pdo->prepare("SELECT id, name FROM playlists WHERE user_id = ? ORDER BY name ASC");
    $stmt->execute([$userId]);
    header('Content-Type: application/json');
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    exit;
}

$message = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['create_playlist'])) {
    $playlist_name = trim($_POST['playlist_name'] ?? '');
    if ($playlist_name === '') {
        $message = "Playlist name cannot be empty.";
    } else {
        $stmt = $pdo->prepare("INSERT INTO playlists (user_id, name) VALUES (?, ?)");
        $stmt->execute([$userId, $playlist_name]);
        $message = "Playlist created!";
    }
}

if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $stmt = $pdo->prepare("DELETE FROM playlists WHERE id = ? AND user_id = ?");
    $stmt->execute([$_GET['delete'], $userId]);
    header('Location: playlists.php');
    exit;
}

$stmt = $pdo->prepare("SELECT p.*, (SELECT COUNT(*) FROM playlist_songs WHERE playlist_id = p.id) as song_count FROM playlists p WHERE p.user_id = ? ORDER BY p.created_at DESC");
$stmt->execute([$userId]);
$playlists = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Playlists</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="../style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        .pl-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(240px, 1fr)); gap: 16px; }
        .pl-card { background: var(--card); border-radius: 12px; padding: 20px; border: 1px solid var(--border); transition: all 0.2s; }
        .pl-card:hover { border-color: var(--primary); transform: translateY(-2px); }
        .pl-card h3 { font-size: 1.1rem; margin-bottom: 8px; }
        .pl-card .meta { font-size: 0.8rem; color: var(--text-muted); margin-bottom: 16px; }
        .pl-card .pl-actions { display: flex; gap: 8px; }
        .create-form { display: flex; gap: 12px; margin-bottom: 24px; }
        .create-form input { flex: 1; padding: 12px 16px; background: var(--card); border: 1px solid var(--border); border-radius: 8px; color: var(--text); font-size: 0.9rem; outline: none; }
        .create-form input:focus { border-color: var(--primary); }
        .toast { position: fixed; bottom: 24px; right: 24px; padding: 12px 20px; border-radius: 8px; font-size: 0.85rem; z-index: 9999; animation: slideIn 0.3s ease; }
        .toast.success { background: #1a3a1a; border: 1px solid var(--success); color: var(--success); }
        @keyframes slideIn { from { transform: translateY(20px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
    </style>
</head>
<body>

<nav class="user-topnav">
    <a href="dashboard.php" class="topnav-logo">&#9835; <span>MusicStream</span></a>
    <div class="topnav-search">
        <form class="search-pill" onsubmit="return false;">
            <input type="text" id="topnavSearchInput" placeholder="Search music..." autocomplete="off">
            <button type="button" class="search-submit" onclick="window.location.href='search.php?q='+document.getElementById('topnavSearchInput').value">&#128269;</button>
        </form>
    </div>
    <div class="topnav-user">
        <a href="dashboard.php" class="nav-link">Dashboard</a>
        <a href="logout.php" class="nav-link">Logout</a>
    </div>
</nav>

<div class="user-layout">
    <aside class="user-sidebar">
        <div class="nav-section">
            <div class="nav-section-title">Menu</div>
            <a href="dashboard.php" class="sidebar-link"><span class="link-icon">&#127968;</span> Dashboard</a>
            <a href="search.php" class="sidebar-link"><span class="link-icon">&#128269;</span> Search</a>
            <a href="library.php" class="sidebar-link"><span class="link-icon">&#128190;</span> Library</a>
            <a href="playlists.php" class="sidebar-link active"><span class="link-icon">&#127925;</span> Playlists</a>
        </div>
        <div class="nav-section">
            <div class="nav-section-title">Account</div>
            <a href="logout.php" class="sidebar-link"><span class="link-icon">&#10140;</span> Logout</a>
        </div>
    </aside>

    <main class="user-content">
        <h1 style="font-size:1.8rem; margin-bottom:24px;">My Playlists</h1>

        <?php if ($message): ?>
            <div style="padding:12px 16px; background:rgba(0,200,83,0.1); border:1px solid var(--success); border-radius:8px; color:var(--success); margin-bottom:16px;"><?php echo $message; ?></div>
        <?php endif; ?>

        <form class="create-form" method="post">
            <input type="text" name="playlist_name" placeholder="New playlist name..." required>
            <button type="submit" name="create_playlist" class="btn btn-primary">Create</button>
        </form>

        <?php if (empty($playlists)): ?>
        <div style="text-align:center; padding:64px 24px; color:var(--text-muted);">
            <div style="font-size:3rem; margin-bottom:16px;">&#127925;</div>
            <h2 style="color:var(--text); margin-bottom:8px;">No playlists yet</h2>
            <p>Create your first playlist above.</p>
        </div>
        <?php else: ?>
        <div class="pl-grid">
            <?php foreach ($playlists as $pl): ?>
            <div class="pl-card">
                <h3><?php echo htmlspecialchars($pl['name']); ?></h3>
                <div class="meta"><?php echo $pl['song_count']; ?> songs &middot; <?php echo date('M j, Y', strtotime($pl['created_at'])); ?></div>
                <div class="pl-actions">
                    <a href="view_playlist.php?id=<?php echo $pl['id']; ?>" class="btn btn-primary btn-sm">View Songs</a>
                    <a href="playlists.php?delete=<?php echo $pl['id']; ?>" class="btn btn-ghost btn-sm" style="color:var(--danger);" onclick="return confirm('Delete this playlist?')">Delete</a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </main>
</div>

<div id="toastContainer"></div>

</body>
</html>
