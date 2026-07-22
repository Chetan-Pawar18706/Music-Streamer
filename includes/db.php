<?php
// Start the session for the entire application
session_start();

// Database credentials
define('DB_HOST', 'localhost');
define('DB_USER', 'root'); // Replace with your DB username
define('DB_PASS', '');     // Replace with your DB password
define('DB_NAME', 'music_db'); // Replace with your DB name

// YouTube Data API v3 key
define('YOUTUBE_API_KEY', 'AIzaSyC8jUHGeF8tp2TbLJOw800c5zF691det24');

// Create a PDO instance
try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    // Set the PDO error mode to exception
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e){
    die("ERROR: Could not connect. " . $e->getMessage());
}
?>