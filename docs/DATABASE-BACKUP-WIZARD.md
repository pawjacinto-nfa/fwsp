# Database backup wizard operations

Added October 9, 2026. Location: Help → System Maintenance → Database Management, System Admin only.

## Supported scope

Full logical database data restore into the existing schema. InnoDB only; no views, triggers, stored routines, events, or cross-database foreign keys. Generated columns (including transactions.total_cost) are recalculated by the current schema. FSR ZIPs contain a versioned manifest, base64-encoded JSON lines, per-table hashes, and a SQL export for exported/recovery snapshots. Complete phpMyAdmin SQL files are parsed for definitions and literal INSERT rows; uploaded SQL is never executed. SQL imports validate column order and types, not imported index/default/constraint definitions; the current database structure governs restored records. Structure changes need a separately reviewed migration.

Use UTF-8 and UTC SQL exports with structure and data for every table, including empty tables. Single-quoted strings, numbers, NULL, and hex literals are supported. Export smaller INSERT batches if a statement exceeds 32 MB. FSR ZIP is preferred for exact schema verification. SQL files that use expressions, unsupported literal formats, routines or special SQL modes fail closed. No partial-table restore.

## Deployment

- PHP 8.1+ with PDO MySQL and ZIP. Database account needs SELECT, INSERT, UPDATE, DELETE and metadata access for its own database. Existing application migrations may need additional permissions.
- Private writable storage defaults to `C:/xampp/fsr-private/database-backups` for this checkout. Override `FSR_BACKUP_PATH` only with a private path outside the document root; restrict OS permissions to the service account and authorized administrators. Files contain confidential data, including password hashes; archives are not encrypted.
- Leave room for uncompressed staging data, SQL output, the archive, and a full recovery snapshot. Wizard maximum: 256 MB upload, 2 GB expanded, 32 MB per SQL statement/data row. PHP upload_max_filesize and post_max_size are additional, lower limits; leave multipart overhead below post_max_size. Configure request/proxy timeouts for the expected data volume. UI shows current PHP limits. No server-wide upload settings were increased.
- Apache module `.htaccess` and CGI/FPM `.user.ini` disable public startup/error display, including oversized-upload warnings. Verify your deployment honors these settings; FPM may cache `.user.ini` changes.
- All application requests coordinate using `FSR_DATA_PATH/database-operation.lock`. Backup and restore hold an exclusive lock; competing requests receive HTTP 503. This is a single-host design. Do not run on multiple app hosts unless a shared lock is reliably enforced. Stop external SQL writers and scheduled jobs before restoring; phpMyAdmin/CLI writes do not participate in this lock.
- No automatic retention deletion. Monitor private disk space and apply the organization's secure retention policy to archive/status files. Staged uploads also remain private; review authorization expires in one hour. History shows the latest 30 operations.

## Restore and recovery

Turn maintenance on immediately, separately protect files, upload/review, then acknowledge and type RESTORE DATABASE. Password verification is repeated after obtaining the exclusive lock. The automatic recovery export must finish and verify before replacement starts. Data changes happen in one InnoDB transaction, with prepared INSERTs, strict conversion checks, row counts, foreign-key checks, and a restored active administrator account check. Existing AUTO_INCREMENT counters may remain higher; no IDs are renumbered.

A successful restore writes an audit record and rotates a database session epoch; existing sessions become invalid. Maintenance stays on. Active offline-device credentials and password-reset approvals are revoked. Sign in with the password in the restored backup and check records and reports before reopening access.

A detected error rolls back the transaction. Backup history is stored outside the database and survives replacement. On process termination or a lost response, do not assume success or failure: inspect history and the `Database restored` audit record (includes source SHA-256 and recovery ID). A stale `restoring` entry requires hosting-admin investigation. Database commit and filesystem status cannot be atomic. Recovery backups can be downloaded and imported through the same wizard. They do not restore attachments or other filesystem content.

## Verification

`php tests/database-backup.php` uses a randomly named, synthetic isolated database and removes only its own fixtures. Tests binary/Unicode/numeric preservation, ZIP and SQL round-trip, rollback, FK failures, schema mismatch, replay, malformed archives and unsupported SQL.

`python tests/database-backup-http.py` copies only schema from the configured app, creates synthetic users in a randomly named temporary database, runs a localhost PHP server, and verifies role/password/CSRF checks, downloads, complete-schema SQL imports/restores, session invalidation, history, and upload errors. It never copies or replaces live records. Defaults to local root access for test provisioning. Run only in a development environment. `FSR_WIZARD_VISUAL=1` keeps the temporary server available until Enter for visual verification.
