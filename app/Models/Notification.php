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
            SELECT n.id, n.message, n.target_url, DATE_FORMAT(n.created_at, '%b %d, %Y %h:%i %p') AS time,
                CASE WHEN n.user_id IS NULL THEN n.is_read = 1 OR EXISTS(
                    SELECT 1 FROM notification_reads nr WHERE nr.notification_id = n.id AND nr.user_id = :read_user_id
                ) ELSE n.is_read END AS `read`
            FROM notifications n
            {$where}
            ORDER BY created_at DESC, id DESC
            LIMIT 10
        ";

        $stmt = Database::connection()->prepare($sql);
        $stmt->execute($userId ? ['user_id' => $userId, 'read_user_id' => $userId] : ['read_user_id' => 0]);

        return $stmt->fetchAll();
    }

    /** Return the complete notification history for one user. */
    public static function forUser(int $userId): array
    {
        self::ensureSchema();
        $stmt = Database::connection()->prepare("
            SELECT n.id, n.message, n.target_url, DATE_FORMAT(n.created_at, '%b %d, %Y %h:%i %p') AS time,
                CASE WHEN n.user_id IS NULL THEN n.is_read = 1 OR EXISTS(
                    SELECT 1 FROM notification_reads nr WHERE nr.notification_id = n.id AND nr.user_id = :read_user_id
                ) ELSE n.is_read END AS `read`
            FROM notifications n
            WHERE n.user_id = :owner_user_id OR n.user_id IS NULL
            ORDER BY n.created_at DESC, n.id DESC
        ");
        $stmt->execute(['owner_user_id' => $userId, 'read_user_id' => $userId]);

        return $stmt->fetchAll();
    }

    public static function unreadCount(?int $userId = null): int
    {
        self::ensureSchema();
        $where = $userId
            ? 'WHERE (n.user_id = :owner_user_id AND n.is_read = 0) OR (n.user_id IS NULL AND n.is_read = 0 AND NOT EXISTS (SELECT 1 FROM notification_reads nr WHERE nr.notification_id = n.id AND nr.user_id = :read_user_id))'
            : 'WHERE n.is_read = 0';
        $stmt = Database::connection()->prepare("SELECT COUNT(*) FROM notifications n {$where}");
        $stmt->execute($userId ? ['owner_user_id' => $userId, 'read_user_id' => $userId] : []);

        return (int) $stmt->fetchColumn();
    }

    public static function add(string $message, ?int $userId = null, string $targetUrl = '', string $category = 'account_updates'): void
    {
        self::ensureSchema();
        $allowed = ['office_location', 'farmer_new', 'farmer_updates', 'farmer_delivery_individual', 'farmer_delivery_fo', 'annual_bag_limit', 'cross_location_delivery', 'tech_support', 'account_updates'];
        $category = in_array($category, $allowed, true) ? $category : 'account_updates';
        if ($userId === null) {
            $users = Database::connection()->query('SELECT id FROM users WHERE is_active = 1')->fetchAll();
            foreach ($users as $user) self::add($message, (int) $user['id'], $targetUrl, $category);
            return;
        }
        $preferences = self::preferencesForUser($userId);
        if (empty($preferences[$category])) return;
        $stmt = Database::connection()->prepare('INSERT INTO notifications (user_id, message, target_url, is_read) VALUES (:user_id, :message, :target_url, 0)');
        $stmt->execute([
            'user_id' => $userId,
            'message' => $message,
            'target_url' => $targetUrl !== '' ? $targetUrl : null,
        ]);
    }

    public static function preferencesForUser(int $userId): array
    {
        self::ensureSchema();
        $stmt = Database::connection()->prepare('SELECT * FROM notification_preferences WHERE user_id = :user_id');
        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetch() ?: ['office_location' => 1, 'location_level' => 'Region', 'farmer_new' => 1, 'farmer_updates' => 1, 'farmer_delivery_individual' => 1, 'farmer_delivery_fo' => 1, 'annual_bag_limit' => 1, 'cross_location_delivery' => 1, 'tech_support' => 1, 'account_updates' => 1];
    }

    public static function savePreferences(int $userId, array $preferences): void
    {
        self::ensureSchema();
        $stmt = Database::connection()->prepare('INSERT INTO notification_preferences (user_id, office_location, location_level, farmer_new, farmer_updates, farmer_delivery_individual, farmer_delivery_fo, annual_bag_limit, cross_location_delivery, tech_support, account_updates) VALUES (:user_id, :office_location, :location_level, :farmer_new, :farmer_updates, :farmer_delivery_individual, :farmer_delivery_fo, :annual_bag_limit, :cross_location_delivery, :tech_support, :account_updates) ON DUPLICATE KEY UPDATE office_location=VALUES(office_location), location_level=VALUES(location_level), farmer_new=VALUES(farmer_new), farmer_updates=VALUES(farmer_updates), farmer_delivery_individual=VALUES(farmer_delivery_individual), farmer_delivery_fo=VALUES(farmer_delivery_fo), annual_bag_limit=VALUES(annual_bag_limit), cross_location_delivery=VALUES(cross_location_delivery), tech_support=VALUES(tech_support), account_updates=VALUES(account_updates)');
        $params = ['user_id' => $userId];
        $params['location_level'] = in_array(($preferences['location_level'] ?? ''), ['Region', 'Province', 'Office', 'Facility'], true) ? $preferences['location_level'] : 'Region';
        $params['office_location'] = 1;
        foreach (['farmer_new', 'farmer_updates', 'farmer_delivery_individual', 'farmer_delivery_fo', 'annual_bag_limit', 'cross_location_delivery', 'tech_support', 'account_updates'] as $key) {
            $params[$key] = !empty($preferences[$key]) ? 1 : 0;
        }
        $stmt->execute($params);
    }

    public static function addUserRegistrationPending(): void
    {
        self::ensureSchema();

        $stmt = Database::connection()->query("
            SELECT id
            FROM users
            WHERE role = 'System Admin' AND is_active = 1
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

        $markSharedRead = Database::connection()->prepare("
            INSERT IGNORE INTO notification_reads (notification_id, user_id)
            SELECT id, :user_id FROM notifications WHERE id = :id AND user_id IS NULL
        ");
        $markSharedRead->execute(['id' => $notificationId, 'user_id' => $userId]);
        $update = Database::connection()->prepare('UPDATE notifications SET is_read = 1 WHERE id = :id AND user_id = :user_id');
        $update->execute(['id' => $notificationId, 'user_id' => $userId]);

        return self::safeTarget(
            (string) ($notification['target_url'] ?? ''),
            (string) ($notification['message'] ?? '')
        );
    }

    public static function clearForUser(int $userId): void
    {
        self::ensureSchema();

        $markSharedRead = Database::connection()->prepare('INSERT IGNORE INTO notification_reads (notification_id, user_id) SELECT id, :user_id FROM notifications WHERE user_id IS NULL');
        $markSharedRead->execute(['user_id' => $userId]);
        $stmt = Database::connection()->prepare('DELETE FROM notifications WHERE user_id = :user_id');
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
        Database::connection()->exec('CREATE TABLE IF NOT EXISTS notification_reads (notification_id BIGINT UNSIGNED NOT NULL, user_id BIGINT UNSIGNED NOT NULL, read_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, PRIMARY KEY (notification_id, user_id), FOREIGN KEY (notification_id) REFERENCES notifications(id) ON DELETE CASCADE, FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE)');
        Database::connection()->exec("CREATE TABLE IF NOT EXISTS notification_preferences (user_id BIGINT UNSIGNED NOT NULL PRIMARY KEY, office_location TINYINT(1) NOT NULL DEFAULT 1, location_level VARCHAR(16) NOT NULL DEFAULT 'Region', farmer_new TINYINT(1) NOT NULL DEFAULT 1, farmer_updates TINYINT(1) NOT NULL DEFAULT 1, farmer_delivery_individual TINYINT(1) NOT NULL DEFAULT 1, farmer_delivery_fo TINYINT(1) NOT NULL DEFAULT 1, tech_support TINYINT(1) NOT NULL DEFAULT 1, account_updates TINYINT(1) NOT NULL DEFAULT 1, updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP, FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE)");
        Database::connection()->exec("ALTER TABLE notification_preferences ADD COLUMN IF NOT EXISTS location_level VARCHAR(16) NOT NULL DEFAULT 'Region', ADD COLUMN IF NOT EXISTS farmer_delivery_individual TINYINT(1) NOT NULL DEFAULT 1, ADD COLUMN IF NOT EXISTS farmer_delivery_fo TINYINT(1) NOT NULL DEFAULT 1");
        Database::connection()->exec('ALTER TABLE notification_preferences ADD COLUMN IF NOT EXISTS annual_bag_limit TINYINT(1) NOT NULL DEFAULT 1, ADD COLUMN IF NOT EXISTS cross_location_delivery TINYINT(1) NOT NULL DEFAULT 1');
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
