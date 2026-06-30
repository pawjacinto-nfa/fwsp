<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

final class Farmer
{
    public static function all(): array
    {
        self::ensureFarmerKeySchema();
        $sql = "
            SELECT
                f.id,
                f.farmer_key,
                f.rsbsa_number AS rsbsa,
                f.first_name,
                f.middle_name,
                f.last_name,
                f.address,
                f.birthdate,
                f.birthplace,
                f.civil_status,
                f.spouse_name AS spouse,
                f.dependents,
                f.contact_number AS contact,
                f.email,
                f.sex,
                f.photo_path,
                f.gender_orientation,
                f.sector,
                f.is_ip_group_member,
                f.warehouse_id,
                COALESCE((
                    SELECT SUM(limit_t.bags_50kg)
                    FROM transactions limit_t
                    WHERE limit_t.seller_type = 'Individual'
                        AND limit_t.farmer_id = f.id
                        AND YEAR(limit_t.delivery_date) = YEAR(CURDATE())
                ), 0) AS annual_bags_delivered,
                r.id AS region_id,
                b.id AS branch_id,
                p.id AS province_id,
                l.classification AS landholding,
                CASE WHEN l.irrigated = 1 THEN 'Yes' ELSE 'No' END AS irrigated,
                l.palay_location,
                l.harvested_area_hectares AS harvest_area,
                l.average_yield_per_hectare AS average_yield,
                COALESCE(fo.name, '') AS organization
                , COALESCE(r.name, '') AS region_name
                , COALESCE(b.name, '') AS branch_name
                , COALESCE(p.name, '') AS province_name
                , COALESCE(w.name, '') AS warehouse_name
            FROM farmers f
            LEFT JOIN landholdings l ON l.farmer_id = f.id
            LEFT JOIN farmer_organizations fo ON fo.id = f.farmer_organization_id
            LEFT JOIN warehouse_offices w ON w.id = f.warehouse_id
            LEFT JOIN province_offices p ON p.id = w.province_id
            LEFT JOIN branch_offices b ON b.id = COALESCE(p.branch_id, w.branch_id)
            LEFT JOIN regions r ON r.id = b.region_id
            ORDER BY f.created_at DESC, f.id DESC
        ";

        return array_map([self::class, 'decodeJsonFields'], Database::connection()->query($sql)->fetchAll());
    }

    public static function search(array $filters): array
    {
        self::ensureFarmerKeySchema();
        $sql = "
            SELECT
                f.id,
                f.farmer_key,
                f.rsbsa_number AS rsbsa,
                f.first_name,
                f.middle_name,
                f.last_name,
                f.address,
                f.birthdate,
                CASE
                    WHEN f.birthdate IS NULL THEN NULL
                    ELSE TIMESTAMPDIFF(YEAR, f.birthdate, CURDATE())
                END AS age,
                f.sex,
                f.photo_path,
                f.gender_orientation,
                f.sector,
                f.is_ip_group_member,
                f.created_at,
                COALESCE((
                    SELECT SUM(limit_t.bags_50kg)
                    FROM transactions limit_t
                    WHERE limit_t.seller_type = 'Individual'
                        AND limit_t.farmer_id = f.id
                        AND YEAR(limit_t.delivery_date) = YEAR(CURDATE())
                ), 0) AS annual_bags_delivered,
                COALESCE(fo.name, '') AS organization,
                COALESCE(l.palay_location, '') AS palay_location,
                COALESCE(r.name, '') AS region_name,
                COALESCE(b.name, '') AS branch_name,
                COALESCE(p.name, '') AS province_name,
                COALESCE(w.name, '') AS warehouse_name
            FROM farmers f
            LEFT JOIN landholdings l ON l.farmer_id = f.id
            LEFT JOIN farmer_organizations fo ON fo.id = f.farmer_organization_id
            LEFT JOIN warehouse_offices w ON w.id = f.warehouse_id
            LEFT JOIN province_offices p ON p.id = w.province_id
            LEFT JOIN branch_offices b ON b.id = COALESCE(p.branch_id, w.branch_id)
            LEFT JOIN regions r ON r.id = b.region_id
            WHERE 1 = 1
        ";
        $params = [];

        if (($filters['q'] ?? '') !== '') {
            $sql .= " AND (
                f.farmer_key LIKE :q_farmer_key
                OR f.rsbsa_number LIKE :q_rsbsa
                OR f.first_name LIKE :q_first_name
                OR f.last_name LIKE :q_last_name
                OR f.address LIKE :q_address
            )";
            $query = '%' . $filters['q'] . '%';
            $params += [
                'q_farmer_key' => $query,
                'q_rsbsa' => $query,
                'q_first_name' => $query,
                'q_last_name' => $query,
                'q_address' => $query,
            ];
        }

        foreach (['region_id' => 'r.id', 'branch_id' => 'b.id', 'province_id' => 'p.id', 'warehouse_id' => 'w.id'] as $key => $column) {
            if (!empty($filters[$key])) {
                $sql .= " AND {$column} = :{$key}";
                $params[$key] = $filters[$key];
            }
        }

        if (!empty($filters['date_from'])) {
            $sql .= ' AND DATE(f.created_at) >= :date_from';
            $params['date_from'] = $filters['date_from'];
        }

        if (!empty($filters['date_to'])) {
            $sql .= ' AND DATE(f.created_at) <= :date_to';
            $params['date_to'] = $filters['date_to'];
        }

        $sql .= ' ORDER BY f.created_at DESC, f.id DESC';
        $stmt = Database::connection()->prepare($sql);
        $stmt->execute($params);

        return array_map([self::class, 'decodeJsonFields'], $stmt->fetchAll());
    }

    public static function find(int $id): ?array
    {
        $farmers = array_filter(self::all(), fn (array $farmer): bool => (int) $farmer['id'] === $id);
        return array_values($farmers)[0] ?? null;
    }

    public static function create(array $farmer): void
    {
        self::ensureFarmerKeySchema();
        $db = Database::connection();
        $db->beginTransaction();

        try {
            $organizationId = self::organizationId($farmer['organization'] ?? '');
            $farmerKey = self::reserveFarmerKey($db);

            $stmt = $db->prepare("
                INSERT INTO farmers (
                    farmer_key, rsbsa_number, first_name, middle_name, last_name, address, birthdate, birthplace,
                    civil_status, spouse_name, dependents, contact_number, email, sex,
                    gender_orientation, sector, is_ip_group_member, farmer_organization_id, warehouse_id, photo_path
                ) VALUES (
                    :farmer_key, :rsbsa, :first_name, :middle_name, :last_name, :address, :birthdate, :birthplace,
                    :civil_status, :spouse, :dependents, :contact, :email, :sex,
                    :gender_orientation, :sector, :is_ip_group_member, :farmer_organization_id, :warehouse_id, :photo_path
                )
            ");
            $stmt->execute([
                'farmer_key' => $farmerKey,
                'rsbsa' => $farmer['rsbsa'],
                'first_name' => $farmer['first_name'],
                'middle_name' => $farmer['middle_name'],
                'last_name' => $farmer['last_name'],
                'address' => $farmer['address'],
                'birthdate' => self::nullable($farmer['birthdate']),
                'birthplace' => $farmer['birthplace'],
                'civil_status' => $farmer['civil_status'],
                'spouse' => $farmer['spouse'],
                'dependents' => (int) ($farmer['dependents'] ?: 0),
                'contact' => $farmer['contact'],
                'email' => $farmer['email'],
                'sex' => $farmer['sex'],
                'gender_orientation' => json_encode($farmer['gender_orientation']),
                'sector' => json_encode($farmer['sector']),
                'is_ip_group_member' => !empty($farmer['is_ip_group_member']) ? 1 : 0,
                'farmer_organization_id' => $organizationId,
                'warehouse_id' => $farmer['warehouse_id'] ?: Location::defaultWarehouseId(),
                'photo_path' => $farmer['photo_path'],
            ]);

            $farmerId = (int) $db->lastInsertId();
            $landholding = $db->prepare("
                INSERT INTO landholdings (
                    farmer_id, classification, irrigated, palay_location,
                    harvested_area_hectares, average_yield_per_hectare
                ) VALUES (
                    :farmer_id, :classification, :irrigated, :palay_location,
                    :harvest_area, :average_yield
                )
            ");
            $landholding->execute([
                'farmer_id' => $farmerId,
                'classification' => json_encode($farmer['landholding']),
                'irrigated' => ($farmer['irrigated'] ?? '') === 'Yes' ? 1 : 0,
                'palay_location' => $farmer['palay_location'],
                'harvest_area' => self::nullable($farmer['harvest_area']),
                'average_yield' => self::nullable($farmer['average_yield']),
            ]);

            $db->commit();
        } catch (\Throwable $exception) {
            $db->rollBack();
            throw $exception;
        }
    }

    public static function update(int $id, array $farmer): void
    {
        self::ensureFarmerKeySchema();
        if ($id <= 0) {
            return;
        }

        $db = Database::connection();
        $db->beginTransaction();

        try {
            $organizationId = self::organizationId($farmer['organization'] ?? '');

            $stmt = $db->prepare("
                UPDATE farmers
                SET
                    rsbsa_number = :rsbsa,
                    first_name = :first_name,
                    middle_name = :middle_name,
                    last_name = :last_name,
                    address = :address,
                    birthdate = :birthdate,
                    birthplace = :birthplace,
                    civil_status = :civil_status,
                    spouse_name = :spouse,
                    dependents = :dependents,
                    contact_number = :contact,
                    email = :email,
                    sex = :sex,
                    gender_orientation = :gender_orientation,
                    sector = :sector,
                    is_ip_group_member = :is_ip_group_member,
                    farmer_organization_id = :farmer_organization_id,
                    warehouse_id = :warehouse_id,
                    photo_path = COALESCE(:photo_path, photo_path)
                WHERE id = :id
            ");
            $stmt->execute([
                'id' => $id,
                'rsbsa' => $farmer['rsbsa'],
                'first_name' => $farmer['first_name'],
                'middle_name' => $farmer['middle_name'],
                'last_name' => $farmer['last_name'],
                'address' => $farmer['address'],
                'birthdate' => self::nullable($farmer['birthdate']),
                'birthplace' => $farmer['birthplace'],
                'civil_status' => $farmer['civil_status'],
                'spouse' => $farmer['spouse'],
                'dependents' => (int) ($farmer['dependents'] ?: 0),
                'contact' => $farmer['contact'],
                'email' => $farmer['email'],
                'sex' => $farmer['sex'],
                'gender_orientation' => json_encode($farmer['gender_orientation']),
                'sector' => json_encode($farmer['sector']),
                'is_ip_group_member' => !empty($farmer['is_ip_group_member']) ? 1 : 0,
                'farmer_organization_id' => $organizationId,
                'warehouse_id' => $farmer['warehouse_id'] ?: Location::defaultWarehouseId(),
                'photo_path' => $farmer['photo_path'],
            ]);

            $landholding = $db->prepare("
                INSERT INTO landholdings (
                    farmer_id, classification, irrigated, palay_location,
                    harvested_area_hectares, average_yield_per_hectare
                ) VALUES (
                    :farmer_id, :classification, :irrigated, :palay_location,
                    :harvest_area, :average_yield
                )
                ON DUPLICATE KEY UPDATE
                    classification = VALUES(classification),
                    irrigated = VALUES(irrigated),
                    palay_location = VALUES(palay_location),
                    harvested_area_hectares = VALUES(harvested_area_hectares),
                    average_yield_per_hectare = VALUES(average_yield_per_hectare)
            ");
            $landholding->execute([
                'farmer_id' => $id,
                'classification' => json_encode($farmer['landholding']),
                'irrigated' => ($farmer['irrigated'] ?? '') === 'Yes' ? 1 : 0,
                'palay_location' => $farmer['palay_location'],
                'harvest_area' => self::nullable($farmer['harvest_area']),
                'average_yield' => self::nullable($farmer['average_yield']),
            ]);

            $db->commit();
        } catch (\Throwable $exception) {
            $db->rollBack();
            throw $exception;
        }
    }

    public static function idFromRsbsa(string $rsbsa): ?int
    {
        self::ensureFarmerKeySchema();
        $stmt = Database::connection()->prepare('SELECT id FROM farmers WHERE rsbsa_number = :rsbsa LIMIT 1');
        $stmt->execute(['rsbsa' => self::extractRsbsa($rsbsa)]);
        $id = $stmt->fetchColumn();

        return $id ? (int) $id : null;
    }

    public static function areIpGroupMembers(array $farmerIds): bool
    {
        self::ensureFarmerKeySchema();
        $farmerIds = array_values(array_unique(array_filter(array_map('intval', $farmerIds), fn (int $id): bool => $id > 0)));
        if ($farmerIds === []) {
            return false;
        }

        $placeholders = implode(', ', array_fill(0, count($farmerIds), '?'));
        $stmt = Database::connection()->prepare("SELECT COUNT(*) FROM farmers WHERE is_ip_group_member = 1 AND id IN ({$placeholders})");
        $stmt->execute($farmerIds);

        return (int) $stmt->fetchColumn() === count($farmerIds);
    }

    public static function organizationId(string $name): ?int
    {
        $name = trim($name);

        if ($name === '') {
            return null;
        }

        $db = Database::connection();
        $stmt = $db->prepare('INSERT IGNORE INTO farmer_organizations (name) VALUES (:name)');
        $stmt->execute(['name' => $name]);

        $select = $db->prepare('SELECT id FROM farmer_organizations WHERE name = :name LIMIT 1');
        $select->execute(['name' => $name]);

        return (int) $select->fetchColumn();
    }

    public static function extractRsbsa(string $value): string
    {
        return trim(explode(' - ', $value)[0]);
    }

    public static function nextKeyPreview(): string
    {
        self::ensureFarmerKeySchema();
        $stmt = Database::connection()->prepare("
            SELECT AUTO_INCREMENT
            FROM information_schema.TABLES
            WHERE TABLE_SCHEMA = DATABASE()
                AND TABLE_NAME = 'farmer_key_sequences'
            LIMIT 1
        ");
        $stmt->execute();

        return self::formatFarmerKey(max(1, (int) $stmt->fetchColumn()));
    }

    private static function reserveFarmerKey(\PDO $db): string
    {
        $db->exec('INSERT INTO farmer_key_sequences () VALUES ()');
        $sequence = (int) $db->lastInsertId();
        if ($sequence > 9999999) {
            throw new \RuntimeException('The farmer key sequence has exceeded seven digits.');
        }

        return self::formatFarmerKey($sequence);
    }

    private static function formatFarmerKey(int $sequence, ?string $period = null): string
    {
        return sprintf('NFAFWSP-%s-%07d', $period ?: date('ym'), $sequence);
    }

    private static function ensureFarmerKeySchema(): void
    {
        static $ready = false;
        if ($ready) {
            return;
        }

        $db = Database::connection();
        $db->exec("
            CREATE TABLE IF NOT EXISTS farmer_key_sequences (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
        $db->exec('ALTER TABLE farmers ADD COLUMN IF NOT EXISTS farmer_key VARCHAR(32) NULL AFTER id');
        $db->exec('ALTER TABLE farmers ADD COLUMN IF NOT EXISTS is_ip_group_member TINYINT(1) NOT NULL DEFAULT 0');

        $indexStmt = $db->prepare("
            SELECT COUNT(*)
            FROM information_schema.STATISTICS
            WHERE TABLE_SCHEMA = DATABASE()
                AND TABLE_NAME = 'farmers'
                AND INDEX_NAME = 'farmers_farmer_key_unique'
        ");
        $indexStmt->execute();
        if ((int) $indexStmt->fetchColumn() === 0) {
            $db->exec('CREATE UNIQUE INDEX farmers_farmer_key_unique ON farmers (farmer_key)');
        }

        $missingFarmers = $db->query("
            SELECT id, DATE_FORMAT(created_at, '%y%m') AS key_period
            FROM farmers
            WHERE farmer_key IS NULL OR farmer_key = ''
            ORDER BY created_at, id
        ")->fetchAll();
        if ($missingFarmers !== []) {
            $sequenceStmt = $db->prepare('INSERT INTO farmer_key_sequences () VALUES ()');
            $updateStmt = $db->prepare('UPDATE farmers SET farmer_key = :farmer_key WHERE id = :id');
            foreach ($missingFarmers as $farmer) {
                $sequenceStmt->execute();
                $sequence = (int) $db->lastInsertId();
                $updateStmt->execute([
                    'id' => $farmer['id'],
                    'farmer_key' => self::formatFarmerKey($sequence, $farmer['key_period'] ?: date('ym')),
                ]);
            }
        }

        $ready = true;
    }

    private static function nullable(string|int|float|null $value): string|int|float|null
    {
        return $value === '' || $value === null ? null : $value;
    }

    private static function decodeJsonFields(array $farmer): array
    {
        foreach (['gender_orientation', 'sector', 'landholding'] as $field) {
            $decoded = json_decode((string) ($farmer[$field] ?? '[]'), true);
            $farmer[$field] = is_array($decoded) ? $decoded : [];
        }

        return $farmer;
    }
}
