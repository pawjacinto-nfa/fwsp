<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

final class Activity
{
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
        $stmt = Database::connection()->prepare('INSERT INTO audit_logs (user_id, action, details) VALUES (:user_id, :action, JSON_OBJECT())');
        $stmt->execute([
            'user_id' => !empty($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : null,
            'action' => $message,
        ]);
    }
}
