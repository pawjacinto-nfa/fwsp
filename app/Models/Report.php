<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

final class Report
{
    public static function summary(string $scope = 'region', array $filters = []): array
    {
        if ($scope !== 'branch' && !self::hasLocationFilters($filters)) {
            return self::nationalSummaryAllRegions($filters);
        }

        $groupField = $scope === 'branch' ? 'b.name' : 'r.name';
        $label = $scope === 'branch' ? 'region_branch' : 'region';
        $where = [];
        $params = [];

        foreach (['region_id' => 'r.id', 'branch_id' => 'b.id', 'province_id' => 'p.id', 'warehouse_id' => 'w.id'] as $key => $column) {
            if (!empty($filters[$key])) {
                $where[] = "{$column} = :{$key}";
                $params[$key] = $filters[$key];
            }
        }

        if (!empty($filters['date_from'])) {
            $where[] = 't.delivery_date >= :date_from';
            $params['date_from'] = $filters['date_from'];
        }

        if (!empty($filters['date_to'])) {
            $where[] = 't.delivery_date <= :date_to';
            $params['date_to'] = $filters['date_to'];
        }

        $whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

        $orderSql = $scope === 'branch'
            ? $groupField
            : self::regionOrderSql($groupField) . ', ' . self::regionVariantOrderSql($groupField) . ', ' . $groupField;
        $sql = "
            SELECT
                {$groupField} AS {$label},
                SUM(CASE WHEN t.seller_type = 'Individual' AND t.procurement_type = 'In-Warehouse' THEN 1 ELSE 0 END) AS individual_farmers,
                SUM(CASE WHEN t.seller_type = 'Individual' AND t.procurement_type = 'In-Warehouse' THEN t.bags_50kg ELSE 0 END) AS individual_qty,
                SUM(CASE WHEN t.seller_type = 'Individual' AND t.procurement_type = 'In-Warehouse' THEN t.net_kilogram * t.price_per_kilogram ELSE 0 END) AS individual_amount,
                SUM(CASE WHEN t.seller_type = 'Individual' AND t.procurement_type = 'Mobile Procurement' THEN 1 ELSE 0 END) AS walkin_farmers,
                SUM(CASE WHEN t.seller_type = 'Individual' AND t.procurement_type = 'Mobile Procurement' THEN t.bags_50kg ELSE 0 END) AS walkin_qty,
                SUM(CASE WHEN t.seller_type = 'Individual' AND t.procurement_type = 'Mobile Procurement' THEN t.net_kilogram * t.price_per_kilogram ELSE 0 END) AS walkin_amount,
                SUM(CASE WHEN t.seller_type = 'Farmer Organization' THEN 1 ELSE 0 END) AS fo_count,
                SUM(CASE WHEN t.seller_type = 'Farmer Organization' THEN COALESCE(t.total_members, 0) ELSE 0 END) AS fo_members,
                SUM(CASE WHEN t.seller_type = 'Farmer Organization' THEN t.bags_50kg ELSE 0 END) AS fo_qty,
                SUM(CASE WHEN t.seller_type = 'Farmer Organization' THEN t.net_kilogram * t.price_per_kilogram ELSE 0 END) AS fo_amount,
                COUNT(t.id) AS total_farmers,
                SUM(t.bags_50kg) AS total_qty,
                SUM(t.net_kilogram * t.price_per_kilogram) AS total_amount
            FROM transactions t
            LEFT JOIN farmers f ON f.id = t.farmer_id
            LEFT JOIN warehouse_offices w ON w.id = COALESCE(f.warehouse_id, t.warehouse_id)
            LEFT JOIN province_offices p ON p.id = w.province_id
            LEFT JOIN branch_offices b ON b.id = COALESCE(p.branch_id, w.branch_id)
            LEFT JOIN regions r ON r.id = b.region_id
            {$whereSql}
            GROUP BY {$groupField}
            ORDER BY {$orderSql}
        ";

        $stmt = Database::connection()->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll();
    }

    private static function nationalSummaryAllRegions(array $filters = []): array
    {
        $transactionConditions = ['t.warehouse_id = w.id'];
        $params = [];

        if (!empty($filters['date_from'])) {
            $transactionConditions[] = 't.delivery_date >= :date_from';
            $params['date_from'] = $filters['date_from'];
        }

        if (!empty($filters['date_to'])) {
            $transactionConditions[] = 't.delivery_date <= :date_to';
            $params['date_to'] = $filters['date_to'];
        }

        $metricSql = self::summaryMetricSql();
        $transactionJoin = implode(' AND ', $transactionConditions);
        $regionNameSql = self::romanRegionNameSql('r.name');
        $sql = "
            SELECT
                {$regionNameSql} AS region,
                {$metricSql}
            FROM regions r
            LEFT JOIN branch_offices b ON b.region_id = r.id
            LEFT JOIN province_offices p ON p.branch_id = b.id
            LEFT JOIN warehouse_offices w ON w.province_id = p.id OR w.branch_id = b.id
            LEFT JOIN transactions t ON {$transactionJoin}
            LEFT JOIN farmers f ON f.id = t.farmer_id
            GROUP BY region
            ORDER BY " . self::regionOrderSql('region') . ', region';

        $stmt = Database::connection()->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll();
    }

    public static function summaryByBranchRegion(array $filters = []): array
    {
        $where = [];
        $params = [];

        foreach (['region_id' => 'r.id', 'branch_id' => 'b.id', 'province_id' => 'p.id', 'warehouse_id' => 'w.id'] as $key => $column) {
            if (!empty($filters[$key])) {
                $where[] = "{$column} = :{$key}";
                $params[$key] = $filters[$key];
            }
        }

        if (!empty($filters['date_from'])) {
            $where[] = 't.delivery_date >= :date_from';
            $params['date_from'] = $filters['date_from'];
        }

        if (!empty($filters['date_to'])) {
            $where[] = 't.delivery_date <= :date_to';
            $params['date_to'] = $filters['date_to'];
        }

        $whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';
        $metricSql = self::summaryMetricSql();
        $sql = "
            SELECT
                COALESCE(r.name, 'Unassigned') AS region,
                COALESCE(b.name, 'Unassigned Branch') AS branch,
                {$metricSql}
            FROM transactions t
            LEFT JOIN farmers f ON f.id = t.farmer_id
            LEFT JOIN warehouse_offices w ON w.id = COALESCE(f.warehouse_id, t.warehouse_id)
            LEFT JOIN province_offices p ON p.id = w.province_id
            LEFT JOIN branch_offices b ON b.id = COALESCE(p.branch_id, w.branch_id)
            LEFT JOIN regions r ON r.id = b.region_id
            {$whereSql}
            GROUP BY COALESCE(r.name, 'Unassigned'), COALESCE(b.name, 'Unassigned Branch')
            ORDER BY COALESCE(r.name, 'Unassigned'), COALESCE(b.name, 'Unassigned Branch')
        ";

        $stmt = Database::connection()->prepare($sql);
        $stmt->execute($params);
        $branchRows = $stmt->fetchAll();

        return self::withRegionTotalRows($branchRows);
    }

    private static function summaryMetricSql(): string
    {
        return "
            SUM(CASE WHEN t.seller_type = 'Individual' AND t.procurement_type = 'In-Warehouse' THEN 1 ELSE 0 END) AS individual_farmers,
            SUM(CASE WHEN t.seller_type = 'Individual' AND t.procurement_type = 'In-Warehouse' THEN t.bags_50kg ELSE 0 END) AS individual_qty,
            SUM(CASE WHEN t.seller_type = 'Individual' AND t.procurement_type = 'In-Warehouse' THEN t.net_kilogram * t.price_per_kilogram ELSE 0 END) AS individual_amount,
            SUM(CASE WHEN t.seller_type = 'Individual' AND t.procurement_type = 'Mobile Procurement' THEN 1 ELSE 0 END) AS walkin_farmers,
            SUM(CASE WHEN t.seller_type = 'Individual' AND t.procurement_type = 'Mobile Procurement' THEN t.bags_50kg ELSE 0 END) AS walkin_qty,
            SUM(CASE WHEN t.seller_type = 'Individual' AND t.procurement_type = 'Mobile Procurement' THEN t.net_kilogram * t.price_per_kilogram ELSE 0 END) AS walkin_amount,
            SUM(CASE WHEN t.seller_type = 'Farmer Organization' THEN 1 ELSE 0 END) AS fo_count,
            SUM(CASE WHEN t.seller_type = 'Farmer Organization' THEN COALESCE(t.total_members, 0) ELSE 0 END) AS fo_members,
            SUM(CASE WHEN t.seller_type = 'Farmer Organization' THEN t.bags_50kg ELSE 0 END) AS fo_qty,
            SUM(CASE WHEN t.seller_type = 'Farmer Organization' THEN t.net_kilogram * t.price_per_kilogram ELSE 0 END) AS fo_amount,
            COUNT(t.id) AS total_farmers,
            SUM(t.bags_50kg) AS total_qty,
            SUM(t.net_kilogram * t.price_per_kilogram) AS total_amount
        ";
    }

    private static function withRegionTotalRows(array $branchRows): array
    {
        $metricKeys = self::summaryMetricKeys();
        $grouped = [];

        foreach ($branchRows as $row) {
            $region = $row['region'] ?: 'Unassigned';
            $grouped[$region][] = $row;
        }

        $rows = [];
        foreach ($grouped as $region => $branches) {
            $total = [
                'row_type' => 'region_total',
                'region' => $region,
                'branch' => '',
                'region_branch' => $region,
            ];
            foreach ($metricKeys as $key) {
                $total[$key] = 0;
            }

            foreach ($branches as $branch) {
                foreach ($metricKeys as $key) {
                    $total[$key] += (float) ($branch[$key] ?? 0);
                }
            }

            $rows[] = $total;
            foreach ($branches as $branch) {
                $branch['row_type'] = 'branch';
                $branch['region_branch'] = $branch['branch'] ?? 'Unassigned Branch';
                $rows[] = $branch;
            }
        }

        return $rows;
    }

    public static function summaryMetricKeys(): array
    {
        return [
            'individual_farmers',
            'individual_qty',
            'individual_amount',
            'walkin_farmers',
            'walkin_qty',
            'walkin_amount',
            'fo_count',
            'fo_members',
            'fo_qty',
            'fo_amount',
            'total_farmers',
            'total_qty',
            'total_amount',
        ];
    }

    private static function hasLocationFilters(array $filters): bool
    {
        foreach (['region_id', 'branch_id', 'province_id', 'warehouse_id'] as $key) {
            if (!empty($filters[$key])) {
                return true;
            }
        }

        return false;
    }

    private static function regionOrderSql(string $column): string
    {
        return "
            CASE
                WHEN {$column} IN ('Region 1', 'Region I') THEN 1
                WHEN {$column} IN ('Region 2', 'Region II') THEN 2
                WHEN {$column} IN ('Region 3', 'Region III') THEN 3
                WHEN {$column} IN ('Region 4', 'Region IV') THEN 4
                WHEN {$column} IN ('Region 5', 'Region V') THEN 5
                WHEN {$column} IN ('Region 6', 'Region VI') THEN 6
                WHEN {$column} IN ('Region 7', 'Region VII') THEN 7
                WHEN {$column} IN ('Region 8', 'Region VIII') THEN 8
                WHEN {$column} IN ('Region 9', 'Region IX') THEN 9
                WHEN {$column} IN ('Region 10', 'Region X') THEN 10
                WHEN {$column} IN ('Region 11', 'Region XI') THEN 11
                WHEN {$column} IN ('Region 12', 'Region XII') THEN 12
                WHEN {$column} IN ('Region 13', 'Region XIII') THEN 13
                WHEN {$column} IN ('Region 14', 'Region XIV') THEN 14
                WHEN {$column} IN ('Region 15', 'Region XV') THEN 15
                WHEN {$column} = 'ARMM' THEN 900
                WHEN {$column} = 'CARAGA' THEN 901
                WHEN {$column} = 'NCR' THEN 902
                ELSE 800
            END
        ";
    }

    private static function regionVariantOrderSql(string $column): string
    {
        return "
            CASE
                WHEN {$column} IN (
                    'Region I', 'Region II', 'Region III', 'Region IV', 'Region V',
                    'Region VI', 'Region VII', 'Region VIII', 'Region IX', 'Region X',
                    'Region XI', 'Region XII', 'Region XIII', 'Region XIV', 'Region XV'
                ) THEN 0
                ELSE 1
            END
        ";
    }

    private static function romanRegionNameSql(string $column): string
    {
        return "
            CASE
                WHEN {$column} = 'Region 1' THEN 'Region I'
                WHEN {$column} = 'Region 2' THEN 'Region II'
                WHEN {$column} = 'Region 3' THEN 'Region III'
                WHEN {$column} = 'Region 4' THEN 'Region IV'
                WHEN {$column} = 'Region 5' THEN 'Region V'
                WHEN {$column} = 'Region 6' THEN 'Region VI'
                WHEN {$column} = 'Region 7' THEN 'Region VII'
                WHEN {$column} = 'Region 8' THEN 'Region VIII'
                WHEN {$column} = 'Region 9' THEN 'Region IX'
                WHEN {$column} = 'Region 10' THEN 'Region X'
                WHEN {$column} = 'Region 11' THEN 'Region XI'
                WHEN {$column} = 'Region 12' THEN 'Region XII'
                WHEN {$column} = 'Region 13' THEN 'Region XIII'
                WHEN {$column} = 'Region 14' THEN 'Region XIV'
                WHEN {$column} = 'Region 15' THEN 'Region XV'
                ELSE {$column}
            END
        ";
    }

    public static function sectoralScore(array $filters): array
    {
        $where = ['f.id IS NOT NULL'];
        $params = [];
        $source = ($filters['source'] ?? 'farmers') === 'sold_palay' ? 'sold_palay' : 'farmers';
        $dateColumn = self::hasColumn('transactions', 'transaction_date') ? 't.transaction_date' : 't.delivery_date';
        $fromSql = $source === 'sold_palay'
            ? 'transactions t INNER JOIN farmers f ON f.id = t.farmer_id'
            : 'farmers f';
        $warehouseJoin = $source === 'sold_palay'
            ? 'w.id = COALESCE(f.warehouse_id, t.warehouse_id)'
            : 'w.id = f.warehouse_id';
        $sectorExpressions = [
            'muslim' => self::flagExpression('farmers', 'muslim', "JSON_CONTAINS(COALESCE(f.sector, JSON_ARRAY()), JSON_QUOTE('Muslim'))"),
            'pwd' => self::flagExpression('farmers', 'persons_with_disability', "JSON_CONTAINS(COALESCE(f.sector, JSON_ARRAY()), JSON_QUOTE('Persons with Disability'))"),
            'ip' => self::flagExpression('farmers', 'indigenous_people', "JSON_CONTAINS(COALESCE(f.sector, JSON_ARRAY()), JSON_QUOTE('Indigenous People'))"),
            'youth' => self::flagExpression('farmers', 'youth', "JSON_CONTAINS(COALESCE(f.sector, JSON_ARRAY()), JSON_QUOTE('Youth'))"),
            'senior' => self::flagExpression('farmers', 'senior_citizen', "JSON_CONTAINS(COALESCE(f.sector, JSON_ARRAY()), JSON_QUOTE('Senior Citizen'))"),
            'male' => self::flagExpression('farmers', 'male', "f.sex = 'Male'"),
            'female' => self::flagExpression('farmers', 'female', "f.sex = 'Female'"),
            'lgbtqia' => self::flagExpression('farmers', 'lgbtqia', "JSON_LENGTH(COALESCE(f.gender_orientation, JSON_ARRAY())) > 0 AND NOT JSON_CONTAINS(COALESCE(f.gender_orientation, JSON_ARRAY()), JSON_QUOTE('N/A'))"),
            'young_age' => "f.birthdate IS NOT NULL AND TIMESTAMPDIFF(YEAR, f.birthdate, CURDATE()) BETWEEN 18 AND 28",
            'adult_age' => "f.birthdate IS NOT NULL AND TIMESTAMPDIFF(YEAR, f.birthdate, CURDATE()) BETWEEN 29 AND 59",
        ];
        $filterExpressions = [
            'male' => $sectorExpressions['male'],
            'female' => $sectorExpressions['female'],
            'young' => $sectorExpressions['young_age'],
            'adult' => $sectorExpressions['adult_age'],
            'senior' => $sectorExpressions['senior'],
            'sogie' => $sectorExpressions['lgbtqia'],
            'muslim' => $sectorExpressions['muslim'],
            'ip' => $sectorExpressions['ip'],
        ];
        $selectedFilters = array_values(array_intersect(
            (array) ($filters['sdd_filter'] ?? []),
            array_keys($filterExpressions)
        ));

        foreach (['region_id' => 'r.id', 'branch_id' => 'b.id', 'province_id' => 'p.id', 'warehouse_id' => 'w.id'] as $key => $column) {
            if (!empty($filters[$key])) {
                $where[] = "{$column} = :{$key}";
                $params[$key] = $filters[$key];
            }
        }

        if ($source === 'sold_palay' && !empty($filters['date_from'])) {
            $where[] = "{$dateColumn} >= :date_from";
            $params['date_from'] = $filters['date_from'];
        }

        if ($source === 'sold_palay' && !empty($filters['date_to'])) {
            $where[] = "{$dateColumn} <= :date_to";
            $params['date_to'] = $filters['date_to'];
        }

        if ($selectedFilters !== []) {
            $selectedWhere = array_map(
                fn (string $key): string => '(' . $filterExpressions[$key] . ')',
                $selectedFilters
            );
            $where[] = '(' . implode(' OR ', $selectedWhere) . ')';
        }

        $whereSql = 'WHERE ' . implode(' AND ', $where);
        $sql = "
            SELECT
                COUNT(*) AS total_farmers,
                SUM(is_sectoral) AS total_sectoral_farmers,
                SUM(is_muslim) AS muslim,
                SUM(is_pwd) AS persons_with_disability,
                SUM(is_ip) AS indigenous_people,
                SUM(is_youth) AS youth,
                SUM(is_senior) AS senior_citizen,
                SUM(is_male) AS male,
                SUM(is_female) AS female,
                SUM(is_lgbtqia) AS lgbtqia,
                SUM(is_young_age) AS young_age,
                SUM(is_adult_age) AS adult_age
            FROM (
                SELECT
                    COALESCE(f.id, 0) AS farmer_key,
                    MAX(CASE WHEN {$sectorExpressions['muslim']} THEN 1 ELSE 0 END) AS is_muslim,
                    MAX(CASE WHEN {$sectorExpressions['pwd']} THEN 1 ELSE 0 END) AS is_pwd,
                    MAX(CASE WHEN {$sectorExpressions['ip']} THEN 1 ELSE 0 END) AS is_ip,
                    MAX(CASE WHEN {$sectorExpressions['youth']} THEN 1 ELSE 0 END) AS is_youth,
                    MAX(CASE WHEN {$sectorExpressions['senior']} THEN 1 ELSE 0 END) AS is_senior,
                    MAX(CASE WHEN {$sectorExpressions['male']} THEN 1 ELSE 0 END) AS is_male,
                    MAX(CASE WHEN {$sectorExpressions['female']} THEN 1 ELSE 0 END) AS is_female,
                    MAX(CASE WHEN {$sectorExpressions['lgbtqia']} THEN 1 ELSE 0 END) AS is_lgbtqia,
                    MAX(CASE WHEN {$sectorExpressions['young_age']} THEN 1 ELSE 0 END) AS is_young_age,
                    MAX(CASE WHEN {$sectorExpressions['adult_age']} THEN 1 ELSE 0 END) AS is_adult_age,
                    MAX(CASE
                        WHEN {$sectorExpressions['male']}
                            OR {$sectorExpressions['female']}
                            OR {$sectorExpressions['lgbtqia']}
                            OR {$sectorExpressions['muslim']}
                            OR {$sectorExpressions['pwd']}
                            OR {$sectorExpressions['ip']}
                            OR {$sectorExpressions['youth']}
                            OR {$sectorExpressions['senior']}
                        THEN 1 ELSE 0 END
                    ) AS is_sectoral
                FROM {$fromSql}
                LEFT JOIN warehouse_offices w ON {$warehouseJoin}
                LEFT JOIN province_offices p ON p.id = w.province_id
                LEFT JOIN branch_offices b ON b.id = COALESCE(p.branch_id, w.branch_id)
                LEFT JOIN regions r ON r.id = b.region_id
                {$whereSql}
                GROUP BY COALESCE(CAST(f.id AS CHAR), f.rsbsa_number)
            ) sectoral
        ";

        $stmt = Database::connection()->prepare($sql);
        $stmt->execute($params);
        $row = $stmt->fetch() ?: [];

        $totalFarmers = (int) ($row['total_farmers'] ?? 0);
        $totalSectoral = (int) ($row['total_sectoral_farmers'] ?? 0);
        $rate = $totalFarmers > 0 ? ($totalSectoral / $totalFarmers) * 100 : 0;

        $breakdown = [
            'Muslim Farmers' => (int) ($row['muslim'] ?? 0),
            'Persons with Disability' => (int) ($row['persons_with_disability'] ?? 0),
            'Indigenous People' => (int) ($row['indigenous_people'] ?? 0),
            'Youth Farmers' => (int) ($row['youth'] ?? 0),
            'Senior Citizen Farmers' => (int) ($row['senior_citizen'] ?? 0),
            'Male Farmers' => (int) ($row['male'] ?? 0),
            'Female Farmers' => (int) ($row['female'] ?? 0),
            'LGBTQIA+ Identifications' => (int) ($row['lgbtqia'] ?? 0),
        ];
        $filterBreakdown = [
            'Male' => (int) ($row['male'] ?? 0),
            'Female' => (int) ($row['female'] ?? 0),
            'Young (18-28)' => (int) ($row['young_age'] ?? 0),
            'Adult (29-59)' => (int) ($row['adult_age'] ?? 0),
            'Senior Citizen' => (int) ($row['senior_citizen'] ?? 0),
            'SOGIE' => (int) ($row['lgbtqia'] ?? 0),
            'Muslim' => (int) ($row['muslim'] ?? 0),
            'Indigenous People' => (int) ($row['indigenous_people'] ?? 0),
        ];
        $filterLabels = [
            'male' => 'Male',
            'female' => 'Female',
            'young' => 'Young (18-28)',
            'adult' => 'Adult (29-59)',
            'senior' => 'Senior Citizen',
            'sogie' => 'SOGIE',
            'muslim' => 'Muslim',
            'ip' => 'Indigenous People',
        ];
        $selectedFilterBreakdown = [];
        foreach ($selectedFilters as $filter) {
            $label = $filterLabels[$filter];
            $selectedFilterBreakdown[$label] = $filterBreakdown[$label];
        }
        $chartBoards = self::sectoralChartBoards($fromSql, $warehouseJoin, $whereSql, $params);

        return [
            'total_farmers' => $totalFarmers,
            'total_sectoral_farmers' => $totalSectoral,
            'inclusivity_rate' => $rate,
            'breakdown' => $breakdown,
            'filter_breakdown' => $filterBreakdown,
            'selected_filter_breakdown' => $selectedFilterBreakdown,
            'selected_filters' => $selectedFilters,
            'chart_boards' => $chartBoards,
        ];
    }

    private static function sectoralChartBoards(string $fromSql, string $warehouseJoin, string $whereSql, array $params): array
    {
        $stmt = Database::connection()->prepare("
            SELECT
                f.id,
                f.sex,
                f.sector,
                f.gender_orientation
            FROM {$fromSql}
            LEFT JOIN warehouse_offices w ON {$warehouseJoin}
            LEFT JOIN province_offices p ON p.id = w.province_id
            LEFT JOIN branch_offices b ON b.id = COALESCE(p.branch_id, w.branch_id)
            LEFT JOIN regions r ON r.id = b.region_id
            {$whereSql}
            GROUP BY f.id, f.sex, f.sector, f.gender_orientation
        ");
        $stmt->execute($params);
        $farmers = $stmt->fetchAll();

        $sex = ['Male' => 0, 'Female' => 0];
        $sectoral = [
            'Muslim' => 0,
            'Persons with Disability' => 0,
            'Indigenous People' => 0,
            'Youth' => 0,
            'Senior Citizen' => 0,
        ];
        $sogie = [
            'Lesbian' => 0,
            'Gay' => 0,
            'Bisexual' => 0,
            'Transgender' => 0,
            'Others' => 0,
        ];

        foreach ($farmers as $farmer) {
            if (isset($sex[$farmer['sex'] ?? ''])) {
                $sex[$farmer['sex']]++;
            }

            $sectorValues = json_decode((string) ($farmer['sector'] ?? '[]'), true);
            if (!is_array($sectorValues)) {
                $sectorValues = [];
            }

            foreach ($sectorValues as $sector) {
                if (isset($sectoral[$sector])) {
                    $sectoral[$sector]++;
                }
            }

            $orientationValues = json_decode((string) ($farmer['gender_orientation'] ?? '[]'), true);
            if (!is_array($orientationValues)) {
                $orientationValues = [];
            }

            $countedOrientations = [];
            $countedOther = false;
            foreach ($orientationValues as $orientation) {
                $orientation = trim((string) $orientation);
                if ($orientation === '' || strtoupper($orientation) === 'N/A') {
                    continue;
                }

                if (isset($sogie[$orientation])) {
                    if (isset($countedOrientations[$orientation])) {
                        continue;
                    }

                    $sogie[$orientation]++;
                    $countedOrientations[$orientation] = true;
                    continue;
                }

                if (!$countedOther) {
                    $sogie['Others']++;
                    $countedOther = true;
                }
            }
        }

        return [
            'sex' => $sex,
            'sectoral' => $sectoral,
            'sogie' => array_filter($sogie, fn (int $count): bool => $count > 0),
        ];
    }

    private static function flagExpression(string $table, string $column, string $fallback): string
    {
        if (!self::hasColumn($table, $column)) {
            return $fallback;
        }

        return "(COALESCE(f.{$column}, 0) IN (1, '1', 'Yes', 'yes', 'Y', 'y', 'True', 'true'))";
    }

    private static function hasColumn(string $table, string $column): bool
    {
        static $cache = [];
        $key = "{$table}.{$column}";

        if (array_key_exists($key, $cache)) {
            return $cache[$key];
        }

        $stmt = Database::connection()->prepare("
            SELECT COUNT(*)
            FROM information_schema.COLUMNS
            WHERE TABLE_SCHEMA = DATABASE()
                AND TABLE_NAME = :table_name
                AND COLUMN_NAME = :column_name
        ");
        $stmt->execute(['table_name' => $table, 'column_name' => $column]);
        $cache[$key] = (int) $stmt->fetchColumn() > 0;

        return $cache[$key];
    }
}
