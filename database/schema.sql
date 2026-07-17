CREATE DATABASE IF NOT EXISTS fwsp CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE fwsp;

CREATE TABLE IF NOT EXISTS users (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(160) NOT NULL,
    username VARCHAR(80) NOT NULL UNIQUE,
    email VARCHAR(160) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role VARCHAR(60) NOT NULL DEFAULT 'Read-Only User',
    is_active BOOLEAN NOT NULL DEFAULT FALSE,
    status VARCHAR(30) NOT NULL DEFAULT 'Pending',
    office_scope VARCHAR(30) NOT NULL DEFAULT 'field',
    region_id BIGINT UNSIGNED,
    branch_id BIGINT UNSIGNED,
    province_id BIGINT UNSIGNED,
    warehouse_id BIGINT UNSIGNED,
    central_department_id BIGINT UNSIGNED,
    central_division_id BIGINT UNSIGNED,
    central_unit_id BIGINT UNSIGNED,
    designation VARCHAR(120),
    contact_number VARCHAR(40),
    profile_image VARCHAR(255),
    password_reset_status VARCHAR(30),
    password_reset_requested_at TIMESTAMP NULL,
    password_reset_approved_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS report_signatories (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    full_name VARCHAR(160) NOT NULL,
    designation VARCHAR(160) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX report_signatories_user_idx (user_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS regions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NOT NULL UNIQUE
);

CREATE TABLE IF NOT EXISTS location_masterlist (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    region VARCHAR(120) NOT NULL,
    branch VARCHAR(160) NOT NULL,
    province VARCHAR(160) NOT NULL,
    facility_name VARCHAR(180) NOT NULL,
    UNIQUE KEY location_master_unique (region, branch, province, facility_name)
);

CREATE TABLE IF NOT EXISTS branch_offices (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    region_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(160) NOT NULL,
    UNIQUE KEY branch_region_unique (region_id, name),
    FOREIGN KEY (region_id) REFERENCES regions(id)
);

CREATE TABLE IF NOT EXISTS province_offices (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    branch_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(160) NOT NULL,
    UNIQUE KEY province_branch_unique (branch_id, name),
    FOREIGN KEY (branch_id) REFERENCES branch_offices(id)
);

CREATE TABLE IF NOT EXISTS warehouse_offices (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    branch_id BIGINT UNSIGNED NOT NULL,
    province_id BIGINT UNSIGNED,
    name VARCHAR(160) NOT NULL,
    KEY warehouse_branch_idx (branch_id),
    UNIQUE KEY warehouse_province_unique (province_id, name),
    FOREIGN KEY (branch_id) REFERENCES branch_offices(id),
    FOREIGN KEY (province_id) REFERENCES province_offices(id)
);

CREATE TABLE IF NOT EXISTS central_departments (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(180) NOT NULL UNIQUE
);

CREATE TABLE IF NOT EXISTS central_divisions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    department_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(180) NOT NULL,
    UNIQUE KEY central_division_unique (department_id, name),
    FOREIGN KEY (department_id) REFERENCES central_departments(id)
);

CREATE TABLE IF NOT EXISTS central_units (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    division_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(180) NOT NULL,
    UNIQUE KEY central_unit_unique (division_id, name),
    FOREIGN KEY (division_id) REFERENCES central_divisions(id)
);

CREATE TABLE IF NOT EXISTS farmer_organizations (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(180) NOT NULL UNIQUE,
    is_indigenous_sector_group BOOLEAN NOT NULL DEFAULT FALSE,
    classification_type VARCHAR(40) NOT NULL DEFAULT 'Farmer Organization',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS farmer_key_sequences (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS farmers (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    farmer_key VARCHAR(32) UNIQUE,
    rsbsa_number VARCHAR(60) NOT NULL UNIQUE,
    first_name VARCHAR(100) NOT NULL,
    middle_name VARCHAR(100),
    last_name VARCHAR(100) NOT NULL,
    address TEXT NOT NULL,
    birthdate DATE,
    birthplace VARCHAR(160),
    civil_status VARCHAR(40),
    spouse_name VARCHAR(160),
    dependents INT UNSIGNED DEFAULT 0,
    contact_number VARCHAR(40),
    email VARCHAR(160),
    sex ENUM('Female', 'Male') NOT NULL,
    photo_path VARCHAR(255),
    gender_orientation JSON,
    sector JSON,
    is_ip_group_member BOOLEAN NOT NULL DEFAULT FALSE,
    farmer_organization_id BIGINT UNSIGNED,
    warehouse_id BIGINT UNSIGNED,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (farmer_organization_id) REFERENCES farmer_organizations(id),
    FOREIGN KEY (warehouse_id) REFERENCES warehouse_offices(id)
);

CREATE TABLE IF NOT EXISTS landholdings (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    farmer_id BIGINT UNSIGNED NOT NULL,
    classification JSON,
    irrigated BOOLEAN,
    harvest_sharing_lessor DECIMAL(5,2),
    harvest_sharing_lessee DECIMAL(5,2),
    palay_location VARCHAR(180),
    harvested_area_hectares DECIMAL(10,2),
    average_yield_per_hectare DECIMAL(10,2),
    UNIQUE KEY farmer_landholding_unique (farmer_id),
    FOREIGN KEY (farmer_id) REFERENCES farmers(id)
);

CREATE TABLE IF NOT EXISTS transactions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    seller_type ENUM('Individual', 'Farmer Organization') NOT NULL,
    procurement_type ENUM('In-Warehouse', 'Mobile Procurement') NOT NULL,
    farmer_id BIGINT UNSIGNED,
    farmer_organization_id BIGINT UNSIGNED,
    representative_name VARCHAR(180),
    total_members INT UNSIGNED,
    is_ip_group_delivery BOOLEAN NOT NULL DEFAULT FALSE,
    verified_farm_area DECIMAL(10,2),
    delivery_date DATE NOT NULL,
    warehouse_stock_receipt_number VARCHAR(80) NOT NULL UNIQUE,
    price_per_kilogram DECIMAL(10,2) NOT NULL,
    net_kilogram DECIMAL(12,2) NOT NULL,
    total_cost DECIMAL(20,2) GENERATED ALWAYS AS (ROUND(price_per_kilogram * net_kilogram, 2)) STORED,
    bags_50kg INT UNSIGNED NOT NULL,
    warehouse_id BIGINT UNSIGNED,
      created_by BIGINT UNSIGNED,
      client_control_number VARCHAR(96) NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (farmer_id) REFERENCES farmers(id),
    FOREIGN KEY (farmer_organization_id) REFERENCES farmer_organizations(id),
    FOREIGN KEY (created_by) REFERENCES users(id),
    FOREIGN KEY (warehouse_id) REFERENCES warehouse_offices(id)
);

ALTER TABLE users MODIFY role VARCHAR(60) NOT NULL DEFAULT 'Read-Only User';
ALTER TABLE users ADD COLUMN IF NOT EXISTS is_active BOOLEAN NOT NULL DEFAULT FALSE;
ALTER TABLE users ADD COLUMN IF NOT EXISTS status VARCHAR(30) NOT NULL DEFAULT 'Pending';
ALTER TABLE users ADD COLUMN IF NOT EXISTS office_scope VARCHAR(30) NOT NULL DEFAULT 'field';
ALTER TABLE users ADD COLUMN IF NOT EXISTS region_id BIGINT UNSIGNED NULL;
ALTER TABLE users ADD COLUMN IF NOT EXISTS branch_id BIGINT UNSIGNED NULL;
ALTER TABLE users ADD COLUMN IF NOT EXISTS province_id BIGINT UNSIGNED NULL;
ALTER TABLE users ADD COLUMN IF NOT EXISTS warehouse_id BIGINT UNSIGNED NULL;
ALTER TABLE users ADD COLUMN IF NOT EXISTS central_department_id BIGINT UNSIGNED NULL;
ALTER TABLE users ADD COLUMN IF NOT EXISTS central_division_id BIGINT UNSIGNED NULL;
  ALTER TABLE users ADD COLUMN IF NOT EXISTS central_unit_id BIGINT UNSIGNED NULL;
  ALTER TABLE users ADD COLUMN IF NOT EXISTS offline_enabled TINYINT(1) NOT NULL DEFAULT 0;
ALTER TABLE users ADD COLUMN IF NOT EXISTS designation VARCHAR(120) NULL;
ALTER TABLE users ADD COLUMN IF NOT EXISTS contact_number VARCHAR(40) NULL;
ALTER TABLE users ADD COLUMN IF NOT EXISTS profile_image VARCHAR(255) NULL;
ALTER TABLE users ADD COLUMN IF NOT EXISTS password_reset_status VARCHAR(30) NULL;
ALTER TABLE users ADD COLUMN IF NOT EXISTS password_reset_requested_at TIMESTAMP NULL;
ALTER TABLE users ADD COLUMN IF NOT EXISTS password_reset_approved_at TIMESTAMP NULL;
ALTER TABLE farmers ADD COLUMN IF NOT EXISTS warehouse_id BIGINT UNSIGNED NULL;
ALTER TABLE farmers ADD COLUMN IF NOT EXISTS photo_path VARCHAR(255) NULL;
ALTER TABLE farmers ADD COLUMN IF NOT EXISTS farmer_key VARCHAR(32) NULL AFTER id;
ALTER TABLE farmers ADD COLUMN IF NOT EXISTS is_ip_group_member TINYINT(1) NOT NULL DEFAULT 0;
ALTER TABLE farmer_organizations ADD COLUMN IF NOT EXISTS is_indigenous_sector_group TINYINT(1) NOT NULL DEFAULT 0;
ALTER TABLE farmer_organizations ADD COLUMN IF NOT EXISTS classification_type VARCHAR(40) NOT NULL DEFAULT 'Farmer Organization';
UPDATE farmer_organizations
SET classification_type = CASE
    WHEN is_indigenous_sector_group = 1 THEN 'Indigenous People Group'
    ELSE 'Farmer Organization'
END;
ALTER TABLE transactions ADD COLUMN IF NOT EXISTS warehouse_id BIGINT UNSIGNED NULL;
ALTER TABLE transactions ADD COLUMN IF NOT EXISTS is_ip_group_delivery TINYINT(1) NOT NULL DEFAULT 0;
ALTER TABLE warehouse_offices ADD COLUMN IF NOT EXISTS province_id BIGINT UNSIGNED NULL;

CREATE TABLE IF NOT EXISTS province_offices (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    branch_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(160) NOT NULL,
    UNIQUE KEY province_branch_unique (branch_id, name),
    FOREIGN KEY (branch_id) REFERENCES branch_offices(id)
);

CREATE TABLE IF NOT EXISTS notifications (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED,
    message VARCHAR(255) NOT NULL,
    target_url VARCHAR(255),
    is_read BOOLEAN NOT NULL DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

CREATE TABLE IF NOT EXISTS support_tickets (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    reporter_id BIGINT UNSIGNED NOT NULL,
    title VARCHAR(180) NOT NULL,
    category VARCHAR(80) NOT NULL,
    description TEXT NOT NULL,
    screenshot_path VARCHAR(255),
    status VARCHAR(30) NOT NULL DEFAULT 'Open',
    reporter_archived BOOLEAN NOT NULL DEFAULT FALSE,
    admin_archived BOOLEAN NOT NULL DEFAULT FALSE,
    resolved_by BIGINT UNSIGNED NULL,
    resolved_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX support_tickets_reporter_idx (reporter_id),
    INDEX support_tickets_status_idx (status),
    FOREIGN KEY (reporter_id) REFERENCES users(id),
    FOREIGN KEY (resolved_by) REFERENCES users(id)
);

CREATE TABLE IF NOT EXISTS support_ticket_messages (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    ticket_id BIGINT UNSIGNED NOT NULL,
    sender_id BIGINT UNSIGNED NOT NULL,
    message TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX support_ticket_messages_ticket_idx (ticket_id),
    FOREIGN KEY (ticket_id) REFERENCES support_tickets(id) ON DELETE CASCADE,
    FOREIGN KEY (sender_id) REFERENCES users(id)
);

CREATE TABLE IF NOT EXISTS audit_logs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED,
    action VARCHAR(120) NOT NULL,
    details JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

INSERT IGNORE INTO users (full_name, username, email, password_hash, role, is_active, status, designation, contact_number) VALUES
('System Admin', '940640', 'superadmin@fwsp.local', '$2y$10$GN7cBbOJqlqWKG4WTlq9WeDddCeEISNlbqSS3enkM2UeyQxVXti9e', 'System Admin', 1, 'Active', 'System Administrator', 'n/a'),
('Maria Warehouse', 'warehouse', 'warehouse@fwsp.local', '$2y$10$eImiTXuWVxfM37uY4JANjQeD8ZtcVgHPwrFA4ocK9n53KRzLtPz4S', 'Warehouse Personnel', 1, 'Active', 'Warehouse Personnel', '09170000000');

INSERT IGNORE INTO regions (id, name) VALUES
(1, 'Region 1'), (2, 'Region 2'), (3, 'Region 3'), (4, 'Region 4'), (5, 'Region 5'),
(6, 'Region 6'), (7, 'Region 7'), (8, 'Region 8'), (9, 'Region 9'), (10, 'Region 10'),
(11, 'Region 11'), (12, 'Region 12'), (13, 'Region 13'), (14, 'Region 14'), (15, 'Region 15');

INSERT IGNORE INTO branch_offices (region_id, name)
SELECT id, 'Nueva Ecija Branch' FROM regions WHERE name = 'Region 3';

INSERT IGNORE INTO province_offices (branch_id, name)
SELECT id, 'Nueva Ecija' FROM branch_offices WHERE name = 'Nueva Ecija Branch';

INSERT IGNORE INTO warehouse_offices (branch_id, name)
SELECT id, 'San Jose Warehouse' FROM branch_offices WHERE name = 'Nueva Ecija Branch';

UPDATE warehouse_offices w
JOIN branch_offices b ON b.id = w.branch_id
JOIN province_offices p ON p.branch_id = b.id AND p.name = 'Nueva Ecija'
SET w.province_id = p.id
WHERE w.name = 'San Jose Warehouse' AND w.province_id IS NULL;

UPDATE farmers SET warehouse_id = (SELECT w.id FROM warehouse_offices w JOIN branch_offices b ON b.id = w.branch_id JOIN regions r ON r.id = b.region_id WHERE r.name = 'Region 3' AND w.name = 'San Jose Warehouse' LIMIT 1)
WHERE warehouse_id IN (SELECT w.id FROM warehouse_offices w JOIN branch_offices b ON b.id = w.branch_id JOIN regions r ON r.id = b.region_id WHERE r.name = 'Region III');
UPDATE transactions SET warehouse_id = (SELECT w.id FROM warehouse_offices w JOIN branch_offices b ON b.id = w.branch_id JOIN regions r ON r.id = b.region_id WHERE r.name = 'Region 3' AND w.name = 'San Jose Warehouse' LIMIT 1)
WHERE warehouse_id IN (SELECT w.id FROM warehouse_offices w JOIN branch_offices b ON b.id = w.branch_id JOIN regions r ON r.id = b.region_id WHERE r.name = 'Region III');
DELETE w FROM warehouse_offices w JOIN branch_offices b ON b.id = w.branch_id JOIN regions r ON r.id = b.region_id WHERE r.name = 'Region III';
DELETE b FROM branch_offices b JOIN regions r ON r.id = b.region_id WHERE r.name = 'Region III';
UPDATE regions SET name = 'Region 1' WHERE name = 'Region III';

INSERT IGNORE INTO farmer_organizations (name) VALUES
('Nueva Harvest FO'),
('Munoz Rice Growers Association');

INSERT IGNORE INTO farmers (
    rsbsa_number, first_name, middle_name, last_name, address, birthdate, birthplace,
    civil_status, spouse_name, dependents, contact_number, email, sex,
    gender_orientation, sector, farmer_organization_id
) VALUES
(
    '03-24-001-000001', 'Maria', 'Santos', 'Dela Cruz', 'San Jose, Nueva Ecija',
    '1984-04-12', 'Nueva Ecija', 'Married', 'Ramon Dela Cruz', 4, '09171234567',
    'maria@example.com', 'Female', JSON_ARRAY(), JSON_ARRAY('Adult'),
    (SELECT id FROM farmer_organizations WHERE name = 'Nueva Harvest FO')
),
(
    '03-24-001-000002', 'Jose', 'Reyes', 'Garcia', 'Munoz, Nueva Ecija',
    '1976-09-03', 'Nueva Ecija', 'Single', NULL, 2, '09179876543',
    'jose@example.com', 'Male', JSON_ARRAY(), JSON_ARRAY('Adult'),
    (SELECT id FROM farmer_organizations WHERE name = 'Munoz Rice Growers Association')
);

UPDATE users SET role = 'Warehouse Personnel', is_active = 1, status = 'Active' WHERE username = 'warehouse';
UPDATE users SET role = 'System Admin', is_active = 1, status = 'Active' WHERE username = '940640';

UPDATE users SET role = 'System Admin' WHERE role = 'Super Admin';
UPDATE users SET role = 'Warehouse Personnel' WHERE role = 'Warehouse Supervisor';
UPDATE users SET role = 'Manager' WHERE role = 'Regional/Branch Manager';
UPDATE users SET role = 'Read-Only User' WHERE role = 'Viewer';
UPDATE farmers SET warehouse_id = (SELECT id FROM warehouse_offices WHERE name = 'San Jose Warehouse' LIMIT 1) WHERE warehouse_id IS NULL;
UPDATE transactions SET warehouse_id = (SELECT id FROM warehouse_offices WHERE name = 'San Jose Warehouse' LIMIT 1) WHERE warehouse_id IS NULL;

INSERT IGNORE INTO landholdings (
    farmer_id, classification, irrigated, palay_location, harvested_area_hectares, average_yield_per_hectare
)
SELECT id, JSON_ARRAY('Riceland', 'Owner-Tiller'), 1, 'San Jose', 2.40, 4.80
FROM farmers
WHERE rsbsa_number = '03-24-001-000001';

INSERT IGNORE INTO landholdings (
    farmer_id, classification, irrigated, palay_location, harvested_area_hectares, average_yield_per_hectare
)
SELECT id, JSON_ARRAY('Riceland', 'CLT Holder/Recipient'), 0, 'Munoz', 1.70, 4.20
FROM farmers
WHERE rsbsa_number = '03-24-001-000002';

INSERT IGNORE INTO transactions (
    seller_type, procurement_type, farmer_id, farmer_organization_id, representative_name,
    total_members, verified_farm_area, delivery_date, warehouse_stock_receipt_number,
    price_per_kilogram, net_kilogram, bags_50kg, created_by
) VALUES
(
    'Individual', 'In-Warehouse',
    (SELECT id FROM farmers WHERE rsbsa_number = '03-24-001-000001'),
    (SELECT id FROM farmer_organizations WHERE name = 'Nueva Harvest FO'),
    NULL, NULL, 2.40, CURDATE(), 'WSR-2026-0001', 23.00, 2400.00, 48,
    (SELECT id FROM users WHERE username = 'warehouse')
),
(
    'Individual', 'Mobile Procurement',
    (SELECT id FROM farmers WHERE rsbsa_number = '03-24-001-000002'),
    (SELECT id FROM farmer_organizations WHERE name = 'Munoz Rice Growers Association'),
    NULL, NULL, 1.70, CURDATE(), 'WSR-2026-0002', 23.00, 1700.00, 34,
    (SELECT id FROM users WHERE username = 'warehouse')
);

INSERT IGNORE INTO notifications (user_id, message, is_read) VALUES
((SELECT id FROM users WHERE username = 'warehouse'), 'Review new warehouse submissions for approval.', 0),
((SELECT id FROM users WHERE username = 'warehouse'), 'Two seed farmer records are ready for reporting.', 0);

INSERT IGNORE INTO audit_logs (user_id, action, details) VALUES
((SELECT id FROM users WHERE username = '940640'), 'Database schema created and seeded.', JSON_OBJECT('source', 'database/schema.sql')),
((SELECT id FROM users WHERE username = 'warehouse'), 'Seed warehouse transactions recorded.', JSON_OBJECT('count', 2));
