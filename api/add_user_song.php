<?php
require_once '../includes/db.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_logged_in'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$youtubeId = trim($input['youtube_id'] ?? '');
$title = trim($input['title'] ?? '');
$artist = trim($input['artist'] ?? '');
$coverImage = trim($input['cover_image'] ?? '');

if (empty($youtubeId) || empty($title) || empty($artist)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit;
}

$userId = $_SESSION['user_id'];

$stmt = $pdo->prepare("INSERT IGNORE INTO user_songs (user_id, youtube_id, title, artist, cover_image) VALUES (?, ?, ?, ?, ?)");
$stmt->execute([$userId, $youtubeId, $title, $artist, $coverImage]);

if ($stmt->rowCount() > 0) {
    echo json_encode(['success' => true, 'message' => 'Song added to your library']);
} else {
    echo json_encode(['success' => false, 'message' => 'Song already in your library']);
}
