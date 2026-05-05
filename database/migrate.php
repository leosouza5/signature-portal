<?php

require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/Migration.php';

$databaseFile = __DIR__ . '/database.sqlite';

if (!file_exists($databaseFile)) {
    touch($databaseFile);
}

try {
    $migration = new Migration(Database::getConnection(), __DIR__ . '/migrations');
    $migration->run();
    echo "Migrations executadas com sucesso.\n";
} catch (Throwable $exception) {
    echo "Erro ao executar migrations: " . $exception->getMessage() . "\n";
    exit(1);
}
