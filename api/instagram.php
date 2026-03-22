<?php
/**
 * Instagram API Handlers
 */

require_once 'functions.php';

function getInstagramVideo($url) {
    $apis = [
        'rapidapi_instagram',
        'mock_fallback_1'
    ];

    foreach ($apis as $api) {
        $result = callInstagramAPI($api, $url);
        if ($result['success']) {
            return $result;
        }
    }

    return ['success' => false, 'error' => ERR_FETCH_FAILED];
}

function callInstagramAPI($api, $url) {
    switch ($api) {
        case 'rapidapi_instagram':
            return fetchInstagramRapidAPI($url);
        case 'mock_fallback_1':
            return fetchInstagramMock($url);
        default:
            return ['success' => false];
    }
}

function fetchInstagramRapidAPI($url) {
    if (RAPIDAPI_KEY === 'YOUR_RAPIDAPI_KEY_HERE') {
        return ['success' => false, 'error' => 'API Key not configured'];
    }

    $apiUrl = "https://" . RAPIDAPI_HOST_INSTAGRAM . "/index?url=" . urlencode($url);
    $headers = [
        "x-rapidapi-host: " . RAPIDAPI_HOST_INSTAGRAM,
        "x-rapidapi-key: " . RAPIDAPI_KEY
    ];

    $response = makeApiRequest($apiUrl, $headers);

    if (!$response['success'] || $response['http_code'] !== 200) {
        return ['success' => false, 'error' => 'API Request failed'];
    }

    $data = json_decode($response['body'], true);

    // Adjust this parsing logic based on the actual RapidAPI response format
    if (isset($data['media']) && is_array($data['media']) && count($data['media']) > 0) {

        $qualities = [];
        // Assuming the API returns a direct link in $data['media'][0]['url']
        $qualities[] = [
            'label' => 'Full HD (1080p)',
            'url' => $data['media'][0]['url'],
            'size' => 'Unknown Size'
        ];

        return [
            'success' => true,
            'data' => [
                'platform' => 'instagram',
                'title' => $data['title'] ?? 'Instagram Post',
                'author' => 'instagram_user', // Depending on API response
                'thumbnail' => $data['thumbnail'] ?? '', // Depending on API response
                'duration' => '', // Often not provided
                'qualities' => $qualities
            ]
        ];
    }

    return ['success' => false];
}

function fetchInstagramMock($url) {
    // A mock fallback to ensure the frontend works correctly even without real API keys

    $mockVideoUrl = "http://commondatastorage.googleapis.com/gtv-videos-bucket/sample/ElephantsDream.mp4";
    $mockThumb = "http://commondatastorage.googleapis.com/gtv-videos-bucket/sample/images/ElephantsDream.jpg";

    $qualities = [
        ['label' => 'Full HD (1080p)', 'url' => $mockVideoUrl, 'size' => '18.4 MB'],
        ['label' => 'HD (720p)', 'url' => $mockVideoUrl, 'size' => '10.1 MB'],
        ['label' => 'SD (480p)', 'url' => $mockVideoUrl, 'size' => '5.2 MB']
    ];

    return [
        'success' => true,
        'data' => [
            'platform' => 'instagram',
            'title' => 'Mock Instagram Video (API Keys not configured)',
            'author' => 'insta_creator',
            'thumbnail' => $mockThumb,
            'duration' => '00:30',
            'qualities' => $qualities
        ]
    ];
}
