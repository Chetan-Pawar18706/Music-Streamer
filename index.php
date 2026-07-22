<?php
require_once 'includes/db.php';
$userCount = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$songCount = $pdo->query("SELECT COUNT(*) FROM user_songs")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Music Stream - Search, Play, Stream</title>
    <link rel="stylesheet" href="style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body class="landing-page">
    <header class="landing-header">
        <nav>
            <a href="index.php" class="logo">&#9835; Music Stream</a>
            <div class="nav-links">
                <?php if (isset($_SESSION['user_logged_in'])): ?>
                    <a href="user/dashboard.php" class="btn">Dashboard</a>
                    <a href="player.php" class="btn btn-primary">Open Player</a>
                    <a href="user/logout.php" class="btn btn-ghost">Logout</a>
                <?php else: ?>
                    <a href="user/login.php" class="btn btn-ghost">Login</a>
                    <a href="user/register.php" class="btn btn-primary">Sign Up Free</a>
                <?php endif; ?>
            </div>
        </nav>
    </header>

    <main class="landing-main">
        <section class="hero">
            <div class="hero-content">
                <h1>Search. Play. Stream.</h1>
                <p>Search millions of songs from YouTube, save them to your library, create playlists, and stream them all from one place. Your music, your way.</p>
                <div class="hero-buttons">
                    <?php if (isset($_SESSION['user_logged_in'])): ?>
                        <a href="user/search.php" class="btn btn-primary btn-large">Search Music</a>
                        <a href="player.php" class="btn btn-secondary btn-large">Open Player</a>
                    <?php else: ?>
                        <a href="user/register.php" class="btn btn-primary btn-large">Get Started Free</a>
                        <a href="user/login.php" class="btn btn-secondary btn-large">Login</a>
                    <?php endif; ?>
                </div>
            </div>
        </section>

        <section class="features">
            <h2>How It Works</h2>
            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon">&#128269;</div>
                    <h3>Search Songs</h3>
                    <p>Search for any song from YouTube's vast library. Find music from every genre and era.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">&#10133;</div>
                    <h3>Save to Library</h3>
                    <p>Build your personal music library. Save your favorite tracks and access them anytime.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">&#127925;</div>
                    <h3>Create Playlists</h3>
                    <p>Organize your music into custom playlists. Perfect for moods, activities, or sharing.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">&#127911;</div>
                    <h3>Stream & Play</h3>
                    <p>Enjoy a built-in player with controls, shuffle, and repeat. Seamless playback experience.</p>
                </div>
            </div>
        </section>

        <section class="stats">
            <div class="stat-item">
                <h3><?php echo number_format($userCount); ?></h3>
                <p>Active Users</p>
            </div>
            <div class="stat-item">
                <h3><?php echo number_format($songCount); ?></h3>
                <p>Songs Saved</p>
            </div>
            <div class="stat-item">
                <h3>&#8734;</h3>
                <p>Songs Available</p>
            </div>
        </section>
    </main>

    <footer class="landing-footer">
        <p>&copy; <?php echo date('Y'); ?> Music Stream. Built with PHP & YouTube API.</p>
    </footer>
</body>
</html>
