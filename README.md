# TikTok & Instagram Video Downloader

A professional, production-ready video downloader website implemented in PHP with a modern, brutalist monochrome design.

## Features

- **Multi-Platform Support**: Downloads videos from TikTok and Instagram.
- **Flawless API Integration**: Features a multi-fallback API system for maximum reliability.
- **Sleek Monochrome Design**: Built with a strict black, white, and dark gray color palette.
- **Robust Error Handling**: Handles network timeouts, invalid URLs, and API rate limits gracefully.
- **Security Features**: Input sanitization, CSRF protection, rate limiting, and domain validation.

## Architecture

The project is structured with a clean separation of concerns:

- `index.php`: Main interface and layout.
- `api/download.php`: Main download handler and API router.
- `api/tiktok.php` / `api/instagram.php`: Platform-specific API logic and fallbacks.
- `api/functions.php`: Helpers for URL validation, cURL wrapper, and CSRF protection.
- `api/config.php`: Central configuration for API keys, timeouts, and rate limits.
- `assets/css/style.css`: Clean, monochrome brutalist styling.
- `assets/js/app.js`: Frontend logic for platform detection, AJAX implementation, and direct downloads.

## Setup Instructions

1. **Requirements**:
   - PHP 7.4+ with `cURL` enabled.
   - A web server like Apache or Nginx.

2. **Configuration**:
   - Open `api/config.php`.
   - Replace the placeholder API keys with your actual keys.
   - Adjust `CURL_TIMEOUT`, `RATE_LIMIT_MAX_REQUESTS`, and `RATE_LIMIT_TIME_WINDOW` as needed.

3. **Deployment**:
   - Upload the entire directory to your web server's public directory.
   - Ensure the `api` and `assets` folders have the appropriate read permissions.
   - Access `index.php` via your web browser to start using the downloader.

## API Integration Note

Currently, `api/tiktok.php` and `api/instagram.php` are configured with a `mock_fallback_1` which returns static video data if valid API keys are not provided. This ensures that the application is fully testable out-of-the-box. When deploying to production, ensure you supply real API credentials in `api/config.php` and the real API logic will seamlessly take over.
