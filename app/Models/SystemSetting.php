<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

final class SystemSetting
{
    private const MAINTENANCE_MODE = 'maintenance_mode';
    private const MAINTENANCE_SCHEDULE = 'maintenance_schedule';

    public static function maintenanceModeEnabled(): bool
    {
        self::ensureSchema();
        if (self::value(self::MAINTENANCE_MODE) !== '1') return false;
        $schedule = self::maintenanceSchedule();
        return !$schedule || strtotime($schedule) <= time();
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

    public static function maintenanceSchedule(): ?string
    {
        self::ensureSchema();
        $value = self::value(self::MAINTENANCE_SCHEDULE);
        return $value !== null && strtotime($value) !== false ? $value : null;
    }

    /** A future scheduled maintenance is announced now and activated automatically at its start time. */
    public static function scheduledMaintenanceNotice(): ?string
    {
        self::ensureSchema();
        $schedule = self::maintenanceSchedule();
        if (self::value(self::MAINTENANCE_MODE) !== '1' || !$schedule || strtotime($schedule) <= time()) return null;
        return $schedule;
    }

    public static function setMaintenanceSchedule(?string $schedule): void
    {
        self::ensureSchema();
        $stmt = Database::connection()->prepare(
            'INSERT INTO system_settings (setting_key, setting_value) VALUES (:setting_key, :setting_value) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)'
        );
        $stmt->execute(['setting_key' => self::MAINTENANCE_SCHEDULE, 'setting_value' => $schedule ?? '']);
    }

    private static function value(string $key): ?string
    {
        $stmt = Database::connection()->prepare('SELECT setting_value FROM system_settings WHERE setting_key = :setting_key LIMIT 1');
        $stmt->execute(['setting_key' => $key]);
        $value = $stmt->fetchColumn();
        return $value === false ? null : (string) $value;
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
        Database::connection()->exec(
            "INSERT IGNORE INTO system_settings (setting_key, setting_value)
             VALUES ('maintenance_schedule', '')"
        );
        $ready = true;
    }
}
