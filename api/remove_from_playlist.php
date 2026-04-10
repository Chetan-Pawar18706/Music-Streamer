<?php
// This script handles AJAX requests to remove a song from a playlist.
// It expects a POST request with 'playlist_id' and 'song_id'.

require_once '../includes/db.php';

// Check if user is logged in
if (!isset($_SESSION['user_logged_in'])) {
    http_response_code(403); // Forbidden
    echo json_encode(['success' => false, 'message' => 'You must be logged in to do that.']);
    exit;
}

// Check if the request is a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); // Method Not Allowed
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit;
}

// Get the data from the POST request
$playlist_id = $_POST['playlist_id'] ?? null;
$song_id = $_POST['song_id'] ?? null;

// Validate the data
if (empty($playlist_id) || empty($song_id) || !is_numeric($playlist_id) || !is_numeric($song_id)) {
    http_response_code(400); // Bad Request
    echo json_encode(['success' => false, 'message' => 'Invalid data provided.']);
    exit;
}

// Verify that the playlist belongs to the current user
$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT id FROM playlists WHERE id = ? AND user_id = ?");
$stmt->execute([$playlist_id, $user_id]);
if (!$stmt->fetch()) {
    http_response_code(403); // Forbidden
    echo json_encode(['success' => false, 'message' => 'You do not own this playlist.']);
    exit;
}

// Remove the song from the playlist
try {
    $stmt = $pdo->prepare("DELETE FROM playlist_songs WHERE playlist_id = ? AND song_id = ?");
    $stmt->execute([$playlist_id, $song_id]);
    
    // Check if a row was actually deleted
    if ($stmt->rowCount() > 0) {
        echo json_encode(['success' => true, 'message' => 'Song removed from playlist successfully!']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Song was not in this playlist.']);
    }
} catch (PDOException $e) {
    http_response_code(500); // Internal Server Error
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>