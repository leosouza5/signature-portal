<?php

namespace App\Repositories;

use Core\Database;

class DocumentRepository
{
    private \PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function create(int $envelopeId, string $originalName, string $localPath): int
    {
        $stmt = $this->db->prepare('INSERT INTO documents (envelope_id, original_name, local_path) VALUES (?, ?, ?)');
        $stmt->execute([$envelopeId, $originalName, $localPath]);

        return (int) $this->db->lastInsertId();
    }

    public function allByEnvelope(int $envelopeId): array
    {
        $stmt = $this->db->prepare('SELECT * FROM documents WHERE envelope_id = ? ORDER BY id ASC');
        $stmt->execute([$envelopeId]);

        return $stmt->fetchAll();
    }

    public function updateUploadId(int $id, string $uploadId): void
    {
        $stmt = $this->db->prepare('UPDATE documents SET certisign_upload_id = ? WHERE id = ?');
        $stmt->execute([$uploadId, $id]);
    }

    public function updateCertisignData(int $id, ?string $documentId, ?string $documentKey, string $status): void
    {
        $stmt = $this->db->prepare('UPDATE documents SET certisign_document_id = ?, certisign_document_key = ?, status = ? WHERE id = ?');
        $stmt->execute([$documentId, $documentKey, $status, $id]);
    }
}
