<?php

class Migration
{
    private PDO $db;
    private string $migrationsPath;

    public function __construct(PDO $db, string $migrationsPath)
    {
        $this->db = $db;
        $this->migrationsPath = rtrim($migrationsPath, '/\\');
    }

    public function run(): void
    {
        $files = glob($this->migrationsPath . '/*.sql') ?: [];
        sort($files);

        foreach ($files as $file) {
            $sql = file_get_contents($file);

            if ($sql === false) {
                throw new RuntimeException('Nao foi possivel ler a migration: ' . basename($file));
            }

            $this->db->exec($sql);
        }
    }
}
