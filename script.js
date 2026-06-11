document.addEventListener('DOMContentLoaded', () => {
    const audioPlayer = document.getElementById('audio-player');
    const playPauseBtn = document.getElementById('play-pause-btn');
    const nextBtn = document.getElementById('next-btn');
    const prevBtn = document.getElementById('prev-btn');
    const shuffleBtn = document.getElementById('shuffle-btn');
    const repeatBtn = document.getElementById('repeat-btn');
    const externalLinkPlayerBtn = document.getElementById('external-link-player-btn');
    const progressBar = document.getElementById('progress-bar');
    const currentTimeEl = document.getElementById('current-time');
    const durationEl = document.getElementById('duration');
    const playerTitle = document.getElementById('player-title');
    const playerArtist = document.getElementById('player-artist');
    const playerCategory = document.getElementById('player-category');
    const playerCover = document.getElementById('player-cover');
    const songItems = document.querySelectorAll('.song-item');
    const searchBar = document.getElementById('search-bar');
    const modal = document.getElementById('playlist-modal');
    const addToPlaylistBtns = document.querySelectorAll('.add-to-playlist-btn');
    const closeBtn = document.querySelector('.close-btn');
    const addToPlaylistForm = document.getElementById('add-to-playlist-form');
    const playlistSelect = document.getElementById('playlist-select');
    const hiddenSongIdInput = document.getElementById('hidden-song-id');
    const playlistFeedback = document.getElementById('playlist-feedback');

    let currentSongIndex = 0;
    let songs = Array.from(songItems).map(item => ({
        id: item.dataset.songId,
        path: item.dataset.songPath,
        externalLink: item.dataset.externalLink,
        category: item.dataset.songCategory,
        title: item.dataset.songTitle,
        artist: item.dataset.songArtist,
        cover: item.querySelector('.song-cover') ? item.querySelector('.song-cover').src : 'https://via.placeholder.com/50',
        element: item
    }));

    let isPlaying = false;
    let isShuffleOn = false;
    let repeatMode = 'off';

    const formatTime = (seconds) => {
        if (isNaN(seconds)) return '0:00';
        const minutes = Math.floor(seconds / 60);
        const secs = Math.floor(seconds % 60);
        return `${minutes}:${secs < 10 ? '0' : ''}${secs}`;
    };

    const getSongIndexById = (id) => songs.findIndex(song => song.id === id);

    const updateButtonStates = () => {
        const canPlay = Boolean(audioPlayer.src);
        playPauseBtn.disabled = !canPlay;
        playPauseBtn.style.opacity = canPlay ? '1' : '0.5';
        playPauseBtn.style.cursor = canPlay ? 'pointer' : 'not-allowed';
        if (externalLinkPlayerBtn) {
            const currentSong = songs[currentSongIndex];
            if (currentSong && currentSong.externalLink) {
                externalLinkPlayerBtn.style.display = 'inline-flex';
                externalLinkPlayerBtn.dataset.link = currentSong.externalLink;
            } else {
                externalLinkPlayerBtn.style.display = 'none';
                externalLinkPlayerBtn.dataset.link = '';
            }
        }
        shuffleBtn.classList.toggle('active', isShuffleOn);
        repeatBtn.classList.toggle('active', repeatMode !== 'off');
    };

    const updateMetadata = (song) => {
        playerTitle.textContent = song.title || 'Unknown Track';
        playerArtist.textContent = song.artist || 'Unknown Artist';
        playerCategory.textContent = song.category ? song.category : '';
        playerCover.src = song.cover || 'https://via.placeholder.com/50';
        document.querySelectorAll('.song-item.active').forEach(el => el.classList.remove('active'));
        if (song.element) song.element.classList.add('active');
        updateButtonStates();
    };

    const loadSong = (song) => {
        const source = song.path ? encodeURI(song.path) : (song.externalLink ? `api/proxy_audio.php?url=${encodeURIComponent(song.externalLink)}` : '');
        if (!source) {
            audioPlayer.removeAttribute('src');
            audioPlayer.load();
            updateMetadata(song);
            return;
        }

        audioPlayer.src = source;
        audioPlayer.load();
        updateMetadata(song);
    };

    const playSong = async () => {
        if (!audioPlayer.src) {
            alert('No local preview is available for this song. Use the external link button if one is available.');
            return;
        }

        try {
            await audioPlayer.play();
            isPlaying = true;
            playPauseBtn.textContent = '⏸';
        } catch (error) {
            console.error('Playback failed:', error);
        }
    };

    const pauseSong = () => {
        isPlaying = false;
        audioPlayer.pause();
        playPauseBtn.textContent = '▶';
    };

    const togglePlayPause = () => {
        if (playPauseBtn.disabled) return;
        isPlaying ? pauseSong() : playSong();
    };

    const getNextSongIndex = () => {
        if (repeatMode === 'one') {
            return currentSongIndex;
        }
        if (isShuffleOn && songs.length > 1) {
            let newIndex;
            do {
                newIndex = Math.floor(Math.random() * songs.length);
            } while (newIndex === currentSongIndex);
            return newIndex;
        }
        return (currentSongIndex + 1) % songs.length;
    };

    const playNextSong = () => {
        currentSongIndex = getNextSongIndex();
        loadSong(songs[currentSongIndex]);
        playSong();
    };

    const playPreviousSong = () => {
        if (repeatMode === 'one') {
            loadSong(songs[currentSongIndex]);
        } else if (isShuffleOn && songs.length > 1) {
            currentSongIndex = getNextSongIndex();
            loadSong(songs[currentSongIndex]);
        } else {
            currentSongIndex = (currentSongIndex - 1 + songs.length) % songs.length;
            loadSong(songs[currentSongIndex]);
        }
        playSong();
    };

    const toggleShuffle = () => {
        isShuffleOn = !isShuffleOn;
        updateButtonStates();
    };

    const toggleRepeat = () => {
        if (repeatMode === 'off') {
            repeatMode = 'all';
            repeatBtn.textContent = '🔁';
            repeatBtn.title = 'Repeat All';
        } else if (repeatMode === 'all') {
            repeatMode = 'one';
            repeatBtn.textContent = '🔂';
            repeatBtn.title = 'Repeat One';
        } else {
            repeatMode = 'off';
            repeatBtn.textContent = '🔁';
            repeatBtn.title = 'Repeat Off';
        }
        updateButtonStates();
    };

    const openExternalLink = () => {
        if (!externalLinkPlayerBtn) return;
        const link = externalLinkPlayerBtn.dataset.link;
        if (link) {
            window.open(link, '_blank');
        }
    };

    const openModal = async (songId) => {
        if (!playlistSelect || !hiddenSongIdInput) return;

        hiddenSongIdInput.value = songId;
        playlistFeedback.textContent = '';
        playlistFeedback.className = '';

        try {
            const response = await fetch('user/playlists.php?fetch_as_json=true');
            if (!response.ok) throw new Error('Could not fetch playlists.');
            const playlists = await response.json();
            playlistSelect.innerHTML = '';

            if (playlists.length === 0) {
                playlistSelect.innerHTML = '<option value="">You have no playlists.</option>';
            } else {
                playlists.forEach(p => {
                    const option = document.createElement('option');
                    option.value = p.id;
                    option.textContent = p.name;
                    playlistSelect.appendChild(option);
                });
            }

            modal.style.display = 'block';
        } catch (error) {
            alert(error.message);
        }
    };

    const closeModal = () => {
        if (!modal) return;
        modal.style.display = 'none';
    };

    const loadSavedSong = () => {
        const savedSongJSON = localStorage.getItem('currentSong');
        if (!savedSongJSON) return false;

        try {
            const savedSong = JSON.parse(savedSongJSON);
            if (!savedSong || !savedSong.id) return false;

            const existingIndex = getSongIndexById(savedSong.id);
            if (existingIndex >= 0) {
                currentSongIndex = existingIndex;
                loadSong(songs[currentSongIndex]);
                return true;
            }

            if (savedSong.path || savedSong.externalLink) {
                songs.unshift({
                    ...savedSong,
                    element: null
                });
                currentSongIndex = 0;
                loadSong(songs[currentSongIndex]);
                return true;
            }
        } catch (error) {
            console.warn('Unable to parse currentSong from localStorage.', error);
        }

        return false;
    };

    playPauseBtn.addEventListener('click', togglePlayPause);
    nextBtn.addEventListener('click', playNextSong);
    prevBtn.addEventListener('click', playPreviousSong);
    shuffleBtn.addEventListener('click', toggleShuffle);
    repeatBtn.addEventListener('click', toggleRepeat);
    if (externalLinkPlayerBtn) externalLinkPlayerBtn.addEventListener('click', openExternalLink);

    progressBar.addEventListener('click', (e) => {
        if (!audioPlayer.duration) return;
        audioPlayer.currentTime = (e.offsetX / progressBar.clientWidth) * audioPlayer.duration;
    });

    audioPlayer.addEventListener('timeupdate', () => {
        if (!audioPlayer.duration) return;
        const progressPercent = (audioPlayer.currentTime / audioPlayer.duration) * 100;
        progressBar.value = progressPercent || 0;
        currentTimeEl.textContent = formatTime(audioPlayer.currentTime);
    });

    audioPlayer.addEventListener('loadedmetadata', () => {
        durationEl.textContent = formatTime(audioPlayer.duration);
        updateButtonStates();
    });

    audioPlayer.addEventListener('ended', () => {
        if (repeatMode === 'one') {
            audioPlayer.currentTime = 0;
            playSong();
        } else {
            playNextSong();
        }
    });

    songItems.forEach((item, index) => {
        item.addEventListener('click', (e) => {
            if (e.target.classList.contains('add-to-playlist-btn') || e.target.classList.contains('external-link-btn')) return;
            currentSongIndex = index;
            loadSong(songs[currentSongIndex]);
            playSong();
        });
    });

    addToPlaylistBtns.forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.stopPropagation();
            const songId = btn.closest('.song-item')?.dataset.songId;
            if (songId) openModal(songId);
        });
    });

    closeBtn.addEventListener('click', closeModal);
    window.addEventListener('click', (e) => {
        if (e.target === modal) closeModal();
    });

    addToPlaylistForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        playlistFeedback.textContent = 'Adding...';
        playlistFeedback.className = '';
        const formData = new FormData(addToPlaylistForm);

        try {
            const response = await fetch('api/add_to_playlist.php', {
                method: 'POST',
                body: formData
            });
            const result = await response.json();
            if (result.success) {
                playlistFeedback.textContent = result.message;
                playlistFeedback.className = 'success';
                setTimeout(closeModal, 1500);
            } else {
                playlistFeedback.textContent = result.message;
                playlistFeedback.className = 'error';
            }
        } catch (error) {
            playlistFeedback.textContent = 'An error occurred. Please try again.';
            playlistFeedback.className = 'error';
        }
    });

    if (searchBar) {
        searchBar.addEventListener('input', (e) => {
            const searchTerm = e.target.value.toLowerCase();
            songs.forEach(song => {
                const title = song.title.toLowerCase();
                const artist = song.artist.toLowerCase();
                if (song.element) {
                    song.element.style.display = (title.includes(searchTerm) || artist.includes(searchTerm)) ? '' : 'none';
                }
            });
        });
    }

    if (songs.length > 0) {
        const hasSavedSong = loadSavedSong();
        if (!hasSavedSong) loadSong(songs[currentSongIndex]);
    }
});