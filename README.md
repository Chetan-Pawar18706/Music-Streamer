# Music Streamer

A simple PHP + MySQL music streaming web app with admin song management, user playlists, and a browser-based audio player.

## What’s new

- Add a cover image as a file upload or by URL.
- Add a song as a local MP3 upload or by external link (YouTube, SoundCloud, etc.).
- Add a category for each song to improve browsing and UX.
- External song links are stored with the track and displayed in the app.
- Admin dashboard now exposes external links and categories for songs.

## Features

- Admin dashboard to add, edit, and delete songs
- Optional local MP3 upload or external song URL
- Optional cover image upload or cover image URL
- User accounts and playlists
- Search songs by title or artist
- Simple audio player for local MP3 playback

## Setup

1. Create the database and tables using `music_streamer_v2.sql`.
2. Update `includes/db.php` with your MySQL connection settings.
3. Ensure the following folders are writable by PHP:
   - `uploads/songs/`
   - `uploads/covers/`

## Adding a song

In the admin panel, you can now:

- Upload an MP3 file, or leave the file blank and provide an external link.
- Upload a cover image, or leave the file blank and provide a cover image URL.

At least one of the following is required when adding a song:

- MP3 file upload
- External song URL

## Database migration

If you already have an existing `songs` table, add the new column:

```sql
ALTER TABLE songs
  ADD COLUMN external_link varchar(255) DEFAULT NULL;
```

If you are installing fresh, the updated SQL file already includes the `external_link` field.

## Notes

- A song with an external link can still keep an uploaded local file.
- Cover image storage now supports either local files or remote image URLs.
- External links open in a new tab from the song list.
