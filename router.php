<?php
/**
 * Router script for PHP built-in server
 * Run with: php -S localhost:8000 router.php
 */

$uri = $_SERVER['REQUEST_URI'] ?? '/';
$path = parse_url($uri, PHP_URL_PATH) ?: '/';

function normalize_path(string $path): string {
    $path = '/' . ltrim($path, '/');
    // Remove duplicate slashes
    $path = preg_replace('#/+#', '/', $path);
    // Remove trailing slash except root
    if ($path !== '/' && str_ends_with($path, '/')) {
        $path = rtrim($path, '/');
    }
    return $path;
}

function inject_base_href(string $html): string {
    if (stripos($html, '<base ') !== false) {
        return $html;
    }
    // Insert <base href="/"> right after <head>
    return preg_replace(
        '/<head(\s[^>]*)?>/i',
        "$0\n    <base href=\"/\" />",
        $html,
        1
    ) ?? $html;
}

function render_html_file(string $absFilePath): void {
    if (!is_file($absFilePath)) {
        http_response_code(404);
        echo '404 Not Found';
        return;
    }
    header('Content-Type: text/html; charset=UTF-8');
    $html = file_get_contents($absFilePath);
    if ($html === false) {
        http_response_code(500);
        echo '500 Server Error';
        return;
    }
    echo inject_base_href($html);
}

$path = normalize_path($path);

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
    render_html_file(__DIR__ . '/index.html');
    exit;
}

// Pretty URL routes
$routes = [
    // Public
    '/services' => __DIR__ . '/pages/services.html',
    '/providers' => __DIR__ . '/pages/providers.html',
    '/service-detail' => __DIR__ . '/pages/service-detail.html',
    '/about' => __DIR__ . '/pages/about.html',
    '/contact' => __DIR__ . '/pages/contact.html',
    '/faq' => __DIR__ . '/pages/faq.html',
    '/help' => __DIR__ . '/pages/help.html',
    '/terms' => __DIR__ . '/pages/terms.html',
    '/privacy' => __DIR__ . '/pages/privacy.html',

    // Auth
    '/login' => __DIR__ . '/pages/login.html',
    '/register' => __DIR__ . '/pages/register.html',
    '/forgot-password' => __DIR__ . '/pages/forgot-password.html',

    // Customer
    '/dashboard' => __DIR__ . '/pages/dashboard.html',
    '/bookings' => __DIR__ . '/pages/bookings.html',
    '/messages' => __DIR__ . '/pages/messages.html',
    '/favorites' => __DIR__ . '/pages/favorites.html',
    '/profile' => __DIR__ . '/pages/profile.html',
    '/profile/edit' => __DIR__ . '/pages/profile-edit.html',
    '/settings' => __DIR__ . '/pages/settings.html',
    '/booking-confirmation' => __DIR__ . '/pages/booking-confirmation.html',

    // Provider
    '/provider/dashboard' => __DIR__ . '/pages/provider-dashboard.html',
    '/provider/bookings' => __DIR__ . '/pages/provider-bookings.html',
    '/provider/messages' => __DIR__ . '/pages/provider-messages.html',
    '/provider/profile' => __DIR__ . '/pages/provider-profile.html',
    '/provider/reviews' => __DIR__ . '/pages/provider-reviews.html',
    '/provider/services' => __DIR__ . '/pages/provider-services.html',
    '/provider/settings' => __DIR__ . '/pages/provider-settings.html',
    '/provider/earnings' => __DIR__ . '/pages/provider-earnings.html',
    '/provider/verification' => __DIR__ . '/pages/provider-verification.html',

    // Admin
    '/admin' => __DIR__ . '/pages/admin/dashboard.html',
    '/admin/login' => __DIR__ . '/pages/admin/login.html',
    '/admin/dashboard' => __DIR__ . '/pages/admin/dashboard.html',
    '/admin/providers' => __DIR__ . '/pages/admin/providers.html',
    '/admin/users' => __DIR__ . '/pages/admin/users.html',
    '/admin/bookings' => __DIR__ . '/pages/admin/bookings.html',
    '/admin/services' => __DIR__ . '/pages/admin/services.html',
    '/admin/verifications' => __DIR__ . '/pages/admin/verifications.html',
    '/admin/documents' => __DIR__ . '/pages/admin/documents.html',
    '/admin/analytics' => __DIR__ . '/pages/admin/analytics.html',
    '/admin/transactions' => __DIR__ . '/pages/admin/transactions.html',
];

if (isset($routes[$path]) && file_exists($routes[$path])) {
    render_html_file($routes[$path]);
    exit;
}

// Generic fallbacks:
// 1) Map /admin/<page> to pages/admin/<page>.html
if (preg_match('#^/admin/([a-z0-9\-]+)$#i', $path, $m)) {
    $candidate = __DIR__ . '/pages/admin/' . $m[1] . '.html';
    if (file_exists($candidate)) {
        render_html_file($candidate);
        exit;
    }
}

// 2) Map /<page> to pages/<page>.html
if (preg_match('#^/([a-z0-9\-]+)$#i', $path, $m)) {
    $candidate = __DIR__ . '/pages/' . $m[1] . '.html';
    if (file_exists($candidate)) {
        render_html_file($candidate);
        exit;
    }
}

// Try to serve the file directly if it exists
$filePath = __DIR__ . $path;
if (file_exists($filePath)) {
    if (is_dir($filePath)) {
        // Check for index.html in directory
        $indexFile = rtrim($filePath, '/') . '/index.html';
        if (file_exists($indexFile)) {
            render_html_file($indexFile);
            exit;
        }
    }
    return false; // Let PHP serve the file
}

// Check for .html extension
$htmlPath = $filePath . '.html';
if (file_exists($htmlPath)) {
    render_html_file($htmlPath);
    exit;
}

// Check under /pages for legacy direct paths
$pagesPath = __DIR__ . '/pages' . $path;
if (file_exists($pagesPath) && is_file($pagesPath) && str_ends_with(strtolower($pagesPath), '.html')) {
    render_html_file($pagesPath);
    exit;
}
$pagesHtmlPath = __DIR__ . '/pages' . $path . '.html';
if (file_exists($pagesHtmlPath)) {
    render_html_file($pagesHtmlPath);
    exit;
}

// 404 for everything else
http_response_code(404);
if (file_exists(__DIR__ . '/404.html')) {
    render_html_file(__DIR__ . '/404.html');
} else {
    echo "404 Not Found";
}
