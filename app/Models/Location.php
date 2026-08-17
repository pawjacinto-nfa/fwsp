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
            ORDER BY b.name
        ')->fetchAll();
    }

    public static function provinces(): array
    {
        self::ensureSchema();
        return Database::connection()->query('
            SELECT p.id, p.branch_id, p.name
            FROM province_offices p
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
            FROM province_offices p
            JOIN branch_offices b ON b.id = p.branch_id
            LEFT JOIN regions r ON r.id = b.region_id
            LEFT JOIN warehouse_offices w ON w.province_id = p.id
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

    /** @return array{region_code:string,branch_code:string,branch_id:int}|null */
    public static function referenceCodesForWarehouse(int $warehouseId): ?array
    {
        self::ensureReferenceCodeSchema();
        $stmt = Database::connection()->prepare("
            SELECT r.reference_code AS region_code, b.reference_code AS branch_code, b.id AS branch_id
            FROM warehouse_offices w
            LEFT JOIN province_offices p ON p.id = w.province_id
            INNER JOIN branch_offices b ON b.id = COALESCE(p.branch_id, w.branch_id)
            INNER JOIN regions r ON r.id = b.region_id
            WHERE w.id = :warehouse_id
            LIMIT 1
        ");
        $stmt->execute(['warehouse_id' => $warehouseId]);
        $row = $stmt->fetch();
        if (!$row || empty($row['region_code']) || empty($row['branch_code'])) return null;

        return [
            'region_code' => (string) $row['region_code'],
            'branch_code' => (string) $row['branch_code'],
            'branch_id' => (int) $row['branch_id'],
        ];
    }

    public static function ensureReferenceCodeSchema(): void
    {
        self::ensureSchema();
        static $ready = false;
        if ($ready) return;

        $db = Database::connection();
        $db->exec('ALTER TABLE regions ADD COLUMN IF NOT EXISTS reference_code VARCHAR(12) NULL AFTER name');
        $db->exec('ALTER TABLE branch_offices ADD COLUMN IF NOT EXISTS reference_code VARCHAR(12) NULL AFTER name');

        $regions = $db->query('SELECT id, name, reference_code FROM regions ORDER BY id')->fetchAll();
        $usedRegionCodes = [];
        $updateRegion = $db->prepare('UPDATE regions SET reference_code = :code WHERE id = :id');
        foreach ($regions as $region) {
            $existing = strtoupper(trim((string) ($region['reference_code'] ?? '')));
            $base = $existing !== '' ? $existing : self::regionReferenceCode((string) $region['name']);
            $code = self::distinctReferenceCode($base, $usedRegionCodes, 12);
            $usedRegionCodes[$code] = true;
            if ($code !== $existing) $updateRegion->execute(['code' => $code, 'id' => $region['id']]);
        }

        $branches = $db->query('SELECT id, region_id, name, reference_code FROM branch_offices ORDER BY region_id, id')->fetchAll();
        $usedByRegion = [];
        $updateBranch = $db->prepare('UPDATE branch_offices SET reference_code = :code WHERE id = :id');
        foreach ($branches as $branch) {
            $regionId = (int) $branch['region_id'];
            $existing = strtoupper(trim((string) ($branch['reference_code'] ?? '')));
            $base = $existing !== '' ? $existing : self::branchReferenceCode((string) $branch['name']);
            $usedByRegion[$regionId] ??= [];
            $code = self::distinctReferenceCode($base, $usedByRegion[$regionId], 3);
            $usedByRegion[$regionId][$code] = true;
            if ($code !== $existing) $updateBranch->execute(['code' => $code, 'id' => $branch['id']]);
        }

        try { $db->exec('CREATE UNIQUE INDEX regions_reference_code_unique ON regions (reference_code)'); } catch (\Throwable) { }
        try { $db->exec('CREATE UNIQUE INDEX branch_offices_region_reference_code_unique ON branch_offices (region_id, reference_code)'); } catch (\Throwable) { }
        $ready = true;
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

    private static function regionReferenceCode(string $name): string
    {
        if (preg_match('/^Region\s+([IVXLCDM]+|\d+)$/i', trim($name), $match)) {
            $number = ctype_digit($match[1]) ? (int) $match[1] : self::romanToInteger(strtoupper($match[1]));
            if ($number > 0) return 'R' . $number;
        }

        return substr(preg_replace('/[^A-Z0-9]/', '', strtoupper($name)) ?: 'REGION', 0, 12);
    }

    private static function branchReferenceCode(string $name): string
    {
        $clean = preg_replace('/\bNFA\b/i', '', $name);
        $clean = preg_replace('/\bBranch\s+Office\b|\bBranch\b/i', '', (string) $clean);
        $words = preg_split('/[^A-Za-z0-9]+/', strtoupper(trim((string) $clean)), -1, PREG_SPLIT_NO_EMPTY) ?: [];
        if ($words === []) return 'BRN';
        if (count($words) === 1) return substr($words[0] . 'XXX', 0, 3);
        if (count($words) === 2) return substr($words[0], 0, 1) . substr($words[1] . 'XX', 0, 2);
        return substr(implode('', array_map(static fn (string $word): string => $word[0], $words)), 0, 3);
    }

    /** @param array<string,bool> $used */
    private static function distinctReferenceCode(string $base, array $used, int $maxLength): string
    {
        $base = substr(preg_replace('/[^A-Z0-9]/', '', strtoupper($base)) ?: 'LOC', 0, $maxLength);
        if (!isset($used[$base])) return $base;
        for ($suffix = 2; $suffix < 36; $suffix++) {
            $tail = strtoupper(base_convert((string) $suffix, 10, 36));
            $candidate = substr($base, 0, max(1, $maxLength - strlen($tail))) . $tail;
            if (!isset($used[$candidate])) return $candidate;
        }
        throw new \RuntimeException('A distinct location reference code could not be generated.');
    }

    private static function romanToInteger(string $roman): int
    {
        $values = ['I' => 1, 'V' => 5, 'X' => 10, 'L' => 50, 'C' => 100, 'D' => 500, 'M' => 1000];
        $total = 0; $previous = 0;
        for ($index = strlen($roman) - 1; $index >= 0; $index--) {
            $value = $values[$roman[$index]] ?? 0;
            $total += $value < $previous ? -$value : $value;
            $previous = max($previous, $value);
        }
        return $total;
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
