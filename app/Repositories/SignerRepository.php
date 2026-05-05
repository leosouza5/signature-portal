<?php

namespace App\Repositories;

use Core\Database;

class SignerRepository
{
    private \PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function create(int $envelopeId, string $name, string $email, string $cpf, int $step): int
    {
        $stmt = $this->db->prepare('INSERT INTO signers (envelope_id, name, email, cpf, step) VALUES (?, ?, ?, ?, ?)');
        $stmt->execute([$envelopeId, $name, $email, $cpf, $step]);

        return (int) $this->db->lastInsertId();
    }

    public function allByEnvelope(int $envelopeId): array
    {
        $stmt = $this->db->prepare('SELECT * FROM signers WHERE envelope_id = ? ORDER BY step ASC, id ASC');
        $stmt->execute([$envelopeId]);

        return $stmt->fetchAll();
    }

    public function updateCertisignData(int $id, ?string $attendeeId, ?string $signUrl): void
    {
        $stmt = $this->db->prepare('UPDATE signers SET certisign_attendee_id = ?, sign_url = ? WHERE id = ?');
        $stmt->execute([$attendeeId, $signUrl, $id]);
    }
}
