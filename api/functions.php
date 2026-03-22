<?php
/**
 * Helper Functions
 */

require_once 'config.php';

/**
 * Validate URL based on platform supported domains
 */
function isValidUrl($url, $platform) {
    global $SUPPORTED_DOMAINS;

    if (!filter_var($url, FILTER_VALIDATE_URL)) {
        return false;
    }

    $parsedUrl = parse_url($url);
    if (!isset($parsedUrl['host'])) {
        return false;
    }

    $host = strtolower($parsedUrl['host']);
    $domains = $SUPPORTED_DOMAINS[$platform] ?? [];

    foreach ($domains as $domain) {
        if ($host === $domain || substr($host, -strlen('.' . $domain)) === '.' . $domain) {
            return true;
        }
    }

    return false;
}

/**
 * Basic cURL wrapper for API calls
 */
function makeApiRequest($url, $headers = [], $method = 'GET', $data = null) {
    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, CURL_TIMEOUT);
    curl_setopt($ch, CURLOPT_ENCODING, ""); // Handle gzip
    curl_setopt($ch, CURLOPT_USERAGENT, CURL_USER_AGENT);

    if (!empty($headers)) {
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    }

    if ($method === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        if ($data) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        }
    }

    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);

    $response = curl_exec($ch);
    $error = curl_error($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    curl_close($ch);

    if ($error) {
        return ['success' => false, 'error' => $error];
    }

    return [
        'success' => true,
        'http_code' => $httpCode,
        'body' => $response
    ];
}

/**
 * Rate Limiting Check
 */
function checkRateLimit() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    $now = time();
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $sessionKey = 'rate_limit_' . md5($ip);

    if (!isset($_SESSION[$sessionKey])) {
        $_SESSION[$sessionKey] = [];
    }

    // Filter out old requests
    $_SESSION[$sessionKey] = array_filter($_SESSION[$sessionKey], function($timestamp) use ($now) {
        return ($now - $timestamp) < RATE_LIMIT_TIME_WINDOW;
    });

    if (count($_SESSION[$sessionKey]) >= RATE_LIMIT_MAX_REQUESTS) {
        return false; // Rate limit exceeded
    }

    $_SESSION[$sessionKey][] = $now;
    return true;
}

/**
 * Generate CSRF Token
 */
function generateCsrfToken() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

/**
 * Validate CSRF Token
 */
function validateCsrfToken($token) {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    if (!empty($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token)) {
        return true;
    }

    return false;
}

/**
 * Return JSON Response
 */
function jsonResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

/**
 * Format duration helper (seconds to MM:SS)
 */
function formatDuration($seconds) {
    if (!$seconds || !is_numeric($seconds)) return "00:00";
    $m = floor($seconds / 60);
    $s = $seconds % 60;
    return sprintf("%02d:%02d", $m, $s);
}

/**
 * Sort qualities from highest to lowest
 */
function sortQualities($qualities) {
    $order = ['4K (2160p)', 'Full HD (1080p)', 'HD (720p)', 'SD (480p)', 'Original', 'Watermarked'];
    usort($qualities, function($a, $b) use ($order) {
        $posA = array_search($a['label'], $order);
        $posB = array_search($b['label'], $order);
        $posA = $posA === false ? 99 : $posA;
        $posB = $posB === false ? 99 : $posB;
        return $posA - $posB;
    });
    return $qualities;
}
