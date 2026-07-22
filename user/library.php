<?php
require_once '../includes/db.php';
if (!isset($_SESSION['user_logged_in'])) { header('Location: login.php'); exit; }

$userId = $_SESSION['user_id'];
$sort = $_GET['sort'] ?? 'recent';
$orderBy = match($sort) {
    'title'  => 'us.title ASC',
    'artist' => 'us.artist ASC',
    default  => 'us.added_at DESC',
};

$stmt = $pdo->prepare("SELECT us.* FROM user_songs us WHERE us.user_id = ? ORDER BY $orderBy");
$stmt->execute([$userId]);
$songs = $stmt->fetchAll(PDO::FETCH_ASSOC);
$songCount = count($songs);

$stmt2 = $pdo->prepare("SELECT COUNT(*) FROM playlists WHERE user_id = ?");
$stmt2->execute([$userId]);
$playlistCount = $stmt2->fetchColumn();
$playlists = $pdo->prepare("SELECT id, name FROM playlists WHERE user_id = ? ORDER BY name");
$playlists->execute([$userId]);
$playlists = $playlists->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Library</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="../style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        .lib-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 24px; flex-wrap: wrap; gap: 16px; }
        .lib-header h1 { font-size: 1.8rem; }
        .lib-controls { display: flex; gap: 12px; align-items: center; }
        .sort-select { padding: 8px 14px; background: var(--card); border: 1px solid var(--border); border-radius: 8px; color: var(--text); font-size: 0.85rem; }
        .lib-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(180px, 1fr)); gap: 16px; }
        .lib-card { background: var(--card); border-radius: 12px; overflow: hidden; transition: all 0.2s; border: 1px solid transparent; }
        .lib-card:hover { border-color: var(--border); transform: translateY(-2px); }
        .lib-card img { width: 100%; aspect-ratio: 16/9; object-fit: cover; display: block; }
        .lib-card .info { padding: 12px; }
        .lib-card .title { font-size: 0.85rem; font-weight: 600; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .lib-card .artist { font-size: 0.75rem; color: var(--text-muted); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; margin-top: 2px; }
        .lib-card .actions { display: flex; gap: 6px; padding: 0 12px 12px; }
        .lib-card .actions button { flex: 1; padding: 6px; border-radius: 6px; border: none; cursor: pointer; font-size: 0.75rem; font-weight: 600; transition: all 0.2s; }
        .btn-play-card { background: var(--primary); color: #fff; }
        .btn-play-card:hover { background: #cc0000; }
        .btn-add-playlist { background: var(--surface); color: var(--text-muted); border: 1px solid var(--border) !important; }
        .btn-add-playlist:hover { color: var(--text); border-color: var(--text-muted) !important; }
        .btn-remove-card { background: transparent; color: var(--danger); border: 1px solid transparent !important; }
        .btn-remove-card:hover { border-color: var(--danger) !important; }
        .playlist-modal { display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.7); z-index: 1000; align-items: center; justify-content: center; }
        .playlist-modal.open { display: flex; }
        .modal-box { background: var(--card); border-radius: 12px; padding: 24px; max-width: 400px; width: 90%; border: 1px solid var(--border); }
        .modal-box h3 { margin-bottom: 16px; }
        .modal-box select { width: 100%; padding: 10px; background: var(--surface); border: 1px solid var(--border); border-radius: 8px; color: var(--text); margin-bottom: 16px; }
        .modal-box .modal-btns { display: flex; gap: 8px; justify-content: flex-end; }
        .toast { position: fixed; bottom: 24px; right: 24px; padding: 12px 20px; border-radius: 8px; font-size: 0.85rem; z-index: 9999; animation: slideIn 0.3s ease; box-shadow: 0 4px 20px rgba(0,0,0,0.4); }
        .toast.success { background: #1a3a1a; border: 1px solid var(--success); color: var(--success); }
        .toast.error { background: #3a1a1a; border: 1px solid var(--danger); color: var(--danger); }
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
            <a href="library.php" class="sidebar-link active"><span class="link-icon">&#128190;</span> Library</a>
            <a href="playlists.php" class="sidebar-link"><span class="link-icon">&#127925;</span> Playlists</a>
        </div>
        <div class="nav-section">
            <div class="nav-section-title">Account</div>
            <a href="logout.php" class="sidebar-link"><span class="link-icon">&#10140;</span> Logout</a>
        </div>
    </aside>

    <main class="user-content">
        <div class="lib-header">
            <h1>My Library <span style="font-size:0.9rem; color:var(--text-muted); font-weight:400;">(<?php echo $songCount; ?> songs)</span></h1>
            <div class="lib-controls">
                <select class="sort-select" onchange="window.location.href='?sort='+this.value">
                    <option value="recent" <?php echo $sort === 'recent' ? 'selected' : ''; ?>>Recently Added</option>
                    <option value="title" <?php echo $sort === 'title' ? 'selected' : ''; ?>>Title A-Z</option>
                    <option value="artist" <?php echo $sort === 'artist' ? 'selected' : ''; ?>>Artist A-Z</option>
                </select>
            </div>
        </div>

        <?php if (empty($songs)): ?>
        <div style="text-align:center; padding:64px 24px; color:var(--text-muted);">
            <div style="font-size:3rem; margin-bottom:16px;">&#128190;</div>
            <h2 style="color:var(--text); margin-bottom:8px;">Your library is empty</h2>
            <p style="margin-bottom:16px;">Start by searching for songs and adding them here.</p>
            <a href="search.php" style="color:var(--primary); font-weight:600;">Search Music &rarr;</a>
        </div>
        <?php else: ?>
        <div class="lib-grid">
            <?php foreach ($songs as $song): ?>
            <div class="lib-card" id="song-<?php echo $song['id']; ?>">
                <img src="<?php echo htmlspecialchars($song['cover_image'] ?: 'https://via.placeholder.com/300'); ?>" alt="" loading="lazy">
                <div class="info">
                    <div class="title"><?php echo htmlspecialchars($song['title']); ?></div>
                    <div class="artist"><?php echo htmlspecialchars($song['artist']); ?></div>
                </div>
                <div class="actions">
                    <button class="btn-play-card" onclick="playSong('<?php echo htmlspecialchars($song['youtube_id']); ?>', '<?php echo htmlspecialchars(addslashes($song['title'])); ?>', '<?php echo htmlspecialchars(addslashes($song['artist'])); ?>', '<?php echo htmlspecialchars($song['cover_image']); ?>')">&#9654; Play</button>
                    <button class="btn-add-playlist" onclick="openPlaylistModal(<?php echo $song['id']; ?>, '<?php echo htmlspecialchars(addslashes($song['title'])); ?>')">&#127925;</button>
                    <button class="btn-remove-card" onclick="removeSong(<?php echo $song['id']; ?>)">&#10005;</button>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </main>
</div>

<div class="playlist-modal" id="playlistModal">
    <div class="modal-box">
        <h3>Add to Playlist</h3>
        <select id="playlistSelect">
            <?php if (empty($playlists)): ?>
                <option value="">No playlists found</option>
            <?php else: ?>
                <?php foreach ($playlists as $p): ?>
                <option value="<?php echo $p['id']; ?>"><?php echo htmlspecialchars($p['name']); ?></option>
                <?php endforeach; ?>
            <?php endif; ?>
        </select>
        <div class="modal-btns">
            <button class="btn btn-ghost btn-sm" onclick="closePlaylistModal()">Cancel</button>
            <button class="btn btn-primary btn-sm" onclick="addToPlaylist()">Add</button>
        </div>
    </div>
</div>

<div id="toastContainer"></div>

<script>
var currentSongId = null;

function playSong(ytId, title, artist, cover) {
    localStorage.setItem('currentSong', JSON.stringify({
        youtubeId: ytId, title: title, artist: artist, cover: cover
    }));
    window.location.href = '../player.php';
}

function openPlaylistModal(songId, songTitle) {
    currentSongId = songId;
    document.getElementById('playlistModal').classList.add('open');
}

function closePlaylistModal() {
    document.getElementById('playlistModal').classList.remove('open');
    currentSongId = null;
}

function addToPlaylist() {
    var playlistId = document.getElementById('playlistSelect').value;
    if (!playlistId || !currentSongId) { showToast('Select a playlist', 'error'); return; }

    var fd = new FormData();
    fd.append('playlist_id', playlistId);
    fd.append('song_id', currentSongId);

    fetch('../api/add_to_playlist.php', { method: 'POST', body: fd })
        .then(function(r) { return r.json(); })
        .then(function(d) {
            closePlaylistModal();
            showToast(d.message || 'Done', d.success ? 'success' : 'error');
        })
        .catch(function() { showToast('Error', 'error'); });
}

function removeSong(songId) {
    if (!confirm('Remove this song from library?')) return;

    fetch('../api/remove_user_song.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ song_id: songId })
    })
    .then(function(r) { return r.json(); })
    .then(function(d) {
        if (d.success) {
            var el = document.getElementById('song-' + songId);
            if (el) { el.style.transition = 'opacity 0.3s'; el.style.opacity = '0'; setTimeout(function() { el.remove(); }, 300); }
            showToast('Removed from library', 'success');
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
