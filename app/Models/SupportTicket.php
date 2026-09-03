<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

final class SupportTicket
{
    private const AUTO_CLOSE_WARNING_MESSAGE = 'This ticket will be closed if there is no reply within three working days.';

    public const CATEGORIES = [
        'Encoding Profile',
        'Encoding Transaction',
        'Reports and Analytics',
        'Account Access',
        'Location Library',
        'Data Correction',
        'System Performance',
        'System Error',
        'Other Concern',
    ];

    public static function create(array $data): int
    {
        self::ensureSchema();

        $stmt = Database::connection()->prepare("
            INSERT INTO support_tickets (reporter_id, title, category, description, screenshot_path)
            VALUES (:reporter_id, :title, :category, :description, :screenshot_path)
        ");
        $stmt->execute([
            'reporter_id' => $data['reporter_id'],
            'title' => $data['title'],
            'category' => $data['category'],
            'description' => $data['description'],
            'screenshot_path' => $data['screenshot_path'] ?: null,
        ]);

        return (int) Database::connection()->lastInsertId();
    }

    public static function forReporter(int $reporterId): array
    {
        self::ensureSchema();

        $stmt = Database::connection()->prepare("
            SELECT t.*, u.full_name AS reporter_name,
                DATE_FORMAT(t.created_at, '%b %d, %Y %h:%i %p') AS submitted_at,
                DATE_FORMAT(t.updated_at, '%b %d, %Y %h:%i %p') AS updated_label
            FROM support_tickets t
            LEFT JOIN users u ON u.id = t.reporter_id
            WHERE t.reporter_id = :reporter_id
            ORDER BY t.created_at DESC, t.id DESC
        ");
        $stmt->execute(['reporter_id' => $reporterId]);

        return self::withMessages($stmt->fetchAll());
    }

    public static function allForAdmin(): array
    {
        self::ensureSchema();

        $sql = "
            SELECT t.*, u.full_name AS reporter_name,
                DATE_FORMAT(t.created_at, '%b %d, %Y %h:%i %p') AS submitted_at,
                DATE_FORMAT(t.updated_at, '%b %d, %Y %h:%i %p') AS updated_label
            FROM support_tickets t
            LEFT JOIN users u ON u.id = t.reporter_id
            WHERE t.admin_archived = 0
            ORDER BY t.created_at DESC, t.id DESC
        ";

        return self::withMessages(Database::connection()->query($sql)->fetchAll());
    }

    public static function archivedForAdmin(): array
    {
        self::ensureSchema();
        $rows = Database::connection()->query("SELECT t.*, u.full_name AS reporter_name, DATE_FORMAT(t.created_at, '%b %d, %Y %h:%i %p') AS submitted_at, DATE_FORMAT(t.updated_at, '%b %d, %Y %h:%i %p') AS updated_label FROM support_tickets t LEFT JOIN users u ON u.id = t.reporter_id WHERE t.admin_archived = 1 ORDER BY t.updated_at DESC, t.id DESC")->fetchAll();
        return self::withMessages($rows);
    }

    public static function findVisibleTo(int $ticketId, int $userId, string $role): ?array
    {
        self::ensureSchema();

        $where = $role === 'System Admin' ? 't.id = :id' : 't.id = :id AND t.reporter_id = :user_id';
        $stmt = Database::connection()->prepare("
            SELECT t.*, u.full_name AS reporter_name
            FROM support_tickets t
            LEFT JOIN users u ON u.id = t.reporter_id
            WHERE {$where}
            LIMIT 1
        ");
        $params = ['id' => $ticketId];
        if ($role !== 'System Admin') {
            $params['user_id'] = $userId;
        }
        $stmt->execute($params);
        $ticket = $stmt->fetch();

        return $ticket ?: null;
    }

    public static function addMessage(int $ticketId, int $senderId, string $message): void
    {
        self::ensureSchema();

        $stmt = Database::connection()->prepare("
            INSERT INTO support_ticket_messages (ticket_id, sender_id, message)
            VALUES (:ticket_id, :sender_id, :message)
        ");
        $stmt->execute([
            'ticket_id' => $ticketId,
            'sender_id' => $senderId,
            'message' => $message,
        ]);

        if (!self::isSystemAdmin($senderId)) {
            $reset = Database::connection()->prepare('UPDATE support_tickets SET auto_close_warning_sent_at = NULL WHERE id = :id');
            $reset->execute(['id' => $ticketId]);
        }

        self::touch($ticketId);
    }

    public static function processAutoClosures(): void
    {
        self::ensureSchema();

        $adminId = self::defaultSystemAdminId();
        if ($adminId === null) {
            return;
        }

        self::autoCompleteExpiredTickets($adminId);
        self::warnTicketsAwaitingUserReply();
    }

    public static function markCompleted(int $ticketId, int $resolverId): void
    {
        self::ensureSchema();

        $stmt = Database::connection()->prepare("
            UPDATE support_tickets
            SET status = 'Completed', resolved_by = :resolved_by, resolved_at = CURRENT_TIMESTAMP, updated_at = CURRENT_TIMESTAMP
            WHERE id = :id
        ");
        $stmt->execute(['id' => $ticketId, 'resolved_by' => $resolverId]);
    }

    public static function markCompletedBulk(array $ids, int $resolverId): int
    {
        $ids = array_values(array_unique(array_filter(array_map('intval', $ids))));
        if ($ids === []) return 0;
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $stmt = Database::connection()->prepare("UPDATE support_tickets SET status = 'Completed', resolved_by = ?, resolved_at = CURRENT_TIMESTAMP, updated_at = CURRENT_TIMESTAMP WHERE id IN ({$placeholders}) AND admin_archived = 0");
        $stmt->execute([$resolverId, ...$ids]);
        return $stmt->rowCount();
    }

    public static function archiveBulkForAdmin(array $ids): int
    {
        $ids = array_values(array_unique(array_filter(array_map('intval', $ids))));
        if ($ids === []) return 0;
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $stmt = Database::connection()->prepare("UPDATE support_tickets SET admin_archived = 1, updated_at = CURRENT_TIMESTAMP WHERE id IN ({$placeholders})");
        $stmt->execute($ids);
        return $stmt->rowCount();
    }

    public static function archiveFor(int $ticketId, string $role): void
    {
        self::ensureSchema();

        $column = $role === 'System Admin' ? 'admin_archived' : 'reporter_archived';
        $stmt = Database::connection()->prepare("UPDATE support_tickets SET {$column} = 1, updated_at = CURRENT_TIMESTAMP WHERE id = :id");
        $stmt->execute(['id' => $ticketId]);
    }

    public static function superAdminIds(): array
    {
        $stmt = Database::connection()->query("
            SELECT id
            FROM users
            WHERE role = 'System Admin' AND is_active = 1
        ");

        return array_map('intval', array_column($stmt->fetchAll(), 'id'));
    }

    private static function withMessages(array $tickets): array
    {
        foreach ($tickets as &$ticket) {
            $ticket['messages'] = self::messages((int) $ticket['id']);
        }
        unset($ticket);

        return $tickets;
    }

    private static function messages(int $ticketId): array
    {
        $stmt = Database::connection()->prepare("
            SELECT m.*, u.full_name AS sender_name, u.role AS sender_role,
                DATE_FORMAT(m.created_at, '%b %d, %Y %h:%i %p') AS sent_at
            FROM support_ticket_messages m
            JOIN users u ON u.id = m.sender_id
            WHERE m.ticket_id = :ticket_id
            ORDER BY m.created_at ASC, m.id ASC
        ");
        $stmt->execute(['ticket_id' => $ticketId]);

        return $stmt->fetchAll();
    }

    private static function warnTicketsAwaitingUserReply(): void
    {
        $stmt = Database::connection()->query("
            SELECT t.id, t.title, t.reporter_id, latest.sender_id, latest.created_at AS last_message_at
            FROM support_tickets t
            JOIN (
                SELECT m.*
                FROM support_ticket_messages m
                JOIN (
                    SELECT ticket_id, MAX(id) AS latest_message_id
                    FROM support_ticket_messages
                    GROUP BY ticket_id
                ) last_message ON last_message.latest_message_id = m.id
            ) latest ON latest.ticket_id = t.id
            JOIN users u ON u.id = latest.sender_id
            WHERE t.status <> 'Completed'
                AND t.auto_close_warning_sent_at IS NULL
                AND u.role = 'System Admin'
        ");

        $insert = Database::connection()->prepare("
            INSERT INTO support_ticket_messages (ticket_id, sender_id, message)
            VALUES (:ticket_id, :sender_id, :message)
        ");
        $markWarned = Database::connection()->prepare("
            UPDATE support_tickets
            SET auto_close_warning_sent_at = CURRENT_TIMESTAMP, updated_at = CURRENT_TIMESTAMP
            WHERE id = :id AND auto_close_warning_sent_at IS NULL AND status <> 'Completed'
        ");

        foreach ($stmt->fetchAll() as $ticket) {
            if (self::workingDaysElapsed((string) $ticket['last_message_at']) < 3) {
                continue;
            }

            $ticketId = (int) $ticket['id'];
            $insert->execute([
                'ticket_id' => $ticketId,
                'sender_id' => (int) $ticket['sender_id'],
                'message' => self::AUTO_CLOSE_WARNING_MESSAGE,
            ]);
            $markWarned->execute(['id' => $ticketId]);

            if (!empty($ticket['reporter_id'])) {
                Notification::add(
                    'Your tech support ticket will be closed if there is no reply within three working days: ' . $ticket['title'] . '.',
                    (int) $ticket['reporter_id'],
                    'index.php?page=tech-support',
                    'tech_support'
                );
            }
        }
    }

    private static function autoCompleteExpiredTickets(int $resolverId): void
    {
        $stmt = Database::connection()->query("
            SELECT t.id, t.title, t.reporter_id, t.auto_close_warning_sent_at
            FROM support_tickets t
            WHERE t.status <> 'Completed'
                AND t.auto_close_warning_sent_at IS NOT NULL
                AND NOT EXISTS (
                    SELECT 1
                    FROM support_ticket_messages m
                    JOIN users u ON u.id = m.sender_id
                    WHERE m.ticket_id = t.id
                        AND m.created_at > t.auto_close_warning_sent_at
                        AND u.role <> 'System Admin'
                )
        ");

        foreach ($stmt->fetchAll() as $ticket) {
            if (self::workingDaysElapsed((string) $ticket['auto_close_warning_sent_at']) < 3) {
                continue;
            }

            self::markCompleted((int) $ticket['id'], $resolverId);

            if (!empty($ticket['reporter_id'])) {
                Notification::add(
                    'Your tech support ticket was automatically marked completed due to no user reply: ' . $ticket['title'] . '.',
                    (int) $ticket['reporter_id'],
                    'index.php?page=tech-support',
                    'tech_support'
                );
            }
        }
    }

    private static function workingDaysElapsed(string $startDateTime): int
    {
        try {
            $start = new \DateTimeImmutable($startDateTime);
        } catch (\Exception) {
            return 0;
        }

        $cursor = $start->modify('tomorrow')->setTime(0, 0);
        $today = (new \DateTimeImmutable())->setTime(0, 0);
        $days = 0;

        while ($cursor <= $today) {
            if ((int) $cursor->format('N') < 6) {
                $days++;
            }
            $cursor = $cursor->modify('+1 day');
        }

        return $days;
    }

    private static function defaultSystemAdminId(): ?int
    {
        $stmt = Database::connection()->query("
            SELECT id
            FROM users
            WHERE role = 'System Admin' AND is_active = 1
            ORDER BY id ASC
            LIMIT 1
        ");
        $id = $stmt->fetchColumn();

        return $id !== false ? (int) $id : null;
    }

    private static function isSystemAdmin(int $userId): bool
    {
        $stmt = Database::connection()->prepare("SELECT role FROM users WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => $userId]);

        return $stmt->fetchColumn() === 'System Admin';
    }

    private static function touch(int $ticketId): void
    {
        $stmt = Database::connection()->prepare('UPDATE support_tickets SET updated_at = CURRENT_TIMESTAMP WHERE id = :id');
        $stmt->execute(['id' => $ticketId]);
    }

    private static function ensureSchema(): void
    {
        static $ready = false;
        if ($ready) {
            return;
        }

        $db = Database::connection();
        $db->exec("
            CREATE TABLE IF NOT EXISTS support_tickets (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                reporter_id BIGINT UNSIGNED NULL,
                title VARCHAR(180) NOT NULL,
                category VARCHAR(80) NOT NULL,
                description TEXT NOT NULL,
                screenshot_path VARCHAR(255),
                status VARCHAR(30) NOT NULL DEFAULT 'Open',
                reporter_archived BOOLEAN NOT NULL DEFAULT FALSE,
                admin_archived BOOLEAN NOT NULL DEFAULT FALSE,
                auto_close_warning_sent_at TIMESTAMP NULL,
                resolved_by BIGINT UNSIGNED NULL,
                resolved_at TIMESTAMP NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX support_tickets_reporter_idx (reporter_id),
                INDEX support_tickets_status_idx (status),
                FOREIGN KEY (reporter_id) REFERENCES users(id),
                FOREIGN KEY (resolved_by) REFERENCES users(id)
            )
        ");
        $db->exec('ALTER TABLE support_tickets MODIFY reporter_id BIGINT UNSIGNED NULL');
        $db->exec('ALTER TABLE support_tickets ADD COLUMN IF NOT EXISTS auto_close_warning_sent_at TIMESTAMP NULL');
        $db->exec("
            CREATE TABLE IF NOT EXISTS support_ticket_messages (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                ticket_id BIGINT UNSIGNED NOT NULL,
                sender_id BIGINT UNSIGNED NOT NULL,
                message TEXT NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX support_ticket_messages_ticket_idx (ticket_id),
                FOREIGN KEY (ticket_id) REFERENCES support_tickets(id) ON DELETE CASCADE,
                FOREIGN KEY (sender_id) REFERENCES users(id)
            )
        ");

        $ready = true;
    }
}
