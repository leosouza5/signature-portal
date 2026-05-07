<?php

namespace App\Repositories;

use App\Database\Connection;

class SignerRepository
{
    private \PDO $db;

    public function __construct()
    {
        $this->db = Connection::getInstance();
    }

    public function create(int $documentId, string $name, string $email, string $cpf, int $step): int
    {
        $stmt = $this->db->prepare('
            INSERT INTO signers (document_id, name, email, cpf, step)
            VALUES (?, ?, ?, ?, ?)
            RETURNING id
        ');
        $stmt->execute([$documentId, $name, $email, $cpf, $step]);

        return (int) $stmt->fetchColumn();
    }

    public function getAllByDocument(int $documentId): array
    {
        $stmt = $this->db->prepare('
            SELECT *
            FROM signers
            WHERE document_id = ?
            ORDER BY step ASC, id ASC
        ');
        $stmt->execute([$documentId]);

        return $stmt->fetchAll();
    }

    public function updateStatus(int $id, string $status): void
    {
        $stmt = $this->db->prepare('
            UPDATE signers
            SET status = ?
            WHERE id = ?
        ');
        $stmt->execute([$status, $id]);
    }

    public function updateSignatureInfo(int $id, ?string $attendeeId, ?string $signUrl): void
    {
        $stmt = $this->db->prepare('
            UPDATE signers
            SET certisign_attendee_id = ?,
                sign_url = ?
            WHERE id = ?
        ');
        $stmt->execute([$attendeeId, $signUrl, $id]);
    }
}
