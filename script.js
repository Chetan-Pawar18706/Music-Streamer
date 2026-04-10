document.addEventListener('DOMContentLoaded', () => {
    const audioPlayer = document.getElementById('audio-player');
    const playPauseBtn = document.getElementById('play-pause-btn');
    const nextBtn = document.getElementById('next-btn');
    const progressBar = document.getElementById('progress-bar');
    const currentTimeEl = document.getElementById('current-time'); const durationEl = document.getElementById('duration');
    const playerTitle = document.getElementById('player-title'); const playerArtist = document.getElementById('player-artist'); const playerCover = document.getElementById('player-cover');
    const songItems = document.querySelectorAll('.song-item'); const searchBar = document.getElementById('search-bar');
    const modal = document.getElementById('playlist-modal'); const addToPlaylistBtns = document.querySelectorAll('.add-to-playlist-btn');
    const closeBtn = document.querySelector('.close-btn'); const addToPlaylistForm = document.getElementById('add-to-playlist-form');
    const playlistSelect = document.getElementById('playlist-select'); const hiddenSongIdInput = document.getElementById('hidden-song-id'); const playlistFeedback = document.getElementById('playlist-feedback');

    let currentSongIndex = 0;
    let songs = Array.from(songItems).map(item => ({ id: item.dataset.songId, path: item.dataset.songPath, title: item.dataset.songTitle, artist: item.dataset.songArtist, cover: item.querySelector('.song-cover').src, element: item }));
    let isPlaying = false; let isShuffleOn = false; let repeatMode = 'off';

    const loadSong = (song) => {
        audioPlayer.src = song.path; playerTitle.textContent = song.title; playerArtist.textContent = song.artist; playerCover.src = song.cover;
        document.querySelectorAll('.song-item.active').forEach(el => el.classList.remove('active')); song.element.classList.add('active');
    };
    const playSong = () => { isPlaying = true; audioPlayer.play(); playPauseBtn.textContent = '⏸'; };
    const pauseSong = () => { isPlaying = false; audioPlayer.pause(); playPauseBtn.textContent = '▶'; };
    const togglePlayPause = () => (isPlaying ? pauseSong() : playSong());
    const getNextSongIndex = () => {
        if (isShuffleOn) { let newIndex; do { newIndex = Math.floor(Math.random() * songs.length); } while (newIndex === currentSongIndex && songs.length > 1); return newIndex; }
        return (currentSongIndex + 1) % songs.length;
    };
    const playNextSong = () => { currentSongIndex = getNextSongIndex(); loadSong(songs[currentSongIndex]); playSong(); };
    const formatTime = (seconds) => { if (isNaN(seconds)) return '0:00'; const minutes = Math.floor(seconds / 60); const secs = Math.floor(seconds % 60); return `${minutes}:${secs < 10 ? '0' : ''}${secs}`; };
    const openModal = async (songId) => {
        hiddenSongIdInput.value = songId; playlistFeedback.textContent = ''; playlistFeedback.className = '';
        try {
            const response = await fetch('user/playlists.php?fetch_as_json=true');
            if (!response.ok) throw new Error('Could not fetch playlists.');
            const playlists = await response.json(); playlistSelect.innerHTML = '';
            if (playlists.length === 0) { playlistSelect.innerHTML = '<option value="">You have no playlists.</option>'; }
            else { playlists.forEach(p => { const option = document.createElement('option'); option.value = p.id; option.textContent = p.name; playlistSelect.appendChild(option); }); }
            modal.style.display = 'block';
        } catch (error) { alert(error.message); }
    };
    const closeModal = () => { modal.style.display = 'none'; };

    playPauseBtn.addEventListener('click', togglePlayPause);
    nextBtn.addEventListener('click', playNextSong);
    progressBar.addEventListener('click', (e) => { audioPlayer.currentTime = (e.offsetX / progressBar.clientWidth) * audioPlayer.duration; });
    audioPlayer.addEventListener('timeupdate', () => { const progressPercent = (audioPlayer.currentTime / audioPlayer.duration) * 100; progressBar.value = progressPercent || 0; currentTimeEl.textContent = formatTime(audioPlayer.currentTime); });
    audioPlayer.addEventListener('loadedmetadata', () => { durationEl.textContent = formatTime(audioPlayer.duration); });
    audioPlayer.addEventListener('ended', () => { if (repeatMode !== 'one') playNextSong(); });
    songItems.forEach((item, index) => {
        item.addEventListener('click', (e) => {
            if (e.target.classList.contains('add-to-playlist-btn')) return;
            currentSongIndex = index; loadSong(songs[currentSongIndex]); playSong();
        });
    });
    addToPlaylistBtns.forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.stopPropagation();
            const songId = btn.closest('.song-item').dataset.songId;
            openModal(songId);
        });
    });
    closeBtn.addEventListener('click', closeModal);
    window.addEventListener('click', (e) => { if (e.target == modal) closeModal(); });
    addToPlaylistForm.addEventListener('submit', async (e) => {
        e.preventDefault(); playlistFeedback.textContent = 'Adding...'; playlistFeedback.className = '';
        const formData = new FormData(addToPlaylistForm);
        try {
            const response = await fetch('api/add_to_playlist.php', { method: 'POST', body: formData });
            const result = await response.json();
            if (result.success) { playlistFeedback.textContent = result.message; playlistFeedback.className = 'success'; setTimeout(closeModal, 1500); }
            else { playlistFeedback.textContent = result.message; playlistFeedback.className = 'error'; }
        } catch (error) { playlistFeedback.textContent = 'An error occurred. Please try again.'; playlistFeedback.className = 'error'; }
    });
    searchBar.addEventListener('input', (e) => {
        const searchTerm = e.target.value.toLowerCase();
        songs.forEach(song => {
            const title = song.title.toLowerCase(); const artist = song.artist.toLowerCase();
            song.element.style.display = (title.includes(searchTerm) || artist.includes(searchTerm)) ? '' : 'none';
        });
    });
    if (songs.length > 0) loadSong(songs[0]);
});
document.addEventListener('DOMContentLoaded', () => {
    // --- Get references to all the new modal elements ---
    const modal = document.getElementById('playlist-modal');
    const addToPlaylistForm = document.getElementById('add-to-playlist-form');
    const playlistSelect = document.getElementById('playlist-select');
    const hiddenSongIdInput = document.getElementById('hidden-song-id');
    const playlistFeedback = document.getElementById('playlist-feedback');

    // --- Get all the "+" buttons on the page ---
    const addToPlaylistBtns = document.querySelectorAll('.add-to-playlist-btn');

    // --- Function to OPEN the modal and fetch playlists ---
    const openModal = async (songId) => {
        // Set the song ID into the hidden form field
        hiddenSongIdInput.value = songId;
        
        // Clear any previous messages
        playlistFeedback.textContent = '';
        playlistFeedback.className = '';
        
        try {
            // Fetch the user's playlists from our backend
            const response = await fetch('user/playlists.php?fetch_as_json=true');
            if (!response.ok) {
                throw new Error('Could not fetch playlists.');
            }
            const playlists = await response.json();

            // Clear the dropdown and populate it with the user's playlists
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
            
            // Show the modal
            modal.style.display = 'block';
        } catch (error) {
            alert(error.message);
        }
    };

    // --- Function to CLOSE the modal ---
    const closeModal = () => {
        modal.style.display = 'none';
    };

    // --- Event Listener for each "+" button ---
    addToPlaylistBtns.forEach(btn => {
        btn.addEventListener('click', (e) => {
            // Prevent the click from also triggering the "play song" action
            e.stopPropagation();
            
            // Get the song ID from the parent song item's data attribute
            const songId = btn.closest('.song-item').dataset.songId;
            
            // Open the modal for this specific song
            openModal(songId);
        });
    });

    // --- Event Listener for the close button ---
    closeBtn.addEventListener('click', closeModal);

    // --- Event Listener to close modal if user clicks outside ---
    window.addEventListener('click', (e) => {
        if (e.target == modal) {
            closeModal();
        }
    });

    // --- Event Listener for the form submission (the core logic) ---
    addToPlaylistForm.addEventListener('submit', async (e) => {
        // Prevent the form from reloading the page
        e.preventDefault();
        
        // Show a loading message
        playlistFeedback.textContent = 'Adding...';
        playlistFeedback.className = '';
        
        // Create a FormData object to easily send all form data
        const formData = new FormData(addToPlaylistForm);
        
        try {
            // Send the data to our backend API endpoint
            const response = await fetch('api/add_to_playlist.php', {
                method: 'POST',
                body: formData
            });
            const result = await response.json();

            // Handle the response from the backend
            if (result.success) {
                playlistFeedback.textContent = result.message;
                playlistFeedback.className = 'success';
                // Close the modal after a short delay to show the success message
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
});
document.addEventListener('DOMContentLoaded', () => {
    // --- All your existing code for the player ... ---

    // --- NEW: Check for a song passed from playlist view ---
    const savedSong = localStorage.getItem('currentSong');
    if (savedSong) {
        try {
            const songData = JSON.parse(savedSong);
            // Find the song in our main 'songs' array by its ID
            const songToPlay = songs.find(s => s.id === songData.id);
            
            if (songToPlay) {
                // Load and play the song
                loadSong(songToPlay);
                playSong();
            }
        } catch (e) {
            console.error("Could not parse saved song data", e);
            localStorage.removeItem('currentSong'); // Clean up invalid data
        }
    }

    // --- The rest of your existing event listeners ... ---
});