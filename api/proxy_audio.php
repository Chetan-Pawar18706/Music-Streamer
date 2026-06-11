<?php
// Simple proxy for remote audio playback.
// This avoids CORS issues by streaming the audio through the same origin.

$url = $_GET['url'] ?? null;
if (empty($url) || !filter_var($url, FILTER_VALIDATE_URL)) {
    http_response_code(400);
    echo 'Invalid or missing URL.';
    exit;
}

$parsed = parse_url($url);
$scheme = strtolower($parsed['scheme'] ?? '');
if (!in_array($scheme, ['http', 'https'], true)) {
    http_response_code(400);
    echo 'Only HTTP and HTTPS audio sources are allowed.';
    exit;
}

$allowedExtensions = ['mp3', 'wav', 'ogg', 'm4a', 'aac', 'flac'];
$path = $parsed['path'] ?? '';
$extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));

$rangeHeader = $_SERVER['HTTP_RANGE'] ?? null;

// First perform a HEAD request to verify the remote resource and content-type.
$headCh = curl_init($url);
if ($headCh === false) {
    http_response_code(500);
    echo 'Unable to initialize proxy.';
    exit;
}

curl_setopt($headCh, CURLOPT_NOBODY, true);
curl_setopt($headCh, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($headCh, CURLOPT_RETURNTRANSFER, true);
curl_setopt($headCh, CURLOPT_HEADER, true);
curl_setopt($headCh, CURLOPT_SSL_VERIFYHOST, 2);
curl_setopt($headCh, CURLOPT_SSL_VERIFYPEER, true);
curl_setopt($headCh, CURLOPT_USERAGENT, 'PHP Music Streamer Proxy');
curl_setopt($headCh, CURLOPT_HTTPHEADER, ['Accept: audio/*,*/*']);

$headResponse = curl_exec($headCh);
$headInfo = curl_getinfo($headCh);
$headError = curl_error($headCh);
curl_close($headCh);

$headHttpCode = $headInfo['http_code'] ?? 0;
if ($headResponse === false || $headHttpCode >= 400) {
    if ($headHttpCode !== 405 && !in_array($extension, $allowedExtensions, true)) {
        http_response_code($headHttpCode ?: 502);
        echo 'Unable to verify remote audio source.';
        exit;
    }
}

$contentType = '';
if (preg_match('/Content-Type:\s*([^;\r\n]+)/i', $headResponse, $matches)) {
    $contentType = trim($matches[1]);
}

$isValidAudio = false;
if ($extension !== '' && in_array($extension, $allowedExtensions, true)) {
    $isValidAudio = true;
}
if (!$isValidAudio && stripos($contentType, 'audio/') === 0) {
    $isValidAudio = true;
}
if (!$isValidAudio && stripos($contentType, 'application/octet-stream') === 0 && $extension !== '' && in_array($extension, $allowedExtensions, true)) {
    $isValidAudio = true;
}

if (!$isValidAudio) {
    http_response_code(400);
    echo 'The provided URL does not appear to be an audio source.';
    exit;
}

$ch = curl_init($url);
if ($ch === false) {
    http_response_code(500);
    echo 'Unable to initialize proxy.';
    exit;
}

curl_setopt($ch, CURLOPT_RETURNTRANSFER, false);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_HEADER, false);
curl_setopt($ch, CURLOPT_BUFFERSIZE, 8192);
curl_setopt($ch, CURLOPT_USERAGENT, 'PHP Music Streamer Proxy');
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Accept: audio/*,*/*']);

$headers = [];
if ($rangeHeader) {
    $headers[] = 'Range: ' . $rangeHeader;
}
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

curl_setopt($ch, CURLOPT_HEADERFUNCTION, function ($curl, $headerLine) {
    $len = strlen($headerLine);
    $trimmed = trim($headerLine);
    if ($trimmed === '') {
        return $len;
    }

    if (stripos($trimmed, 'HTTP/') === 0) {
        if (preg_match('/HTTP\/\d\.\d\s+(\d+)/', $trimmed, $matches)) {
            http_response_code((int) $matches[1]);
        }
        return $len;
    }

    if (stripos($trimmed, 'transfer-encoding:') === 0 || stripos($trimmed, 'connection:') === 0) {
        return $len;
    }

    if (stripos($trimmed, 'content-length:') === 0 || stripos($trimmed, 'accept-ranges:') === 0 || stripos($trimmed, 'content-range:') === 0 || stripos($trimmed, 'content-type:') === 0) {
        header($trimmed, false);
    }

    return $len;
});

curl_setopt($ch, CURLOPT_WRITEFUNCTION, function ($curl, $data) {
    echo $data;
    flush();
    return strlen($data);
});

$result = curl_exec($ch);
$error = curl_error($ch);
$info = curl_getinfo($ch);

curl_close($ch);

if ($result === false || ($info['http_code'] ?? 0) >= 400) {
    if (!headers_sent()) {
        http_response_code($info['http_code'] ?? 502);
    }
    echo 'Remote fetch failed.';
    exit;
}
