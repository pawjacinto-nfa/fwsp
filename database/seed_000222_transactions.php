<?php
declare(strict_types=1);

require dirname(__DIR__) . '/app/bootstrap.php';

use App\Core\Database;

$db = Database::connection();

$userStmt = $db->prepare(
    "SELECT
        u.id,
        u.username,
        u.full_name,
        u.warehouse_id,
        r.name AS region_name,
        b.name AS branch_name,
        p.name AS province_name,
        w.name AS warehouse_name
    FROM users u
    LEFT JOIN warehouse_offices w ON w.id = u.warehouse_id
    LEFT JOIN province_offices p ON p.id = w.province_id
    LEFT JOIN branch_offices b ON b.id = COALESCE(p.branch_id, w.branch_id)
    LEFT JOIN regions r ON r.id = b.region_id
    WHERE u.username = :username
    LIMIT 1"
);
$userStmt->execute(['username' => '000222']);
$user = $userStmt->fetch();

if (!$user || !$user['warehouse_id']) {
    fwrite(STDERR, "User 000222 was not found or has no default warehouse assigned.\n");
    exit(1);
}

$farmerStmt = $db->prepare(
    "SELECT id, rsbsa_number
    FROM farmers
    WHERE warehouse_id = :warehouse_id
        AND rsbsa_number LIKE 'WM000222-2026-%'
    ORDER BY rsbsa_number"
);
$farmerStmt->execute(['warehouse_id' => $user['warehouse_id']]);
$farmers = $farmerStmt->fetchAll();

if (count($farmers) < 30) {
    fwrite(STDERR, "Expected 30 seeded farmers for user 000222, found " . count($farmers) . ". Run database/seed_000222_farmers.php first.\n");
    exit(1);
}

$transactionStmt = $db->prepare(
    "INSERT INTO transactions (
        seller_type, procurement_type, farmer_id, farmer_organization_id, warehouse_id,
        representative_name, total_members, verified_farm_area, delivery_date,
        warehouse_stock_receipt_number, price_per_kilogram, net_kilogram, bags_50kg, created_by
    ) VALUES (
        :seller_type, :procurement_type, :farmer_id, NULL, :warehouse_id,
        NULL, NULL, :verified_farm_area, :delivery_date,
        :wsr, :price, :net_kg, :bags, :created_by
    )
    ON DUPLICATE KEY UPDATE
        seller_type = VALUES(seller_type),
        procurement_type = VALUES(procurement_type),
        farmer_id = VALUES(farmer_id),
        farmer_organization_id = VALUES(farmer_organization_id),
        warehouse_id = VALUES(warehouse_id),
        representative_name = VALUES(representative_name),
        total_members = VALUES(total_members),
        verified_farm_area = VALUES(verified_farm_area),
        delivery_date = VALUES(delivery_date),
        price_per_kilogram = VALUES(price_per_kilogram),
        net_kilogram = VALUES(net_kilogram),
        bags_50kg = VALUES(bags_50kg),
        created_by = VALUES(created_by)"
);

$db->beginTransaction();

try {
    for ($index = 0; $index < 50; $index++) {
        $number = $index + 1;
        $farmer = $farmers[$index % count($farmers)];
        $bags = 18 + (($index * 7) % 55);
        $netKg = $bags * 50;
        $month = $index < 25 ? 5 : 6;
        $day = ($index % 25) + 1;

        $transactionStmt->execute([
            'seller_type' => 'Individual',
            'procurement_type' => $index % 3 === 0 ? 'Mobile Procurement' : 'In-Warehouse',
            'farmer_id' => $farmer['id'],
            'warehouse_id' => $user['warehouse_id'],
            'verified_farm_area' => number_format(1.20 + (($index % 12) * 0.28), 2, '.', ''),
            'delivery_date' => sprintf('2026-%02d-%02d', $month, $day),
            'wsr' => sprintf('WSR-WM000222-2026-%03d', $number),
            'price' => number_format(23.00 + (($index % 5) * 0.15), 2, '.', ''),
            'net_kg' => number_format($netKg, 2, '.', ''),
            'bags' => $bags,
            'created_by' => $user['id'],
        ]);
    }

    $db->commit();
} catch (Throwable $exception) {
    $db->rollBack();
    fwrite(STDERR, $exception->getMessage() . "\n");
    exit(1);
}

printf(
    "Seeded 50 transactions for user 000222 at %s, %s, %s, %s.\n",
    $user['region_name'] ?? 'No region',
    $user['branch_name'] ?? 'No branch',
    $user['province_name'] ?? 'No province',
    $user['warehouse_name'] ?? 'No warehouse'
);
