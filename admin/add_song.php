<?php require_once '../includes/db.php'; if (!isset($_SESSION['admin_logged_in'])) { header('Location: login.php'); exit; } ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add New Song</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="admin-container">
        <header><h1>Add New Song</h1><a href="index.php" class="btn btn-back">Back to Dashboard</a></header>
        <main>
            <form action="handle_song.php" method="post" enctype="multipart/form-data" class="song-form">
                <div class="form-group"><label for="title">Song Title</label><input type="text" id="title" name="title" required></div>
                <div class="form-group"><label for="artist">Artist</label><input type="text" id="artist" name="artist" required></div>
                <div class="form-group"><label for="category">Category</label><select id="category" name="category" required>
                    <option value="">Select category</option>
                    <option value="Traditional">Traditional</option>
                    <option value="Modern">Modern</option>
                    <option value="Pop">Pop</option>
                    <option value="Rock">Rock</option>
                    <option value="Jazz">Jazz</option>
                    <option value="Electronic">Electronic</option>
                    <option value="Acoustic">Acoustic</option>
                    <option value="Hip-Hop">Hip-Hop</option>
                    <option value="Country">Country</option>
                    <option value="Classical">Classical</option>
                    <option value="Folk">Folk</option>
                    <option value="R&B">R&B</option>
                    <option value="World">World</option>
                    <option value="Indie">Indie</option>
                </select></div>
                <div class="form-group"><label for="song_file">MP3 File (optional if external link is provided)</label><input type="file" id="song_file" name="song_file" accept="audio/mpeg"></div>
                <div class="form-group"><label for="external_link">External Song Link</label><input type="url" id="external_link" name="external_link" placeholder="https://example.com/song.mp3"><p>Use a direct audio URL (MP3/WAV/OGG), not a YouTube or page link.</p></div>
                <div class="form-group"><label for="cover_image">Cover Image File (optional)</label><input type="file" id="cover_image" name="cover_image" accept="image/jpeg, image/png, image/webp"></div>
                <div class="form-group"><label for="cover_image_url">Cover Image URL (optional)</label><input type="url" id="cover_image_url" name="cover_image_url" placeholder="https://example.com/cover.jpg"></div>
                <button type="submit" name="action" value="add" class="btn btn-submit">Upload Song</button>
            </form>
        </main>
    </div>
</body>
</html>