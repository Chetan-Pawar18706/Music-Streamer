<?php
require_once '../includes/db.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_logged_in'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$userId = $_SESSION['user_id'];
$sort = $_GET['sort'] ?? 'recent';

$orderBy = 'added_at DESC';
if ($sort === 'title') $orderBy = 'title ASC';
elseif ($sort === 'artist') $orderBy = 'artist ASC';

$stmt = $pdo->prepare("SELECT * FROM user_songs WHERE user_id = ? ORDER BY $orderBy");
$stmt->execute([$userId]);
$songs = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode(['success' => true, 'songs' => $songs]);
