<?php
declare(strict_types=1);

$config = require dirname(__DIR__) . '/app/config/database.php';
$dsn = sprintf(
    'mysql:host=%s;port=%s;dbname=%s;charset=%s',
    $config['host'],
    $config['port'],
    $config['database'],
    $config['charset']
);

$db = new PDO($dsn, $config['username'], $config['password'], [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
]);

try {
    $db->exec('ALTER TABLE farmer_organizations ADD COLUMN IF NOT EXISTS total_members INT UNSIGNED NOT NULL DEFAULT 0');
    $db->exec('ALTER TABLE farmer_organizations ADD COLUMN IF NOT EXISTS office_location VARCHAR(255) NULL');
    $db->exec('ALTER TABLE farmer_organizations ADD COLUMN IF NOT EXISTS warehouse_id BIGINT UNSIGNED NULL');
    $db->exec("
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

    $db->beginTransaction();

    $warehouse = $db->query("
        SELECT
            w.id AS warehouse_id,
            w.name AS warehouse_name,
            p.name AS province_name,
            b.id AS branch_id,
            r.id AS region_id,
            r.name AS region_name
        FROM warehouse_offices w
        LEFT JOIN province_offices p ON p.id = w.province_id
        LEFT JOIN branch_offices b ON b.id = COALESCE(p.branch_id, w.branch_id)
        LEFT JOIN regions r ON r.id = b.region_id
        ORDER BY w.id
        LIMIT 1
    ")->fetch();

    if (!$warehouse) {
        throw new RuntimeException('No warehouse found. Seed the base schema first.');
    }

    $createdBy = (int) $db->query("SELECT id FROM users ORDER BY id LIMIT 1")->fetchColumn();
    if ($createdBy <= 0) {
        throw new RuntimeException('No user found for created_by.');
    }

    $organizations = [
        [
            'name' => 'Nueva Harvest FO',
            'office_location' => 'San Jose, Nueva Ecija',
            'representative' => 'Alma Reyes',
        ],
        [
            'name' => 'Munoz Rice Growers Association',
            'office_location' => 'Munoz, Nueva Ecija',
            'representative' => 'Bernardo Cruz',
        ],
        [
            'name' => 'Central Luzon Palay Producers Cooperative',
            'office_location' => 'Cabanatuan, Nueva Ecija',
            'representative' => 'Carina Santos',
        ],
        [
            'name' => 'Golden Grain Farmers Association',
            'office_location' => 'Talavera, Nueva Ecija',
            'representative' => 'Danilo Garcia',
        ],
    ];
    $firstNames = [
        ['Aida', 'Ben', 'Corazon', 'Dennis', 'Elvie'],
        ['Felisa', 'Gregorio', 'Helen', 'Isko', 'Julieta'],
        ['Kardo', 'Lina', 'Mario', 'Nelia', 'Oscar'],
        ['Perla', 'Quentin', 'Rosalinda', 'Samuel', 'Teresita'],
    ];
    $lastNames = ['Dizon', 'Evangelista', 'Fajardo', 'Galang', 'Hizon'];
    $deliverySchedule = [
        ['date' => '2026-01-16', 'procurement' => 'In-Warehouse', 'suffix' => 'Q1'],
        ['date' => '2026-04-18', 'procurement' => 'Mobile Procurement', 'suffix' => 'Q2'],
        ['date' => '2026-07-20', 'procurement' => 'In-Warehouse', 'suffix' => 'Q3'],
        ['date' => '2026-10-22', 'procurement' => 'Mobile Procurement', 'suffix' => 'Q4'],
    ];

    $organizationStmt = $db->prepare("
        INSERT INTO farmer_organizations (name, total_members, warehouse_id, office_location)
        VALUES (:name, 5, :warehouse_id, :office_location)
        ON DUPLICATE KEY UPDATE
            total_members = 5,
            warehouse_id = VALUES(warehouse_id),
            office_location = VALUES(office_location)
    ");
    $selectOrganizationStmt = $db->prepare('SELECT id FROM farmer_organizations WHERE name = :name LIMIT 1');
    $farmerStmt = $db->prepare("
        INSERT INTO farmers (
            rsbsa_number, first_name, middle_name, last_name, address, birthdate, birthplace,
            civil_status, spouse_name, dependents, contact_number, email, sex,
            gender_orientation, sector, farmer_organization_id, warehouse_id
        ) VALUES (
            :rsbsa, :first_name, 'FO', :last_name, :address, :birthdate, :birthplace,
            :civil_status, :spouse_name, :dependents, :contact_number, :email, :sex,
            JSON_ARRAY('N/A'), JSON_ARRAY('Adult'), :farmer_organization_id, :warehouse_id
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
            warehouse_id = VALUES(warehouse_id)
    ");
    $selectFarmerStmt = $db->prepare('SELECT id FROM farmers WHERE rsbsa_number = :rsbsa LIMIT 1');
    $transactionStmt = $db->prepare("
        INSERT INTO transactions (
            seller_type, procurement_type, farmer_id, farmer_organization_id, warehouse_id,
            representative_name, total_members, verified_farm_area, delivery_date,
            warehouse_stock_receipt_number, price_per_kilogram, net_kilogram, bags_50kg, created_by
        ) VALUES (
            'Farmer Organization', :procurement_type, NULL, :farmer_organization_id, :warehouse_id,
            :representative_name, 5, :verified_farm_area, :delivery_date,
            :wsr, :price_per_kilogram, :net_kilogram, :bags_50kg, :created_by
        )
        ON DUPLICATE KEY UPDATE
            procurement_type = VALUES(procurement_type),
            farmer_organization_id = VALUES(farmer_organization_id),
            warehouse_id = VALUES(warehouse_id),
            representative_name = VALUES(representative_name),
            total_members = VALUES(total_members),
            verified_farm_area = VALUES(verified_farm_area),
            delivery_date = VALUES(delivery_date),
            price_per_kilogram = VALUES(price_per_kilogram),
            net_kilogram = VALUES(net_kilogram),
            bags_50kg = VALUES(bags_50kg),
            created_by = VALUES(created_by)
    ");
    $selectTransactionStmt = $db->prepare('SELECT id FROM transactions WHERE warehouse_stock_receipt_number = :wsr LIMIT 1');
    $clearMembersStmt = $db->prepare('DELETE FROM transaction_farmer_members WHERE transaction_id = :transaction_id');
    $memberStmt = $db->prepare("
        INSERT IGNORE INTO transaction_farmer_members (transaction_id, farmer_id)
        VALUES (:transaction_id, :farmer_id)
    ");

    $seededTransactions = 0;
    $seededMembers = 0;

    foreach ($organizations as $orgIndex => $organization) {
        $organizationStmt->execute([
            'name' => $organization['name'],
            'warehouse_id' => $warehouse['warehouse_id'],
            'office_location' => $organization['office_location'],
        ]);
        $selectOrganizationStmt->execute(['name' => $organization['name']]);
        $organizationId = (int) $selectOrganizationStmt->fetchColumn();
        $memberIds = [];

        for ($memberIndex = 0; $memberIndex < 5; $memberIndex++) {
            $memberNumber = ($orgIndex * 5) + $memberIndex + 1;
            $rsbsa = sprintf('FULLLIST-FO-2026-%02d-%02d', $orgIndex + 1, $memberIndex + 1);
            $farmerStmt->execute([
                'rsbsa' => $rsbsa,
                'first_name' => $firstNames[$orgIndex][$memberIndex],
                'last_name' => $lastNames[$memberIndex],
                'address' => $organization['office_location'],
                'birthdate' => sprintf('%04d-%02d-%02d', 1970 + $memberNumber, ($memberIndex + 3), 12 + $memberIndex),
                'birthplace' => $warehouse['province_name'] ?: 'Nueva Ecija',
                'civil_status' => $memberIndex % 2 === 0 ? 'Married' : 'Single',
                'spouse_name' => $memberIndex % 2 === 0 ? 'Seed Spouse ' . $memberNumber : null,
                'dependents' => $memberIndex + 1,
                'contact_number' => sprintf('0918%07d', 7000000 + $memberNumber),
                'email' => sprintf('full.list.fo%02d.member%02d@example.com', $orgIndex + 1, $memberIndex + 1),
                'sex' => $memberIndex % 2 === 0 ? 'Female' : 'Male',
                'farmer_organization_id' => $organizationId,
                'warehouse_id' => $warehouse['warehouse_id'],
            ]);
            $selectFarmerStmt->execute(['rsbsa' => $rsbsa]);
            $memberIds[] = (int) $selectFarmerStmt->fetchColumn();
            $seededMembers++;
        }

        foreach ($deliverySchedule as $quarterIndex => $schedule) {
            $netKg = 6500 + ($orgIndex * 550) + ($quarterIndex * 425);
            $wsr = sprintf('FULLLIST-FO-%02d-%s', $orgIndex + 1, $schedule['suffix']);
            $transactionStmt->execute([
                'procurement_type' => $schedule['procurement'],
                'farmer_organization_id' => $organizationId,
                'warehouse_id' => $warehouse['warehouse_id'],
                'representative_name' => $organization['representative'],
                'verified_farm_area' => 7.50 + $orgIndex + ($quarterIndex * 0.35),
                'delivery_date' => $schedule['date'],
                'wsr' => $wsr,
                'price_per_kilogram' => 23.00 + ($quarterIndex * 0.25),
                'net_kilogram' => $netKg,
                'bags_50kg' => (int) round($netKg / 50),
                'created_by' => $createdBy,
            ]);

            $selectTransactionStmt->execute(['wsr' => $wsr]);
            $transactionId = (int) $selectTransactionStmt->fetchColumn();
            $clearMembersStmt->execute(['transaction_id' => $transactionId]);
            foreach ($memberIds as $memberId) {
                $memberStmt->execute([
                    'transaction_id' => $transactionId,
                    'farmer_id' => $memberId,
                ]);
            }
            $seededTransactions++;
        }
    }

    $db->commit();
    printf(
        "Seeded %d farmer organizations, %d member farmer records, and %d FO delivery transactions for Full List (FWSP).\n",
        count($organizations),
        $seededMembers,
        $seededTransactions
    );
} catch (Throwable $exception) {
    if ($db->inTransaction()) {
        $db->rollBack();
    }
    fwrite(STDERR, $exception->getMessage() . PHP_EOL);
    exit(1);
}
