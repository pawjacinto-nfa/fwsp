<?php
declare(strict_types=1);
namespace App\Support;

/** All web requests participate, including reads that run schema migrations. */
final class DatabaseMaintenanceGate
{
    private static $handle = null;
    public static function shared(): bool
    {
        self::$handle = fopen(DATA_PATH . '/database-operation.lock', 'c+');
        if (!self::$handle) throw new \RuntimeException('Cannot open database operation lock.');
        return flock(self::$handle, LOCK_SH | LOCK_NB);
    }
    public static function exclusive(): void
    {
        if (!self::$handle) self::$handle = fopen(DATA_PATH . '/database-operation.lock', 'c+');
        if (!self::$handle) throw new \RuntimeException('Cannot open database operation lock.');
        flock(self::$handle, LOCK_UN);
        $until = microtime(true) + 15;
        do {
            if (flock(self::$handle, LOCK_EX | LOCK_NB)) return;
            usleep(100000);
        } while (microtime(true) < $until);
        throw new \InvalidArgumentException('Other requests are still running. Wait a moment, then retry. No database replacement has started.');
    }
}
