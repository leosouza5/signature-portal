<?php

namespace App\Http;

class Response
{
    public static function redirect(string $path): never
    {
        header('Location: ' . $path);
        exit;
    }

    public static function view(string $name, array $data = []): void
    {
        extract($data);
        require __DIR__ . '/../../public/views/' . $name . '.php';
    }

    public static function abort(int $code, string $message): never
    {
        http_response_code($code);
        echo htmlspecialchars($message);
        exit;
    }
}
