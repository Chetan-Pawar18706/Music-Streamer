<?php
require_once '../includes/db.php';

// Check if user is logged in
if (!isset($_SESSION['user_logged_in'])) {
    header('Location: login.php');
    exit;
}

// Get playlist ID from the URL
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: playlists.php'); // Redirect if no valid ID
    exit;
}
$playlist_id = $_GET['id'];

// Verify that the playlist belongs to the current user and get its name
$stmt = $pdo->prepare("SELECT * FROM playlists WHERE id = ? AND user_id = ?");
$stmt->execute([$playlist_id, $_SESSION['user_id']]);
$playlist = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$playlist) {
    header('Location: playlists.php'); // Redirect if playlist doesn't exist or belong to user
    exit;
}

// Fetch all songs for this specific playlist
$stmt = $pdo->prepare(
    "SELECT s.* FROM songs s
     JOIN playlist_songs ps ON s.id = ps.song_id
     WHERE ps.playlist_id = ?
     ORDER BY ps.added_at ASC"
);
$stmt->execute([$playlist_id]);
$songs = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($playlist['name']); ?> - Playlist</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="dashboard-container">
        <header class="dashboard-header">
            <h1><?php echo htmlspecialchars($playlist['name']); ?></h1>
            <a href="playlists.php" class="btn">Back to My Playlists</a>
        </header>
        <main>
            <?php if (empty($songs)): ?>
                <p>This playlist is empty.</p>
            <?php else: ?>
                <div class="song-list-view">
                    <?php foreach ($songs as $song): ?>
                        <div class="song-item-view" data-song-id="<?php echo $song['id']; ?>">
                            <img src="<?php echo htmlspecialchars($song['cover_image'] ?? 'https://via.placeholder.com/100'); ?>" alt="Cover" class="song-cover-view">
                            <div class="song-info-view">
                                <h4 class="song-title-view"><?php echo htmlspecialchars($song['title']); ?></h4>
                                <p class="song-artist-view"><?php echo htmlspecialchars($song['artist']); ?></p>
                            </div>
                            <button class="btn btn-remove remove-from-playlist-btn" data-playlist-id="<?php echo $playlist_id; ?>" data-song-id="<?php echo $song['id']; ?>">Remove</button>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </main>
    </div>

    <script>
        
        // Simple JavaScript for handling removal
        document.querySelectorAll('.remove-from-playlist-btn').forEach(button => {
            button.addEventListener('click', async function() {
                if (!confirm('Are you sure you want to remove this song from the playlist?')) {
                    return;
                }

                const playlistId = this.dataset.playlistId;
                const songId = this.dataset.songId;
                const songItem = this.closest('.song-item-view');

                try {
                    const response = await fetch('../api/remove_from_playlist.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: `playlist_id=${playlistId}&song_id=${songId}`
                    });
                    const result = await response.json();

                    if (result.success) {
                        // Remove the song element from the page with a fade-out effect
                        songItem.style.transition = 'opacity 0.5s';
                        songItem.style.opacity = '0';
                        setTimeout(() => songItem.remove(), 500);
                    } else {
                        alert('Error: ' + result.message);
                    }
                } catch (error) {
                    alert('An error occurred. Please try again.');
                }
            });
        });
    </script>
        <script>
        // Simple JavaScript for handling removal
        document.querySelectorAll('.remove-from-playlist-btn').forEach(button => {
            button.addEventListener('click', async function() {
                if (!confirm('Are you sure you want to remove this song from the playlist?')) {
                    return;
                }

                const playlistId = this.dataset.playlistId;
                const songId = this.dataset.songId;
                const songItem = this.closest('.song-item-view');

                try {
                    const response = await fetch('../api/remove_from_playlist.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: `playlist_id=${playlistId}&song_id=${songId}`
                    });
                    const result = await response.json();

                    if (result.success) {
                        // Remove the song element from the page with a fade-out effect
                        songItem.style.transition = 'opacity 0.5s';
                        songItem.style.opacity = '0';
                        setTimeout(() => songItem.remove(), 500);
                    } else {
                        alert('Error: ' + result.message);
                    }
                } catch (error) {
                    alert('An error occurred. Please try again.');
                }
            });
        });

        // --- NEW: Handle clicks on songs to play them ---
        document.querySelectorAll('.song-item-view').forEach(item => {
            item.addEventListener('click', () => {
                // Get song details from the data attributes
                const songData = {
                    id: item.dataset.songId,
                    path: item.dataset.songPath,
                    title: item.dataset.songTitle,
                    artist: item.dataset.songArtist,
                    cover: item.querySelector('.song-cover-view').src
                };

                // Save song data to localStorage
                localStorage.setItem('currentSong', JSON.stringify(songData));

                // Redirect to the player page
                window.location.href = '../player.php';
            });
        });
    </script>
</body>
</html>