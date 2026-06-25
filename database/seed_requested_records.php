<?php
declare(strict_types=1);

require dirname(__DIR__) . '/app/bootstrap.php';

use App\Core\Database;

$db = Database::connection();
$db->beginTransaction();

try {
    $regionOne = $db->query("SELECT id FROM regions WHERE name = 'Region I' LIMIT 1")->fetchColumn();
    if (!$regionOne) {
        throw new RuntimeException('Region I was not found. Import the location masterlist first.');
    }

    $regionOneLocation = $db->query("
        SELECT b.id AS branch_id, p.id AS province_id, w.id AS warehouse_id
        FROM branch_offices b
        JOIN province_offices p ON p.branch_id = b.id
        JOIN warehouse_offices w ON w.province_id = p.id
        WHERE b.region_id = {$regionOne}
        ORDER BY b.name, p.name, w.name
        LIMIT 1
    ")->fetch();

    if (!$regionOneLocation) {
        throw new RuntimeException('No Region I facility was found.');
    }

    $passwordHash = password_hash('password', PASSWORD_DEFAULT);
    $users = [
        [
            'full_name' => 'Seed Warehouse Manager',
            'username' => '000222',
            'email' => '000222@fwsp.local',
            'role' => 'Warehouse Supervisor',
            'region_id' => $regionOne,
            'branch_id' => $regionOneLocation['branch_id'],
            'province_id' => $regionOneLocation['province_id'],
            'warehouse_id' => $regionOneLocation['warehouse_id'],
            'designation' => 'Warehouse Manager',
            'contact_number' => '09000000222',
        ],
        [
            'full_name' => 'Seed Regional Branch Manager',
            'username' => '000111',
            'email' => '000111@fwsp.local',
            'role' => 'Regional/Branch Manager',
            'region_id' => $regionOne,
            'branch_id' => $regionOneLocation['branch_id'],
            'province_id' => null,
            'warehouse_id' => null,
            'designation' => 'Regional/Branch Manager',
            'contact_number' => '09000000111',
        ],
    ];

    $userStmt = $db->prepare("
        INSERT INTO users (
            full_name, username, email, password_hash, role, is_active, status,
            region_id, branch_id, province_id, warehouse_id, designation, contact_number
        ) VALUES (
            :full_name, :username, :email, :password_hash, :role, 1, 'Active',
            :region_id, :branch_id, :province_id, :warehouse_id, :designation, :contact_number
        )
        ON DUPLICATE KEY UPDATE
            full_name = VALUES(full_name),
            password_hash = VALUES(password_hash),
            role = VALUES(role),
            is_active = 1,
            status = 'Active',
            region_id = VALUES(region_id),
            branch_id = VALUES(branch_id),
            province_id = VALUES(province_id),
            warehouse_id = VALUES(warehouse_id),
            designation = VALUES(designation),
            contact_number = VALUES(contact_number)
    ");

    foreach ($users as $user) {
        $userStmt->execute($user + ['password_hash' => $passwordHash]);
    }

    $createdBy = (int) $db->query("SELECT id FROM users WHERE username = '000222' LIMIT 1")->fetchColumn();

    $locations = $db->query("
        SELECT r.name AS region_name, b.name AS branch_name, p.name AS province_name, w.id AS warehouse_id, w.name AS warehouse_name
        FROM location_masterlist l
        JOIN regions r ON r.name = l.region
        JOIN branch_offices b ON b.region_id = r.id AND b.name = l.branch
        JOIN province_offices p ON p.branch_id = b.id AND p.name = l.province
        JOIN warehouse_offices w ON w.province_id = p.id AND w.name = l.facility_name
        GROUP BY r.name, b.name, p.name, w.id, w.name
        ORDER BY r.name, b.name, p.name, w.name
    ")->fetchAll();

    if (count($locations) < 30) {
        throw new RuntimeException('At least 30 populated facility locations are required.');
    }

    $foName = 'Seed Palay Farmers Organization';
    $db->prepare('INSERT IGNORE INTO farmer_organizations (name) VALUES (:name)')->execute(['name' => $foName]);
    $selectFo = $db->prepare('SELECT id FROM farmer_organizations WHERE name = :name LIMIT 1');
    $selectFo->execute(['name' => $foName]);
    $foId = (int) $selectFo->fetchColumn();

    $farmerStmt = $db->prepare("
        INSERT INTO farmers (
            rsbsa_number, first_name, middle_name, last_name, address, birthdate, birthplace,
            civil_status, spouse_name, dependents, contact_number, email, sex,
            gender_orientation, sector, farmer_organization_id, warehouse_id
        ) VALUES (
            :rsbsa, :first_name, :middle_name, :last_name, :address, :birthdate, :birthplace,
            :civil_status, :spouse_name, :dependents, :contact_number, :email, :sex,
            JSON_ARRAY(), JSON_ARRAY('Adult'), :farmer_organization_id, :warehouse_id
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
            farmer_organization_id = VALUES(farmer_organization_id),
            warehouse_id = VALUES(warehouse_id)
    ");

    $landStmt = $db->prepare("
        INSERT INTO landholdings (
            farmer_id, classification, irrigated, palay_location, harvested_area_hectares, average_yield_per_hectare
        ) VALUES (
            :farmer_id, JSON_ARRAY('Riceland', 'Owner-Tiller'), :irrigated, :palay_location, :area, :yield
        )
        ON DUPLICATE KEY UPDATE
            classification = VALUES(classification),
            irrigated = VALUES(irrigated),
            palay_location = VALUES(palay_location),
            harvested_area_hectares = VALUES(harvested_area_hectares),
            average_yield_per_hectare = VALUES(average_yield_per_hectare)
    ");

    $firstNames = ['Amelia', 'Benito', 'Carla', 'Dante', 'Elena', 'Felix', 'Grace', 'Hector', 'Isabel', 'Jun', 'Karla', 'Leo', 'Minda', 'Nestor', 'Olivia', 'Pedro', 'Queenie', 'Ramon', 'Sofia', 'Tomas', 'Ursula', 'Victor', 'Wena', 'Xander', 'Yolanda', 'Zandro', 'Ana', 'Berto', 'Celia', 'Diego'];
    $lastNames = ['Santos', 'Reyes', 'Cruz', 'Garcia', 'Mendoza', 'Torres', 'Flores', 'Ramos', 'Aquino', 'Bautista', 'Castro', 'Diaz', 'Enriquez', 'Fernandez', 'Gonzales', 'Hernandez', 'Ilagan', 'Jimenez', 'Lazaro', 'Morales', 'Navarro', 'Ocampo', 'Perez', 'Quinto', 'Rivera', 'Salazar', 'Tolentino', 'Uy', 'Villanueva', 'Zamora'];
    $farmerIds = [];

    for ($i = 1; $i <= 30; $i++) {
        $location = $locations[(int) floor(($i - 1) * count($locations) / 30)];
        $rsbsa = sprintf('SEED-2026-%03d', $i);
        $isFoMember = $i > 20;

        $farmerStmt->execute([
            'rsbsa' => $rsbsa,
            'first_name' => $firstNames[$i - 1],
            'middle_name' => 'Seed',
            'last_name' => $lastNames[$i - 1],
            'address' => $location['province_name'] . ', ' . $location['region_name'],
            'birthdate' => sprintf('%04d-%02d-%02d', 1975 + ($i % 22), (($i - 1) % 12) + 1, (($i - 1) % 26) + 1),
            'birthplace' => $location['province_name'],
            'civil_status' => $i % 3 === 0 ? 'Single' : 'Married',
            'spouse_name' => $i % 3 === 0 ? null : 'Seed Spouse ' . $i,
            'dependents' => $i % 6,
            'contact_number' => sprintf('0917%07d', $i),
            'email' => sprintf('seed.farmer%02d@example.com', $i),
            'sex' => $i % 2 === 0 ? 'Male' : 'Female',
            'farmer_organization_id' => $isFoMember ? $foId : null,
            'warehouse_id' => $location['warehouse_id'],
        ]);

        $selectFarmer = $db->prepare('SELECT id FROM farmers WHERE rsbsa_number = :rsbsa LIMIT 1');
        $selectFarmer->execute(['rsbsa' => $rsbsa]);
        $farmerId = (int) $selectFarmer->fetchColumn();
        $farmerIds[$i] = ['id' => $farmerId, 'warehouse_id' => $location['warehouse_id']];

        $landStmt->execute([
            'farmer_id' => $farmerId,
            'irrigated' => $i % 4 === 0 ? 0 : 1,
            'palay_location' => $location['province_name'],
            'area' => 1.2 + ($i * 0.13),
            'yield' => 3.5 + (($i % 7) * 0.25),
        ]);
    }

    $transactionStmt = $db->prepare("
        INSERT INTO transactions (
            seller_type, procurement_type, farmer_id, farmer_organization_id, warehouse_id,
            representative_name, total_members, verified_farm_area, delivery_date,
            warehouse_stock_receipt_number, price_per_kilogram, net_kilogram, bags_50kg, created_by
        ) VALUES (
            :seller_type, :procurement_type, :farmer_id, :farmer_organization_id, :warehouse_id,
            :representative_name, :total_members, :verified_farm_area, :delivery_date,
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
            created_by = VALUES(created_by)
    ");

    for ($i = 1; $i <= 20; $i++) {
        $month = $i <= 10 ? '2026-01' : '2026-02';
        $netKg = 1200 + ($i * 85);
        $transactionStmt->execute([
            'seller_type' => 'Individual',
            'procurement_type' => $i % 2 === 0 ? 'Mobile Procurement' : 'In-Warehouse',
            'farmer_id' => $farmerIds[$i]['id'],
            'farmer_organization_id' => null,
            'warehouse_id' => $farmerIds[$i]['warehouse_id'],
            'representative_name' => null,
            'total_members' => null,
            'verified_farm_area' => 1.5 + ($i * 0.1),
            'delivery_date' => sprintf('%s-%02d', $month, (($i - 1) % 10) + 5),
            'wsr' => sprintf('SEED-IND-2026-%03d', $i),
            'price' => 23.00 + (($i % 3) * 0.25),
            'net_kg' => $netKg,
            'bags' => (int) round($netKg / 50),
            'created_by' => $createdBy,
        ]);
    }

    $foNetKg = 15250.00;
    $transactionStmt->execute([
        'seller_type' => 'Farmer Organization',
        'procurement_type' => 'In-Warehouse',
        'farmer_id' => null,
        'farmer_organization_id' => $foId,
        'warehouse_id' => $farmerIds[21]['warehouse_id'],
        'representative_name' => 'Seed FO Representative',
        'total_members' => 10,
        'verified_farm_area' => 18.75,
        'delivery_date' => '2026-03-15',
        'wsr' => 'SEED-FO-2026-001',
        'price' => 23.50,
        'net_kg' => $foNetKg,
        'bags' => (int) round($foNetKg / 50),
        'created_by' => $createdBy,
    ]);

    $db->commit();
    echo "Seeded users, 30 farmers, 20 individual deliveries, and 1 organization delivery for 10 member-farmers.\n";
} catch (Throwable $exception) {
    $db->rollBack();
    fwrite(STDERR, $exception->getMessage() . "\n");
    exit(1);
}
