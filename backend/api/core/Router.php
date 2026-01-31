<?php

class Router {
    /**
     * @var array<string, callable>
     */
    private array $routes = [];

    public function add(string $method, string $path, callable $handler): void {
        $key = strtoupper($method) . ' ' . $path;
        $this->routes[$key] = $handler;
    }

    public function dispatch(Request $req): void {
        $resource = $req->resource();
        $action = $req->action();
        $path = '/' . $req->version() . '/' . $resource;
        if ($action !== '') {
            $path .= '/' . $action;
        }

        $key = $req->method . ' ' . $path;
        if (isset($this->routes[$key])) {
            ($this->routes[$key])($req);
            return;
        }

        // default resource root (no action)
        $keyRoot = $req->method . ' ' . ('/' . $req->version() . '/' . $resource);
        if ($action === '' && isset($this->routes[$keyRoot])) {
            ($this->routes[$keyRoot])($req);
            return;
        }

        throw new Exception('Route not found', 404);
    }
}
