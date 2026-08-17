CREATE DATABASE IF NOT EXISTS fsr CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE fsr;

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
    deactivation_reason TEXT NULL,
    deactivated_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS system_settings (
    setting_key VARCHAR(80) PRIMARY KEY,
    setting_value VARCHAR(255) NOT NULL
);

INSERT IGNORE INTO system_settings (setting_key, setting_value)
VALUES ('maintenance_mode', '0');

INSERT IGNORE INTO system_settings (setting_key, setting_value)
VALUES ('maintenance_schedule', ''), ('encoding_mode', '0'), ('delivery_schedule_mode', '0');

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
    name VARCHAR(120) NOT NULL UNIQUE,
    reference_code VARCHAR(12) NULL UNIQUE
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
    reference_code VARCHAR(12) NULL,
    UNIQUE KEY branch_region_unique (region_id, name),
    UNIQUE KEY branch_region_reference_unique (region_id, reference_code),
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
    total_members INT UNSIGNED NOT NULL DEFAULT 0,
    office_location VARCHAR(255) NULL,
    warehouse_id BIGINT UNSIGNED NULL,
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
    rsbsa_number VARCHAR(60) NULL UNIQUE,
    mao_certification VARCHAR(60) NULL,
    no_available_control_number BOOLEAN NOT NULL DEFAULT FALSE,
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
    valid_id_path VARCHAR(255),
    gender_orientation JSON,
    sector JSON,
    is_ip_group_member BOOLEAN NOT NULL DEFAULT FALSE,
    farmer_organization_id BIGINT UNSIGNED,
    province_id BIGINT UNSIGNED,
    warehouse_id BIGINT UNSIGNED,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (farmer_organization_id) REFERENCES farmer_organizations(id),
    FOREIGN KEY (province_id) REFERENCES province_offices(id),
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
    harvested_area_hectares DECIMAL(10,3),
    average_yield_per_hectare DECIMAL(10,3),
    summer_yield_per_hectare DECIMAL(10,3),
    third_crop_yield_per_hectare DECIMAL(10,3),
    INDEX landholdings_farmer_idx (farmer_id),
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
    verified_farm_area DECIMAL(10,3),
    delivery_date DATE NOT NULL,
    warehouse_stock_receipt_number VARCHAR(80) NOT NULL UNIQUE,
    palay_variety VARCHAR(10) NOT NULL DEFAULT 'PD1',
    price_per_kilogram DECIMAL(10,3) NOT NULL,
    net_kilogram DECIMAL(12,3) NOT NULL,
    total_amount DECIMAL(20,3) NOT NULL DEFAULT 0,
    total_cost DECIMAL(20,3) GENERATED ALWAYS AS (total_amount) STORED,
    bags_50kg DECIMAL(12,3) NOT NULL,
    warehouse_id BIGINT UNSIGNED,
      created_by BIGINT UNSIGNED,
      client_control_number VARCHAR(96) NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (farmer_id) REFERENCES farmers(id),
    FOREIGN KEY (farmer_organization_id) REFERENCES farmer_organizations(id),
    FOREIGN KEY (created_by) REFERENCES users(id),
    FOREIGN KEY (warehouse_id) REFERENCES warehouse_offices(id)
);

CREATE TABLE IF NOT EXISTS delivery_schedules (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    seller_type ENUM('Individual', 'Farmer Organization') NOT NULL DEFAULT 'Individual',
    farmer_id BIGINT UNSIGNED NULL,
    temporary_name VARCHAR(180) NULL,
    temporary_contact_number VARCHAR(40) NULL,
    farmer_organization_id BIGINT UNSIGNED NULL,
    temporary_organization_name VARCHAR(180) NULL,
    representative_name VARCHAR(180) NULL,
    schedule_date DATE NOT NULL,
    expected_bags DECIMAL(12,3) NOT NULL,
    confirmation_code VARCHAR(128) NOT NULL,
    status ENUM('Scheduled', 'Completed', 'Rescheduled', 'No-show') NOT NULL DEFAULT 'Scheduled',
    status_changed_at TIMESTAMP NULL,
    warehouse_id BIGINT UNSIGNED NOT NULL,
    created_by BIGINT UNSIGNED NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX delivery_schedules_date_warehouse (schedule_date, warehouse_id),
    INDEX delivery_schedules_reference_idx (confirmation_code),
    FOREIGN KEY (farmer_id) REFERENCES farmers(id) ON DELETE SET NULL,
    FOREIGN KEY (farmer_organization_id) REFERENCES farmer_organizations(id) ON DELETE SET NULL,
    FOREIGN KEY (warehouse_id) REFERENCES warehouse_offices(id),
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS delivery_schedule_days (
    warehouse_id BIGINT UNSIGNED NOT NULL,
    schedule_date DATE NOT NULL,
    status ENUM('Vacant', 'Full') NOT NULL DEFAULT 'Vacant',
    PRIMARY KEY (warehouse_id, schedule_date),
    FOREIGN KEY (warehouse_id) REFERENCES warehouse_offices(id)
);

CREATE TABLE IF NOT EXISTS delivery_schedule_sequences (
    branch_id BIGINT UNSIGNED NOT NULL,
    sequence_month CHAR(6) NOT NULL,
    last_number SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    PRIMARY KEY (branch_id, sequence_month),
    FOREIGN KEY (branch_id) REFERENCES branch_offices(id)
);

CREATE TABLE IF NOT EXISTS record_versions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    entity_type VARCHAR(40) NOT NULL,
    record_id BIGINT UNSIGNED NOT NULL,
    changes JSON NOT NULL,
    changed_by BIGINT UNSIGNED NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX record_versions_lookup (entity_type, record_id, created_at),
    FOREIGN KEY (changed_by) REFERENCES users(id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS transaction_farmer_members (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    transaction_id BIGINT UNSIGNED NOT NULL,
    farmer_id BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY transaction_farmer_unique (transaction_id, farmer_id),
    KEY transaction_farmer_members_farmer_id_index (farmer_id),
    FOREIGN KEY (transaction_id) REFERENCES transactions(id) ON DELETE CASCADE,
    FOREIGN KEY (farmer_id) REFERENCES farmers(id) ON DELETE CASCADE
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
ALTER TABLE users ADD COLUMN IF NOT EXISTS deactivation_reason TEXT NULL;
ALTER TABLE users ADD COLUMN IF NOT EXISTS deactivated_at TIMESTAMP NULL;
ALTER TABLE farmers ADD COLUMN IF NOT EXISTS warehouse_id BIGINT UNSIGNED NULL;
ALTER TABLE farmers ADD COLUMN IF NOT EXISTS province_id BIGINT UNSIGNED NULL;
ALTER TABLE farmers ADD COLUMN IF NOT EXISTS photo_path VARCHAR(255) NULL;
ALTER TABLE farmers ADD COLUMN IF NOT EXISTS valid_id_path VARCHAR(255) NULL;
ALTER TABLE landholdings ADD COLUMN IF NOT EXISTS third_crop_yield_per_hectare DECIMAL(10,3) NULL;
ALTER TABLE farmers ADD COLUMN IF NOT EXISTS farmer_key VARCHAR(32) NULL AFTER id;
ALTER TABLE farmers ADD COLUMN IF NOT EXISTS is_ip_group_member TINYINT(1) NOT NULL DEFAULT 0;
ALTER TABLE farmers ADD COLUMN IF NOT EXISTS mao_certification VARCHAR(60) NULL;
ALTER TABLE farmers ADD COLUMN IF NOT EXISTS no_available_control_number TINYINT(1) NOT NULL DEFAULT 0;
ALTER TABLE farmers MODIFY rsbsa_number VARCHAR(60) NULL;
ALTER TABLE farmer_organizations ADD COLUMN IF NOT EXISTS total_members INT UNSIGNED NOT NULL DEFAULT 0;
ALTER TABLE farmer_organizations ADD COLUMN IF NOT EXISTS office_location VARCHAR(255) NULL;
ALTER TABLE farmer_organizations ADD COLUMN IF NOT EXISTS warehouse_id BIGINT UNSIGNED NULL;
ALTER TABLE farmer_organizations ADD COLUMN IF NOT EXISTS is_indigenous_sector_group TINYINT(1) NOT NULL DEFAULT 0;
ALTER TABLE farmer_organizations ADD COLUMN IF NOT EXISTS classification_type VARCHAR(40) NOT NULL DEFAULT 'Farmer Organization';
UPDATE farmer_organizations
SET classification_type = CASE
    WHEN is_indigenous_sector_group = 1 THEN 'Indigenous People Group'
    ELSE 'Farmer Organization'
END;
ALTER TABLE transactions ADD COLUMN IF NOT EXISTS warehouse_id BIGINT UNSIGNED NULL;
ALTER TABLE transactions ADD COLUMN IF NOT EXISTS is_ip_group_delivery TINYINT(1) NOT NULL DEFAULT 0;
ALTER TABLE transactions ADD COLUMN IF NOT EXISTS total_amount DECIMAL(20,3) NOT NULL DEFAULT 0;
ALTER TABLE transactions ADD COLUMN IF NOT EXISTS palay_variety VARCHAR(10) NOT NULL DEFAULT 'PD1' AFTER warehouse_stock_receipt_number;
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

CREATE TABLE IF NOT EXISTS notification_reads (
    notification_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED NOT NULL,
    read_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (notification_id, user_id),
    FOREIGN KEY (notification_id) REFERENCES notifications(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Added for per-user notification choices.  This creates settings only and does
-- not alter existing farmer, transaction, or notification records.
CREATE TABLE IF NOT EXISTS notification_preferences (
    user_id BIGINT UNSIGNED NOT NULL PRIMARY KEY,
    office_location TINYINT(1) NOT NULL DEFAULT 1,
    location_level VARCHAR(16) NOT NULL DEFAULT 'Region',
    farmer_new TINYINT(1) NOT NULL DEFAULT 1,
    farmer_updates TINYINT(1) NOT NULL DEFAULT 1,
    farmer_delivery TINYINT(1) NOT NULL DEFAULT 1,
    farmer_delivery_individual TINYINT(1) NOT NULL DEFAULT 1,
    farmer_delivery_fo TINYINT(1) NOT NULL DEFAULT 1,
    annual_bag_limit TINYINT(1) NOT NULL DEFAULT 1,
    cross_location_delivery TINYINT(1) NOT NULL DEFAULT 1,
    tech_support TINYINT(1) NOT NULL DEFAULT 1,
    account_updates TINYINT(1) NOT NULL DEFAULT 1,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS display_settings (
    id TINYINT UNSIGNED PRIMARY KEY,
    loop_duration TINYINT UNSIGNED NOT NULL DEFAULT 7,
    panning_enabled TINYINT(1) NOT NULL DEFAULT 1
);

CREATE TABLE IF NOT EXISTS display_photos (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    submitted_by BIGINT UNSIGNED NULL,
    title VARCHAR(160) NOT NULL,
    photographer_name VARCHAR(160) NOT NULL,
    location VARCHAR(160) NOT NULL DEFAULT '',
    image_path VARCHAR(255) NOT NULL,
    optimized_path VARCHAR(255) NULL,
    source VARCHAR(80) NOT NULL DEFAULT 'User submission',
    image_width INT UNSIGNED NULL,
    image_height INT UNSIGNED NULL,
    position INT UNSIGNED NOT NULL DEFAULT 999,
    status ENUM('Pending', 'Approved', 'Rejected') NOT NULL DEFAULT 'Pending',
    reviewed_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX display_photo_status_position (status, position),
    FOREIGN KEY (submitted_by) REFERENCES users(id) ON DELETE SET NULL
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
('System Admin', '940640', 'superadmin@fsr.local', '$2y$10$GN7cBbOJqlqWKG4WTlq9WeDddCeEISNlbqSS3enkM2UeyQxVXti9e', 'System Admin', 1, 'Active', 'System Administrator', 'n/a'),
('Maria Warehouse', 'warehouse', 'warehouse@fsr.local', '$2y$10$eImiTXuWVxfM37uY4JANjQeD8ZtcVgHPwrFA4ocK9n53KRzLtPz4S', 'Warehouse Personnel', 1, 'Active', 'Warehouse Personnel', '09170000000');

INSERT IGNORE INTO regions (name) VALUES
('Region I'), ('Region II'), ('Region III'), ('Region IV'), ('Region V'),
('Region VI'), ('Region VII'), ('Region VIII'), ('Region IX'), ('Region X'),
('Region XI'), ('Region XII'), ('Region XIII'), ('Region XIV'), ('Region XV');

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
    NULL
),
(
    '03-24-001-000002', 'Jose', 'Reyes', 'Garcia', 'Munoz, Nueva Ecija',
    '1976-09-03', 'Nueva Ecija', 'Single', NULL, 2, '09179876543',
    'jose@example.com', 'Male', JSON_ARRAY(), JSON_ARRAY('Adult'),
    NULL
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
    NULL,
    NULL, NULL, 2.40, CURDATE(), 'WSR-2026-0001', 23.00, 2400.00, 48,
    (SELECT id FROM users WHERE username = 'warehouse')
),
(
    'Individual', 'Mobile Procurement',
    (SELECT id FROM farmers WHERE rsbsa_number = '03-24-001-000002'),
    NULL,
    NULL, NULL, 1.70, CURDATE(), 'WSR-2026-0002', 23.00, 1700.00, 34,
    (SELECT id FROM users WHERE username = 'warehouse')
);

INSERT IGNORE INTO notifications (user_id, message, is_read) VALUES
((SELECT id FROM users WHERE username = 'warehouse'), 'Review new warehouse submissions for approval.', 0),
((SELECT id FROM users WHERE username = 'warehouse'), 'Two seed farmer records are ready for reporting.', 0);

INSERT IGNORE INTO audit_logs (user_id, action, details) VALUES
((SELECT id FROM users WHERE username = '940640'), 'Database schema created and seeded.', JSON_OBJECT('source', 'database/schema.sql')),
((SELECT id FROM users WHERE username = 'warehouse'), 'Seed warehouse transactions recorded.', JSON_OBJECT('count', 2));
