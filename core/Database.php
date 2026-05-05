<?php

namespace Core;

use PDO;

class Database
{
    private static ?PDO $connection = null;

    public static function getConnection(): PDO
    {
        if (self::$connection === null) {
            if (!in_array('sqlite', PDO::getAvailableDrivers(), true)) {
                throw new RuntimeException('Driver pdo_sqlite nao esta habilitado no PHP.');
            }

            $path = __DIR__ . '/../database/database.sqlite';
            self::$connection = new PDO('sqlite:' . $path);
            self::$connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            self::$connection->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        }

        return self::$connection;
    }
}
