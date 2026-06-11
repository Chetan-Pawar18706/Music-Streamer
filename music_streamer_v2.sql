-- create database `music_db`;
-- use `music_db`;

-- Table for users
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL UNIQUE,
  `email` varchar(100) NOT NULL UNIQUE,
  `password_hash` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- Table for playlists
CREATE TABLE `playlists` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Linking table for songs in a playlist
CREATE TABLE `playlist_songs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `playlist_id` int(11) NOT NULL,
  `song_id` int(11) NOT NULL,
  `added_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_playlist_song` (`playlist_id`, `song_id`),
  FOREIGN KEY (`playlist_id`) REFERENCES `playlists`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`song_id`) REFERENCES `songs`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table for songs
CREATE TABLE `songs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `artist` varchar(255) NOT NULL,
  `category` varchar(100) NOT NULL DEFAULT 'Uncategorized',
  `file_path` varchar(255) NOT NULL,
  `cover_image` varchar(255) DEFAULT NULL,
  `external_link` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
-- Sample songs with real online cover image URLs
INSERT INTO `songs` (`title`, `artist`, `category`, `file_path`, `cover_image`, `external_link`) VALUES
('Chill Waves', 'Neon Nights', 'Electronic', '', 'https://images.unsplash.com/photo-1493225457124-a3eb161ffa5f?q=80&w=800', 'https://www.youtube.com/watch?v=dQw4w9WgXcQ'),

('Future Drive', 'Silver Echo', 'Modern', '', 'https://images.unsplash.com/photo-1470229722913-7c0e2dbbafd3?q=80&w=800', 'https://www.youtube.com/watch?v=3JZ4pnNtyxQ'),

('Midnight Groove', 'Luna Soul', 'Jazz', '', 'https://images.unsplash.com/photo-1511192336575-5a79af67a629?q=80&w=800', 'https://www.youtube.com/watch?v=V-_O7nl0Ii0'),

('Heritage Song', 'Golden Roots', 'Traditional', '', 'https://images.unsplash.com/photo-1507838153414-b4b713384a76?q=80&w=800', 'https://www.youtube.com/watch?v=2vjPBrBU-TM'),

('Desert Sunrise', 'Luna Strings', 'World', '', 'https://images.unsplash.com/photo-1500530855697-b586d89ba3ee?q=80&w=800', 'https://www.youtube.com/watch?v=04854XqcfCY'),

('Neon City', 'Urban Pulse', 'Pop', '', 'https://images.unsplash.com/photo-1499364615650-ec38552f4f34?q=80&w=800', 'https://www.youtube.com/watch?v=Zi_XLOBDo_Y'),

('Ocean Whisper', 'Aqua Folk', 'Folk', '', 'https://images.unsplash.com/photo-1507525428034-b723cf961d3e?q=80&w=800', 'https://www.youtube.com/watch?v=YykjpeuMNEk'),

('Electric Heart', 'Pulse Theory', 'EDM', '', 'https://images.unsplash.com/photo-1501386761578-eac5c94b800a?q=80&w=800', 'https://www.youtube.com/watch?v=kXYiU_JCYtU'),

('Sunset Road', 'Country Lane', 'Country', '', 'https://images.unsplash.com/photo-1500534623283-312aade485b7?q=80&w=800', 'https://www.youtube.com/watch?v=0KSOMA3QBU0'),

('Moonlit Jazz', 'Blue Notes', 'Jazz', '', 'https://images.unsplash.com/photo-1511671782779-c97d3d27a1d4?q=80&w=800', 'https://www.youtube.com/watch?v=HMj2uJf9I9s'),

('Street Lights', 'Indie Drive', 'Indie', '', 'https://images.unsplash.com/photo-1496293455970-f8581aae0e3b?q=80&w=800', 'https://www.youtube.com/watch?v=OPf0YbXqDm0'),

('Soul Bloom', 'Velvet Soul', 'R&B', '', 'https://images.unsplash.com/photo-1516280440614-37939bbacd81?q=80&w=800', 'https://www.youtube.com/watch?v=RlVSQ2Z75FY'),

('Echoes of Home', 'Folk Harmony', 'Traditional', '', 'https://images.unsplash.com/photo-1511379938547-c1f69419868d?q=80&w=800', 'https://www.youtube.com/watch?v=U9BwWKXjVaI'),

('Crimson Nights', 'Rock Alloy', 'Rock', '', 'https://images.unsplash.com/photo-1501612780327-45045538702b?q=80&w=800', 'https://www.youtube.com/watch?v=ktvTqknDobU'),

('Summer Bloom', 'Latin Fire', 'Latin', '', 'https://images.unsplash.com/photo-1504609773096-104ff2c73ba4?q=80&w=800', 'https://www.youtube.com/watch?v=dQw4w9WgXcQ'),

('Soul Motion', 'Velvet Rhythm', 'Soul', '', 'https://images.unsplash.com/photo-1493225457124-a3eb161ffa5f?q=80&w=800', 'https://www.youtube.com/watch?v=09R8_2nJtjg'),

('Northern Stars', 'Ambient Sky', 'Electronic', '', 'https://images.unsplash.com/photo-1506744038136-46273834b3fb?q=80&w=800', 'https://www.youtube.com/watch?v=CevxZvSJLk8'),

('River Song', 'Acoustic Wood', 'Acoustic', '', 'https://images.unsplash.com/photo-1459749411175-04bf5292ceea?q=80&w=800', 'https://www.youtube.com/watch?v=hLQl3WQQoQ0'),

('Golden Roads', 'Retro Groove', 'Blues', '', 'https://images.unsplash.com/photo-1465847899084-d164df4dedc6?q=80&w=800', 'https://www.youtube.com/watch?v=MtCMf_gqzJM'),

('Island Breeze', 'Reggae Vibes', 'Reggae', '', 'https://images.unsplash.com/photo-1500534314209-a25ddb2bd429?q=80&w=800', 'https://www.youtube.com/watch?v=TYi5I4HTpTE');