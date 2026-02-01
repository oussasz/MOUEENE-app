<?php
/**
 * Router script for PHP built-in server
 * Run with: php -S localhost:8000 router.php
 */

$uri = $_SERVER['REQUEST_URI'];
$path = parse_url($uri, PHP_URL_PATH);

// Favicon fallback for local dev
if ($path === '/favicon.ico') {
    $fallback = __DIR__ . '/logo.png';
    if (file_exists($fallback)) {
        header('Content-Type: image/png');
        readfile($fallback);
        exit;
    }
}

// Serve static files directly
if (preg_match('/\.(?:html|css|js|png|jpg|jpeg|gif|ico|svg|woff|woff2|ttf|eot)$/', $path)) {
    $filePath = __DIR__ . $path;
    if (file_exists($filePath)) {
        return false; // Let PHP built-in server handle static files
    }
}

// Route API requests to the backend
if (strpos($path, '/backend/api') === 0) {
    // Include the API entry point
    require_once __DIR__ . '/backend/api/index.php';
    exit;
}

// For root path, serve index.html
if ($path === '/' || $path === '') {
    include __DIR__ . '/index.html';
    exit;
}

// Try to serve the file directly if it exists
$filePath = __DIR__ . $path;
if (file_exists($filePath)) {
    if (is_dir($filePath)) {
        // Check for index.html in directory
        $indexFile = rtrim($filePath, '/') . '/index.html';
        if (file_exists($indexFile)) {
            include $indexFile;
            exit;
        }
    }
    return false; // Let PHP serve the file
}

// Check for .html extension
$htmlPath = $filePath . '.html';
if (file_exists($htmlPath)) {
    include $htmlPath;
    exit;
}

// 404 for everything else
http_response_code(404);
if (file_exists(__DIR__ . '/404.html')) {
    include __DIR__ . '/404.html';
} else {
    echo "404 Not Found";
}
