<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

final class Farmer
{
    public static function all(): array
    {
        $sql = "
            SELECT
                f.id,
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
                f.warehouse_id,
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
        $sql = "
            SELECT
                f.id,
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
                f.created_at,
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
            $sql .= " AND (f.rsbsa_number LIKE :q OR f.first_name LIKE :q OR f.last_name LIKE :q OR f.address LIKE :q)";
            $params['q'] = '%' . $filters['q'] . '%';
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
        $db = Database::connection();
        $db->beginTransaction();

        try {
            $organizationId = self::organizationId($farmer['organization'] ?? '');

            $stmt = $db->prepare("
                INSERT INTO farmers (
                    rsbsa_number, first_name, middle_name, last_name, address, birthdate, birthplace,
                    civil_status, spouse_name, dependents, contact_number, email, sex,
                    gender_orientation, sector, farmer_organization_id, warehouse_id, photo_path
                ) VALUES (
                    :rsbsa, :first_name, :middle_name, :last_name, :address, :birthdate, :birthplace,
                    :civil_status, :spouse, :dependents, :contact, :email, :sex,
                    :gender_orientation, :sector, :farmer_organization_id, :warehouse_id, :photo_path
                )
            ");
            $stmt->execute([
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
        $stmt = Database::connection()->prepare('SELECT id FROM farmers WHERE rsbsa_number = :rsbsa LIMIT 1');
        $stmt->execute(['rsbsa' => self::extractRsbsa($rsbsa)]);
        $id = $stmt->fetchColumn();

        return $id ? (int) $id : null;
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
