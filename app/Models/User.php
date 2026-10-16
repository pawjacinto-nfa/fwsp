<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

final class User
{
    public static function migrateLegacyRoles(): void
    {
        Database::connection()->exec("
            UPDATE users
            SET role = CASE role
                WHEN 'Super Admin' THEN 'System Admin'
                WHEN 'Regional/Branch Manager' THEN 'Manager'
                WHEN 'Warehouse Supervisor' THEN 'Warehouse Personnel'
                WHEN 'Viewer' THEN 'Read-Only User'
                ELSE role
            END
            WHERE role IN ('Super Admin', 'Regional/Branch Manager', 'Warehouse Supervisor', 'Viewer')
        ");
    }

    public static function authenticate(string $username, string $password): ?array
    {
        self::ensurePasswordResetSchema();
        $stmt = Database::connection()->prepare('SELECT * FROM users WHERE username = :username LIMIT 1');
        $stmt->execute(['username' => $username]);
        $user = $stmt->fetch();

        if (!$user || (int) $user['is_active'] !== 1 || !password_verify($password, $user['password_hash'])) {
            return null;
        }

        return $user;
    }

    public static function findByUsername(string $username): ?array
    {
        self::ensurePasswordResetSchema();
        $stmt = Database::connection()->prepare('SELECT * FROM users WHERE username = :username LIMIT 1');
        $stmt->execute(['username' => $username]);
        $user = $stmt->fetch();

        return $user ?: null;
    }

    public static function register(array $data): void
    {
        CentralOffice::ensureSchema();
        $stmt = Database::connection()->prepare("
            INSERT INTO users (
                full_name, username, email, password_hash, role, is_active, status,
                office_scope, region_id, branch_id, province_id, warehouse_id,
                central_department_id, central_division_id, central_unit_id,
                designation, contact_number
            ) VALUES (
                :full_name, :username, :email, :password_hash, 'Read-Only User', 0, 'Pending',
                :office_scope, :region_id, :branch_id, :province_id, :warehouse_id,
                :central_department_id, :central_division_id, :central_unit_id,
                :designation, :contact_number
            )
        ");
        $stmt->execute([
            'full_name' => $data['full_name'] ?: $data['username'],
            'username' => $data['username'],
            'email' => $data['email'],
            'password_hash' => password_hash($data['password'], PASSWORD_DEFAULT),
            'office_scope' => $data['office_scope'] === 'central' ? 'central' : 'field',
            'region_id' => $data['region_id'] ?: null,
            'branch_id' => $data['branch_id'] ?: null,
            'province_id' => $data['province_id'] ?: null,
            'warehouse_id' => $data['warehouse_id'] ?: null,
            'central_department_id' => $data['central_department_id'] ?: null,
            'central_division_id' => $data['central_division_id'] ?: null,
            'central_unit_id' => $data['central_unit_id'] ?: null,
            'designation' => $data['designation'],
            'contact_number' => $data['contact_number'],
        ]);
    }

    public static function all(): array
    {
        CentralOffice::ensureSchema();
        self::ensurePasswordResetSchema();
        $sql = "
            SELECT
                u.*,
                r.name AS region_name,
                b.name AS branch_name,
                p.name AS province_name,
                w.name AS warehouse_name,
                cd.name AS central_department_name,
                cv.name AS central_division_name,
                cu.name AS central_unit_name
            FROM users u
            LEFT JOIN regions r ON r.id = u.region_id
            LEFT JOIN branch_offices b ON b.id = u.branch_id
            LEFT JOIN province_offices p ON p.id = u.province_id
            LEFT JOIN warehouse_offices w ON w.id = u.warehouse_id
            LEFT JOIN central_departments cd ON cd.id = u.central_department_id
            LEFT JOIN central_divisions cv ON cv.id = u.central_division_id
            LEFT JOIN central_units cu ON cu.id = u.central_unit_id
            ORDER BY u.status = 'Pending' DESC, u.created_at DESC
        ";

        return Database::connection()->query($sql)->fetchAll();
    }

    public static function find(int $id): ?array
    {
        CentralOffice::ensureSchema();
        self::ensurePasswordResetSchema();
        $stmt = Database::connection()->prepare("
            SELECT
                u.*,
                r.name AS region_name,
                b.name AS branch_name,
                p.name AS province_name,
                w.name AS warehouse_name,
                cd.name AS central_department_name,
                cv.name AS central_division_name,
                cu.name AS central_unit_name
            FROM users u
            LEFT JOIN regions r ON r.id = u.region_id
            LEFT JOIN branch_offices b ON b.id = u.branch_id
            LEFT JOIN province_offices p ON p.id = u.province_id
            LEFT JOIN warehouse_offices w ON w.id = u.warehouse_id
            LEFT JOIN central_departments cd ON cd.id = u.central_department_id
            LEFT JOIN central_divisions cv ON cv.id = u.central_division_id
            LEFT JOIN central_units cu ON cu.id = u.central_unit_id
            WHERE u.id = :id
            LIMIT 1
        ");
        $stmt->execute(['id' => $id]);
        $user = $stmt->fetch();

        return $user ?: null;
    }

    public static function locationLabel(array $user): string
    {
        if (($user['office_scope'] ?? 'field') === 'central') {
            $parts = [
                $user['central_department_name'] ?? '',
                $user['central_division_name'] ?? '',
                $user['central_unit_name'] ?? '',
            ];
            $parts = array_values(array_filter(array_map('trim', $parts)));

            return $parts ? implode("\n", $parts) : 'Central Office';
        }

        $parts = [
            $user['region_name'] ?? '',
            $user['province_name'] ?? '',
            $user['warehouse_name'] ?? '',
        ];
        $parts = array_values(array_filter(array_map('trim', $parts)));

        return $parts ? implode("\n", $parts) : 'Not set';
    }

    public static function updateAccount(int $id, array $data): void
    {
        CentralOffice::ensureSchema();
        Database::connection()->exec('ALTER TABLE users ADD COLUMN IF NOT EXISTS offline_enabled TINYINT(1) NOT NULL DEFAULT 0');
        $sets = [
            'full_name = :full_name',
            'email = :email',
            'contact_number = :contact_number',
            'designation = :designation',
            'region_id = :region_id',
            'branch_id = :branch_id',
            'province_id = :province_id',
            'warehouse_id = :warehouse_id',
        ];
        $params = [
            'id' => $id,
            'full_name' => $data['full_name'],
            'email' => $data['email'],
            'contact_number' => $data['contact_number'],
            'designation' => $data['designation'],
            'region_id' => $data['region_id'] ?: null,
            'branch_id' => $data['branch_id'] ?: null,
            'province_id' => $data['province_id'] ?: null,
            'warehouse_id' => $data['warehouse_id'] ?: null,
        ];

        if (!empty($data['profile_image'])) {
            $sets[] = 'profile_image = :profile_image';
            $params['profile_image'] = $data['profile_image'];
        }

        if (!empty($data['password'])) {
            $sets[] = 'password_hash = :password_hash';
            $params['password_hash'] = password_hash($data['password'], PASSWORD_DEFAULT);
        }

        if (array_key_exists('offline_enabled', $data)) {
            $sets[] = 'offline_enabled = :offline_enabled';
            $params['offline_enabled'] = $data['offline_enabled'] ? 1 : 0;
        }

        $stmt = Database::connection()->prepare('UPDATE users SET ' . implode(', ', $sets) . ' WHERE id = :id');
        $stmt->execute($params);
    }

    public static function updateAdminProfile(int $id, array $data): void
    {
        self::ensurePasswordResetSchema();
        // Invalidate pending recovery when the registered address changes.
        $sets = ["password_reset_status=CASE WHEN email<>:reset_email THEN NULL ELSE password_reset_status END"];
        $params = ['id'=>$id, 'reset_email'=>$data['email']];
        foreach (['full_name','email','contact_number','designation','office_scope','region_id','branch_id','province_id','warehouse_id','central_department_id','central_division_id','central_unit_id'] as $field) {
            $sets[] = $field . '=:' . $field;
            $params[$field] = $data[$field];
        }
        if (!empty($data['profile_image'])) {
            $sets[] = 'profile_image=:profile_image';
            $params['profile_image'] = $data['profile_image'];
        }
        Database::connection()->prepare('UPDATE users SET ' . implode(',', $sets) . ' WHERE id=:id')->execute($params);
    }

    public static function deactivate(int $id, string $reason): void
    {
        self::ensurePasswordResetSchema();
        $stmt = Database::connection()->prepare("\n            UPDATE users\n            SET status = 'Deleted', is_active = 0, deactivation_reason = :reason, deactivated_at = CURRENT_TIMESTAMP\n            WHERE id = :id\n        ");
        $stmt->execute(['id' => $id, 'reason' => $reason]);
    }

    public static function activeSystemAdminCount(): int
    {
        return count(self::activeSystemAdminIds());
    }

    /** @return list<int> */
    public static function activeSystemAdminIds(): array
    {
        $stmt = Database::connection()->query("
            SELECT id
            FROM users
            WHERE role = 'System Admin'
              AND status = 'Active'
              AND is_active = 1
        ");

        return array_map('intval', $stmt->fetchAll(\PDO::FETCH_COLUMN));
    }

    public static function updateAccess(int $id, string $role, string $status): void
    {
        self::ensurePasswordResetSchema();
        $stmt = Database::connection()->prepare("
            UPDATE users
            SET role = :role, status = :status, is_active = :is_active
            WHERE id = :id
        ");
        $stmt->execute([
            'id' => $id,
            'role' => $role,
            'status' => $status,
            'is_active' => $status === 'Active' ? 1 : 0,
        ]);
    }

    /** @param list<int> $ids */
    public static function updateAccessBulk(array $ids, ?string $role, ?string $status): int
    {
        $ids = array_values(array_unique(array_filter($ids, static fn (int $id): bool => $id > 0)));
        if ($ids === [] || ($role === null && $status === null)) return 0;

        self::ensurePasswordResetSchema();
        $sets = [];
        $params = [];
        if ($role !== null) { $sets[] = 'role = :role'; $params['role'] = $role; }
        if ($status !== null) {
            $sets[] = 'status = :status';
            $sets[] = 'is_active = :is_active';
            $params['status'] = $status;
            $params['is_active'] = $status === 'Active' ? 1 : 0;
        }
        $placeholders = [];
        foreach ($ids as $index => $id) {
            $key = 'id' . $index;
            $placeholders[] = ':' . $key;
            $params[$key] = $id;
        }
        $stmt = Database::connection()->prepare('UPDATE users SET ' . implode(', ', $sets) . ' WHERE id IN (' . implode(', ', $placeholders) . ')');
        $stmt->execute($params);
        return $stmt->rowCount();
    }

    public static function requestPasswordReset(int $id): bool
    {
        self::ensurePasswordResetSchema();
        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $link = bin2hex(random_bytes(32));
        $db = Database::connection();
        $stmt = $db->prepare("UPDATE users SET password_reset_status='EmailPending',
            password_reset_requested_at=NOW(), password_reset_approved_at=NULL,
            password_reset_token_hash=NULL, password_reset_code_hash=:code,
            password_reset_link_hash=:link, password_reset_attempts=0,
            password_reset_expires_at=DATE_ADD(NOW(), INTERVAL 15 MINUTE)
            WHERE id=:id AND is_active=1 AND email IS NOT NULL AND email<>''
            AND (password_reset_requested_at IS NULL OR password_reset_requested_at <= DATE_SUB(NOW(), INTERVAL 60 SECOND))");
        $stmt->execute(['id'=>$id, 'code'=>password_hash($code, PASSWORD_DEFAULT), 'link'=>hash('sha256', $link)]);
        if ($stmt->rowCount() !== 1) return false;
        $user = self::find($id);
        try {
            \App\Support\ResetMailer::send($user['email'], $code, $link, false, (string) $user['full_name']);
        } catch (\Throwable $error) {
            $stmt = $db->prepare("UPDATE users SET password_reset_status=NULL, password_reset_code_hash=NULL,
                password_reset_link_hash=NULL, password_reset_expires_at=NULL
                WHERE id=:id AND password_reset_link_hash=:link");
            $stmt->execute(['id'=>$id, 'link'=>hash('sha256', $link)]);
            // Never log email bodies, codes, SMTP credentials or exception details.
            error_log('FSR password reset email delivery failed; check the private SMTP configuration.');
            throw new \RuntimeException('Email delivery failed.');
        }
        return true;
    }

    public static function resetLinkUsername(string $link): string
    {
        self::ensurePasswordResetSchema();
        if (!preg_match('/^[a-f0-9]{64}$/D', $link)) return '';
        $stmt = Database::connection()->prepare("SELECT username FROM users WHERE is_active=1
            AND password_reset_status='EmailPending' AND password_reset_link_hash=:link
            AND password_reset_expires_at>NOW() AND password_reset_attempts<5");
        $stmt->execute(['link'=>hash('sha256', $link)]);
        return (string) ($stmt->fetchColumn() ?: '');
    }

    public static function verifyResetCode(int $id, string $code): string
    {
        self::ensurePasswordResetSchema();
        $db = Database::connection();
        $db->beginTransaction();
        try {
            $stmt = $db->prepare("SELECT * FROM users WHERE id=:id AND is_active=1
                AND password_reset_status='EmailPending' AND password_reset_expires_at>NOW()
                AND password_reset_attempts<5 FOR UPDATE");
            $stmt->execute(['id'=>$id]);
            $user = $stmt->fetch();
            if (!$user) { $db->commit(); return ''; }
            if (!preg_match('/^[0-9]{6}$/D', $code) || !password_verify($code, (string) $user['password_reset_code_hash'])) {
                $db->prepare('UPDATE users SET password_reset_attempts=password_reset_attempts+1 WHERE id=?')->execute([$id]);
                $db->commit();
                return '';
            }
            $grant = bin2hex(random_bytes(32));
            $stmt = $db->prepare("UPDATE users SET password_reset_status='EmailVerified',
                password_reset_token_hash=:grant, password_reset_code_hash=NULL, password_reset_link_hash=NULL
                WHERE id=:id");
            $stmt->execute(['id'=>$id, 'grant'=>hash('sha256', $grant)]);
            $db->commit();
            return $grant;
        } catch (\Throwable $error) {
            if ($db->inTransaction()) $db->rollBack();
            throw $error;
        }
    }

    public static function completePasswordReset(int $id, string $password, string $token): bool
    {
        self::ensurePasswordResetSchema();
        if (!security_password_valid($password) || !preg_match('/^[a-f0-9]{64}$/D', $token)) return false;
        $stmt = Database::connection()->prepare("UPDATE users SET password_hash=:password,
            password_reset_status=NULL, password_reset_requested_at=NULL, password_reset_approved_at=NULL,
            password_reset_token_hash=NULL, password_reset_code_hash=NULL, password_reset_link_hash=NULL,
            password_reset_expires_at=NULL, password_reset_attempts=0
            WHERE id=:id AND is_active=1 AND password_reset_status='EmailVerified'
            AND password_reset_token_hash=:token AND password_reset_expires_at>NOW()");
        $stmt->execute(['id'=>$id, 'password'=>password_hash($password, PASSWORD_DEFAULT), 'token'=>hash('sha256', $token)]);
        return $stmt->rowCount() === 1;
    }

    public static function resetTokenValid(array $user, string $token): bool
    {
        if (!preg_match('/^[a-f0-9]{64}$/D', $token)) return false;
        $stmt = Database::connection()->prepare("SELECT 1 FROM users WHERE id=:id AND is_active=1
            AND password_reset_status='EmailVerified' AND password_reset_token_hash=:token
            AND password_reset_expires_at>NOW()");
        $stmt->execute(['id'=>$user['id'], 'token'=>hash('sha256', $token)]);
        return (bool) $stmt->fetchColumn();
    }

    public static function activeIdsForWarehouse(int $warehouseId): array
    {
        $stmt = Database::connection()->prepare("
            SELECT id
            FROM users
            WHERE is_active = 1
                AND warehouse_id = :warehouse_id
        ");
        $stmt->execute(['warehouse_id' => $warehouseId]);

        return array_map('intval', array_column($stmt->fetchAll(), 'id'));
    }

    private static function ensurePasswordResetSchema(): void
    {
        static $ready = false;
        if ($ready) {
            return;
        }

        Database::connection()->exec("ALTER TABLE users ADD COLUMN IF NOT EXISTS password_reset_status VARCHAR(30) NULL");
        Database::connection()->exec("ALTER TABLE users ADD COLUMN IF NOT EXISTS password_reset_requested_at TIMESTAMP NULL");
        Database::connection()->exec("ALTER TABLE users ADD COLUMN IF NOT EXISTS password_reset_approved_at TIMESTAMP NULL");
        Database::connection()->exec("ALTER TABLE users ADD COLUMN IF NOT EXISTS password_reset_token_hash CHAR(64) NULL");
        Database::connection()->exec("ALTER TABLE users ADD COLUMN IF NOT EXISTS password_reset_expires_at DATETIME NULL");
        Database::connection()->exec("ALTER TABLE users ADD COLUMN IF NOT EXISTS deactivation_reason TEXT NULL");
        Database::connection()->exec("ALTER TABLE users ADD COLUMN IF NOT EXISTS deactivated_at TIMESTAMP NULL");
        Database::connection()->exec("ALTER TABLE users ADD COLUMN IF NOT EXISTS password_reset_code_hash VARCHAR(255) NULL");
        Database::connection()->exec("ALTER TABLE users ADD COLUMN IF NOT EXISTS password_reset_link_hash CHAR(64) NULL");
        Database::connection()->exec("ALTER TABLE users ADD COLUMN IF NOT EXISTS password_reset_attempts INT NOT NULL DEFAULT 0");
        $ready = true;
    }
}
