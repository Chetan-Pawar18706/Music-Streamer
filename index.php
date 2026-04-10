<?php
require_once 'includes/db.php';
// Get the total number of songs and users for the landing page stats
 $songCount = $pdo->query("SELECT COUNT(*) FROM songs")->fetchColumn();
 $userCount = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PHP Music Streamer - Your Music, Your Way</title>
    <link rel="stylesheet" href="style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
</head>
<body class="landing-page">
    <header class="landing-header">
        <nav>
            <a href="index.php" class="logo">PHP Music Streamer</a>
            <div class="nav-links">
                <a href="player.php">Launch Player</a>
                
                <!-- Admin Login Link -->
                <a href="admin/login.php" class="btn">Admin Login</a>

                <?php if (isset($_SESSION['user_logged_in'])): ?>
                    <a href="user/dashboard.php" class="btn">My Dashboard</a>
                    <a href="user/logout.php" class="btn btn-logout">Logout</a>
                <?php else: ?>
                    <a href="user/login.php" class="btn">Login</a>
                    <a href="user/register.php" class="btn btn-primary">Sign Up</a>
                <?php endif; ?>
            </div>
        </nav>
    </header>

    <main class="landing-main">
        <section class="hero">
            <div class="hero-content">
                <h1>Your Music, Uploaded and Streamed.</h1>
                <p>Upload your favorite tracks, create personal playlists, and enjoy your music from anywhere. Built with PHP and MySQL.</p>
                <div class="hero-buttons">
                    <?php if (isset($_SESSION['user_logged_in'])): ?>
                        <a href="player.php" class="btn btn-primary btn-large">Open Player</a>
                    <?php else: ?>
                        <a href="user/register.php" class="btn btn-primary btn-large">Get Started For Free</a>
                        <a href="player.php" class="btn btn-secondary btn-large">Browse Music</a>
                    <?php endif; ?>
                </div>
            </div>
        </section>

        <section class="features">
            <h2>Features</h2>
            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon">🎵</div>
                    <h3>Upload & Manage</h3>
                    <p>Admins can easily upload MP3 files and cover art to build the library.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">👤</div>
                    <h3>User Accounts</h3>
                    <p>Create a personal account to save your preferences and manage your playlists.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">📜</div>
                    <h3>Personal Playlists</h3>
                    <p>Build your own custom playlists from the entire music library.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">🎧</div>
                    <h3>Modern Player</h3>
                    <p>Enjoy a sleek audio player with queue, shuffle, and repeat controls.</p>
                </div>
            </div>
        </section>

        <section class="stats">
            <div class="stat-item">
                <h3><?php echo $songCount; ?></h3>
                <p>Songs in Library</p>
            </div>
            <div class="stat-item">
                <h3><?php echo $userCount; ?></h3>
                <p>Registered Users</p>
            </div>
        </section>
    </main>

    <footer class="landing-footer">
        <p>&copy; <?php echo date('Y'); ?> PHP Music Streamer. Create By Mr. Chetan Pawar.</p>
    </footer>
</body>
</html>