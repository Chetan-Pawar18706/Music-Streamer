(function() {
    let ytPlayer = null;
    let ytReady = false;
    let ytLoadPromise = null;
    let isPlaying = false;
    let isShuffleOn = false;
    let repeatMode = 'off';
    let songs = [];
    let currentIndex = -1;
    let ytProgressInterval = null;

    const getEl = (id) => document.getElementById(id);

    const playPauseBtn = getEl('play-pause-btn');
    const nextBtn = getEl('next-btn');
    const prevBtn = getEl('prev-btn');
    const shuffleBtn = getEl('shuffle-btn');
    const repeatBtn = getEl('repeat-btn');
    const progressBar = getEl('progress-bar');
    const currentTimeEl = getEl('current-time');
    const durationEl = getEl('duration');
    const playerTitle = getEl('player-title');
    const playerArtist = getEl('player-artist');
    const playerCover = getEl('player-cover');
    const npCover = getEl('np-cover');
    const npTitle = getEl('np-title');
    const npArtist = getEl('np-artist');
    const ytContainer = getEl('youtube-player-container');
    const sidebarSearch = getEl('sidebar-search');

    function initSongs() {
        const sidebarEls = document.querySelectorAll('.sidebar-song');
        songs = Array.from(sidebarEls).map(el => ({
            youtubeId: el.dataset.youtubeId,
            title: el.dataset.title,
            artist: el.dataset.artist,
            cover: el.dataset.cover || 'https://via.placeholder.com/300',
            element: el
        }));
    }

    function ensureYtApi() {
        if (ytLoadPromise) return ytLoadPromise;
        ytLoadPromise = new Promise((resolve) => {
            if (window.YT && window.YT.Player) { resolve(); return; }
            window.onYouTubeIframeAPIReady = function() {
                ytReady = true;
                resolve();
            };
            if (!document.querySelector('script[src*="youtube.com/iframe_api"]')) {
                const tag = document.createElement('script');
                tag.src = 'https://www.youtube.com/iframe_api';
                document.head.appendChild(tag);
            }
        });
        return ytLoadPromise;
    }

    function createYtPlayer(videoId) {
        return ensureYtApi().then(() => {
            return new Promise((resolve) => {
                if (ytPlayer) {
                    ytPlayer.loadVideoById(videoId);
                    resolve(ytPlayer);
                    return;
                }
                ytPlayer = new YT.Player('youtube-player', {
                    height: '202',
                    width: '360',
                    videoId: videoId,
                    playerVars: { autoplay: 1, controls: 1, modestbranding: 1, rel: 0 },
                    events: {
                        onReady: function() { resolve(ytPlayer); },
                        onStateChange: function(e) {
                            if (e.data === YT.PlayerState.ENDED) handleEnded();
                        }
                    }
                });
            });
        });
    }

    function updateUI(song) {
        if (!song) return;
        if (playerTitle) playerTitle.textContent = song.title || '-';
        if (playerArtist) playerArtist.textContent = song.artist || '-';
        if (playerCover) playerCover.src = song.cover;
        if (npCover) npCover.src = song.cover;
        if (npTitle) npTitle.textContent = song.title || 'Select a song';
        if (npArtist) npArtist.textContent = song.artist || 'Browse your library to start playing';
        document.querySelectorAll('.sidebar-song.active').forEach(el => el.classList.remove('active'));
        if (song.element) song.element.classList.add('active');
        if (playPauseBtn) {
            playPauseBtn.disabled = false;
            playPauseBtn.style.opacity = '1';
        }
    }

    function startProgress() {
        stopProgress();
        ytProgressInterval = setInterval(() => {
            if (!ytPlayer || typeof ytPlayer.getCurrentTime !== 'function') return;
            const cur = ytPlayer.getCurrentTime() || 0;
            const dur = ytPlayer.getDuration() || 0;
            if (dur > 0) {
                progressBar.value = (cur / dur) * 100;
                currentTimeEl.textContent = formatTime(cur);
                durationEl.textContent = formatTime(dur);
            }
        }, 250);
    }

    function stopProgress() {
        if (ytProgressInterval) { clearInterval(ytProgressInterval); ytProgressInterval = null; }
    }

    function formatTime(s) {
        if (isNaN(s)) return '0:00';
        const m = Math.floor(s / 60);
        const sec = Math.floor(s % 60);
        return m + ':' + (sec < 10 ? '0' : '') + sec;
    }

    function saveCurrentSong(song) {
        localStorage.setItem('currentSong', JSON.stringify({
            youtubeId: song.youtubeId,
            title: song.title,
            artist: song.artist,
            cover: song.cover
        }));
    }

    async function loadSong(index) {
        if (index < 0 || index >= songs.length) return;
        currentIndex = index;
        const song = songs[index];
        updateUI(song);
        if (ytContainer) ytContainer.classList.add('active');
        await createYtPlayer(song.youtubeId);
        if (ytPlayer && typeof ytPlayer.playVideo === 'function') {
            ytPlayer.playVideo();
        }
        isPlaying = true;
        if (playPauseBtn) playPauseBtn.innerHTML = '&#9646;&#9646;';
        startProgress();
        saveCurrentSong(song);
    }

    function handleEnded() {
        if (repeatMode === 'one') {
            if (ytPlayer) { ytPlayer.seekTo(0, true); ytPlayer.playVideo(); }
        } else {
            playNext();
        }
    }

    function togglePlayPause() {
        if (!ytPlayer || typeof ytPlayer.playVideo !== 'function') return;
        if (isPlaying) {
            ytPlayer.pauseVideo();
            isPlaying = false;
            if (playPauseBtn) playPauseBtn.innerHTML = '&#9654;';
            stopProgress();
        } else {
            ytPlayer.playVideo();
            isPlaying = true;
            if (playPauseBtn) playPauseBtn.innerHTML = '&#9646;&#9646;';
            startProgress();
        }
    }

    function getNextIndex() {
        if (songs.length === 0) return -1;
        if (repeatMode === 'one') return currentIndex;
        if (isShuffleOn) {
            let n; do { n = Math.floor(Math.random() * songs.length); } while (n === currentIndex && songs.length > 1);
            return n;
        }
        return (currentIndex + 1) % songs.length;
    }

    function getPrevIndex() {
        if (songs.length === 0) return -1;
        if (repeatMode === 'one') return currentIndex;
        if (isShuffleOn) {
            let p; do { p = Math.floor(Math.random() * songs.length); } while (p === currentIndex && songs.length > 1);
            return p;
        }
        return (currentIndex - 1 + songs.length) % songs.length;
    }

    function playNext() { const n = getNextIndex(); if (n >= 0) loadSong(n); }
    function playPrev() { const p = getPrevIndex(); if (p >= 0) loadSong(p); }

    if (playPauseBtn) playPauseBtn.addEventListener('click', togglePlayPause);
    if (nextBtn) nextBtn.addEventListener('click', playNext);
    if (prevBtn) prevBtn.addEventListener('click', playPrev);
    if (shuffleBtn) shuffleBtn.addEventListener('click', () => { isShuffleOn = !isShuffleOn; shuffleBtn.classList.toggle('active', isShuffleOn); });
    if (repeatBtn) repeatBtn.addEventListener('click', () => {
        if (repeatMode === 'off') { repeatMode = 'all'; repeatBtn.classList.add('active'); repeatBtn.title = 'Repeat All'; }
        else if (repeatMode === 'all') { repeatMode = 'one'; repeatBtn.title = 'Repeat One'; }
        else { repeatMode = 'off'; repeatBtn.classList.remove('active'); repeatBtn.title = 'Repeat Off'; }
    });
    if (progressBar) {
        progressBar.addEventListener('click', (e) => {
            if (!ytPlayer || typeof ytPlayer.getDuration !== 'function') return;
            const dur = ytPlayer.getDuration();
            if (dur > 0) ytPlayer.seekTo((e.offsetX / progressBar.clientWidth) * dur, true);
        });
    }

    document.querySelectorAll('.sidebar-song').forEach((el, i) => {
        el.addEventListener('click', () => loadSong(i));
    });
    if (sidebarSearch) {
        sidebarSearch.addEventListener('input', (e) => {
            const t = e.target.value.toLowerCase();
            songs.forEach(s => {
                if (s.element) {
                    s.element.style.display = (s.title.toLowerCase().includes(t) || s.artist.toLowerCase().includes(t)) ? '' : 'none';
                }
            });
        });
    }

    window.playSongFromSearch = function(youtubeId, title, artist, cover) {
        const song = { youtubeId, title, artist, cover, element: null };
        const existingIdx = songs.findIndex(s => s.youtubeId === youtubeId);
        if (existingIdx >= 0) {
            loadSong(existingIdx);
        } else {
            songs.unshift(song);
            loadSong(0);
        }
    };

    function init() {
        initSongs();
        const saved = localStorage.getItem('currentSong');
        if (saved) {
            try {
                const data = JSON.parse(saved);
                if (data && data.youtubeId) {
                    const idx = songs.findIndex(s => s.youtubeId === data.youtubeId);
                    if (idx >= 0) {
                        currentIndex = idx;
                        updateUI(songs[idx]);
                    } else {
                        songs.unshift({ ...data, element: null });
                        currentIndex = 0;
                        updateUI(songs[0]);
                    }
                    if (ytContainer) ytContainer.classList.add('active');
                    createYtPlayer(data.youtubeId).then(() => {
                        if (ytPlayer && typeof ytPlayer.playVideo === 'function') {
                            ytPlayer.playVideo();
                            isPlaying = true;
                            if (playPauseBtn) playPauseBtn.innerHTML = '&#9646;&#9646;';
                            startProgress();
                        }
                    });
                }
            } catch(e) {}
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
