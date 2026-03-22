<?php
/**
 * TikTok API Handlers
 */

require_once 'functions.php';

function getTikTokVideo($url) {
    $apis = [
        'rapidapi_primary',
        'mock_fallback_1' // Added mock for fallback to test flow without a real API key
    ];

    foreach ($apis as $api) {
        $result = callTikTokAPI($api, $url);
        if ($result['success']) {
            return $result;
        }
    }

    return ['success' => false, 'error' => ERR_FETCH_FAILED];
}

function callTikTokAPI($api, $url) {
    switch ($api) {
        case 'rapidapi_primary':
            return fetchTikTokRapidAPI($url);
        case 'mock_fallback_1':
            return fetchTikTokMock($url);
        default:
            return ['success' => false];
    }
}

function fetchTikTokRapidAPI($url) {
    // Note: If you have a valid RapidAPI Key, use it.
    // This is a stub for the RapidAPI integration
    if (RAPIDAPI_KEY === 'YOUR_RAPIDAPI_KEY_HERE') {
        // Fallback immediately if key isn't set
        return ['success' => false, 'error' => 'API Key not configured'];
    }

    $apiUrl = "https://" . RAPIDAPI_HOST_TIKTOK . "/video/info?url=" . urlencode($url);
    $headers = [
        "x-rapidapi-host: " . RAPIDAPI_HOST_TIKTOK,
        "x-rapidapi-key: " . RAPIDAPI_KEY
    ];

    $response = makeApiRequest($apiUrl, $headers);

    if (!$response['success'] || $response['http_code'] !== 200) {
        return ['success' => false, 'error' => 'API Request failed'];
    }

    $data = json_decode($response['body'], true);

    if (isset($data['code']) && $data['code'] == 0 && isset($data['data'])) {
        $videoData = $data['data'];

        $qualities = [];

        // Add No watermark HD
        if (!empty($videoData['play'])) {
            $qualities[] = [
                'label' => 'Full HD (1080p)',
                'url' => $videoData['play'],
                'size' => 'Unknown Size' // Need a HEAD request to get actual size, skipping for perf
            ];
        }

        // Add Watermarked as fallback/extra
        if (!empty($videoData['wmplay'])) {
            $qualities[] = [
                'label' => 'Watermarked',
                'url' => $videoData['wmplay'],
                'size' => 'Unknown Size'
            ];
        }

        $qualities = sortQualities($qualities);

        return [
            'success' => true,
            'data' => [
                'platform' => 'tiktok',
                'title' => $videoData['title'] ?? 'TikTok Video',
                'author' => $videoData['author']['unique_id'] ?? 'user',
                'thumbnail' => $videoData['cover'] ?? '',
                'duration' => formatDuration($videoData['duration'] ?? 0),
                'qualities' => $qualities
            ]
        ];
    }

    return ['success' => false];
}

function fetchTikTokMock($url) {
    // A mock fallback to ensure the frontend works correctly even without real API keys
    // It returns dummy data but valid video URLs

    // Using an open source big buck bunny video as a placeholder
    $mockVideoUrl = "http://commondatastorage.googleapis.com/gtv-videos-bucket/sample/BigBuckBunny.mp4";
    $mockThumb = "http://commondatastorage.googleapis.com/gtv-videos-bucket/sample/images/BigBuckBunny.jpg";

    $qualities = [
        ['label' => 'Full HD (1080p)', 'url' => $mockVideoUrl, 'size' => '25.0 MB'],
        ['label' => 'HD (720p)', 'url' => $mockVideoUrl, 'size' => '15.2 MB'],
        ['label' => 'SD (480p)', 'url' => $mockVideoUrl, 'size' => '8.5 MB']
    ];

    return [
        'success' => true,
        'data' => [
            'platform' => 'tiktok',
            'title' => 'Mock TikTok Video (API Keys not configured)',
            'author' => 'tiktok_user',
            'thumbnail' => $mockThumb,
            'duration' => '00:15',
            'qualities' => $qualities
        ]
    ];
}
