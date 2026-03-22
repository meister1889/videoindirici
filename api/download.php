<?php
/**
 * Main Download Handler
 * Receives POST requests, validates input, and delegates to platform specific APIs
 */

require_once 'config.php';
require_once 'functions.php';
require_once 'tiktok.php';
require_once 'instagram.php';

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['success' => false, 'error' => 'Method not allowed'], 405);
}

// 1. Rate Limiting Check
if (!checkRateLimit()) {
    jsonResponse(['success' => false, 'error' => ERR_RATE_LIMIT], 429);
}

// 2. Extract and Sanitize Input
$csrfToken = $_POST['csrf_token'] ?? '';
if (!validateCsrfToken($csrfToken)) {
    jsonResponse(['success' => false, 'error' => 'Invalid CSRF token.'], 403);
}

$url = filter_input(INPUT_POST, 'url', FILTER_SANITIZE_URL);
$platform = $_POST['platform'] ?? '';
$platform = htmlspecialchars($platform, ENT_QUOTES, 'UTF-8');

// 3. Validation
if (empty($url) || !filter_var($url, FILTER_VALIDATE_URL)) {
    jsonResponse(['success' => false, 'error' => ERR_INVALID_URL]);
}

if (empty($platform) || !in_array($platform, ['tiktok', 'instagram'])) {
    jsonResponse(['success' => false, 'error' => ERR_INVALID_PLATFORM]);
}

if (!isValidUrl($url, $platform)) {
    jsonResponse(['success' => false, 'error' => "URL does not match the selected platform ($platform)."]);
}

// 4. Delegate to Platform Specific Logic
if ($platform === 'tiktok') {
    $result = getTikTokVideo($url);
} else {
    $result = getInstagramVideo($url);
}

// 5. Return the JSON result
jsonResponse($result);
