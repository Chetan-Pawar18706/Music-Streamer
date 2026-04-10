<?php
require_once '../includes/db.php';
if (!isset($_SESSION['user_logged_in'])) {
    header('Location: login.php');
    exit;
}
if (isset($_GET['fetch_as_json'])) {
    $stmt = $pdo->prepare("SELECT id, name FROM playlists WHERE user_id = ? ORDER BY name ASC");
    $stmt->execute([$_SESSION['user_id']]);
    header('Content-Type: application/json');
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    exit;
}
$message = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['create_playlist'])) {
    $stmt = $pdo->prepare("INSERT INTO playlists (user_id, name) VALUES (?, ?)");
    $stmt->execute([$_SESSION['user_id'], trim($_POST['playlist_name'])]);
    $message = "Playlist created successfully!";
}
$stmt = $pdo->prepare("SELECT * FROM playlists WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$_SESSION['user_id']]);
$playlists = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>My Playlists</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <div class="dashboard-container">
        <header class="dashboard-header">
            <h1>My Playlists</h1>
            <a href="../player.php" class="btn">Back to Player</a>
        </header>
        <main>
            <?php if ($message): ?>
                <p class="success"><?php echo $message; ?></p><?php endif; ?>
            <form action="playlists.php" method="post" class="playlist-create-form">
                <input type="text" name="playlist_name" placeholder="New playlist name..." required>
                <button type="submit" name="create_playlist" class="btn">Create Playlist</button>
            </form>
            <div class="playlist-grid">
                <?php if ($playlists):
                    foreach ($playlists as $playlist): ?>
                        <div class="playlist-card">
                            <h3><?php echo htmlspecialchars($playlist['name']); ?></h3>
                            <div class="playlist-actions">
                                <a href="view_playlist.php?id=<?php echo $playlist['id']; ?>" class="btn">View Songs</a>
                            </div>
                           
                        </div>
                    <?php endforeach; else: ?>
                    <p>You haven't created any playlists yet.</p>
                <?php endif; ?>
            </div>
        </main>
    </div>
</body>

</html>