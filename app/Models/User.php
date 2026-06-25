<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

final class User
{
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
                :full_name, :username, :email, :password_hash, 'Viewer', 0, 'Pending',
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

        $stmt = Database::connection()->prepare('UPDATE users SET ' . implode(', ', $sets) . ' WHERE id = :id');
        $stmt->execute($params);
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

    public static function requestPasswordReset(int $id): void
    {
        self::ensurePasswordResetSchema();
        $stmt = Database::connection()->prepare("
            UPDATE users
            SET password_reset_status = 'Requested',
                password_reset_requested_at = CURRENT_TIMESTAMP,
                password_reset_approved_at = NULL
            WHERE id = :id
        ");
        $stmt->execute(['id' => $id]);
    }

    public static function approvePasswordReset(int $id): void
    {
        self::ensurePasswordResetSchema();
        $stmt = Database::connection()->prepare("
            UPDATE users
            SET password_reset_status = 'Approved',
                password_reset_approved_at = CURRENT_TIMESTAMP
            WHERE id = :id
        ");
        $stmt->execute(['id' => $id]);
    }

    public static function completePasswordReset(int $id, string $password): void
    {
        self::ensurePasswordResetSchema();
        $stmt = Database::connection()->prepare("
            UPDATE users
            SET password_hash = :password_hash,
                password_reset_status = NULL,
                password_reset_requested_at = NULL,
                password_reset_approved_at = NULL
            WHERE id = :id
        ");
        $stmt->execute([
            'id' => $id,
            'password_hash' => password_hash($password, PASSWORD_DEFAULT),
        ]);
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
        $ready = true;
    }
}
