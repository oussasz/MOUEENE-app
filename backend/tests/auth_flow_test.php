<?php
/**
 * Minimal auth flow tests (no PHPUnit)
 *
 * Runs a few HTTP requests against a local PHP server and asserts:
 * - Provider registration returns provider_id
 * - User registration returns user_id
 * - Provider login works against providers table
 * - User login works against users table
 *
 * Usage:
 *   php backend/tests/auth_flow_test.php
 */

$baseDir = realpath(__DIR__ . '/../../');
if ($baseDir === false) {
    fwrite(STDERR, "Failed to resolve project root.\n");
    exit(1);
}

$host = '127.0.0.1';
$port = 8011;
$baseUrl = "http://{$host}:{$port}";

$router = $baseDir . '/router.php';
if (!file_exists($router)) {
    fwrite(STDERR, "router.php not found at {$router}\n");
    exit(1);
}

function httpJson(string $url, array $payload): array {
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
        CURLOPT_POSTFIELDS => json_encode($payload),
        CURLOPT_TIMEOUT => 15,
    ]);

    $body = curl_exec($ch);
    $err = curl_error($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($body === false) {
        return ['ok' => false, 'code' => $code, 'error' => $err, 'json' => null];
    }

    $json = json_decode($body, true);
    return ['ok' => true, 'code' => $code, 'error' => $err, 'json' => $json, 'raw' => $body];
}

function assertTrue(bool $cond, string $message): void {
    if (!$cond) {
        fwrite(STDERR, "FAIL: {$message}\n");
        exit(1);
    }
    echo "PASS: {$message}\n";
}

// Start server
$cmd = sprintf(
    'php -S %s:%d %s > /dev/null 2>&1 & echo $!',
    escapeshellarg($host),
    $port,
    escapeshellarg($router)
);

$pid = trim(shell_exec('cd ' . escapeshellarg($baseDir) . ' && ' . $cmd) ?? '');
assertTrue($pid !== '' && ctype_digit($pid), "Started PHP server (pid={$pid})");

// Ensure we stop server
register_shutdown_function(function () use ($pid) {
    if ($pid !== '' && ctype_digit($pid)) {
        @posix_kill((int)$pid, SIGTERM);
    }
});

// Small wait
usleep(400000);

$uniq = bin2hex(random_bytes(4));

$providerEmail = "provider_{$uniq}@example.com";
$userEmail = "user_{$uniq}@example.com";
$password = 'Test12345';

// Provider registration
$resp = httpJson("{$baseUrl}/backend/api/v1/auth/register-provider", [
    'email' => $providerEmail,
    'password' => $password,
    'first_name' => 'Test',
    'last_name' => 'Provider',
    'phone' => '+213555123456',
    'address' => '123 Test St',
    'city' => 'Algiers',
]);
assertTrue($resp['ok'] === true, 'Provider register request completed');
assertTrue(isset($resp['json']['success']) && $resp['json']['success'] === true, 'Provider register success=true');
assertTrue(isset($resp['json']['data']['provider_id']), 'Provider register returns provider_id');

// User registration
$resp = httpJson("{$baseUrl}/backend/api/v1/auth/register-user", [
    'email' => $userEmail,
    'password' => $password,
    'first_name' => 'Test',
    'last_name' => 'User',
    'phone' => '+213555000000',
    'address' => '456 User Rd',
    'city' => 'Bejaia',
]);
assertTrue($resp['ok'] === true, 'User register request completed');
assertTrue(isset($resp['json']['success']) && $resp['json']['success'] === true, 'User register success=true');
assertTrue(isset($resp['json']['data']['user_id']), 'User register returns user_id');

// Provider login
$resp = httpJson("{$baseUrl}/backend/api/v1/auth/login-provider", [
    'email' => $providerEmail,
    'password' => $password,
]);
assertTrue($resp['ok'] === true, 'Provider login request completed');
assertTrue(isset($resp['json']['success']) && $resp['json']['success'] === true, 'Provider login success=true');

// User login
$resp = httpJson("{$baseUrl}/backend/api/v1/auth/login-user", [
    'email' => $userEmail,
    'password' => $password,
]);
assertTrue($resp['ok'] === true, 'User login request completed');
assertTrue(isset($resp['json']['success']) && $resp['json']['success'] === true, 'User login success=true');

echo "\nAll auth flow tests passed.\n";
