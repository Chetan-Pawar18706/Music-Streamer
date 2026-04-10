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
                <div class="form-group"><label for="song_file">MP3 File</label><input type="file" id="song_file" name="song_file" accept="audio/mpeg" required></div>
                <div class="form-group"><label for="cover_image">Cover Image (optional)</label><input type="file" id="cover_image" name="cover_image" accept="image/jpeg, image/png, image/webp"></div>
                <button type="submit" name="action" value="add" class="btn btn-submit">Upload Song</button>
            </form>
        </main>
    </div>
</body>
</html>