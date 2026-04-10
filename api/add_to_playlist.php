<?php
// This script handles AJAX requests to add a song to a playlist.
// It expects a POST request with 'playlist_id' and 'song_id'.

require_once '../includes/db.php';

// --- SECURITY CHECK: Ensure user is logged in ---
if (!isset($_SESSION['user_logged_in'])) {
    http_response_code(403); // Forbidden
    echo json_encode(['success' => false, 'message' => 'You must be logged in to do that.']);
    exit;
}

// --- SECURITY CHECK: Ensure the request is a POST request ---
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); // Method Not Allowed
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit;
}

// --- Get and Validate Data ---
 $playlist_id = $_POST['playlist_id'] ?? null;
 $song_id = $_POST['song_id'] ?? null;

if (empty($playlist_id) || empty($song_id) || !is_numeric($playlist_id) || !is_numeric($song_id)) {
    http_response_code(400); // Bad Request
    echo json_encode(['success' => false, 'message' => 'Invalid data provided.']);
    exit;
}

// --- SECURITY CHECK: Verify playlist ownership ---
 $user_id = $_SESSION['user_id'];
 $stmt = $pdo->prepare("SELECT id FROM playlists WHERE id = ? AND user_id = ?");
 $stmt->execute([$playlist_id, $user_id]);
if (!$stmt->fetch()) {
    http_response_code(403); // Forbidden
    echo json_encode(['success' => false, 'message' => 'You do not own this playlist.']);
    exit;
}

// --- Database Operation: Add song to playlist ---
try {
    // Use INSERT IGNORE to prevent errors if the song is already in the playlist
    $stmt = $pdo->prepare("INSERT IGNORE INTO playlist_songs (playlist_id, song_id) VALUES (?, ?)");
    $stmt->execute([$playlist_id, $song_id]);

    // Check if a row was actually inserted
    if ($stmt->rowCount() > 0) {
        echo json_encode(['success' => true, 'message' => 'Song added to playlist successfully!']);
    } else {
        echo json_encode(['success' => false, 'message' => 'This song is already in the playlist.']);
    }
} catch (PDOException $e) {
    http_response_code(500); // Internal Server Error
    echo json_encode(['success' => false, 'message' => 'Database error.']);
}
?>