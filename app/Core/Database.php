<?php
declare(strict_types=1);

namespace App\Core;

use PDO;

final class Database
{
    private static ?PDO $connection = null;
    private static bool $utf8mb4Checked = false;

    public static function connection(): PDO
    {
        if (self::$connection !== null) {
            return self::$connection;
        }

        $config = require BASE_PATH . '/app/config/database.php';
        $dsn = sprintf(
            'mysql:host=%s;port=%s;dbname=%s;charset=%s',
            $config['host'],
            $config['port'],
            $config['database'],
            $config['charset']
        );

        self::$connection = new PDO($dsn, $config['username'], $config['password'], [
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci",
        ]);
        self::ensureUtf8mb4(self::$connection, (string) $config['database']);

        return self::$connection;
    }

    /** Keeps legacy tables compatible with names such as Peña and Niño. */
    private static function ensureUtf8mb4(PDO $db, string $database): void
    {
        if (self::$utf8mb4Checked) {
            return;
        }
        self::$utf8mb4Checked = true;
        $databaseName = preg_replace('/[^A-Za-z0-9_]/', '', $database);
        if ($databaseName === '') {
            return;
        }

        $db->exec('SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci');
        $db->exec("ALTER DATABASE `{$databaseName}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        $tables = $db->prepare("SELECT TABLE_NAME FROM information_schema.TABLES WHERE TABLE_SCHEMA = :database_name AND TABLE_TYPE = 'BASE TABLE' AND (TABLE_COLLATION IS NULL OR TABLE_COLLATION NOT LIKE 'utf8mb4%')");
        $tables->execute(['database_name' => $databaseName]);
        foreach ($tables->fetchAll(PDO::FETCH_COLUMN) as $table) {
            $safeTable = str_replace('`', '``', (string) $table);
            $db->exec("ALTER TABLE `{$safeTable}` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        }
    }
}
