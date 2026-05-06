<?php

declare(strict_types=1);

namespace App\Router;

class Router
{
    private array $routes = [];

    public function get(string $path, array $handler, array $middleware = []): void
    {
        $this->add('GET', $path, $handler, $middleware);
    }

    public function post(string $path, array $handler, array $middleware = []): void
    {
        $this->add('POST', $path, $handler, $middleware);
    }

    private function add(string $method, string $path, array $handler, array $middleware): void
    {
        $this->routes[] = compact('method', 'path', 'handler', 'middleware');
    }

    public function dispatch(string $method, string $uri): void
    {
        $path = parse_url($uri, PHP_URL_PATH) ?: '/';

        foreach ($this->routes as $route) {
            $params = $this->match($route['path'], $path);

            if ($route['method'] === $method && $params !== null) {
                if (in_array('auth', $route['middleware']) && empty($_SESSION['user_id'])) {
                    header('Location: /login');
                    exit;
                }

                [$class, $action] = $route['handler'];
                $controller = new $class();
                $controller->$action(...array_map('intval', $params));
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
