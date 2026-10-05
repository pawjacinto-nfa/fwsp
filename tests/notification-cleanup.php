<?php
declare(strict_types=1);

if (PHP_SAPI !== 'cli') exit;
define('BASE_PATH', dirname(__DIR__));
require BASE_PATH . '/app/Core/Database.php';
require BASE_PATH . '/app/Models/Notification.php';

use App\Core\Database;
use App\Models\Notification;

function verifyCleanup(bool $condition, string $message): void
{
    if (!$condition) throw new RuntimeException($message);
    echo 'PASS: ' . $message . PHP_EOL;
}

set_exception_handler(static function (Throwable $error): never {
    fwrite(STDERR, $error->getMessage() . PHP_EOL);
    exit(1);
});
$period = new ReflectionMethod(Notification::class, 'cleanupPeriod');
[$where, $params] = $period->invoke(null, 'range', '2026-10-01', '2026-10-02');
verifyCleanup($params === ['start'=>'2026-10-01 00:00:00', 'end'=>'2026-10-03 00:00:00'], 'Range uses inclusive start and exclusive next-day end');
foreach ([['range','2026-02-30','2026-03-01'], ['range','2026-10-03','2026-10-01'], ['range','',''], ['invalid','','']] as [$scope,$from,$to]) {
    try {
        $period->invoke(null, $scope, $from, $to);
        throw new RuntimeException('Invalid period was accepted');
    } catch (InvalidArgumentException $error) {
        verifyCleanup(true, 'Invalid cleanup period rejected');
    }
}
verifyCleanup($period->invoke(null, 'all', '', '') === ['', []], 'All dates explicitly selects every notification');
function e(string|int|float|null $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}
$tables = [];
$schema = null;
$selectedTable = '';
$notificationTotal = 4;
$cleanupScope = 'range';
$cleanupFrom = '2026-10-01';
$cleanupTo = '2026-10-02';
$cleanupCount = 2;
ob_start();
require BASE_PATH . '/app/Views/database-management.php';
$html = ob_get_clean();
verifyCleanup(str_contains($html, '2 notification(s)') && str_contains($html, '2026-10-01 through 2026-10-02'), 'Preview renders the matching count and period');
verifyCleanup(str_contains($html, 'data-confirm-title="Delete notifications for all users"') && str_contains($html, 'value="notifications-cleanup"'), 'Deletion renders with explicit confirmation and POST action');
if (in_array('--validation-only', $argv, true)) exit;
$db = Database::connection();
// Session-local tables shadow production tables; every test delete targets fixtures only.
$sourceSchema = str_replace('`', '``', (string) $db->query('SELECT DATABASE()')->fetchColumn());
foreach (['notifications', 'notification_reads', 'notification_preferences', 'audit_logs'] as $table) {
    $fixtureTable = '__test_' . $table;
    $db->exec('CREATE TEMPORARY TABLE `' . $fixtureTable . '` LIKE `' . $sourceSchema . '`.`' . $table . '`');
    $db->exec('ALTER TABLE `' . $fixtureTable . '` RENAME TO `' . $table . '`');
}
$db->exec("INSERT INTO notifications (id,user_id,message,is_read,created_at) VALUES
    (1,1,'Before',0,'2026-09-30 23:59:59'),
    (2,1,'Start',0,'2026-10-01 00:00:00'),
    (3,2,'End',1,'2026-10-02 23:59:59'),
    (4,NULL,'Shared after',0,'2026-10-03 00:00:00')");
$db->exec('INSERT INTO notification_reads (notification_id,user_id) VALUES (2,1),(3,2),(4,1)');
$db->exec('INSERT INTO notification_preferences (user_id) VALUES (1),(2)');
verifyCleanup(Notification::cleanupCount('range', '2026-10-01', '2026-10-02') === 2, 'Inclusive range includes both boundary dates');
foreach ([['range','2026-02-30','2026-03-01'], ['range','2026-10-03','2026-10-01'], ['range','',''], ['invalid','','']] as [$scope,$from,$to]) {
    try {
        Notification::cleanupCount($scope, $from, $to);
        throw new RuntimeException('Invalid period was accepted');
    } catch (InvalidArgumentException $error) {
        verifyCleanup(true, 'Invalid cleanup period rejected');
    }
}
verifyCleanup(Notification::cleanupForAllUsers('range','2026-10-01','2026-10-02',1) === 2, 'Range deletes read and unread fixtures across users');
verifyCleanup(Notification::cleanupCount() === 2, 'Notifications outside range are retained');
verifyCleanup((int) $db->query('SELECT COUNT(*) FROM notification_reads')->fetchColumn() === 1, 'Only matching read receipts are deleted');
verifyCleanup((int) $db->query('SELECT COUNT(*) FROM notification_preferences')->fetchColumn() === 2, 'Preferences are retained');
verifyCleanup(Notification::cleanupForAllUsers('all','','',1) === 2, 'All dates also removes shared notifications');
verifyCleanup(Notification::cleanupCount() === 0 && (int) $db->query('SELECT COUNT(*) FROM notification_reads')->fetchColumn() === 0, 'All notification storage is cleared');
verifyCleanup((int) $db->query('SELECT COUNT(*) FROM audit_logs')->fetchColumn() === 2, 'Both cleanups are audited');
// Force an audit failure to confirm the deletion rolls back atomically.
$db->exec("INSERT INTO notifications (id,user_id,message,is_read,created_at) VALUES (5,1,'Rollback',0,NOW())");
$db->exec('DROP TEMPORARY TABLE audit_logs');
$db->exec('CREATE TEMPORARY TABLE audit_logs (unusable INT)');
try {
    Notification::cleanupForAllUsers('all','','',1);
    throw new RuntimeException('Audit failure was not reported');
} catch (PDOException $error) {
    verifyCleanup(Notification::cleanupCount() === 1, 'Audit failure rolls back notification deletion');
}
