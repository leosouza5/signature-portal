<?php

namespace App\Repositories;

use Core\Database;

class UserRepository
{
    private \PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function create(string $name, string $email, string $passwordHash): int
    {
        $stmt = $this->db->prepare('INSERT INTO users (name, email, password) VALUES (?, ?, ?)');
        $stmt->execute([$name, $email, $passwordHash]);

        return (int) $this->db->lastInsertId();
    }

    public function findByEmail(string $email): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM users WHERE email = ? LIMIT 1');
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        return $user ?: null;
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM users WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        $user = $stmt->fetch();

        return $user ?: null;
    }
}
