<?php
declare(strict_types=1);
namespace App\Models;

use App\Core\Database;
use App\Support\SqlBackupReader;
use InvalidArgumentException;
use PDO;
use RuntimeException;
use ZipArchive;

/** Portable logical backups. Restore executes parameterized data statements, never uploaded SQL. */
final class DatabaseBackup
{
    public const MAX_UPLOAD = 256 * 1024 * 1024;
    private const MAX_EXPANDED = 2 * 1024 * 1024 * 1024;
    private const MAX_ROW = 32 * 1024 * 1024;

    public static function epoch(): string
    {
        return (string) (Database::connection()->query("SELECT setting_value FROM system_settings WHERE setting_key='database_restore_epoch'")->fetchColumn() ?: '');
    }

    public static function directory(): string
    {
        $path = getenv('FSR_BACKUP_PATH') ?: dirname(BASE_PATH, 2) . '/fsr-private/database-backups';
        if (!is_dir($path) && !mkdir($path, 0700, true)) throw new RuntimeException('Backup storage could not be created.');
        if (!is_writable($path)) throw new RuntimeException('Backup storage is not writable.');
        if (!is_file($path . '/.htaccess')) self::write($path . '/.htaccess', "Require all denied\n");
        return $path;
    }
    public static function path(string $id, string $extension = 'zip'): string
    {
        if (!preg_match('/^[a-f0-9]{32}$/D', $id) || !in_array($extension, ['zip', 'json', 'sql'], true)) throw new InvalidArgumentException('Invalid backup reference.');
        return self::directory() . '/' . $id . '.' . $extension;
    }
    private static function write(string $path, string $content): void
    {
        if (file_put_contents($path, $content, LOCK_EX) !== strlen($content)) throw new RuntimeException('Backup storage is full or unavailable.');
    }
    private static function put($stream, string $content): void
    {
        if (fwrite($stream, $content) !== strlen($content)) throw new RuntimeException('Backup storage is full or unavailable.');
    }
    public static function save(array $record): void
    {
        $file = self::path($record['id'], 'json');
        self::write($file . '.tmp', json_encode($record, JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR));
        if (!rename($file . '.tmp', $file)) throw new RuntimeException('Cannot save backup status.');
    }
    public static function record(string $id): array
    {
        $path = self::path($id, 'json');
        if (!is_file($path)) throw new InvalidArgumentException('The backup reference was not found.');
        return json_decode(file_get_contents($path), true, 32, JSON_THROW_ON_ERROR);
    }
    public static function history(): array
    {
        $records = [];
        foreach (glob(self::directory() . '/*.json') ?: [] as $file) {
            try { $row = json_decode(file_get_contents($file), true, 32, JSON_THROW_ON_ERROR); if (isset($row['id'], $row['created_at'], $row['kind'])) $records[] = $row; } catch (\Throwable) { }
        }
        usort($records, static fn ($a, $b) => strcmp($b['created_at'], $a['created_at']));
        return array_slice($records, 0, 30);
    }
    public static function schema(PDO $db): array
    {
        $tables = $db->query("SELECT TABLE_NAME, ENGINE, TABLE_TYPE FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() ORDER BY TABLE_NAME")->fetchAll();
        if (!$tables) throw new InvalidArgumentException('No database tables are available.');
        if ((int) $db->query('SELECT COUNT(*) FROM information_schema.TRIGGERS WHERE TRIGGER_SCHEMA=DATABASE()')->fetchColumn() > 0) throw new InvalidArgumentException('Databases with triggers require a hosting-admin backup and restore.');
        foreach (['ROUTINES'=>'ROUTINE_SCHEMA', 'EVENTS'=>'EVENT_SCHEMA'] as $object=>$field) {
            if ((int) $db->query("SELECT COUNT(*) FROM information_schema.$object WHERE $field=DATABASE()")->fetchColumn()) throw new InvalidArgumentException('Stored routines and events require a hosting-admin backup.');
        }
        $schema = [];
        foreach ($tables as $table) {
            $name = SqlBackupReader::identifier($table['TABLE_NAME']);
            if ($table['TABLE_TYPE'] !== 'BASE TABLE' || $table['ENGINE'] !== 'InnoDB') throw new InvalidArgumentException('This wizard requires InnoDB tables without views. Contact the hosting administrator for this database.');
            $columns = $db->query("SHOW FULL COLUMNS FROM `$name`")->fetchAll();
            $writable = array_values(array_filter($columns, static fn ($column) => stripos($column['Extra'], 'GENERATED') === false));
            $ddl = $db->query("SHOW CREATE TABLE `$name`")->fetch(PDO::FETCH_NUM)[1];
            $schema[$name] = ['columns' => array_column($writable, 'Field'), 'definition_columns'=>array_column($columns, 'Field'), 'types' => array_column($columns, 'Type', 'Field'), 'ddl' => $ddl,
                'signature' => hash('sha256', preg_replace('/ AUTO_INCREMENT=\d+/', '', $ddl))];
        }
        return $schema;
    }
    private static function encode(array $row): string
    {
        return json_encode(array_map(static fn ($v) => $v === null ? null : base64_encode((string) $v), $row), JSON_THROW_ON_ERROR) . "\n";
    }
    private static function decode(string $line, int $columns): array
    {
        $row = json_decode($line, true, 4, JSON_THROW_ON_ERROR);
        if (!is_array($row) || !array_is_list($row) || count($row) !== $columns) throw new InvalidArgumentException('The backup contains a malformed data row.');
        return array_map(static function ($value) {
            if ($value === null) return null;
            if (!is_string($value) || ($decoded = base64_decode($value, true)) === false) throw new InvalidArgumentException('The backup contains invalid data encoding.');
            return $decoded;
        }, $row);
    }
    private static function manifest(PDO $db, array $schema): array
    {
        return ['format' => 'nfa-fsr-backup', 'version' => 1, 'timezone'=>'+00:00', 'created_at' => gmdate('c'), 'database' => $db->query('SELECT DATABASE()')->fetchColumn(), 'tables' => $schema];
    }
    private static function finish(string $id, array $manifest, array $files, string $kind, int $actor, ?string $sql = null): array
    {
        $archive = self::path($id); $zip = new ZipArchive();
        if ($zip->open($archive . '.part', ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) throw new RuntimeException('Cannot create the backup archive.');
        try {
            foreach ($files as $table => $file) {
                $manifest['tables'][$table]['sha256'] = hash_file('sha256', $file);
                if (!$zip->addFile($file, 'tables/' . $table . '.jsonl')) throw new RuntimeException('Cannot add backup data.');
            }
            if ($sql !== null && !$zip->addFile($sql, 'database.sql')) throw new RuntimeException('Cannot add SQL export.');
            if (!$zip->addFromString('manifest.json', json_encode($manifest, JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR))) throw new RuntimeException('Cannot add backup manifest.');
            if (!$zip->close()) throw new RuntimeException('Cannot finish the backup archive.');
            if (!rename($archive . '.part', $archive)) throw new RuntimeException('Cannot finalize the backup file.');
        } catch (\Throwable $e) { try { $zip->close(); } catch (\Throwable) { } throw $e; }
        $record = ['id' => $id, 'kind' => $kind, 'created_at' => $manifest['created_at'], 'actor_id' => $actor,
            'database' => $manifest['database'], 'tables' => count($files), 'rows' => array_sum(array_column($manifest['tables'], 'rows')),
            'bytes' => filesize($archive), 'sha256' => hash_file('sha256', $archive), 'status' => $kind === 'upload' ? 'review' : 'ready'];
        self::inspect($archive, $manifest['tables']);
        self::save($record);
        return $record;
    }
    public static function create(int $actor, string $kind = 'export', ?PDO $db = null): array
    {
        $db ??= Database::connection(); $schema = self::schema($db); $id = bin2hex(random_bytes(16));
        $manifest = self::manifest($db, $schema); $files = []; $expanded = 0; $sqlPath = self::path($id, 'sql') . '.part';
        $sql = fopen($sqlPath, 'wb');
        if (!$sql) throw new RuntimeException('Cannot write SQL export.');
        $oldTimezone = (string) $db->query('SELECT @@SESSION.time_zone')->fetchColumn();
        $db->exec("SET SESSION time_zone='+00:00'");
        $db->exec('SET TRANSACTION ISOLATION LEVEL REPEATABLE READ'); $db->beginTransaction();
        try {
            self::put($sql, "-- NFA FSR database export; includes confidential personal data.\nSET NAMES utf8mb4;\nSET time_zone='+00:00';\nSET FOREIGN_KEY_CHECKS=0;\nSET SQL_MODE='NO_AUTO_VALUE_ON_ZERO';\n");
            $db->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, false);
            foreach ($schema as $name => $meta) {
                $file = self::directory() . '/' . $id . '-' . $name . '.part'; $files[$name] = $file;
                $out = fopen($file, 'wb'); if (!$out) throw new RuntimeException('Cannot write table data.');
                self::put($sql, "\nDROP TABLE IF EXISTS `$name`;\n" . $meta['ddl'] . ";\n");
                $prefix = "INSERT INTO `$name` (`" . implode('`,`', $meta['columns']) . "`) VALUES (";
                $count = 0; $stmt = $db->query('SELECT `' . implode('`,`', $meta['columns']) . "` FROM `$name`");
                try {
                    while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
                        $line = self::encode($row);
                        $insert = $prefix . implode(',', array_map(static fn ($v) => $v === null ? 'NULL' : "X'" . bin2hex((string) $v) . "'", $row)) . ");\n";
                        $expanded += strlen($line) + strlen($insert);
                        if (strlen($line) > self::MAX_ROW || strlen($insert) > self::MAX_ROW || $expanded > self::MAX_EXPANDED) throw new InvalidArgumentException('This database exceeds the wizard size limits. Use a hosting-admin backup.');
                        self::put($out, $line);
                        self::put($sql, $insert);
                        $count++;
                    }
                } finally { $stmt->closeCursor(); fclose($out); }
                $manifest['tables'][$name]['rows'] = $count;
            }
            self::put($sql, "SET FOREIGN_KEY_CHECKS=1;\n"); fclose($sql); $sql = null;
            $db->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, true);
            $db->commit();
            if (array_column(self::schema($db), 'signature') !== array_column($schema, 'signature')) throw new InvalidArgumentException('The table structure changed during export. Retry when maintenance work has stopped.');
            return self::finish($id, $manifest, $files, $kind, $actor, $sqlPath);
        } finally {
            if ($db->inTransaction()) $db->rollBack();
            $db->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, true);
            if (is_resource($sql)) fclose($sql);
            foreach (array_merge(array_values($files), [$sqlPath]) as $file) if (is_file($file)) unlink($file);
            $db->exec('SET SESSION time_zone=' . $db->quote($oldTimezone));
        }
    }
    public static function inspect(string $file, array $schema): array
    {
        $zip = new ZipArchive();
        if ($zip->open($file, ZipArchive::RDONLY) !== true) throw new InvalidArgumentException('The file is not a readable ZIP backup.');
        try {
            $total = 0; $names = [];
            for ($i = 0; $i < $zip->numFiles; $i++) {
                $stat = $zip->statIndex($i); $name = $stat['name'];
                if (isset($names[$name]) || !preg_match('#^(manifest\.json|database\.sql|tables/[a-zA-Z0-9_]+\.jsonl)$#D', $name)) throw new InvalidArgumentException('The archive contains duplicate or unexpected files.');
                $names[$name] = true; $total += $stat['size'];
                if ($total > self::MAX_EXPANDED) throw new InvalidArgumentException('The expanded backup exceeds the 2 GB limit.');
            }
            $stat = $zip->statName('manifest.json');
            if (!$stat || $stat['size'] > 2 * 1024 * 1024) throw new InvalidArgumentException('The FSR backup manifest is missing or too large.');
            $manifest = json_decode($zip->getFromName('manifest.json'), true, 32, JSON_THROW_ON_ERROR);
            if (($manifest['format'] ?? '') !== 'nfa-fsr-backup' || ($manifest['version'] ?? 0) !== 1 || ($manifest['timezone'] ?? '') !== '+00:00' || !is_array($manifest['tables'] ?? null)) throw new InvalidArgumentException('This ZIP is not a supported FSR database backup.');
            if (!is_string($manifest['database'] ?? null) || strlen($manifest['database']) > 128 || !is_string($manifest['created_at'] ?? null) || strlen($manifest['created_at']) > 64 || strtotime($manifest['created_at']) === false) throw new InvalidArgumentException('The backup metadata is invalid.');
            $keys = array_keys($manifest['tables']); $current = array_keys($schema); sort($keys); sort($current);
            if ($keys !== $current) throw new InvalidArgumentException('The backup table list differs from this application. Restore a backup from the same database structure.');
            if (count($names) !== count($keys) + 1 + (int) isset($names['database.sql'])) throw new InvalidArgumentException('Unexpected table files in the backup.');
            foreach ($manifest['tables'] as $name => $meta) {
                if (($meta['signature'] ?? '') !== $schema[$name]['signature'] || ($meta['columns'] ?? null) !== $schema[$name]['columns']) throw new InvalidArgumentException("Table structure differs for $name. Schema migrations must be handled before restoring data.");
                $input = $zip->getStream('tables/' . $name . '.jsonl');
                if (!$input) throw new InvalidArgumentException("Missing data for $name.");
                $hash = hash_init('sha256'); $count = 0;
                try {
                    while (($line = fgets($input, self::MAX_ROW + 1)) !== false) {
                        if (!str_ends_with($line, "\n")) throw new InvalidArgumentException('A data row is too large or incomplete.');
                        hash_update($hash, $line); self::decode($line, count($meta['columns'])); $count++;
                    }
                } finally { fclose($input); }
                if (!is_int($meta['rows'] ?? null) || $count !== $meta['rows'] || !hash_equals(hash_final($hash), (string) ($meta['sha256'] ?? ''))) throw new InvalidArgumentException("Data verification failed for $name. The backup may be incomplete or changed.");
            }
            return $manifest;
        } finally { $zip->close(); }
    }
    public static function stage(string $file, string $extension, int $actor): array
    {
        $schema = self::schema(Database::connection());
        if ($extension === 'sql') return self::convertSql($file, $schema, $actor);
        if ($extension !== 'zip') throw new InvalidArgumentException('Select an FSR .zip backup or a compatible .sql export.');
        $manifest = self::inspect($file, $schema); $id = bin2hex(random_bytes(16));
        if (!copy($file, self::path($id))) throw new RuntimeException('Cannot store uploaded backup.');
        $record = ['id'=>$id, 'kind'=>'upload', 'created_at'=>gmdate('c'), 'backup_date'=>$manifest['created_at'], 'actor_id'=>$actor,
            'database'=>$manifest['database'], 'tables'=>count($schema), 'rows'=>array_sum(array_column($manifest['tables'], 'rows')),
            'bytes'=>filesize(self::path($id)), 'sha256'=>hash_file('sha256',self::path($id)), 'status'=>'review'];
        self::save($record); return $record;
    }
    private static function convertSql(string $file, array $schema, int $actor): array
    {
        $id = bin2hex(random_bytes(16)); $files = []; $handles = []; $seen = []; $expanded = 0;
        $manifest = self::manifest(Database::connection(), $schema);
        foreach ($schema as $name => $meta) {
            $files[$name] = self::directory() . '/' . $id . '-' . $name . '.part';
            $handles[$name] = fopen($files[$name], 'wb');
            if (!$handles[$name]) throw new RuntimeException('Cannot stage SQL data.');
            $manifest['tables'][$name]['rows'] = 0;
        }
        try {
            foreach (SqlBackupReader::statements($file) as $sql) {
                if (preg_match('/^CREATE TABLE\s+(?:IF NOT EXISTS\s+)?(`[^`]+`|[a-zA-Z0-9_]+)\s*\((.*)\)\s*[^;]*$/isD', $sql, $m)) {
                    $name = SqlBackupReader::identifier($m[1]);
                    if (!isset($schema[$name]) || isset($seen[$name])) throw new InvalidArgumentException('The SQL contains unknown or repeated table definitions.');
                    $cols = [];
                    foreach (SqlBackupReader::splitDefinitions($m[2]) as $definition) {
                        if (preg_match('/^(PRIMARY|UNIQUE|KEY|INDEX|CONSTRAINT|CHECK|FULLTEXT|SPATIAL)\b/i', $definition)) continue;
                        if (!preg_match('/^(`[^`]+`|[a-zA-Z0-9_]+)\s+([a-z]+(?:\s*\([^)]*\))?(?:\s+unsigned)?)/i', $definition, $cm)) throw new InvalidArgumentException('Unsupported SQL column definition.');
                        $column = SqlBackupReader::identifier($cm[1]); $cols[] = $column;
                        $normalize = static fn ($type) => preg_replace('/\b(tinyint|smallint|mediumint|int|bigint)\(\d+\)/', '$1', strtolower(preg_replace('/\s+/', '', $type)));
                        if (!isset($schema[$name]['types'][$column]) || $normalize($cm[2]) !== $normalize($schema[$name]['types'][$column])) throw new InvalidArgumentException("Column type differs for $name.$column. The wizard does not apply schema changes.");
                    }
                    if ($cols !== $schema[$name]['definition_columns']) throw new InvalidArgumentException("Column list differs for $name.");
                    $seen[$name] = true;
                } elseif (preg_match('/^INSERT INTO\s+(`[^`]+`|[a-zA-Z0-9_]+)\s*(?:\(([^)]*)\))?\s*VALUES\s*(.+)$/isD', $sql, $m)) {
                    $name = SqlBackupReader::identifier($m[1]);
                    if (!isset($schema[$name])) throw new InvalidArgumentException('The SQL writes to an unknown table.');
                    $cols = !empty($m[2]) ? array_map([SqlBackupReader::class, 'identifier'], explode(',', $m[2])) : $schema[$name]['definition_columns'];
                    if ($cols !== $schema[$name]['columns'] && $cols !== $schema[$name]['definition_columns']) throw new InvalidArgumentException("INSERT columns differ for $name. Export all columns in table order.");
                    foreach (SqlBackupReader::rows($m[3]) as $row) {
                        if (count($row) !== count($cols)) throw new InvalidArgumentException("Wrong number of values for $name.");
                        $mapped = array_combine($cols, $row);
                        $row = array_map(static fn ($column) => $mapped[$column], $schema[$name]['columns']);
                        $line = self::encode($row); $expanded += strlen($line);
                        if (strlen($line) > self::MAX_ROW || $expanded > self::MAX_EXPANDED) throw new InvalidArgumentException('Converted SQL exceeds the supported backup size.');
                        self::put($handles[$name], $line); $manifest['tables'][$name]['rows']++;
                    }
                } elseif (preg_match('/^(SET\s|START TRANSACTION$|COMMIT$|BEGIN$|LOCK TABLES\s|UNLOCK TABLES$|USE\s)/i', $sql)) {
                    if (stripos($sql, 'NO_BACKSLASH_ESCAPES') !== false) throw new InvalidArgumentException('SQL using NO_BACKSLASH_ESCAPES is not supported. Use the wizard ZIP format.');
                    if (preg_match('/^SET\s+(?:SESSION\s+)?time_zone\s*=\s*([\x27\x22])([^\x27\x22]+)\1/i', $sql, $zone) && $zone[2] !== '+00:00') throw new InvalidArgumentException('Export SQL in UTC (time_zone +00:00) before importing.');
                    // Session / database directives are never executed.
                } elseif (preg_match('/^(?:DROP TABLE(?: IF EXISTS)?|ALTER TABLE)\s+(`[^`]+`|[a-zA-Z0-9_]+)(.*)$/isD', $sql, $m)) {
                    $name = SqlBackupReader::identifier($m[1]);
                    if (!isset($schema[$name]) || str_contains($m[2], '.')) throw new InvalidArgumentException('Unsupported table directive in SQL export.');
                    // Existing schema is preserved, including indexes and AUTO_INCREMENT counters.
                } else throw new InvalidArgumentException('Unsupported SQL statement. Use a complete plain SQL table export without routines, triggers, views, or custom commands.');
            }
            if (count($seen) !== count($schema)) throw new InvalidArgumentException('The SQL must contain every table definition, including empty tables. Partial imports are not supported.');
            foreach ($handles as $handle) fclose($handle); $handles = [];
            return self::finish($id, $manifest, $files, 'upload', $actor);
        } finally {
            foreach ($handles as $handle) fclose($handle);
            foreach ($files as $file) if (is_file($file)) unlink($file);
        }
    }
    public static function restore(string $id, int $actor, string $username, ?PDO $db = null): array
    {
        $db ??= Database::connection(); $record = self::record($id);
        if ($record['kind'] !== 'upload' || $record['status'] !== 'review' || $record['actor_id'] !== $actor || strtotime($record['created_at']) < time() - 3600) throw new InvalidArgumentException('Upload and review a fresh backup before restoring. Reviews expire after one hour.');
        if (!hash_equals($record['sha256'], hash_file('sha256', self::path($id)))) throw new InvalidArgumentException('The uploaded file changed after preview. Upload it again.');
        $schema = self::schema($db); $manifest = self::inspect(self::path($id), $schema);
        $setting = $db->query("SELECT setting_key,setting_value FROM system_settings WHERE setting_key IN ('maintenance_mode','maintenance_schedule')")->fetchAll(PDO::FETCH_KEY_PAIR);
        if (($setting['maintenance_mode'] ?? '') !== '1' || (!empty($setting['maintenance_schedule']) && strtotime($setting['maintenance_schedule']) > time())) throw new InvalidArgumentException('Turn on maintenance now before restoring. A future schedule is not sufficient.');
        $recovery = self::create($actor, 'recovery', $db);
        $record['recovery_id'] = $recovery['id']; $record['status'] = 'restoring'; self::save($record);
        $zip = new ZipArchive(); if ($zip->open(self::path($id), ZipArchive::RDONLY) !== true) throw new RuntimeException('Cannot reopen validated backup.');
        $oldMode = (string) $db->query('SELECT @@SESSION.sql_mode')->fetchColumn();
        $oldTimezone = (string) $db->query('SELECT @@SESSION.time_zone')->fetchColumn();
        $db->exec("SET SESSION time_zone='+00:00'");
        $db->exec("SET SESSION sql_mode='STRICT_ALL_TABLES,NO_AUTO_VALUE_ON_ZERO,NO_ENGINE_SUBSTITUTION'");
        $db->exec('SET FOREIGN_KEY_CHECKS=0'); $db->beginTransaction();
        try {
            foreach ($schema as $name => $meta) $db->exec("DELETE FROM `$name`");
            foreach ($schema as $name => $meta) {
                $input = $zip->getStream('tables/' . $name . '.jsonl');
                $stmt = $db->prepare("INSERT INTO `$name` (`" . implode('`,`', $meta['columns']) . '`) VALUES (' . implode(',', array_fill(0, count($meta['columns']), '?')) . ')');
                try {
                    while (($line = fgets($input, self::MAX_ROW + 1)) !== false) $stmt->execute(self::decode($line, count($meta['columns'])));
                } finally { fclose($input); }
                if ((int) $db->query("SELECT COUNT(*) FROM `$name`")->fetchColumn() !== $manifest['tables'][$name]['rows']) throw new RuntimeException('Restored row count does not match the backup.');
            }
            self::verifyRelations($db);
            $admin = $db->prepare("SELECT id FROM users WHERE username=:username AND role='System Admin' AND is_active=1 AND status='Active'");
            $admin->execute(['username'=>$username]); $restoredId = $admin->fetchColumn();
            if (!$restoredId) throw new InvalidArgumentException('The backup does not contain your active System Admin account. Restore was rolled back to prevent lockout.');
            $set = $db->prepare('INSERT INTO system_settings (setting_key,setting_value) VALUES (?,?) ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value)');
            foreach (['maintenance_mode'=>'1','maintenance_schedule'=>'','database_restore_epoch'=>bin2hex(random_bytes(16))] as $key=>$value) $set->execute([$key,$value]);
            // Never revive old device credentials or pending password-reset approvals.
            if (isset($schema['offline_devices'])) $db->exec("UPDATE offline_devices SET status='Revoked',revoked_at=NOW() WHERE status='Active'");
            $db->exec("UPDATE users SET password_reset_status=NULL,password_reset_token_hash=NULL,password_reset_expires_at=NULL");
            $audit = $db->prepare('INSERT INTO audit_logs (user_id,action,details) VALUES (?,?,?)');
            $audit->execute([$restoredId, 'Database restored', json_encode(['administrator'=>$username,'source_sha256'=>$record['sha256'],'recovery_backup'=>$recovery['id']], JSON_THROW_ON_ERROR)]);
            $db->commit();
        } catch (\Throwable $error) {
            if ($db->inTransaction()) $db->rollBack();
            $record['status'] = 'failed'; $record['finished_at'] = gmdate('c'); self::save($record);
            throw $error;
        } finally {
            try { $zip->close(); $db->exec('SET FOREIGN_KEY_CHECKS=1'); $db->exec('SET SESSION sql_mode=' . $db->quote($oldMode)); $db->exec('SET SESSION time_zone=' . $db->quote($oldTimezone)); }
            catch (\Throwable $cleanupError) { error_log('Database restore connection cleanup failed: ' . $id); }
        }
        $record['status'] = 'completed'; $record['finished_at'] = gmdate('c');
        try { self::save($record); } catch (\Throwable $e) { error_log('Restore committed; status file could not be updated: ' . $id); }
        return $record;
    }
    private static function verifyRelations(PDO $db): void
    {
        $relations = $db->query('SELECT TABLE_NAME,COLUMN_NAME,CONSTRAINT_NAME,REFERENCED_TABLE_SCHEMA,REFERENCED_TABLE_NAME,REFERENCED_COLUMN_NAME FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA=DATABASE() AND REFERENCED_TABLE_NAME IS NOT NULL ORDER BY TABLE_NAME,CONSTRAINT_NAME,ORDINAL_POSITION')->fetchAll();
        $groups = []; $database = $db->query('SELECT DATABASE()')->fetchColumn();
        foreach ($relations as $r) {
            if ($r['REFERENCED_TABLE_SCHEMA'] !== $database) throw new InvalidArgumentException('Cross-database relationships are not supported.');
            $groups[$r['TABLE_NAME'] . '/' . $r['CONSTRAINT_NAME']][] = $r;
        }
        foreach ($groups as $relations) {
            $table = SqlBackupReader::identifier($relations[0]['TABLE_NAME']); $parent = SqlBackupReader::identifier($relations[0]['REFERENCED_TABLE_NAME']);
            $notNull = []; $match = [];
            foreach ($relations as $r) { $col=SqlBackupReader::identifier($r['COLUMN_NAME']); $ref=SqlBackupReader::identifier($r['REFERENCED_COLUMN_NAME']); $notNull[]="c.`$col` IS NOT NULL"; $match[]="p.`$ref`=c.`$col`"; }
            if ($db->query("SELECT 1 FROM `$table` c WHERE " . implode(' AND ', $notNull) . " AND NOT EXISTS (SELECT 1 FROM `$parent` p WHERE " . implode(' AND ', $match) . ') LIMIT 1')->fetchColumn()) throw new InvalidArgumentException("The backup has broken relationships in $table. Restore was rolled back.");
        }
    }
}
