<?php

session_start();

require_once __DIR__ . '/../vendor/autoload.php';

use Core\Router;

function view(string $name, array $data = []): void
{
    extract($data);
    require __DIR__ . '/../views/' . $name . '.php';
}

function redirect(string $path): void
{
    header('Location: ' . $path);
    exit;
}

function flash(string $type, string $message): void
{
    $_SESSION['flash'][$type] = $message;
}

function flash_message(string $type): ?string
{
    if (empty($_SESSION['flash'][$type])) {
        return null;
    }

    $message = $_SESSION['flash'][$type];
    unset($_SESSION['flash'][$type]);

    return $message;
}

function flash_old(array $data): void
{
    $_SESSION['old_input'] = $data;
}

function old(string $key, string $default = ''): string
{
    $value = $_SESSION['old_input'][$key] ?? $default;
    unset($_SESSION['old_input'][$key]);
    return e(is_array($value) ? '' : (string) $value);
}

function old_array(string $key): array
{
    $value = $_SESSION['old_input'][$key] ?? [];
    unset($_SESSION['old_input'][$key]);
    return is_array($value) ? $value : [];
}

function current_user_id(): int
{
    return (int) ($_SESSION['user_id'] ?? 0);
}

function require_login(): void
{
    if (!current_user_id()) {
        redirect('/login');
    }
}

function e(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

$router = new Router();
require __DIR__ . '/../routes/web.php';

try {
    $router->dispatch($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']);
} catch (Throwable $exception) {
    http_response_code(500);
    echo 'Erro: ' . e($exception->getMessage());
}
