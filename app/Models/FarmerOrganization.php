<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

final class FarmerOrganization
{
    public const CLASSIFICATION_ORGANIZATION = 'Farmer Organization';
    public const CLASSIFICATION_INDIGENOUS = 'Indigenous People Group';

    public static function all(array $filters = []): array
    {
        self::ensureSchema();
        $where = [];
        $params = [];
        foreach (['region_id' => 'r.id', 'branch_id' => 'b.id', 'province_id' => 'p.id', 'warehouse_id' => 'fo.warehouse_id'] as $key => $column) {
            if (!empty($filters[$key])) {
                $where[] = "{$column} = :{$key}";
                $params[$key] = (int) $filters[$key];
            }
        }
        if (empty($filters['include_deleted'])) {
            $where[] = 'fo.deleted_at IS NULL';
        }
        $whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';
        $stmt = Database::connection()->prepare("
            SELECT
                fo.id,
                fo.name,
                fo.total_members,
                fo.office_location,
                fo.is_indigenous_sector_group,
                fo.classification_type,
                fo.warehouse_id,
                fo.created_at,
                fo.deleted_at,
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
            {$whereSql}
            ORDER BY fo.name
        ");
        $stmt->execute($params);

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
                fo.is_indigenous_sector_group,
                fo.classification_type,
                fo.warehouse_id,
                fo.created_at,
                fo.deleted_at,
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

    public static function isIndigenousSectorGroup(string $name): bool
    {
        self::ensureSchema();
        $stmt = Database::connection()->prepare('SELECT classification_type FROM farmer_organizations WHERE name = :name AND deleted_at IS NULL LIMIT 1');
        $stmt->execute(['name' => trim($name)]);

        return $stmt->fetchColumn() === self::CLASSIFICATION_INDIGENOUS;
    }

    public static function idByName(string $name): ?int
    {
        self::ensureSchema();
        $stmt = Database::connection()->prepare('SELECT id FROM farmer_organizations WHERE name = :name AND deleted_at IS NULL LIMIT 1');
        $stmt->execute(['name' => trim($name)]);
        $id = $stmt->fetchColumn();

        return $id === false ? null : (int) $id;
    }

    public static function likelyDuplicates(string $name, string $classificationType = self::CLASSIFICATION_ORGANIZATION, int $excludeId = 0): array
    {
        self::ensureSchema();
        $name = trim($name);

        if ($name === '') {
            return [];
        }

        $stmt = Database::connection()->prepare("
            SELECT
                fo.id,
                fo.name,
                fo.classification_type,
                COALESCE(w.name, '') AS warehouse_name
            FROM farmer_organizations fo
            LEFT JOIN warehouse_offices w ON w.id = fo.warehouse_id
            WHERE fo.classification_type = :classification_type
                AND fo.id <> :exclude_id
                AND fo.deleted_at IS NULL
            ORDER BY fo.name
        ");
        $stmt->execute([
            'classification_type' => $classificationType,
            'exclude_id' => $excludeId,
        ]);

        $needle = self::nameFingerprint($name);
        $needleCompact = self::compactName($needle);
        $matches = [];

        foreach ($stmt->fetchAll() as $organization) {
            $candidate = self::nameFingerprint((string) $organization['name']);
            $candidateCompact = self::compactName($candidate);

            if ($needleCompact === '' || $candidateCompact === '') {
                continue;
            }

            $score = self::nameSimilarityScore($needle, $candidate);
            $compactMatch = $needleCompact === $candidateCompact
                || (strlen($needleCompact) >= 5 && strlen($candidateCompact) >= 5
                    && (str_contains($needleCompact, $candidateCompact) || str_contains($candidateCompact, $needleCompact)));

            if (!$compactMatch && $score < 0.82) {
                continue;
            }

            $organization['similarity'] = $compactMatch ? 1.0 : $score;
            $matches[] = $organization;
        }

        usort($matches, fn (array $left, array $right): int => ($right['similarity'] <=> $left['similarity']) ?: strcmp($left['name'], $right['name']));

        return array_slice($matches, 0, 5);
    }

    public static function create(string $name, int $totalMembers = 0, string $officeLocation = '', bool $isIndigenousSectorGroup = false, ?int $warehouseId = null): void
    {
        self::ensureSchema();
        $name = trim($name);

        if ($name === '') {
            return;
        }

        $stmt = Database::connection()->prepare("
            INSERT INTO farmer_organizations (name, total_members, office_location, is_indigenous_sector_group, classification_type, warehouse_id, deleted_at)
            VALUES (:name, :total_members, :office_location, :is_indigenous_sector_group, :classification_type, :warehouse_id, NULL)
            ON DUPLICATE KEY UPDATE
                total_members = VALUES(total_members),
                office_location = VALUES(office_location),
                is_indigenous_sector_group = VALUES(is_indigenous_sector_group),
                classification_type = VALUES(classification_type),
                warehouse_id = VALUES(warehouse_id),
                deleted_at = NULL
        ");
        $stmt->execute([
            'name' => $name,
            'total_members' => max(0, $totalMembers),
            'office_location' => trim($officeLocation),
            'is_indigenous_sector_group' => $isIndigenousSectorGroup ? 1 : 0,
            'classification_type' => $isIndigenousSectorGroup ? self::CLASSIFICATION_INDIGENOUS : self::CLASSIFICATION_ORGANIZATION,
            'warehouse_id' => $warehouseId && $warehouseId > 0 ? $warehouseId : null,
        ]);
    }

    public static function update(int $id, string $name, int $totalMembers = 0, string $officeLocation = '', bool $isIndigenousSectorGroup = false, ?int $warehouseId = null): void
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
                office_location = :office_location,
                is_indigenous_sector_group = :is_indigenous_sector_group,
                classification_type = :classification_type,
                warehouse_id = :warehouse_id,
                deleted_at = NULL
            WHERE id = :id
        ');
        $stmt->execute([
            'id' => $id,
            'name' => $name,
            'total_members' => max(0, $totalMembers),
            'office_location' => trim($officeLocation),
            'is_indigenous_sector_group' => $isIndigenousSectorGroup ? 1 : 0,
            'classification_type' => $isIndigenousSectorGroup ? self::CLASSIFICATION_INDIGENOUS : self::CLASSIFICATION_ORGANIZATION,
            'warehouse_id' => $warehouseId && $warehouseId > 0 ? $warehouseId : null,
        ]);
    }

    public static function softDelete(int $id): bool
    {
        self::ensureSchema();
        if ($id <= 0) {
            return false;
        }

        $stmt = Database::connection()->prepare('
            UPDATE farmer_organizations
            SET deleted_at = CURRENT_TIMESTAMP
            WHERE id = :id
                AND deleted_at IS NULL
        ');
        $stmt->execute(['id' => $id]);

        return $stmt->rowCount() > 0;
    }

    public static function ensureSchema(): void
    {
        static $ready = false;

        if ($ready) {
            return;
        }

        $db = Database::connection();
        $db->exec('ALTER TABLE farmer_organizations ADD COLUMN IF NOT EXISTS total_members INT UNSIGNED NOT NULL DEFAULT 0');
        $db->exec('ALTER TABLE farmer_organizations ADD COLUMN IF NOT EXISTS office_location VARCHAR(255) NULL');
        $db->exec('ALTER TABLE farmer_organizations ADD COLUMN IF NOT EXISTS warehouse_id BIGINT UNSIGNED NULL');
        $db->exec('ALTER TABLE farmer_organizations ADD COLUMN IF NOT EXISTS is_indigenous_sector_group TINYINT(1) NOT NULL DEFAULT 0');
        $db->exec("ALTER TABLE farmer_organizations ADD COLUMN IF NOT EXISTS classification_type VARCHAR(40) NOT NULL DEFAULT 'Farmer Organization'");
        $db->exec('ALTER TABLE farmer_organizations ADD COLUMN IF NOT EXISTS deleted_at TIMESTAMP NULL');
        $db->exec("
            UPDATE farmer_organizations
            SET classification_type = CASE
                WHEN is_indigenous_sector_group = 1 THEN 'Indigenous People Group'
                ELSE 'Farmer Organization'
            END
            WHERE classification_type NOT IN ('Farmer Organization', 'Indigenous People Group')
               OR (is_indigenous_sector_group = 1 AND classification_type <> 'Indigenous People Group')
               OR (is_indigenous_sector_group = 0 AND classification_type <> 'Farmer Organization')
        ");
        $db->exec("
            INSERT INTO farmer_organizations (name, total_members, office_location, is_indigenous_sector_group, classification_type)
            VALUES ('Indigenous Sector Group', 0, '', 1, 'Indigenous People Group')
            ON DUPLICATE KEY UPDATE
                is_indigenous_sector_group = 1,
                classification_type = 'Indigenous People Group'
        ");

        $ready = true;
    }

    private static function nameFingerprint(string $name): string
    {
        $name = strtolower(trim($name));
        $name = str_replace('&', ' and ', $name);
        $name = preg_replace('/[^a-z0-9]+/', ' ', $name) ?? '';
        $tokens = array_values(array_filter(explode(' ', $name), fn (string $token): bool => $token !== ''));
        $genericTokens = [
            'fo' => true,
            'farmer' => true,
            'farmers' => true,
            'organization' => true,
            'organizations' => true,
            'association' => true,
            'assoc' => true,
            'cooperative' => true,
            'coop' => true,
            'group' => true,
            'people' => true,
            'indigenous' => true,
            'sector' => true,
            'delivery' => true,
            'member' => true,
            'members' => true,
        ];
        $meaningfulTokens = array_values(array_filter($tokens, fn (string $token): bool => !isset($genericTokens[$token])));

        return implode(' ', $meaningfulTokens !== [] ? $meaningfulTokens : $tokens);
    }

    private static function compactName(string $name): string
    {
        return preg_replace('/[^a-z0-9]+/', '', $name) ?? '';
    }

    private static function nameSimilarityScore(string $left, string $right): float
    {
        $leftCompact = self::compactName($left);
        $rightCompact = self::compactName($right);
        $maxLength = max(strlen($leftCompact), strlen($rightCompact));

        if ($maxLength === 0) {
            return 0.0;
        }

        $distance = levenshtein($leftCompact, $rightCompact);
        $characterScore = max(0, 1 - ($distance / $maxLength));
        $leftTokens = array_unique(array_filter(explode(' ', $left)));
        $rightTokens = array_unique(array_filter(explode(' ', $right)));
        $intersection = count(array_intersect($leftTokens, $rightTokens));
        $union = count(array_unique(array_merge($leftTokens, $rightTokens)));
        $tokenScore = $union > 0 ? $intersection / $union : 0;

        return max($characterScore, $tokenScore);
    }
}
