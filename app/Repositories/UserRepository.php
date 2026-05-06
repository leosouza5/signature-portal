<?php

namespace App\Repositories;

use App\Database\Connection;

class UserRepository
{
    private \PDO $db;

    public function __construct()
    {
        $this->db = Connection::getInstance();
    }

    public function create(string $name, string $email, string $passwordHash): int
    {
        $stmt = $this->db->prepare('
            INSERT INTO users (name, email, password)
            VALUES (?, ?, ?)
        ');
        $stmt->execute([$name, $email, $passwordHash]);

        return (int) $this->db->lastInsertId();
    }

    public function findByEmail(string $email): ?array
    {
        $stmt = $this->db->prepare('
            SELECT *
            FROM users
            WHERE email = ?
            LIMIT 1
        ');
        $stmt->execute([$email]);

        return $stmt->fetch() ?: null;
    }

    public function getById(int $id): ?array
    {
        $stmt = $this->db->prepare('
            SELECT *
            FROM users
            WHERE id = ?
            LIMIT 1
        ');
        $stmt->execute([$id]);

        return $stmt->fetch() ?: null;
    }
}
