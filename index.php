<?php
require_once 'api/functions.php';
$csrfToken = generateCsrfToken();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Download TikTok and Instagram videos in HD quality without watermark.">
    <meta name="csrf-token" content="<?php echo htmlspecialchars($csrfToken); ?>">
    <title>Video Downloader</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>VIDEO DOWNLOADER</h1>
            <p class="subtitle">Download videos in HD quality</p>
        </header>

        <main>
            <div class="platform-selector">
                <button class="platform-btn" data-platform="tiktok">TikTok</button>
                <button class="platform-btn" data-platform="instagram">Instagram</button>
            </div>

            <div class="input-area">
                <div class="input-wrapper">
                    <input type="text" id="urlInput" class="url-input" placeholder="Paste video link here..." autocomplete="off">
                </div>
                <div id="errorMsg" class="error-msg"></div>
                <button id="btnAnalyze" class="btn-primary">ANALYZE VIDEO</button>
            </div>

            <div id="resultsSection" class="results-section">
                <div class="video-container">
                    <video id="videoPreview" controls muted preload="none">
                        Your browser does not support HTML video.
                    </video>
                </div>

                <div class="video-info">
                    <span id="platformBadge" class="platform-badge"></span>
                    <h2 id="videoTitle" class="video-title"></h2>
                    <div id="videoAuthor" class="video-meta"></div>
                    <div id="videoDuration" class="video-duration"></div>
                </div>

                <div class="quality-selector">
                    <label for="qualitySelect">Select Quality:</label>
                    <div class="custom-select">
                        <select id="qualitySelect">
                            <!-- Options injected by JS -->
                        </select>
                    </div>
                </div>

                <button id="btnDownload" class="btn-download">DOWNLOAD</button>
                <div id="downloadSuccess" class="download-success"></div>
            </div>
        </main>
    </div>

    <footer>
        <p>Supports TikTok & Instagram • No Watermarks • HD Quality</p>
    </footer>

    <script src="assets/js/app.js"></script>
</body>
</html>
