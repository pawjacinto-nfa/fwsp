<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

final class SystemSetting
{
    private const MAINTENANCE_MODE = 'maintenance_mode';

    public static function maintenanceModeEnabled(): bool
    {
        self::ensureSchema();
        $stmt = Database::connection()->prepare(
            'SELECT setting_value FROM system_settings WHERE setting_key = :setting_key LIMIT 1'
        );
        $stmt->execute(['setting_key' => self::MAINTENANCE_MODE]);

        return $stmt->fetchColumn() === '1';
    }

    public static function setMaintenanceMode(bool $enabled): void
    {
        self::ensureSchema();
        $stmt = Database::connection()->prepare(
            'INSERT INTO system_settings (setting_key, setting_value)
             VALUES (:setting_key, :setting_value)
             ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)'
        );
        $stmt->execute([
            'setting_key' => self::MAINTENANCE_MODE,
            'setting_value' => $enabled ? '1' : '0',
        ]);
    }

    private static function ensureSchema(): void
    {
        static $ready = false;
        if ($ready) {
            return;
        }

        Database::connection()->exec(
            'CREATE TABLE IF NOT EXISTS system_settings (
                setting_key VARCHAR(80) PRIMARY KEY,
                setting_value VARCHAR(255) NOT NULL
            )'
        );
        Database::connection()->exec(
            "INSERT IGNORE INTO system_settings (setting_key, setting_value)
             VALUES ('maintenance_mode', '0')"
        );
        $ready = true;
    }
}
