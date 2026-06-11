<?php
require_once '../includes/db.php';
if (!isset($_SESSION['admin_logged_in'])) { header('Location: login.php'); exit; }
 $stmt = $pdo->query("SELECT * FROM songs ORDER BY created_at DESC");
 $songs = $stmt->fetchAll(PDO::FETCH_ASSOC);
if (isset($_SESSION['message'])) { $message = $_SESSION['message']; unset($_SESSION['message']); }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="admin-container">
        <header>
            <h1>Admin Dashboard</h1>
            <div><a href="add_song.php" class="btn btn-add">Add New Song</a><a href="logout.php" class="btn btn-logout">Logout</a></div>
        </header>
        <?php if (isset($message)): ?><p class="message"><?php echo $message; ?></p><?php endif; ?>
        <main><h2>Song List</h2>
            <table>
                <thead><tr><th>Cover</th><th>Title</th><th>Artist</th><th>Category</th><th>External Link</th><th>Actions</th></tr></thead>
                <tbody>
                    <?php if ($songs): foreach ($songs as $song): ?>
                        <tr>
                            <td><?php $coverSrc = strpos($song['cover_image'] ?? '', 'http') === 0 ? $song['cover_image'] : '../' . ($song['cover_image'] ?: 'https://via.placeholder.com/50'); ?><img src="<?php echo htmlspecialchars($coverSrc); ?>" alt="Cover" width="50"></td>
                            <td><?php echo htmlspecialchars($song['title']); ?></td>
                            <td><?php echo htmlspecialchars($song['artist']); ?></td>
                            <td><?php echo htmlspecialchars($song['category'] ?? 'Uncategorized'); ?></td>
                            <td><?php if (!empty($song['external_link'])): ?><a href="<?php echo htmlspecialchars($song['external_link']); ?>" target="_blank" class="btn btn-link">Open</a><?php else: ?>&mdash;<?php endif; ?></td>
                            <td><a href="edit_song.php?id=<?php echo $song['id']; ?>" class="btn btn-edit">Edit</a><a href="handle_song.php?action=delete&id=<?php echo $song['id']; ?>" class="btn btn-delete" onclick="return confirm('Are you sure?');">Delete</a></td>
                        </tr>
                    <?php endforeach; else: ?>
                        <tr><td colspan="4">No songs found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </main>
    </div>
</body>
</html>