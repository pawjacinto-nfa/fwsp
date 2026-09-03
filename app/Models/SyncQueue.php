<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

/** Durable local outbox. A sync worker may safely retry any row by client_key. */
final class SyncQueue
{
    public static function enqueue(string $entityType, string $clientKey, string $operation, array $payload): void
    {
        self::ensureSchema();
        $stmt = Database::connection()->prepare(
            "INSERT INTO sync_queue (entity_type, client_key, operation_name, payload_json, sync_status, queued_at)
             VALUES (:entity_type, :client_key, :operation_name, :payload_json, 'pending', NOW())
             ON DUPLICATE KEY UPDATE operation_name=VALUES(operation_name), payload_json=VALUES(payload_json), sync_status='pending', queued_at=NOW(), last_error=NULL"
        );
        $stmt->execute([
            'entity_type' => $entityType,
            'client_key' => $clientKey,
            'operation_name' => $operation,
            'payload_json' => json_encode($payload, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE),
        ]);
    }

    public static function pendingCount(): int
    {
        self::ensureSchema();
        return (int) Database::connection()->query("SELECT COUNT(*) FROM sync_queue WHERE sync_status IN ('pending', 'uploading', 'failed')")->fetchColumn();
    }

    private static function ensureSchema(): void
    {
        Database::connection()->exec("CREATE TABLE IF NOT EXISTS sync_queue (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
            entity_type VARCHAR(64) NOT NULL,
            client_key VARCHAR(191) NOT NULL,
            operation_name VARCHAR(32) NOT NULL,
            payload_json LONGTEXT NOT NULL,
            sync_status ENUM('pending','uploading','uploaded','failed','conflict') NOT NULL DEFAULT 'pending',
            queued_at DATETIME NOT NULL,
            uploaded_at DATETIME NULL,
            last_error TEXT NULL,
            UNIQUE KEY sync_queue_entity_key (entity_type, client_key)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    }
}
