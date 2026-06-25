<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

final class CentralOffice
{
    public static function hierarchy(): array
    {
        return [
            'departments' => self::departments(),
            'divisions' => self::divisions(),
            'units' => self::units(),
        ];
    }

    public static function departments(): array
    {
        self::ensureSchema();
        return Database::connection()->query('SELECT id, name FROM central_departments ORDER BY name')->fetchAll();
    }

    public static function divisions(): array
    {
        self::ensureSchema();
        return Database::connection()->query('SELECT id, department_id, name FROM central_divisions ORDER BY name')->fetchAll();
    }

    public static function units(): array
    {
        self::ensureSchema();
        return Database::connection()->query('SELECT id, division_id, name FROM central_units ORDER BY name')->fetchAll();
    }

    public static function libraryRows(): array
    {
        self::ensureSchema();
        return Database::connection()->query("
            SELECT
                d.id AS department_id,
                d.name AS department_name,
                v.id AS division_id,
                v.name AS division_name,
                u.id AS unit_id,
                u.name AS unit_name
            FROM central_departments d
            LEFT JOIN central_divisions v ON v.department_id = d.id
            LEFT JOIN central_units u ON u.division_id = v.id
            ORDER BY d.name, v.name, u.name
        ")->fetchAll();
    }

    public static function createDepartment(string $name): void
    {
        self::ensureSchema();
        $stmt = Database::connection()->prepare('INSERT IGNORE INTO central_departments (name) VALUES (:name)');
        $stmt->execute(['name' => trim($name)]);
    }

    public static function createDivision(int $departmentId, string $name): void
    {
        self::ensureSchema();
        $stmt = Database::connection()->prepare('INSERT IGNORE INTO central_divisions (department_id, name) VALUES (:department_id, :name)');
        $stmt->execute(['department_id' => $departmentId, 'name' => trim($name)]);
    }

    public static function createUnit(int $divisionId, string $name): void
    {
        self::ensureSchema();
        $stmt = Database::connection()->prepare('INSERT IGNORE INTO central_units (division_id, name) VALUES (:division_id, :name)');
        $stmt->execute(['division_id' => $divisionId, 'name' => trim($name)]);
    }

    public static function updateName(string $type, int $id, string $name): void
    {
        self::ensureSchema();
        $tables = [
            'department' => 'central_departments',
            'division' => 'central_divisions',
            'unit' => 'central_units',
        ];

        if (!isset($tables[$type])) {
            return;
        }

        $stmt = Database::connection()->prepare("UPDATE {$tables[$type]} SET name = :name WHERE id = :id");
        $stmt->execute(['name' => trim($name), 'id' => $id]);
    }

    public static function delete(string $type, int $id): void
    {
        self::ensureSchema();
        if ($id <= 0) {
            throw new \RuntimeException('Invalid central office directory entry selected.');
        }

        match ($type) {
            'department' => self::deleteGuarded('central_departments', $id, 'Department', [
                ['central_divisions', 'department_id', 'division'],
                ['users', 'central_department_id', 'user account'],
            ]),
            'division' => self::deleteGuarded('central_divisions', $id, 'Division', [
                ['central_units', 'division_id', 'service/unit'],
                ['users', 'central_division_id', 'user account'],
            ]),
            'unit' => self::deleteGuarded('central_units', $id, 'Service/Unit', [
                ['users', 'central_unit_id', 'user account'],
            ]),
            default => throw new \RuntimeException('Invalid central office directory type selected.'),
        };
    }

    private static function deleteGuarded(string $table, int $id, string $label, array $checks): void
    {
        $db = Database::connection();
        foreach ($checks as [$referenceTable, $column, $referenceLabel]) {
            $stmt = $db->prepare("SELECT COUNT(*) FROM {$referenceTable} WHERE {$column} = :id");
            $stmt->execute(['id' => $id]);
            if ((int) $stmt->fetchColumn() > 0) {
                throw new \RuntimeException("This {$label} cannot be deleted because it is still used by a {$referenceLabel}.");
            }
        }

        $stmt = $db->prepare("DELETE FROM {$table} WHERE id = :id");
        $stmt->execute(['id' => $id]);
    }

    public static function ensureSchema(): void
    {
        static $ready = false;

        if ($ready) {
            return;
        }

        $db = Database::connection();
        $db->exec("
            CREATE TABLE IF NOT EXISTS central_departments (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(180) NOT NULL UNIQUE
            )
        ");
        $db->exec("
            CREATE TABLE IF NOT EXISTS central_divisions (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                department_id BIGINT UNSIGNED NOT NULL,
                name VARCHAR(180) NOT NULL,
                UNIQUE KEY central_division_unique (department_id, name),
                FOREIGN KEY (department_id) REFERENCES central_departments(id)
            )
        ");
        $db->exec("
            CREATE TABLE IF NOT EXISTS central_units (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                division_id BIGINT UNSIGNED NOT NULL,
                name VARCHAR(180) NOT NULL,
                UNIQUE KEY central_unit_unique (division_id, name),
                FOREIGN KEY (division_id) REFERENCES central_divisions(id)
            )
        ");
        $db->exec("ALTER TABLE users ADD COLUMN IF NOT EXISTS office_scope VARCHAR(30) NOT NULL DEFAULT 'field'");
        $db->exec('ALTER TABLE users ADD COLUMN IF NOT EXISTS central_department_id BIGINT UNSIGNED NULL');
        $db->exec('ALTER TABLE users ADD COLUMN IF NOT EXISTS central_division_id BIGINT UNSIGNED NULL');
        $db->exec('ALTER TABLE users ADD COLUMN IF NOT EXISTS central_unit_id BIGINT UNSIGNED NULL');

        $ready = true;
    }
}
