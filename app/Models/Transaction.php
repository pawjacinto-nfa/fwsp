<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

final class Transaction
{
    public const MAX_INDIVIDUAL_ANNUAL_BAGS = 400;

    public static function all(): array
    {
        self::ensureSchema();

        $sql = "
            SELECT
                t.id,
                t.seller_type AS type,
                t.procurement_type AS procurement,
                COALESCE(f.rsbsa_number, '') AS rsbsa,
                COALESCE(fo.name, '') AS fo_name,
                t.representative_name AS representative,
                t.total_members AS members,
                t.verified_farm_area AS farm_area,
                t.delivery_date,
                t.warehouse_stock_receipt_number AS wsr,
                t.price_per_kilogram AS price,
                t.net_kilogram AS net_kg,
                t.total_cost,
                t.bags_50kg AS bags
                , CASE WHEN fo.classification_type = 'Indigenous People Group' THEN 1 ELSE 0 END AS is_ip_group_delivery
                , COALESCE(r.name, '') AS region_name
                , COALESCE(b.name, '') AS branch_name
                , COALESCE(p.name, '') AS province_name
                , COALESCE(w.name, '') AS warehouse_name
                , CONCAT(COALESCE(f.first_name, ''), ' ', COALESCE(f.last_name, '')) AS farmer_name
            FROM transactions t
            LEFT JOIN farmers f ON f.id = t.farmer_id
            LEFT JOIN farmer_organizations fo ON fo.id = t.farmer_organization_id
            LEFT JOIN warehouse_offices w ON w.id = COALESCE(f.warehouse_id, t.warehouse_id)
            LEFT JOIN province_offices p ON p.id = w.province_id
            LEFT JOIN branch_offices b ON b.id = COALESCE(p.branch_id, w.branch_id)
            LEFT JOIN regions r ON r.id = b.region_id
            ORDER BY t.delivery_date DESC, t.id DESC
        ";

        return Database::connection()->query($sql)->fetchAll();
    }

    public static function search(array $filters): array
    {
        self::ensureSchema();

        $sql = "
            SELECT
                t.id,
                t.seller_type AS type,
                t.procurement_type AS procurement,
                COALESCE(f.rsbsa_number, '') AS rsbsa,
                CONCAT(COALESCE(f.first_name, ''), ' ', COALESCE(f.last_name, '')) AS farmer_name,
                COALESCE(fo.name, '') AS fo_name,
                t.delivery_date,
                t.warehouse_stock_receipt_number AS wsr,
                t.price_per_kilogram AS price,
                t.net_kilogram AS net_kg,
                t.total_cost,
                t.bags_50kg AS bags,
                CASE WHEN fo.classification_type = 'Indigenous People Group' THEN 1 ELSE 0 END AS is_ip_group_delivery,
                COALESCE(r.name, '') AS region_name,
                COALESCE(b.name, '') AS branch_name,
                COALESCE(p.name, '') AS province_name,
                COALESCE(w.name, '') AS warehouse_name
            FROM transactions t
            LEFT JOIN farmers f ON f.id = t.farmer_id
            LEFT JOIN farmer_organizations fo ON fo.id = t.farmer_organization_id
            LEFT JOIN warehouse_offices w ON w.id = COALESCE(f.warehouse_id, t.warehouse_id)
            LEFT JOIN province_offices p ON p.id = w.province_id
            LEFT JOIN branch_offices b ON b.id = COALESCE(p.branch_id, w.branch_id)
            LEFT JOIN regions r ON r.id = b.region_id
            WHERE 1 = 1
        ";
        $params = [];

        if (($filters['q'] ?? '') !== '') {
            $sql .= " AND (
                t.warehouse_stock_receipt_number LIKE :q_wsr
                OR f.rsbsa_number LIKE :q_rsbsa
                OR f.first_name LIKE :q_first_name
                OR f.last_name LIKE :q_last_name
                OR fo.name LIKE :q_organization
            )";
            $query = '%' . $filters['q'] . '%';
            $params += [
                'q_wsr' => $query,
                'q_rsbsa' => $query,
                'q_first_name' => $query,
                'q_last_name' => $query,
                'q_organization' => $query,
            ];
        }

        foreach (['region_id' => 'r.id', 'branch_id' => 'b.id', 'province_id' => 'p.id', 'warehouse_id' => 'w.id'] as $key => $column) {
            if (!empty($filters[$key])) {
                $sql .= " AND {$column} = :{$key}";
                $params[$key] = $filters[$key];
            }
        }

        $procurementFilters = array_values(array_intersect(
            (array) ($filters['procurement'] ?? []),
            ['In-Warehouse', 'Mobile Procurement']
        ));
        if ($procurementFilters !== []) {
            $placeholders = [];
            foreach ($procurementFilters as $index => $procurement) {
                $key = 'procurement_' . $index;
                $placeholders[] = ':' . $key;
                $params[$key] = $procurement;
            }
            $sql .= ' AND t.procurement_type IN (' . implode(', ', $placeholders) . ')';
        }

        if (!empty($filters['date_from'])) {
            $sql .= ' AND t.delivery_date >= :date_from';
            $params['date_from'] = $filters['date_from'];
        }

        if (!empty($filters['date_to'])) {
            $sql .= ' AND t.delivery_date <= :date_to';
            $params['date_to'] = $filters['date_to'];
        }

        $sql .= ' ORDER BY t.delivery_date DESC, t.id DESC';
        $stmt = Database::connection()->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll();
    }

    public static function find(int $id): ?array
    {
        self::ensureSchema();

        $stmt = Database::connection()->prepare("
            SELECT
                t.*,
                t.warehouse_stock_receipt_number AS wsr,
                COALESCE(f.rsbsa_number, '') AS rsbsa,
                CONCAT(COALESCE(f.first_name, ''), ' ', COALESCE(f.last_name, '')) AS farmer_name,
                COALESCE(fo.name, '') AS fo_name,
                CASE WHEN fo.classification_type = 'Indigenous People Group' THEN 1 ELSE 0 END AS is_ip_group_delivery,
                COALESCE(r.name, '') AS region_name,
                COALESCE(b.name, '') AS branch_name,
                COALESCE(p.name, '') AS province_name,
                COALESCE(w.name, '') AS warehouse_name
            FROM transactions t
            LEFT JOIN farmers f ON f.id = t.farmer_id
            LEFT JOIN farmer_organizations fo ON fo.id = t.farmer_organization_id
            LEFT JOIN warehouse_offices w ON w.id = COALESCE(f.warehouse_id, t.warehouse_id)
            LEFT JOIN province_offices p ON p.id = w.province_id
            LEFT JOIN branch_offices b ON b.id = COALESCE(p.branch_id, w.branch_id)
            LEFT JOIN regions r ON r.id = b.region_id
            WHERE t.id = :id
            LIMIT 1
        ");
        $stmt->execute(['id' => $id]);
        $transaction = $stmt->fetch();

        if (!$transaction) {
            return null;
        }

        $transaction['delivered_members'] = self::deliveredMembers($id);
        if (($transaction['seller_type'] ?? '') === 'Farmer Organization' && $transaction['delivered_members'] === []) {
            $transaction['delivered_members'] = self::organizationMembersForLegacyTransaction($transaction);
        }

        return $transaction;
    }

    public static function create(array $transaction): array
    {
        self::ensureSchema();

        $farmerId = Farmer::idFromRsbsa($transaction['rsbsa'] ?? '');
        $organizationId = Farmer::organizationId($transaction['fo_name'] ?? '');
        $deliveredFarmerIds = array_values(array_unique(array_filter(
            array_map('intval', (array) ($transaction['delivered_farmer_ids'] ?? [])),
            fn (int $id): bool => $id > 0
        )));
        $members = $transaction['members'] !== '' && $transaction['members'] !== null
            ? (int) $transaction['members']
            : count($deliveredFarmerIds);
        $deliveryDate = self::nullable($transaction['delivery_date']) ?? date('Y-m-d');
        $deliveryYear = (int) substr((string) $deliveryDate, 0, 4);
        $bags = (int) ($transaction['bags'] ?: 0);
        $annualBagsAfterDelivery = 0;
        $reachedAnnualLimit = false;
        $isIpGroupDelivery = ($transaction['type'] ?? '') === 'Farmer Organization'
            && FarmerOrganization::isIndigenousSectorGroup($transaction['fo_name'] ?? '');

        if (($transaction['type'] ?? '') === 'Individual' && $farmerId !== null) {
            $existingBags = self::individualAnnualBags($farmerId, $deliveryYear);
            $annualBagsAfterDelivery = $existingBags + $bags;
            if ($annualBagsAfterDelivery > self::MAX_INDIVIDUAL_ANNUAL_BAGS) {
                throw new \DomainException(sprintf(
                    'This farmer has already delivered %d bags in %d. The annual maximum is %d bags, so only %d more bag(s) can be accepted.',
                    $existingBags,
                    $deliveryYear,
                    self::MAX_INDIVIDUAL_ANNUAL_BAGS,
                    max(0, self::MAX_INDIVIDUAL_ANNUAL_BAGS - $existingBags)
                ));
            }
            $reachedAnnualLimit = $existingBags < self::MAX_INDIVIDUAL_ANNUAL_BAGS
                && $annualBagsAfterDelivery === self::MAX_INDIVIDUAL_ANNUAL_BAGS;
        }

        $db = Database::connection();
        $db->beginTransaction();

        try {
            $stmt = $db->prepare("
                INSERT INTO transactions (
                    seller_type, procurement_type, farmer_id, farmer_organization_id, warehouse_id,
                    representative_name, total_members, is_ip_group_delivery, verified_farm_area, delivery_date,
                    warehouse_stock_receipt_number, price_per_kilogram, net_kilogram, bags_50kg
                ) VALUES (
                    :seller_type, :procurement_type, :farmer_id, :farmer_organization_id, :warehouse_id,
                    :representative, :members, :is_ip_group_delivery, :farm_area, :delivery_date,
                    :wsr, :price, :net_kg, :bags
                )
            ");
            $stmt->execute([
                'seller_type' => $transaction['type'],
                'procurement_type' => $transaction['procurement'],
                'farmer_id' => $farmerId,
                'farmer_organization_id' => $organizationId,
                'warehouse_id' => $transaction['warehouse_id'] ?: Location::defaultWarehouseId(),
                'representative' => $transaction['representative'],
                'members' => $members,
                'is_ip_group_delivery' => $isIpGroupDelivery ? 1 : 0,
                'farm_area' => self::nullable($transaction['farm_area']),
                'delivery_date' => $deliveryDate,
                'wsr' => $transaction['wsr'],
                'price' => (float) ($transaction['price'] ?: 0),
                'net_kg' => (float) ($transaction['net_kg'] ?: 0),
                'bags' => $bags,
            ]);

            $transactionId = (int) $db->lastInsertId();
            if (($transaction['type'] ?? '') === 'Farmer Organization' && $deliveredFarmerIds !== []) {
                $memberStmt = $db->prepare("
                    INSERT IGNORE INTO transaction_farmer_members (transaction_id, farmer_id)
                    VALUES (:transaction_id, :farmer_id)
                ");

                foreach ($deliveredFarmerIds as $farmerId) {
                    $memberStmt->execute([
                        'transaction_id' => $transactionId,
                        'farmer_id' => $farmerId,
                    ]);
                }
            }

            $db->commit();
        } catch (\Throwable $exception) {
            $db->rollBack();
            throw $exception;
        }

        return [
            'farmer_id' => $farmerId,
            'delivery_year' => $deliveryYear,
            'annual_bags' => $annualBagsAfterDelivery,
            'reached_annual_limit' => $reachedAnnualLimit,
        ];
    }

    public static function individualAnnualBags(int $farmerId, int $deliveryYear): int
    {
        $stmt = Database::connection()->prepare("
            SELECT COALESCE(SUM(bags_50kg), 0)
            FROM transactions
            WHERE seller_type = 'Individual'
                AND farmer_id = :farmer_id
                AND YEAR(delivery_date) = :delivery_year
        ");
        $stmt->execute([
            'farmer_id' => $farmerId,
            'delivery_year' => $deliveryYear,
        ]);

        return (int) $stmt->fetchColumn();
    }

    public static function individualAnnualBagsForRsbsa(string $rsbsa, int $deliveryYear): int
    {
        $farmerId = Farmer::idFromRsbsa($rsbsa);

        return $farmerId ? self::individualAnnualBags($farmerId, $deliveryYear) : 0;
    }

    public static function deliveredMembers(int $transactionId): array
    {
        self::ensureSchema();

        $stmt = Database::connection()->prepare("
            SELECT
                f.id,
                f.rsbsa_number AS rsbsa,
                CONCAT(COALESCE(f.first_name, ''), ' ', COALESCE(f.last_name, '')) AS full_name,
                COALESCE(fo.name, '') AS organization
            FROM transaction_farmer_members tfm
            INNER JOIN farmers f ON f.id = tfm.farmer_id
            LEFT JOIN farmer_organizations fo ON fo.id = f.farmer_organization_id
            WHERE tfm.transaction_id = :transaction_id
            ORDER BY f.last_name, f.first_name, f.rsbsa_number
        ");
        $stmt->execute(['transaction_id' => $transactionId]);

        return $stmt->fetchAll();
    }

    private static function organizationMembersForLegacyTransaction(array $transaction): array
    {
        $organizationId = (int) ($transaction['farmer_organization_id'] ?? 0);
        if ($organizationId <= 0) {
            return [];
        }

        $limit = (int) ($transaction['total_members'] ?? 0);
        $sql = "
            SELECT
                f.id,
                f.rsbsa_number AS rsbsa,
                CONCAT(COALESCE(f.first_name, ''), ' ', COALESCE(f.last_name, '')) AS full_name,
                COALESCE(fo.name, '') AS organization
            FROM farmers f
            LEFT JOIN farmer_organizations fo ON fo.id = f.farmer_organization_id
            WHERE f.farmer_organization_id = :farmer_organization_id
            ORDER BY f.last_name, f.first_name, f.rsbsa_number
        ";
        if ($limit > 0) {
            $sql .= ' LIMIT ' . $limit;
        }

        $stmt = Database::connection()->prepare($sql);
        $stmt->execute(['farmer_organization_id' => $organizationId]);

        return $stmt->fetchAll();
    }

    private static function ensureSchema(): void
    {
        FarmerOrganization::ensureSchema();
        Database::connection()->exec('ALTER TABLE transactions ADD COLUMN IF NOT EXISTS is_ip_group_delivery TINYINT(1) NOT NULL DEFAULT 0');
        Database::connection()->exec('
            ALTER TABLE transactions
            ADD COLUMN IF NOT EXISTS total_cost DECIMAL(20,2)
            GENERATED ALWAYS AS (ROUND(price_per_kilogram * net_kilogram, 2)) STORED
        ');
        Database::connection()->exec("
            CREATE TABLE IF NOT EXISTS transaction_farmer_members (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                transaction_id BIGINT UNSIGNED NOT NULL,
                farmer_id BIGINT UNSIGNED NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                UNIQUE KEY transaction_farmer_unique (transaction_id, farmer_id),
                KEY transaction_farmer_members_farmer_id_index (farmer_id),
                CONSTRAINT transaction_farmer_members_transaction_fk
                    FOREIGN KEY (transaction_id) REFERENCES transactions(id)
                    ON DELETE CASCADE,
                CONSTRAINT transaction_farmer_members_farmer_fk
                    FOREIGN KEY (farmer_id) REFERENCES farmers(id)
                    ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
    }

    private static function nullable(string|int|float|null $value): string|int|float|null
    {
        return $value === '' || $value === null ? null : $value;
    }
}
