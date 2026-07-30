<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

final class RecordVersion
{
    public static function record(string $entity, int $recordId, array $before, array $after): void
    {
        self::ensureSchema();
        $changes = [];
        foreach ($after as $field => $value) {
            if (in_array($field, ['id', 'created_at', 'updated_at', 'photo_path'], true)) continue;
            $old = self::value($before[$field] ?? null);
            $new = self::value($value);
            if ($old !== $new) $changes[$field] = ['from' => $old, 'to' => $new];
        }
        if ($changes === []) return;
        $stmt = Database::connection()->prepare('INSERT INTO record_versions (entity_type, record_id, changes, changed_by) VALUES (:entity, :record_id, :changes, :changed_by)');
        $stmt->execute([
            'entity' => $entity,
            'record_id' => $recordId,
            'changes' => json_encode($changes, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR),
            'changed_by' => $_SESSION['user_id'] ?? null,
        ]);
    }

    public static function forRecord(string $entity, int $recordId): array
    {
        self::ensureSchema();
        $stmt = Database::connection()->prepare('SELECT rv.*, COALESCE(u.full_name, u.username, "System") AS changed_by_name FROM record_versions rv LEFT JOIN users u ON u.id = rv.changed_by WHERE rv.entity_type = :entity AND rv.record_id = :record_id ORDER BY rv.created_at DESC, rv.id DESC');
        $stmt->execute(['entity' => $entity, 'record_id' => $recordId]);
        return array_map(function (array $row): array { $row['changes'] = json_decode($row['changes'], true) ?: []; return $row; }, $stmt->fetchAll());
    }

    private static function value(mixed $value): string
    {
        if (is_array($value)) {
            if ($value === []) return '';
            $containsNestedValues = array_filter($value, static fn (mixed $item): bool => is_array($item) || is_object($item)) !== [];
            if (!$containsNestedValues) {
                return implode(', ', array_map([self::class, 'value'], $value));
            }

            return json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
        }
        if (is_bool($value)) return $value ? 'Yes' : 'No';
        if ($value === null) return '';
        return (string) $value;
    }

    private static function ensureSchema(): void
    {
        static $ready = false;
        if ($ready) return;

        Database::connection()->exec('CREATE TABLE IF NOT EXISTS record_versions (id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, entity_type VARCHAR(40) NOT NULL, record_id BIGINT UNSIGNED NOT NULL, changes JSON NOT NULL, changed_by BIGINT UNSIGNED NULL, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, INDEX record_versions_lookup (entity_type, record_id, created_at), FOREIGN KEY (changed_by) REFERENCES users(id) ON DELETE SET NULL) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');
        $ready = true;
    }
}
