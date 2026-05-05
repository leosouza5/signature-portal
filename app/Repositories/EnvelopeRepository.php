<?php

namespace App\Repositories;

use App\Database\Connection;

class EnvelopeRepository
{
    private \PDO $db;

    public function __construct()
    {
        $this->db = Connection::getInstance();
    }

    public function create(int $userId, string $title): int
    {
        $stmt = $this->db->prepare('INSERT INTO envelopes (user_id, title, status) VALUES (?, ?, ?)');
        $stmt->execute([$userId, $title, 'DRAFT']);

        return (int) $this->db->lastInsertId();
    }

    public function allByUser(int $userId): array
    {
        $stmt = $this->db->prepare('SELECT * FROM envelopes WHERE user_id = ? ORDER BY id DESC');
        $stmt->execute([$userId]);

        return $stmt->fetchAll();
    }

    public function findForUser(int $id, int $userId): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM envelopes WHERE id = ? AND user_id = ? LIMIT 1');
        $stmt->execute([$id, $userId]);
        $envelope = $stmt->fetch();

        return $envelope ?: null;
    }

    public function updateStatus(int $id, string $status, ?string $errorMessage = null): void
    {
        $stmt = $this->db->prepare('UPDATE envelopes SET status = ?, error_message = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?');
        $stmt->execute([$status, $errorMessage, $id]);
    }

    public function delete(int $id): void
    {
        $this->db->prepare('DELETE FROM envelopes WHERE id = ?')->execute([$id]);
    }
}
