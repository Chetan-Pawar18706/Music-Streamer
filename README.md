# Music Streamer

A YouTube-first music streaming platform built with PHP, MySQL, and the YouTube Data API v3. Users can search YouTube, build personal libraries, create playlists, and play music directly in the browser.

## Features

### User
- **YouTube Search** — Search millions of songs from YouTube with instant results
- **Unlimited Results** — Load as many results as you want with pagination
- **Smart Dashboard** — Personalized song recommendations based on search history
- **My Library** — Save any song from YouTube to your personal library
- **Playlists** — Create unlimited playlists and organize your saved songs
- **In-Browser Player** — YouTube IFrame API powered player with shuffle, repeat, and progress control
- **Quick Play** — Click play anywhere and jump straight into the player

### Admin
- **Dashboard** — Overview of total users, songs, playlists, and searches
- **User Management** — View all registered users, inspect their libraries and playlists
- **Search Activity** — Monitor what users are searching for
- **No Song Management** — Songs come entirely from YouTube, no manual entry needed

## Tech Stack

- **Backend:** PHP 8+ with PDO
- **Database:** MySQL (music_db)
- **API:** YouTube Data API v3
- **Frontend:** Vanilla HTML/CSS/JS
- **Player:** YouTube IFrame API
- **Font:** Google Fonts (Inter)

## Setup

1. **Import the database:**
   ```sql
   SOURCE music_streamer_v3.sql;
   ```
   Or import `music_streamer_v3.sql` via phpMyAdmin.

2. **Configure database connection:**
   Edit `includes/db.php` with your MySQL credentials.

3. **Set your YouTube API Key:**
   In `includes/db.php`, replace the API key with your own from [Google Cloud Console](https://console.cloud.google.com/).

4. **Start the server:**
   Place the project in your web server's root (e.g., XAMPP `htdocs/`) and visit:
   ```
   http://localhost/music-streamer/
   ```

5. **Create an admin account:**
   Use PHP to hash a password and insert into the `admins` table.

## Default Credentials

Admin credentials are in the database. Change them after first login.

## Database Schema

- **users** — User accounts (id, username, email, password_hash, created_at)
- **admins** — Admin accounts (id, username, password_hash, created_at)
- **user_songs** — Songs saved by users from YouTube (id, user_id, youtube_id, title, artist, cover_image, added_at)
- **playlists** — User playlists (id, user_id, name, created_at)
- **playlist_songs** — Songs in playlists (id, playlist_id, song_id, added_at)
- **user_searches** — Search history (id, user_id, query, searched_at)

## How It Works

1. User searches for a song on the search page
2. Results are fetched from the YouTube Data API v3
3. User can **play** any song instantly or **add** it to their library
4. Saved songs appear in the player sidebar for quick access
5. Users can organize songs into playlists
6. Dashboard shows personalized recommendations based on past searches

## License

MIT
