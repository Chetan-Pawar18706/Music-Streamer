<?php
require_once '../includes/db.php';
if (!isset($_SESSION['admin_logged_in'])) { header('Location: login.php'); exit; }
if (!isset($_GET['id'])) { header('Location: index.php'); exit; }
 $song_id = $_GET['id'];
 $stmt = $pdo->prepare("SELECT * FROM songs WHERE id = ?"); $stmt->execute([$song_id]); $song = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$song) { header('Location: index.php'); exit; }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Song: <?php echo htmlspecialchars($song['title']); ?></title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="admin-container">
        <header><h1>Edit Song</h1><a href="index.php" class="btn btn-back">Back to Dashboard</a></header>
        <main>
            <form action="handle_song.php" method="post" enctype="multipart/form-data" class="song-form">
                <input type="hidden" name="song_id" value="<?php echo $song['id']; ?>">
                <div class="form-group"><label for="title">Song Title</label><input type="text" id="title" name="title" value="<?php echo htmlspecialchars($song['title']); ?>" required></div>
                <div class="form-group"><label for="artist">Artist</label><input type="text" id="artist" name="artist" value="<?php echo htmlspecialchars($song['artist']); ?>" required></div>
                <div class="form-group"><label for="category">Category</label><select id="category" name="category" required>
                    <option value="" <?php echo empty($song['category']) ? 'selected' : ''; ?>>Select category</option>
                    <?php foreach (['Traditional','Modern','Pop','Rock','Jazz','Electronic','Acoustic','Hip-Hop','Country','Classical','Folk','R&B','World','Indie'] as $cat): ?>
                        <option value="<?php echo $cat; ?>" <?php echo ($song['category'] ?? '') === $cat ? 'selected' : ''; ?>><?php echo $cat; ?></option>
                    <?php endforeach; ?>
                </select></div>
                <div class="form-group"><label for="song_file">MP3 File (Leave blank to keep current)</label><input type="file" id="song_file" name="song_file" accept="audio/mpeg"><p>Current: <?php echo basename($song['file_path']) ?: 'No local file'; ?></p></div>
                <div class="form-group"><label for="external_link">External Song Link</label><input type="url" id="external_link" name="external_link" value="<?php echo htmlspecialchars($song['external_link'] ?? ''); ?>" placeholder="https://example.com/song.mp3"><p>Use a direct audio URL (MP3/WAV/OGG), not a YouTube or page link.</p></div>
                <div class="form-group"><label for="cover_image">Cover Image File (Leave blank to keep current)</label><input type="file" id="cover_image" name="cover_image" accept="image/jpeg, image/png, image/webp"><p>Current: <?php echo strpos($song['cover_image'] ?? '', 'http') === 0 ? 'Remote cover URL' : basename($song['cover_image']); ?></p></div>
                <div class="form-group"><label for="cover_image_url">Cover Image URL (optional)</label><input type="url" id="cover_image_url" name="cover_image_url" value="<?php echo strpos($song['cover_image'] ?? '', 'http') === 0 ? htmlspecialchars($song['cover_image']) : ''; ?>" placeholder="https://example.com/cover.jpg"></div>
                <button type="submit" name="action" value="edit" class="btn btn-submit">Update Song</button>
            </form>
        </main>
    </div>
</body>
</html>