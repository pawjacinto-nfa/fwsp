<?php
declare(strict_types=1);

require dirname(__DIR__) . '/app/bootstrap.php';

use App\Core\Database;

$db = Database::connection();

$sourceStmt = $db->prepare("
    SELECT
        u.region_id,
        u.branch_id,
        u.province_id,
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
    WHERE u.username = '000222'
    LIMIT 1
");
$sourceStmt->execute();
$source = $sourceStmt->fetch();

if (!$source || !$source['warehouse_id']) {
    fwrite(STDERR, "User 000222 was not found or has no default facility assigned.\n");
    exit(1);
}

$warehouseStmt = $db->prepare("
    SELECT
        w.id AS warehouse_id,
        w.name AS warehouse_name,
        p.id AS province_id,
        p.name AS province_name,
        b.id AS branch_id,
        b.name AS branch_name,
        r.id AS region_id,
        r.name AS region_name
    FROM warehouse_offices w
    LEFT JOIN province_offices p ON p.id = w.province_id
    LEFT JOIN branch_offices b ON b.id = COALESCE(p.branch_id, w.branch_id)
    LEFT JOIN regions r ON r.id = b.region_id
    WHERE r.id = :region_id
        AND b.id = :branch_id
        AND p.id = :province_id
        AND w.id <> :warehouse_id
    ORDER BY w.name
    LIMIT 1
");
$warehouseStmt->execute([
    'region_id' => $source['region_id'],
    'branch_id' => $source['branch_id'],
    'province_id' => $source['province_id'],
    'warehouse_id' => $source['warehouse_id'],
]);
$target = $warehouseStmt->fetch();

if (!$target) {
    fwrite(STDERR, "No alternate facility was found under user 000222's location.\n");
    exit(1);
}

$firstNames = [
    'Althea', 'Benedicto', 'Carmela', 'Delfin', 'Emelita', 'Florencio', 'Ginalyn', 'Herminio', 'Irene', 'Joselito',
    'Lorna', 'Marvin', 'Nelia', 'Orlando', 'Perla', 'Quirino', 'Rowena', 'Severino', 'Talia', 'Urbano',
    'Virgilia', 'Winston', 'Yasmin', 'Zenaida', 'Arnel', 'Bernadette', 'Cristina', 'Dominador', 'Evangeline', 'Federico',
];
$middleNames = [
    'Abalos', 'Baltazar', 'Cayetano', 'De Vera', 'Escobar', 'Ferrer', 'Guevarra', 'Hizon', 'Ilustre', 'Javier',
    'Luna', 'Manalo', 'Nieto', 'Ortega', 'Pineda', 'Quiambao', 'Robles', 'Serrano', 'Tiongson', 'Umali',
    'Valerio', 'Yabut', 'Zaragoza', 'Alcantara', 'Buenaventura', 'Corpus', 'Dimaculangan', 'Evangelista', 'Francisco', 'Gatchalian',
];
$lastNames = [
    'Agbayani', 'Basa', 'Cabal', 'Domingo', 'Estrella', 'Ferrer', 'Galvez', 'Hidalgo', 'Ibarra', 'Jacinto',
    'Limgenco', 'Magsino', 'Natividad', 'Ordona', 'Padilla', 'Quinto', 'Roldan', 'Soriano', 'Tamayo', 'Urbina',
    'Valdez', 'Yap', 'Zamora', 'Alvarez', 'Bermudez', 'Cordero', 'Dizon', 'Espiritu', 'Fajardo', 'Garcia',
];
$civilStatuses = ['Single', 'Married', 'Widowed', 'Separated'];
$sectors = [
    ['Adult'],
    ['Youth'],
    ['Muslim'],
    ['Persons with Disability'],
    ['Indigenous People'],
    ['Senior Citizen'],
    ['Adult', 'Muslim'],
    ['Adult', 'Indigenous People'],
];
$sogie = [
    ['N/A'],
    ['Lesbian'],
    ['Gay'],
    ['Bisexual'],
    ['Transgender'],
    ['N/A'],
];
$classifications = [
    ['Riceland', 'Owner-Tiller'],
    ['Riceland', 'Tenant'],
    ['Riceland', 'Lessee'],
    ['Riceland', 'Amortizing Owner'],
];
$organizations = [
    [
        'name' => 'Eastern Pangasinan Seed Growers Association',
        'office_location' => $target['province_name'] . ', ' . $target['branch_name'],
    ],
    [
        'name' => 'Asingan Palay Farmers Cooperative',
        'office_location' => $target['warehouse_name'] . ', ' . $target['province_name'],
    ],
    [
        'name' => 'Region I Progressive Rice Farmers Group',
        'office_location' => $target['region_name'] . ' - ' . $target['province_name'],
    ],
];

$db->beginTransaction();

try {
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
    $userStmt->execute([
        'full_name' => 'Seed Warehouse Manager 000333',
        'username' => '000333',
        'email' => '000333@fwsp.local',
        'password_hash' => password_hash('password', PASSWORD_DEFAULT),
        'role' => 'Warehouse Personnel',
        'region_id' => $target['region_id'],
        'branch_id' => $target['branch_id'],
        'province_id' => $target['province_id'],
        'warehouse_id' => $target['warehouse_id'],
        'designation' => 'Warehouse Manager',
        'contact_number' => '09000000333',
    ]);

    $organizationStmt = $db->prepare("
        INSERT INTO farmer_organizations (name, total_members, warehouse_id, office_location)
        VALUES (:name, 0, :warehouse_id, :office_location)
        ON DUPLICATE KEY UPDATE
            warehouse_id = VALUES(warehouse_id),
            office_location = VALUES(office_location)
    ");
    $selectOrganizationStmt = $db->prepare('SELECT id FROM farmer_organizations WHERE name = :name LIMIT 1');
    $organizationIds = [];

    foreach ($organizations as $organization) {
        $organizationStmt->execute([
            'name' => $organization['name'],
            'warehouse_id' => $target['warehouse_id'],
            'office_location' => $organization['office_location'],
        ]);
        $selectOrganizationStmt->execute(['name' => $organization['name']]);
        $organizationIds[] = (int) $selectOrganizationStmt->fetchColumn();
    }

    $farmerStmt = $db->prepare("
        INSERT INTO farmers (
            rsbsa_number, first_name, middle_name, last_name, address, birthdate, birthplace,
            civil_status, spouse_name, dependents, contact_number, email, sex,
            gender_orientation, sector, farmer_organization_id, warehouse_id
        ) VALUES (
            :rsbsa_number, :first_name, :middle_name, :last_name, :address, :birthdate, :birthplace,
            :civil_status, :spouse_name, :dependents, :contact_number, :email, :sex,
            :gender_orientation, :sector, :farmer_organization_id, :warehouse_id
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
    $selectFarmerStmt = $db->prepare('SELECT id FROM farmers WHERE rsbsa_number = :rsbsa_number LIMIT 1');
    $landholdingStmt = $db->prepare("
        INSERT INTO landholdings (
            farmer_id, classification, irrigated, harvest_sharing_lessor, harvest_sharing_lessee,
            palay_location, harvested_area_hectares, average_yield_per_hectare
        ) VALUES (
            :farmer_id, :classification, :irrigated, :harvest_sharing_lessor, :harvest_sharing_lessee,
            :palay_location, :harvested_area_hectares, :average_yield_per_hectare
        )
        ON DUPLICATE KEY UPDATE
            classification = VALUES(classification),
            irrigated = VALUES(irrigated),
            harvest_sharing_lessor = VALUES(harvest_sharing_lessor),
            harvest_sharing_lessee = VALUES(harvest_sharing_lessee),
            palay_location = VALUES(palay_location),
            harvested_area_hectares = VALUES(harvested_area_hectares),
            average_yield_per_hectare = VALUES(average_yield_per_hectare)
    ");

    for ($index = 0; $index < 30; $index++) {
        $number = $index + 1;
        $organizationId = $organizationIds[$index % count($organizationIds)];
        $civilStatus = $civilStatuses[$index % count($civilStatuses)];

        $farmerStmt->execute([
            'rsbsa_number' => sprintf('WM000333-2026-%03d', $number),
            'first_name' => $firstNames[$index],
            'middle_name' => $middleNames[$index],
            'last_name' => $lastNames[$index],
            'address' => sprintf('%s Service Area, %s, %s', $target['warehouse_name'], $target['province_name'], $target['region_name']),
            'birthdate' => sprintf('%04d-%02d-%02d', 1968 + ($index % 34), ($index % 12) + 1, ($index % 27) + 1),
            'birthplace' => $target['province_name'],
            'civil_status' => $civilStatus,
            'spouse_name' => $civilStatus === 'Married' ? 'Seeded Spouse ' . $number : null,
            'dependents' => $index % 7,
            'contact_number' => sprintf('0918333%04d', $number),
            'email' => sprintf('wm000333.farmer%03d@fwsp.local', $number),
            'sex' => $number % 2 === 0 ? 'Male' : 'Female',
            'gender_orientation' => json_encode($sogie[$index % count($sogie)], JSON_THROW_ON_ERROR),
            'sector' => json_encode($sectors[$index % count($sectors)], JSON_THROW_ON_ERROR),
            'farmer_organization_id' => $organizationId,
            'warehouse_id' => $target['warehouse_id'],
        ]);

        $selectFarmerStmt->execute(['rsbsa_number' => sprintf('WM000333-2026-%03d', $number)]);
        $farmerId = (int) $selectFarmerStmt->fetchColumn();

        $landholdingStmt->execute([
            'farmer_id' => $farmerId,
            'classification' => json_encode($classifications[$index % count($classifications)], JSON_THROW_ON_ERROR),
            'irrigated' => $index % 4 === 0 ? 0 : 1,
            'harvest_sharing_lessor' => $index % 5 === 0 ? 25.00 : null,
            'harvest_sharing_lessee' => $index % 5 === 0 ? 75.00 : null,
            'palay_location' => $target['province_name'],
            'harvested_area_hectares' => number_format(1.25 + (($index % 10) * 0.32), 2, '.', ''),
            'average_yield_per_hectare' => number_format(3.65 + (($index % 8) * 0.22), 2, '.', ''),
        ]);
    }

    $memberCountStmt = $db->prepare("
        UPDATE farmer_organizations fo
        SET total_members = (
            SELECT COUNT(*)
            FROM farmers f
            WHERE f.farmer_organization_id = fo.id
        )
        WHERE fo.id = :id
    ");
    foreach ($organizationIds as $organizationId) {
        $memberCountStmt->execute(['id' => $organizationId]);
    }

    $db->commit();
} catch (Throwable $exception) {
    $db->rollBack();
    fwrite(STDERR, $exception->getMessage() . "\n");
    exit(1);
}

printf(
    "Seeded user 000333, 3 farmer organizations, and 30 farmers at %s, %s, %s, %s.\n",
    $target['region_name'],
    $target['branch_name'],
    $target['province_name'],
    $target['warehouse_name']
);
