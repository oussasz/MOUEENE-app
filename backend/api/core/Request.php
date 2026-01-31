<?php

class Request {
    public string $method;
    public string $path;
    public array $segments;
    public array $query;
    public array $json;

    public function __construct(string $method, string $path, array $segments, array $query, array $json) {
        $this->method = strtoupper($method);
        $this->path = $path;
        $this->segments = $segments;
        $this->query = $query;
        $this->json = $json;
    }

    public static function fromGlobals(): self {
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
        $path = trim($path ?? '', '/');

        // Normalize path to start after /api/ segment if present
        if (strpos($path, 'backend/api/') === 0) {
            $path = substr($path, strlen('backend/api/'));
        } elseif (strpos($path, 'api/') === 0) {
            $path = substr($path, strlen('api/'));
        } else {
            $apiPos = strpos($path, '/api/');
            if ($apiPos !== false) {
                $path = substr($path, $apiPos + 5);
            }
        }

        $segments = array_values(array_filter(explode('/', $path), 'strlen'));

        // Remove any leading backend/api segments if still present
        while (!empty($segments) && in_array($segments[0], ['backend', 'api'], true)) {
            array_shift($segments);
        }

        // If first segment isn't a version (v1, v2...), default to v1
        if (!empty($segments[0]) && !preg_match('/^v\d+$/', $segments[0])) {
            array_unshift($segments, 'v1');
        }

        $json = json_decode(file_get_contents('php://input'), true);
        if (!is_array($json)) {
            $json = [];
        }

        return new self($method, $path, $segments, $_GET ?? [], $json);
    }

    public function version(): string {
        return $this->segments[0] ?? 'v1';
    }

    public function resource(): string {
        return $this->segments[1] ?? '';
    }

    public function action(): string {
        return $this->segments[2] ?? '';
    }
}
