<?php
/**
 * Uploads API Endpoints (Cloudinary signing)
 *
 * Notes:
 * - Never expose Cloudinary API secret to the client.
 * - Client requests a signature, then uploads directly to Cloudinary.
 */

$action = $parts[2] ?? '';
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

Auth::requireAuth();
$authUser = Auth::user();

if (!in_array($authUser['user_type'] ?? '', ['provider', 'admin'], true)) {
    Response::error('Access denied', 403);
}

switch ($action) {
    case 'cloudinary-signature':
        if ($method === 'GET') {
            handleCloudinarySignature($authUser);
        } else {
            Response::error('Method not allowed', 405);
        }
        break;
    default:
        Response::error('Invalid uploads endpoint', 404);
}

function cloudinarySign(array $params, string $apiSecret): string {
    ksort($params);
    $pairs = [];
    foreach ($params as $k => $v) {
        if ($v === null || $v === '') continue;
        $pairs[] = $k . '=' . $v;
    }
    $toSign = implode('&', $pairs) . $apiSecret;
    return sha1($toSign);
}

function handleCloudinarySignature(array $authUser): void {
    $cloudName = getenv('CLOUDINARY_CLOUD_NAME') ?: '';
    $apiKey = getenv('CLOUDINARY_API_KEY') ?: '';
    $apiSecret = getenv('CLOUDINARY_API_SECRET') ?: '';

    if ($cloudName === '' || $apiKey === '' || $apiSecret === '') {
        Response::error('Cloudinary is not configured on the server', 500, [
            'missing' => [
                $cloudName === '' ? 'CLOUDINARY_CLOUD_NAME' : null,
                $apiKey === '' ? 'CLOUDINARY_API_KEY' : null,
                $apiSecret === '' ? 'CLOUDINARY_API_SECRET' : null,
            ],
        ]);
    }

    // Optional safe hints from client
    $resource = strtolower(trim($_GET['resource_type'] ?? 'image'));
    if (!in_array($resource, ['image'], true)) {
        $resource = 'image';
    }

    $folder = trim($_GET['folder'] ?? 'moueene');
    // Force folder under our namespace
    $folder = preg_replace('~[^a-zA-Z0-9_\-/]~', '', $folder);
    if ($folder === '' || strpos($folder, 'moueene') !== 0) {
        $folder = 'moueene';
    }

    $timestamp = time();

    // Cloudinary signature is over upload params (not including file/api_key/signature)
    $paramsToSign = [
        'folder' => $folder,
        'timestamp' => $timestamp,
    ];

    $signature = cloudinarySign($paramsToSign, $apiSecret);

    Response::success([
        'cloud_name' => $cloudName,
        'api_key' => $apiKey,
        'timestamp' => $timestamp,
        'signature' => $signature,
        'folder' => $folder,
        'resource_type' => $resource,
    ]);
}
