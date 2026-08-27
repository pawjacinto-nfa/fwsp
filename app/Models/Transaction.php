<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

final class Transaction
{
    public const MAX_INDIVIDUAL_ANNUAL_BAGS = 400;
    public const PALAY_VARIETIES = ['PD', 'PD1', 'PD1A', 'PD1B', 'PD2A', 'PD2B', 'PD3', 'PW', 'PW1', 'PW3', 'RPD', 'HPD'];

    public static function duplicateWsrExists(string $wsr, int $excludeId = 0): bool
    {
        self::ensureSchema();
        $wsr = trim($wsr);
        if ($wsr === '') return false;

        $stmt = Database::connection()->prepare("SELECT 1 FROM transactions WHERE warehouse_stock_receipt_number = :wsr AND id <> :exclude_id AND warehouse_stock_receipt_number NOT LIKE 'DELETED-%' LIMIT 1");
        $stmt->execute(['wsr' => $wsr, 'exclude_id' => $excludeId]);
        return (bool) $stmt->fetchColumn();
    }

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
                t.palay_variety,
                t.price_per_kilogram AS price,
                t.net_kilogram AS net_kg,
                t.total_cost,
                t.total_amount,
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
            LEFT JOIN warehouse_offices w ON w.id = COALESCE(t.warehouse_id, f.warehouse_id)
            LEFT JOIN province_offices p ON p.id = w.province_id
            LEFT JOIN branch_offices b ON b.id = COALESCE(p.branch_id, w.branch_id)
            LEFT JOIN regions r ON r.id = b.region_id
            WHERE " . self::deletedVisibility('t') . "
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
                t.palay_variety,
                t.price_per_kilogram AS price,
                t.net_kilogram AS net_kg,
                t.total_cost,
                t.total_amount,
                t.bags_50kg AS bags,
                CASE WHEN fo.classification_type = 'Indigenous People Group' THEN 1 ELSE 0 END AS is_ip_group_delivery,
                r.id AS resolved_region_id,
                b.id AS resolved_branch_id,
                p.id AS resolved_province_id,
                w.id AS resolved_warehouse_id,
                COALESCE(r.name, '') AS region_name,
                COALESCE(b.name, '') AS branch_name,
                COALESCE(p.name, '') AS province_name,
                COALESCE(w.name, '') AS warehouse_name
            FROM transactions t
            LEFT JOIN farmers f ON f.id = t.farmer_id
            LEFT JOIN farmer_organizations fo ON fo.id = t.farmer_organization_id
            LEFT JOIN warehouse_offices w ON w.id = COALESCE(t.warehouse_id, f.warehouse_id)
            LEFT JOIN province_offices p ON p.id = w.province_id
            LEFT JOIN branch_offices b ON b.id = COALESCE(p.branch_id, w.branch_id)
            LEFT JOIN regions r ON r.id = b.region_id
            WHERE 1 = 1 AND " . self::deletedVisibility('t') . "
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
                COALESCE(NULLIF(f.rsbsa_number, ''), f.farmer_key, '') AS rsbsa,
                CONCAT(COALESCE(f.first_name, ''), ' ', COALESCE(f.last_name, '')) AS farmer_name,
                COALESCE(fo.name, '') AS fo_name,
                CASE WHEN fo.classification_type = 'Indigenous People Group' THEN 1 ELSE 0 END AS is_ip_group_delivery,
                r.id AS resolved_region_id,
                b.id AS resolved_branch_id,
                p.id AS resolved_province_id,
                w.id AS resolved_warehouse_id,
                COALESCE(r.name, '') AS region_name,
                COALESCE(b.name, '') AS branch_name,
                COALESCE(p.name, '') AS province_name,
                COALESCE(w.name, '') AS warehouse_name
            FROM transactions t
            LEFT JOIN farmers f ON f.id = t.farmer_id
            LEFT JOIN farmer_organizations fo ON fo.id = t.farmer_organization_id
            LEFT JOIN warehouse_offices w ON w.id = COALESCE(t.warehouse_id, f.warehouse_id)
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

    public static function softDelete(int $id): bool
    {
        self::ensureSchema();
        $stmt = Database::connection()->prepare("UPDATE transactions SET warehouse_stock_receipt_number = CONCAT('DELETED-', LEFT(warehouse_stock_receipt_number, 72)) WHERE id = :id AND warehouse_stock_receipt_number NOT LIKE 'DELETED-%'");
        $stmt->execute(['id' => $id]);
        return $stmt->rowCount() > 0;
    }

    public static function create(array $transaction): array
    {
        self::ensureSchema();
        RecordVersion::forRecord('transaction', 0);
        self::assertValidInput($transaction);

        $farmerId = Farmer::idFromRsbsa($transaction['rsbsa'] ?? '');
        $organizationId = FarmerOrganization::idByName($transaction['fo_name'] ?? '');
        $deliveredFarmerIds = array_values(array_unique(array_filter(
            array_map('intval', (array) ($transaction['delivered_farmer_ids'] ?? [])),
            fn (int $id): bool => $id > 0
        )));
        $members = $deliveredFarmerIds !== []
            ? count($deliveredFarmerIds)
            : ($transaction['members'] !== '' && $transaction['members'] !== null
            ? (int) $transaction['members']
            : 0);
        $deliveryDate = self::nullable($transaction['delivery_date']) ?? date('Y-m-d');
        $deliveryYear = (int) substr((string) $deliveryDate, 0, 4);
        $bags = (float) ($transaction['bags'] ?: 0);
        $annualBagsAfterDelivery = 0;
        $reachedAnnualLimit = false;
        $isIpGroupDelivery = ($transaction['type'] ?? '') === 'Farmer Organization'
            && FarmerOrganization::isIndigenousSectorGroup($transaction['fo_name'] ?? '');

        if (($transaction['type'] ?? '') === 'Individual' && $farmerId === null) {
            throw new \DomainException('Select a valid farmer record before recording a transaction.');
        }

        if (($transaction['type'] ?? '') === 'Individual' && $farmerId !== null) {
            $organizationName = Farmer::organizationNameForFarmer($farmerId);
            if ($organizationName !== null) {
                throw new \DomainException('This farmer belongs to the farmer group "' . $organizationName . '" and must transact through Farmer Organization Delivery.');
            }
        }

        if (($transaction['type'] ?? '') === 'Farmer Organization') {
            if (!$organizationId) {
                throw new \DomainException('Select a valid Farmer Group before recording the transaction.');
            }
            self::assertMembersBelongToOrganization($deliveredFarmerIds, $organizationId);
        }

        if (!SystemSetting::allowsNoControlNumberTransactions() && ($transaction['type'] ?? '') === 'Farmer Organization' && $deliveredFarmerIds !== []) {
            $db = Database::connection();
            $flagged = $db->prepare('SELECT id FROM farmers WHERE id = :id AND no_available_control_number = 1');
            foreach ($deliveredFarmerIds as $memberId) {
                $flagged->execute(['id' => $memberId]);
                if ($flagged->fetchColumn() && self::farmerTransactionCount($memberId) > 0) {
                    throw new \DomainException('Transaction declined');
                }
            }
        }

        if (($transaction['type'] ?? '') === 'Individual' && $farmerId !== null) {
            $db = Database::connection();
            $flagStmt = $db->prepare('SELECT no_available_control_number FROM farmers WHERE id = :id');
            $flagStmt->execute(['id' => $farmerId]);
            if (!SystemSetting::allowsNoControlNumberTransactions() && (int) $flagStmt->fetchColumn() === 1 && self::farmerTransactionCount($farmerId) > 0) {
                throw new \DomainException('Transaction declined');
            }
            $existingBags = self::individualAnnualBags($farmerId, $deliveryYear);
            $annualBagsAfterDelivery = $existingBags + $bags;
            if ($annualBagsAfterDelivery > self::MAX_INDIVIDUAL_ANNUAL_BAGS) {
                throw new \DomainException(sprintf(
                    'This farmer has already delivered %s bags in %d. The annual maximum is %d bags, so only %s more bag(s) can be accepted.',
                    self::formatBagCount($existingBags),
                    $deliveryYear,
                    self::MAX_INDIVIDUAL_ANNUAL_BAGS,
                    self::formatBagCount(max(0, self::MAX_INDIVIDUAL_ANNUAL_BAGS - $existingBags))
                ));
            }
            $reachedAnnualLimit = $existingBags < self::MAX_INDIVIDUAL_ANNUAL_BAGS
                && abs($annualBagsAfterDelivery - self::MAX_INDIVIDUAL_ANNUAL_BAGS) < 0.0005;
        }

        $db = Database::connection();
        $warehouseId = $transaction['warehouse_id'] ?: Location::defaultWarehouseId();
        $controlNumber = trim((string) ($transaction['client_control_number'] ?? ''));
        if ($controlNumber !== '') {
            $existing = $db->prepare('SELECT id FROM transactions WHERE client_control_number = :control_number LIMIT 1');
            $existing->execute(['control_number' => $controlNumber]);
            $existingId = $existing->fetchColumn();
            if ($existingId) return ['transaction_id' => (int) $existingId, 'duplicate' => true, 'delivery_year' => $deliveryYear, 'annual_bags' => 0, 'reached_annual_limit' => false];
        }
        $db->beginTransaction();

        try {
            $stmt = $db->prepare("
                INSERT INTO transactions (
                    seller_type, procurement_type, farmer_id, farmer_organization_id, warehouse_id,
                    representative_name, total_members, is_ip_group_delivery, verified_farm_area, delivery_date,
                    warehouse_stock_receipt_number, palay_variety, price_per_kilogram, net_kilogram, total_amount, bags_50kg, client_control_number, created_by
                ) VALUES (
                    :seller_type, :procurement_type, :farmer_id, :farmer_organization_id, :warehouse_id,
                    :representative, :members, :is_ip_group_delivery, :farm_area, :delivery_date,
                    :wsr, :palay_variety, :price, :net_kg, :total_amount, :bags, :client_control_number, :created_by
                )
            ");
            $stmt->execute([
                'seller_type' => $transaction['type'],
                'procurement_type' => $transaction['procurement'],
                'farmer_id' => $farmerId,
                'farmer_organization_id' => $organizationId,
                'warehouse_id' => $warehouseId,
                'representative' => $transaction['representative'],
                'members' => $members,
                'is_ip_group_delivery' => $isIpGroupDelivery ? 1 : 0,
                'farm_area' => self::nullable($transaction['farm_area']),
                'delivery_date' => $deliveryDate,
                'wsr' => $transaction['wsr'],
                'palay_variety' => self::palayVariety($transaction['palay_variety'] ?? ''),
                'price' => (float) ($transaction['price'] ?: 0),
                'net_kg' => (float) ($transaction['net_kg'] ?: 0),
                'total_amount' => ($transaction['total_amount'] ?? '') !== '' ? (float) $transaction['total_amount'] : round((float) ($transaction['price'] ?: 0) * (float) ($transaction['net_kg'] ?: 0), 3),
                'bags' => $bags,
                'client_control_number' => $controlNumber ?: null,
                'created_by' => $_SESSION['user_id'] ?? null,
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

            RecordVersion::record('transaction', $transactionId, [], $transaction);

            $db->commit();
        } catch (\Throwable $exception) {
            $db->rollBack();
            throw $exception;
        }

        return [
            'transaction_id' => $transactionId,
            'duplicate' => false,
            'farmer_id' => $farmerId,
            'delivery_year' => $deliveryYear,
            'annual_bags' => $annualBagsAfterDelivery,
            'reached_annual_limit' => $reachedAnnualLimit,
        ];
    }

    public static function individualAnnualBags(int $farmerId, int $deliveryYear, int $excludeTransactionId = 0): float
    {
        $sql = "
            SELECT COALESCE(SUM(bags_50kg), 0)
            FROM transactions
            WHERE seller_type = 'Individual'
                AND farmer_id = :farmer_id
                AND YEAR(delivery_date) = :delivery_year
        ";
        $params = [
            'farmer_id' => $farmerId,
            'delivery_year' => $deliveryYear,
        ];
        if ($excludeTransactionId > 0) {
            $sql .= ' AND id <> :exclude_transaction_id';
            $params['exclude_transaction_id'] = $excludeTransactionId;
        }
        $stmt = Database::connection()->prepare($sql);
        $stmt->execute($params);

        return (float) $stmt->fetchColumn();
    }

    public static function individualTransactionCount(int $farmerId): int
    {
        $stmt = Database::connection()->prepare("SELECT COUNT(*) FROM transactions WHERE seller_type = 'Individual' AND farmer_id = :farmer_id");
        $stmt->execute(['farmer_id' => $farmerId]);
        return (int) $stmt->fetchColumn();
    }

    public static function farmerTransactionCount(int $farmerId): int
    {
        $stmt = Database::connection()->prepare("SELECT COUNT(*) FROM transactions t WHERE t.farmer_id = :id OR EXISTS (SELECT 1 FROM transaction_farmer_members tfm WHERE tfm.transaction_id = t.id AND tfm.farmer_id = :id)");
        $stmt->execute(['id' => $farmerId]);
        return (int) $stmt->fetchColumn();
    }

    public static function update(int $id, array $transaction): void
    {
        self::ensureSchema();
        RecordVersion::forRecord('transaction', 0);
        $existing = self::find($id);
        if (!$existing || strtotime((string) $existing['created_at']) < strtotime('-14 days')) throw new \DomainException('This transaction is no longer editable after two weeks.');

        $sellerType = (string) ($existing['seller_type'] ?? '');
        self::assertValidInput(['type' => $sellerType] + $transaction);
        $deliveryDate = self::nullable($transaction['delivery_date'] ?? '') ?? date('Y-m-d');
        $bags = (float) ($transaction['bags'] ?? 0);
        $organizationId = (int) ($existing['farmer_organization_id'] ?? 0);
        $deliveredFarmerIds = array_values(array_unique(array_filter(
            array_map('intval', (array) ($transaction['delivered_farmer_ids'] ?? [])),
            static fn (int $farmerId): bool => $farmerId > 0
        )));

        if ($sellerType === 'Individual' && !empty($existing['farmer_id'])) {
            $deliveryYear = (int) substr((string) $deliveryDate, 0, 4);
            $otherBags = self::individualAnnualBags((int) $existing['farmer_id'], $deliveryYear, $id);
            if ($otherBags + $bags > self::MAX_INDIVIDUAL_ANNUAL_BAGS) {
                throw new \DomainException(sprintf(
                    'This farmer has already delivered %s other bags in %d. The annual maximum is %d bags, so only %s bag(s) can be saved for this transaction.',
                    self::formatBagCount($otherBags),
                    $deliveryYear,
                    self::MAX_INDIVIDUAL_ANNUAL_BAGS,
                    self::formatBagCount(max(0, self::MAX_INDIVIDUAL_ANNUAL_BAGS - $otherBags))
                ));
            }
        }

        if ($sellerType === 'Farmer Organization') {
            $organizationId = FarmerOrganization::idByName((string) ($transaction['fo_name'] ?? '')) ?? 0;
            if ($organizationId <= 0) {
                throw new \DomainException('Select a valid Farmer Group before saving the transaction.');
            }
            self::assertMembersBelongToOrganization($deliveredFarmerIds, $organizationId);
        }

        $beforeForHistory = [
            'procurement' => $existing['procurement_type'] ?? '', 'representative' => $existing['representative_name'] ?? '',
            'members' => $existing['total_members'] ?? '', 'farm_area' => $existing['verified_farm_area'] ?? '',
            'delivery_date' => $existing['delivery_date'] ?? '', 'wsr' => $existing['wsr'] ?? '',
            'palay_variety' => $existing['palay_variety'] ?? 'PD1', 'price' => $existing['price_per_kilogram'] ?? '',
            'net_kg' => $existing['net_kilogram'] ?? '', 'total_amount' => $existing['total_amount'] ?? '', 'bags' => $existing['bags_50kg'] ?? '',
            'warehouse_id' => $existing['resolved_warehouse_id'] ?? $existing['warehouse_id'] ?? '',
            'fo_name' => $existing['fo_name'] ?? '',
            'delivered_farmer_ids' => array_map('intval', array_column($existing['delivered_members'] ?? [], 'id')),
        ];

        $normalized = $transaction;
        $normalized['delivery_date'] = $deliveryDate;
        $normalized['bags'] = $bags;
        $normalized['palay_variety'] = self::palayVariety((string) ($transaction['palay_variety'] ?? ''));
        $normalized['total_amount'] = ($transaction['total_amount'] ?? '') !== ''
            ? (float) $transaction['total_amount']
            : round((float) $transaction['price'] * (float) $transaction['net_kg'], 3);
        $normalized['warehouse_id'] = self::nullable($transaction['warehouse_id'] ?? null);
        $normalized['fo_name'] = $sellerType === 'Farmer Organization' ? (string) ($transaction['fo_name'] ?? '') : '';
        $normalized['delivered_farmer_ids'] = $sellerType === 'Farmer Organization' ? $deliveredFarmerIds : [];
        if ($sellerType === 'Farmer Organization' && $deliveredFarmerIds !== []) {
            $normalized['members'] = count($deliveredFarmerIds);
        }
        $warehouseId = $normalized['warehouse_id'] ?: Location::defaultWarehouseId();

        $db = Database::connection();
        $db->beginTransaction();
        try {
            $stmt = $db->prepare('UPDATE transactions SET procurement_type=:procurement, farmer_organization_id=:farmer_organization_id, representative_name=:representative, total_members=:members, verified_farm_area=:farm_area, delivery_date=:delivery_date, warehouse_stock_receipt_number=:wsr, palay_variety=:palay_variety, price_per_kilogram=:price, net_kilogram=:net_kg, total_amount=:total_amount, bags_50kg=:bags, warehouse_id=:warehouse_id WHERE id=:id');
            $stmt->execute([
                'id' => $id,
                'procurement' => $normalized['procurement'],
                'farmer_organization_id' => $sellerType === 'Farmer Organization' ? $organizationId : null,
                'representative' => $normalized['representative'],
                'members' => $normalized['members'] !== '' ? $normalized['members'] : null,
                'farm_area' => self::nullable($normalized['farm_area']),
                'delivery_date' => $normalized['delivery_date'],
                'wsr' => $normalized['wsr'],
                'palay_variety' => $normalized['palay_variety'],
                'price' => (float) $normalized['price'],
                'net_kg' => (float) $normalized['net_kg'],
                'total_amount' => (float) $normalized['total_amount'],
                'bags' => $normalized['bags'],
                'warehouse_id' => $warehouseId,
            ]);

            if ($sellerType === 'Farmer Organization') {
                $db->prepare('DELETE FROM transaction_farmer_members WHERE transaction_id = :transaction_id')
                    ->execute(['transaction_id' => $id]);
                $memberStmt = $db->prepare('INSERT INTO transaction_farmer_members (transaction_id, farmer_id) VALUES (:transaction_id, :farmer_id)');
                foreach ($deliveredFarmerIds as $farmerId) {
                    $memberStmt->execute(['transaction_id' => $id, 'farmer_id' => $farmerId]);
                }
            }

            RecordVersion::record('transaction', $id, $beforeForHistory, $normalized);
            $db->commit();
        } catch (\Throwable $exception) {
            if ($db->inTransaction()) $db->rollBack();
            throw $exception;
        }
    }

    public static function individualAnnualBagsForRsbsa(string $rsbsa, int $deliveryYear): float
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

    /** @param list<int> $farmerIds */
    private static function assertMembersBelongToOrganization(array $farmerIds, ?int $organizationId): void
    {
        if ($farmerIds === []) return;
        if (!$organizationId) {
            throw new \DomainException('Select a valid Farmer Group before adding delivered farmers.');
        }

        $placeholders = implode(', ', array_fill(0, count($farmerIds), '?'));
        $stmt = Database::connection()->prepare("
            SELECT COUNT(*)
            FROM farmers
            WHERE farmer_organization_id = ?
              AND id IN ({$placeholders})
        ");
        $stmt->execute([$organizationId, ...$farmerIds]);
        if ((int) $stmt->fetchColumn() !== count($farmerIds)) {
            throw new \DomainException('Every delivered farmer must belong to the selected Farmer Group.');
        }
    }

    private static function formatBagCount(float $bags): string
    {
        return rtrim(rtrim(number_format($bags, 3, '.', ''), '0'), '.');
    }

    private static function assertValidInput(array $transaction): void
    {
        if (!in_array($transaction['type'] ?? '', ['Individual', 'Farmer Organization'], true)) {
            throw new \DomainException('Select a valid seller type.');
        }
        if (!in_array($transaction['procurement'] ?? '', ['In-Warehouse', 'Mobile Procurement'], true)) {
            throw new \DomainException('Select a valid procurement method.');
        }
        if (trim((string) ($transaction['wsr'] ?? '')) === '') {
            throw new \DomainException('Enter the WSR number.');
        }

        $deliveryDate = (string) ($transaction['delivery_date'] ?? '');
        $parsedDate = \DateTimeImmutable::createFromFormat('!Y-m-d', $deliveryDate);
        if (!$parsedDate || $parsedDate->format('Y-m-d') !== $deliveryDate) {
            throw new \DomainException('Enter a valid delivery date.');
        }

        foreach (['price' => 'Price/Kg', 'net_kg' => 'Net Kilogram', 'bags' => 'Bags Delivered'] as $field => $label) {
            $value = $transaction[$field] ?? null;
            if (!is_numeric($value) || (float) $value <= 0) {
                throw new \DomainException($label . ' must be greater than zero.');
            }
        }
        foreach (['farm_area' => 'Verified Farm Area', 'total_amount' => 'Total Amount', 'members' => 'Total Farmer-Members'] as $field => $label) {
            $value = $transaction[$field] ?? '';
            if ($value !== '' && (!is_numeric($value) || (float) $value < 0)) {
                throw new \DomainException($label . ' cannot be negative.');
            }
        }
    }

    private static function ensureSchema(): void
    {
        static $ready = false;
        if ($ready) return;

        FarmerOrganization::ensureSchema();
        Database::connection()->exec('ALTER TABLE transactions ADD COLUMN IF NOT EXISTS is_ip_group_delivery TINYINT(1) NOT NULL DEFAULT 0');
        Database::connection()->exec('ALTER TABLE transactions ADD COLUMN IF NOT EXISTS client_control_number VARCHAR(96) NULL');
        Database::connection()->exec('ALTER TABLE transactions ADD COLUMN IF NOT EXISTS total_amount DECIMAL(20,3) NOT NULL DEFAULT 0');
        Database::connection()->exec("ALTER TABLE transactions ADD COLUMN IF NOT EXISTS palay_variety VARCHAR(10) NOT NULL DEFAULT 'PD1' AFTER warehouse_stock_receipt_number");
        Database::connection()->exec('ALTER TABLE transactions MODIFY verified_farm_area DECIMAL(10,3) NULL, MODIFY price_per_kilogram DECIMAL(10,3) NOT NULL, MODIFY net_kilogram DECIMAL(12,3) NOT NULL, MODIFY bags_50kg DECIMAL(12,3) NOT NULL');
        Database::connection()->exec('UPDATE transactions SET total_amount = ROUND(price_per_kilogram * net_kilogram, 3) WHERE total_amount = 0');
        try { Database::connection()->exec('CREATE UNIQUE INDEX transactions_client_control_number_unique ON transactions (client_control_number)'); } catch (\Throwable) { }
        Database::connection()->exec('
            ALTER TABLE transactions
            ADD COLUMN IF NOT EXISTS total_amount DECIMAL(20,3) NOT NULL DEFAULT 0
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
        $ready = true;
    }

    private static function nullable(string|int|float|null $value): string|int|float|null
    {
        return $value === '' || $value === null ? null : $value;
    }

    private static function palayVariety(string $value): string
    {
        return in_array($value, self::PALAY_VARIETIES, true) ? $value : 'PD1';
    }

    private static function deletedVisibility(string $alias): string
    {
        return (($_SESSION['role'] ?? '') === 'System Admin')
            ? '1 = 1'
            : "COALESCE({$alias}.warehouse_stock_receipt_number, '') NOT LIKE 'DELETED-%'";
    }
}
