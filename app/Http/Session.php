<?php

namespace App\Http;

class Session
{
    public function setUserId(int $id): void
    {
        $_SESSION['user_id'] = $id;
    }

    public function getUserId(): int
    {
        return (int) ($_SESSION['user_id'] ?? 0);
    }

    public function setMessage(string $type, string $message): void
    {
        $_SESSION['message'][$type] = $message;
    }

    public function getMessage(string $type): ?string
    {
        if (empty($_SESSION['message'][$type])) {
            return null;
        }

        $message = $_SESSION['message'][$type];
        unset($_SESSION['message'][$type]);

        return $message;
    }

    public function saveForm(array $data): void
    {
        $_SESSION['old_value'] = $data;
    }

    public function getFormValue(string $key, string $default = ''): string
    {
        $value = $_SESSION['old_value'][$key] ?? $default;
        unset($_SESSION['old_value'][$key]);

        return htmlspecialchars((string) $value);
    }

    public function getFormArray(string $key): array
    {
        $value = $_SESSION['old_value'][$key] ?? [];
        unset($_SESSION['old_value'][$key]);

        return is_array($value) ? $value : [];
    }
}
