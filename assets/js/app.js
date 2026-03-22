document.addEventListener('DOMContentLoaded', () => {
    // Elements
    const urlInput = document.getElementById('urlInput');
    const btnAnalyze = document.getElementById('btnAnalyze');
    const errorMsg = document.getElementById('errorMsg');
    const platformBtns = document.querySelectorAll('.platform-btn');
    const resultsSection = document.getElementById('resultsSection');
    const videoPreview = document.getElementById('videoPreview');
    const platformBadge = document.getElementById('platformBadge');
    const videoTitle = document.getElementById('videoTitle');
    const videoAuthor = document.getElementById('videoAuthor');
    const videoDuration = document.getElementById('videoDuration');
    const qualitySelect = document.getElementById('qualitySelect');
    const btnDownload = document.getElementById('btnDownload');
    const downloadSuccess = document.getElementById('downloadSuccess');

    let activePlatform = null;
    let currentVideoData = null;

    // Platform Selection Logic
    function activateButton(platform) {
        platformBtns.forEach(btn => btn.classList.remove('active'));
        const btn = document.querySelector(`.platform-btn[data-platform="${platform}"]`);
        if (btn) {
            btn.classList.add('active');
            activePlatform = platform;
            errorMsg.style.display = 'none';
        }
    }

    function detectPlatform(url) {
        if (!url) return null;
        const lowerUrl = url.toLowerCase();
        if (lowerUrl.includes('tiktok.com') || lowerUrl.includes('vm.tiktok') || lowerUrl.includes('vt.tiktok')) {
            activateButton('tiktok');
            return 'tiktok';
        } else if (lowerUrl.includes('instagram.com')) {
            activateButton('instagram');
            return 'instagram';
        }
        return null;
    }

    // Event Listeners
    platformBtns.forEach(btn => {
        btn.addEventListener('click', (e) => {
            const platform = e.target.dataset.platform;
            activateButton(platform);

            // Re-validate input if exists
            if (urlInput.value) {
                validateInput();
            }
        });
    });

    // Handle Paste and Input
    let inputTimeout;
    urlInput.addEventListener('input', (e) => {
        clearTimeout(inputTimeout);
        const url = e.target.value.trim();

        if (url === '') {
            platformBtns.forEach(btn => btn.classList.remove('active'));
            activePlatform = null;
            errorMsg.style.display = 'none';
            btnAnalyze.disabled = false;
            return;
        }

        detectPlatform(url);

        // Debounce validation
        inputTimeout = setTimeout(validateInput, 300);
    });

    urlInput.addEventListener('paste', (e) => {
        // Allow a brief moment for the pasted value to populate
        setTimeout(() => {
            const url = urlInput.value.trim();
            detectPlatform(url);
            validateInput();
        }, 50);
    });

    function validateInput() {
        const url = urlInput.value.trim();
        if (!url) {
            showError('');
            return false;
        }

        // Basic URL regex
        const urlRegex = /^(https?:\/\/)?([\da-z\.-]+)\.([a-z\.]{2,6})([\/\w \.-]*)*\/?.*$/;
        if (!urlRegex.test(url)) {
            showError('Please enter a valid URL.');
            return false;
        }

        if (!activePlatform) {
            showError('Please select a platform or paste a valid TikTok/Instagram link.');
            return false;
        }

        // Platform specific validation
        if (activePlatform === 'tiktok' && !url.includes('tiktok.com')) {
            showError('The URL does not appear to be a valid TikTok link.');
            return false;
        }

        if (activePlatform === 'instagram' && !url.includes('instagram.com')) {
            showError('The URL does not appear to be a valid Instagram link.');
            return false;
        }

        showError('');
        return true;
    }

    function showError(msg) {
        if (msg) {
            errorMsg.textContent = msg;
            errorMsg.style.display = 'block';
            btnAnalyze.disabled = true;
        } else {
            errorMsg.style.display = 'none';
            btnAnalyze.disabled = false;
        }
    }

    // Analyze Action
    btnAnalyze.addEventListener('click', async () => {
        if (!validateInput()) return;

        const url = urlInput.value.trim();
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        setLoading(true);
        resultsSection.style.display = 'none';

        try {
            const formData = new FormData();
            formData.append('url', url);
            formData.append('platform', activePlatform);
            formData.append('csrf_token', csrfToken);

            const response = await fetch('api/download.php', {
                method: 'POST',
                body: formData
            });

            let data;
            try {
                data = await response.json();
            } catch (e) {
                throw new Error('Invalid response from server.');
            }

            if (data.success) {
                renderResults(data.data);
            } else {
                showError(data.error || 'Failed to analyze video.');
            }
        } catch (error) {
            showError('Network error. Please try again.');
        } finally {
            setLoading(false);
        }
    });

    function setLoading(isLoading) {
        if (isLoading) {
            btnAnalyze.textContent = 'ANALYZING...';
            btnAnalyze.disabled = true;
            urlInput.disabled = true;
            platformBtns.forEach(btn => btn.style.pointerEvents = 'none');
        } else {
            btnAnalyze.textContent = 'ANALYZE VIDEO';
            btnAnalyze.disabled = false;
            urlInput.disabled = false;
            platformBtns.forEach(btn => btn.style.pointerEvents = 'auto');
        }
    }

    function renderResults(data) {
        currentVideoData = data;

        // Populate info
        platformBadge.textContent = data.platform === 'tiktok' ? 'TikTok' : 'Instagram';
        videoTitle.textContent = data.title || 'Untitled Video';
        videoAuthor.textContent = data.author ? `@${data.author}` : '';

        const durationText = data.duration ? ` • ${data.duration}` : '';
        videoDuration.textContent = `${data.qualities[0].size || 'Unknown Size'}${durationText}`;

        // Populate video preview (using best quality)
        videoPreview.src = data.qualities[0].url;
        if (data.thumbnail) {
            videoPreview.poster = data.thumbnail;
        }
        videoPreview.load();

        // Populate dropdown
        qualitySelect.innerHTML = '';
        data.qualities.forEach((q, index) => {
            const option = document.createElement('option');
            option.value = index;
            option.textContent = `${q.label} ${q.size && q.size !== 'Unknown Size' ? `(${q.size})` : ''}`;
            qualitySelect.appendChild(option);
        });

        // Update download button text
        updateDownloadButtonText();

        resultsSection.style.display = 'block';

        // Scroll to results
        resultsSection.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }

    qualitySelect.addEventListener('change', updateDownloadButtonText);

    function updateDownloadButtonText() {
        const selectedIndex = qualitySelect.value;
        const quality = currentVideoData.qualities[selectedIndex];

        // Extract just the resolution part like 1080p from "Full HD (1080p)"
        const match = quality.label.match(/\((.*?)\)/);
        const resText = match ? match[1] : quality.label;

        btnDownload.textContent = `DOWNLOAD ${resText}`;

        // Update size info
        const durationText = currentVideoData.duration ? ` • ${currentVideoData.duration}` : '';
        videoDuration.textContent = `${quality.size || 'Unknown Size'}${durationText}`;
    }

    // Download Action (Direct download)
    btnDownload.addEventListener('click', () => {
        if (!currentVideoData) return;

        const selectedIndex = qualitySelect.value;
        const quality = currentVideoData.qualities[selectedIndex];

        downloadSuccess.style.display = 'none';
        btnDownload.textContent = 'DOWNLOADING...';
        btnDownload.disabled = true;

        // Force download mechanism without opening a new tab
        // Fetch the blob and trigger download
        fetch(quality.url)
            .then(response => response.blob())
            .then(blob => {
                const blobUrl = window.URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.style.display = 'none';
                a.href = blobUrl;
                // Generate a filename
                const sanitizeTitle = (currentVideoData.title || 'video').replace(/[^a-z0-9]/gi, '_').toLowerCase();
                a.download = `${currentVideoData.platform}_${sanitizeTitle}.mp4`;

                document.body.appendChild(a);
                a.click();

                // Cleanup
                setTimeout(() => {
                    document.body.removeChild(a);
                    window.URL.revokeObjectURL(blobUrl);

                    updateDownloadButtonText();
                    btnDownload.disabled = false;
                    downloadSuccess.textContent = 'Download started!';
                    downloadSuccess.style.display = 'block';
                }, 100);
            })
            .catch(error => {
                console.error("Download failed, falling back to direct link", error);
                // Fallback for CORS issues
                const a = document.createElement('a');
                a.href = quality.url;
                a.target = "_blank";
                a.download = `video.mp4`;
                document.body.appendChild(a);
                a.click();
                document.body.removeChild(a);

                updateDownloadButtonText();
                btnDownload.disabled = false;
            });
    });
});
