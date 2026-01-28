<?php
/**
 * Debug file for cPanel - DELETE AFTER TESTING
 */
header('Content-Type: application/json');

$requestUri = $_SERVER['REQUEST_URI'] ?? '';
$path = parse_url($requestUri, PHP_URL_PATH);
$path = trim($path ?? '', '/');

// Test providers path extraction
$afterProviders = '';
$providersPos = strpos($path, '/providers');
if ($providersPos !== false) {
    $afterProviders = substr($path, $providersPos + strlen('/providers'));
} else {
    if (preg_match('#providers/?(.*)$#', $path, $matches)) {
        $afterProviders = $matches[1] ?? '';
    }
}

$afterProviders = trim($afterProviders, '/');
$segments = $afterProviders === '' ? [] : explode('/', $afterProviders);

echo json_encode([
    'REQUEST_URI' => $requestUri,
    'SCRIPT_NAME' => $_SERVER['SCRIPT_NAME'] ?? '',
    'PHP_SELF' => $_SERVER['PHP_SELF'] ?? '',
    'PATH_INFO' => $_SERVER['PATH_INFO'] ?? 'not set',
    'QUERY_STRING' => $_SERVER['QUERY_STRING'] ?? '',
    'parsed_path' => $path,
    'providersPos' => $providersPos,
    'afterProviders' => $afterProviders,
    'segments' => $segments,
    'action_would_be' => $segments[0] ?? '(empty)',
    'document_root' => $_SERVER['DOCUMENT_ROOT'] ?? '',
    'script_filename' => $_SERVER['SCRIPT_FILENAME'] ?? ''
], JSON_PRETTY_PRINT);
