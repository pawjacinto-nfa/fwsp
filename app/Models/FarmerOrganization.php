<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

final class FarmerOrganization
{
    public static function all(): array
    {
        self::ensureSchema();
        $stmt = Database::connection()->query("
            SELECT
                fo.id,
                fo.name,
                fo.total_members,
                fo.office_location,
                fo.warehouse_id,
                fo.created_at,
                r.id AS region_id,
                b.id AS branch_id,
                p.id AS province_id,
                COALESCE(r.name, '') AS region_name,
                COALESCE(b.name, '') AS branch_name,
                COALESCE(p.name, '') AS province_name,
                COALESCE(w.name, '') AS warehouse_name
            FROM farmer_organizations fo
            LEFT JOIN warehouse_offices w ON w.id = fo.warehouse_id
            LEFT JOIN province_offices p ON p.id = w.province_id
            LEFT JOIN branch_offices b ON b.id = COALESCE(p.branch_id, w.branch_id)
            LEFT JOIN regions r ON r.id = b.region_id
            ORDER BY fo.name
        ");

        return $stmt->fetchAll();
    }

    public static function find(int $id): ?array
    {
        self::ensureSchema();
        $stmt = Database::connection()->prepare("
            SELECT
                fo.id,
                fo.name,
                fo.total_members,
                fo.office_location,
                fo.warehouse_id,
                fo.created_at,
                r.id AS region_id,
                b.id AS branch_id,
                p.id AS province_id,
                COALESCE(r.name, '') AS region_name,
                COALESCE(b.name, '') AS branch_name,
                COALESCE(p.name, '') AS province_name,
                COALESCE(w.name, '') AS warehouse_name
            FROM farmer_organizations fo
            LEFT JOIN warehouse_offices w ON w.id = fo.warehouse_id
            LEFT JOIN province_offices p ON p.id = w.province_id
            LEFT JOIN branch_offices b ON b.id = COALESCE(p.branch_id, w.branch_id)
            LEFT JOIN regions r ON r.id = b.region_id
            WHERE fo.id = :id
            LIMIT 1
        ");
        $stmt->execute(['id' => $id]);
        $organization = $stmt->fetch();

        return $organization ?: null;
    }

    public static function members(int $id): array
    {
        self::ensureSchema();
        $stmt = Database::connection()->prepare("
            SELECT
                f.id,
                f.rsbsa_number AS rsbsa,
                f.first_name,
                f.middle_name,
                f.last_name,
                f.sex,
                f.birthdate,
                CASE
                    WHEN f.birthdate IS NULL THEN NULL
                    ELSE TIMESTAMPDIFF(YEAR, f.birthdate, CURDATE())
                END AS age,
                COALESCE(r.name, '') AS region_name,
                COALESCE(b.name, '') AS branch_name,
                COALESCE(p.name, '') AS province_name,
                COALESCE(w.name, '') AS warehouse_name
            FROM farmers f
            LEFT JOIN warehouse_offices w ON w.id = f.warehouse_id
            LEFT JOIN province_offices p ON p.id = w.province_id
            LEFT JOIN branch_offices b ON b.id = COALESCE(p.branch_id, w.branch_id)
            LEFT JOIN regions r ON r.id = b.region_id
            WHERE f.farmer_organization_id = :id
            ORDER BY f.last_name, f.first_name
        ");
        $stmt->execute(['id' => $id]);

        return $stmt->fetchAll();
    }

    public static function create(string $name, int $totalMembers = 0, string $officeLocation = ''): void
    {
        self::ensureSchema();
        $name = trim($name);

        if ($name === '') {
            return;
        }

        $stmt = Database::connection()->prepare("
            INSERT INTO farmer_organizations (name, total_members, office_location)
            VALUES (:name, :total_members, :office_location)
            ON DUPLICATE KEY UPDATE
                total_members = VALUES(total_members),
                office_location = VALUES(office_location)
        ");
        $stmt->execute([
            'name' => $name,
            'total_members' => max(0, $totalMembers),
            'office_location' => trim($officeLocation),
        ]);
    }

    public static function update(int $id, string $name, int $totalMembers = 0, string $officeLocation = ''): void
    {
        self::ensureSchema();
        $name = trim($name);

        if ($id <= 0 || $name === '') {
            return;
        }

        $stmt = Database::connection()->prepare('
            UPDATE farmer_organizations
            SET name = :name,
                total_members = :total_members,
                office_location = :office_location
            WHERE id = :id
        ');
        $stmt->execute([
            'id' => $id,
            'name' => $name,
            'total_members' => max(0, $totalMembers),
            'office_location' => trim($officeLocation),
        ]);
    }

    private static function ensureSchema(): void
    {
        static $ready = false;

        if ($ready) {
            return;
        }

        $db = Database::connection();
        $db->exec('ALTER TABLE farmer_organizations ADD COLUMN IF NOT EXISTS total_members INT UNSIGNED NOT NULL DEFAULT 0');
        $db->exec('ALTER TABLE farmer_organizations ADD COLUMN IF NOT EXISTS office_location VARCHAR(255) NULL');
        $db->exec('ALTER TABLE farmer_organizations ADD COLUMN IF NOT EXISTS warehouse_id BIGINT UNSIGNED NULL');

        $ready = true;
    }
}
