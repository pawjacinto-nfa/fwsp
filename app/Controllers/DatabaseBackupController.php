<?php
declare(strict_types=1);
namespace App\Controllers;

use App\Core\Database;
use App\Models\DatabaseBackup;
use App\Support\DatabaseMaintenanceGate;
use InvalidArgumentException;

final class DatabaseBackupController
{
    public function handle(string $action, array $payload, array $files): never
    {
        header('Cache-Control: no-store, private');
        try {
            $user = $this->authorize($payload);
            if (!extension_loaded('zip')) throw new InvalidArgumentException('The server needs the PHP ZIP extension enabled before using this wizard.');
            if ($action === 'database-backup-download') $this->download((string) ($payload['backup_id'] ?? ''), (string) ($payload['format'] ?? 'zip'));
            if (in_array($action, ['database-backup-create','database-backup-restore'], true)) {
                DatabaseMaintenanceGate::exclusive();
                $user = $this->authorize($payload, false);
            }
            set_time_limit(0);
            ignore_user_abort(true);
            $id = (string) ($payload['backup_id'] ?? '');
            if ($action === 'database-backup-create') {
                $result = DatabaseBackup::create((int) $user['id']);
                $message = 'Backup created and verified. Download and keep a copy away from this server.';
            } elseif ($action === 'database-backup-upload') {
                $file = $files['backup_file'] ?? null;
                if (!$file || ($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) throw new InvalidArgumentException('The upload did not complete. Check the file size against the upload limit shown on this page.');
                if (!is_uploaded_file($file['tmp_name']) || filesize($file['tmp_name']) > DatabaseBackup::MAX_UPLOAD) throw new InvalidArgumentException('The file exceeds the wizard upload limit.');
                $result = DatabaseBackup::stage($file['tmp_name'], strtolower(pathinfo($file['name'], PATHINFO_EXTENSION)), (int) $user['id']);
                $manifest = DatabaseBackup::inspect(DatabaseBackup::path($result['id']), DatabaseBackup::schema(Database::connection()));
                $result['table_summary'] = array_map(static fn ($name, $meta) => ['name'=>$name,'rows'=>$meta['rows']], array_keys($manifest['tables']), array_values($manifest['tables']));
                $message = 'File validated. No database records have been replaced. Review the details below.';
            } elseif ($action === 'database-backup-restore') {
                if (($payload['confirmation'] ?? '') !== 'RESTORE DATABASE' || ($payload['acknowledge'] ?? '') !== '1') throw new InvalidArgumentException('Acknowledge the replacement and type RESTORE DATABASE exactly.');
                $result = DatabaseBackup::restore($id, (int) $user['id'], $user['username']);
                $_SESSION = []; session_regenerate_id(true);
                $message = 'Restore completed. Maintenance remains ON. Sign in with your account and the password stored in the restored backup, verify the records, then turn maintenance off.';
            } else throw new InvalidArgumentException('Unknown database wizard action.');
            $this->reply(200, ['success'=>true,'message'=>$message,'backup'=>$result]);
        } catch (InvalidArgumentException $error) {
            $this->reply(422, ['success'=>false,'message'=>$error->getMessage()]);
        } catch (\Throwable $error) {
            $reference = system_error_reference();
            error_log('Database wizard ' . $reference . ': ' . $error->getMessage());
            $this->reply(500, ['success'=>false,'message'=>'The operation could not finish. Do not retry a restore until you check Backup history for its status. Contact the administrator with reference ' . $reference . '.']);
        }
    }
    private function authorize(array $payload, bool $rateLimit = true): array
    {
        $db = Database::connection();
        $stmt = $db->prepare('SELECT id,username,role,is_active,status,password_hash FROM users WHERE id=?');
        $stmt->execute([(int) ($_SESSION['user_id'] ?? 0)]); $user = $stmt->fetch();
        $epoch = (string) ($db->query("SELECT setting_value FROM system_settings WHERE setting_key='database_restore_epoch'")->fetchColumn() ?: '');
        if (!$user || $user['role'] !== 'System Admin' || (int) $user['is_active'] !== 1 || $user['status'] !== 'Active'
            || !hash_equals(hash('sha256', $user['password_hash']), (string) ($_SESSION['password_fingerprint'] ?? ''))
            || !hash_equals($epoch, (string) ($_SESSION['database_restore_epoch'] ?? ''))) {
            $this->reply(403, ['success'=>false,'message'=>'An active System Admin session is required. Sign in again.']);
        }
        if ($rateLimit && !security_rate_limit('database-wizard:' . $user['id'], 40, 900)) $this->reply(429,['success'=>false,'message'=>'Too many verification attempts. Wait 15 minutes before retrying.']);
        if (!is_string($payload['current_password'] ?? null) || !password_verify($payload['current_password'], $user['password_hash'])) throw new InvalidArgumentException('Enter your current administrator password to continue.');
        return $user;
    }
    private function download(string $id, string $format): never
    {
        $record = DatabaseBackup::record($id);
        if (!in_array($record['kind'], ['export','recovery'], true) || $record['status'] !== 'ready') throw new InvalidArgumentException('Only verified exports and recovery backups can be downloaded.');
        $file = DatabaseBackup::path($id);
        if (!is_file($file) || !hash_equals($record['sha256'], hash_file('sha256', $file))) throw new InvalidArgumentException('Backup verification failed. The file was changed or is no longer available.');
        if (!in_array($format, ['zip','sql'], true)) throw new InvalidArgumentException('Unknown download format.');
        $zip = null;
        if ($format === 'sql') {
            $zip = new \ZipArchive();
            if ($zip->open($file, \ZipArchive::RDONLY) !== true || !($stream = $zip->getStream('database.sql'))) throw new InvalidArgumentException('This backup does not contain a SQL export.');
            $size = $zip->statName('database.sql')['size'];
        } else { $stream = fopen($file, 'rb'); $size = filesize($file); }
        if (!$stream) throw new \RuntimeException('Cannot read backup file.');
        session_write_close();
        while (ob_get_level()) ob_end_clean();
        header('Content-Type: ' . ($format === 'zip' ? 'application/zip' : 'application/sql'));
        header('Content-Disposition: attachment; filename="FSR-' . $record['kind'] . '-' . gmdate('Ymd-His', strtotime($record['created_at'])) . '-' . substr($id, 0, 8) . '.' . $format . '"');
        header('Content-Length: ' . $size);
        header('X-Content-Type-Options: nosniff');
        fpassthru($stream); fclose($stream); if ($zip) $zip->close(); exit;
    }
    private function reply(int $status, array $data): never
    {
        if (($_POST['action'] ?? '') === 'database-backup-download' && empty($data['success'])) {
            $_SESSION['flash'] = ['type'=>'danger','message'=>$data['message']];
            header('Location: index.php?page=system-maintenance&tab=database', true, 303);
            exit;
        }
        http_response_code($status); header('Content-Type: application/json; charset=UTF-8');
        echo json_encode($data, JSON_THROW_ON_ERROR | JSON_INVALID_UTF8_SUBSTITUTE); exit;
    }
}
