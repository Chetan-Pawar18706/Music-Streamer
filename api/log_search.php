<?php
require_once '../includes/db.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_logged_in'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$query = trim($input['query'] ?? '');

if (empty($query)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Missing query']);
    exit;
}

$userId = $_SESSION['user_id'];
$stmt = $pdo->prepare("INSERT INTO user_searches (user_id, query) VALUES (?, ?)");
$stmt->execute([$userId, $query]);

echo json_encode(['success' => true]);
