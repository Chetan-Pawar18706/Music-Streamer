<?php
require_once '../includes/db.php';
if (!isset($_SESSION['user_logged_in'])) { header('Location: login.php'); exit; }

$userId = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT username, email, created_at FROM users WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

$stmt = $pdo->prepare("SELECT COUNT(*) FROM user_songs WHERE user_id = ?");
$stmt->execute([$userId]);
$songCount = $stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT COUNT(*) FROM playlists WHERE user_id = ?");
$stmt->execute([$userId]);
$playlistCount = $stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT * FROM user_songs WHERE user_id = ? ORDER BY added_at DESC LIMIT 6");
$stmt->execute([$userId]);
$recentSongs = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $pdo->prepare("SELECT query FROM user_searches WHERE user_id = ? ORDER BY searched_at DESC LIMIT 5");
$stmt->execute([$userId]);
$recentSearches = $stmt->fetchAll(PDO::FETCH_COLUMN);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="../style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        .welcome-hero { background: linear-gradient(135deg, #1a0505 0%, #0f0f0f 100%); border-radius: 12px; padding: 40px; margin-bottom: 24px; }
        .welcome-hero h1 { font-size: 2rem; margin-bottom: 8px; }
        .welcome-hero p { color: var(--text-muted); }
        .stats-row { display: flex; gap: 16px; margin-bottom: 24px; }
        .stat-card { flex: 1; padding: 20px; background: var(--card); border-radius: 12px; text-align: center; }
        .stat-num { font-size: 2rem; font-weight: 800; color: var(--primary); }
        .stat-lbl { font-size: 0.85rem; color: var(--text-muted); margin-top: 4px; }
        .section-title { font-size: 1.2rem; font-weight: 700; margin-bottom: 16px; }
        .section-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 16px; }
        .section-header h2 { font-size: 1.2rem; font-weight: 700; margin: 0; }
        .section-header a { color: var(--primary); text-decoration: none; font-size: 0.85rem; font-weight: 600; }
        .section-header a:hover { text-decoration: underline; }
        .recent-scroll { display: flex; gap: 16px; overflow-x: auto; padding: 8px 0 16px; }
        .recent-scroll::-webkit-scrollbar { height: 6px; }
        .recent-scroll::-webkit-scrollbar-thumb { background: var(--border); border-radius: 3px; }
        .recent-card { min-width: 160px; max-width: 160px; background: var(--card); border-radius: 12px; overflow: hidden; cursor: pointer; transition: all 0.2s; border: 1px solid transparent; position: relative; }
        .recent-card:hover { border-color: var(--border); transform: translateY(-2px); }
        .recent-card img { width: 100%; aspect-ratio: 16/9; object-fit: cover; }
        .recent-card .info { padding: 10px; }
        .recent-card .title { font-size: 0.8rem; font-weight: 600; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .recent-card .artist { font-size: 0.7rem; color: var(--text-muted); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .recent-card .play-overlay { position: absolute; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); display: flex; align-items: center; justify-content: center; opacity: 0; transition: opacity 0.2s; }
        .recent-card:hover .play-overlay { opacity: 1; }
        .play-circle { width: 48px; height: 48px; border-radius: 50%; background: var(--primary); color: #fff; display: flex; align-items: center; justify-content: center; font-size: 1.2rem; border: none; cursor: pointer; }
        .play-circle:hover { transform: scale(1.1); background: #cc0000; }
        .rec-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(180px, 1fr)); gap: 16px; margin-bottom: 32px; }
        .rec-card { background: var(--card); border-radius: 12px; overflow: hidden; transition: all 0.2s; border: 1px solid transparent; position: relative; }
        .rec-card:hover { border-color: var(--border); transform: translateY(-2px); }
        .rec-card .thumb-wrap { position: relative; }
        .rec-card img { width: 100%; aspect-ratio: 16/9; object-fit: cover; display: block; }
        .rec-card .rec-actions { position: absolute; bottom: 8px; right: 8px; display: flex; gap: 6px; opacity: 0; transition: opacity 0.2s; }
        .rec-card:hover .rec-actions { opacity: 1; }
        .rec-card .info { padding: 10px; }
        .rec-card .title { font-size: 0.8rem; font-weight: 600; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; color: var(--text); }
        .rec-card .artist { font-size: 0.7rem; color: var(--text-muted); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; margin-top: 2px; }
        .btn-icon { width: 34px; height: 34px; border-radius: 50%; border: none; cursor: pointer; display: flex; align-items: center; justify-content: center; font-size: 0.9rem; transition: all 0.2s; }
        .btn-play { background: var(--primary); color: #fff; }
        .btn-play:hover { background: #cc0000; transform: scale(1.1); }
        .btn-add { background: rgba(0,0,0,0.7); color: #fff; backdrop-filter: blur(4px); }
        .btn-add:hover { background: var(--primary); }
        .btn-add.active { background: var(--success); pointer-events: none; }
        .quick-links { display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 16px; margin-top: 24px; }
        .ql-card { display: flex; align-items: center; gap: 16px; padding: 20px; background: var(--card); border-radius: 12px; text-decoration: none; color: var(--text); transition: all 0.2s; border: 1px solid transparent; }
        .ql-card:hover { border-color: var(--primary); transform: translateY(-2px); }
        .ql-card .icon { font-size: 1.5rem; }
        .ql-card .label { font-weight: 600; }
        .toast { position: fixed; bottom: 24px; right: 24px; padding: 12px 20px; border-radius: 8px; font-size: 0.85rem; z-index: 9999; animation: slideIn 0.3s ease; box-shadow: 0 4px 20px rgba(0,0,0,0.4); }
        .toast.success { background: #1a3a1a; border: 1px solid var(--success); color: var(--success); }
        .toast.error { background: #3a1a1a; border: 1px solid var(--danger); color: var(--danger); }
        @keyframes slideIn { from { transform: translateY(20px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
        .search-tags { display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 24px; }
        .search-tag { padding: 6px 14px; background: var(--card); border: 1px solid var(--border); border-radius: 999px; color: var(--text-muted); font-size: 0.8rem; cursor: pointer; transition: all 0.2s; text-decoration: none; }
        .search-tag:hover { border-color: var(--primary); color: var(--primary); }
        .rec-loading { text-align: center; padding: 40px; color: var(--text-muted); }
        .rec-loading .spinner { width: 32px; height: 32px; border: 3px solid var(--border); border-top-color: var(--primary); border-radius: 50%; animation: spin 0.8s linear infinite; margin: 0 auto 12px; }
        @keyframes spin { to { transform: rotate(360deg); } }
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
        <a href="logout.php" class="nav-link">Logout</a>
        <button class="user-avatar-btn"><?php echo strtoupper(substr($user['username'], 0, 1)); ?></button>
    </div>
</nav>

<div class="user-layout">
    <aside class="user-sidebar">
        <div class="nav-section">
            <div class="nav-section-title">Menu</div>
            <a href="dashboard.php" class="sidebar-link active"><span class="link-icon">&#127968;</span> Dashboard</a>
            <a href="search.php" class="sidebar-link"><span class="link-icon">&#128269;</span> Search</a>
            <a href="library.php" class="sidebar-link"><span class="link-icon">&#128190;</span> Library</a>
            <a href="playlists.php" class="sidebar-link"><span class="link-icon">&#127925;</span> Playlists</a>
        </div>
        <div class="nav-section">
            <div class="nav-section-title">Account</div>
            <a href="logout.php" class="sidebar-link"><span class="link-icon">&#10140;</span> Logout</a>
        </div>
    </aside>

    <main class="user-content">
        <div class="welcome-hero">
            <h1>Welcome back, <?php echo htmlspecialchars($user['username']); ?>!</h1>
            <p>Member since <?php echo date('F j, Y', strtotime($user['created_at'])); ?></p>
        </div>

        <div class="search-tags">
            <a href="search.php?q=bollywood" class="search-tag">Bollywood</a>
            <a href="search.php?q=english hits" class="search-tag">English Hits</a>
            <a href="search.php?q=punjabi songs" class="search-tag">Punjabi</a>
            <a href="search.php?q=romantic" class="search-tag">Romantic</a>
            <a href="search.php?q=workout music" class="search-tag">Workout</a>
            <a href="search.php?q=lofi" class="search-tag">Lo-fi</a>
            <a href="search.php?q=party songs" class="search-tag">Party</a>
            <a href="search.php?q=classical" class="search-tag">Classical</a>
        </div>

        <div class="stats-row">
            <div class="stat-card">
                <div class="stat-num"><?php echo $songCount; ?></div>
                <div class="stat-lbl">Songs in Library</div>
            </div>
            <div class="stat-card">
                <div class="stat-num"><?php echo $playlistCount; ?></div>
                <div class="stat-lbl">Playlists Created</div>
            </div>
        </div>

        <?php if (!empty($recentSongs)): ?>
        <div class="section-header">
            <h2>Your Library</h2>
            <a href="library.php">View All &#8594;</a>
        </div>
        <div class="recent-scroll">
            <?php foreach ($recentSongs as $song): ?>
            <div class="recent-card" onclick="playSong('<?php echo htmlspecialchars($song['youtube_id']); ?>', '<?php echo htmlspecialchars(addslashes($song['title'])); ?>', '<?php echo htmlspecialchars(addslashes($song['artist'])); ?>', '<?php echo htmlspecialchars($song['cover_image']); ?>')">
                <img src="<?php echo htmlspecialchars($song['cover_image'] ?: 'https://via.placeholder.com/300'); ?>" alt="" loading="lazy">
                <div class="play-overlay">
                    <button class="play-circle" onclick="event.stopPropagation(); playSong('<?php echo htmlspecialchars($song['youtube_id']); ?>', '<?php echo htmlspecialchars(addslashes($song['title'])); ?>', '<?php echo htmlspecialchars(addslashes($song['artist'])); ?>', '<?php echo htmlspecialchars($song['cover_image']); ?>')">&#9654;</button>
                </div>
                <div class="info">
                    <div class="title"><?php echo htmlspecialchars($song['title']); ?></div>
                    <div class="artist"><?php echo htmlspecialchars($song['artist']); ?></div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <div class="section-header">
            <h2>Recommended For You</h2>
        </div>
        <div id="recLoading" class="rec-loading">
            <div class="spinner"></div>
            <p>Finding songs for you...</p>
        </div>
        <div id="recGrid" class="rec-grid"></div>

        <div class="quick-links">
            <a href="search.php" class="ql-card"><span class="icon">&#128269;</span><span class="label">Search Music</span></a>
            <a href="library.php" class="ql-card"><span class="icon">&#128190;</span><span class="label">My Library</span></a>
            <a href="playlists.php" class="ql-card"><span class="icon">&#127925;</span><span class="label">My Playlists</span></a>
        </div>
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

function esc(s) {
    var d = document.createElement('div');
    d.appendChild(document.createTextNode(s || ''));
    return d.innerHTML;
}

function addToLibrary(btn, ytId, title, artist, thumb) {
    btn.disabled = true;
    fetch('../api/add_user_song.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ youtube_id: ytId, title: title, artist: artist, cover_image: thumb })
    })
    .then(function(r) { return r.json(); })
    .then(function(d) {
        btn.innerHTML = '&#10003;';
        btn.classList.add('active');
        showToast(d.success ? 'Added to library!' : 'Already in library', 'success');
    })
    .catch(function() { btn.disabled = false; showToast('Failed to add', 'error'); });
}

function showToast(msg, type) {
    var t = document.createElement('div');
    t.className = 'toast ' + (type || 'info');
    t.textContent = msg;
    document.getElementById('toastContainer').appendChild(t);
    setTimeout(function() { if (t.parentNode) t.remove(); }, 3000);
}

function renderRecCard(r) {
    var t = esc(r.title);
    var c = esc(r.channel);
    var th = esc(r.thumbnail);
    var id = esc(r.videoId);
    return '<div class="rec-card">'
        + '<div class="thumb-wrap">'
        + '<img src="' + th + '" alt="' + t + '" loading="lazy">'
        + '<div class="rec-actions">'
        + '<button class="btn-icon btn-play" title="Play" onclick="playSong(\'' + id + '\', \'' + t.replace(/'/g, "\\'") + '\', \'' + c.replace(/'/g, "\\'") + '\', \'' + th.replace(/'/g, "\\'") + '\')">&#9654;</button>'
        + '<button class="btn-icon btn-add" title="Add to Library" onclick="addToLibrary(this, \'' + id + '\', \'' + t.replace(/'/g, "\\'") + '\', \'' + c.replace(/'/g, "\\'") + '\', \'' + th.replace(/'/g, "\\'") + '\')">&#43;</button>'
        + '</div></div>'
        + '<div class="info">'
        + '<div class="title">' + t + '</div>'
        + '<div class="artist">' + c + '</div>'
        + '</div></div>';
}

fetch('../api/recommendations.php')
    .then(function(r) { return r.json(); })
    .then(function(data) {
        document.getElementById('recLoading').style.display = 'none';
        var grid = document.getElementById('recGrid');
        if (data.results && data.results.length > 0) {
            var html = '';
            for (var i = 0; i < data.results.length; i++) {
                html += renderRecCard(data.results[i]);
            }
            grid.innerHTML = html;
        } else {
            grid.innerHTML = '<p style="color:var(--text-muted); padding:20px;">No recommendations yet. Start searching for songs!</p>';
        }
    })
    .catch(function() {
        document.getElementById('recLoading').innerHTML = '<p style="color:var(--text-muted);">Failed to load recommendations</p>';
    });
</script>

</body>
</html>
