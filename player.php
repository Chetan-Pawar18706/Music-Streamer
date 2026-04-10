<?php
require_once 'includes/db.php';
 $stmt = $pdo->query("SELECT * FROM songs ORDER BY created_at DESC");
 $songs = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>PHP Music Streamer V2</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>PHP Music Streamer</h1>
            <div class="header-controls">
                <div class="search-container"><input type="search" id="search-bar" placeholder="Search for songs or artists..."></div>
                <div class="auth-links">
                    <?php if (isset($_SESSION['user_logged_in'])): ?>
                        <span>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?></span>
                        <a href="user/dashboard.php" class="btn">My Dashboard</a>
                        <a href="user/playlists.php" class="btn">My Playlists</a>
                        <a href="user/logout.php" class="btn btn-logout">Logout</a>
                    <?php else: ?>
                        <a href="user/login.php" class="btn">Login</a>
                        <a href="user/register.php" class="btn">Register</a>
                    <?php endif; ?>
                </div>
            </div>
        </header>
        <main>
            <div class="song-list" id="song-list">
                <?php if ($songs): foreach ($songs as $song): ?>
                    <div class="song-item" data-song-id="<?php echo $song['id']; ?>" data-song-path="<?php echo htmlspecialchars($song['file_path']); ?>" data-song-title="<?php echo htmlspecialchars($song['title']); ?>" data-song-artist="<?php echo htmlspecialchars($song['artist']); ?>">
                        <img src="<?php echo htmlspecialchars($song['cover_image'] ?? 'https://via.placeholder.com/150'); ?>" alt="Cover" class="song-cover">
                        <div class="song-info">
                            <h3 class="song-title"><?php echo htmlspecialchars($song['title']); ?></h3>
                            <p class="song-artist"><?php echo htmlspecialchars($song['artist']); ?></p>
                        </div>
                        <?php if (isset($_SESSION['user_logged_in'])): ?>
                            <button class="add-to-playlist-btn" title="Add to Playlist">+</button>
                        <?php endif; ?>
                    </div>
                <?php endforeach; else: ?>
                    <p>No songs available. Please ask the admin to add some.</p>
                <?php endif; ?>
            </div>
        </main>
        <footer>
            <div class="audio-player-container">
                <div class="player-info"><img id="player-cover" src="https://via.placeholder.com/50" alt="Now Playing"><div><div id="player-title">Select a song</div><div id="player-artist">&nbsp;</div></div></div>
                <div class="player-controls"><button id="prev-btn" class="control-btn">⏮</button><button id="play-pause-btn" class="control-btn">▶</button><button id="next-btn" class="control-btn">⏭</button></div>
                <div class="player-extras"><button id="shuffle-btn" class="control-btn">🔀</button><button id="repeat-btn" class="control-btn">🔁</button></div>
                <div class="progress-container"><span id="current-time">0:00</span><input type="range" id="progress-bar" min="0" max="100" value="0"><span id="duration">0:00</span></div>
                <audio id="audio-player"></audio>
            </div>
        </footer>
    </div>
    <div id="playlist-modal" class="modal"><div class="modal-content"><span class="close-btn">&times;</span><h2>Add to Playlist</h2><form id="add-to-playlist-form"><label for="playlist-select">Choose a playlist:</label><select name="playlist_id" id="playlist-select" required><option value="" disabled selected>Loading your playlists...</option></select><input type="hidden" id="hidden-song-id" name="song_id"><button type="submit" class="btn">Add Song</button></form><div id="playlist-feedback"></div></div></div>
    <script src="script.js"></script>
</body>
</html>