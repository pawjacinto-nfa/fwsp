<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

/** Server registry for device-bound, revocable offline authorizations. */
final class OfflineDevice
{
    private const AUTHORIZATION_DAYS = 7;

    public static function issue(int $userId, string $deviceId, string $deviceName): array
    {
        self::ensureSchema();
        $user = User::find($userId);
        if (!$user || (int) $user['is_active'] !== 1) throw new \RuntimeException('This account is not active.');

        $token = bin2hex(random_bytes(32));
        $expiresAt = (new \DateTimeImmutable('+' . self::AUTHORIZATION_DAYS . ' days'))->format('Y-m-d H:i:s');
        $stmt = Database::connection()->prepare(
            "INSERT INTO offline_devices (user_id, device_id, device_name, token_hash, status, issued_at, expires_at, last_validated_at)
             VALUES (:user_id, :device_id, :device_name, :token_hash, 'Active', NOW(), :expires_at, NOW())
             ON DUPLICATE KEY UPDATE user_id=VALUES(user_id), device_name=VALUES(device_name), token_hash=VALUES(token_hash), status='Active', issued_at=NOW(), expires_at=VALUES(expires_at), last_validated_at=NOW(), revoked_at=NULL"
        );
        $stmt->execute(['user_id' => $userId, 'device_id' => $deviceId, 'device_name' => $deviceName, 'token_hash' => hash('sha256', $token), 'expires_at' => $expiresAt]);

        return ['token' => $token, 'expires_at' => $expiresAt, 'user_id' => $userId, 'role' => $user['role'], 'username' => $user['username']];
    }

    public static function forUser(int $userId): array
    {
        self::ensureSchema();
        $stmt = Database::connection()->prepare('SELECT id, device_id, device_name, status, issued_at, expires_at, last_validated_at, revoked_at FROM offline_devices WHERE user_id = :user_id ORDER BY issued_at DESC');
        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetchAll();
    }

    public static function all(): array
    {
        self::ensureSchema();
        return Database::connection()->query('SELECT od.id, od.device_id, od.device_name, od.status, od.issued_at, od.expires_at, od.last_validated_at, od.revoked_at, u.username, u.full_name FROM offline_devices od INNER JOIN users u ON u.id = od.user_id ORDER BY od.issued_at DESC')->fetchAll();
    }

    public static function revoke(int $id): bool
    {
        self::ensureSchema();
        $stmt = Database::connection()->prepare("UPDATE offline_devices SET status='Revoked', revoked_at=NOW() WHERE id = :id AND status = 'Active'");
        $stmt->execute(['id' => $id]);
        return $stmt->rowCount() > 0;
    }

    public static function validate(int $userId, string $deviceId, string $token): ?array
    {
        self::ensureSchema();
        $stmt = Database::connection()->prepare("SELECT od.id, od.device_id, od.device_name, od.token_hash, od.expires_at, u.id AS user_id, u.username, u.role FROM offline_devices od INNER JOIN users u ON u.id = od.user_id WHERE od.user_id = :user_id AND od.device_id = :device_id AND od.status = 'Active' AND od.expires_at > NOW() AND u.is_active = 1 AND u.status = 'Active' LIMIT 1");
        $stmt->execute(['user_id' => $userId, 'device_id' => $deviceId]);
        $device = $stmt->fetch();
        if (!$device || !hash_equals((string) $device['token_hash'], hash('sha256', $token))) return null;
        Database::connection()->prepare('UPDATE offline_devices SET last_validated_at = NOW() WHERE id = :id')->execute(['id' => $device['id']]);
        return ['user_id' => (int) $device['user_id'], 'device_id' => $device['device_id'], 'device_name' => $device['device_name'], 'username' => $device['username'], 'role' => $device['role'], 'expires_at' => $device['expires_at']];
    }

    public static function reserveSubmission(array $authorization, string $submissionId, string $action): array
    {
        self::ensureSchema();
        $stmt = Database::connection()->prepare("INSERT IGNORE INTO offline_submission_audit (user_id, device_id, submission_id, action_name, payload_hash, sync_status, uploaded_at) VALUES (:user_id, :device_id, :submission_id, :action_name, :payload_hash, 'Reserved', NOW())");
        $stmt->execute(['user_id' => $authorization['user_id'], 'device_id' => $authorization['device_id'], 'submission_id' => $submissionId, 'action_name' => $action, 'payload_hash' => hash('sha256', $submissionId . '|' . $action . '|' . $authorization['device_id'])]);
        if ($stmt->rowCount() === 1) return ['state' => 'ready'];

        $existing = Database::connection()->prepare('SELECT user_id, device_id, sync_status FROM offline_submission_audit WHERE submission_id = :submission_id LIMIT 1');
        $existing->execute(['submission_id' => $submissionId]);
        $row = $existing->fetch();
        if (!$row || (int) $row['user_id'] !== (int) $authorization['user_id'] || $row['device_id'] !== $authorization['device_id']) return ['state' => 'conflict'];
        return ['state' => $row['sync_status'] === 'Uploaded' ? 'uploaded' : 'review'];
    }

    public static function completeSubmission(array $authorization, string $submissionId): bool
    {
        self::ensureSchema();
        $stmt = Database::connection()->prepare("UPDATE offline_submission_audit SET sync_status = 'Uploaded', uploaded_at = NOW() WHERE submission_id = :submission_id AND user_id = :user_id AND device_id = :device_id AND sync_status = 'Reserved'");
        $stmt->execute(['submission_id' => $submissionId, 'user_id' => $authorization['user_id'], 'device_id' => $authorization['device_id']]);
        if ($stmt->rowCount() > 0) return true;
        $check = Database::connection()->prepare("SELECT sync_status FROM offline_submission_audit WHERE submission_id = :submission_id AND user_id = :user_id AND device_id = :device_id LIMIT 1");
        $check->execute(['submission_id' => $submissionId, 'user_id' => $authorization['user_id'], 'device_id' => $authorization['device_id']]);
        return $check->fetchColumn() === 'Uploaded';
    }

    public static function submissions(): array
    {
        self::ensureSchema();
        return Database::connection()->query('SELECT osa.submission_id, osa.action_name, osa.sync_status, osa.uploaded_at, osa.device_id, u.full_name, u.username FROM offline_submission_audit osa INNER JOIN users u ON u.id = osa.user_id ORDER BY osa.uploaded_at DESC')->fetchAll();
    }

    private static function ensureSchema(): void
    {
        Database::connection()->exec("CREATE TABLE IF NOT EXISTS offline_devices (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
            user_id BIGINT UNSIGNED NOT NULL,
            device_id VARCHAR(128) NOT NULL UNIQUE,
            device_name VARCHAR(160) NOT NULL,
            token_hash CHAR(64) NOT NULL,
            status ENUM('Active','Revoked') NOT NULL DEFAULT 'Active',
            issued_at DATETIME NOT NULL,
            expires_at DATETIME NOT NULL,
            last_validated_at DATETIME NULL,
            revoked_at DATETIME NULL,
            KEY offline_devices_user_status (user_id, status),
            CONSTRAINT offline_devices_user_fk FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        Database::connection()->exec("CREATE TABLE IF NOT EXISTS offline_submission_audit (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
            user_id BIGINT UNSIGNED NOT NULL,
            device_id VARCHAR(128) NOT NULL,
            submission_id VARCHAR(128) NOT NULL UNIQUE,
            action_name VARCHAR(64) NOT NULL,
            payload_hash CHAR(64) NOT NULL,
            sync_status ENUM('Reserved','Uploaded','Review') NOT NULL DEFAULT 'Reserved',
            uploaded_at DATETIME NOT NULL,
            KEY offline_submission_user_device (user_id, device_id),
            CONSTRAINT offline_submission_user_fk FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        Database::connection()->exec("ALTER TABLE offline_submission_audit ADD COLUMN IF NOT EXISTS sync_status ENUM('Reserved','Uploaded','Review') NOT NULL DEFAULT 'Reserved' AFTER payload_hash");
    }
}
