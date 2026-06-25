<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

final class Location
{
    public static function regions(): array
    {
        self::ensureSchema();
        return Database::connection()->query('
            SELECT DISTINCT r.id, r.name
            FROM regions r
            JOIN branch_offices b ON b.region_id = r.id
            JOIN province_offices p ON p.branch_id = b.id
            JOIN warehouse_offices w ON w.province_id = p.id
            ORDER BY r.name
        ')->fetchAll();
    }

    public static function branches(): array
    {
        self::ensureSchema();
        return Database::connection()->query('
            SELECT DISTINCT b.id, b.region_id, b.name
            FROM branch_offices b
            JOIN province_offices p ON p.branch_id = b.id
            JOIN warehouse_offices w ON w.province_id = p.id
            ORDER BY b.name
        ')->fetchAll();
    }

    public static function provinces(): array
    {
        self::ensureSchema();
        return Database::connection()->query('
            SELECT DISTINCT p.id, p.branch_id, p.name
            FROM province_offices p
            JOIN warehouse_offices w ON w.province_id = p.id
            ORDER BY p.name
        ')->fetchAll();
    }

    public static function warehouses(): array
    {
        self::ensureSchema();
        return Database::connection()->query('SELECT id, branch_id, province_id, name FROM warehouse_offices WHERE province_id IS NOT NULL ORDER BY name')->fetchAll();
    }

    public static function hierarchy(): array
    {
        return [
            'regions' => self::regions(),
            'branches' => self::branches(),
            'provinces' => self::provinces(),
            'warehouses' => self::warehouses(),
        ];
    }

    public static function allRegions(): array
    {
        self::ensureSchema();
        return Database::connection()->query('SELECT id, name FROM regions ORDER BY name')->fetchAll();
    }

    public static function allBranches(): array
    {
        self::ensureSchema();
        return Database::connection()->query('SELECT id, region_id, name FROM branch_offices ORDER BY name')->fetchAll();
    }

    public static function allProvinces(): array
    {
        self::ensureSchema();
        return Database::connection()->query('SELECT id, branch_id, name FROM province_offices ORDER BY name')->fetchAll();
    }

    public static function libraryRows(): array
    {
        self::ensureSchema();
        return Database::connection()->query("
            SELECT
                r.id AS region_id,
                r.name AS region_name,
                b.id AS branch_id,
                b.name AS branch_name,
                p.id AS province_id,
                p.name AS province_name,
                w.id AS warehouse_id,
                w.name AS warehouse_name
            FROM warehouse_offices w
            LEFT JOIN province_offices p ON p.id = w.province_id
            LEFT JOIN branch_offices b ON b.id = COALESCE(p.branch_id, w.branch_id)
            LEFT JOIN regions r ON r.id = b.region_id
            ORDER BY r.name, b.name, p.name, w.name
        ")->fetchAll();
    }

    public static function defaultWarehouseId(): ?int
    {
        self::ensureSchema();
        $id = Database::connection()->query('SELECT id FROM warehouse_offices ORDER BY id LIMIT 1')->fetchColumn();
        return $id ? (int) $id : null;
    }

    public static function warehouseLabel(int $warehouseId): string
    {
        self::ensureSchema();
        $stmt = Database::connection()->prepare("
            SELECT
                COALESCE(r.name, '') AS region_name,
                COALESCE(b.name, '') AS branch_name,
                COALESCE(p.name, '') AS province_name,
                COALESCE(w.name, '') AS warehouse_name
            FROM warehouse_offices w
            LEFT JOIN province_offices p ON p.id = w.province_id
            LEFT JOIN branch_offices b ON b.id = COALESCE(p.branch_id, w.branch_id)
            LEFT JOIN regions r ON r.id = b.region_id
            WHERE w.id = :warehouse_id
            LIMIT 1
        ");
        $stmt->execute(['warehouse_id' => $warehouseId]);
        $row = $stmt->fetch() ?: [];
        $parts = array_filter([
            $row['region_name'] ?? '',
            $row['branch_name'] ?? '',
            $row['province_name'] ?? '',
            $row['warehouse_name'] ?? '',
        ]);

        return $parts ? implode(', ', $parts) : 'the selected location';
    }

    public static function createRegion(string $name): void
    {
        self::ensureSchema();
        $stmt = Database::connection()->prepare('INSERT IGNORE INTO regions (name) VALUES (:name)');
        $stmt->execute(['name' => trim($name)]);
    }

    public static function createBranch(int $regionId, string $name): void
    {
        self::ensureSchema();
        $stmt = Database::connection()->prepare('INSERT IGNORE INTO branch_offices (region_id, name) VALUES (:region_id, :name)');
        $stmt->execute(['region_id' => $regionId, 'name' => trim($name)]);
    }

    public static function createProvince(int $branchId, string $name): void
    {
        self::ensureSchema();
        $stmt = Database::connection()->prepare('INSERT IGNORE INTO province_offices (branch_id, name) VALUES (:branch_id, :name)');
        $stmt->execute(['branch_id' => $branchId, 'name' => trim($name)]);
    }

    public static function createWarehouse(int $provinceId, string $name): void
    {
        self::ensureSchema();
        $db = Database::connection();
        $branchId = self::branchIdForProvince($provinceId);
        $stmt = $db->prepare('INSERT IGNORE INTO warehouse_offices (branch_id, province_id, name) VALUES (:branch_id, :province_id, :name)');
        $stmt->execute(['branch_id' => $branchId, 'province_id' => $provinceId, 'name' => trim($name)]);
    }

    public static function updateName(string $type, int $id, string $name): void
    {
        self::ensureSchema();
        $tables = [
            'region' => 'regions',
            'branch' => 'branch_offices',
            'province' => 'province_offices',
            'warehouse' => 'warehouse_offices',
        ];

        if (!isset($tables[$type])) {
            return;
        }

        $stmt = Database::connection()->prepare("UPDATE {$tables[$type]} SET name = :name WHERE id = :id");
        $stmt->execute(['name' => trim($name), 'id' => $id]);
    }

    public static function delete(string $type, int $id): void
    {
        self::ensureSchema();
        if ($id <= 0) {
            throw new \RuntimeException('Invalid location selected.');
        }

        match ($type) {
            'region' => self::deleteGuarded('regions', $id, 'Region', [
                ['branch_offices', 'region_id', 'branch office'],
                ['users', 'region_id', 'user account'],
            ]),
            'branch' => self::deleteGuarded('branch_offices', $id, 'Branch', [
                ['province_offices', 'branch_id', 'province'],
                ['warehouse_offices', 'branch_id', 'facility'],
                ['users', 'branch_id', 'user account'],
            ]),
            'province' => self::deleteGuarded('province_offices', $id, 'Province', [
                ['warehouse_offices', 'province_id', 'facility'],
                ['users', 'province_id', 'user account'],
            ]),
            'warehouse' => self::deleteGuarded('warehouse_offices', $id, 'Facility', [
                ['users', 'warehouse_id', 'user account'],
                ['farmers', 'warehouse_id', 'farmer profile'],
                ['transactions', 'warehouse_id', 'transaction'],
                ['farmer_organizations', 'warehouse_id', 'farmer organization'],
            ]),
            default => throw new \RuntimeException('Invalid location type selected.'),
        };
    }

    private static function deleteGuarded(string $table, int $id, string $label, array $checks): void
    {
        $db = Database::connection();
        foreach ($checks as [$referenceTable, $column, $referenceLabel]) {
            $stmt = $db->prepare("SELECT COUNT(*) FROM {$referenceTable} WHERE {$column} = :id");
            $stmt->execute(['id' => $id]);
            if ((int) $stmt->fetchColumn() > 0) {
                throw new \RuntimeException("This {$label} cannot be deleted because it is still used by a {$referenceLabel}.");
            }
        }

        $stmt = $db->prepare("DELETE FROM {$table} WHERE id = :id");
        $stmt->execute(['id' => $id]);
    }

    private static function branchIdForProvince(int $provinceId): ?int
    {
        $stmt = Database::connection()->prepare('SELECT branch_id FROM province_offices WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $provinceId]);
        $id = $stmt->fetchColumn();

        return $id ? (int) $id : null;
    }

    private static function ensureSchema(): void
    {
        static $ready = false;

        if ($ready) {
            return;
        }

        $db = Database::connection();
        $db->exec("
            CREATE TABLE IF NOT EXISTS location_masterlist (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                region VARCHAR(120) NOT NULL,
                branch VARCHAR(160) NOT NULL,
                province VARCHAR(160) NOT NULL,
                facility_name VARCHAR(180) NOT NULL,
                UNIQUE KEY location_master_unique (region, branch, province, facility_name)
            )
        ");
        $db->exec("
            CREATE TABLE IF NOT EXISTS province_offices (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                branch_id BIGINT UNSIGNED NOT NULL,
                name VARCHAR(160) NOT NULL,
                UNIQUE KEY province_branch_unique (branch_id, name),
                FOREIGN KEY (branch_id) REFERENCES branch_offices(id)
            )
        ");
        $db->exec('ALTER TABLE warehouse_offices ADD COLUMN IF NOT EXISTS province_id BIGINT UNSIGNED NULL');
        $db->exec('ALTER TABLE users ADD COLUMN IF NOT EXISTS province_id BIGINT UNSIGNED NULL');

        $ready = true;
    }
}
