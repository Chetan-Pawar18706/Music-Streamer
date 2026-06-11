<?php
require_once '../includes/db.php';
if (!isset($_SESSION['admin_logged_in'])) { header('Location: login.php'); exit; }
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $song_id = $_GET['id'];
    $stmt = $pdo->prepare("SELECT file_path, cover_image FROM songs WHERE id = ?");
    $stmt->execute([$song_id]); $song = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($song) {
        if (file_exists('../' . $song['file_path'])) unlink('../' . $song['file_path']);
        if ($song['cover_image'] && file_exists('../' . $song['cover_image'])) unlink('../' . $song['cover_image']);
        $stmt = $pdo->prepare("DELETE FROM songs WHERE id = ?"); $stmt->execute([$song_id]);
        $_SESSION['message'] = "Song deleted successfully.";
    }
    header('Location: index.php'); exit;
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $title = trim($_POST['title']);
    $artist = trim($_POST['artist']);
    $category = trim($_POST['category'] ?? '');
    $external_link = trim($_POST['external_link'] ?? '');
    $cover_image_url = trim($_POST['cover_image_url'] ?? '');

    if (empty($title) || empty($artist) || empty($category)) {
        $_SESSION['message'] = "Title, Artist, and Category are required.";
        header('Location: index.php');
        exit;
    }

    if (empty($category)) {
        $category = 'Uncategorized';
    }

    $target_dir_song = '../uploads/songs/';
    $target_dir_cover = '../uploads/covers/';

    $isLocalPath = function ($path) {
        return $path && !preg_match('#^https?://#i', $path);
    };

    try {
        if ($_POST['action'] == 'add') {
            if ((!isset($_FILES['song_file']) || $_FILES['song_file']['error'] !== 0) && empty($external_link)) {
                throw new Exception("Please upload an MP3 file or provide an external song link.");
            }
            if (!empty($external_link) && !filter_var($external_link, FILTER_VALIDATE_URL)) {
                throw new Exception("Invalid external song link.");
            }

            $song_file_path = '';
            $cover_image_path = 'uploads/covers/default.png';

            if (isset($_FILES['song_file']) && $_FILES['song_file']['error'] == 0) {
                $song_file = $_FILES['song_file'];
                $songFileType = mime_content_type($song_file['tmp_name']);
                if ($songFileType !== 'audio/mpeg') {
                    throw new Exception("Invalid file type. Only MP3 allowed.");
                }
                $songFileName = uniqid('song_', true) . '.mp3';
                $song_file_path = 'uploads/songs/' . $songFileName;
                move_uploaded_file($song_file['tmp_name'], $target_dir_song . $songFileName);
            }

            if (!empty($cover_image_url)) {
                if (!filter_var($cover_image_url, FILTER_VALIDATE_URL)) {
                    throw new Exception("Invalid cover image URL.");
                }
                $cover_image_path = $cover_image_url;
            } elseif (isset($_FILES['cover_image']) && $_FILES['cover_image']['error'] == 0) {
                $cover_file = $_FILES['cover_image'];
                $imageFileType = mime_content_type($cover_file['tmp_name']);
                if (!in_array($imageFileType, ['image/jpeg', 'image/png', 'image/webp'])) {
                    throw new Exception("Invalid cover image type. Only JPG, PNG, and WEBP allowed.");
                }
                $coverFileName = uniqid('cover_', true) . '.' . pathinfo($cover_file['name'], PATHINFO_EXTENSION);
                $cover_image_path = 'uploads/covers/' . $coverFileName;
                move_uploaded_file($cover_file['tmp_name'], $target_dir_cover . $coverFileName);
            }

            $sql = "INSERT INTO songs (title, artist, category, file_path, cover_image, external_link) VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$title, $artist, $category, $song_file_path, $cover_image_path, $external_link]);
            $_SESSION['message'] = "Song added successfully.";
        } elseif ($_POST['action'] == 'edit') {
            $song_id = $_POST['song_id'];
            $stmt = $pdo->prepare("SELECT * FROM songs WHERE id = ?");
            $stmt->execute([$song_id]);
            $current_song = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$current_song) {
                throw new Exception("Song not found.");
            }

            if (!empty($external_link) && !filter_var($external_link, FILTER_VALIDATE_URL)) {
                throw new Exception("Invalid external song link.");
            }

            $new_song_path = $current_song['file_path'];
            $new_cover_path = $current_song['cover_image'];
            $new_external_link = $external_link;

            if (isset($_FILES['song_file']) && $_FILES['song_file']['error'] == 0) {
                if ($isLocalPath($current_song['file_path']) && file_exists('../' . $current_song['file_path'])) {
                    unlink('../' . $current_song['file_path']);
                }
                $song_file = $_FILES['song_file'];
                $songFileType = mime_content_type($song_file['tmp_name']);
                if ($songFileType !== 'audio/mpeg') {
                    throw new Exception("Invalid file type. Only MP3 allowed.");
                }
                $songFileName = uniqid('song_', true) . '.mp3';
                $new_song_path = 'uploads/songs/' . $songFileName;
                move_uploaded_file($song_file['tmp_name'], $target_dir_song . $songFileName);
            }

            if (!empty($cover_image_url)) {
                if (!filter_var($cover_image_url, FILTER_VALIDATE_URL)) {
                    throw new Exception("Invalid cover image URL.");
                }
                if ($isLocalPath($current_song['cover_image']) && file_exists('../' . $current_song['cover_image'])) {
                    unlink('../' . $current_song['cover_image']);
                }
                $new_cover_path = $cover_image_url;
            } elseif (isset($_FILES['cover_image']) && $_FILES['cover_image']['error'] == 0) {
                if ($isLocalPath($current_song['cover_image']) && file_exists('../' . $current_song['cover_image'])) {
                    unlink('../' . $current_song['cover_image']);
                }
                $cover_file = $_FILES['cover_image'];
                $imageFileType = mime_content_type($cover_file['tmp_name']);
                if (!in_array($imageFileType, ['image/jpeg', 'image/png', 'image/webp'])) {
                    throw new Exception("Invalid cover image type. Only JPG, PNG, and WEBP allowed.");
                }
                $coverFileName = uniqid('cover_', true) . '.' . pathinfo($cover_file['name'], PATHINFO_EXTENSION);
                $new_cover_path = 'uploads/covers/' . $coverFileName;
                move_uploaded_file($cover_file['tmp_name'], $target_dir_cover . $coverFileName);
            }

            $sql = "UPDATE songs SET title = ?, artist = ?, category = ?, file_path = ?, cover_image = ?, external_link = ? WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$title, $artist, $category, $new_song_path, $new_cover_path, $new_external_link, $song_id]);
            $_SESSION['message'] = "Song updated successfully.";
        }
    } catch (Exception $e) {
        $_SESSION['message'] = "Error: " . $e->getMessage();
    }
    header('Location: index.php');
    exit;
}
?>