<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

final class Signatory
{
    public static function forUser(int $userId): array
    {
        self::ensureSchema();
        $stmt = Database::connection()->prepare('
            SELECT id, full_name, designation
            FROM report_signatories
            WHERE user_id = :user_id
            ORDER BY id
        ');
        $stmt->execute(['user_id' => $userId]);

        return $stmt->fetchAll();
    }

    public static function create(int $userId, string $fullName, string $designation): void
    {
        self::ensureSchema();
        $stmt = Database::connection()->prepare('
            INSERT INTO report_signatories (user_id, full_name, designation)
            VALUES (:user_id, :full_name, :designation)
        ');
        $stmt->execute([
            'user_id' => $userId,
            'full_name' => $fullName,
            'designation' => $designation,
        ]);
    }

    public static function update(int $id, int $userId, string $fullName, string $designation): bool
    {
        self::ensureSchema();
        $stmt = Database::connection()->prepare('
            UPDATE report_signatories
            SET full_name = :full_name, designation = :designation
            WHERE id = :id AND user_id = :user_id
        ');
        $stmt->execute([
            'id' => $id,
            'user_id' => $userId,
            'full_name' => $fullName,
            'designation' => $designation,
        ]);

        return $stmt->rowCount() > 0;
    }

    public static function delete(int $id, int $userId): bool
    {
        self::ensureSchema();
        $stmt = Database::connection()->prepare('DELETE FROM report_signatories WHERE id = :id AND user_id = :user_id');
        $stmt->execute(['id' => $id, 'user_id' => $userId]);

        return $stmt->rowCount() > 0;
    }

    private static function ensureSchema(): void
    {
        Database::connection()->exec('
            CREATE TABLE IF NOT EXISTS report_signatories (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                user_id BIGINT UNSIGNED NOT NULL,
                full_name VARCHAR(160) NOT NULL,
                designation VARCHAR(160) NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX report_signatories_user_idx (user_id),
                CONSTRAINT report_signatories_user_fk FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ');
    }
}
