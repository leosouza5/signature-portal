<?php

namespace Core;

class Router
{
    private array $routes = [];

    public function get(string $path, array $handler): void
    {
        $this->add('GET', $path, $handler);
    }

    public function post(string $path, array $handler): void
    {
        $this->add('POST', $path, $handler);
    }

    private function add(string $method, string $path, array $handler): void
    {
        $this->routes[] = compact('method', 'path', 'handler');
    }

    public function dispatch(string $method, string $uri): void
    {
        $path = parse_url($uri, PHP_URL_PATH) ?: '/';

        foreach ($this->routes as $route) {
            $params = $this->match($route['path'], $path);

            if ($route['method'] === $method && $params !== null) {
                [$class, $action] = $route['handler'];
                $controller = new $class();
                $controller->$action(...$params);
                return;
            }
        }

        http_response_code(404);
        echo 'Pagina não encontrada.';
    }

    private function match(string $routePath, string $requestPath): ?array
    {
        $pattern = preg_replace('#\{[a-zA-Z_]+\}#', '([0-9]+)', $routePath);
        $pattern = '#^' . $pattern . '$#';

        if (!preg_match($pattern, $requestPath, $matches)) {
            return null;
        }

        array_shift($matches);
        return $matches;
    }
}
