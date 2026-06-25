<?php
declare(strict_types=1);

require dirname(__DIR__) . '/app/bootstrap.php';

use App\Core\Database;

$db = Database::connection();

$userStmt = $db->prepare(
    "SELECT
        u.id,
        u.warehouse_id,
        r.name AS region_name,
        b.name AS branch_name,
        p.name AS province_name,
        w.name AS warehouse_name
    FROM users u
    LEFT JOIN regions r ON r.id = u.region_id
    LEFT JOIN branch_offices b ON b.id = u.branch_id
    LEFT JOIN province_offices p ON p.id = u.province_id
    LEFT JOIN warehouse_offices w ON w.id = u.warehouse_id
    WHERE u.username = :username
    LIMIT 1"
);
$userStmt->execute(['username' => '000222']);
$user = $userStmt->fetch();

if (!$user || !$user['warehouse_id']) {
    fwrite(STDERR, "User 000222 was not found or has no default warehouse assigned.\n");
    exit(1);
}

$rsbsa = 'WM000222-LIMIT-2026';
$deliveryDate = '2026-06-15';
$wsr = 'WSR-WM000222-LIMIT-20260615';

$db->beginTransaction();

try {
    $farmerStmt = $db->prepare(
        "INSERT INTO farmers (
            rsbsa_number, first_name, middle_name, last_name, address, birthdate, birthplace,
            civil_status, spouse_name, dependents, contact_number, email, sex, gender_orientation,
            sector, farmer_organization_id, warehouse_id
        ) VALUES (
            :rsbsa_number, 'Daily', 'Limit', 'Tester', :address, '1984-03-12', :birthplace,
            'Married', 'Limit Spouse', 2, '09182229999', 'daily.limit.tester@fwsp.local',
            'Male', :gender_orientation, :sector, NULL, :warehouse_id
        )
        ON DUPLICATE KEY UPDATE
            first_name = VALUES(first_name),
            middle_name = VALUES(middle_name),
            last_name = VALUES(last_name),
            address = VALUES(address),
            birthdate = VALUES(birthdate),
            birthplace = VALUES(birthplace),
            civil_status = VALUES(civil_status),
            spouse_name = VALUES(spouse_name),
            dependents = VALUES(dependents),
            contact_number = VALUES(contact_number),
            email = VALUES(email),
            sex = VALUES(sex),
            gender_orientation = VALUES(gender_orientation),
            sector = VALUES(sector),
            farmer_organization_id = VALUES(farmer_organization_id),
            warehouse_id = VALUES(warehouse_id)"
    );
    $farmerStmt->execute([
        'rsbsa_number' => $rsbsa,
        'address' => sprintf(
            '%s Service Area, %s, %s',
            $user['warehouse_name'] ?? 'NFA Warehouse',
            $user['province_name'] ?? 'Province',
            $user['region_name'] ?? 'Region'
        ),
        'birthplace' => $user['province_name'] ?: $user['branch_name'],
        'gender_orientation' => json_encode(['N/A'], JSON_THROW_ON_ERROR),
        'sector' => json_encode(['Adult'], JSON_THROW_ON_ERROR),
        'warehouse_id' => $user['warehouse_id'],
    ]);

    $farmerIdStmt = $db->prepare('SELECT id FROM farmers WHERE rsbsa_number = :rsbsa_number LIMIT 1');
    $farmerIdStmt->execute(['rsbsa_number' => $rsbsa]);
    $farmerId = (int) $farmerIdStmt->fetchColumn();

    $landholdingStmt = $db->prepare(
        "INSERT INTO landholdings (
            farmer_id, classification, irrigated, palay_location,
            harvested_area_hectares, average_yield_per_hectare
        ) VALUES (
            :farmer_id, :classification, 1, :palay_location, 2.50, 4.20
        )
        ON DUPLICATE KEY UPDATE
            classification = VALUES(classification),
            irrigated = VALUES(irrigated),
            palay_location = VALUES(palay_location),
            harvested_area_hectares = VALUES(harvested_area_hectares),
            average_yield_per_hectare = VALUES(average_yield_per_hectare)"
    );
    $landholdingStmt->execute([
        'farmer_id' => $farmerId,
        'classification' => json_encode(['Riceland', 'Owner-Tiller'], JSON_THROW_ON_ERROR),
        'palay_location' => $user['province_name'] ?: $user['branch_name'],
    ]);

    $transactionStmt = $db->prepare(
        "INSERT INTO transactions (
            seller_type, procurement_type, farmer_id, farmer_organization_id, warehouse_id,
            representative_name, total_members, verified_farm_area, delivery_date,
            warehouse_stock_receipt_number, price_per_kilogram, net_kilogram, bags_50kg, created_by
        ) VALUES (
            'Individual', 'In-Warehouse', :farmer_id, NULL, :warehouse_id,
            NULL, NULL, 2.50, :delivery_date,
            :wsr, 30.00, 4500.00, 90, :created_by
        )
        ON DUPLICATE KEY UPDATE
            farmer_id = VALUES(farmer_id),
            warehouse_id = VALUES(warehouse_id),
            delivery_date = VALUES(delivery_date),
            price_per_kilogram = VALUES(price_per_kilogram),
            net_kilogram = VALUES(net_kilogram),
            bags_50kg = VALUES(bags_50kg),
            created_by = VALUES(created_by)"
    );
    $transactionStmt->execute([
        'farmer_id' => $farmerId,
        'warehouse_id' => $user['warehouse_id'],
        'delivery_date' => $deliveryDate,
        'wsr' => $wsr,
        'created_by' => $user['id'],
    ]);

    $db->commit();
} catch (Throwable $exception) {
    $db->rollBack();
    fwrite(STDERR, $exception->getMessage() . "\n");
    exit(1);
}

printf(
    "Seeded daily limit test farmer %s with 90 bags on %s for user 000222.\n",
    $rsbsa,
    $deliveryDate
);
