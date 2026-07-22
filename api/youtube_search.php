<?php
require_once '../includes/db.php';
header('Content-Type: application/json');

if (!isset($_SESSION['admin_logged_in']) && !isset($_SESSION['user_logged_in'])) {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$query = trim($_GET['q'] ?? '');
$pageToken = trim($_GET['pageToken'] ?? '');
if (empty($query)) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing search query']);
    exit;
}

function ytSearch($q, $max = 20, $pageTok = '') {
    $params = [
        'part'       => 'snippet',
        'q'          => $q,
        'type'       => 'video',
        'videoCategoryId' => '10',
        'maxResults' => $max,
        'key'        => YOUTUBE_API_KEY,
    ];
    if ($pageTok) $params['pageToken'] = $pageTok;

    $apiUrl = 'https://www.googleapis.com/youtube/v3/search?' . http_build_query($params);
    $ch = curl_init($apiUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($response !== false && $httpCode === 200) {
        $data = json_decode($response, true);
        if ($data && !isset($data['error'])) {
            return [
                'items' => $data['items'] ?? [],
                'nextPageToken' => $data['nextPageToken'] ?? '',
            ];
        }
    }
    return ['items' => [], 'nextPageToken' => ''];
}

$existing = [];
$results = [];

// Main search
$main = ytSearch($query, 20, $pageToken);
foreach ($main['items'] as $item) {
    $snippet = $item['snippet'] ?? [];
    $videoId = $item['id']['videoId'] ?? '';
    if (!$videoId || isset($existing[$videoId])) continue;
    $existing[$videoId] = true;
    $results[] = [
        'videoId'   => $videoId,
        'title'     => $snippet['title'] ?? '',
        'channel'   => $snippet['channelTitle'] ?? '',
        'thumbnail' => $snippet['thumbnails']['high']['url'] ?? ($snippet['thumbnails']['default']['url'] ?? ''),
        'url'       => 'https://www.youtube.com/watch?v=' . $videoId,
    ];
}

$nextToken = $main['nextPageToken'];

// Fill more with related queries if less than 40
$relatedQueries = [$query . ' songs', $query . ' music', $query . ' hits', $query . ' mix', $query . ' official'];
foreach ($relatedQueries as $rq) {
    if (count($results) >= 40) break;
    $extra = ytSearch($rq, 10);
    foreach ($extra['items'] as $item) {
        if (count($results) >= 50) break;
        $snippet = $item['snippet'] ?? [];
        $videoId = $item['id']['videoId'] ?? '';
        if (!$videoId || isset($existing[$videoId])) continue;
        $existing[$videoId] = true;
        $results[] = [
            'videoId'   => $videoId,
            'title'     => $snippet['title'] ?? '',
            'channel'   => $snippet['channelTitle'] ?? '',
            'thumbnail' => $snippet['thumbnails']['high']['url'] ?? ($snippet['thumbnails']['default']['url'] ?? ''),
            'url'       => 'https://www.youtube.com/watch?v=' . $videoId,
        ];
    }
}

echo json_encode(['results' => $results, 'nextPageToken' => $nextToken]);
