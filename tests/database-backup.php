<?php
/** Isolated integration test. Never selects or changes the application database. */
declare(strict_types=1);
if (PHP_SAPI !== 'cli') exit;
define('BASE_PATH', dirname(__DIR__));
require BASE_PATH . '/app/Core/Database.php';
require BASE_PATH . '/app/Support/SqlBackupReader.php';
require BASE_PATH . '/app/Models/DatabaseBackup.php';
use App\Core\Database;
use App\Models\DatabaseBackup as Backup;
function check(bool $condition, string $label): void { if (!$condition) throw new RuntimeException($label); echo "PASS: $label\n"; }
function rejected(callable $operation, string $label): void {
    try { $operation(); } catch (Throwable $e) { check(true, $label); return; }
    throw new RuntimeException('Unexpected acceptance: ' . $label);
}
$config = require BASE_PATH . '/app/config/database.php';
$name = 'fsr_wizard_test_' . bin2hex(random_bytes(6));
$server = new PDO('mysql:host=' . $config['host'] . ';port=' . $config['port'], getenv('FSR_TEST_DB_USER') ?: 'root', getenv('FSR_TEST_DB_PASSWORD') ?: '', [PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]);
$server->exec("CREATE DATABASE `$name` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
putenv('FSR_DB_NAME=' . $name); putenv('FSR_DB_USER=' . (getenv('FSR_TEST_DB_USER') ?: 'root')); putenv('FSR_DB_PASSWORD=' . (getenv('FSR_TEST_DB_PASSWORD') ?: ''));
$folder = sys_get_temp_dir() . '/' . $name; putenv('FSR_BACKUP_PATH=' . $folder);
try {
    $db = Database::connection();
    $db->exec("CREATE TABLE users (id INT PRIMARY KEY,username VARCHAR(80),role VARCHAR(30),is_active INT,status VARCHAR(20),password_reset_status VARCHAR(30),password_reset_token_hash VARCHAR(80),password_reset_expires_at DATETIME) ENGINE=InnoDB");
    $db->exec("CREATE TABLE system_settings (setting_key VARCHAR(80) PRIMARY KEY,setting_value TEXT) ENGINE=InnoDB");
    $db->exec("CREATE TABLE audit_logs (id INT AUTO_INCREMENT PRIMARY KEY,user_id INT,action TEXT,details TEXT) ENGINE=InnoDB");
    $db->exec("CREATE TABLE samples (id BIGINT UNSIGNED PRIMARY KEY, label TEXT, payload BLOB, amount DECIMAL(20,4), optional_value TEXT NULL, owner INT, FOREIGN KEY(owner) REFERENCES users(id)) ENGINE=InnoDB");
    $db->exec("INSERT INTO users VALUES (1,'wizard-admin','System Admin',1,'Active','Pending','old-token',NULL)");
    $db->exec("INSERT INTO system_settings VALUES ('maintenance_mode','1')");
    $values = ['18446744073709551610', "O'Brien; — palay 🌾\nline\\next\0", "\x00\xff\x01", '1234567890123456.1234', null, 1];
    $db->prepare('INSERT INTO samples VALUES (?,?,?,?,?,?)')->execute($values);
    $db->exec('ALTER TABLE samples ADD calculated DECIMAL(22,4) GENERATED ALWAYS AS (amount * 2) STORED');
    $db->exec("ALTER TABLE samples ADD moment TIMESTAMP NULL DEFAULT NULL");
    $db->exec("UPDATE samples SET moment='2026-10-09 02:03:04'");
    $original = $db->query('SELECT * FROM samples')->fetchAll();
    $export = Backup::create(1);
    check($export['status'] === 'ready' && $export['tables'] === 4, 'Export creates verified complete archive');
    $zip = new ZipArchive(); $zip->open(Backup::path($export['id']));
    $sqlPath = $folder . '/phpmyadmin.sql'; file_put_contents($sqlPath, $zip->getFromName('database.sql')); $zip->close();
    $sql = Backup::stage($sqlPath,'sql',1);
    check($sql['rows'] === $export['rows'], 'SQL export can be imported through literal parser');
    // Execute only this test's own generated export, inside its synthetic database.
    $originalZone = $db->query('SELECT @@SESSION.time_zone')->fetchColumn();
    foreach (\App\Support\SqlBackupReader::statements($sqlPath) as $statement) $db->exec($statement);
    $db->exec('SET SESSION time_zone=' . $db->quote($originalZone));
    check($db->query('SELECT * FROM samples')->fetchAll() === $original, 'Downloaded SQL restores through the native database engine, including calculated columns and timestamps');
    $stage = Backup::stage(Backup::path($export['id']),'zip',1);
    $db->exec("UPDATE samples SET label='new current data'");
    $result = Backup::restore($stage['id'],1,'wizard-admin');
    check($result['status'] === 'completed', 'Transactional restore completes');
    check($db->query('SELECT * FROM samples')->fetchAll() === $original, 'Unicode, binary, NULL, large integers, decimals and escaping round-trip exactly');
    check(Backup::record($result['recovery_id'])['status'] === 'ready', 'Verified recovery backup exists before replacement');
    check(Backup::epoch() !== '', 'Restore rotates the session epoch');
    check($db->query("SELECT setting_value FROM system_settings WHERE setting_key='maintenance_mode'")->fetchColumn() === '1', 'Maintenance stays enabled');
    check($db->query('SELECT password_reset_token_hash FROM users')->fetchColumn() === null, 'Old password reset approvals are cleared');
    rejected(fn()=>Backup::restore($stage['id'],1,'wizard-admin'), 'Completed restore cannot be replayed');
    $stage = Backup::stage(Backup::path($export['id']),'zip',1);
    $db->exec("UPDATE samples SET label='must survive rollback'");
    rejected(fn()=>Backup::restore($stage['id'],1,'missing-admin'), 'Missing administrator rolls back the entire replacement');
    check($db->query('SELECT label FROM samples')->fetchColumn() === 'must survive rollback', 'Rollback preserves current data');
    $stage = Backup::stage(Backup::path($export['id']),'zip',1);
    $db->exec("UPDATE system_settings SET setting_value='0' WHERE setting_key='maintenance_mode'");
    rejected(fn()=>Backup::restore($stage['id'],1,'wizard-admin'), 'Restore requires immediate maintenance mode');
    $db->exec("UPDATE system_settings SET setting_value='1' WHERE setting_key='maintenance_mode'");
    $db->exec('SET FOREIGN_KEY_CHECKS=0'); $db->exec('UPDATE samples SET owner=999'); $db->exec('SET FOREIGN_KEY_CHECKS=1');
    $bad = Backup::create(1); $badStage = Backup::stage(Backup::path($bad['id']),'zip',1);
    $db->exec('UPDATE samples SET owner=1');
    rejected(fn()=>Backup::restore($badStage['id'],1,'wizard-admin'), 'Orphan relationships trigger rollback');
    check((int)$db->query('SELECT owner FROM samples')->fetchColumn() === 1, 'Foreign-key failure preserves original records');
    $db->exec('ALTER TABLE samples ADD extra INT NULL');
    rejected(fn()=>Backup::stage(Backup::path($export['id']),'zip',1), 'Schema mismatch rejected before restore');
    $db->exec('ALTER TABLE samples DROP extra');
    $malicious = file_get_contents($sqlPath) . "\nINSERT INTO samples VALUES (2,SLEEP(1),NULL,0,NULL,1);";
    file_put_contents($folder . '/bad.sql', $malicious);
    rejected(fn()=>Backup::stage($folder . '/bad.sql','sql',1), 'SQL expressions are never executed');
    file_put_contents($folder . '/bad.sql', "/*!50003 CREATE TRIGGER unsafe */;\n" . file_get_contents($sqlPath));
    rejected(fn()=>Backup::stage($folder . '/bad.sql','sql',1), 'Executable trigger comments rejected');
    $badFile=$folder.'/bad.zip'; copy(Backup::path($export['id']),$badFile); $zip->open($badFile); $zip->addFromString('../escape','bad'); $zip->close();
    rejected(fn()=>Backup::stage($badFile,'zip',1), 'Archive traversal entries rejected');
    $zip->open($badFile); $zip->deleteName('../escape'); $zip->addFromString('tables/samples.jsonl', "[null]\n"); $zip->close();
    rejected(fn()=>Backup::stage($badFile,'zip',1), 'Corrupted rows rejected');
    $rows = iterator_to_array(\App\Support\SqlBackupReader::rows("(1,'O''Brien',NULL,0x00ff),(2,'line\\nnext',X'',-1.2e3)"));
    check($rows[0] === ['1',"O'Brien",null,"\0\xff"] && $rows[1][1] === "line\nnext", 'phpMyAdmin multi-row literals and escaping parsed');
    echo "ALL DATABASE WIZARD TESTS PASSED\n";
} finally {
    // This exact name was randomly generated and created by this process above.
    if (preg_match('/^fsr_wizard_test_[a-f0-9]{12}$/D',$name)) $server->exec("DROP DATABASE `$name`");
    foreach (glob($folder . '/*') ?: [] as $file) if (is_file($file)) unlink($file);
    if (is_file($folder . '/.htaccess')) unlink($folder . '/.htaccess');
    if (is_dir($folder)) rmdir($folder);
}
