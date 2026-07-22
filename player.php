<?php
require_once 'includes/db.php';
$userSongs = [];
if (isset($_SESSION['user_logged_in'])) {
    $stmt = $pdo->prepare("SELECT * FROM user_songs WHERE user_id = ? ORDER BY added_at DESC");
    $stmt->execute([$_SESSION['user_id']]);
    $userSongs = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Music Stream Player</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        .player-page { padding-bottom: 100px; }
        .player-page .player-sidebar { position: fixed; left: 0; top: 0; bottom: 80px; width: 280px; background: #141414; border-right: 1px solid #252525; overflow-y: auto; z-index: 100; }
        .player-page .player-main { margin-left: 280px; padding: 30px; min-height: calc(100vh - 80px); }
        .sidebar-search { padding: 16px; border-bottom: 1px solid #252525; }
        .sidebar-search input { width: 100%; padding: 10px 14px; background: #252525; border: 1px solid #333; border-radius: 8px; color: #fff; font-size: 14px; }
        .sidebar-search input:focus { outline: none; border-color: #ff0000; }
        .sidebar-song-list { padding: 8px 0; }
        .sidebar-song { display: flex; align-items: center; gap: 12px; padding: 10px 16px; cursor: pointer; transition: background 0.2s; border-left: 3px solid transparent; }
        .sidebar-song:hover { background: #1a1a1a; }
        .sidebar-song.active { background: #1a1a1a; border-left-color: #ff0000; }
        .sidebar-song img { width: 44px; height: 44px; border-radius: 6px; object-fit: cover; }
        .sidebar-song-info { flex: 1; min-width: 0; }
        .sidebar-song-title { font-size: 13px; font-weight: 600; color: #fff; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .sidebar-song-artist { font-size: 12px; color: #aaa; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .player-now-playing { text-align: center; padding: 60px 20px; }
        .player-now-playing .np-cover { width: 300px; height: 300px; border-radius: 12px; object-fit: cover; box-shadow: 0 8px 32px rgba(0,0,0,0.5); margin-bottom: 24px; }
        .player-now-playing .np-title { font-size: 24px; font-weight: 700; color: #fff; margin-bottom: 8px; }
        .player-now-playing .np-artist { font-size: 16px; color: #aaa; }
        #youtube-player-container { display: none; position: fixed; bottom: 100px; right: 20px; width: 360px; height: 202px; z-index: 1000; border-radius: 12px; overflow: hidden; box-shadow: 0 8px 32px rgba(0,0,0,0.6); border: 1px solid #333; }
        #youtube-player-container.active { display: block; }
        .no-songs-msg { text-align: center; padding: 80px 20px; color: #aaa; }
        .no-songs-msg h2 { color: #fff; margin-bottom: 12px; }
        .no-songs-msg a { color: #ff0000; text-decoration: none; }
        .no-songs-msg a:hover { text-decoration: underline; }
    </style>
</head>
<body class="player-page">
    <div class="player-sidebar">
        <div class="sidebar-nav" style="padding: 16px; border-bottom: 1px solid #252525;">
            <a href="index.php" style="color: #ff0000; font-size: 18px; font-weight: 700; text-decoration: none;">&#9835; Music Stream</a>
        </div>
        <?php if (isset($_SESSION['user_logged_in'])): ?>
        <nav style="padding: 8px 0; border-bottom: 1px solid #252525;">
            <a href="user/dashboard.php" style="display: block; padding: 10px 16px; color: #aaa; text-decoration: none;">&#127968; Dashboard</a>
            <a href="user/search.php" style="display: block; padding: 10px 16px; color: #aaa; text-decoration: none;">&#128269; Search</a>
            <a href="user/library.php" style="display: block; padding: 10px 16px; color: #aaa; text-decoration: none;">&#128190; Library</a>
            <a href="user/playlists.php" style="display: block; padding: 10px 16px; color: #aaa; text-decoration: none;">&#127925; Playlists</a>
        </nav>
        <?php else: ?>
        <nav style="padding: 8px 0; border-bottom: 1px solid #252525;">
            <a href="user/login.php" style="display: block; padding: 10px 16px; color: #aaa; text-decoration: none;">Login</a>
        </nav>
        <?php endif; ?>
        <div class="sidebar-search">
            <input type="text" id="sidebar-search" placeholder="Filter songs...">
        </div>
        <div class="sidebar-song-list" id="sidebar-song-list">
            <?php foreach ($userSongs as $song): ?>
            <div class="sidebar-song"
                 data-youtube-id="<?php echo htmlspecialchars($song['youtube_id']); ?>"
                 data-title="<?php echo htmlspecialchars($song['title']); ?>"
                 data-artist="<?php echo htmlspecialchars($song['artist']); ?>"
                 data-cover="<?php echo htmlspecialchars($song['cover_image'] ?? ''); ?>">
                <img src="<?php echo htmlspecialchars($song['cover_image'] ?: 'https://via.placeholder.com/44'); ?>" alt="">
                <div class="sidebar-song-info">
                    <div class="sidebar-song-title"><?php echo htmlspecialchars($song['title']); ?></div>
                    <div class="sidebar-song-artist"><?php echo htmlspecialchars($song['artist']); ?></div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="player-main">
        <div class="player-now-playing" id="now-playing">
            <img id="np-cover" class="np-cover" src="https://via.placeholder.com/300" alt="Now Playing">
            <div id="np-title" class="np-title">Select a song</div>
            <div id="np-artist" class="np-artist">Browse your library to start playing</div>
        </div>
        <?php if (empty($userSongs) && !isset($_SESSION['user_logged_in'])): ?>
        <div class="no-songs-msg">
            <h2>Welcome to Music Stream</h2>
            <p>Login to search, save songs, and create playlists.</p>
            <a href="user/login.php">Login Now</a> or <a href="user/register.php">Sign Up Free</a>
        </div>
        <?php elseif (empty($userSongs)): ?>
        <div class="no-songs-msg">
            <h2>Your library is empty</h2>
            <p>Start by searching for songs and adding them to your library.</p>
            <a href="user/search.php">Search Music</a>
        </div>
        <?php endif; ?>
    </div>

    <div id="youtube-player-container"><div id="youtube-player"></div></div>

    <div class="player-bar" id="player-bar">
        <div class="player-song-info">
            <img id="player-cover" src="https://via.placeholder.com/50" alt="">
            <div>
                <div id="player-title" style="font-weight:600;font-size:14px;">-</div>
                <div id="player-artist" style="font-size:12px;color:#aaa;">-</div>
            </div>
        </div>
        <div class="player-controls">
            <button id="prev-btn" class="control-btn" title="Previous">&#9198;</button>
            <button id="play-pause-btn" class="control-btn play-btn" title="Play">&#9654;</button>
            <button id="next-btn" class="control-btn" title="Next">&#9197;</button>
        </div>
        <div class="player-extras">
            <button id="shuffle-btn" class="control-btn" title="Shuffle">&#128256;</button>
            <button id="repeat-btn" class="control-btn" title="Repeat">&#128257;</button>
            <div class="progress-container">
                <span id="current-time">0:00</span>
                <input type="range" id="progress-bar" min="0" max="100" value="0">
                <span id="duration">0:00</span>
            </div>
        </div>
    </div>

    <script src="script.js"></script>
</body>
</html>
