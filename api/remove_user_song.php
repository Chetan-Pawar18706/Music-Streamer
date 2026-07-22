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
$songId = $input['song_id'] ?? 0;
$userId = $_SESSION['user_id'];

if (!$songId) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Missing song_id']);
    exit;
}

$stmt = $pdo->prepare("DELETE FROM user_songs WHERE id = ? AND user_id = ?");
$stmt->execute([$songId, $userId]);

if ($stmt->rowCount() > 0) {
    echo json_encode(['success' => true, 'message' => 'Song removed from library']);
} else {
    echo json_encode(['success' => false, 'message' => 'Song not found']);
}
