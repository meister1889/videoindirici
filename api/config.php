<?php
/**
 * Configuration File
 * Contains API keys, timeout settings, rate limits, and other constants.
 */

// General Settings
define('APP_NAME', 'Video Downloader');
define('APP_VERSION', '1.0.0');

// cURL Settings
define('CURL_TIMEOUT', 30); // 30 seconds max
define('CURL_USER_AGENT', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36');

// Rate Limiting
define('RATE_LIMIT_MAX_REQUESTS', 10); // max 10 requests
define('RATE_LIMIT_TIME_WINDOW', 60); // per minute (60 seconds)

// API Keys (Replace these with your actual keys in production)
// RapidAPI TikTok Downloader
define('RAPIDAPI_KEY', 'YOUR_RAPIDAPI_KEY_HERE');
define('RAPIDAPI_HOST_TIKTOK', 'tiktok-video-no-watermark2.p.rapidapi.com');

// RapidAPI Instagram Downloader
define('RAPIDAPI_HOST_INSTAGRAM', 'instagram-downloader-download-instagram-videos-stories.p.rapidapi.com');

// Error Messages
define('ERR_INVALID_URL', 'Please enter a valid video URL.');
define('ERR_INVALID_PLATFORM', 'Invalid platform selected.');
define('ERR_NETWORK_TIMEOUT', 'Request timed out. Please try again.');
define('ERR_RATE_LIMIT', 'Too many requests. Please wait a moment.');
define('ERR_FETCH_FAILED', 'Unable to fetch video. Please try again.');

// Supported Domains
$SUPPORTED_DOMAINS = [
    'tiktok' => ['tiktok.com', 'vm.tiktok.com', 'm.tiktok.com', 'vt.tiktok.com'],
    'instagram' => ['instagram.com', 'www.instagram.com']
];
