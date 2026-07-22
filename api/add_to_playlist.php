<?php
require_once '../includes/db.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_logged_in'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'You must be logged in.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit;
}

$playlistId = $_POST['playlist_id'] ?? null;
$userSongId = $_POST['song_id'] ?? null;

if (empty($playlistId) || empty($userSongId) || !is_numeric($playlistId) || !is_numeric($userSongId)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid data provided.']);
    exit;
}

$userId = $_SESSION['user_id'];

$stmt = $pdo->prepare("SELECT id FROM playlists WHERE id = ? AND user_id = ?");
$stmt->execute([$playlistId, $userId]);
if (!$stmt->fetch()) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'You do not own this playlist.']);
    exit;
}

$stmt = $pdo->prepare("SELECT id FROM user_songs WHERE id = ? AND user_id = ?");
$stmt->execute([$userSongId, $userId]);
if (!$stmt->fetch()) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Song not in your library.']);
    exit;
}

try {
    $stmt = $pdo->prepare("INSERT IGNORE INTO playlist_songs (playlist_id, song_id) VALUES (?, ?)");
    $stmt->execute([$playlistId, $userSongId]);

    if ($stmt->rowCount() > 0) {
        echo json_encode(['success' => true, 'message' => 'Song added to playlist!']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Song already in playlist.']);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error.']);
}
