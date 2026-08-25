<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDOException;

final class Activity
{
    private static bool $schemaChecked = false;

    public static function all(): array
    {
        $sql = "
            SELECT action AS message, DATE_FORMAT(created_at, '%b %d, %Y %h:%i %p') AS time
            FROM audit_logs
            ORDER BY created_at DESC, id DESC
            LIMIT 8
        ";

        return Database::connection()->query($sql)->fetchAll();
    }

    public static function auditLogs(): array
    {
        $sql = "
            SELECT
                al.id,
                COALESCE(u.username, 'System') AS username,
                COALESCE(u.full_name, 'System') AS full_name,
                al.action,
                DATE_FORMAT(al.created_at, '%b %d, %Y %h:%i %p') AS created_at,
                DATE_FORMAT(al.created_at, '%Y-%m-%d %H:%i:%s') AS sortable_created_at
            FROM audit_logs al
            LEFT JOIN users u ON u.id = al.user_id
            ORDER BY al.created_at DESC, al.id DESC
        ";

        return Database::connection()->query($sql)->fetchAll();
    }

    public static function add(string $message): void
    {
        self::ensureSchema();

        $payload = [
            'user_id' => !empty($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : null,
            'action' => $message,
        ];

        try {
            self::insert($payload);
        } catch (PDOException $exception) {
            if (!self::isDuplicateZeroPrimaryKey($exception)) {
                throw $exception;
            }

            self::$schemaChecked = false;
            self::ensureSchema();
            self::insert($payload);
        }
    }

    private static function insert(array $payload): void
    {
        $stmt = Database::connection()->prepare('INSERT INTO audit_logs (user_id, action, details) VALUES (:user_id, :action, JSON_OBJECT())');
        $stmt->execute($payload);
    }

    private static function ensureSchema(): void
    {
        if (self::$schemaChecked) {
            return;
        }
        self::$schemaChecked = true;

        $db = Database::connection();
        $db->exec("
            CREATE TABLE IF NOT EXISTS audit_logs (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                user_id BIGINT UNSIGNED NULL,
                action VARCHAR(120) NOT NULL,
                details JSON NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX audit_logs_user_id (user_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        $zeroIdCount = (int) $db->query('SELECT COUNT(*) FROM audit_logs WHERE id = 0')->fetchColumn();
        if ($zeroIdCount > 0) {
            $nextId = max(1, (int) $db->query('SELECT COALESCE(MAX(id), 0) + 1 FROM audit_logs WHERE id <> 0')->fetchColumn());
            $update = $db->prepare('UPDATE audit_logs SET id = :next_id WHERE id = 0 LIMIT 1');
            $update->execute(['next_id' => $nextId]);
        }

        $db->exec('ALTER TABLE audit_logs MODIFY id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT');
        $nextAutoIncrement = max(1, (int) $db->query('SELECT COALESCE(MAX(id), 0) + 1 FROM audit_logs')->fetchColumn());
        $db->exec('ALTER TABLE audit_logs AUTO_INCREMENT = ' . $nextAutoIncrement);
    }

    private static function isDuplicateZeroPrimaryKey(PDOException $exception): bool
    {
        $errorInfo = $exception->errorInfo;

        return ($errorInfo[0] ?? null) === '23000'
            && (int) ($errorInfo[1] ?? 0) === 1062
            && str_contains($exception->getMessage(), "Duplicate entry '0'");
    }
}
