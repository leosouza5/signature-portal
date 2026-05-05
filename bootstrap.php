<?php

declare(strict_types=1);

require_once __DIR__ . '/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

use App\Database\Migrations;

try {
    Migrations::run();
    echo "Migrations executadas com sucesso.\n";
} catch (Throwable $e) {
    echo "Erro ao executar migrations: " . $e->getMessage() . "\n";
    exit(1);
}
