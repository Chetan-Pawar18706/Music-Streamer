<?php
require_once '../includes/db.php';
if (!isset($_SESSION['user_logged_in'])) { header('Location: login.php'); exit; }

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) { header('Location: playlists.php'); exit; }

$playlist_id = (int)$_GET['id'];
$userId = $_SESSION['user_id'];

$stmt = $pdo->prepare("SELECT * FROM playlists WHERE id = ? AND user_id = ?");
$stmt->execute([$playlist_id, $userId]);
$playlist = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$playlist) { header('Location: playlists.php'); exit; }

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    $stmt = $pdo->prepare("DELETE FROM playlists WHERE id = ? AND user_id = ?");
    $stmt->execute([$playlist_id, $userId]);
    header('Location: playlists.php');
    exit;
}

$stmt = $pdo->prepare("SELECT us.*, ps.id as ps_id FROM user_songs us JOIN playlist_songs ps ON us.id = ps.song_id WHERE ps.playlist_id = ? ORDER BY ps.added_at ASC");
$stmt->execute([$playlist_id]);
$songs = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($playlist['name']); ?></title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="../style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        .pl-header { display: flex; align-items: center; gap: 24px; margin-bottom: 32px; padding-bottom: 24px; border-bottom: 1px solid var(--border); }
        .pl-header .cover { width: 180px; height: 180px; border-radius: 12px; background: linear-gradient(135deg, var(--primary), var(--accent)); display: flex; align-items: center; justify-content: center; font-size: 4rem; flex-shrink: 0; }
        .pl-header .details h1 { font-size: 2rem; margin-bottom: 8px; }
        .pl-header .details .meta { color: var(--text-muted); font-size: 0.9rem; margin-bottom: 16px; }
        .song-list { display: flex; flex-direction: column; }
        .song-row { display: grid; grid-template-columns: 40px 50px 1fr 1fr auto; gap: 16px; align-items: center; padding: 10px 12px; border-radius: 8px; transition: background 0.2s; cursor: pointer; }
        .song-row:hover { background: var(--card); }
        .song-row .num { text-align: center; color: var(--text-muted); font-size: 0.9rem; }
        .song-row .cover { width: 50px; height: 50px; border-radius: 6px; object-fit: cover; }
        .song-row .title { font-weight: 600; font-size: 0.9rem; }
        .song-row .artist-name { color: var(--text-muted); font-size: 0.85rem; }
        .song-row .actions { display: flex; gap: 8px; }
        .song-row .actions button { padding: 6px 12px; border-radius: 6px; border: none; cursor: pointer; font-size: 0.75rem; font-weight: 600; }
        .btn-play-row { background: var(--primary); color: #fff; }
        .btn-play-row:hover { background: #cc0000; }
        .btn-remove-row { background: transparent; color: var(--danger); border: 1px solid var(--border) !important; }
        .btn-remove-row:hover { border-color: var(--danger) !important; }
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
        <a href="playlists.php" style="color:var(--text-muted); font-size:0.9rem; display:inline-block; margin-bottom:16px;">&larr; Back to Playlists</a>

        <div class="pl-header">
            <div class="cover">&#127925;</div>
            <div class="details">
                <h1><?php echo htmlspecialchars($playlist['name']); ?></h1>
                <div class="meta"><?php echo count($songs); ?> songs &middot; Created <?php echo date('M j, Y', strtotime($playlist['created_at'])); ?></div>
                <form method="post" style="display:inline;" onsubmit="return confirm('Delete this playlist?')">
                    <input type="hidden" name="action" value="delete">
                    <button type="submit" class="btn btn-ghost btn-sm" style="color:var(--danger);">Delete Playlist</button>
                </form>
            </div>
        </div>

        <?php if (empty($songs)): ?>
        <div style="text-align:center; padding:64px 24px; color:var(--text-muted);">
            <div style="font-size:3rem; margin-bottom:16px;">&#127925;</div>
            <h2 style="color:var(--text); margin-bottom:8px;">This playlist is empty</h2>
            <p style="margin-bottom:16px;">Add songs from your library or search for music.</p>
            <a href="search.php" style="color:var(--primary); font-weight:600;">Search Music &rarr;</a>
        </div>
        <?php else: ?>
        <div class="song-list">
            <?php foreach ($songs as $i => $song): ?>
            <div class="song-row" id="ps-<?php echo $song['ps_id']; ?>">
                <div class="num"><?php echo $i + 1; ?></div>
                <img class="cover" src="<?php echo htmlspecialchars($song['cover_image'] ?: 'https://via.placeholder.com/50'); ?>" alt="">
                <div class="title"><?php echo htmlspecialchars($song['title']); ?></div>
                <div class="artist-name"><?php echo htmlspecialchars($song['artist']); ?></div>
                <div class="actions">
                    <button class="btn-play-row" onclick="playSong('<?php echo htmlspecialchars($song['youtube_id']); ?>', '<?php echo htmlspecialchars(addslashes($song['title'])); ?>', '<?php echo htmlspecialchars(addslashes($song['artist'])); ?>', '<?php echo htmlspecialchars($song['cover_image']); ?>')">&#9654;</button>
                    <button class="btn-remove-row" onclick="removeFromPlaylist(<?php echo $song['ps_id']; ?>)">&#10005;</button>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </main>
</div>

<div id="toastContainer"></div>

<script>
function playSong(ytId, title, artist, cover) {
    localStorage.setItem('currentSong', JSON.stringify({
        youtubeId: ytId, title: title, artist: artist, cover: cover
    }));
    window.location.href = '../player.php';
}

function removeFromPlaylist(psId) {
    if (!confirm('Remove this song?')) return;

    fetch('../api/remove_from_playlist.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'playlist_id=<?php echo $playlist_id; ?>&song_id=' + psId
    })
    .then(function(r) { return r.json(); })
    .then(function(d) {
        if (d.success) {
            var el = document.getElementById('ps-' + psId);
            if (el) { el.style.transition = 'opacity 0.3s'; el.style.opacity = '0'; setTimeout(function() { el.remove(); }, 300); }
            showToast('Removed from playlist', 'success');
        }
    })
    .catch(function() { showToast('Error', 'error'); });
}

function showToast(msg, type) {
    var t = document.createElement('div');
    t.className = 'toast ' + (type || 'info');
    t.textContent = msg;
    document.getElementById('toastContainer').appendChild(t);
    setTimeout(function() { if (t.parentNode) t.parentNode.removeChild(t); }, 3000);
}
</script>

</body>
</html>
