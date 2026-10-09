<?php
$backupHistory = []; $backupError = '';
try { $backupHistory = \App\Models\DatabaseBackup::history(); }
catch (\Throwable $error) { $backupError = 'Backup storage is unavailable. Ask the hosting administrator to check its permissions.'; }
?>
<div class="panel no-print mb-3" id="database-backup-wizard">
    <p class="eyebrow">Administrator tools</p>
    <h4>Database backup &amp; restore</h4>
    <p>Keep a verified copy of all database records, or restore a complete backup. Photos, attachments, application files, and server settings are separate and are not included.</p>
    <div class="alert alert-info">Restoring replaces <strong>all database records, accounts, passwords, and settings</strong>. The existing table structure is preserved. A recovery backup is created automatically before replacement. Maintenance stays on after a successful restore, and everyone must sign in again.</div>
    <?php if ($backupError): ?><div class="alert alert-danger"><?= e($backupError) ?></div><?php endif; ?>
    <div id="backup-message" class="alert d-none" role="status" aria-live="polite" tabindex="-1"></div>
    <div class="row g-4">
        <div class="col-lg-6">
            <h5>Export a backup</h5>
            <p>Create and verify a complete database snapshot, then download the ZIP or SQL copy. Other requests briefly pause while the snapshot is created.</p>
            <form method="post" data-backup-action="create">
                <input type="hidden" name="action" value="database-backup-create">
                <label class="form-label" for="backup-export-password">Current administrator password</label>
                <input class="form-control mb-3" id="backup-export-password" type="password" name="current_password" autocomplete="current-password" required>
                <button class="btn btn-primary" type="submit">Create verified backup</button>
            </form>
            <div id="backup-created" class="mt-3 d-none"></div>
        </div>
        <div class="col-lg-6">
            <h5>Import a backup</h5>
            <p><strong>1. Choose file → 2. Review → 3. Restore</strong></p>
            <p>Turn maintenance ON in the Maintenance tab before starting an import.</p>
            <p>Choose an FSR ZIP, or a complete phpMyAdmin SQL export with structure and data for every table. Table names, column order, and types must match. Partial dumps, routines, triggers, views, expressions, and schema changes are not supported.</p>
            <form method="post" enctype="multipart/form-data" data-backup-action="upload">
                <input type="hidden" name="action" value="database-backup-upload">
                <label class="form-label" for="backup-file">Backup file (.zip or .sql)</label>
                <input class="form-control" id="backup-file" type="file" name="backup_file" accept=".zip,.sql" required>
                <p class="form-text">Server upload limit: <?= e(ini_get('upload_max_filesize')) ?> per file; <?= e(ini_get('post_max_size')) ?> per request. Wizard limit: 256 MB compressed / 2 GB expanded. SQL statements: maximum 32 MB. Allow room for the upload form.</p>
                <label class="form-label" for="backup-upload-password">Current administrator password</label>
                <input class="form-control mb-3" id="backup-upload-password" type="password" name="current_password" autocomplete="current-password" required>
                <button class="btn btn-outline-primary" type="submit">Upload and validate</button>
            </form>
        </div>
    </div>
    <section id="backup-review" class="mt-4 border-top pt-3 d-none" aria-labelledby="backup-review-title">
        <h5 id="backup-review-title">2. Review the backup</h5>
        <p id="backup-review-summary"></p>
        <details class="mb-3"><summary>View table record counts</summary><div id="backup-table-summary" class="table-responsive"></div></details>
        <p>No records have changed. This review expires after one hour. Canceling leaves your database unchanged.</p>
        <h5>3. Confirm replacement</h5>
        <div class="alert alert-warning">First turn maintenance <strong>ON now</strong> in the Maintenance tab. Stop any scheduled jobs or external database writes. You will need the password stored in this backup to sign in afterward. Old offline device credentials and pending password resets will be revoked.</div>
        <form method="post" data-backup-action="restore">
            <input type="hidden" name="action" value="database-backup-restore">
            <input type="hidden" name="backup_id" id="backup-restore-id">
            <div class="form-check mb-3"><input class="form-check-input" id="backup-acknowledge" type="checkbox" name="acknowledge" value="1" required><label class="form-check-label" for="backup-acknowledge">I understand that all database records will be replaced and I have separately protected my files.</label></div>
            <label class="form-label" for="backup-confirmation">Type RESTORE DATABASE</label>
            <input class="form-control mb-3" id="backup-confirmation" name="confirmation" autocomplete="off" pattern="RESTORE DATABASE" required>
            <label class="form-label" for="backup-restore-password">Current administrator password</label>
            <input class="form-control mb-3" id="backup-restore-password" type="password" name="current_password" autocomplete="current-password" required>
            <button class="btn btn-danger" type="submit">Create recovery backup and restore</button>
            <button class="btn btn-outline-secondary" id="backup-cancel" type="button">Cancel review</button>
        </form>
    </section>
    <section class="mt-4 border-top pt-3">
        <h5>Backup history</h5>
        <p>Latest 30 operations. Backups are stored privately on this server until the hosting administrator removes them. Download a copy to secure storage elsewhere. <a href="index.php?page=system-maintenance&amp;tab=database">Refresh history</a> after an interrupted request before retrying.</p>
        <div class="table-responsive"><table class="table align-middle"><thead><tr><th>Created (UTC)</th><th>Type / status</th><th>Contents</th><th>Download</th></tr></thead><tbody>
        <?php foreach ($backupHistory as $backup): ?>
            <tr><td><?= e($backup['created_at']) ?><br><small>Admin #<?= e($backup['actor_id']) ?></small></td>
                <td><?= e(ucfirst($backup['kind'])) ?> / <?= e($backup['status']) ?><details><summary>Verification details</summary><small class="text-break">Reference: <?= e($backup['id']) ?><br>SHA-256: <?= e($backup['sha256']) ?><?php if (!empty($backup['recovery_id'])): ?><br>Recovery: <?= e($backup['recovery_id']) ?><?php endif; ?></small></details></td>
                <td><?= number_format($backup['tables']) ?> tables<br><?= number_format($backup['rows']) ?> records<br><?= number_format($backup['bytes']/1048576, 2) ?> MB</td>
                <td><?php if ($backup['status'] === 'ready' && in_array($backup['kind'], ['export','recovery'], true)): ?>
                    <form method="post" data-backup-download>
                        <input type="hidden" name="action" value="database-backup-download"><input type="hidden" name="backup_id" value="<?= e($backup['id']) ?>">
                        <input class="form-control form-control-sm mb-2" type="password" name="current_password" aria-label="Current password for backup <?= e(substr($backup['id'],0,8)) ?>" placeholder="Current password" autocomplete="current-password" required>
                        <button class="btn btn-sm btn-outline-primary" name="format" value="zip">ZIP</button> <button class="btn btn-sm btn-outline-secondary" name="format" value="sql">SQL</button>
                    </form>
                <?php else: ?><small><?= $backup['status'] === 'review' ? 'Upload again to review.' : 'See operation status.' ?></small><?php endif; ?></td>
            </tr>
        <?php endforeach; ?>
        <?php if (!$backupHistory): ?><tr><td colspan="4">No backups yet.</td></tr><?php endif; ?>
        </tbody></table></div>
    </section>
</div>
<script src="assets/js/database-backup.js?v=<?= e((string) filemtime(BASE_PATH . '/assets/js/database-backup.js')) ?>" defer></script>
