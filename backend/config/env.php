<?php
/**
 * Minimal .env loader (no external dependencies).
 *
 * - Reads backend/config/.env for local development.
 * - Does NOT override existing environment variables.
 * - Supports comments and optional single/double quotes.
 */

class Env {
    public static function load(string $envFilePath): void {
        if (!is_file($envFilePath) || !is_readable($envFilePath)) {
            return;
        }

        $lines = file($envFilePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if (!is_array($lines)) {
            return;
        }

        foreach ($lines as $line) {
            $line = trim((string)$line);
            if ($line === '' || strpos($line, '#') === 0) {
                continue;
            }

            $parts = explode('=', $line, 2);
            if (count($parts) !== 2) {
                continue;
            }

            $key = trim($parts[0]);
            $value = trim($parts[1]);

            if ($key === '') {
                continue;
            }

            // Strip optional surrounding quotes
            if (
                (strlen($value) >= 2) &&
                (($value[0] === '"' && $value[strlen($value) - 1] === '"') ||
                 ($value[0] === "'" && $value[strlen($value) - 1] === "'"))
            ) {
                $value = substr($value, 1, -1);
            }

            // Don't override if already set in process env
            $existing = getenv($key);
            if (is_string($existing) && $existing !== '') {
                continue;
            }

            // Set process env + superglobals for convenience
            @putenv($key . '=' . $value);
            $_ENV[$key] = $value;
            $_SERVER[$key] = $value;
        }
    }
}
