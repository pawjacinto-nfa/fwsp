<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

final class Notification
{
    public static function all(?int $userId = null): array
    {
        self::ensureSchema();
        $where = $userId ? 'WHERE user_id = :user_id OR user_id IS NULL' : '';
        $sql = "
            SELECT id, message, target_url, DATE_FORMAT(created_at, '%b %d, %Y %h:%i %p') AS time, is_read AS `read`
            FROM notifications
            {$where}
            ORDER BY created_at DESC, id DESC
            LIMIT 10
        ";

        $stmt = Database::connection()->prepare($sql);
        $stmt->execute($userId ? ['user_id' => $userId] : []);

        return $stmt->fetchAll();
    }

    public static function unreadCount(?int $userId = null): int
    {
        self::ensureSchema();
        $where = $userId ? 'WHERE is_read = 0 AND (user_id = :user_id OR user_id IS NULL)' : 'WHERE is_read = 0';
        $stmt = Database::connection()->prepare("SELECT COUNT(*) FROM notifications {$where}");
        $stmt->execute($userId ? ['user_id' => $userId] : []);

        return (int) $stmt->fetchColumn();
    }

    public static function add(string $message, ?int $userId = null, string $targetUrl = ''): void
    {
        self::ensureSchema();
        $stmt = Database::connection()->prepare('INSERT INTO notifications (user_id, message, target_url, is_read) VALUES (:user_id, :message, :target_url, 0)');
        $stmt->execute([
            'user_id' => $userId,
            'message' => $message,
            'target_url' => $targetUrl !== '' ? $targetUrl : null,
        ]);
    }

    public static function addUserRegistrationPending(): void
    {
        self::ensureSchema();

        $stmt = Database::connection()->query("
            SELECT id
            FROM users
            WHERE role = 'Super Admin' AND is_active = 1
        ");

        foreach (array_map('intval', array_column($stmt->fetchAll(), 'id')) as $adminId) {
            self::add('New user registration is pending activation.', $adminId, 'index.php?page=users');
        }
    }

    public static function markReadForUser(int $notificationId, int $userId): ?string
    {
        self::ensureSchema();

        $stmt = Database::connection()->prepare("
            SELECT message, target_url
            FROM notifications
            WHERE id = :id AND (user_id = :user_id OR user_id IS NULL)
            LIMIT 1
        ");
        $stmt->execute(['id' => $notificationId, 'user_id' => $userId]);
        $notification = $stmt->fetch();

        if (!$notification) {
            return null;
        }

        $update = Database::connection()->prepare("
            UPDATE notifications
            SET is_read = 1
            WHERE id = :id AND (user_id = :user_id OR user_id IS NULL)
        ");
        $update->execute(['id' => $notificationId, 'user_id' => $userId]);

        return self::safeTarget(
            (string) ($notification['target_url'] ?? ''),
            (string) ($notification['message'] ?? '')
        );
    }

    public static function clearForUser(int $userId): void
    {
        self::ensureSchema();

        $stmt = Database::connection()->prepare("
            DELETE FROM notifications
            WHERE user_id = :user_id OR user_id IS NULL
        ");
        $stmt->execute(['user_id' => $userId]);
    }

    private static function ensureSchema(): void
    {
        static $ready = false;
        if ($ready) {
            return;
        }

        Database::connection()->exec('ALTER TABLE notifications ADD COLUMN IF NOT EXISTS user_id BIGINT UNSIGNED NULL');
        Database::connection()->exec('ALTER TABLE notifications ADD COLUMN IF NOT EXISTS target_url VARCHAR(255) NULL');
        Database::connection()->exec("
            UPDATE notifications
            SET target_url = 'index.php?page=tech-support'
            WHERE (target_url IS NULL OR target_url = '')
                AND (
                    message LIKE '%tech support ticket%'
                    OR message LIKE '%ticket submitted%'
                    OR message LIKE '%replied to your ticket%'
                    OR message LIKE '%ticket has been marked completed%'
                )
        ");
        Database::connection()->exec("
            UPDATE notifications
            SET target_url = 'index.php?page=users'
            WHERE (target_url IS NULL OR target_url = '')
                AND message LIKE '%registration is pending activation%'
        ");
        $ready = true;
    }

    private static function safeTarget(string $targetUrl, string $message = ''): string
    {
        if ($targetUrl === '') {
            $targetUrl = self::targetFromMessage($message);
        }

        if ($targetUrl === '') {
            return 'index.php';
        }

        if (preg_match('/^index\.php(\?[A-Za-z0-9_\-=&%.#]+)?$/', $targetUrl) === 1) {
            return $targetUrl;
        }

        return 'index.php';
    }

    private static function targetFromMessage(string $message): string
    {
        $message = strtolower($message);

        if (
            str_contains($message, 'tech support ticket')
            || str_contains($message, 'ticket submitted')
            || str_contains($message, 'replied to your ticket')
            || str_contains($message, 'ticket has been marked completed')
        ) {
            return 'index.php?page=tech-support';
        }

        if (str_contains($message, 'registration is pending activation')) {
            return 'index.php?page=users';
        }

        return '';
    }
}
