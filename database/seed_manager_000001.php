<?php
declare(strict_types=1);

require dirname(__DIR__) . '/app/bootstrap.php';

use App\Core\Database;

$db = Database::connection();
$db->beginTransaction();

try {
    $location = $db->query("
        SELECT r.id AS region_id, b.id AS branch_id
        FROM regions r
        LEFT JOIN branch_offices b ON b.region_id = r.id
        WHERE r.name = 'Region I'
        ORDER BY r.name, b.name
        LIMIT 1
    ")->fetch();

    $stmt = $db->prepare("
        INSERT INTO users (
            full_name, username, email, password_hash, role, is_active, status,
            office_scope, region_id, branch_id, province_id, warehouse_id,
            designation, contact_number
        ) VALUES (
            :full_name, :username, :email, :password_hash, 'Manager', 1, 'Active',
            'field', :region_id, :branch_id, NULL, NULL, 'Manager', NULL
        )
        ON DUPLICATE KEY UPDATE
            full_name = VALUES(full_name),
            email = VALUES(email),
            password_hash = VALUES(password_hash),
            role = 'Manager',
            is_active = 1,
            status = 'Active',
            office_scope = 'field',
            region_id = VALUES(region_id),
            branch_id = VALUES(branch_id),
            province_id = NULL,
            warehouse_id = NULL,
            designation = 'Manager'
    ");
    $stmt->execute([
        'full_name' => 'Seed Manager 000001',
        'username' => '000001',
        'email' => '000001@fwsp.local',
        'password_hash' => password_hash('password', PASSWORD_DEFAULT),
        'region_id' => $location['region_id'] ?? null,
        'branch_id' => $location['branch_id'] ?? null,
    ]);

    $db->commit();
    echo "Manager account 000001 seeded.\n";
} catch (Throwable $exception) {
    if ($db->inTransaction()) {
        $db->rollBack();
    }
    throw $exception;
}
