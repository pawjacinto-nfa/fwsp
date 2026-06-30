<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;

final class DatabaseSchema
{
    public static function tables(): array
    {
        $statement = Database::connection()->query(
            "SELECT TABLE_NAME, TABLE_ROWS, TABLE_COMMENT
             FROM information_schema.TABLES
             WHERE TABLE_SCHEMA = DATABASE() AND TABLE_TYPE = 'BASE TABLE'
             ORDER BY TABLE_NAME"
        );

        return $statement->fetchAll();
    }

    public static function describe(string $table): ?array
    {
        $tables = self::tables();
        $tableInfo = null;
        foreach ($tables as $candidate) {
            if (hash_equals((string) $candidate['TABLE_NAME'], $table)) {
                $tableInfo = $candidate;
                break;
            }
        }

        if ($tableInfo === null) {
            return null;
        }

        $pdo = Database::connection();
        $columnStatement = $pdo->prepare(
            "SELECT COLUMN_NAME, COLUMN_TYPE, DATA_TYPE, IS_NULLABLE, COLUMN_DEFAULT,
                    COLUMN_KEY, EXTRA, COLUMN_COMMENT, ORDINAL_POSITION
             FROM information_schema.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = :table
             ORDER BY ORDINAL_POSITION"
        );
        $columnStatement->execute(['table' => $table]);
        $columns = $columnStatement->fetchAll();

        $relationStatement = $pdo->prepare(
            "SELECT COLUMN_NAME, CONSTRAINT_NAME, REFERENCED_TABLE_NAME, REFERENCED_COLUMN_NAME
             FROM information_schema.KEY_COLUMN_USAGE
             WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = :table
               AND REFERENCED_TABLE_NAME IS NOT NULL
             ORDER BY ORDINAL_POSITION"
        );
        $relationStatement->execute(['table' => $table]);
        $relations = $relationStatement->fetchAll();

        $indexStatement = $pdo->prepare(
            "SELECT INDEX_NAME, NON_UNIQUE, GROUP_CONCAT(COLUMN_NAME ORDER BY SEQ_IN_INDEX SEPARATOR ', ') AS COLUMNS
             FROM information_schema.STATISTICS
             WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = :table
             GROUP BY INDEX_NAME, NON_UNIQUE
             ORDER BY (INDEX_NAME = 'PRIMARY') DESC, INDEX_NAME"
        );
        $indexStatement->execute(['table' => $table]);
        $indexes = $indexStatement->fetchAll();

        $example = $pdo->query('SELECT * FROM `' . str_replace('`', '``', $table) . '` LIMIT 1')->fetch(PDO::FETCH_ASSOC) ?: [];
        foreach ($columns as &$column) {
            $column['EXAMPLE'] = self::exampleValue((string) $column['COLUMN_NAME'], $example[$column['COLUMN_NAME']] ?? null);
        }
        unset($column);

        return [
            'table' => $tableInfo,
            'columns' => $columns,
            'relations' => $relations,
            'indexes' => $indexes,
        ];
    }

    private static function exampleValue(string $column, mixed $value): string
    {
        if (preg_match('/password|passwd|token|secret|session|credential/i', $column)) {
            return '[masked]';
        }
        if ($value === null) {
            return 'NULL';
        }
        if (is_resource($value)) {
            return '[binary data]';
        }

        $text = preg_replace('/\s+/u', ' ', trim((string) $value)) ?? '';
        if ($text === '') {
            return '(empty)';
        }
        if (!mb_check_encoding($text, 'UTF-8')) {
            return '[binary data]';
        }

        return mb_strlen($text) > 80 ? mb_substr($text, 0, 77) . '...' : $text;
    }
}
