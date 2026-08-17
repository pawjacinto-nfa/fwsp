<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

/** Delivery appointments are deliberately separate from completed transactions. */
final class DeliverySchedule
{
    public static function forMonth(string $month, ?int $warehouseId): array
    {
        self::ensureSchema();
        $from = $month . '-01';
        $to = date('Y-m-d', strtotime($from . ' +1 month'));
        $sql = "SELECT s.*, CONCAT(COALESCE(f.first_name, ''), ' ', COALESCE(f.last_name, '')) AS enrolled_name,
                       COALESCE(fo.name, '') AS enrolled_organization_name, COALESCE(w.name, '') AS warehouse_name
                FROM delivery_schedules s
                LEFT JOIN farmers f ON f.id = s.farmer_id
                LEFT JOIN farmer_organizations fo ON fo.id = s.farmer_organization_id
                LEFT JOIN warehouse_offices w ON w.id = s.warehouse_id
                WHERE s.schedule_date >= :from_date AND s.schedule_date < :to_date";
        $params = ['from_date' => $from, 'to_date' => $to];
        if ($warehouseId) { $sql .= ' AND s.warehouse_id = :warehouse_id'; $params['warehouse_id'] = $warehouseId; }
        $sql .= ' ORDER BY s.schedule_date, s.id';
        $stmt = Database::connection()->prepare($sql); $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public static function dayStatuses(string $month, ?int $warehouseId): array
    {
        self::ensureSchema(); if (!$warehouseId) return [];
        $stmt = Database::connection()->prepare('SELECT schedule_date, status FROM delivery_schedule_days WHERE warehouse_id=:warehouse_id AND schedule_date >= :from_date AND schedule_date < :to_date');
        $stmt->execute(['warehouse_id' => $warehouseId, 'from_date' => $month . '-01', 'to_date' => date('Y-m-d', strtotime($month . '-01 +1 month'))]);
        return $stmt->fetchAll(\PDO::FETCH_KEY_PAIR);
    }

    public static function allDayStatuses(string $month): array
    {
        self::ensureSchema();
        $stmt = Database::connection()->prepare('SELECT warehouse_id, schedule_date, status FROM delivery_schedule_days WHERE schedule_date >= :from_date AND schedule_date < :to_date');
        $stmt->execute(['from_date' => $month . '-01', 'to_date' => date('Y-m-d', strtotime($month . '-01 +1 month'))]);
        $statuses = [];
        foreach ($stmt->fetchAll() as $row) $statuses[(string) $row['warehouse_id']][$row['schedule_date']] = $row['status'];
        return $statuses;
    }

    public static function create(array $data): int
    {
        self::ensureSchema();
        $date = (string) ($data['schedule_date'] ?? '');
        $valid = \DateTimeImmutable::createFromFormat('!Y-m-d', $date);
        if (!$valid || $valid->format('Y-m-d') !== $date) throw new \DomainException('Choose a valid delivery date.');
        $sellerType = (string) ($data['seller_type'] ?? 'Individual');
        if (!in_array($sellerType, ['Individual', 'Farmer Organization'], true)) throw new \DomainException('Select a valid schedule type.');
        $farmerId = (int) ($data['farmer_id'] ?? 0);
        $temporary = trim((string) ($data['temporary_name'] ?? ''));
        $temporaryContact = trim((string) ($data['temporary_contact_number'] ?? ''));
        $organizationId = (int) ($data['farmer_organization_id'] ?? 0);
        $temporaryOrganization = trim((string) ($data['temporary_organization_name'] ?? ''));
        $representative = trim((string) ($data['representative_name'] ?? ''));
        if ($sellerType === 'Individual' && (($farmerId <= 0 && $temporary === '') || ($farmerId > 0 && $temporary !== ''))) throw new \DomainException('Select an enrolled farmer or enter one temporary full name.');
        if ($sellerType === 'Farmer Organization' && (($organizationId <= 0 && $temporaryOrganization === '') || ($organizationId > 0 && $temporaryOrganization !== ''))) throw new \DomainException('Select an enrolled farmer organization or enter one temporary organization name.');
        if ($sellerType === 'Farmer Organization' && $representative === '') throw new \DomainException('Enter the name of the farmer organization representative.');
        if (mb_strlen($temporaryContact) > 40) throw new \DomainException('Contact number must not exceed 40 characters.');
        if (!is_numeric($data['expected_bags'] ?? null) || (float) $data['expected_bags'] <= 0) throw new \DomainException('Number of bags must be greater than zero.');
        $db = Database::connection();
        if ($sellerType === 'Individual' && $farmerId > 0) {
            $check = $db->prepare('SELECT farmer_key FROM farmers WHERE id = :id LIMIT 1'); $check->execute(['id' => $farmerId]);
            $farmerKey = $check->fetchColumn();
            if (!$farmerKey) throw new \DomainException('The selected farmer record was not found.');
        }
        if ($sellerType === 'Farmer Organization' && $organizationId > 0) {
            $organizationCheck = $db->prepare('SELECT name FROM farmer_organizations WHERE id = :id LIMIT 1');
            $organizationCheck->execute(['id' => $organizationId]);
            if (!$organizationCheck->fetchColumn()) throw new \DomainException('The selected farmer organization was not found.');
        }
        $warehouse = (int) ($data['warehouse_id'] ?? 0);
        if (!$warehouse) throw new \DomainException('Select the delivery region, branch, province, and facility.');
        $slotCheck = $db->prepare("SELECT status FROM delivery_schedule_days WHERE warehouse_id = :warehouse_id AND schedule_date = :schedule_date LIMIT 1");
        $slotCheck->execute(['warehouse_id' => $warehouse, 'schedule_date' => $date]);
        if ($slotCheck->fetchColumn() === 'Full') throw new \DomainException('This day has no available delivery slots.');

        $db->beginTransaction();
        try {
            $reference = self::nextReferenceNumber($warehouse, $date);
            $stmt = $db->prepare('INSERT INTO delivery_schedules (seller_type, farmer_id, temporary_name, temporary_contact_number, farmer_organization_id, temporary_organization_name, representative_name, schedule_date, expected_bags, confirmation_code, warehouse_id, created_by) VALUES (:seller_type, :farmer_id, :temporary_name, :temporary_contact_number, :farmer_organization_id, :temporary_organization_name, :representative_name, :schedule_date, :bags, :confirmation_code, :warehouse_id, :created_by)');
            $stmt->execute([
                'seller_type' => $sellerType,
                'farmer_id' => $sellerType === 'Individual' ? ($farmerId ?: null) : null,
                'temporary_name' => $sellerType === 'Individual' ? ($temporary ?: null) : null,
                'temporary_contact_number' => $sellerType === 'Individual' && $farmerId <= 0 ? ($temporaryContact ?: null) : null,
                'farmer_organization_id' => $sellerType === 'Farmer Organization' ? ($organizationId ?: null) : null,
                'temporary_organization_name' => $sellerType === 'Farmer Organization' ? ($temporaryOrganization ?: null) : null,
                'representative_name' => $sellerType === 'Farmer Organization' ? $representative : null,
                'schedule_date' => $date, 'bags' => $data['expected_bags'], 'confirmation_code' => $reference,
                'warehouse_id' => $warehouse, 'created_by' => $_SESSION['user_id'] ?? null,
            ]);
            $id = (int) $db->lastInsertId();
            $db->commit();
            return $id;
        } catch (\Throwable $exception) {
            if ($db->inTransaction()) $db->rollBack();
            throw $exception;
        }
    }

    public static function setDayStatus(string $date, int $warehouse, string $status): void
    {
        self::ensureSchema();
        if (!in_array($status, ['Vacant', 'Full'], true)) return;
        Database::connection()->prepare('INSERT INTO delivery_schedule_days (warehouse_id, schedule_date, status) VALUES (:warehouse_id,:schedule_date,:status) ON DUPLICATE KEY UPDATE status=VALUES(status)')->execute(['warehouse_id'=>$warehouse,'schedule_date'=>$date,'status'=>$status]);
    }

    public static function updateStatus(int $id, string $status): ?array
    {
        self::ensureSchema();
        if (!in_array($status, ['Completed', 'Rescheduled', 'No-show'], true)) {
            throw new \DomainException('Select a valid schedule action.');
        }

        $stmt = Database::connection()->prepare('UPDATE delivery_schedules SET status = :status, status_changed_at = CURRENT_TIMESTAMP WHERE id = :id');
        $stmt->execute(['status' => $status, 'id' => $id]);
        if ($stmt->rowCount() === 0 && !self::find($id)) {
            return null;
        }

        return self::find($id);
    }

    public static function find(int $id): ?array
    {
        self::ensureSchema();
        $stmt = Database::connection()->prepare("SELECT s.*, CONCAT(COALESCE(f.first_name, ''), ' ', COALESCE(f.last_name, '')) AS enrolled_name,
            f.farmer_key, f.rsbsa_number AS farmer_rsbsa, f.mao_certification AS farmer_mao_certification,
            f.address AS farmer_address, f.contact_number AS farmer_contact,
            COALESCE(fo.name, '') AS enrolled_organization_name, fo.office_location AS organization_address,
            creator.full_name AS created_by_name, creator.designation AS created_by_designation,
            w.name AS warehouse_name, r.id AS region_id, r.name AS region_name,
            b.id AS branch_id, b.name AS branch_name, p.id AS province_id, p.name AS province_name
            FROM delivery_schedules s
            LEFT JOIN farmers f ON f.id=s.farmer_id
            LEFT JOIN farmer_organizations fo ON fo.id=s.farmer_organization_id
            LEFT JOIN users creator ON creator.id=s.created_by
            LEFT JOIN warehouse_offices w ON w.id=s.warehouse_id
            LEFT JOIN province_offices p ON p.id=w.province_id
            LEFT JOIN branch_offices b ON b.id=COALESCE(p.branch_id, w.branch_id)
            LEFT JOIN regions r ON r.id=b.region_id
            WHERE s.id=:id LIMIT 1");
        $stmt->execute(['id' => $id]); return $stmt->fetch() ?: null;
    }

    private static function nextReferenceNumber(int $warehouseId, string $date): string
    {
        $codes = Location::referenceCodesForWarehouse($warehouseId);
        if (!$codes) throw new \DomainException('The selected facility has no valid region or branch reference code.');

        $month = str_replace('-', '', substr($date, 0, 7));
        $db = Database::connection();
        $db->prepare('INSERT IGNORE INTO delivery_schedule_sequences (branch_id, sequence_month, last_number) VALUES (:branch_id, :sequence_month, 0)')
            ->execute(['branch_id' => $codes['branch_id'], 'sequence_month' => $month]);
        $select = $db->prepare('SELECT last_number FROM delivery_schedule_sequences WHERE branch_id = :branch_id AND sequence_month = :sequence_month FOR UPDATE');
        $select->execute(['branch_id' => $codes['branch_id'], 'sequence_month' => $month]);
        $next = (int) $select->fetchColumn() + 1;
        if ($next > 999) throw new \DomainException('This branch has reached the 999 schedule-reference limit for the month.');
        $db->prepare('UPDATE delivery_schedule_sequences SET last_number = :last_number WHERE branch_id = :branch_id AND sequence_month = :sequence_month')
            ->execute(['last_number' => $next, 'branch_id' => $codes['branch_id'], 'sequence_month' => $month]);

        $season = self::croppingSeasonForDate($date);
        return $codes['region_code'] . '-' . $codes['branch_code'] . '-' . substr($date, 0, 4) . $season . '-' . str_pad((string) $next, 3, '0', STR_PAD_LEFT);
    }

    private static function croppingSeasonForDate(string $date): string
    {
        return match ((int) substr($date, 5, 2)) {
            4, 5, 6, 7 => 'SC',
            8 => 'TC',
            9, 10, 11, 12 => 'MC',
            default => 'EC',
        };
    }

    private static function ensureSchema(): void
    {
        static $ready = false; if ($ready) return;
        Location::ensureReferenceCodeSchema();
        $db = Database::connection();
        $db->exec("CREATE TABLE IF NOT EXISTS delivery_schedules (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, seller_type ENUM('Individual','Farmer Organization') NOT NULL DEFAULT 'Individual',
            farmer_id BIGINT UNSIGNED NULL, temporary_name VARCHAR(180) NULL, temporary_contact_number VARCHAR(40) NULL,
            farmer_organization_id BIGINT UNSIGNED NULL,
            temporary_organization_name VARCHAR(180) NULL, representative_name VARCHAR(180) NULL,
            schedule_date DATE NOT NULL, expected_bags DECIMAL(12,3) NOT NULL, confirmation_code VARCHAR(128) NOT NULL,
            status ENUM('Scheduled','Completed','Rescheduled','No-show') NOT NULL DEFAULT 'Scheduled', status_changed_at TIMESTAMP NULL,
            warehouse_id BIGINT UNSIGNED NOT NULL, created_by BIGINT UNSIGNED NULL, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX delivery_schedules_date_warehouse (schedule_date, warehouse_id),
            CONSTRAINT delivery_schedules_farmer_fk FOREIGN KEY (farmer_id) REFERENCES farmers(id) ON DELETE SET NULL,
            CONSTRAINT delivery_schedules_organization_fk FOREIGN KEY (farmer_organization_id) REFERENCES farmer_organizations(id) ON DELETE SET NULL,
            CONSTRAINT delivery_schedules_warehouse_fk FOREIGN KEY (warehouse_id) REFERENCES warehouse_offices(id),
            CONSTRAINT delivery_schedules_creator_fk FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        $hasExpectedBags = (bool) $db->query("SHOW COLUMNS FROM delivery_schedules LIKE 'expected_bags'")->fetch();
        $hasExpectedVolume = (bool) $db->query("SHOW COLUMNS FROM delivery_schedules LIKE 'expected_volume_kg'")->fetch();
        if (!$hasExpectedBags && $hasExpectedVolume) {
            $db->exec('ALTER TABLE delivery_schedules CHANGE COLUMN expected_volume_kg expected_bags DECIMAL(12,3) NOT NULL');
            $db->exec('UPDATE delivery_schedules SET expected_bags = expected_bags / 50');
        }
        elseif (!$hasExpectedBags) $db->exec('ALTER TABLE delivery_schedules ADD COLUMN expected_bags DECIMAL(12,3) NOT NULL DEFAULT 0 AFTER schedule_date');
        $db->exec("ALTER TABLE delivery_schedules ADD COLUMN IF NOT EXISTS status ENUM('Scheduled','Completed','Rescheduled','No-show') NOT NULL DEFAULT 'Scheduled' AFTER confirmation_code");
        $db->exec('ALTER TABLE delivery_schedules ADD COLUMN IF NOT EXISTS status_changed_at TIMESTAMP NULL AFTER status');
        $db->exec("ALTER TABLE delivery_schedules ADD COLUMN IF NOT EXISTS seller_type ENUM('Individual','Farmer Organization') NOT NULL DEFAULT 'Individual' AFTER id");
        $db->exec('ALTER TABLE delivery_schedules ADD COLUMN IF NOT EXISTS temporary_contact_number VARCHAR(40) NULL AFTER temporary_name');
        $db->exec('ALTER TABLE delivery_schedules ADD COLUMN IF NOT EXISTS farmer_organization_id BIGINT UNSIGNED NULL AFTER temporary_name');
        $db->exec('ALTER TABLE delivery_schedules ADD COLUMN IF NOT EXISTS temporary_organization_name VARCHAR(180) NULL AFTER farmer_organization_id');
        $db->exec('ALTER TABLE delivery_schedules ADD COLUMN IF NOT EXISTS representative_name VARCHAR(180) NULL AFTER temporary_organization_name');
        try { $db->exec('ALTER TABLE delivery_schedules ADD CONSTRAINT delivery_schedules_organization_fk FOREIGN KEY (farmer_organization_id) REFERENCES farmer_organizations(id) ON DELETE SET NULL'); } catch (\Throwable) { }
        try { $db->exec('ALTER TABLE delivery_schedules DROP INDEX confirmation_code'); } catch (\Throwable) { }
        try { $db->exec('CREATE INDEX delivery_schedules_reference_idx ON delivery_schedules (confirmation_code)'); } catch (\Throwable) { }
        $db->exec("CREATE TABLE IF NOT EXISTS delivery_schedule_days (
            warehouse_id BIGINT UNSIGNED NOT NULL, schedule_date DATE NOT NULL, status ENUM('Vacant','Full') NOT NULL DEFAULT 'Vacant',
            PRIMARY KEY (warehouse_id, schedule_date), FOREIGN KEY (warehouse_id) REFERENCES warehouse_offices(id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        $db->exec("CREATE TABLE IF NOT EXISTS delivery_schedule_sequences (
            branch_id BIGINT UNSIGNED NOT NULL,
            sequence_month CHAR(6) NOT NULL,
            last_number SMALLINT UNSIGNED NOT NULL DEFAULT 0,
            PRIMARY KEY (branch_id, sequence_month),
            CONSTRAINT delivery_schedule_sequences_branch_fk FOREIGN KEY (branch_id) REFERENCES branch_offices(id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        self::migrateLegacyReferenceNumbers();
        $ready = true;
    }

    private static function migrateLegacyReferenceNumbers(): void
    {
        $db = Database::connection();
        $rows = $db->query("
            SELECT s.id, s.schedule_date, s.confirmation_code, b.id AS branch_id,
                   b.reference_code AS branch_code, r.reference_code AS region_code
            FROM delivery_schedules s
            INNER JOIN warehouse_offices w ON w.id = s.warehouse_id
            LEFT JOIN province_offices p ON p.id = w.province_id
            INNER JOIN branch_offices b ON b.id = COALESCE(p.branch_id, w.branch_id)
            INNER JOIN regions r ON r.id = b.region_id
            ORDER BY s.schedule_date, s.id
        ")->fetchAll();
        if ($rows === []) return;

        $groups = [];
        foreach ($rows as $row) {
            $month = str_replace('-', '', substr((string) $row['schedule_date'], 0, 7));
            $key = $row['branch_id'] . ':' . $month;
            $groups[$key] ??= ['branch_id' => (int) $row['branch_id'], 'month' => $month, 'last' => 0, 'rows' => []];
            if (preg_match('/^[A-Z0-9]+-[A-Z0-9]+-\d{4}[A-Z]{2}-(\d{3})$/', (string) $row['confirmation_code'], $match)) {
                $groups[$key]['last'] = max($groups[$key]['last'], (int) $match[1]);
            }
            $groups[$key]['rows'][] = $row;
        }

        $upsertSequence = $db->prepare('INSERT INTO delivery_schedule_sequences (branch_id, sequence_month, last_number) VALUES (:branch_id, :sequence_month, :last_number) ON DUPLICATE KEY UPDATE last_number = GREATEST(last_number, VALUES(last_number))');
        $readSequence = $db->prepare('SELECT last_number FROM delivery_schedule_sequences WHERE branch_id = :branch_id AND sequence_month = :sequence_month');
        $updateReference = $db->prepare('UPDATE delivery_schedules SET confirmation_code = :reference WHERE id = :id');
        foreach ($groups as $group) {
            $upsertSequence->execute(['branch_id' => $group['branch_id'], 'sequence_month' => $group['month'], 'last_number' => $group['last']]);
            $readSequence->execute(['branch_id' => $group['branch_id'], 'sequence_month' => $group['month']]);
            $last = (int) $readSequence->fetchColumn();
            foreach ($group['rows'] as $row) {
                $sequence = null;
                if (preg_match('/^[A-Z0-9]+-[A-Z0-9]+-\d{4}[A-Z]{2}-(\d{3})$/', (string) $row['confirmation_code'], $match)) {
                    $sequence = (int) $match[1];
                } else {
                    $last++;
                    if ($last > 999) throw new \RuntimeException('A monthly schedule-reference sequence exceeded 999 during migration.');
                    $sequence = $last;
                }
                $season = self::croppingSeasonForDate((string) $row['schedule_date']);
                $reference = $row['region_code'] . '-' . $row['branch_code'] . '-' . substr((string) $row['schedule_date'], 0, 4) . $season . '-' . str_pad((string) $sequence, 3, '0', STR_PAD_LEFT);
                if ($reference !== $row['confirmation_code']) $updateReference->execute(['reference' => $reference, 'id' => $row['id']]);
            }
            $upsertSequence->execute(['branch_id' => $group['branch_id'], 'sequence_month' => $group['month'], 'last_number' => $last]);
        }
    }
}
