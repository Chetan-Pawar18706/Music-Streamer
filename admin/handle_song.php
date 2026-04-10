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
    $title = trim($_POST['title']); $artist = trim($_POST['artist']);
    if (empty($title) || empty($artist)) { $_SESSION['message'] = "Title and Artist are required."; header('Location: index.php'); exit; }
    $target_dir_song = '../uploads/songs/'; $target_dir_cover = '../uploads/covers/';
    try {
        if ($_POST['action'] == 'add') {
            $song_file_path = ''; $cover_image_path = 'uploads/covers/default.png';
            if (isset($_FILES['song_file']) && $_FILES['song_file']['error'] == 0) {
                $song_file = $_FILES['song_file']; $songFileType = mime_content_type($song_file['tmp_name']);
                if ($songFileType !== 'audio/mpeg') throw new Exception("Invalid file type. Only MP3 allowed.");
                $songFileName = uniqid('song_', true) . '.mp3'; $song_file_path = 'uploads/songs/' . $songFileName;
                move_uploaded_file($song_file['tmp_name'], $target_dir_song . $songFileName);
            }
            if (isset($_FILES['cover_image']) && $_FILES['cover_image']['error'] == 0) {
                $cover_file = $_FILES['cover_image']; $imageFileType = mime_content_type($cover_file['tmp_name']);
                if (in_array($imageFileType, ['image/jpeg', 'image/png', 'image/webp'])) {
                    $coverFileName = uniqid('cover_', true) . '.' . pathinfo($cover_file['name'], PATHINFO_EXTENSION);
                    $cover_image_path = 'uploads/covers/' . $coverFileName;
                    move_uploaded_file($cover_file['tmp_name'], $target_dir_cover . $coverFileName);
                }
            }
            $sql = "INSERT INTO songs (title, artist, file_path, cover_image) VALUES (?, ?, ?, ?)";
            $stmt= $pdo->prepare($sql); $stmt->execute([$title, $artist, $song_file_path, $cover_image_path]);
            $_SESSION['message'] = "Song added successfully.";
        } elseif ($_POST['action'] == 'edit') {
            $song_id = $_POST['song_id']; $stmt = $pdo->prepare("SELECT * FROM songs WHERE id = ?");
            $stmt->execute([$song_id]); $current_song = $stmt->fetch(PDO::FETCH_ASSOC);
            $new_song_path = $current_song['file_path']; $new_cover_path = $current_song['cover_image'];
            if (isset($_FILES['song_file']) && $_FILES['song_file']['error'] == 0) {
                if (file_exists('../' . $current_song['file_path'])) unlink('../' . $current_song['file_path']);
                $song_file = $_FILES['song_file']; $songFileName = uniqid('song_', true) . '.mp3';
                $new_song_path = 'uploads/songs/' . $songFileName;
                move_uploaded_file($song_file['tmp_name'], $target_dir_song . $songFileName);
            }
            if (isset($_FILES['cover_image']) && $_FILES['cover_image']['error'] == 0) {
                if ($current_song['cover_image'] && file_exists('../' . $current_song['cover_image'])) unlink('../' . $current_song['cover_image']);
                $cover_file = $_FILES['cover_image']; $coverFileName = uniqid('cover_', true) . '.' . pathinfo($cover_file['name'], PATHINFO_EXTENSION);
                $new_cover_path = 'uploads/covers/' . $coverFileName;
                move_uploaded_file($cover_file['tmp_name'], $target_dir_cover . $coverFileName);
            }
            $sql = "UPDATE songs SET title = ?, artist = ?, file_path = ?, cover_image = ? WHERE id = ?";
            $stmt= $pdo->prepare($sql); $stmt->execute([$title, $artist, $new_song_path, $new_cover_path, $song_id]);
            $_SESSION['message'] = "Song updated successfully.";
        }
    } catch (Exception $e) { $_SESSION['message'] = "Error: " . $e->getMessage(); }
    header('Location: index.php'); exit;
}
?>