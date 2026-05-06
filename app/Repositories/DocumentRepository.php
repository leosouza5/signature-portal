<?php

namespace App\Repositories;

use App\Database\Connection;

class DocumentRepository
{
    private \PDO $db;

    public function __construct()
    {
        $this->db = Connection::getInstance();
    }

    public function create(int $userId, string $title, string $originalName, string $localPath): int
    {
        $stmt = $this->db->prepare('
            INSERT INTO documents (user_id, title, original_name, local_path)
            VALUES (?, ?, ?, ?)
        ');

        $stmt->execute([$userId, $title, $originalName, $localPath]);

        return (int) $this->db->lastInsertId();
    }

    public function getAllByUser(int $userId): array
    {
        $stmt = $this->db->prepare('
            SELECT *
            FROM documents
            WHERE user_id = ?
            ORDER BY id DESC
        ');
        $stmt->execute([$userId]);

        return $stmt->fetchAll();
    }

    public function getPendingByUser(int $userId): array
    {
        $stmt = $this->db->prepare("
            SELECT *
            FROM documents
            WHERE user_id = ?
              AND status IN ('DRAFT', 'SENT')
            ORDER BY id DESC
        ");
        $stmt->execute([$userId]);

        return $stmt->fetchAll();
    }

    public function getSignedByUser(int $userId): array
    {
        $stmt = $this->db->prepare("
            SELECT *
            FROM documents
            WHERE user_id = ?
              AND status = 'COMPLETED'
            ORDER BY id DESC
        ");
        $stmt->execute([$userId]);

        return $stmt->fetchAll();
    }

    public function getErrorByUser(int $userId): array
    {
        $stmt = $this->db->prepare("
            SELECT *
            FROM documents
            WHERE user_id = ?
              AND status = 'ERROR'
            ORDER BY id DESC
        ");
        $stmt->execute([$userId]);

        return $stmt->fetchAll();
    }

    public function getByUser(int $id, int $userId): ?array
    {
        $stmt = $this->db->prepare('
            SELECT *
            FROM documents
            WHERE id = ?
              AND user_id = ?
            LIMIT 1
        ');
        $stmt->execute([$id, $userId]);

        return $stmt->fetch() ?: null;
    }

    public function getById(int $id): ?array
    {
        $stmt = $this->db->prepare('
            SELECT *
            FROM documents
            WHERE id = ?
            LIMIT 1
        ');
        $stmt->execute([$id]);

        return $stmt->fetch() ?: null;
    }

    public function updateStatus(int $id, string $status, ?string $errorMessage = null): void
    {
        $stmt = $this->db->prepare('
            UPDATE documents
            SET status = ?,
                error_message = ?,
                updated_at = CURRENT_TIMESTAMP
            WHERE id = ?
        ');
        $stmt->execute([$status, $errorMessage, $id]);
    }

    public function updateUploadId(int $id, string $uploadId): void
    {
        $stmt = $this->db->prepare('
            UPDATE documents
            SET certisign_upload_id = ?
            WHERE id = ?
        ');
        $stmt->execute([$uploadId, $id]);
    }

    public function updateSignatureInfo(int $id, ?string $documentId, ?string $documentKey, string $status): void
    {
        $stmt = $this->db->prepare('
            UPDATE documents
            SET certisign_document_id = ?,
                certisign_document_key = ?,
                status = ?,
                updated_at = CURRENT_TIMESTAMP
            WHERE id = ?
        ');
        $stmt->execute([$documentId, $documentKey, $status, $id]);
    }

    public function delete(int $id): void
    {
        $this->db->prepare('
            DELETE FROM documents
            WHERE id = ?
        ')->execute([$id]);
    }
}
