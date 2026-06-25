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
    WHERE u.username = :username
    LIMIT 1"
);
$userStmt->execute(['username' => '000222']);
$user = $userStmt->fetch();

if (!$user || !$user['warehouse_id']) {
    fwrite(STDERR, "User 000222 was not found or has no default warehouse assigned.\n");
    exit(1);
}

$firstNames = [
    'Adela', 'Benito', 'Carina', 'Danilo', 'Elena', 'Felix', 'Gemma', 'Hector', 'Imelda', 'Jonas',
    'Katrina', 'Leonardo', 'Maribel', 'Nestor', 'Ofelia', 'Paolo', 'Querubin', 'Rosalie', 'Samuel', 'Teresita',
    'Ulysses', 'Veronica', 'Wilfredo', 'Xandra', 'Yolanda', 'Zandro', 'Amparo', 'Brando', 'Clarissa', 'Domingo',
];
$middleNames = [
    'Aquino', 'Bautista', 'Castro', 'Diaz', 'Enriquez', 'Flores', 'Gomez', 'Hernandez', 'Ignacio', 'Jimenez',
    'Lazaro', 'Mendoza', 'Navarro', 'Ocampo', 'Pascual', 'Quinto', 'Rivera', 'Salazar', 'Torres', 'Valdez',
    'Villanueva', 'Zamora', 'Abad', 'Bernal', 'Cruz', 'Dizon', 'Escoto', 'Fajardo', 'Galang', 'Hilario',
];
$lastNames = [
    'Dela Cruz', 'Santos', 'Reyes', 'Garcia', 'Ramos', 'Mendoza', 'Torres', 'Flores', 'Rivera', 'Gonzales',
    'Bautista', 'Villanueva', 'Fernandez', 'Castillo', 'Aquino', 'Morales', 'Navarro', 'Domingo', 'Pascual', 'Valdez',
    'Cabrera', 'Salvador', 'Aguilar', 'Marquez', 'Santiago', 'Mercado', 'Rosales', 'Tolentino', 'Soriano', 'Velasco',
];
$civilStatuses = ['Single', 'Married', 'Widowed', 'Separated'];
$sectors = [
    ['Adult'],
    ['Youth'],
    ['Senior Citizen'],
    ['Muslim'],
    ['Persons with Disability'],
    ['Indigenous People'],
    ['Adult', 'Muslim'],
    ['Adult', 'Indigenous People'],
];
$classifications = [
    ['Riceland', 'Owner-Tiller'],
    ['Riceland', 'Tenant'],
    ['Riceland', 'Lessee'],
    ['Riceland', 'CLT Holder/Recipient'],
];

$farmerStmt = $db->prepare(
    "INSERT INTO farmers (
        rsbsa_number, first_name, middle_name, last_name, address, birthdate, birthplace,
        civil_status, spouse_name, dependents, contact_number, email, sex, gender_orientation,
        sector, farmer_organization_id, warehouse_id
    ) VALUES (
        :rsbsa_number, :first_name, :middle_name, :last_name, :address, :birthdate, :birthplace,
        :civil_status, :spouse_name, :dependents, :contact_number, :email, :sex, :gender_orientation,
        :sector, NULL, :warehouse_id
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

$selectFarmerStmt = $db->prepare('SELECT id FROM farmers WHERE rsbsa_number = :rsbsa_number LIMIT 1');
$landholdingStmt = $db->prepare(
    "INSERT INTO landholdings (
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
        average_yield_per_hectare = VALUES(average_yield_per_hectare)"
);

$db->beginTransaction();

try {
    for ($index = 0; $index < 30; $index++) {
        $number = $index + 1;
        $rsbsa = sprintf('WM000222-2026-%03d', $number);
        $sex = $number % 2 === 0 ? 'Male' : 'Female';
        $civilStatus = $civilStatuses[$index % count($civilStatuses)];

        $farmerStmt->execute([
            'rsbsa_number' => $rsbsa,
            'first_name' => $firstNames[$index],
            'middle_name' => $middleNames[$index],
            'last_name' => $lastNames[$index],
            'address' => sprintf('%s Service Area, %s, %s', $user['warehouse_name'], $user['province_name'], $user['region_name']),
            'birthdate' => sprintf('%04d-%02d-%02d', 1965 + ($index % 35), ($index % 12) + 1, ($index % 27) + 1),
            'birthplace' => $user['province_name'] ?: $user['branch_name'],
            'civil_status' => $civilStatus,
            'spouse_name' => $civilStatus === 'Married' ? 'Seeded Spouse ' . $number : null,
            'dependents' => $index % 6,
            'contact_number' => sprintf('0918222%04d', $number),
            'email' => sprintf('wm000222.farmer%03d@fwsp.local', $number),
            'sex' => $sex,
            'gender_orientation' => json_encode(['N/A'], JSON_THROW_ON_ERROR),
            'sector' => json_encode($sectors[$index % count($sectors)], JSON_THROW_ON_ERROR),
            'warehouse_id' => $user['warehouse_id'],
        ]);

        $selectFarmerStmt->execute(['rsbsa_number' => $rsbsa]);
        $farmerId = (int) $selectFarmerStmt->fetchColumn();

        $landholdingStmt->execute([
            'farmer_id' => $farmerId,
            'classification' => json_encode($classifications[$index % count($classifications)], JSON_THROW_ON_ERROR),
            'irrigated' => $index % 3 === 0 ? 0 : 1,
            'harvest_sharing_lessor' => $index % 4 === 1 ? 30.00 : null,
            'harvest_sharing_lessee' => $index % 4 === 1 ? 70.00 : null,
            'palay_location' => $user['province_name'] ?: $user['branch_name'],
            'harvested_area_hectares' => number_format(1.10 + (($index % 10) * 0.35), 2, '.', ''),
            'average_yield_per_hectare' => number_format(3.80 + (($index % 8) * 0.20), 2, '.', ''),
        ]);
    }

    $db->commit();
} catch (Throwable $exception) {
    $db->rollBack();
    fwrite(STDERR, $exception->getMessage() . "\n");
    exit(1);
}

printf(
    "Seeded 30 farmer profiles for user 000222 at %s, %s, %s, %s.\n",
    $user['region_name'] ?? 'No region',
    $user['branch_name'] ?? 'No branch',
    $user['province_name'] ?? 'No province',
    $user['warehouse_name'] ?? 'No warehouse'
);
