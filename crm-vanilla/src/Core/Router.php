<?php

namespace Core;

class Router
{
    private array $routes = [];

    public function get(string $path, string $handler): void
    {
        $this->routes['GET'][$path] = $handler;
    }

    public function post(string $path, string $handler): void
    {
        $this->routes['POST'][$path] = $handler;
    }

    public function dispatch(string $uri, string $method): void
    {
        $method = strtoupper($method);
        // Allow POST tunneling via _method
        if ($method === 'POST' && isset($_POST['_method'])) {
            $method = strtoupper($_POST['_method']);
        }

        $uri = '/' . trim(parse_url($uri, PHP_URL_PATH), '/');
        if ($uri === '//') $uri = '/';

        $routes = $this->routes[$method] ?? [];

        foreach ($routes as $pattern => $handler) {
            $regex = $this->toRegex($pattern);
            if (preg_match($regex, $uri, $matches)) {
                array_shift($matches);
                $params = array_values(array_filter($matches, fn($k) => !is_int($k), ARRAY_FILTER_USE_KEY));
                if (empty($params)) {
                    $params = array_values(array_filter($matches));
                }
                $this->call($handler, $params);
                return;
            }
        }

        http_response_code(404);
        View::render('errors/404', ['title' => 'Página no encontrada']);
    }

    private function toRegex(string $pattern): string
    {
        // Replace {param} with named capture groups
        $regex = preg_replace('/\{([a-zA-Z_]+)\}/', '(?P<$1>[^/]+)', $pattern);
        return '#^' . $regex . '$#';
    }

    private function call(string $handler, array $params): void
    {
        [$class, $method] = explode('@', $handler);

        // Support namespacing: Controllers\Admin\UserController
        $fullClass = 'Controllers\\' . str_replace('/', '\\', $class);

        if (!class_exists($fullClass)) {
            http_response_code(500);
            die("Controller {$fullClass} not found.");
        }

        $controller = new $fullClass();

        if (!method_exists($controller, $method)) {
            http_response_code(500);
            die("Method {$method} not found in {$fullClass}.");
        }

        $controller->$method(...$params);
    }
}
