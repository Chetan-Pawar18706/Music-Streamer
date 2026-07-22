<?php
require_once '../includes/db.php';
if (!isset($_SESSION['user_logged_in'])) { header('Location: login.php'); exit; }
$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT username FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
$username = htmlspecialchars($user['username']);
$initialQuery = htmlspecialchars($_GET['q'] ?? '');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Music</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="../style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        .search-results-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 16px; margin-top: 24px; }
        .search-result-item { background: var(--card); border-radius: 12px; overflow: hidden; transition: all 0.2s; border: 1px solid transparent; }
        .search-result-item:hover { border-color: var(--border); transform: translateY(-2px); }
        .thumb-wrapper { position: relative; }
        .thumb-wrapper img { width: 100%; aspect-ratio: 16/9; object-fit: cover; display: block; }
        .thumb-actions { position: absolute; bottom: 8px; right: 8px; display: flex; gap: 6px; opacity: 0; transition: opacity 0.2s; }
        .search-result-item:hover .thumb-actions { opacity: 1; }
        .btn-icon { width: 36px; height: 36px; border-radius: 50%; border: none; cursor: pointer; display: flex; align-items: center; justify-content: center; font-size: 1rem; transition: all 0.2s; }
        .btn-play { background: var(--primary); color: #fff; }
        .btn-play:hover { background: #cc0000; transform: scale(1.1); }
        .btn-add { background: rgba(0,0,0,0.7); color: #fff; backdrop-filter: blur(4px); }
        .btn-add:hover { background: var(--primary); }
        .btn-add.active { background: var(--success); pointer-events: none; }
        .result-info { padding: 12px; }
        .result-title { font-size: 0.85rem; font-weight: 600; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; color: var(--text); }
        .result-artist { font-size: 0.75rem; color: var(--text-muted); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; margin-top: 2px; }
        .search-bar-wrapper { display: flex; gap: 8px; max-width: 600px; margin: 0 auto 24px; }
        .search-bar-wrapper input { flex: 1; padding: 14px 20px; background: var(--card); border: 2px solid var(--border); border-radius: 999px; color: var(--text); font-size: 1rem; outline: none; transition: border-color 0.2s; }
        .search-bar-wrapper input:focus { border-color: var(--primary); box-shadow: 0 0 0 4px rgba(255,0,0,0.15); }
        .search-bar-wrapper button { padding: 14px 28px; background: var(--primary); color: #fff; border: none; border-radius: 999px; font-size: 1rem; font-weight: 600; cursor: pointer; transition: background 0.2s; }
        .search-bar-wrapper button:hover { background: #cc0000; }
        .section-title { font-size: 1.2rem; font-weight: 700; margin-top: 32px; margin-bottom: 16px; color: var(--text); }
        .loading-spinner { text-align: center; padding: 48px; }
        .loading-spinner .spinner { width: 40px; height: 40px; border: 3px solid var(--border); border-top-color: var(--primary); border-radius: 50%; animation: spin 0.8s linear infinite; margin: 0 auto 12px; }
        @keyframes spin { to { transform: rotate(360deg); } }
        .toast { position: fixed; bottom: 24px; right: 24px; padding: 12px 20px; border-radius: 8px; font-size: 0.85rem; z-index: 9999; animation: slideIn 0.3s ease; box-shadow: 0 4px 20px rgba(0,0,0,0.4); }
        .toast.success { background: #1a3a1a; border: 1px solid var(--success); color: var(--success); }
        .toast.error { background: #3a1a1a; border: 1px solid var(--danger); color: var(--danger); }
        @keyframes slideIn { from { transform: translateY(20px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
        .load-more-btn { display: block; width: 200px; margin: 24px auto; padding: 12px 24px; background: var(--card); border: 1px solid var(--border); border-radius: 999px; color: var(--text); font-size: 0.9rem; font-weight: 600; cursor: pointer; transition: all 0.2s; text-align: center; }
        .load-more-btn:hover { border-color: var(--primary); color: var(--primary); }
        .load-more-btn:disabled { opacity: 0.5; cursor: not-allowed; }
        .result-count { color: var(--text-muted); font-size: 0.85rem; margin-top: 8px; }
    </style>
</head>
<body>

<nav class="user-topnav">
    <a href="dashboard.php" class="topnav-logo">&#9835; <span>MusicStream</span></a>
    <div class="topnav-search">
        <form class="search-pill" id="topnavSearchForm" onsubmit="return false;">
            <input type="text" id="topnavSearchInput" placeholder="Search music..." autocomplete="off">
            <button type="button" class="search-submit" onclick="navigateToSearch()">&#128269;</button>
        </form>
    </div>
    <div class="topnav-user">
        <a href="dashboard.php" class="nav-link">Dashboard</a>
        <a href="logout.php" class="nav-link">Logout</a>
        <button class="user-avatar-btn"><?php echo strtoupper(substr($username, 0, 1)); ?></button>
    </div>
</nav>

<div class="user-layout">
    <aside class="user-sidebar">
        <div class="nav-section">
            <div class="nav-section-title">Menu</div>
            <a href="dashboard.php" class="sidebar-link"><span class="link-icon">&#127968;</span> Dashboard</a>
            <a href="search.php" class="sidebar-link active"><span class="link-icon">&#128269;</span> Search</a>
            <a href="library.php" class="sidebar-link"><span class="link-icon">&#128190;</span> Library</a>
            <a href="playlists.php" class="sidebar-link"><span class="link-icon">&#127925;</span> Playlists</a>
        </div>
        <div class="nav-section">
            <div class="nav-section-title">Account</div>
            <a href="logout.php" class="sidebar-link"><span class="link-icon">&#10140;</span> Logout</a>
        </div>
    </aside>

    <main class="user-content">
        <h1 style="font-size:1.8rem; margin-bottom:24px;">Search Music</h1>

        <div class="search-bar-wrapper">
            <input type="text" id="searchInput" placeholder="Search for songs, artists, albums..." autocomplete="off">
            <button type="button" id="searchBtn">Search</button>
        </div>

        <div id="loading" class="loading-spinner" style="display:none;">
            <div class="spinner"></div>
            <p style="color:var(--text-muted);">Searching YouTube...</p>
        </div>

        <div id="resultsSection" style="display:none;">
            <div class="section-title" id="resultsTitle">Results</div>
            <div class="result-count" id="resultCount"></div>
            <div id="resultsGrid" class="search-results-grid"></div>
            <button id="loadMoreBtn" class="load-more-btn" style="display:none;" onclick="loadMore()">Load More Songs</button>
        </div>

        <div id="emptyState" style="display:none; text-align:center; padding:64px 24px; color:var(--text-muted);">
            <div style="font-size:3rem; margin-bottom:16px;">&#128269;</div>
            <h2 style="color:var(--text); margin-bottom:8px;">No results found</h2>
            <p>Try a different search term</p>
        </div>

        <div id="initialState" style="text-align:center; padding:64px 24px; color:var(--text-muted);">
            <div style="font-size:3rem; margin-bottom:16px;">&#9835;</div>
            <h2 style="color:var(--text); margin-bottom:8px;">Search for music</h2>
            <p>Find your favorite songs, artists, and albums from YouTube</p>
            <div style="margin-top:24px; display:flex; flex-wrap:wrap; gap:8px; justify-content:center;">
                <button class="load-more-btn" style="width:auto;" onclick="quickSearch('bollywood hits')">Bollywood</button>
                <button class="load-more-btn" style="width:auto;" onclick="quickSearch('english songs 2024')">English</button>
                <button class="load-more-btn" style="width:auto;" onclick="quickSearch('punjabi songs')">Punjabi</button>
                <button class="load-more-btn" style="width:auto;" onclick="quickSearch('romantic songs')">Romantic</button>
                <button class="load-more-btn" style="width:auto;" onclick="quickSearch('lofi music')">Lo-fi</button>
            </div>
        </div>
    </main>
</div>

<div id="toastContainer"></div>

<script>
var searchInput = document.getElementById('searchInput');
var searchBtn = document.getElementById('searchBtn');
var loading = document.getElementById('loading');
var resultsSection = document.getElementById('resultsSection');
var resultsGrid = document.getElementById('resultsGrid');
var resultsTitle = document.getElementById('resultsTitle');
var resultCount = document.getElementById('resultCount');
var emptyState = document.getElementById('emptyState');
var initialState = document.getElementById('initialState');
var loadMoreBtn = document.getElementById('loadMoreBtn');
var toastContainer = document.getElementById('toastContainer');

var currentQuery = '';
var allResults = [];
var nextPageToken = '';
var isLoading = false;

searchBtn.addEventListener('click', startSearch);
searchInput.addEventListener('keydown', function(e) { if (e.key === 'Enter') startSearch(); });

function navigateToSearch() {
    var q = document.getElementById('topnavSearchInput').value.trim();
    if (q) { searchInput.value = q; startSearch(); }
}

function quickSearch(q) {
    searchInput.value = q;
    startSearch();
}

function startSearch() {
    var query = searchInput.value.trim();
    if (!query) return;
    currentQuery = query;
    allResults = [];
    nextPageToken = '';
    resultsGrid.innerHTML = '';

    loading.style.display = 'block';
    resultsSection.style.display = 'none';
    emptyState.style.display = 'none';
    initialState.style.display = 'none';
    loadMoreBtn.style.display = 'none';

    fetch('../api/log_search.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ query: query })
    }).catch(function() {});

    fetchResults(query, '');
}

function fetchResults(query, pageToken) {
    isLoading = true;
    var url = '../api/youtube_search.php?q=' + encodeURIComponent(query);
    if (pageToken) url += '&pageToken=' + encodeURIComponent(pageToken);

    fetch(url)
        .then(function(resp) { return resp.json(); })
        .then(function(data) {
            isLoading = false;
            loading.style.display = 'none';

            if (data.error || !data.results || data.results.length === 0) {
                if (allResults.length === 0) {
                    emptyState.style.display = 'block';
                }
                loadMoreBtn.style.display = 'none';
                return;
            }

            var newResults = data.results;
            for (var i = 0; i < newResults.length; i++) {
                allResults.push(newResults[i]);
            }

            nextPageToken = data.nextPageToken || '';
            renderResults(newResults, allResults.length);
            resultsSection.style.display = 'block';
            resultsTitle.textContent = 'Results for "' + currentQuery + '"';
            resultCount.textContent = allResults.length + ' songs found';
            loadMoreBtn.style.display = nextPageToken ? 'block' : 'none';
            loadMoreBtn.textContent = 'Load More Songs';
            loadMoreBtn.disabled = false;
        })
        .catch(function() {
            isLoading = false;
            loading.style.display = 'none';
            if (allResults.length === 0) emptyState.style.display = 'block';
            loadMoreBtn.style.display = 'none';
        });
}

function loadMore() {
    if (isLoading || !nextPageToken) return;
    loadMoreBtn.textContent = 'Loading...';
    loadMoreBtn.disabled = true;
    fetchResults(currentQuery, nextPageToken);
}

function renderResults(results, totalCount) {
    for (var i = 0; i < results.length; i++) {
        var r = results[i];
        var t = esc(r.title);
        var c = esc(r.channel);
        var th = esc(r.thumbnail);
        var id = esc(r.videoId);

        var card = document.createElement('div');
        card.className = 'search-result-item';
        card.dataset.id = id;
        card.dataset.title = t;
        card.dataset.channel = c;
        card.dataset.thumb = th;

        card.innerHTML = '<div class="thumb-wrapper">'
            + '<img src="' + th + '" alt="' + t + '" loading="lazy">'
            + '<div class="thumb-actions">'
            + '<button class="btn-icon btn-play" title="Play">&#9654;</button>'
            + '<button class="btn-icon btn-add" title="Add to Library">&#43;</button>'
            + '</div></div>'
            + '<div class="result-info">'
            + '<div class="result-title">' + t + '</div>'
            + '<div class="result-artist">' + c + '</div>'
            + '</div>';

        (function(card) {
            card.querySelector('.btn-play').addEventListener('click', function(e) {
                e.stopPropagation();
                playSong(card.dataset.id, card.dataset.title, card.dataset.channel, card.dataset.thumb);
            });
            card.querySelector('.btn-add').addEventListener('click', function(e) {
                e.stopPropagation();
                addToLibrary(e.currentTarget, card.dataset.id, card.dataset.title, card.dataset.channel, card.dataset.thumb);
            });
        })(card);

        resultsGrid.appendChild(card);
    }
}

function playSong(ytId, title, artist, cover) {
    localStorage.setItem('currentSong', JSON.stringify({
        youtubeId: ytId, title: title, artist: artist, cover: cover
    }));
    window.location.href = '../player.php';
}

function addToLibrary(btn, ytId, title, artist, thumb) {
    btn.disabled = true;
    fetch('../api/add_user_song.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ youtube_id: ytId, title: title, artist: artist, cover_image: thumb })
    })
    .then(function(r) { return r.json(); })
    .then(function(d) {
        btn.innerHTML = '&#10003;';
        btn.classList.add('active');
        showToast(d.success ? 'Added to library!' : 'Already in library', 'success');
    })
    .catch(function() { btn.disabled = false; showToast('Failed to add song', 'error'); });
}

function showToast(msg, type) {
    var t = document.createElement('div');
    t.className = 'toast ' + (type || 'info');
    t.textContent = msg;
    toastContainer.appendChild(t);
    setTimeout(function() { if (t.parentNode) t.parentNode.removeChild(t); }, 3000);
}

function esc(str) {
    var d = document.createElement('div');
    d.appendChild(document.createTextNode(str || ''));
    return d.innerHTML;
}

<?php if ($initialQuery): ?>
searchInput.value = <?php echo json_encode($_GET['q']); ?>;
startSearch();
<?php endif; ?>
</script>

</body>
</html>
