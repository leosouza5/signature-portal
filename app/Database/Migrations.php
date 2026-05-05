<?php

declare(strict_types=1);

namespace App\Database;

final class Migrations
{
    public static function run(): void
    {
        $pdo = Connection::getInstance();

        $pdo->exec(
            "CREATE TABLE IF NOT EXISTS users (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name TEXT NOT NULL,
                email TEXT NOT NULL UNIQUE,
                password TEXT NOT NULL,
                created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
            )"
        );

        $pdo->exec(
            "CREATE TABLE IF NOT EXISTS documents (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                user_id INTEGER NOT NULL,
                title TEXT NOT NULL,
                original_name TEXT NOT NULL,
                local_path TEXT NOT NULL,
                certisign_upload_id TEXT,
                certisign_document_id TEXT,
                certisign_document_key TEXT,
                status TEXT NOT NULL DEFAULT 'DRAFT',
                error_message TEXT,
                created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES users(id)
            )"
        );

        $pdo->exec(
            "CREATE TABLE IF NOT EXISTS signers (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                document_id INTEGER NOT NULL,
                name TEXT NOT NULL,
                email TEXT NOT NULL,
                cpf TEXT NOT NULL,
                step INTEGER NOT NULL DEFAULT 1,
                certisign_attendee_id TEXT,
                sign_url TEXT,
                status TEXT NOT NULL DEFAULT 'PENDING',
                created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (document_id) REFERENCES documents(id)
            )"
        );
    }
}
