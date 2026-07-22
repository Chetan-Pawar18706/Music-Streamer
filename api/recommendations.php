<?php
require_once '../includes/db.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_logged_in'])) {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$userId = $_SESSION['user_id'];

// Get user's past search queries
$stmt = $pdo->prepare("SELECT query FROM user_searches WHERE user_id = ? ORDER BY searched_at DESC LIMIT 10");
$stmt->execute([$userId]);
$queries = $stmt->fetchAll(PDO::FETCH_COLUMN);

// Also get genres/moods from search history
$genres = ['bollywood hits', 'english songs', 'punjabi songs', 'romantic songs', 'workout music', 'party songs', 'lofi music', 'classical', 'hip hop', 'rock music', 'telugu songs', 'tamil songs', 'japanese music', 'korean music'];

// Mix user queries with popular genres
$searchTerms = [];
foreach ($queries as $q) {
    $searchTerms[] = $q;
}
// Add random genres to fill up
shuffle($genres);
$searchTerms = array_merge($searchTerms, array_slice($genres, 0, 5));

// Deduplicate
$searchTerms = array_unique(array_slice($searchTerms, 0, 8));

function ytSearch($q, $max = 5) {
    $apiUrl = 'https://www.googleapis.com/youtube/v3/search?' . http_build_query([
        'part'       => 'snippet',
        'q'          => $q,
        'type'       => 'video',
        'videoCategoryId' => '10',
        'maxResults' => $max,
        'key'        => YOUTUBE_API_KEY,
    ]);
    $ch = curl_init($apiUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 8);
    $response = curl_exec($ch);
    curl_close($ch);

    if ($response !== false) {
        $data = json_decode($response, true);
        if ($data && !isset($data['error'])) {
            return $data['items'] ?? [];
        }
    }
    return [];
}

$existing = [];
$results = [];

foreach ($searchTerms as $term) {
    if (count($results) >= 40) break;
    $items = ytSearch($term, 6);
    foreach ($items as $item) {
        if (count($results) >= 40) break;
        $snippet = $item['snippet'] ?? [];
        $videoId = $item['id']['videoId'] ?? '';
        if (!$videoId || isset($existing[$videoId])) continue;
        $existing[$videoId] = true;
        $results[] = [
            'videoId'   => $videoId,
            'title'     => $snippet['title'] ?? '',
            'channel'   => $snippet['channelTitle'] ?? '',
            'thumbnail' => $snippet['thumbnails']['high']['url'] ?? ($snippet['thumbnails']['default']['url'] ?? ''),
        ];
    }
}

// If no search history, add default popular results
if (empty($queries)) {
    $defaults = ['trending songs 2024', 'top 100 songs', 'best of bollywood', 'popular english songs'];
    foreach ($defaults as $term) {
        if (count($results) >= 40) break;
        $items = ytSearch($term, 8);
        foreach ($items as $item) {
            if (count($results) >= 40) break;
            $snippet = $item['snippet'] ?? [];
            $videoId = $item['id']['videoId'] ?? '';
            if (!$videoId || isset($existing[$videoId])) continue;
            $existing[$videoId] = true;
            $results[] = [
                'videoId'   => $videoId,
                'title'     => $snippet['title'] ?? '',
                'channel'   => $snippet['channelTitle'] ?? '',
                'thumbnail' => $snippet['thumbnails']['high']['url'] ?? ($snippet['thumbnails']['default']['url'] ?? ''),
            ];
        }
    }
}

echo json_encode(['results' => $results]);
