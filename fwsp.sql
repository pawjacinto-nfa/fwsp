-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 22, 2026 at 08:59 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `fwsp`
--

-- --------------------------------------------------------

--
-- Table structure for table `audit_logs`
--

DROP TABLE IF EXISTS `audit_logs`;
CREATE TABLE `audit_logs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `action` varchar(120) NOT NULL,
  `details` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`details`)),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `audit_logs`
--

INSERT INTO `audit_logs` (`id`, `user_id`, `action`, `details`, `created_at`) VALUES
(1, 1, 'Database schema created and seeded.', '{\"source\": \"database/schema.sql\"}', '2026-06-25 13:45:17'),
(2, 2, 'Seed warehouse transactions recorded.', '{\"count\": 2}', '2026-06-25 13:45:17'),
(3, 1, 'Super Admin logged in.', '{}', '2026-06-25 13:48:54'),
(4, 1, 'Super Admin logged in.', '{}', '2026-06-25 13:52:32'),
(5, 1, 'Super Admin logged in.', '{}', '2026-06-25 13:54:19'),
(6, 1, 'Super Admin logged out.', '{}', '2026-06-25 13:54:39'),
(7, 1, 'Super Admin logged in.', '{}', '2026-06-25 13:54:54'),
(8, 1, 'Super Admin logged in.', '{}', '2026-06-25 14:08:26'),
(9, 1, 'Super Admin logged in.', '{}', '2026-06-25 14:14:15'),
(10, 1, 'Central office directory entry deleted.', '{}', '2026-06-25 14:14:21'),
(11, 1, 'Central office directory entry deleted.', '{}', '2026-06-25 14:14:45'),
(12, 1, 'Central office directory updated.', '{}', '2026-06-25 14:17:45'),
(13, 1, 'Central office directory entry deleted.', '{}', '2026-06-25 14:17:51'),
(14, 1, 'Super Admin logged out.', '{}', '2026-06-29 01:24:54'),
(15, 3, 'Seed Warehouse Manager logged in.', '{}', '2026-06-29 01:25:02'),
(16, 3, 'Seed Warehouse Manager logged out.', '{}', '2026-06-29 01:32:58'),
(17, 4, 'Seed Regional Branch Manager logged in.', '{}', '2026-06-29 01:34:41'),
(18, 1, 'Super Admin logged in.', '{}', '2026-06-29 01:41:13'),
(19, 1, 'Super Admin logged out.', '{}', '2026-06-29 01:41:25'),
(20, 1, 'Super Admin logged in.', '{}', '2026-06-29 01:41:30'),
(21, 1, 'Super Admin logged out.', '{}', '2026-06-29 02:21:15'),
(22, 1, 'Super Admin logged in.', '{}', '2026-06-29 02:21:18'),
(23, 1, 'Super Admin logged out.', '{}', '2026-06-29 02:21:20'),
(24, 1, 'Super Admin logged in.', '{}', '2026-06-29 02:21:44'),
(25, 1, 'Super Admin logged out.', '{}', '2026-06-29 06:02:17'),
(26, 3, 'Seed Warehouse Manager logged in.', '{}', '2026-06-29 06:02:28'),
(27, 3, 'Danica Garcia updated account details.', '{}', '2026-06-29 06:02:57'),
(28, 3, 'Danica Garcia logged out.', '{}', '2026-06-29 06:31:03'),
(29, 1, 'Super Admin logged in.', '{}', '2026-06-29 06:33:06'),
(30, 1, 'Paw Jacinto updated account details.', '{}', '2026-06-29 06:34:11'),
(31, 1, 'Paw Jacinto logged out.', '{}', '2026-06-29 06:55:41'),
(32, 3, 'Danica Garcia logged in.', '{}', '2026-06-29 06:55:54'),
(33, 3, 'Danica Garcia logged out.', '{}', '2026-06-29 07:25:29'),
(34, 3, 'Danica Garcia logged in.', '{}', '2026-06-29 07:25:52'),
(35, 3, 'Danica Garcia logged out.', '{}', '2026-06-29 07:26:48'),
(36, 1, 'Paw Jacinto logged in.', '{}', '2026-06-29 07:26:54'),
(37, 1, 'Paw Jacinto logged out.', '{}', '2026-06-29 07:35:52'),
(38, 1, 'Paw Jacinto logged in.', '{}', '2026-06-29 07:37:25'),
(39, 1, 'Paw Jacinto logged out.', '{}', '2026-06-29 16:06:56'),
(40, 3, 'Danica Garcia logged in.', '{}', '2026-06-29 16:07:22'),
(41, 3, 'Danica Garcia logged out.', '{}', '2026-06-30 02:53:34'),
(42, 6, 'Seed Manager 000001 logged in.', '{}', '2026-06-30 02:53:40'),
(43, 6, 'Seed Manager 000001 logged out.', '{}', '2026-06-30 02:55:18'),
(44, 6, 'Seed Manager 000001 logged in.', '{}', '2026-06-30 02:56:31'),
(45, 6, 'Seed Manager 000001 logged out.', '{}', '2026-06-30 02:57:00'),
(46, 1, 'Paw Jacinto logged in.', '{}', '2026-06-30 02:57:21'),
(47, 1, 'Paw Jacinto logged out.', '{}', '2026-06-30 06:25:20'),
(48, 3, 'Danica Garcia logged in.', '{}', '2026-06-30 07:02:12'),
(49, 3, 'Danica Garcia logged in.', '{}', '2026-06-30 07:04:26'),
(50, 3, 'Danica Garcia logged in.', '{}', '2026-06-30 07:04:44'),
(51, 1, 'Paw Jacinto logged in.', '{}', '2026-07-03 02:28:29'),
(52, 1, 'Paw Jacinto logged in.', '{}', '2026-07-03 02:45:57'),
(53, 1, 'Paw Jacinto logged out.', '{}', '2026-07-15 07:41:39'),
(54, 1, 'Paw Jacinto logged in.', '{}', '2026-07-15 08:10:36'),
(55, 1, 'Paw Jacinto logged out.', '{}', '2026-07-16 01:18:23'),
(56, 1, 'Paw Jacinto logged in.', '{}', '2026-07-16 01:18:29'),
(57, 1, 'Paw Jacinto logged in.', '{}', '2026-07-17 07:13:48'),
(58, 1, 'Bulk user access updated for 5 account(s).', '{}', '2026-07-22 01:10:12'),
(59, 1, 'Bulk user access updated for 5 account(s).', '{}', '2026-07-22 01:10:23'),
(60, 1, 'Paw Jacinto logged out.', '{}', '2026-07-22 01:17:02'),
(61, 1, 'Paw Jacinto logged in.', '{}', '2026-07-22 02:08:46'),
(62, 1, 'Paw Jacinto logged out.', '{}', '2026-07-22 02:19:01'),
(63, 1, 'Paw Jacinto logged in.', '{}', '2026-07-22 03:06:55'),
(64, 1, 'Paw Jacinto logged out.', '{}', '2026-07-22 03:11:56'),
(65, 1, 'Paw Jacinto logged in.', '{}', '2026-07-22 03:12:37'),
(66, 1, 'Paw Jacinto logged out.', '{}', '2026-07-22 03:13:42'),
(67, NULL, 'New user registration submitted for 111111.', '{}', '2026-07-22 03:14:48'),
(68, 1, 'Paw Jacinto logged in.', '{}', '2026-07-22 03:14:57'),
(69, 1, 'User access updated.', '{}', '2026-07-22 03:15:09'),
(70, 1, 'Paw Jacinto logged out.', '{}', '2026-07-22 03:15:13'),
(71, 8, 'Test User Warehouse logged in.', '{}', '2026-07-22 03:15:27'),
(72, 8, 'Test User Warehouse logged out.', '{}', '2026-07-22 03:18:09'),
(73, 8, 'Test User Warehouse logged in.', '{}', '2026-07-22 03:31:05'),
(74, 3, 'Danica Garcia logged in.', '{}', '2026-07-22 04:09:06'),
(75, 3, 'Danica Garcia logged in.', '{}', '2026-07-22 04:09:24'),
(76, 3, 'Danica Garcia logged in.', '{}', '2026-07-22 04:11:58'),
(77, 1, 'Paw Jacinto logged in.', '{}', '2026-07-22 04:22:44'),
(78, 3, 'Danica Garcia logged in.', '{}', '2026-07-22 04:25:24');

-- --------------------------------------------------------

--
-- Table structure for table `branch_offices`
--

DROP TABLE IF EXISTS `branch_offices`;
CREATE TABLE `branch_offices` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `region_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(160) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `branch_offices`
--

INSERT INTO `branch_offices` (`id`, `region_id`, `name`) VALUES
(2, 16, 'BASULTA Branch'),
(3, 16, 'Lanao del Sur Branch'),
(4, 16, 'Maguindanao Branch'),
(5, 16, 'Regional Office'),
(6, 17, 'Agusan Del Sur Branch Office'),
(7, 17, 'Regional Office CARAGA'),
(8, 17, 'Surigao Del Sur Branch Office'),
(9, 18, 'Central District'),
(10, 18, 'East District'),
(11, 18, 'Regional Office NCR'),
(12, 19, 'Eastern Pangasinan Branch Office'),
(13, 19, 'Ilocos Norte Branch Office'),
(14, 19, 'La Union Branch Office'),
(15, 19, 'NFA Regional Office'),
(16, 20, 'Cagayan Branch Office'),
(17, 20, 'Isabela Branch Office'),
(18, 20, 'NFA Region 2'),
(19, 20, 'Nueva Vizcaya Branch Office'),
(20, 21, 'Bulacan Branch Office'),
(1, 21, 'Nueva Ecija Branch'),
(21, 21, 'Nueva Ecija Branch Office'),
(22, 21, 'Pampanga Branch Office'),
(23, 21, 'Regional Office IIII'),
(24, 21, 'Tarlac Branch Office'),
(25, 22, 'Batangas Branch Office'),
(26, 22, 'Laguna Branch Office'),
(27, 22, 'Occidental Mindoro Branch Office'),
(28, 22, 'Oriental Mindoro Branch Office'),
(29, 22, 'Palawan Branch Office'),
(30, 22, 'Quezon Branch Office'),
(31, 22, 'Regional Office IV'),
(32, 23, 'Zamboanga Branch Office'),
(33, 23, 'Zamboanga del Sur Branch Office'),
(34, 24, 'Albay Branch Office'),
(35, 24, 'Camarines Sur Branch Office'),
(36, 24, 'Regional Office'),
(37, 24, 'Sorsogon Branch Office'),
(38, 25, 'Capiz Branch'),
(39, 25, 'Iloilo Branch'),
(40, 25, 'Negros Occidental Branch'),
(41, 25, 'Regional Office'),
(42, 26, 'Bohol Branch Office'),
(43, 26, 'Cebu Branch Office'),
(44, 26, 'Negros Oriental Branch Office'),
(45, 27, 'Leyte Branch Office'),
(46, 27, 'Regional Office'),
(47, 27, 'Samar Branch Office'),
(48, 28, 'Bukidnon Branch Office'),
(49, 28, 'Lanao del Norte Branch Office'),
(50, 28, 'Misamis Oriental Branch Office'),
(51, 28, 'Regional Office X'),
(52, 29, 'Davao del Norte'),
(53, 29, 'Davao Del Sur Branch Office'),
(54, 29, 'Davao Oriental Branch Office'),
(55, 29, 'Regional Office XI'),
(56, 30, 'North Cotabato'),
(57, 30, 'Regional Office XII'),
(58, 30, 'South Cotabato'),
(59, 30, 'Sultan Kudarat');

-- --------------------------------------------------------

--
-- Table structure for table `central_departments`
--

DROP TABLE IF EXISTS `central_departments`;
CREATE TABLE `central_departments` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(180) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `central_departments`
--

INSERT INTO `central_departments` (`id`, `name`) VALUES
(6, 'Administrative and General Services Department'),
(2, 'Corporate Planning and Management Services Department'),
(9, 'Finance Department'),
(10, 'Internal Audit Department'),
(11, 'Legal Affairs Department'),
(1, 'Office of the Administrator'),
(3, 'Office of the Assistant Administrator for Finance and Administration'),
(4, 'Office of the Assistant Administrator for Operations'),
(15, 'Office of the Deputy Administrator'),
(5, 'Office of the NFA Council Secretary'),
(12, 'Operations Coordination Department');

-- --------------------------------------------------------

--
-- Table structure for table `central_divisions`
--

DROP TABLE IF EXISTS `central_divisions`;
CREATE TABLE `central_divisions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `department_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(180) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `central_divisions`
--

INSERT INTO `central_divisions` (`id`, `department_id`, `name`) VALUES
(1, 1, 'Public Affairs Division'),
(2, 2, 'Corporate Planning Division'),
(3, 2, 'Information and Communications Technology Services Division'),
(6, 6, 'General Services Division'),
(5, 6, 'Human Resource Devt. & Services Division'),
(9, 9, 'Accounting Division'),
(10, 9, 'Budget Division'),
(11, 10, 'Management Audit Division'),
(12, 10, 'Operations Audit Division'),
(13, 11, 'Investigation and Documentation Division'),
(14, 11, 'Litigation and Prosecution Division'),
(15, 12, 'Operations Planning and Monitoring Division'),
(16, 12, 'Technical Services Division');

-- --------------------------------------------------------

--
-- Table structure for table `central_units`
--

DROP TABLE IF EXISTS `central_units`;
CREATE TABLE `central_units` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `division_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(180) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `display_photos`
--

DROP TABLE IF EXISTS `display_photos`;
CREATE TABLE `display_photos` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `submitted_by` bigint(20) UNSIGNED DEFAULT NULL,
  `title` varchar(160) NOT NULL,
  `photographer_name` varchar(160) NOT NULL,
  `location` varchar(160) NOT NULL DEFAULT '',
  `image_path` varchar(255) NOT NULL,
  `optimized_path` varchar(255) DEFAULT NULL,
  `source` varchar(80) NOT NULL DEFAULT 'User submission',
  `image_width` int(10) UNSIGNED DEFAULT NULL,
  `image_height` int(10) UNSIGNED DEFAULT NULL,
  `position` int(10) UNSIGNED NOT NULL DEFAULT 999,
  `status` enum('Pending','Approved','Rejected') NOT NULL DEFAULT 'Pending',
  `reviewed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `display_photos`
--

INSERT INTO `display_photos` (`id`, `submitted_by`, `title`, `photographer_name`, `location`, `image_path`, `optimized_path`, `source`, `image_width`, `image_height`, `position`, `status`, `reviewed_at`, `created_at`) VALUES
(1, NULL, 'Rainy-day rice field', 'Ruth Bolano', '', 'assets/images/landing-slides/palay-01.avif', NULL, 'Pexels', NULL, NULL, 1, 'Approved', NULL, '2026-07-22 02:15:40'),
(2, NULL, 'Rice at dusk', 'Stijn Dijkstra', '', 'assets/images/landing-slides/palay-02.avif', NULL, 'Pexels', NULL, NULL, 2, 'Approved', NULL, '2026-07-22 02:15:40'),
(3, NULL, 'Terraced fields', 'Charlie Dogaong', '', 'assets/images/landing-slides/palay-03.avif', NULL, 'Pexels', NULL, NULL, 3, 'Approved', NULL, '2026-07-22 02:15:40'),
(4, NULL, 'Morning over the fields', 'Aria Batula', '', 'assets/images/landing-slides/palay-04.avif', NULL, 'Pexels', NULL, NULL, 4, 'Approved', NULL, '2026-07-22 02:15:40'),
(5, NULL, 'Aerial rice landscape', 'Bobby Mc Gee Lee', '', 'assets/images/landing-slides/palay-05.avif', NULL, 'Pexels', NULL, NULL, 5, 'Approved', NULL, '2026-07-22 02:15:40'),
(6, NULL, 'Working the paddy', 'Dave', '', 'assets/images/landing-slides/palay-06.avif', NULL, 'Pexels', NULL, NULL, 6, 'Approved', NULL, '2026-07-22 02:15:40'),
(7, NULL, 'After the rain', 'Denniz Futalan', '', 'assets/images/landing-slides/palay-07.avif', NULL, 'Pexels', NULL, NULL, 7, 'Approved', NULL, '2026-07-22 02:15:40'),
(8, NULL, 'Watered terraces', 'Dada', '', 'assets/images/landing-slides/palay-08.avif', NULL, 'Pexels', NULL, NULL, 8, 'Approved', NULL, '2026-07-22 02:15:40'),
(9, NULL, 'Planting season', 'Neil Clark Ongchangco', '', 'assets/images/landing-slides/palay-09.avif', NULL, 'Pexels', NULL, NULL, 9, 'Approved', NULL, '2026-07-22 02:15:40'),
(10, NULL, 'Fields in afternoon light', 'XT7CORE', '', 'assets/images/landing-slides/palay-10.avif', NULL, 'Pexels', NULL, NULL, 10, 'Approved', NULL, '2026-07-22 02:15:40');

-- --------------------------------------------------------

--
-- Table structure for table `display_settings`
--

DROP TABLE IF EXISTS `display_settings`;
CREATE TABLE `display_settings` (
  `id` tinyint(3) UNSIGNED NOT NULL,
  `loop_duration` tinyint(3) UNSIGNED NOT NULL DEFAULT 7,
  `panning_enabled` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `display_settings`
--

INSERT INTO `display_settings` (`id`, `loop_duration`, `panning_enabled`) VALUES
(1, 7, 1);

-- --------------------------------------------------------

--
-- Table structure for table `farmers`
--

DROP TABLE IF EXISTS `farmers`;
CREATE TABLE `farmers` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `farmer_key` varchar(32) DEFAULT NULL,
  `rsbsa_number` varchar(60) NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `middle_name` varchar(100) DEFAULT NULL,
  `last_name` varchar(100) NOT NULL,
  `address` text NOT NULL,
  `birthdate` date DEFAULT NULL,
  `birthplace` varchar(160) DEFAULT NULL,
  `civil_status` varchar(40) DEFAULT NULL,
  `spouse_name` varchar(160) DEFAULT NULL,
  `dependents` int(10) UNSIGNED DEFAULT 0,
  `contact_number` varchar(40) DEFAULT NULL,
  `email` varchar(160) DEFAULT NULL,
  `sex` enum('Female','Male') NOT NULL,
  `photo_path` varchar(255) DEFAULT NULL,
  `gender_orientation` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`gender_orientation`)),
  `sector` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`sector`)),
  `farmer_organization_id` bigint(20) UNSIGNED DEFAULT NULL,
  `warehouse_id` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_ip_group_member` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `farmers`
--

INSERT INTO `farmers` (`id`, `farmer_key`, `rsbsa_number`, `first_name`, `middle_name`, `last_name`, `address`, `birthdate`, `birthplace`, `civil_status`, `spouse_name`, `dependents`, `contact_number`, `email`, `sex`, `photo_path`, `gender_orientation`, `sector`, `farmer_organization_id`, `warehouse_id`, `created_at`, `is_ip_group_member`) VALUES
(1, 'NFAFWSP-2606-0000001', '03-24-001-000001', 'Maria', 'Santos', 'Dela Cruz', 'San Jose, Nueva Ecija', '1984-04-12', 'Nueva Ecija', 'Married', 'Ramon Dela Cruz', 4, '09171234567', 'maria@example.com', 'Female', NULL, '[]', '[\"Adult\"]', 1, 1, '2026-06-25 13:45:17', 0),
(2, 'NFAFWSP-2606-0000002', '03-24-001-000002', 'Jose', 'Reyes', 'Garcia', 'Munoz, Nueva Ecija', '1976-09-03', 'Nueva Ecija', 'Single', NULL, 2, '09179876543', 'jose@example.com', 'Male', NULL, '[]', '[\"Adult\"]', 2, 1, '2026-06-25 13:45:17', 0),
(3, 'NFAFWSP-2606-0000003', 'FULLLIST-FO-2026-01-01', 'Aida', 'FO', 'Dizon', 'San Jose, Nueva Ecija', '1971-03-12', 'Nueva Ecija', 'Married', 'Seed Spouse 1', 1, '09187000001', 'full.list.fo01.member01@example.com', 'Female', NULL, '[\"N/A\"]', '[\"Adult\"]', 1, 1, '2026-06-25 13:49:51', 0),
(4, 'NFAFWSP-2606-0000004', 'FULLLIST-FO-2026-01-02', 'Ben', 'FO', 'Evangelista', 'San Jose, Nueva Ecija', '1972-04-13', 'Nueva Ecija', 'Single', NULL, 2, '09187000002', 'full.list.fo01.member02@example.com', 'Male', NULL, '[\"N/A\"]', '[\"Adult\"]', 1, 1, '2026-06-25 13:49:51', 0),
(5, 'NFAFWSP-2606-0000005', 'FULLLIST-FO-2026-01-03', 'Corazon', 'FO', 'Fajardo', 'San Jose, Nueva Ecija', '1973-05-14', 'Nueva Ecija', 'Married', 'Seed Spouse 3', 3, '09187000003', 'full.list.fo01.member03@example.com', 'Female', NULL, '[\"N/A\"]', '[\"Adult\"]', 1, 1, '2026-06-25 13:49:51', 0),
(6, 'NFAFWSP-2606-0000006', 'FULLLIST-FO-2026-01-04', 'Dennis', 'FO', 'Galang', 'San Jose, Nueva Ecija', '1974-06-15', 'Nueva Ecija', 'Single', NULL, 4, '09187000004', 'full.list.fo01.member04@example.com', 'Male', NULL, '[\"N/A\"]', '[\"Adult\"]', 1, 1, '2026-06-25 13:49:51', 0),
(7, 'NFAFWSP-2606-0000007', 'FULLLIST-FO-2026-01-05', 'Elvie', 'FO', 'Hizon', 'San Jose, Nueva Ecija', '1975-07-16', 'Nueva Ecija', 'Married', 'Seed Spouse 5', 5, '09187000005', 'full.list.fo01.member05@example.com', 'Female', NULL, '[\"N/A\"]', '[\"Adult\"]', 1, 1, '2026-06-25 13:49:51', 0),
(8, 'NFAFWSP-2606-0000008', 'FULLLIST-FO-2026-02-01', 'Felisa', 'FO', 'Dizon', 'Munoz, Nueva Ecija', '1976-03-12', 'Nueva Ecija', 'Married', 'Seed Spouse 6', 1, '09187000006', 'full.list.fo02.member01@example.com', 'Female', NULL, '[\"N/A\"]', '[\"Adult\"]', 2, 1, '2026-06-25 13:49:51', 0),
(9, 'NFAFWSP-2606-0000009', 'FULLLIST-FO-2026-02-02', 'Gregorio', 'FO', 'Evangelista', 'Munoz, Nueva Ecija', '1977-04-13', 'Nueva Ecija', 'Single', NULL, 2, '09187000007', 'full.list.fo02.member02@example.com', 'Male', NULL, '[\"N/A\"]', '[\"Adult\"]', 2, 1, '2026-06-25 13:49:51', 0),
(10, 'NFAFWSP-2606-0000010', 'FULLLIST-FO-2026-02-03', 'Helen', 'FO', 'Fajardo', 'Munoz, Nueva Ecija', '1978-05-14', 'Nueva Ecija', 'Married', 'Seed Spouse 8', 3, '09187000008', 'full.list.fo02.member03@example.com', 'Female', NULL, '[\"N/A\"]', '[\"Adult\"]', 2, 1, '2026-06-25 13:49:51', 0),
(11, 'NFAFWSP-2606-0000011', 'FULLLIST-FO-2026-02-04', 'Isko', 'FO', 'Galang', 'Munoz, Nueva Ecija', '1979-06-15', 'Nueva Ecija', 'Single', NULL, 4, '09187000009', 'full.list.fo02.member04@example.com', 'Male', NULL, '[\"N/A\"]', '[\"Adult\"]', 2, 1, '2026-06-25 13:49:51', 0),
(12, 'NFAFWSP-2606-0000012', 'FULLLIST-FO-2026-02-05', 'Julieta', 'FO', 'Hizon', 'Munoz, Nueva Ecija', '1980-07-16', 'Nueva Ecija', 'Married', 'Seed Spouse 10', 5, '09187000010', 'full.list.fo02.member05@example.com', 'Female', NULL, '[\"N/A\"]', '[\"Adult\"]', 2, 1, '2026-06-25 13:49:51', 0),
(13, 'NFAFWSP-2606-0000013', 'FULLLIST-FO-2026-03-01', 'Kardo', 'FO', 'Dizon', 'Cabanatuan, Nueva Ecija', '1981-03-12', 'Nueva Ecija', 'Married', 'Seed Spouse 11', 1, '09187000011', 'full.list.fo03.member01@example.com', 'Female', NULL, '[\"N/A\"]', '[\"Adult\"]', 5, 1, '2026-06-25 13:49:51', 0),
(14, 'NFAFWSP-2606-0000014', 'FULLLIST-FO-2026-03-02', 'Lina', 'FO', 'Evangelista', 'Cabanatuan, Nueva Ecija', '1982-04-13', 'Nueva Ecija', 'Single', NULL, 2, '09187000012', 'full.list.fo03.member02@example.com', 'Male', NULL, '[\"N/A\"]', '[\"Adult\"]', 5, 1, '2026-06-25 13:49:51', 0),
(15, 'NFAFWSP-2606-0000015', 'FULLLIST-FO-2026-03-03', 'Mario', 'FO', 'Fajardo', 'Cabanatuan, Nueva Ecija', '1983-05-14', 'Nueva Ecija', 'Married', 'Seed Spouse 13', 3, '09187000013', 'full.list.fo03.member03@example.com', 'Female', NULL, '[\"N/A\"]', '[\"Adult\"]', 5, 1, '2026-06-25 13:49:51', 0),
(16, 'NFAFWSP-2606-0000016', 'FULLLIST-FO-2026-03-04', 'Nelia', 'FO', 'Galang', 'Cabanatuan, Nueva Ecija', '1984-06-15', 'Nueva Ecija', 'Single', NULL, 4, '09187000014', 'full.list.fo03.member04@example.com', 'Male', NULL, '[\"N/A\"]', '[\"Adult\"]', 5, 1, '2026-06-25 13:49:51', 0),
(17, 'NFAFWSP-2606-0000017', 'FULLLIST-FO-2026-03-05', 'Oscar', 'FO', 'Hizon', 'Cabanatuan, Nueva Ecija', '1985-07-16', 'Nueva Ecija', 'Married', 'Seed Spouse 15', 5, '09187000015', 'full.list.fo03.member05@example.com', 'Female', NULL, '[\"N/A\"]', '[\"Adult\"]', 5, 1, '2026-06-25 13:49:51', 0),
(18, 'NFAFWSP-2606-0000018', 'FULLLIST-FO-2026-04-01', 'Perla', 'FO', 'Dizon', 'Talavera, Nueva Ecija', '1986-03-12', 'Nueva Ecija', 'Married', 'Seed Spouse 16', 1, '09187000016', 'full.list.fo04.member01@example.com', 'Female', NULL, '[\"N/A\"]', '[\"Adult\"]', 6, 1, '2026-06-25 13:49:51', 0),
(19, 'NFAFWSP-2606-0000019', 'FULLLIST-FO-2026-04-02', 'Quentin', 'FO', 'Evangelista', 'Talavera, Nueva Ecija', '1987-04-13', 'Nueva Ecija', 'Single', NULL, 2, '09187000017', 'full.list.fo04.member02@example.com', 'Male', NULL, '[\"N/A\"]', '[\"Adult\"]', 6, 1, '2026-06-25 13:49:51', 0),
(20, 'NFAFWSP-2606-0000020', 'FULLLIST-FO-2026-04-03', 'Rosalinda', 'FO', 'Fajardo', 'Talavera, Nueva Ecija', '1988-05-14', 'Nueva Ecija', 'Married', 'Seed Spouse 18', 3, '09187000018', 'full.list.fo04.member03@example.com', 'Female', NULL, '[\"N/A\"]', '[\"Adult\"]', 6, 1, '2026-06-25 13:49:51', 0),
(21, 'NFAFWSP-2606-0000021', 'FULLLIST-FO-2026-04-04', 'Samuel', 'FO', 'Galang', 'Talavera, Nueva Ecija', '1989-06-15', 'Nueva Ecija', 'Single', NULL, 4, '09187000019', 'full.list.fo04.member04@example.com', 'Male', NULL, '[\"N/A\"]', '[\"Adult\"]', 6, 1, '2026-06-25 13:49:51', 0),
(22, 'NFAFWSP-2606-0000022', 'FULLLIST-FO-2026-04-05', 'Teresita', 'FO', 'Hizon', 'Talavera, Nueva Ecija', '1990-07-16', 'Nueva Ecija', 'Married', 'Seed Spouse 20', 5, '09187000020', 'full.list.fo04.member05@example.com', 'Female', NULL, '[\"N/A\"]', '[\"Adult\"]', 6, 1, '2026-06-25 13:49:51', 0),
(43, 'NFAFWSP-2606-0000023', 'SEED-2026-001', 'Amelia', 'Seed', 'Santos', 'Basilan, ARMM', '1976-01-01', 'Basilan', 'Married', 'Seed Spouse 1', 1, '09170000001', 'seed.farmer01@example.com', 'Female', NULL, '[]', '[\"Adult\"]', NULL, 2, '2026-06-25 13:52:29', 0),
(44, 'NFAFWSP-2606-0000024', 'SEED-2026-002', 'Benito', 'Seed', 'Reyes', 'Agusan Del Norte, CARAGA', '1977-02-02', 'Agusan Del Norte', 'Married', 'Seed Spouse 2', 2, '09170000002', 'seed.farmer02@example.com', 'Male', NULL, '[]', '[\"Adult\"]', NULL, 21, '2026-06-25 13:52:29', 0),
(45, 'NFAFWSP-2606-0000025', 'SEED-2026-003', 'Carla', 'Seed', 'Cruz', 'Cavite, NCR', '1978-03-03', 'Cavite', 'Single', NULL, 3, '09170000003', 'seed.farmer03@example.com', 'Female', NULL, '[]', '[\"Adult\"]', NULL, 41, '2026-06-25 13:52:29', 0),
(46, 'NFAFWSP-2606-0000026', 'SEED-2026-004', 'Dante', 'Seed', 'Garcia', 'Eastern Pangasinan, Region I', '1979-04-04', 'Eastern Pangasinan', 'Married', 'Seed Spouse 4', 4, '09170000004', 'seed.farmer04@example.com', 'Male', NULL, '[]', '[\"Adult\"]', NULL, 61, '2026-06-25 13:52:29', 0),
(47, 'NFAFWSP-2606-0000027', 'SEED-2026-005', 'Elena', 'Seed', 'Mendoza', 'Ilocos Sur, Region I', '1980-05-05', 'Ilocos Sur', 'Married', 'Seed Spouse 5', 5, '09170000005', 'seed.farmer05@example.com', 'Female', NULL, '[]', '[\"Adult\"]', NULL, 81, '2026-06-25 13:52:29', 0),
(48, 'NFAFWSP-2606-0000028', 'SEED-2026-006', 'Felix', 'Seed', 'Torres', 'Cagayan, Region II', '1981-06-06', 'Cagayan', 'Single', NULL, 0, '09170000006', 'seed.farmer06@example.com', 'Male', NULL, '[]', '[\"Adult\"]', NULL, 101, '2026-06-25 13:52:29', 0),
(49, 'NFAFWSP-2606-0000029', 'SEED-2026-007', 'Grace', 'Seed', 'Flores', 'Isabela, Region II', '1982-07-07', 'Isabela', 'Married', 'Seed Spouse 7', 1, '09170000007', 'seed.farmer07@example.com', 'Female', NULL, '[]', '[\"Adult\"]', NULL, 121, '2026-06-25 13:52:29', 0),
(50, 'NFAFWSP-2606-0000030', 'SEED-2026-008', 'Hector', 'Seed', 'Ramos', 'Isabela, Region II', '1983-08-08', 'Isabela', 'Married', 'Seed Spouse 8', 2, '09170000008', 'seed.farmer08@example.com', 'Male', NULL, '[]', '[\"Adult\"]', NULL, 140, '2026-06-25 13:52:29', 0),
(51, 'NFAFWSP-2606-0000031', 'SEED-2026-009', 'Isabel', 'Seed', 'Aquino', 'Nueva Vizcaya, Region II', '1984-09-09', 'Nueva Vizcaya', 'Single', NULL, 3, '09170000009', 'seed.farmer09@example.com', 'Female', NULL, '[]', '[\"Adult\"]', NULL, 160, '2026-06-25 13:52:29', 0),
(52, 'NFAFWSP-2606-0000032', 'SEED-2026-010', 'Jun', 'Seed', 'Bautista', 'Aurora, Region III', '1985-10-10', 'Aurora', 'Married', 'Seed Spouse 10', 4, '09170000010', 'seed.farmer10@example.com', 'Male', NULL, '[]', '[\"Adult\"]', NULL, 180, '2026-06-25 13:52:29', 0),
(53, 'NFAFWSP-2606-0000033', 'SEED-2026-011', 'Karla', 'Seed', 'Castro', 'Bataan, Region III', '1986-11-11', 'Bataan', 'Married', 'Seed Spouse 11', 5, '09170000011', 'seed.farmer11@example.com', 'Female', NULL, '[]', '[\"Adult\"]', NULL, 200, '2026-06-25 13:52:29', 0),
(54, 'NFAFWSP-2606-0000034', 'SEED-2026-012', 'Leo', 'Seed', 'Diaz', 'Tarlac, Region III', '1987-12-12', 'Tarlac', 'Single', NULL, 0, '09170000012', 'seed.farmer12@example.com', 'Male', NULL, '[]', '[\"Adult\"]', NULL, 220, '2026-06-25 13:52:29', 0),
(55, 'NFAFWSP-2606-0000035', 'SEED-2026-013', 'Minda', 'Seed', 'Enriquez', 'Laguna, Region IV', '1988-01-13', 'Laguna', 'Married', 'Seed Spouse 13', 1, '09170000013', 'seed.farmer13@example.com', 'Female', NULL, '[]', '[\"Adult\"]', NULL, 240, '2026-06-25 13:52:29', 0),
(56, 'NFAFWSP-2606-0000036', 'SEED-2026-014', 'Nestor', 'Seed', 'Fernandez', 'Occidental Mindoro, Region IV', '1989-02-14', 'Occidental Mindoro', 'Married', 'Seed Spouse 14', 2, '09170000014', 'seed.farmer14@example.com', 'Male', NULL, '[]', '[\"Adult\"]', NULL, 259, '2026-06-25 13:52:29', 0),
(57, 'NFAFWSP-2606-0000037', 'SEED-2026-015', 'Olivia', 'Seed', 'Gonzales', 'Oriental Mindoro, Region IV', '1990-03-15', 'Oriental Mindoro', 'Single', NULL, 3, '09170000015', 'seed.farmer15@example.com', 'Female', NULL, '[]', '[\"Adult\"]', NULL, 279, '2026-06-25 13:52:29', 0),
(58, 'NFAFWSP-2606-0000038', 'SEED-2026-016', 'Pedro', 'Seed', 'Hernandez', 'Zamboanga Del Sur, Region IX', '1991-04-16', 'Zamboanga Del Sur', 'Married', 'Seed Spouse 16', 4, '09170000016', 'seed.farmer16@example.com', 'Male', NULL, '[]', '[\"Adult\"]', NULL, 299, '2026-06-25 13:52:29', 0),
(59, 'NFAFWSP-2606-0000039', 'SEED-2026-017', 'Queenie', 'Seed', 'Ilagan', 'Zamboanga Del Sur, Region IX', '1992-05-17', 'Zamboanga Del Sur', 'Married', 'Seed Spouse 17', 5, '09170000017', 'seed.farmer17@example.com', 'Female', NULL, '[]', '[\"Adult\"]', NULL, 319, '2026-06-25 13:52:29', 0),
(60, 'NFAFWSP-2606-0000040', 'SEED-2026-018', 'Ramon', 'Seed', 'Jimenez', 'Albay, Region V', '1993-06-18', 'Albay', 'Single', NULL, 0, '09170000018', 'seed.farmer18@example.com', 'Male', NULL, '[]', '[\"Adult\"]', NULL, 339, '2026-06-25 13:52:29', 0),
(61, 'NFAFWSP-2606-0000041', 'SEED-2026-019', 'Sofia', 'Seed', 'Lazaro', 'Masbate, Region V', '1994-07-19', 'Masbate', 'Married', 'Seed Spouse 19', 1, '09170000019', 'seed.farmer19@example.com', 'Female', NULL, '[]', '[\"Adult\"]', NULL, 359, '2026-06-25 13:52:29', 0),
(62, 'NFAFWSP-2606-0000042', 'SEED-2026-020', 'Tomas', 'Seed', 'Morales', 'Antique, Region VI', '1995-08-20', 'Antique', 'Married', 'Seed Spouse 20', 2, '09170000020', 'seed.farmer20@example.com', 'Male', NULL, '[]', '[\"Adult\"]', NULL, 378, '2026-06-25 13:52:29', 0),
(63, 'NFAFWSP-2606-0000043', 'SEED-2026-021', 'Ursula', 'Seed', 'Navarro', 'Iloilo, Region VI', '1996-09-21', 'Iloilo', 'Single', NULL, 3, '09170000021', 'seed.farmer21@example.com', 'Female', NULL, '[]', '[\"Adult\"]', 11, 398, '2026-06-25 13:52:29', 0),
(64, 'NFAFWSP-2606-0000044', 'SEED-2026-022', 'Victor', 'Seed', 'Ocampo', 'Cebu, Region VII', '1975-10-22', 'Cebu', 'Married', 'Seed Spouse 22', 4, '09170000022', 'seed.farmer22@example.com', 'Male', NULL, '[]', '[\"Adult\"]', 11, 418, '2026-06-25 13:52:29', 0),
(65, 'NFAFWSP-2606-0000045', 'SEED-2026-023', 'Wena', 'Seed', 'Perez', 'Leyte, Region VIII', '1976-11-23', 'Leyte', 'Married', 'Seed Spouse 23', 5, '09170000023', 'seed.farmer23@example.com', 'Female', NULL, '[]', '[\"Adult\"]', 11, 438, '2026-06-25 13:52:29', 0),
(66, 'NFAFWSP-2606-0000046', 'SEED-2026-024', 'Xander', 'Seed', 'Quinto', 'Samar, Region VIII', '1977-12-24', 'Samar', 'Single', NULL, 0, '09170000024', 'seed.farmer24@example.com', 'Male', NULL, '[]', '[\"Adult\"]', 11, 458, '2026-06-25 13:52:29', 0),
(67, 'NFAFWSP-2606-0000047', 'SEED-2026-025', 'Yolanda', 'Seed', 'Rivera', 'Bukidnon, Region X', '1978-01-25', 'Bukidnon', 'Married', 'Seed Spouse 25', 1, '09170000025', 'seed.farmer25@example.com', 'Female', NULL, '[]', '[\"Adult\"]', 11, 478, '2026-06-25 13:52:29', 0),
(68, 'NFAFWSP-2606-0000048', 'SEED-2026-026', 'Zandro', 'Seed', 'Salazar', 'Misamis Occidental, Region X', '1979-02-26', 'Misamis Occidental', 'Married', 'Seed Spouse 26', 2, '09170000026', 'seed.farmer26@example.com', 'Male', NULL, '[]', '[\"Adult\"]', 11, 497, '2026-06-25 13:52:29', 0),
(69, 'NFAFWSP-2606-0000049', 'SEED-2026-027', 'Ana', 'Seed', 'Tolentino', 'Davao Del Norte, Region XI', '1980-03-01', 'Davao Del Norte', 'Single', NULL, 3, '09170000027', 'seed.farmer27@example.com', 'Female', NULL, '[]', '[\"Adult\"]', 11, 517, '2026-06-25 13:52:29', 0),
(70, 'NFAFWSP-2606-0000050', 'SEED-2026-028', 'Berto', 'Seed', 'Uy', 'Davao Del Sur, Region XI', '1981-04-02', 'Davao Del Sur', 'Married', 'Seed Spouse 28', 4, '09170000028', 'seed.farmer28@example.com', 'Male', NULL, '[]', '[\"Adult\"]', 11, 537, '2026-06-25 13:52:29', 0),
(71, 'NFAFWSP-2606-0000051', 'SEED-2026-029', 'Celia', 'Seed', 'Villanueva', 'North Cotabato, Region XII', '1982-05-03', 'North Cotabato', 'Married', 'Seed Spouse 29', 5, '09170000029', 'seed.farmer29@example.com', 'Female', NULL, '[]', '[\"Adult\"]', 11, 557, '2026-06-25 13:52:29', 0),
(72, 'NFAFWSP-2606-0000052', 'SEED-2026-030', 'Diego', 'Seed', 'Zamora', 'Sultan Kudarat, Region XII', '1983-06-04', 'Sultan Kudarat', 'Single', NULL, 0, '09170000030', 'seed.farmer30@example.com', 'Male', NULL, '[]', '[\"Adult\"]', 11, 577, '2026-06-25 13:52:29', 0),
(73, 'NFAFWSP-2606-0000053', 'WM000333-2026-001', 'Althea', 'Abalos', 'Agbayani', 'Asingan FLGC Service Area, Eastern Pangasinan, Region I', '1968-01-01', 'Eastern Pangasinan', 'Single', NULL, 0, '09183330001', 'wm000333.farmer001@fwsp.local', 'Female', NULL, '[\"N\\/A\"]', '[\"Adult\"]', 12, 50, '2026-06-25 13:52:45', 0),
(74, 'NFAFWSP-2606-0000054', 'WM000333-2026-002', 'Benedicto', 'Baltazar', 'Basa', 'Asingan FLGC Service Area, Eastern Pangasinan, Region I', '1969-02-02', 'Eastern Pangasinan', 'Married', 'Seeded Spouse 2', 1, '09183330002', 'wm000333.farmer002@fwsp.local', 'Male', NULL, '[\"Lesbian\"]', '[\"Youth\"]', 13, 50, '2026-06-25 13:52:45', 0),
(75, 'NFAFWSP-2606-0000055', 'WM000333-2026-003', 'Carmela', 'Cayetano', 'Cabal', 'Asingan FLGC Service Area, Eastern Pangasinan, Region I', '1970-03-03', 'Eastern Pangasinan', 'Widowed', NULL, 2, '09183330003', 'wm000333.farmer003@fwsp.local', 'Female', NULL, '[\"Gay\"]', '[\"Muslim\"]', 14, 50, '2026-06-25 13:52:45', 0),
(76, 'NFAFWSP-2606-0000056', 'WM000333-2026-004', 'Delfin', 'De Vera', 'Domingo', 'Asingan FLGC Service Area, Eastern Pangasinan, Region I', '1971-04-04', 'Eastern Pangasinan', 'Separated', NULL, 3, '09183330004', 'wm000333.farmer004@fwsp.local', 'Male', NULL, '[\"Bisexual\"]', '[\"Persons with Disability\"]', 12, 50, '2026-06-25 13:52:45', 0),
(77, 'NFAFWSP-2606-0000057', 'WM000333-2026-005', 'Emelita', 'Escobar', 'Estrella', 'Asingan FLGC Service Area, Eastern Pangasinan, Region I', '1972-05-05', 'Eastern Pangasinan', 'Single', NULL, 4, '09183330005', 'wm000333.farmer005@fwsp.local', 'Female', NULL, '[\"Transgender\"]', '[\"Indigenous People\"]', 13, 50, '2026-06-25 13:52:45', 0),
(78, 'NFAFWSP-2606-0000058', 'WM000333-2026-006', 'Florencio', 'Ferrer', 'Ferrer', 'Asingan FLGC Service Area, Eastern Pangasinan, Region I', '1973-06-06', 'Eastern Pangasinan', 'Married', 'Seeded Spouse 6', 5, '09183330006', 'wm000333.farmer006@fwsp.local', 'Male', NULL, '[\"N\\/A\"]', '[\"Senior Citizen\"]', 14, 50, '2026-06-25 13:52:45', 0),
(79, 'NFAFWSP-2606-0000059', 'WM000333-2026-007', 'Ginalyn', 'Guevarra', 'Galvez', 'Asingan FLGC Service Area, Eastern Pangasinan, Region I', '1974-07-07', 'Eastern Pangasinan', 'Widowed', NULL, 6, '09183330007', 'wm000333.farmer007@fwsp.local', 'Female', NULL, '[\"N\\/A\"]', '[\"Adult\",\"Muslim\"]', 12, 50, '2026-06-25 13:52:45', 0),
(80, 'NFAFWSP-2606-0000060', 'WM000333-2026-008', 'Herminio', 'Hizon', 'Hidalgo', 'Asingan FLGC Service Area, Eastern Pangasinan, Region I', '1975-08-08', 'Eastern Pangasinan', 'Separated', NULL, 0, '09183330008', 'wm000333.farmer008@fwsp.local', 'Male', NULL, '[\"Lesbian\"]', '[\"Adult\",\"Indigenous People\"]', 13, 50, '2026-06-25 13:52:45', 0),
(82, 'NFAFWSP-2606-0000061', 'WM000333-2026-009', 'Irene', 'Ilustre', 'Ibarra', 'Asingan FLGC Service Area, Eastern Pangasinan, Region I', '1976-09-09', 'Eastern Pangasinan', 'Single', NULL, 1, '09183330009', 'wm000333.farmer009@fwsp.local', 'Female', NULL, '[\"Gay\"]', '[\"Adult\"]', 14, 50, '2026-06-25 13:52:45', 0),
(84, 'NFAFWSP-2606-0000062', 'WM000333-2026-010', 'Joselito', 'Javier', 'Jacinto', 'Asingan FLGC Service Area, Eastern Pangasinan, Region I', '1977-10-10', 'Eastern Pangasinan', 'Married', 'Seeded Spouse 10', 2, '09183330010', 'wm000333.farmer010@fwsp.local', 'Male', NULL, '[\"Bisexual\"]', '[\"Youth\"]', 12, 50, '2026-06-25 13:52:45', 0),
(86, 'NFAFWSP-2606-0000063', 'WM000333-2026-011', 'Lorna', 'Luna', 'Limgenco', 'Asingan FLGC Service Area, Eastern Pangasinan, Region I', '1978-11-11', 'Eastern Pangasinan', 'Widowed', NULL, 3, '09183330011', 'wm000333.farmer011@fwsp.local', 'Female', NULL, '[\"Transgender\"]', '[\"Muslim\"]', 13, 50, '2026-06-25 13:52:45', 0),
(88, 'NFAFWSP-2606-0000064', 'WM000333-2026-012', 'Marvin', 'Manalo', 'Magsino', 'Asingan FLGC Service Area, Eastern Pangasinan, Region I', '1979-12-12', 'Eastern Pangasinan', 'Separated', NULL, 4, '09183330012', 'wm000333.farmer012@fwsp.local', 'Male', NULL, '[\"N\\/A\"]', '[\"Persons with Disability\"]', 14, 50, '2026-06-25 13:52:45', 0),
(90, 'NFAFWSP-2606-0000065', 'WM000333-2026-013', 'Nelia', 'Nieto', 'Natividad', 'Asingan FLGC Service Area, Eastern Pangasinan, Region I', '1980-01-13', 'Eastern Pangasinan', 'Single', NULL, 5, '09183330013', 'wm000333.farmer013@fwsp.local', 'Female', NULL, '[\"N\\/A\"]', '[\"Indigenous People\"]', 12, 50, '2026-06-25 13:52:45', 0),
(91, 'NFAFWSP-2606-0000066', 'WM000333-2026-014', 'Orlando', 'Ortega', 'Ordona', 'Asingan FLGC Service Area, Eastern Pangasinan, Region I', '1981-02-14', 'Eastern Pangasinan', 'Married', 'Seeded Spouse 14', 6, '09183330014', 'wm000333.farmer014@fwsp.local', 'Male', NULL, '[\"Lesbian\"]', '[\"Senior Citizen\"]', 13, 50, '2026-06-25 13:52:45', 0),
(92, 'NFAFWSP-2606-0000067', 'WM000333-2026-015', 'Perla', 'Pineda', 'Padilla', 'Asingan FLGC Service Area, Eastern Pangasinan, Region I', '1982-03-15', 'Eastern Pangasinan', 'Widowed', NULL, 0, '09183330015', 'wm000333.farmer015@fwsp.local', 'Female', NULL, '[\"Gay\"]', '[\"Adult\",\"Muslim\"]', 14, 50, '2026-06-25 13:52:45', 0),
(93, 'NFAFWSP-2606-0000068', 'WM000333-2026-016', 'Quirino', 'Quiambao', 'Quinto', 'Asingan FLGC Service Area, Eastern Pangasinan, Region I', '1983-04-16', 'Eastern Pangasinan', 'Separated', NULL, 1, '09183330016', 'wm000333.farmer016@fwsp.local', 'Male', NULL, '[\"Bisexual\"]', '[\"Adult\",\"Indigenous People\"]', 12, 50, '2026-06-25 13:52:45', 0),
(94, 'NFAFWSP-2606-0000069', 'WM000333-2026-017', 'Rowena', 'Robles', 'Roldan', 'Asingan FLGC Service Area, Eastern Pangasinan, Region I', '1984-05-17', 'Eastern Pangasinan', 'Single', NULL, 2, '09183330017', 'wm000333.farmer017@fwsp.local', 'Female', NULL, '[\"Transgender\"]', '[\"Adult\"]', 13, 50, '2026-06-25 13:52:45', 0),
(95, 'NFAFWSP-2606-0000070', 'WM000333-2026-018', 'Severino', 'Serrano', 'Soriano', 'Asingan FLGC Service Area, Eastern Pangasinan, Region I', '1985-06-18', 'Eastern Pangasinan', 'Married', 'Seeded Spouse 18', 3, '09183330018', 'wm000333.farmer018@fwsp.local', 'Male', NULL, '[\"N\\/A\"]', '[\"Youth\"]', 14, 50, '2026-06-25 13:52:45', 0),
(96, 'NFAFWSP-2606-0000071', 'WM000333-2026-019', 'Talia', 'Tiongson', 'Tamayo', 'Asingan FLGC Service Area, Eastern Pangasinan, Region I', '1986-07-19', 'Eastern Pangasinan', 'Widowed', NULL, 4, '09183330019', 'wm000333.farmer019@fwsp.local', 'Female', NULL, '[\"N\\/A\"]', '[\"Muslim\"]', 12, 50, '2026-06-25 13:52:45', 0),
(97, 'NFAFWSP-2606-0000072', 'WM000333-2026-020', 'Urbano', 'Umali', 'Urbina', 'Asingan FLGC Service Area, Eastern Pangasinan, Region I', '1987-08-20', 'Eastern Pangasinan', 'Separated', NULL, 5, '09183330020', 'wm000333.farmer020@fwsp.local', 'Male', NULL, '[\"Lesbian\"]', '[\"Persons with Disability\"]', 13, 50, '2026-06-25 13:52:45', 0),
(98, 'NFAFWSP-2606-0000073', 'WM000333-2026-021', 'Virgilia', 'Valerio', 'Valdez', 'Asingan FLGC Service Area, Eastern Pangasinan, Region I', '1988-09-21', 'Eastern Pangasinan', 'Single', NULL, 6, '09183330021', 'wm000333.farmer021@fwsp.local', 'Female', NULL, '[\"Gay\"]', '[\"Indigenous People\"]', 14, 50, '2026-06-25 13:52:45', 0),
(99, 'NFAFWSP-2606-0000074', 'WM000333-2026-022', 'Winston', 'Yabut', 'Yap', 'Asingan FLGC Service Area, Eastern Pangasinan, Region I', '1989-10-22', 'Eastern Pangasinan', 'Married', 'Seeded Spouse 22', 0, '09183330022', 'wm000333.farmer022@fwsp.local', 'Male', NULL, '[\"Bisexual\"]', '[\"Senior Citizen\"]', 12, 50, '2026-06-25 13:52:45', 0),
(100, 'NFAFWSP-2606-0000075', 'WM000333-2026-023', 'Yasmin', 'Zaragoza', 'Zamora', 'Asingan FLGC Service Area, Eastern Pangasinan, Region I', '1990-11-23', 'Eastern Pangasinan', 'Widowed', NULL, 1, '09183330023', 'wm000333.farmer023@fwsp.local', 'Female', NULL, '[\"Transgender\"]', '[\"Adult\",\"Muslim\"]', 13, 50, '2026-06-25 13:52:45', 0),
(101, 'NFAFWSP-2606-0000076', 'WM000333-2026-024', 'Zenaida', 'Alcantara', 'Alvarez', 'Asingan FLGC Service Area, Eastern Pangasinan, Region I', '1991-12-24', 'Eastern Pangasinan', 'Separated', NULL, 2, '09183330024', 'wm000333.farmer024@fwsp.local', 'Male', NULL, '[\"N\\/A\"]', '[\"Adult\",\"Indigenous People\"]', 14, 50, '2026-06-25 13:52:45', 0),
(102, 'NFAFWSP-2606-0000077', 'WM000333-2026-025', 'Arnel', 'Buenaventura', 'Bermudez', 'Asingan FLGC Service Area, Eastern Pangasinan, Region I', '1992-01-25', 'Eastern Pangasinan', 'Single', NULL, 3, '09183330025', 'wm000333.farmer025@fwsp.local', 'Female', NULL, '[\"N\\/A\"]', '[\"Adult\"]', 12, 50, '2026-06-25 13:52:45', 0),
(103, 'NFAFWSP-2606-0000078', 'WM000333-2026-026', 'Bernadette', 'Corpus', 'Cordero', 'Asingan FLGC Service Area, Eastern Pangasinan, Region I', '1993-02-26', 'Eastern Pangasinan', 'Married', 'Seeded Spouse 26', 4, '09183330026', 'wm000333.farmer026@fwsp.local', 'Male', NULL, '[\"Lesbian\"]', '[\"Youth\"]', 13, 50, '2026-06-25 13:52:45', 0),
(104, 'NFAFWSP-2606-0000079', 'WM000333-2026-027', 'Cristina', 'Dimaculangan', 'Dizon', 'Asingan FLGC Service Area, Eastern Pangasinan, Region I', '1994-03-27', 'Eastern Pangasinan', 'Widowed', NULL, 5, '09183330027', 'wm000333.farmer027@fwsp.local', 'Female', NULL, '[\"Gay\"]', '[\"Muslim\"]', 14, 50, '2026-06-25 13:52:45', 0),
(105, 'NFAFWSP-2606-0000080', 'WM000333-2026-028', 'Dominador', 'Evangelista', 'Espiritu', 'Asingan FLGC Service Area, Eastern Pangasinan, Region I', '1995-04-01', 'Eastern Pangasinan', 'Separated', NULL, 6, '09183330028', 'wm000333.farmer028@fwsp.local', 'Male', NULL, '[\"Bisexual\"]', '[\"Persons with Disability\"]', 12, 50, '2026-06-25 13:52:45', 0),
(106, 'NFAFWSP-2606-0000081', 'WM000333-2026-029', 'Evangeline', 'Francisco', 'Fajardo', 'Asingan FLGC Service Area, Eastern Pangasinan, Region I', '1996-05-02', 'Eastern Pangasinan', 'Single', NULL, 0, '09183330029', 'wm000333.farmer029@fwsp.local', 'Female', NULL, '[\"Transgender\"]', '[\"Indigenous People\"]', 13, 50, '2026-06-25 13:52:45', 0),
(108, 'NFAFWSP-2606-0000082', 'WM000333-2026-030', 'Federico', 'Gatchalian', 'Garcia', 'Asingan FLGC Service Area, Eastern Pangasinan, Region I', '1997-06-03', 'Eastern Pangasinan', 'Married', 'Seeded Spouse 30', 1, '09183330030', 'wm000333.farmer030@fwsp.local', 'Male', NULL, '[\"N\\/A\"]', '[\"Senior Citizen\"]', 14, 50, '2026-06-25 13:52:45', 0),
(123, 'NFAFWSP-2606-0000083', 'WM000222-2026-001', 'Adela', 'Aquino', 'Dela Cruz', 'Agricom, Batch Recirculating Dryer Service Area, Eastern Pangasinan, Region I', '1965-01-01', 'Eastern Pangasinan', 'Single', NULL, 0, '09182220001', 'wm000222.farmer001@fwsp.local', 'Female', NULL, '[\"N\\/A\"]', '[\"Adult\"]', NULL, 49, '2026-06-25 13:52:52', 0),
(124, 'NFAFWSP-2606-0000084', 'WM000222-2026-002', 'Benito', 'Bautista', 'Santos', 'Agricom, Batch Recirculating Dryer Service Area, Eastern Pangasinan, Region I', '1966-02-02', 'Eastern Pangasinan', 'Married', 'Seeded Spouse 2', 1, '09182220002', 'wm000222.farmer002@fwsp.local', 'Male', NULL, '[\"N\\/A\"]', '[\"Youth\"]', NULL, 49, '2026-06-25 13:52:52', 0),
(125, 'NFAFWSP-2606-0000085', 'WM000222-2026-003', 'Carina', 'Castro', 'Reyes', 'Agricom, Batch Recirculating Dryer Service Area, Eastern Pangasinan, Region I', '1967-03-03', 'Eastern Pangasinan', 'Widowed', NULL, 2, '09182220003', 'wm000222.farmer003@fwsp.local', 'Female', NULL, '[\"N\\/A\"]', '[\"Senior Citizen\"]', NULL, 49, '2026-06-25 13:52:52', 0),
(126, 'NFAFWSP-2606-0000086', 'WM000222-2026-004', 'Danilo', 'Diaz', 'Garcia', 'Agricom, Batch Recirculating Dryer Service Area, Eastern Pangasinan, Region I', '1968-04-04', 'Eastern Pangasinan', 'Separated', NULL, 3, '09182220004', 'wm000222.farmer004@fwsp.local', 'Male', NULL, '[\"N\\/A\"]', '[\"Muslim\"]', NULL, 49, '2026-06-25 13:52:52', 0),
(127, 'NFAFWSP-2606-0000087', 'WM000222-2026-005', 'Elena', 'Enriquez', 'Ramos', 'Agricom, Batch Recirculating Dryer Service Area, Eastern Pangasinan, Region I', '1969-05-05', 'Eastern Pangasinan', 'Single', NULL, 4, '09182220005', 'wm000222.farmer005@fwsp.local', 'Female', NULL, '[\"N\\/A\"]', '[\"Persons with Disability\"]', NULL, 49, '2026-06-25 13:52:52', 0),
(128, 'NFAFWSP-2606-0000088', 'WM000222-2026-006', 'Felix', 'Flores', 'Mendoza', 'Agricom, Batch Recirculating Dryer Service Area, Eastern Pangasinan, Region I', '1970-06-06', 'Eastern Pangasinan', 'Married', 'Seeded Spouse 6', 5, '09182220006', 'wm000222.farmer006@fwsp.local', 'Male', NULL, '[\"N\\/A\"]', '[\"Indigenous People\"]', NULL, 49, '2026-06-25 13:52:52', 0),
(129, 'NFAFWSP-2606-0000089', 'WM000222-2026-007', 'Gemma', 'Gomez', 'Torres', 'Agricom, Batch Recirculating Dryer Service Area, Eastern Pangasinan, Region I', '1971-07-07', 'Eastern Pangasinan', 'Widowed', NULL, 0, '09182220007', 'wm000222.farmer007@fwsp.local', 'Female', NULL, '[\"N\\/A\"]', '[\"Adult\",\"Muslim\"]', NULL, 49, '2026-06-25 13:52:52', 0),
(130, 'NFAFWSP-2606-0000090', 'WM000222-2026-008', 'Hector', 'Hernandez', 'Flores', 'Agricom, Batch Recirculating Dryer Service Area, Eastern Pangasinan, Region I', '1972-08-08', 'Eastern Pangasinan', 'Separated', NULL, 1, '09182220008', 'wm000222.farmer008@fwsp.local', 'Male', NULL, '[\"N\\/A\"]', '[\"Adult\",\"Indigenous People\"]', NULL, 49, '2026-06-25 13:52:52', 0),
(131, 'NFAFWSP-2606-0000091', 'WM000222-2026-009', 'Imelda', 'Ignacio', 'Rivera', 'Agricom, Batch Recirculating Dryer Service Area, Eastern Pangasinan, Region I', '1973-09-09', 'Eastern Pangasinan', 'Single', NULL, 2, '09182220009', 'wm000222.farmer009@fwsp.local', 'Female', NULL, '[\"N\\/A\"]', '[\"Adult\"]', NULL, 49, '2026-06-25 13:52:52', 0),
(132, 'NFAFWSP-2606-0000092', 'WM000222-2026-010', 'Jonas', 'Jimenez', 'Gonzales', 'Agricom, Batch Recirculating Dryer Service Area, Eastern Pangasinan, Region I', '1974-10-10', 'Eastern Pangasinan', 'Married', 'Seeded Spouse 10', 3, '09182220010', 'wm000222.farmer010@fwsp.local', 'Male', NULL, '[\"N\\/A\"]', '[\"Youth\"]', NULL, 49, '2026-06-25 13:52:52', 0),
(133, 'NFAFWSP-2606-0000093', 'WM000222-2026-011', 'Katrina', 'Lazaro', 'Bautista', 'Agricom, Batch Recirculating Dryer Service Area, Eastern Pangasinan, Region I', '1975-11-11', 'Eastern Pangasinan', 'Widowed', NULL, 4, '09182220011', 'wm000222.farmer011@fwsp.local', 'Female', NULL, '[\"N\\/A\"]', '[\"Senior Citizen\"]', NULL, 49, '2026-06-25 13:52:52', 0),
(134, 'NFAFWSP-2606-0000094', 'WM000222-2026-012', 'Leonardo', 'Mendoza', 'Villanueva', 'Agricom, Batch Recirculating Dryer Service Area, Eastern Pangasinan, Region I', '1976-12-12', 'Eastern Pangasinan', 'Separated', NULL, 5, '09182220012', 'wm000222.farmer012@fwsp.local', 'Male', NULL, '[\"N\\/A\"]', '[\"Muslim\"]', NULL, 49, '2026-06-25 13:52:52', 0),
(135, 'NFAFWSP-2606-0000095', 'WM000222-2026-013', 'Maribel', 'Navarro', 'Fernandez', 'Agricom, Batch Recirculating Dryer Service Area, Eastern Pangasinan, Region I', '1977-01-13', 'Eastern Pangasinan', 'Single', NULL, 0, '09182220013', 'wm000222.farmer013@fwsp.local', 'Female', NULL, '[\"N\\/A\"]', '[\"Persons with Disability\"]', NULL, 49, '2026-06-25 13:52:52', 0),
(136, 'NFAFWSP-2606-0000096', 'WM000222-2026-014', 'Nestor', 'Ocampo', 'Castillo', 'Agricom, Batch Recirculating Dryer Service Area, Eastern Pangasinan, Region I', '1978-02-14', 'Eastern Pangasinan', 'Married', 'Seeded Spouse 14', 1, '09182220014', 'wm000222.farmer014@fwsp.local', 'Male', NULL, '[\"N\\/A\"]', '[\"Indigenous People\"]', NULL, 49, '2026-06-25 13:52:52', 0),
(137, 'NFAFWSP-2606-0000097', 'WM000222-2026-015', 'Ofelia', 'Pascual', 'Aquino', 'Agricom, Batch Recirculating Dryer Service Area, Eastern Pangasinan, Region I', '1979-03-15', 'Eastern Pangasinan', 'Widowed', NULL, 2, '09182220015', 'wm000222.farmer015@fwsp.local', 'Female', NULL, '[\"N\\/A\"]', '[\"Adult\",\"Muslim\"]', NULL, 49, '2026-06-25 13:52:52', 0),
(138, 'NFAFWSP-2606-0000098', 'WM000222-2026-016', 'Paolo', 'Quinto', 'Morales', 'Agricom, Batch Recirculating Dryer Service Area, Eastern Pangasinan, Region I', '1980-04-16', 'Eastern Pangasinan', 'Separated', NULL, 3, '09182220016', 'wm000222.farmer016@fwsp.local', 'Male', NULL, '[\"N\\/A\"]', '[\"Adult\",\"Indigenous People\"]', NULL, 49, '2026-06-25 13:52:52', 0),
(139, 'NFAFWSP-2606-0000099', 'WM000222-2026-017', 'Querubin', 'Rivera', 'Navarro', 'Agricom, Batch Recirculating Dryer Service Area, Eastern Pangasinan, Region I', '1981-05-17', 'Eastern Pangasinan', 'Single', NULL, 4, '09182220017', 'wm000222.farmer017@fwsp.local', 'Female', NULL, '[\"N\\/A\"]', '[\"Adult\"]', NULL, 49, '2026-06-25 13:52:52', 0),
(140, 'NFAFWSP-2606-0000100', 'WM000222-2026-018', 'Rosalie', 'Salazar', 'Domingo', 'Agricom, Batch Recirculating Dryer Service Area, Eastern Pangasinan, Region I', '1982-06-18', 'Eastern Pangasinan', 'Married', 'Seeded Spouse 18', 5, '09182220018', 'wm000222.farmer018@fwsp.local', 'Male', NULL, '[\"N\\/A\"]', '[\"Youth\"]', NULL, 49, '2026-06-25 13:52:52', 0),
(141, 'NFAFWSP-2606-0000101', 'WM000222-2026-019', 'Samuel', 'Torres', 'Pascual', 'Agricom, Batch Recirculating Dryer Service Area, Eastern Pangasinan, Region I', '1983-07-19', 'Eastern Pangasinan', 'Widowed', NULL, 0, '09182220019', 'wm000222.farmer019@fwsp.local', 'Female', NULL, '[\"N\\/A\"]', '[\"Senior Citizen\"]', NULL, 49, '2026-06-25 13:52:52', 0),
(142, 'NFAFWSP-2606-0000102', 'WM000222-2026-020', 'Teresita', 'Valdez', 'Valdez', 'Agricom, Batch Recirculating Dryer Service Area, Eastern Pangasinan, Region I', '1984-08-20', 'Eastern Pangasinan', 'Separated', NULL, 1, '09182220020', 'wm000222.farmer020@fwsp.local', 'Male', NULL, '[\"N\\/A\"]', '[\"Muslim\"]', NULL, 49, '2026-06-25 13:52:52', 0),
(143, 'NFAFWSP-2606-0000103', 'WM000222-2026-021', 'Ulysses', 'Villanueva', 'Cabrera', 'Agricom, Batch Recirculating Dryer Service Area, Eastern Pangasinan, Region I', '1985-09-21', 'Eastern Pangasinan', 'Single', NULL, 2, '09182220021', 'wm000222.farmer021@fwsp.local', 'Female', NULL, '[\"N\\/A\"]', '[\"Persons with Disability\"]', NULL, 49, '2026-06-25 13:52:52', 0),
(144, 'NFAFWSP-2606-0000104', 'WM000222-2026-022', 'Veronica', 'Zamora', 'Salvador', 'Agricom, Batch Recirculating Dryer Service Area, Eastern Pangasinan, Region I', '1986-10-22', 'Eastern Pangasinan', 'Married', 'Seeded Spouse 22', 3, '09182220022', 'wm000222.farmer022@fwsp.local', 'Male', NULL, '[\"N\\/A\"]', '[\"Indigenous People\"]', NULL, 49, '2026-06-25 13:52:52', 0),
(145, 'NFAFWSP-2606-0000105', 'WM000222-2026-023', 'Wilfredo', 'Abad', 'Aguilar', 'Agricom, Batch Recirculating Dryer Service Area, Eastern Pangasinan, Region I', '1987-11-23', 'Eastern Pangasinan', 'Widowed', NULL, 4, '09182220023', 'wm000222.farmer023@fwsp.local', 'Female', NULL, '[\"N\\/A\"]', '[\"Adult\",\"Muslim\"]', NULL, 49, '2026-06-25 13:52:52', 0),
(146, 'NFAFWSP-2606-0000106', 'WM000222-2026-024', 'Xandra', 'Bernal', 'Marquez', 'Agricom, Batch Recirculating Dryer Service Area, Eastern Pangasinan, Region I', '1988-12-24', 'Eastern Pangasinan', 'Separated', NULL, 5, '09182220024', 'wm000222.farmer024@fwsp.local', 'Male', NULL, '[\"N\\/A\"]', '[\"Adult\",\"Indigenous People\"]', NULL, 49, '2026-06-25 13:52:52', 0),
(147, 'NFAFWSP-2606-0000107', 'WM000222-2026-025', 'Yolanda', 'Cruz', 'Santiago', 'Agricom, Batch Recirculating Dryer Service Area, Eastern Pangasinan, Region I', '1989-01-25', 'Eastern Pangasinan', 'Single', NULL, 0, '09182220025', 'wm000222.farmer025@fwsp.local', 'Female', NULL, '[\"N\\/A\"]', '[\"Adult\"]', NULL, 49, '2026-06-25 13:52:52', 0),
(148, 'NFAFWSP-2606-0000108', 'WM000222-2026-026', 'Zandro', 'Dizon', 'Mercado', 'Agricom, Batch Recirculating Dryer Service Area, Eastern Pangasinan, Region I', '1990-02-26', 'Eastern Pangasinan', 'Married', 'Seeded Spouse 26', 1, '09182220026', 'wm000222.farmer026@fwsp.local', 'Male', NULL, '[\"N\\/A\"]', '[\"Youth\"]', NULL, 49, '2026-06-25 13:52:52', 0),
(149, 'NFAFWSP-2606-0000109', 'WM000222-2026-027', 'Amparo', 'Escoto', 'Rosales', 'Agricom, Batch Recirculating Dryer Service Area, Eastern Pangasinan, Region I', '1991-03-27', 'Eastern Pangasinan', 'Widowed', NULL, 2, '09182220027', 'wm000222.farmer027@fwsp.local', 'Female', NULL, '[\"N\\/A\"]', '[\"Senior Citizen\"]', NULL, 49, '2026-06-25 13:52:52', 0),
(150, 'NFAFWSP-2606-0000110', 'WM000222-2026-028', 'Brando', 'Fajardo', 'Tolentino', 'Agricom, Batch Recirculating Dryer Service Area, Eastern Pangasinan, Region I', '1992-04-01', 'Eastern Pangasinan', 'Separated', NULL, 3, '09182220028', 'wm000222.farmer028@fwsp.local', 'Male', NULL, '[\"N\\/A\"]', '[\"Muslim\"]', NULL, 49, '2026-06-25 13:52:52', 0),
(151, 'NFAFWSP-2606-0000111', 'WM000222-2026-029', 'Clarissa', 'Galang', 'Soriano', 'Agricom, Batch Recirculating Dryer Service Area, Eastern Pangasinan, Region I', '1993-05-02', 'Eastern Pangasinan', 'Single', NULL, 4, '09182220029', 'wm000222.farmer029@fwsp.local', 'Female', NULL, '[\"N\\/A\"]', '[\"Persons with Disability\"]', NULL, 49, '2026-06-25 13:52:52', 0),
(152, 'NFAFWSP-2606-0000112', 'WM000222-2026-030', 'Domingo', 'Hilario', 'Velasco', 'Agricom, Batch Recirculating Dryer Service Area, Eastern Pangasinan, Region I', '1994-06-03', 'Eastern Pangasinan', 'Married', 'Seeded Spouse 30', 5, '09182220030', 'wm000222.farmer030@fwsp.local', 'Male', NULL, '[\"N\\/A\"]', '[\"Indigenous People\"]', NULL, 49, '2026-06-25 13:52:52', 0);

-- --------------------------------------------------------

--
-- Table structure for table `farmer_key_sequences`
--

DROP TABLE IF EXISTS `farmer_key_sequences`;
CREATE TABLE `farmer_key_sequences` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `farmer_key_sequences`
--

INSERT INTO `farmer_key_sequences` (`id`, `created_at`) VALUES
(1, '2026-06-28 12:15:24'),
(2, '2026-06-28 12:15:24'),
(3, '2026-06-28 12:15:24'),
(4, '2026-06-28 12:15:24'),
(5, '2026-06-28 12:15:24'),
(6, '2026-06-28 12:15:24'),
(7, '2026-06-28 12:15:24'),
(8, '2026-06-28 12:15:24'),
(9, '2026-06-28 12:15:24'),
(10, '2026-06-28 12:15:24'),
(11, '2026-06-28 12:15:24'),
(12, '2026-06-28 12:15:24'),
(13, '2026-06-28 12:15:24'),
(14, '2026-06-28 12:15:24'),
(15, '2026-06-28 12:15:24'),
(16, '2026-06-28 12:15:24'),
(17, '2026-06-28 12:15:24'),
(18, '2026-06-28 12:15:24'),
(19, '2026-06-28 12:15:24'),
(20, '2026-06-28 12:15:24'),
(21, '2026-06-28 12:15:24'),
(22, '2026-06-28 12:15:24'),
(23, '2026-06-28 12:15:24'),
(24, '2026-06-28 12:15:24'),
(25, '2026-06-28 12:15:24'),
(26, '2026-06-28 12:15:24'),
(27, '2026-06-28 12:15:24'),
(28, '2026-06-28 12:15:24'),
(29, '2026-06-28 12:15:24'),
(30, '2026-06-28 12:15:24'),
(31, '2026-06-28 12:15:24'),
(32, '2026-06-28 12:15:24'),
(33, '2026-06-28 12:15:24'),
(34, '2026-06-28 12:15:24'),
(35, '2026-06-28 12:15:24'),
(36, '2026-06-28 12:15:24'),
(37, '2026-06-28 12:15:24'),
(38, '2026-06-28 12:15:24'),
(39, '2026-06-28 12:15:24'),
(40, '2026-06-28 12:15:24'),
(41, '2026-06-28 12:15:24'),
(42, '2026-06-28 12:15:24'),
(43, '2026-06-28 12:15:24'),
(44, '2026-06-28 12:15:24'),
(45, '2026-06-28 12:15:24'),
(46, '2026-06-28 12:15:24'),
(47, '2026-06-28 12:15:24'),
(48, '2026-06-28 12:15:24'),
(49, '2026-06-28 12:15:24'),
(50, '2026-06-28 12:15:24'),
(51, '2026-06-28 12:15:24'),
(52, '2026-06-28 12:15:24'),
(53, '2026-06-28 12:15:24'),
(54, '2026-06-28 12:15:24'),
(55, '2026-06-28 12:15:24'),
(56, '2026-06-28 12:15:24'),
(57, '2026-06-28 12:15:24'),
(58, '2026-06-28 12:15:24'),
(59, '2026-06-28 12:15:24'),
(60, '2026-06-28 12:15:24'),
(61, '2026-06-28 12:15:24'),
(62, '2026-06-28 12:15:24'),
(63, '2026-06-28 12:15:24'),
(64, '2026-06-28 12:15:24'),
(65, '2026-06-28 12:15:24'),
(66, '2026-06-28 12:15:24'),
(67, '2026-06-28 12:15:25'),
(68, '2026-06-28 12:15:25'),
(69, '2026-06-28 12:15:25'),
(70, '2026-06-28 12:15:25'),
(71, '2026-06-28 12:15:25'),
(72, '2026-06-28 12:15:25'),
(73, '2026-06-28 12:15:25'),
(74, '2026-06-28 12:15:25'),
(75, '2026-06-28 12:15:25'),
(76, '2026-06-28 12:15:25'),
(77, '2026-06-28 12:15:25'),
(78, '2026-06-28 12:15:25'),
(79, '2026-06-28 12:15:25'),
(80, '2026-06-28 12:15:25'),
(81, '2026-06-28 12:15:25'),
(82, '2026-06-28 12:15:25'),
(83, '2026-06-28 12:15:25'),
(84, '2026-06-28 12:15:25'),
(85, '2026-06-28 12:15:25'),
(86, '2026-06-28 12:15:25'),
(87, '2026-06-28 12:15:25'),
(88, '2026-06-28 12:15:25'),
(89, '2026-06-28 12:15:25'),
(90, '2026-06-28 12:15:25'),
(91, '2026-06-28 12:15:25'),
(92, '2026-06-28 12:15:25'),
(93, '2026-06-28 12:15:25'),
(94, '2026-06-28 12:15:25'),
(95, '2026-06-28 12:15:25'),
(96, '2026-06-28 12:15:25'),
(97, '2026-06-28 12:15:25'),
(98, '2026-06-28 12:15:25'),
(99, '2026-06-28 12:15:25'),
(100, '2026-06-28 12:15:25'),
(101, '2026-06-28 12:15:25'),
(102, '2026-06-28 12:15:25'),
(103, '2026-06-28 12:15:25'),
(104, '2026-06-28 12:15:25'),
(105, '2026-06-28 12:15:25'),
(106, '2026-06-28 12:15:25'),
(107, '2026-06-28 12:15:25'),
(108, '2026-06-28 12:15:25'),
(109, '2026-06-28 12:15:25'),
(110, '2026-06-28 12:15:25'),
(111, '2026-06-28 12:15:25'),
(112, '2026-06-28 12:15:25');

-- --------------------------------------------------------

--
-- Table structure for table `farmer_organizations`
--

DROP TABLE IF EXISTS `farmer_organizations`;
CREATE TABLE `farmer_organizations` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(180) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `total_members` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `office_location` varchar(255) DEFAULT NULL,
  `warehouse_id` bigint(20) UNSIGNED DEFAULT NULL,
  `is_indigenous_sector_group` tinyint(1) NOT NULL DEFAULT 0,
  `classification_type` varchar(40) NOT NULL DEFAULT 'Farmer Organization'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `farmer_organizations`
--

INSERT INTO `farmer_organizations` (`id`, `name`, `created_at`, `total_members`, `office_location`, `warehouse_id`, `is_indigenous_sector_group`, `classification_type`) VALUES
(1, 'Nueva Harvest FO', '2026-06-25 13:45:17', 5, 'San Jose, Nueva Ecija', 1, 0, 'Farmer Organization'),
(2, 'Munoz Rice Growers Association', '2026-06-25 13:45:17', 5, 'Munoz, Nueva Ecija', 1, 0, 'Farmer Organization'),
(5, 'Central Luzon Palay Producers Cooperative', '2026-06-25 13:49:51', 5, 'Cabanatuan, Nueva Ecija', 1, 0, 'Farmer Organization'),
(6, 'Golden Grain Farmers Association', '2026-06-25 13:49:51', 5, 'Talavera, Nueva Ecija', 1, 0, 'Farmer Organization'),
(11, 'Seed Palay Farmers Organization', '2026-06-25 13:52:29', 0, NULL, NULL, 0, 'Farmer Organization'),
(12, 'Eastern Pangasinan Seed Growers Association', '2026-06-25 13:52:45', 10, 'Eastern Pangasinan, Eastern Pangasinan Branch Office', 50, 0, 'Farmer Organization'),
(13, 'Asingan Palay Farmers Cooperative', '2026-06-25 13:52:45', 10, 'Asingan FLGC, Eastern Pangasinan', 50, 0, 'Farmer Organization'),
(14, 'Region I Progressive Rice Farmers Group', '2026-06-25 13:52:45', 10, 'Region I - Eastern Pangasinan', 50, 0, 'Farmer Organization'),
(19, 'Indigenous Sector Group', '2026-06-28 13:43:49', 0, '', NULL, 1, 'Indigenous People Group');

-- --------------------------------------------------------

--
-- Table structure for table `landholdings`
--

DROP TABLE IF EXISTS `landholdings`;
CREATE TABLE `landholdings` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `farmer_id` bigint(20) UNSIGNED NOT NULL,
  `classification` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`classification`)),
  `irrigated` tinyint(1) DEFAULT NULL,
  `harvest_sharing_lessor` decimal(5,2) DEFAULT NULL,
  `harvest_sharing_lessee` decimal(5,2) DEFAULT NULL,
  `palay_location` varchar(180) DEFAULT NULL,
  `harvested_area_hectares` decimal(10,2) DEFAULT NULL,
  `average_yield_per_hectare` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `landholdings`
--

INSERT INTO `landholdings` (`id`, `farmer_id`, `classification`, `irrigated`, `harvest_sharing_lessor`, `harvest_sharing_lessee`, `palay_location`, `harvested_area_hectares`, `average_yield_per_hectare`) VALUES
(1, 1, '[\"Riceland\", \"Owner-Tiller\"]', 1, NULL, NULL, 'San Jose', 2.40, 4.80),
(2, 2, '[\"Riceland\", \"CLT Holder/Recipient\"]', 0, NULL, NULL, 'Munoz', 1.70, 4.20),
(3, 43, '[\"Riceland\", \"Owner-Tiller\"]', 1, NULL, NULL, 'Basilan', 1.33, 3.75),
(4, 44, '[\"Riceland\", \"Owner-Tiller\"]', 1, NULL, NULL, 'Agusan Del Norte', 1.46, 4.00),
(5, 45, '[\"Riceland\", \"Owner-Tiller\"]', 1, NULL, NULL, 'Cavite', 1.59, 4.25),
(6, 46, '[\"Riceland\", \"Owner-Tiller\"]', 0, NULL, NULL, 'Eastern Pangasinan', 1.72, 4.50),
(7, 47, '[\"Riceland\", \"Owner-Tiller\"]', 1, NULL, NULL, 'Ilocos Sur', 1.85, 4.75),
(8, 48, '[\"Riceland\", \"Owner-Tiller\"]', 1, NULL, NULL, 'Cagayan', 1.98, 5.00),
(9, 49, '[\"Riceland\", \"Owner-Tiller\"]', 1, NULL, NULL, 'Isabela', 2.11, 3.50),
(10, 50, '[\"Riceland\", \"Owner-Tiller\"]', 0, NULL, NULL, 'Isabela', 2.24, 3.75),
(11, 51, '[\"Riceland\", \"Owner-Tiller\"]', 1, NULL, NULL, 'Nueva Vizcaya', 2.37, 4.00),
(12, 52, '[\"Riceland\", \"Owner-Tiller\"]', 1, NULL, NULL, 'Aurora', 2.50, 4.25),
(13, 53, '[\"Riceland\", \"Owner-Tiller\"]', 1, NULL, NULL, 'Bataan', 2.63, 4.50),
(14, 54, '[\"Riceland\", \"Owner-Tiller\"]', 0, NULL, NULL, 'Tarlac', 2.76, 4.75),
(15, 55, '[\"Riceland\", \"Owner-Tiller\"]', 1, NULL, NULL, 'Laguna', 2.89, 5.00),
(16, 56, '[\"Riceland\", \"Owner-Tiller\"]', 1, NULL, NULL, 'Occidental Mindoro', 3.02, 3.50),
(17, 57, '[\"Riceland\", \"Owner-Tiller\"]', 1, NULL, NULL, 'Oriental Mindoro', 3.15, 3.75),
(18, 58, '[\"Riceland\", \"Owner-Tiller\"]', 0, NULL, NULL, 'Zamboanga Del Sur', 3.28, 4.00),
(19, 59, '[\"Riceland\", \"Owner-Tiller\"]', 1, NULL, NULL, 'Zamboanga Del Sur', 3.41, 4.25),
(20, 60, '[\"Riceland\", \"Owner-Tiller\"]', 1, NULL, NULL, 'Albay', 3.54, 4.50),
(21, 61, '[\"Riceland\", \"Owner-Tiller\"]', 1, NULL, NULL, 'Masbate', 3.67, 4.75),
(22, 62, '[\"Riceland\", \"Owner-Tiller\"]', 0, NULL, NULL, 'Antique', 3.80, 5.00),
(23, 63, '[\"Riceland\", \"Owner-Tiller\"]', 1, NULL, NULL, 'Iloilo', 3.93, 3.50),
(24, 64, '[\"Riceland\", \"Owner-Tiller\"]', 1, NULL, NULL, 'Cebu', 4.06, 3.75),
(25, 65, '[\"Riceland\", \"Owner-Tiller\"]', 1, NULL, NULL, 'Leyte', 4.19, 4.00),
(26, 66, '[\"Riceland\", \"Owner-Tiller\"]', 0, NULL, NULL, 'Samar', 4.32, 4.25),
(27, 67, '[\"Riceland\", \"Owner-Tiller\"]', 1, NULL, NULL, 'Bukidnon', 4.45, 4.50),
(28, 68, '[\"Riceland\", \"Owner-Tiller\"]', 1, NULL, NULL, 'Misamis Occidental', 4.58, 4.75),
(29, 69, '[\"Riceland\", \"Owner-Tiller\"]', 1, NULL, NULL, 'Davao Del Norte', 4.71, 5.00),
(30, 70, '[\"Riceland\", \"Owner-Tiller\"]', 0, NULL, NULL, 'Davao Del Sur', 4.84, 3.50),
(31, 71, '[\"Riceland\", \"Owner-Tiller\"]', 1, NULL, NULL, 'North Cotabato', 4.97, 3.75),
(32, 72, '[\"Riceland\", \"Owner-Tiller\"]', 1, NULL, NULL, 'Sultan Kudarat', 5.10, 4.00),
(33, 73, '[\"Riceland\",\"Owner-Tiller\"]', 0, 25.00, 75.00, 'Eastern Pangasinan', 1.25, 3.65),
(34, 74, '[\"Riceland\",\"Tenant\"]', 1, NULL, NULL, 'Eastern Pangasinan', 1.57, 3.87),
(35, 75, '[\"Riceland\",\"Lessee\"]', 1, NULL, NULL, 'Eastern Pangasinan', 1.89, 4.09),
(36, 76, '[\"Riceland\",\"Amortizing Owner\"]', 1, NULL, NULL, 'Eastern Pangasinan', 2.21, 4.31),
(37, 77, '[\"Riceland\",\"Owner-Tiller\"]', 0, NULL, NULL, 'Eastern Pangasinan', 2.53, 4.53),
(38, 78, '[\"Riceland\",\"Tenant\"]', 1, 25.00, 75.00, 'Eastern Pangasinan', 2.85, 4.75),
(39, 79, '[\"Riceland\",\"Lessee\"]', 1, NULL, NULL, 'Eastern Pangasinan', 3.17, 4.97),
(40, 80, '[\"Riceland\",\"Amortizing Owner\"]', 1, NULL, NULL, 'Eastern Pangasinan', 3.49, 5.19),
(41, 82, '[\"Riceland\",\"Owner-Tiller\"]', 0, NULL, NULL, 'Eastern Pangasinan', 3.81, 3.65),
(42, 84, '[\"Riceland\",\"Tenant\"]', 1, NULL, NULL, 'Eastern Pangasinan', 4.13, 3.87),
(43, 86, '[\"Riceland\",\"Lessee\"]', 1, 25.00, 75.00, 'Eastern Pangasinan', 1.25, 4.09),
(44, 88, '[\"Riceland\",\"Amortizing Owner\"]', 1, NULL, NULL, 'Eastern Pangasinan', 1.57, 4.31),
(45, 90, '[\"Riceland\",\"Owner-Tiller\"]', 0, NULL, NULL, 'Eastern Pangasinan', 1.89, 4.53),
(46, 91, '[\"Riceland\",\"Tenant\"]', 1, NULL, NULL, 'Eastern Pangasinan', 2.21, 4.75),
(47, 92, '[\"Riceland\",\"Lessee\"]', 1, NULL, NULL, 'Eastern Pangasinan', 2.53, 4.97),
(48, 93, '[\"Riceland\",\"Amortizing Owner\"]', 1, 25.00, 75.00, 'Eastern Pangasinan', 2.85, 5.19),
(49, 94, '[\"Riceland\",\"Owner-Tiller\"]', 0, NULL, NULL, 'Eastern Pangasinan', 3.17, 3.65),
(50, 95, '[\"Riceland\",\"Tenant\"]', 1, NULL, NULL, 'Eastern Pangasinan', 3.49, 3.87),
(51, 96, '[\"Riceland\",\"Lessee\"]', 1, NULL, NULL, 'Eastern Pangasinan', 3.81, 4.09),
(52, 97, '[\"Riceland\",\"Amortizing Owner\"]', 1, NULL, NULL, 'Eastern Pangasinan', 4.13, 4.31),
(53, 98, '[\"Riceland\",\"Owner-Tiller\"]', 0, 25.00, 75.00, 'Eastern Pangasinan', 1.25, 4.53),
(54, 99, '[\"Riceland\",\"Tenant\"]', 1, NULL, NULL, 'Eastern Pangasinan', 1.57, 4.75),
(55, 100, '[\"Riceland\",\"Lessee\"]', 1, NULL, NULL, 'Eastern Pangasinan', 1.89, 4.97),
(56, 101, '[\"Riceland\",\"Amortizing Owner\"]', 1, NULL, NULL, 'Eastern Pangasinan', 2.21, 5.19),
(57, 102, '[\"Riceland\",\"Owner-Tiller\"]', 0, NULL, NULL, 'Eastern Pangasinan', 2.53, 3.65),
(58, 103, '[\"Riceland\",\"Tenant\"]', 1, 25.00, 75.00, 'Eastern Pangasinan', 2.85, 3.87),
(59, 104, '[\"Riceland\",\"Lessee\"]', 1, NULL, NULL, 'Eastern Pangasinan', 3.17, 4.09),
(60, 105, '[\"Riceland\",\"Amortizing Owner\"]', 1, NULL, NULL, 'Eastern Pangasinan', 3.49, 4.31),
(61, 106, '[\"Riceland\",\"Owner-Tiller\"]', 0, NULL, NULL, 'Eastern Pangasinan', 3.81, 4.53),
(62, 108, '[\"Riceland\",\"Tenant\"]', 1, NULL, NULL, 'Eastern Pangasinan', 4.13, 4.75),
(63, 123, '[\"Riceland\",\"Owner-Tiller\"]', 0, NULL, NULL, 'Eastern Pangasinan', 1.10, 3.80),
(64, 124, '[\"Riceland\",\"Tenant\"]', 1, 30.00, 70.00, 'Eastern Pangasinan', 1.45, 4.00),
(65, 125, '[\"Riceland\",\"Lessee\"]', 1, NULL, NULL, 'Eastern Pangasinan', 1.80, 4.20),
(66, 126, '[\"Riceland\",\"CLT Holder\\/Recipient\"]', 0, NULL, NULL, 'Eastern Pangasinan', 2.15, 4.40),
(67, 127, '[\"Riceland\",\"Owner-Tiller\"]', 1, NULL, NULL, 'Eastern Pangasinan', 2.50, 4.60),
(68, 128, '[\"Riceland\",\"Tenant\"]', 1, 30.00, 70.00, 'Eastern Pangasinan', 2.85, 4.80),
(69, 129, '[\"Riceland\",\"Lessee\"]', 0, NULL, NULL, 'Eastern Pangasinan', 3.20, 5.00),
(70, 130, '[\"Riceland\",\"CLT Holder\\/Recipient\"]', 1, NULL, NULL, 'Eastern Pangasinan', 3.55, 5.20),
(71, 131, '[\"Riceland\",\"Owner-Tiller\"]', 1, NULL, NULL, 'Eastern Pangasinan', 3.90, 3.80),
(72, 132, '[\"Riceland\",\"Tenant\"]', 0, 30.00, 70.00, 'Eastern Pangasinan', 4.25, 4.00),
(73, 133, '[\"Riceland\",\"Lessee\"]', 1, NULL, NULL, 'Eastern Pangasinan', 1.10, 4.20),
(74, 134, '[\"Riceland\",\"CLT Holder\\/Recipient\"]', 1, NULL, NULL, 'Eastern Pangasinan', 1.45, 4.40),
(75, 135, '[\"Riceland\",\"Owner-Tiller\"]', 0, NULL, NULL, 'Eastern Pangasinan', 1.80, 4.60),
(76, 136, '[\"Riceland\",\"Tenant\"]', 1, 30.00, 70.00, 'Eastern Pangasinan', 2.15, 4.80),
(77, 137, '[\"Riceland\",\"Lessee\"]', 1, NULL, NULL, 'Eastern Pangasinan', 2.50, 5.00),
(78, 138, '[\"Riceland\",\"CLT Holder\\/Recipient\"]', 0, NULL, NULL, 'Eastern Pangasinan', 2.85, 5.20),
(79, 139, '[\"Riceland\",\"Owner-Tiller\"]', 1, NULL, NULL, 'Eastern Pangasinan', 3.20, 3.80),
(80, 140, '[\"Riceland\",\"Tenant\"]', 1, 30.00, 70.00, 'Eastern Pangasinan', 3.55, 4.00),
(81, 141, '[\"Riceland\",\"Lessee\"]', 0, NULL, NULL, 'Eastern Pangasinan', 3.90, 4.20),
(82, 142, '[\"Riceland\",\"CLT Holder\\/Recipient\"]', 1, NULL, NULL, 'Eastern Pangasinan', 4.25, 4.40),
(83, 143, '[\"Riceland\",\"Owner-Tiller\"]', 1, NULL, NULL, 'Eastern Pangasinan', 1.10, 4.60),
(84, 144, '[\"Riceland\",\"Tenant\"]', 0, 30.00, 70.00, 'Eastern Pangasinan', 1.45, 4.80),
(85, 145, '[\"Riceland\",\"Lessee\"]', 1, NULL, NULL, 'Eastern Pangasinan', 1.80, 5.00),
(86, 146, '[\"Riceland\",\"CLT Holder\\/Recipient\"]', 1, NULL, NULL, 'Eastern Pangasinan', 2.15, 5.20),
(87, 147, '[\"Riceland\",\"Owner-Tiller\"]', 0, NULL, NULL, 'Eastern Pangasinan', 2.50, 3.80),
(88, 148, '[\"Riceland\",\"Tenant\"]', 1, 30.00, 70.00, 'Eastern Pangasinan', 2.85, 4.00),
(89, 149, '[\"Riceland\",\"Lessee\"]', 1, NULL, NULL, 'Eastern Pangasinan', 3.20, 4.20),
(90, 150, '[\"Riceland\",\"CLT Holder\\/Recipient\"]', 0, NULL, NULL, 'Eastern Pangasinan', 3.55, 4.40),
(91, 151, '[\"Riceland\",\"Owner-Tiller\"]', 1, NULL, NULL, 'Eastern Pangasinan', 3.90, 4.60),
(92, 152, '[\"Riceland\",\"Tenant\"]', 1, 30.00, 70.00, 'Eastern Pangasinan', 4.25, 4.80);

-- --------------------------------------------------------

--
-- Table structure for table `location_masterlist`
--

DROP TABLE IF EXISTS `location_masterlist`;
CREATE TABLE `location_masterlist` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `region` varchar(120) NOT NULL,
  `branch` varchar(160) NOT NULL,
  `province` varchar(160) NOT NULL,
  `facility_name` varchar(180) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `location_masterlist`
--

INSERT INTO `location_masterlist` (`id`, `region`, `branch`, `province`, `facility_name`) VALUES
(578, 'ARMM', 'BASULTA Branch', 'Basilan', 'GID BASILAN'),
(574, 'ARMM', 'BASULTA Branch', 'Bongao', 'TAWI-TAWI SATELITE OFFICE'),
(572, 'ARMM', 'BASULTA Branch', 'Isabela City', 'BASILAN-BRANCH OFFICE'),
(573, 'ARMM', 'BASULTA Branch', 'Patikul, Sulu', 'SULU SATELITE OFFICE'),
(579, 'ARMM', 'BASULTA Branch', 'Sulu', 'GID SULU'),
(580, 'ARMM', 'BASULTA Branch', 'Tawi-Tawi', 'GID TAWI-TAWI'),
(577, 'ARMM', 'Lanao del Sur Branch', 'Lanao Del Sur', 'GID MARAWI'),
(571, 'ARMM', 'Lanao del Sur Branch', 'Marawi City', 'LANAO DEL SUR - BRANCH OFFICE'),
(581, 'ARMM', 'Lanao del Sur Branch', 'Marawi City', 'SHIN HEUNG RECIRCULATING GRAIN DRYER'),
(570, 'ARMM', 'Maguindanao Branch', 'Cotabato City', 'MAGUINDANAO BRANCH OFFICE'),
(576, 'ARMM', 'Maguindanao Branch', 'Maguindanao Del Sur', 'GID SHARIFF AGUAK'),
(575, 'ARMM', 'Maguindanao Branch', 'Sultan Kudarat', 'GID 6'),
(569, 'ARMM', 'Regional Office', 'Cotabato City', 'REGION XIV REGIONAL OFFICE (BARMM)'),
(584, 'CARAGA', 'Agusan Del Sur Branch Office', 'Agusan Del Norte', 'GID LIBERTAD I WHSE.'),
(585, 'CARAGA', 'Agusan Del Sur Branch Office', 'Agusan Del Norte', 'GID LIBERTAD II WHSE.'),
(583, 'CARAGA', 'Agusan Del Sur Branch Office', 'Agusan Del Sur', 'AGUSAN DEL SUR BRANCH OFFICE'),
(587, 'CARAGA', 'Agusan Del Sur Branch Office', 'Agusan Del Sur', 'GID ALEGRIA WHSE.'),
(586, 'CARAGA', 'Agusan Del Sur Branch Office', 'Agusan Del Sur', 'GID BAYUGAN WHSE.'),
(588, 'CARAGA', 'Agusan Del Sur Branch Office', 'Agusan Del Sur', 'GID TRENTO WHSE.'),
(582, 'CARAGA', 'Regional Office CARAGA', 'Agusan Del Norte', 'CARAGA REGIONAL OFFICE'),
(595, 'CARAGA', 'Surigao Del Sur Branch Office', 'Province Of Dinagat Islands', 'GID SAN JOSE WHSE'),
(594, 'CARAGA', 'Surigao Del Sur Branch Office', 'Surigao Del Norte', 'GID DAPA WHSE'),
(593, 'CARAGA', 'Surigao Del Sur Branch Office', 'Surigao Del Norte', 'GID KM. 10 WHSE'),
(592, 'CARAGA', 'Surigao Del Sur Branch Office', 'Surigao Del Sur', 'GID CANTILAN WHSE'),
(590, 'CARAGA', 'Surigao Del Sur Branch Office', 'Surigao Del Sur', 'GID DUPLEX WHSE'),
(591, 'CARAGA', 'Surigao Del Sur Branch Office', 'Surigao Del Sur', 'GID MANGAGOY WHSE'),
(589, 'CARAGA', 'Surigao Del Sur Branch Office', 'Surigao Del Sur', 'SURIGAO DEL SUR BRANCH OFFICE'),
(556, 'NCR', 'Central District', 'Batanes', 'Basco Warehouse'),
(565, 'NCR', 'Central District', 'Batanes', 'Batanes Unit Office'),
(549, 'NCR', 'Central District', 'Metro Manila', 'MFC #1'),
(550, 'NCR', 'Central District', 'Metro Manila', 'MFC #2'),
(551, 'NCR', 'Central District', 'Metro Manila', 'MFC #3'),
(552, 'NCR', 'Central District', 'Metro Manila', 'MFC #4'),
(553, 'NCR', 'Central District', 'Metro Manila', 'MFC #5'),
(554, 'NCR', 'Central District', 'Metro Manila', 'MFC #7'),
(555, 'NCR', 'Central District', 'Metro Manila', 'Minprocor'),
(563, 'NCR', 'Central District', 'Valenzuela', 'MFC Office'),
(564, 'NCR', 'East District', 'Antipolo, Rizal', 'East District Branch Office'),
(568, 'NCR', 'East District', 'Antipolo, Rizal', 'EDBO Quality Assurance Office'),
(566, 'NCR', 'East District', 'Cavite', 'Cavite Office'),
(557, 'NCR', 'East District', 'Cavite', 'General Trias A'),
(558, 'NCR', 'East District', 'Cavite', 'General Trias B'),
(567, 'NCR', 'East District', 'Marikina', 'Marikina Office'),
(561, 'NCR', 'East District', 'Metro Manila', 'Marikina Warehouse'),
(559, 'NCR', 'East District', 'Rizal', 'Antipolo Warehouse 1'),
(560, 'NCR', 'East District', 'Rizal', 'Antipolo Warehouse 2'),
(562, 'NCR', 'Regional Office NCR', 'Metro Manila', 'NCR Regional Office'),
(10, 'Region I', 'Eastern Pangasinan Branch Office', 'Eastern Pangasinan', 'Agricom, Batch Recirculating Dryer'),
(12, 'Region I', 'Eastern Pangasinan Branch Office', 'Eastern Pangasinan', 'Asingan FLGC'),
(3, 'Region I', 'Eastern Pangasinan Branch Office', 'Eastern Pangasinan', 'Binalonan DM'),
(2, 'Region I', 'Eastern Pangasinan Branch Office', 'Eastern Pangasinan', 'Binalonan GID'),
(4, 'Region I', 'Eastern Pangasinan Branch Office', 'Eastern Pangasinan', 'Binalonan Triplex'),
(1, 'Region I', 'Eastern Pangasinan Branch Office', 'Eastern Pangasinan', 'Eastern Pangasinan Branch Office'),
(5, 'Region I', 'Eastern Pangasinan Branch Office', 'Eastern Pangasinan', 'LSU Type, Batch Recirculating Dryer'),
(6, 'Region I', 'Eastern Pangasinan Branch Office', 'Eastern Pangasinan', 'Maruyama, Batch Recirculating Dryer'),
(9, 'Region I', 'Eastern Pangasinan Branch Office', 'Eastern Pangasinan', 'Naric'),
(7, 'Region I', 'Eastern Pangasinan Branch Office', 'Eastern Pangasinan', 'Rosales I'),
(8, 'Region I', 'Eastern Pangasinan Branch Office', 'Eastern Pangasinan', 'Rosales II'),
(13, 'Region I', 'Eastern Pangasinan Branch Office', 'Eastern Pangasinan', 'San Quintuin FLGC'),
(11, 'Region I', 'Eastern Pangasinan Branch Office', 'Eastern Pangasinan', 'Satake Ricemill'),
(16, 'Region I', 'Eastern Pangasinan Branch Office', 'Western Pangasinan', 'Alaminos GID'),
(19, 'Region I', 'Eastern Pangasinan Branch Office', 'Western Pangasinan', 'Corporate Farming-RPS 1 (Free rent)'),
(14, 'Region I', 'Eastern Pangasinan Branch Office', 'Western Pangasinan', 'Mangatarem GID I'),
(15, 'Region I', 'Eastern Pangasinan Branch Office', 'Western Pangasinan', 'Mangatarem GID II'),
(17, 'Region I', 'Eastern Pangasinan Branch Office', 'Western Pangasinan', 'Unique 1 Warehouse (Leased)'),
(18, 'Region I', 'Eastern Pangasinan Branch Office', 'Western Pangasinan', 'Unique 2 Warehouse (Leased)'),
(34, 'Region I', 'Ilocos Norte Branch Office', 'Abra', 'Abra Office'),
(35, 'Region I', 'Ilocos Norte Branch Office', 'Abra', 'AHTC Warehouse'),
(36, 'Region I', 'Ilocos Norte Branch Office', 'Abra', 'Bangued GID'),
(24, 'Region I', 'Ilocos Norte Branch Office', 'Ilocos Norte', 'Agricom LSU-Type Recirculating Dryer'),
(25, 'Region I', 'Ilocos Norte Branch Office', 'Ilocos Norte', 'AMCC LSU Recirculating Dryer'),
(23, 'Region I', 'Ilocos Norte Branch Office', 'Ilocos Norte', 'Dingras Duplex'),
(22, 'Region I', 'Ilocos Norte Branch Office', 'Ilocos Norte', 'Dingras Multi-Purpose'),
(20, 'Region I', 'Ilocos Norte Branch Office', 'Ilocos Norte', 'Ilocos Norte Branch Office'),
(21, 'Region I', 'Ilocos Norte Branch Office', 'Ilocos Norte', 'Laoag GID'),
(26, 'Region I', 'Ilocos Norte Branch Office', 'Ilocos Norte', 'Satake Ricemill'),
(28, 'Region I', 'Ilocos Norte Branch Office', 'Ilocos Sur', 'Bantay GID I'),
(29, 'Region I', 'Ilocos Norte Branch Office', 'Ilocos Sur', 'Bantay GID II'),
(33, 'Region I', 'Ilocos Norte Branch Office', 'Ilocos Sur', 'Candon City Warehouse'),
(31, 'Region I', 'Ilocos Norte Branch Office', 'Ilocos Sur', 'FLGC III'),
(27, 'Region I', 'Ilocos Norte Branch Office', 'Ilocos Sur', 'Ilocos Sur Office'),
(30, 'Region I', 'Ilocos Norte Branch Office', 'Ilocos Sur', 'Maruyama, Batch Recirculating Dryer'),
(32, 'Region I', 'Ilocos Norte Branch Office', 'Ilocos Sur', 'Tomato Paste Plant Warehouse'),
(41, 'Region I', 'La Union Branch Office', 'Benguet', 'Loakan GID Warehouse'),
(40, 'Region I', 'La Union Branch Office', 'Benguet', 'Loakan Staffhouse & Training Center'),
(37, 'Region I', 'La Union Branch Office', 'La Union', 'La Union Branch Office'),
(38, 'Region I', 'La Union Branch Office', 'La Union', 'San Juan GID Warehouse 1'),
(39, 'Region I', 'La Union Branch Office', 'La Union', 'San Juan GID Warehouse 2'),
(42, 'Region I', 'NFA Regional Office', 'La Union', 'NFA Regional Office 1'),
(98, 'Region II', 'Cagayan Branch Office', 'Apayao', 'Conner Buying Station'),
(99, 'Region II', 'Cagayan Branch Office', 'Apayao', 'Flora Buying Station'),
(53, 'Region II', 'Cagayan Branch Office', 'Apayao', 'Luna GID II Duplex'),
(100, 'Region II', 'Cagayan Branch Office', 'Cagayan', 'Agricomp Dryer'),
(93, 'Region II', 'Cagayan Branch Office', 'Cagayan', 'Aparri Buying Station'),
(44, 'Region II', 'Cagayan Branch Office', 'Cagayan', 'Cagayan Branch Office'),
(47, 'Region II', 'Cagayan Branch Office', 'Cagayan', 'Carig GID I'),
(48, 'Region II', 'Cagayan Branch Office', 'Cagayan', 'Carig GID II'),
(49, 'Region II', 'Cagayan Branch Office', 'Cagayan', 'Carig Triplex'),
(50, 'Region II', 'Cagayan Branch Office', 'Cagayan', 'Gonzaga GID III'),
(51, 'Region II', 'Cagayan Branch Office', 'Cagayan', 'Lasam GID III'),
(52, 'Region II', 'Cagayan Branch Office', 'Cagayan', 'Matucay GID I Duplex'),
(120, 'Region II', 'Cagayan Branch Office', 'Cagayan', 'NFA Allacapan Truckscale'),
(119, 'Region II', 'Cagayan Branch Office', 'Cagayan', 'NFA Tuguegarao Truckscale'),
(94, 'Region II', 'Cagayan Branch Office', 'Cagayan', 'Peñablanca Buying Station'),
(101, 'Region II', 'Cagayan Branch Office', 'Cagayan', 'Shin Hueng Dryer'),
(95, 'Region II', 'Cagayan Branch Office', 'Cagayan', 'Solana I Buying Station'),
(96, 'Region II', 'Cagayan Branch Office', 'Cagayan', 'Solana II Buying Station'),
(103, 'Region II', 'Cagayan Branch Office', 'Kalinga', 'Padiscor Dryer'),
(97, 'Region II', 'Cagayan Branch Office', 'Kalinga', 'Rizal Buying Station'),
(54, 'Region II', 'Cagayan Branch Office', 'Kalinga', 'Rizal FLGC'),
(113, 'Region II', 'Cagayan Branch Office', 'Kalinga', 'Satake Ricemill Tabuk'),
(102, 'Region II', 'Cagayan Branch Office', 'Kalinga', 'Shin Hueng Dryer'),
(55, 'Region II', 'Cagayan Branch Office', 'Kalinga', 'Tabuk GID Duplex'),
(105, 'Region II', 'Isabela Branch Office', 'Isabela', 'Agricomp Dryer'),
(56, 'Region II', 'Isabela Branch Office', 'Isabela', 'Alicia FLGC'),
(57, 'Region II', 'Isabela Branch Office', 'Isabela', 'Burgos FLGC'),
(58, 'Region II', 'Isabela Branch Office', 'Isabela', 'Cabagan FLGC'),
(59, 'Region II', 'Isabela Branch Office', 'Isabela', 'Cabatuan FLGC'),
(60, 'Region II', 'Isabela Branch Office', 'Isabela', 'Cauayan FLGC'),
(104, 'Region II', 'Isabela Branch Office', 'Isabela', 'Cimbria Dryer'),
(61, 'Region II', 'Isabela Branch Office', 'Isabela', 'Cordon FSW'),
(62, 'Region II', 'Isabela Branch Office', 'Isabela', 'Delfin Albano MLGC'),
(79, 'Region II', 'Isabela Branch Office', 'Isabela', 'Echague NPGC Corn Center'),
(66, 'Region II', 'Isabela Branch Office', 'Isabela', 'Echague NPGC Triplex-GREEN'),
(67, 'Region II', 'Isabela Branch Office', 'Isabela', 'Echague NPGC Trplex-WHITE'),
(63, 'Region II', 'Isabela Branch Office', 'Isabela', 'Gamu DUPLEX'),
(45, 'Region II', 'Isabela Branch Office', 'Isabela', 'Isabela Branch Office'),
(64, 'Region II', 'Isabela Branch Office', 'Isabela', 'Luna FLGC'),
(65, 'Region II', 'Isabela Branch Office', 'Isabela', 'Mallig FLGC'),
(107, 'Region II', 'Isabela Branch Office', 'Isabela', 'Mechapil Dryer'),
(118, 'Region II', 'Isabela Branch Office', 'Isabela', 'NFA Santiago Truckscale'),
(117, 'Region II', 'Isabela Branch Office', 'Isabela', 'NPGC Echague Truckscale'),
(108, 'Region II', 'Isabela Branch Office', 'Isabela', 'Padiscor Dryer'),
(68, 'Region II', 'Isabela Branch Office', 'Isabela', 'Palanan GID'),
(69, 'Region II', 'Isabela Branch Office', 'Isabela', 'Quezon FLGC'),
(70, 'Region II', 'Isabela Branch Office', 'Isabela', 'Ramon FLGC'),
(71, 'Region II', 'Isabela Branch Office', 'Isabela', 'Roxas GID I'),
(72, 'Region II', 'Isabela Branch Office', 'Isabela', 'Roxas GID II'),
(73, 'Region II', 'Isabela Branch Office', 'Isabela', 'San Isidro FLGC'),
(74, 'Region II', 'Isabela Branch Office', 'Isabela', 'San Manuel GID'),
(75, 'Region II', 'Isabela Branch Office', 'Isabela', 'San Mateo GID DUPLEX'),
(77, 'Region II', 'Isabela Branch Office', 'Isabela', 'Santiago GID'),
(78, 'Region II', 'Isabela Branch Office', 'Isabela', 'Santiago TRIPLEX'),
(115, 'Region II', 'Isabela Branch Office', 'Isabela', 'Satake Ricemill NPGC'),
(116, 'Region II', 'Isabela Branch Office', 'Isabela', 'Satake Ricemill Roxas'),
(114, 'Region II', 'Isabela Branch Office', 'Isabela', 'Satake Ricemill Santiago'),
(106, 'Region II', 'Isabela Branch Office', 'Isabela', 'Shin Hueng Dryer'),
(76, 'Region II', 'Isabela Branch Office', 'Isabela', 'Tumauini GID'),
(43, 'Region II', 'NFA Region 2', 'Isabela', 'NFA Regional Office 2'),
(91, 'Region II', 'Nueva Vizcaya Branch Office', 'Ifugao', 'Alfonso Lista FLGC'),
(90, 'Region II', 'Nueva Vizcaya Branch Office', 'Ifugao', 'Lagawe GID'),
(112, 'Region II', 'Nueva Vizcaya Branch Office', 'Ifugao', 'Shin Hueng Dryer'),
(92, 'Region II', 'Nueva Vizcaya Branch Office', 'Mountain Province', 'Bontoc GID'),
(109, 'Region II', 'Nueva Vizcaya Branch Office', 'Nueva Vizcaya', 'Agricomp Dryer'),
(83, 'Region II', 'Nueva Vizcaya Branch Office', 'Nueva Vizcaya', 'Bagabag FLGC'),
(82, 'Region II', 'Nueva Vizcaya Branch Office', 'Nueva Vizcaya', 'Bayombong DUPLEX'),
(80, 'Region II', 'Nueva Vizcaya Branch Office', 'Nueva Vizcaya', 'Bayombong GID I'),
(81, 'Region II', 'Nueva Vizcaya Branch Office', 'Nueva Vizcaya', 'Bayombong GID II'),
(121, 'Region II', 'Nueva Vizcaya Branch Office', 'Nueva Vizcaya', 'MGC Bayombong Truckscale'),
(46, 'Region II', 'Nueva Vizcaya Branch Office', 'Nueva Vizcaya', 'Nueva Vizcaya Branch Office'),
(110, 'Region II', 'Nueva Vizcaya Branch Office', 'Quirino', 'Agricomp Dryer'),
(84, 'Region II', 'Nueva Vizcaya Branch Office', 'Quirino', 'Cabarroguis GID I'),
(85, 'Region II', 'Nueva Vizcaya Branch Office', 'Quirino', 'Cabarroguis GID II'),
(87, 'Region II', 'Nueva Vizcaya Branch Office', 'Quirino', 'Diffun FLGC'),
(89, 'Region II', 'Nueva Vizcaya Branch Office', 'Quirino', 'Maddela FLGC'),
(86, 'Region II', 'Nueva Vizcaya Branch Office', 'Quirino', 'Ricemill House'),
(88, 'Region II', 'Nueva Vizcaya Branch Office', 'Quirino', 'Saguday FLGC'),
(111, 'Region II', 'Nueva Vizcaya Branch Office', 'Quirino', 'Shin Hueng Dryer'),
(142, 'Region III', 'Bulacan Branch Office', 'Bulacan', 'Balagtas 1'),
(143, 'Region III', 'Bulacan Branch Office', 'Bulacan', 'Balagtas 2'),
(144, 'Region III', 'Bulacan Branch Office', 'Bulacan', 'Balagtas 3'),
(141, 'Region III', 'Bulacan Branch Office', 'Bulacan', 'Bulacan Branch Office'),
(145, 'Region III', 'Bulacan Branch Office', 'Bulacan', 'San Ildefonso 1'),
(146, 'Region III', 'Bulacan Branch Office', 'Bulacan', 'San Ildefonso 2'),
(147, 'Region III', 'Bulacan Branch Office', 'Bulacan', 'Sta Rita 1A'),
(148, 'Region III', 'Bulacan Branch Office', 'Bulacan', 'Sta Rita 2A'),
(149, 'Region III', 'Bulacan Branch Office', 'Bulacan', 'Tikay'),
(166, 'Region III', 'Nueva Ecija Branch Office', 'Aurora', 'Baler GID Annex B'),
(165, 'Region III', 'Nueva Ecija Branch Office', 'Aurora', 'Baler GID Main & Annex A'),
(167, 'Region III', 'Nueva Ecija Branch Office', 'Aurora', 'Bartolome Warehouse'),
(168, 'Region III', 'Nueva Ecija Branch Office', 'Aurora', 'Casiguran Warehouse'),
(175, 'Region III', 'Nueva Ecija Branch Office', 'Nueva Ecija', 'Aliaga Food Center'),
(176, 'Region III', 'Nueva Ecija Branch Office', 'Nueva Ecija', 'Bongabon Food Center'),
(169, 'Region III', 'Nueva Ecija Branch Office', 'Nueva Ecija', 'Cabanatuan Warehouse 1'),
(172, 'Region III', 'Nueva Ecija Branch Office', 'Nueva Ecija', 'Cabanatuan Warehouse 11 (Duplex)'),
(173, 'Region III', 'Nueva Ecija Branch Office', 'Nueva Ecija', 'Cabanatuan Warehouse 12 (Triplex)'),
(174, 'Region III', 'Nueva Ecija Branch Office', 'Nueva Ecija', 'Cabanatuan Warehouse 13'),
(170, 'Region III', 'Nueva Ecija Branch Office', 'Nueva Ecija', 'Cabanatuan Warehouse 2'),
(171, 'Region III', 'Nueva Ecija Branch Office', 'Nueva Ecija', 'Cabanatuan Warehouse 3'),
(177, 'Region III', 'Nueva Ecija Branch Office', 'Nueva Ecija', 'Gapan Food Center'),
(180, 'Region III', 'Nueva Ecija Branch Office', 'Nueva Ecija', 'Guimba Drier House'),
(178, 'Region III', 'Nueva Ecija Branch Office', 'Nueva Ecija', 'Guimba Warehouse 1 (Duplex)'),
(179, 'Region III', 'Nueva Ecija Branch Office', 'Nueva Ecija', 'Guimba Warehouse 2'),
(181, 'Region III', 'Nueva Ecija Branch Office', 'Nueva Ecija', 'Muñoz Warehouse 1'),
(164, 'Region III', 'Nueva Ecija Branch Office', 'Nueva Ecija', 'Nueva Ecija Branch Office'),
(182, 'Region III', 'Nueva Ecija Branch Office', 'Nueva Ecija', 'Valle FLGC'),
(150, 'Region III', 'Pampanga Branch Office', 'Bataan', 'Balanga 1'),
(151, 'Region III', 'Pampanga Branch Office', 'Bataan', 'Balanga 2'),
(153, 'Region III', 'Pampanga Branch Office', 'Bataan', 'Bataan Satellite Office'),
(152, 'Region III', 'Pampanga Branch Office', 'Bataan', 'Farm Level Grain Center III - Dinalupihan'),
(158, 'Region III', 'Pampanga Branch Office', 'Pampanga', 'Farm Level Grain Center III - San Luis'),
(154, 'Region III', 'Pampanga Branch Office', 'Pampanga', 'Pampanga Branch Office'),
(155, 'Region III', 'Pampanga Branch Office', 'Pampanga', 'Sindalan 1'),
(156, 'Region III', 'Pampanga Branch Office', 'Pampanga', 'Sindalan 2'),
(157, 'Region III', 'Pampanga Branch Office', 'Pampanga', 'TSB Warehouse'),
(159, 'Region III', 'Pampanga Branch Office', 'Zambales', 'Castillejos'),
(162, 'Region III', 'Pampanga Branch Office', 'Zambales', 'Iba'),
(161, 'Region III', 'Pampanga Branch Office', 'Zambales', 'Mango Terminal'),
(163, 'Region III', 'Pampanga Branch Office', 'Zambales', 'Maruyama YC-100'),
(160, 'Region III', 'Pampanga Branch Office', 'Zambales', 'Zambales Satellite Office'),
(122, 'Region III', 'Regional Office IIII', 'Nueva Ecija', 'Central Luzon Regional Office'),
(124, 'Region III', 'Tarlac Branch Office', 'Tarlac', 'Aguso Warehouse 1'),
(125, 'Region III', 'Tarlac Branch Office', 'Tarlac', 'Aguso Warehouse 2'),
(126, 'Region III', 'Tarlac Branch Office', 'Tarlac', 'Aguso Warehouse 3'),
(127, 'Region III', 'Tarlac Branch Office', 'Tarlac', 'Aguso Warehouse 3-Annex'),
(129, 'Region III', 'Tarlac Branch Office', 'Tarlac', 'Aguso Warehouse 5'),
(138, 'Region III', 'Tarlac Branch Office', 'Tarlac', 'Camiling Productivity Center'),
(135, 'Region III', 'Tarlac Branch Office', 'Tarlac', 'Concepcion GID'),
(134, 'Region III', 'Tarlac Branch Office', 'Tarlac', 'Concepcion Office'),
(131, 'Region III', 'Tarlac Branch Office', 'Tarlac', 'La Paz GID I'),
(132, 'Region III', 'Tarlac Branch Office', 'Tarlac', 'La Paz GID II'),
(133, 'Region III', 'Tarlac Branch Office', 'Tarlac', 'La Paz GID III'),
(130, 'Region III', 'Tarlac Branch Office', 'Tarlac', 'La Paz Office'),
(128, 'Region III', 'Tarlac Branch Office', 'Tarlac', 'New Construction of Modernized Whse'),
(140, 'Region III', 'Tarlac Branch Office', 'Tarlac', 'San Manuel Warehouse'),
(136, 'Region III', 'Tarlac Branch Office', 'Tarlac', 'ShinHeung Recirculating Grain Dryer'),
(139, 'Region III', 'Tarlac Branch Office', 'Tarlac', 'Sta. Ines Productivity Center'),
(137, 'Region III', 'Tarlac Branch Office', 'Tarlac', 'Talimundok FLGC'),
(123, 'Region III', 'Tarlac Branch Office', 'Tarlac', 'Tarlac Branch Office'),
(200, 'Region IV', 'Batangas Branch Office', 'Batangas', 'Batangas Branch Office'),
(201, 'Region IV', 'Batangas Branch Office', 'Batangas', 'GID II Batangas'),
(202, 'Region IV', 'Batangas Branch Office', 'Batangas', 'GID IIA Batangas'),
(203, 'Region IV', 'Batangas Branch Office', 'Batangas', 'GID Romblon'),
(204, 'Region IV', 'Batangas Branch Office', 'Batangas', 'Suntons Warehouse (Leased Warehouse)'),
(197, 'Region IV', 'Laguna Branch Office', 'Laguna', 'FLGC III'),
(195, 'Region IV', 'Laguna Branch Office', 'Laguna', 'GID 1'),
(194, 'Region IV', 'Laguna Branch Office', 'Laguna', 'GID 2'),
(196, 'Region IV', 'Laguna Branch Office', 'Laguna', 'GID Infanta'),
(198, 'Region IV', 'Laguna Branch Office', 'Laguna', 'Laguna Branch Office'),
(234, 'Region IV', 'Occidental Mindoro Branch Office', 'Occidental Mindoro', 'ABIA Warehouse'),
(223, 'Region IV', 'Occidental Mindoro Branch Office', 'Occidental Mindoro', 'Almuete Warehouse'),
(230, 'Region IV', 'Occidental Mindoro Branch Office', 'Occidental Mindoro', 'AMMAN Warehouse'),
(206, 'Region IV', 'Occidental Mindoro Branch Office', 'Occidental Mindoro', 'GID 1'),
(207, 'Region IV', 'Occidental Mindoro Branch Office', 'Occidental Mindoro', 'GID 2'),
(208, 'Region IV', 'Occidental Mindoro Branch Office', 'Occidental Mindoro', 'GID 3'),
(209, 'Region IV', 'Occidental Mindoro Branch Office', 'Occidental Mindoro', 'GID 4'),
(210, 'Region IV', 'Occidental Mindoro Branch Office', 'Occidental Mindoro', 'GID 5'),
(211, 'Region IV', 'Occidental Mindoro Branch Office', 'Occidental Mindoro', 'GID 6'),
(214, 'Region IV', 'Occidental Mindoro Branch Office', 'Occidental Mindoro', 'GID 7'),
(215, 'Region IV', 'Occidental Mindoro Branch Office', 'Occidental Mindoro', 'GID 8'),
(217, 'Region IV', 'Occidental Mindoro Branch Office', 'Occidental Mindoro', 'GID Duplex'),
(216, 'Region IV', 'Occidental Mindoro Branch Office', 'Occidental Mindoro', 'GID Sablayan'),
(219, 'Region IV', 'Occidental Mindoro Branch Office', 'Occidental Mindoro', 'Jafpy Warehouse'),
(235, 'Region IV', 'Occidental Mindoro Branch Office', 'Occidental Mindoro', 'LIMFCO Warehouse'),
(225, 'Region IV', 'Occidental Mindoro Branch Office', 'Occidental Mindoro', 'Magsaysay First Christian MPC'),
(224, 'Region IV', 'Occidental Mindoro Branch Office', 'Occidental Mindoro', 'Magsaysay Orig Warehouse'),
(231, 'Region IV', 'Occidental Mindoro Branch Office', 'Occidental Mindoro', 'MAMAMUCO Warehouse'),
(218, 'Region IV', 'Occidental Mindoro Branch Office', 'Occidental Mindoro', 'Mamburao Office'),
(232, 'Region IV', 'Occidental Mindoro Branch Office', 'Occidental Mindoro', 'Manayan Warehouse'),
(229, 'Region IV', 'Occidental Mindoro Branch Office', 'Occidental Mindoro', 'Miller Warehouse'),
(212, 'Region IV', 'Occidental Mindoro Branch Office', 'Occidental Mindoro', 'NAWACO 1'),
(213, 'Region IV', 'Occidental Mindoro Branch Office', 'Occidental Mindoro', 'NAWACO 2'),
(233, 'Region IV', 'Occidental Mindoro Branch Office', 'Occidental Mindoro', 'New Life Warehouse'),
(237, 'Region IV', 'Occidental Mindoro Branch Office', 'Occidental Mindoro', 'New Pajayon Warehouse'),
(205, 'Region IV', 'Occidental Mindoro Branch Office', 'Occidental Mindoro', 'Occidental Mindoro Branch Office'),
(226, 'Region IV', 'Occidental Mindoro Branch Office', 'Occidental Mindoro', 'Pablo Warehouse'),
(220, 'Region IV', 'Occidental Mindoro Branch Office', 'Occidental Mindoro', 'Pacunla 2A Warehouse'),
(221, 'Region IV', 'Occidental Mindoro Branch Office', 'Occidental Mindoro', 'Pacunla 2B Warehouse'),
(228, 'Region IV', 'Occidental Mindoro Branch Office', 'Occidental Mindoro', 'PAKIKIBAGAI Warehouse'),
(236, 'Region IV', 'Occidental Mindoro Branch Office', 'Occidental Mindoro', 'SACAMUCO Warehouse'),
(227, 'Region IV', 'Occidental Mindoro Branch Office', 'Occidental Mindoro', 'Salvacion Warehouse'),
(222, 'Region IV', 'Occidental Mindoro Branch Office', 'Occidental Mindoro', 'Sebastian Warehouse'),
(184, 'Region IV', 'Oriental Mindoro Branch Office', 'Oriental Mindoro', 'GID Calapan Warehouse'),
(185, 'Region IV', 'Oriental Mindoro Branch Office', 'Oriental Mindoro', 'GID Naujan Warehouse'),
(189, 'Region IV', 'Oriental Mindoro Branch Office', 'Oriental Mindoro', 'GID Roxas Duplex Warehouse'),
(186, 'Region IV', 'Oriental Mindoro Branch Office', 'Oriental Mindoro', 'GID-I Pinamalayan Warehouse'),
(187, 'Region IV', 'Oriental Mindoro Branch Office', 'Oriental Mindoro', 'GID-II Pinamalayan Warehouse'),
(183, 'Region IV', 'Oriental Mindoro Branch Office', 'Oriental Mindoro', 'Oriental Mindoro Branch Office'),
(188, 'Region IV', 'Oriental Mindoro Branch Office', 'Oriental Mindoro', 'RGC Warehouse'),
(241, 'Region IV', 'Palawan Branch Office', 'Palawan', 'GID 01 Warehouse'),
(242, 'Region IV', 'Palawan Branch Office', 'Palawan', 'GID 02 Warehouse'),
(243, 'Region IV', 'Palawan Branch Office', 'Palawan', 'GID 02 Warehouse- Annex'),
(244, 'Region IV', 'Palawan Branch Office', 'Palawan', 'GID 03 Warehouse'),
(247, 'Region IV', 'Palawan Branch Office', 'Palawan', 'LGU-RAC Rizal Buying Station'),
(249, 'Region IV', 'Palawan Branch Office', 'Palawan', 'MCFA PRC 2 Warehouse'),
(240, 'Region IV', 'Palawan Branch Office', 'Palawan', 'NFA Owned'),
(238, 'Region IV', 'Palawan Branch Office', 'Palawan', 'Palawan (New) Branch Office'),
(239, 'Region IV', 'Palawan Branch Office', 'Palawan', 'Palawan (Old) Branch Office'),
(245, 'Region IV', 'Palawan Branch Office', 'Palawan', 'PARCOFED 1 Warehouse'),
(246, 'Region IV', 'Palawan Branch Office', 'Palawan', 'PARCOFED 2 Warehouse'),
(248, 'Region IV', 'Palawan Branch Office', 'Palawan', 'SAMAGMA Warehouse'),
(193, 'Region IV', 'Quezon Branch Office', 'Quezon', 'FLGC'),
(191, 'Region IV', 'Quezon Branch Office', 'Quezon', 'GID Lucena'),
(192, 'Region IV', 'Quezon Branch Office', 'Quezon', 'ONG Warehouse'),
(190, 'Region IV', 'Quezon Branch Office', 'Quezon', 'Quezon Branch Office'),
(199, 'Region IV', 'Regional Office IV', 'Batangas', 'NFA Regional Office IV'),
(399, 'Region IX', 'Zamboanga Branch Office', 'Zamboanga Del Sur', 'Bangco Titay WHSE (For Repair/Improvement)'),
(396, 'Region IX', 'Zamboanga Branch Office', 'Zamboanga Del Sur', 'Dryer House'),
(398, 'Region IX', 'Zamboanga Branch Office', 'Zamboanga Del Sur', 'FLGC Siay WHSE'),
(395, 'Region IX', 'Zamboanga Branch Office', 'Zamboanga Del Sur', 'GID Guilawa WHSE'),
(394, 'Region IX', 'Zamboanga Branch Office', 'Zamboanga Del Sur', 'GID Taway WHSE'),
(392, 'Region IX', 'Zamboanga Branch Office', 'Zamboanga Del Sur', 'GID/FOREMOST WHSE C/D'),
(397, 'Region IX', 'Zamboanga Branch Office', 'Zamboanga Del Sur', 'GTM Leased Warehouse'),
(390, 'Region IX', 'Zamboanga Branch Office', 'Zamboanga Del Sur', 'New GID WHSE'),
(401, 'Region IX', 'Zamboanga Branch Office', 'Zamboanga Del Sur', 'Regional Office'),
(400, 'Region IX', 'Zamboanga Branch Office', 'Zamboanga Del Sur', 'Taway, Ipil Unit Office(To be demolish)'),
(393, 'Region IX', 'Zamboanga Branch Office', 'Zamboanga Del Sur', 'Truck Scale'),
(391, 'Region IX', 'Zamboanga Branch Office', 'Zamboanga Del Sur', 'Warehouse A/B (Lease Warehouse)'),
(389, 'Region IX', 'Zamboanga Branch Office', 'Zamboanga Del Sur', 'Zamboanga City Branch Office'),
(411, 'Region IX', 'Zamboanga del Sur Branch Office', 'Zamboanga Del Norte', 'Dipolog Satelitte Branch Office'),
(414, 'Region IX', 'Zamboanga del Sur Branch Office', 'Zamboanga Del Norte', 'Dryer House'),
(412, 'Region IX', 'Zamboanga del Sur Branch Office', 'Zamboanga Del Norte', 'GID I Dipolog WHSE'),
(413, 'Region IX', 'Zamboanga del Sur Branch Office', 'Zamboanga Del Norte', 'GID II Dipolog WHSE'),
(417, 'Region IX', 'Zamboanga del Sur Branch Office', 'Zamboanga Del Norte', 'GID Siocon WHSE'),
(416, 'Region IX', 'Zamboanga del Sur Branch Office', 'Zamboanga Del Norte', 'Liloy LFSC WHSE'),
(420, 'Region IX', 'Zamboanga del Sur Branch Office', 'Zamboanga Del Norte', 'Truck Scale'),
(423, 'Region IX', 'Zamboanga del Sur Branch Office', 'Zamboanga Del Sur', 'C & A Rice Mill'),
(407, 'Region IX', 'Zamboanga del Sur Branch Office', 'Zamboanga Del Sur', 'Culo A WHSE'),
(408, 'Region IX', 'Zamboanga del Sur Branch Office', 'Zamboanga Del Sur', 'Culo B WHSE'),
(409, 'Region IX', 'Zamboanga del Sur Branch Office', 'Zamboanga Del Sur', 'Culo C WHSE'),
(410, 'Region IX', 'Zamboanga del Sur Branch Office', 'Zamboanga Del Sur', 'Dumingag FLGC WHSE'),
(422, 'Region IX', 'Zamboanga del Sur Branch Office', 'Zamboanga Del Sur', 'Fernandez Rice Mill & Buying Station'),
(406, 'Region IX', 'Zamboanga del Sur Branch Office', 'Zamboanga Del Sur', 'Molave Satelitte Branch Office'),
(415, 'Region IX', 'Zamboanga del Sur Branch Office', 'Zamboanga Del Sur', 'Osmena FSC WHSE'),
(418, 'Region IX', 'Zamboanga del Sur Branch Office', 'Zamboanga Del Sur', 'San Miguel FSW'),
(403, 'Region IX', 'Zamboanga del Sur Branch Office', 'Zamboanga Del Sur', 'Tiguma A WHSE'),
(404, 'Region IX', 'Zamboanga del Sur Branch Office', 'Zamboanga Del Sur', 'Tiguma B WHSE'),
(405, 'Region IX', 'Zamboanga del Sur Branch Office', 'Zamboanga Del Sur', 'Tiguma C WHSE'),
(419, 'Region IX', 'Zamboanga del Sur Branch Office', 'Zamboanga Del Sur', 'Truck Scale'),
(421, 'Region IX', 'Zamboanga del Sur Branch Office', 'Zamboanga Del Sur', 'Villoria Rice and Corn Mill'),
(402, 'Region IX', 'Zamboanga del Sur Branch Office', 'Zamboanga Del Sur', 'Zamboanga del Sur Branch Office'),
(251, 'Region V', 'Albay Branch Office', 'Albay', 'Albay Branch Office'),
(258, 'Region V', 'Albay Branch Office', 'Albay', 'Catanduanes Field Office'),
(257, 'Region V', 'Albay Branch Office', 'Albay', 'GT 345XL Batch Recirculating Dryer'),
(260, 'Region V', 'Albay Branch Office', 'Albay', 'Kolbi Ricemill (Diesel Engine)'),
(252, 'Region V', 'Albay Branch Office', 'Albay', 'Legazpi GID Warehouse'),
(256, 'Region V', 'Albay Branch Office', 'Albay', 'Libon Warehouse'),
(253, 'Region V', 'Albay Branch Office', 'Albay', 'Ligao Warehouse'),
(254, 'Region V', 'Albay Branch Office', 'Albay', 'Tabaco Abacorp Warehouse'),
(255, 'Region V', 'Albay Branch Office', 'Albay', 'Tabaco GID Warehouse'),
(259, 'Region V', 'Albay Branch Office', 'Albay', 'Virac GID II Warehouse'),
(265, 'Region V', 'Camarines Sur Branch Office', 'Camarines Sur', 'Agricom Batch Recirculating Dryer'),
(264, 'Region V', 'Camarines Sur Branch Office', 'Camarines Sur', 'Buivanggo Multi-pass Ricemill'),
(267, 'Region V', 'Camarines Sur Branch Office', 'Camarines Sur', 'Camarines Norte Field Office'),
(261, 'Region V', 'Camarines Sur Branch Office', 'Camarines Sur', 'Camarines Sur Branch Office'),
(271, 'Region V', 'Camarines Sur Branch Office', 'Camarines Sur', 'DA Flatbed Dryer'),
(272, 'Region V', 'Camarines Sur Branch Office', 'Camarines Sur', 'Flatbed Dryer'),
(268, 'Region V', 'Camarines Sur Branch Office', 'Camarines Sur', 'GID 1 Daet Warehouse'),
(262, 'Region V', 'Camarines Sur Branch Office', 'Camarines Sur', 'GID 1 Warehouse Pili'),
(269, 'Region V', 'Camarines Sur Branch Office', 'Camarines Sur', 'GID 2 Daet Warehouse'),
(263, 'Region V', 'Camarines Sur Branch Office', 'Camarines Sur', 'GID 2 Warehouse Pili'),
(266, 'Region V', 'Camarines Sur Branch Office', 'Camarines Sur', 'GID Libmanan Warehouse'),
(270, 'Region V', 'Camarines Sur Branch Office', 'Camarines Sur', 'Kolbi Ricemill (Electric)'),
(250, 'Region V', 'Regional Office', 'Albay', 'NFA Regional Office No. V'),
(283, 'Region V', 'Sorsogon Branch Office', 'Masbate', 'Flatbed Dryer'),
(281, 'Region V', 'Sorsogon Branch Office', 'Masbate', 'GID 1 Warehouse Masbate'),
(280, 'Region V', 'Sorsogon Branch Office', 'Masbate', 'GID Warehouse Masbate'),
(282, 'Region V', 'Sorsogon Branch Office', 'Masbate', 'Kolbi Ricemill (Electric)'),
(279, 'Region V', 'Sorsogon Branch Office', 'Masbate', 'Masbate Field Office'),
(274, 'Region V', 'Sorsogon Branch Office', 'Sorsogon', 'GID 1 Warehouse Sorsogon'),
(275, 'Region V', 'Sorsogon Branch Office', 'Sorsogon', 'GID 2 Warehouse Sorsogon'),
(276, 'Region V', 'Sorsogon Branch Office', 'Sorsogon', 'GID 3 Warehouse Sorsogon'),
(278, 'Region V', 'Sorsogon Branch Office', 'Sorsogon', 'Megasun Batch Recirculating Dryer'),
(277, 'Region V', 'Sorsogon Branch Office', 'Sorsogon', 'Shin Heung Batch Recirculating Dryer'),
(273, 'Region V', 'Sorsogon Branch Office', 'Sorsogon', 'Sorsogon Branch Office'),
(291, 'Region VI', 'Capiz Branch', 'Aklan', 'Aklan Grains Center'),
(323, 'Region VI', 'Capiz Branch', 'Aklan', 'Shin Heung Mechanical  Dryer'),
(289, 'Region VI', 'Capiz Branch', 'Capiz', 'Bolo Grains Center'),
(290, 'Region VI', 'Capiz Branch', 'Capiz', 'Dumalag Grains Center'),
(292, 'Region VI', 'Capiz Branch', 'Capiz', 'Maruyama Recirculating Dryer'),
(322, 'Region VI', 'Capiz Branch', 'Capiz', 'Satake Batch Recirculating Dryer'),
(293, 'Region VI', 'Capiz Branch', 'Capiz', 'Satake Ricemill'),
(288, 'Region VI', 'Capiz Branch', 'Capiz', 'Sigma Grains Center'),
(308, 'Region VI', 'Iloilo Branch', 'Antique', 'Agricum Biomass'),
(307, 'Region VI', 'Iloilo Branch', 'Antique', 'Agricum LSU Type-Circulating Dryer'),
(311, 'Region VI', 'Iloilo Branch', 'Antique', 'Antique Satake Ricemill'),
(294, 'Region VI', 'Iloilo Branch', 'Antique', 'GID Camp Fullon 1'),
(295, 'Region VI', 'Iloilo Branch', 'Antique', 'GID Camp Fullon 3'),
(296, 'Region VI', 'Iloilo Branch', 'Antique', 'GID Culasi'),
(310, 'Region VI', 'Iloilo Branch', 'Antique', 'Shin Heung Mechanical  Dryer'),
(309, 'Region VI', 'Iloilo Branch', 'Antique', 'Shun Kuan EC50 Recirculating Dryer'),
(302, 'Region VI', 'Iloilo Branch', 'Guimaras', 'Jordan Warehouse'),
(303, 'Region VI', 'Iloilo Branch', 'Guimaras', 'Millan JICA'),
(304, 'Region VI', 'Iloilo Branch', 'Guimaras', 'Suclaran JICA'),
(316, 'Region VI', 'Iloilo Branch', 'Iloilo', 'Dueñas MAFIM'),
(299, 'Region VI', 'Iloilo Branch', 'Iloilo', 'Dumangas Grains Center'),
(315, 'Region VI', 'Iloilo Branch', 'Iloilo', 'Dumangas MAFIM'),
(312, 'Region VI', 'Iloilo Branch', 'Iloilo', 'Dumangas Satake Ricemill'),
(320, 'Region VI', 'Iloilo Branch', 'Iloilo', 'Iloilo Branch Office'),
(314, 'Region VI', 'Iloilo Branch', 'Iloilo', 'Jaro MAFIM'),
(313, 'Region VI', 'Iloilo Branch', 'Iloilo', 'Jaro Satake Ricemill'),
(297, 'Region VI', 'Iloilo Branch', 'Iloilo', 'Jaro Triplex Warehouse'),
(298, 'Region VI', 'Iloilo Branch', 'Iloilo', 'Kabsaka Triplex Warehouse'),
(306, 'Region VI', 'Iloilo Branch', 'Iloilo', 'Maruyama Recirculating Dryer'),
(319, 'Region VI', 'Iloilo Branch', 'Iloilo', 'Modified Flatbed Dryer'),
(318, 'Region VI', 'Iloilo Branch', 'Iloilo', 'Modified Flatbed Dryer (TWIN)'),
(300, 'Region VI', 'Iloilo Branch', 'Iloilo', 'Pototan Grains Center'),
(301, 'Region VI', 'Iloilo Branch', 'Iloilo', 'San Dionisio Warehouse'),
(317, 'Region VI', 'Iloilo Branch', 'Iloilo', 'Shin Heung'),
(305, 'Region VI', 'Iloilo Branch', 'Iloilo', 'Shin Heung Mechanical Dryer'),
(284, 'Region VI', 'Negros Occidental Branch', 'Negros Occ', 'GID Warehouse'),
(286, 'Region VI', 'Negros Occidental Branch', 'Negros Occ', 'Malaluan Warehouse A (Leased)'),
(287, 'Region VI', 'Negros Occidental Branch', 'Negros Occ', 'Malaluan Warehouse B (Leased)'),
(285, 'Region VI', 'Negros Occidental Branch', 'Negros Occ', 'Negros Occidental Branch Office'),
(321, 'Region VI', 'Regional Office', 'Iloilo', 'Western Visayas Regional Office'),
(339, 'Region VII', 'Bohol Branch Office', 'Bohol', 'Anihan Portable Recirculating Dryer'),
(335, 'Region VII', 'Bohol Branch Office', 'Bohol', 'Bohol Branch Office'),
(337, 'Region VII', 'Bohol Branch Office', 'Bohol', 'FLGC III Warehouse'),
(338, 'Region VII', 'Bohol Branch Office', 'Bohol', 'FSC Warehouse'),
(336, 'Region VII', 'Bohol Branch Office', 'Bohol', 'GID Warehouse (w/ extension)'),
(340, 'Region VII', 'Bohol Branch Office', 'Bohol', 'Green Mac Batch Recirculating Dryer'),
(342, 'Region VII', 'Bohol Branch Office', 'Bohol', 'Satake Rice Mill'),
(341, 'Region VII', 'Bohol Branch Office', 'Bohol', 'Shin Heung Batch Recirculating Dryer'),
(334, 'Region VII', 'Cebu Branch Office', 'Cebu', 'Asuki Truckscale'),
(333, 'Region VII', 'Cebu Branch Office', 'Cebu', 'Cebu Branch Office'),
(331, 'Region VII', 'Cebu Branch Office', 'Cebu', 'Centennial Warehouse (under usufruct agreement w/ FTI)'),
(324, 'Region VII', 'Cebu Branch Office', 'Cebu', 'GID I / Warehouse-19'),
(325, 'Region VII', 'Cebu Branch Office', 'Cebu', 'GID II / Bogo Unit Warehouse (proposed for major repair)'),
(326, 'Region VII', 'Cebu Branch Office', 'Cebu', 'GID III / Tudela Unit Warehouse (proposed for major repair)'),
(327, 'Region VII', 'Cebu Branch Office', 'Cebu', 'GID IV / Badian Unit Warehouse (under MAFIM repairs)'),
(328, 'Region VII', 'Cebu Branch Office', 'Cebu', 'GID V / Sta Fe Unit Warehouse (under MAFIM repairs)'),
(330, 'Region VII', 'Cebu Branch Office', 'Cebu', 'GID VIII Warehouse (under MAFIM repairs)'),
(329, 'Region VII', 'Cebu Branch Office', 'Cebu', 'Warehouse # 46 (under MAFIM repairs)'),
(332, 'Region VII', 'Cebu Branch Office', 'Cebu', 'Warehouse # 9'),
(345, 'Region VII', 'Negros Oriental Branch Office', 'Negros Oriental', 'Daichi Ricemill'),
(344, 'Region VII', 'Negros Oriental Branch Office', 'Negros Oriental', 'GID I Dumaguete'),
(346, 'Region VII', 'Negros Oriental Branch Office', 'Negros Oriental', 'GID II Guihulngan'),
(343, 'Region VII', 'Negros Oriental Branch Office', 'Negros Oriental', 'Negros Oriental Branch Office'),
(347, 'Region VII', 'Negros Oriental Branch Office', 'Siquijor', 'GID Siquijor'),
(363, 'Region VIII', 'Leyte Branch Office', 'Biliran', 'GID Naval Warehouse'),
(364, 'Region VIII', 'Leyte Branch Office', 'Biliran', 'Naval PHF Shedhouse'),
(354, 'Region VIII', 'Leyte Branch Office', 'Leyte', 'Alangalang Maruyama Dryer'),
(356, 'Region VIII', 'Leyte Branch Office', 'Leyte', 'Alangalang Warehouse- Ricemill'),
(353, 'Region VIII', 'Leyte Branch Office', 'Leyte', 'Alangalang Warehouse-Millhouse'),
(372, 'Region VIII', 'Leyte Branch Office', 'Leyte', 'Delta Leased Warehouse'),
(358, 'Region VIII', 'Leyte Branch Office', 'Leyte', 'Dryers Modernized warehouse (on-going construction under MAFIM Program)'),
(370, 'Region VIII', 'Leyte Branch Office', 'Leyte', 'FLGC LSU Mechanical Dryer'),
(369, 'Region VIII', 'Leyte Branch Office', 'Leyte', 'FLGC Warehouse'),
(351, 'Region VIII', 'Leyte Branch Office', 'Leyte', 'GID Alangalang Warehouse I'),
(352, 'Region VIII', 'Leyte Branch Office', 'Leyte', 'GID Alangalang Warehouse II'),
(361, 'Region VIII', 'Leyte Branch Office', 'Leyte', 'GID Baybay Warehouse'),
(359, 'Region VIII', 'Leyte Branch Office', 'Leyte', 'GID Cogon Warehouse'),
(368, 'Region VIII', 'Leyte Branch Office', 'Leyte', 'GID Maasin Warehouse'),
(350, 'Region VIII', 'Leyte Branch Office', 'Leyte', 'GID Port Area Warehouse'),
(360, 'Region VIII', 'Leyte Branch Office', 'Leyte', 'GID San Pablo Warehouse'),
(366, 'Region VIII', 'Leyte Branch Office', 'Leyte', 'JICA Warehouse'),
(371, 'Region VIII', 'Leyte Branch Office', 'Leyte', 'JK  Leased Warehouse'),
(349, 'Region VIII', 'Leyte Branch Office', 'Leyte', 'Leyte Branch Office'),
(355, 'Region VIII', 'Leyte Branch Office', 'Leyte', 'Modernized Warehouse (on-going construction under MAFIM Program)'),
(367, 'Region VIII', 'Leyte Branch Office', 'Leyte', 'NFA Maasin Satellite Office'),
(365, 'Region VIII', 'Leyte Branch Office', 'Leyte', 'NFA Naval Mechanical Dryers'),
(362, 'Region VIII', 'Leyte Branch Office', 'Leyte', 'NFA Naval satellite Office'),
(357, 'Region VIII', 'Leyte Branch Office', 'Leyte', 'Ricemill for Modernized Warehouse (on-going construction under MAFIM Program)'),
(348, 'Region VIII', 'Regional Office', 'Leyte', 'Regional Office'),
(382, 'Region VIII', 'Samar Branch Office', 'Samar', 'Borongan Satellite Office'),
(387, 'Region VIII', 'Samar Branch Office', 'Samar', 'Catbalogan Dryer house'),
(385, 'Region VIII', 'Samar Branch Office', 'Samar', 'Catbalogan Satellite Office'),
(375, 'Region VIII', 'Samar Branch Office', 'Samar', 'GID Bobon Warehouse'),
(381, 'Region VIII', 'Samar Branch Office', 'Samar', 'GID Borongan Warehouse'),
(388, 'Region VIII', 'Samar Branch Office', 'Samar', 'GID Calbayog Warehouse'),
(386, 'Region VIII', 'Samar Branch Office', 'Samar', 'GID Catbalogan Warehouse'),
(378, 'Region VIII', 'Samar Branch Office', 'Samar', 'GID Catubig Warehouse'),
(380, 'Region VIII', 'Samar Branch Office', 'Samar', 'GID Guiuan Warehouse'),
(383, 'Region VIII', 'Samar Branch Office', 'Samar', 'GID Oras Warehouse'),
(376, 'Region VIII', 'Samar Branch Office', 'Samar', 'GID Rawis Warehouse'),
(373, 'Region VIII', 'Samar Branch Office', 'Samar', 'Leased Warehouse'),
(379, 'Region VIII', 'Samar Branch Office', 'Samar', 'Maruyama Dryer'),
(384, 'Region VIII', 'Samar Branch Office', 'Samar', 'Modernized Warehouse (on-going construction under MAFIM Program)'),
(377, 'Region VIII', 'Samar Branch Office', 'Samar', 'NFA-Owned Duplex Warehouse'),
(374, 'Region VIII', 'Samar Branch Office', 'Samar', 'Northern Samar Branch Office'),
(436, 'Region X', 'Bukidnon Branch Office', 'Bukidnon', 'Alheed Batch Recirculating Mech. Grain Dryer (1 Unit)'),
(434, 'Region X', 'Bukidnon Branch Office', 'Bukidnon', 'Alheed Batch Recirculating Mech. Grain Dryer (2 Units)'),
(425, 'Region X', 'Bukidnon Branch Office', 'Bukidnon', 'GID Aglayan Warehouse'),
(430, 'Region X', 'Bukidnon Branch Office', 'Bukidnon', 'GID Kalilangan Warehouse'),
(429, 'Region X', 'Bukidnon Branch Office', 'Bukidnon', 'GID Maramag Annex Warehouse'),
(428, 'Region X', 'Bukidnon Branch Office', 'Bukidnon', 'GID Maramag Main Warehouse'),
(427, 'Region X', 'Bukidnon Branch Office', 'Bukidnon', 'GID Musuan Warehouse'),
(426, 'Region X', 'Bukidnon Branch Office', 'Bukidnon', 'GID Valencia Warehouse'),
(432, 'Region X', 'Bukidnon Branch Office', 'Bukidnon', 'GID Wao INF Warehouse'),
(431, 'Region X', 'Bukidnon Branch Office', 'Bukidnon', 'GID Wao Warehouse'),
(437, 'Region X', 'Bukidnon Branch Office', 'Bukidnon', 'Greenmac Batch Recirculating Mech. Grain Dryer (3 Units)'),
(438, 'Region X', 'Bukidnon Branch Office', 'Bukidnon', 'Mechaphil Batch Recirculating Mech. Grain Dryer (2 Units)'),
(433, 'Region X', 'Bukidnon Branch Office', 'Bukidnon', 'Mechaphil Batch Recirculating Mech. Grain Dryer (3 Units)'),
(424, 'Region X', 'Bukidnon Branch Office', 'Bukidnon', 'NFA Bukidnon Branch Office'),
(435, 'Region X', 'Bukidnon Branch Office', 'Bukidnon', 'Shin Neung Batch Recirculating Mech. Grain Dryer (1 Unit)'),
(448, 'Region X', 'Lanao del Norte Branch Office', 'Lanao Del Norte', 'GID 1 Iligan Warehouse'),
(450, 'Region X', 'Lanao del Norte Branch Office', 'Lanao Del Norte', 'GID 2 Lala Warehouse'),
(452, 'Region X', 'Lanao del Norte Branch Office', 'Lanao Del Norte', 'Hansung Recirculating Mech. Grain Dryer 6TPB'),
(453, 'Region X', 'Lanao del Norte Branch Office', 'Lanao Del Norte', 'LSU Agricom Recirculating Mech. Grain Dryer 6TB'),
(447, 'Region X', 'Lanao del Norte Branch Office', 'Lanao Del Norte', 'NFA Lanao del Norte Branch Office'),
(451, 'Region X', 'Lanao del Norte Branch Office', 'Lanao Del Norte', 'Suncue Recirculating Mech. Grain Dryer 6TPB'),
(449, 'Region X', 'Lanao del Norte Branch Office', 'Lanao Del Norte', 'Zemic Truckscale 60T'),
(454, 'Region X', 'Lanao del Norte Branch Office', 'Misamis Occidental', 'GID 1 Ozamis Warehouse'),
(455, 'Region X', 'Lanao del Norte Branch Office', 'Misamis Occidental', 'GID 2 Ozamis Warehouse'),
(456, 'Region X', 'Lanao del Norte Branch Office', 'Misamis Occidental', 'GID 3 Ozamis Warehouse'),
(458, 'Region X', 'Lanao del Norte Branch Office', 'Misamis Occidental', 'Mega Sun Recirculating Mech. Grain Dryer 6TPB'),
(459, 'Region X', 'Lanao del Norte Branch Office', 'Misamis Occidental', 'Sato Ricemill 150 KG/HR'),
(457, 'Region X', 'Lanao del Norte Branch Office', 'Misamis Occidental', 'Suncue Recirculating Mech. Grain Dryer 6TPB'),
(460, 'Region X', 'Lanao del Norte Branch Office', 'Misamis Occidental', 'Zemic Truckscale 60T'),
(440, 'Region X', 'Misamis Oriental Branch Office', 'Cagayan De Oro', 'GID 1, Patag Warehouse'),
(441, 'Region X', 'Misamis Oriental Branch Office', 'Cagayan De Oro', 'GID 2, Baloy Warehouse'),
(442, 'Region X', 'Misamis Oriental Branch Office', 'Cagayan De Oro', 'GID 3, Baloy Warehouse'),
(443, 'Region X', 'Misamis Oriental Branch Office', 'Cagayan De Oro', 'GID 4, Baloy Warehouse'),
(439, 'Region X', 'Misamis Oriental Branch Office', 'Cagayan De Oro', 'NFA Misamis Oriental Branch Office'),
(445, 'Region X', 'Misamis Oriental Branch Office', 'Camiguin', '20TCC Warehouse'),
(446, 'Region X', 'Misamis Oriental Branch Office', 'Camiguin', 'FLGC Warehouse'),
(444, 'Region X', 'Misamis Oriental Branch Office', 'Camiguin', 'NFA Camiguin Sub- Office'),
(461, 'Region X', 'Regional Office X', 'Cagayan De Oro, Misamis Oriental', 'Regional Office'),
(480, 'Region XI', 'Davao del Norte', 'Davao Del Norte', 'Davao del Norte Branch Office'),
(485, 'Region XI', 'Davao del Norte', 'Davao Del Norte', 'FLGC Maragusan Warehouse'),
(481, 'Region XI', 'Davao del Norte', 'Davao Del Norte', 'GID 1 Warehouse'),
(482, 'Region XI', 'Davao del Norte', 'Davao Del Norte', 'GID 2 Warehouse'),
(483, 'Region XI', 'Davao del Norte', 'Davao Del Norte', 'GID Compostela Warehouse'),
(490, 'Region XI', 'Davao del Norte', 'Davao Del Norte', 'LSU Mechanical Dryer'),
(487, 'Region XI', 'Davao del Norte', 'Davao Del Norte', 'MAFIM Warehouse (on-going)'),
(488, 'Region XI', 'Davao del Norte', 'Davao Del Norte', 'Maruyama Mechanical Dryer'),
(486, 'Region XI', 'Davao del Norte', 'Davao Del Norte', 'Nabunturan Office'),
(489, 'Region XI', 'Davao del Norte', 'Davao Del Norte', 'Shin Heung Mechanical Dryer'),
(484, 'Region XI', 'Davao del Norte', 'Davao Del Norte', 'Sto. Tomas Warehouse'),
(491, 'Region XI', 'Davao del Norte', 'Davao Del Norte', 'Toledo Truck Scale'),
(492, 'Region XI', 'Davao del Norte', 'Davao Del Norte', 'Weightronix Truck Scale'),
(463, 'Region XI', 'Davao Del Sur Branch Office', 'Davao Del Sur', 'Davao del Sur Branch Office'),
(471, 'Region XI', 'Davao Del Sur Branch Office', 'Davao Del Sur', 'Dryer House'),
(466, 'Region XI', 'Davao Del Sur Branch Office', 'Davao Del Sur', 'GID Warehouse'),
(468, 'Region XI', 'Davao Del Sur Branch Office', 'Davao Del Sur', 'MAFIM Warehouse (on-going construction)'),
(467, 'Region XI', 'Davao Del Sur Branch Office', 'Davao Del Sur', 'OLD Warehouse'),
(464, 'Region XI', 'Davao Del Sur Branch Office', 'Davao Del Sur', 'Santa Ana Office'),
(469, 'Region XI', 'Davao Del Sur Branch Office', 'Davao Del Sur', 'Santa Ana Warehouse'),
(470, 'Region XI', 'Davao Del Sur Branch Office', 'Davao Del Sur', 'Satake Rice Mill'),
(465, 'Region XI', 'Davao Del Sur Branch Office', 'Davao Del Sur', 'Triplex Warehouse'),
(472, 'Region XI', 'Davao Del Sur Branch Office', 'Davao Del Sur', 'Truckscale'),
(478, 'Region XI', 'Davao Oriental Branch Office', 'Davao Del Sur', 'Baganga Buying Station'),
(473, 'Region XI', 'Davao Oriental Branch Office', 'Davao Del Sur', 'Davao Oriental Branch Office'),
(475, 'Region XI', 'Davao Oriental Branch Office', 'Davao Del Sur', 'GID 1 WAREHOUSE'),
(476, 'Region XI', 'Davao Oriental Branch Office', 'Davao Del Sur', 'GID 2 WAREHOUSE'),
(474, 'Region XI', 'Davao Oriental Branch Office', 'Davao Del Sur', 'GID 3 warehouse'),
(477, 'Region XI', 'Davao Oriental Branch Office', 'Davao Del Sur', 'GID 7 WAREHOUSE'),
(479, 'Region XI', 'Davao Oriental Branch Office', 'Davao Del Sur', 'LSU TYPE MECH. DRYER WITH DRYER HOUSE'),
(462, 'Region XI', 'Regional Office XI', 'Davao Del Sur', 'Regional Office XI'),
(518, 'Region XII', 'North Cotabato', 'North Cotabato', 'GID 1 KIDAPAWAN'),
(523, 'Region XII', 'North Cotabato', 'North Cotabato', 'GID 2 M\'LANG'),
(519, 'Region XII', 'North Cotabato', 'North Cotabato', 'GID 3 KIDAPAWAN'),
(522, 'Region XII', 'North Cotabato', 'North Cotabato', 'GID 4 M\'LANG'),
(520, 'Region XII', 'North Cotabato', 'North Cotabato', 'GID 5 KIDAPAWAN'),
(524, 'Region XII', 'North Cotabato', 'North Cotabato', 'GID 6 KABACAN'),
(528, 'Region XII', 'North Cotabato', 'North Cotabato', 'GID 7 BAGUER'),
(529, 'Region XII', 'North Cotabato', 'North Cotabato', 'GID 8 KILADA'),
(533, 'Region XII', 'North Cotabato', 'North Cotabato', 'Mechanical Dryer (MARUYAMA)'),
(535, 'Region XII', 'North Cotabato', 'North Cotabato', 'Mechanical Dryer (PHILMEC)'),
(532, 'Region XII', 'North Cotabato', 'North Cotabato', 'Mechanical Dryer (SHIN HUENG)'),
(534, 'Region XII', 'North Cotabato', 'North Cotabato', 'Mechanical Dryer (SUNCUE)'),
(521, 'Region XII', 'North Cotabato', 'North Cotabato', 'MILL HOUSE KIDAPAWAN'),
(526, 'Region XII', 'North Cotabato', 'North Cotabato', 'NFA OWNED 1 KIDAPAWAN'),
(527, 'Region XII', 'North Cotabato', 'North Cotabato', 'NFA OWNED 2 M\'LANG'),
(525, 'Region XII', 'North Cotabato', 'North Cotabato', 'NFA OWNED 4 BAGUER'),
(531, 'Region XII', 'North Cotabato', 'North Cotabato', 'NORTH COTABATO BRANCH OFFICE'),
(543, 'Region XII', 'North Cotabato', 'North Cotabato', 'Ricemill (SATAKE Multi-pass)'),
(546, 'Region XII', 'North Cotabato', 'North Cotabato', 'Truck Scale (Mettler Toledo)'),
(530, 'Region XII', 'Regional Office XII', 'South Cotabato', 'REGIONAL OFFICE 12 BLDG.'),
(493, 'Region XII', 'South Cotabato', 'South Cotabato', 'FLGC Banga'),
(495, 'Region XII', 'South Cotabato', 'South Cotabato', 'GID 1 GENERAL SANTOS CITY'),
(496, 'Region XII', 'South Cotabato', 'South Cotabato', 'GID 2 GENERAL SANTOS CITY'),
(494, 'Region XII', 'South Cotabato', 'South Cotabato', 'GID 3 GENERAL SANTOS CITY'),
(497, 'Region XII', 'South Cotabato', 'South Cotabato', 'GID 4 GENERAL SANTOS CITY'),
(502, 'Region XII', 'South Cotabato', 'South Cotabato', 'GID 5 MAITUM'),
(500, 'Region XII', 'South Cotabato', 'South Cotabato', 'IBG KORONADAL CITY'),
(504, 'Region XII', 'South Cotabato', 'South Cotabato', 'MLGC SURALLAH'),
(503, 'Region XII', 'South Cotabato', 'South Cotabato', 'NABCOR BANGA'),
(498, 'Region XII', 'South Cotabato', 'South Cotabato', 'NFA 1 GENERAL SANTOS CITY'),
(501, 'Region XII', 'South Cotabato', 'South Cotabato', 'NFA 1 KORONADAL CITY'),
(499, 'Region XII', 'South Cotabato', 'South Cotabato', 'NFA 2 GENERAL SANTOS CITY'),
(506, 'Region XII', 'South Cotabato', 'South Cotabato', 'NFA Gen. Santos Main Building'),
(505, 'Region XII', 'South Cotabato', 'South Cotabato', 'SCBO Koronadal City'),
(544, 'Region XII', 'South Cotabato', 'South Cotabato', 'Truck Scale (Mettler Toledo)'),
(545, 'Region XII', 'South Cotabato', 'South Cotabato', 'Truck Scale (Weightronix)'),
(540, 'Region XII', 'Sultan Kudarat', 'Sultan Kudarat', 'Mechanical Dryer (GREENMAC)'),
(538, 'Region XII', 'Sultan Kudarat', 'Sultan Kudarat', 'Mechanical Dryer (LSU-AGRICOM)'),
(536, 'Region XII', 'Sultan Kudarat', 'Sultan Kudarat', 'Mechanical Dryer (SATAKE)'),
(539, 'Region XII', 'Sultan Kudarat', 'Sultan Kudarat', 'Mechanical Dryer (SHIN HUENG)'),
(537, 'Region XII', 'Sultan Kudarat', 'Sultan Kudarat', 'Mechanical Dryer (SUNCUE I)'),
(541, 'Region XII', 'Sultan Kudarat', 'Sultan Kudarat', 'Mechanical Dryer (SUNCUE)'),
(508, 'Region XII', 'Sultan Kudarat', 'Sultan Kudarat', 'NFA GID 1 LEBAK'),
(509, 'Region XII', 'Sultan Kudarat', 'Sultan Kudarat', 'NFA GID 2 LEBAK'),
(510, 'Region XII', 'Sultan Kudarat', 'Sultan Kudarat', 'NFA GID TRIPLEX ISULAN, (GID 1, GID 2, GID 3)'),
(507, 'Region XII', 'Sultan Kudarat', 'Sultan Kudarat', 'NFA SKBO ISULAN'),
(517, 'Region XII', 'Sultan Kudarat', 'Sultan Kudarat', 'NFA SPGC DUPLEX (GID7)'),
(516, 'Region XII', 'Sultan Kudarat', 'Sultan Kudarat', 'NFA SPGC GID 8'),
(511, 'Region XII', 'Sultan Kudarat', 'Sultan Kudarat', 'NFA SPGC QUINTUPLEX (GID 1)'),
(512, 'Region XII', 'Sultan Kudarat', 'Sultan Kudarat', 'NFA SPGC QUINTUPLEX (GID 2)'),
(513, 'Region XII', 'Sultan Kudarat', 'Sultan Kudarat', 'NFA SPGC QUINTUPLEX (GID 3)'),
(514, 'Region XII', 'Sultan Kudarat', 'Sultan Kudarat', 'NFA SPGC QUINTUPLEX (GID 4)'),
(515, 'Region XII', 'Sultan Kudarat', 'Sultan Kudarat', 'NFA SPGC QUINTUPLEX (GID 5)'),
(542, 'Region XII', 'Sultan Kudarat', 'Sultan Kudarat', 'Ricemill (BUHLER MIAG Multi-pass)'),
(547, 'Region XII', 'Sultan Kudarat', 'Sultan Kudarat', 'Truck Scale (Mettler Toledo)'),
(548, 'Region XII', 'Sultan Kudarat', 'Sultan Kudarat', 'Truck Scale (Weightronix)');

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

DROP TABLE IF EXISTS `notifications`;
CREATE TABLE `notifications` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `message` varchar(255) NOT NULL,
  `target_url` varchar(255) DEFAULT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `user_id`, `message`, `target_url`, `is_read`, `created_at`) VALUES
(1, 2, 'Review new warehouse submissions for approval.', NULL, 0, '2026-06-25 13:45:17'),
(2, 2, 'Two seed farmer records are ready for reporting.', NULL, 0, '2026-06-25 13:45:17'),
(3, 1, 'New user registration is pending activation.', 'index.php?page=users', 1, '2026-07-22 03:14:48'),
(4, 2, 'New user registration is pending activation.', 'index.php?page=users', 0, '2026-07-22 03:14:48'),
(5, 3, 'New user registration is pending activation.', 'index.php?page=users', 0, '2026-07-22 03:14:48'),
(6, 4, 'New user registration is pending activation.', 'index.php?page=users', 0, '2026-07-22 03:14:48'),
(7, 5, 'New user registration is pending activation.', 'index.php?page=users', 0, '2026-07-22 03:14:48'),
(8, 6, 'New user registration is pending activation.', 'index.php?page=users', 0, '2026-07-22 03:14:48');

-- --------------------------------------------------------

--
-- Table structure for table `province_offices`
--

DROP TABLE IF EXISTS `province_offices`;
CREATE TABLE `province_offices` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `branch_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(160) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `province_offices`
--

INSERT INTO `province_offices` (`id`, `branch_id`, `name`) VALUES
(1, 1, 'Nueva Ecija'),
(2, 2, 'Basilan'),
(3, 2, 'Bongao'),
(4, 2, 'Isabela City'),
(5, 2, 'Patikul, Sulu'),
(6, 2, 'Sulu'),
(7, 2, 'Tawi-Tawi'),
(8, 3, 'Lanao Del Sur'),
(9, 3, 'Marawi City'),
(10, 4, 'Cotabato City'),
(11, 4, 'Maguindanao Del Sur'),
(12, 4, 'Sultan Kudarat'),
(13, 5, 'Cotabato City'),
(14, 6, 'Agusan Del Norte'),
(15, 6, 'Agusan Del Sur'),
(16, 7, 'Agusan Del Norte'),
(17, 8, 'Province Of Dinagat Islands'),
(18, 8, 'Surigao Del Norte'),
(19, 8, 'Surigao Del Sur'),
(20, 9, 'Batanes'),
(21, 9, 'Metro Manila'),
(22, 9, 'Valenzuela'),
(23, 10, 'Antipolo, Rizal'),
(24, 10, 'Cavite'),
(25, 10, 'Marikina'),
(26, 10, 'Metro Manila'),
(27, 10, 'Rizal'),
(28, 11, 'Metro Manila'),
(29, 12, 'Eastern Pangasinan'),
(30, 12, 'Western Pangasinan'),
(31, 13, 'Abra'),
(32, 13, 'Ilocos Norte'),
(33, 13, 'Ilocos Sur'),
(34, 14, 'Benguet'),
(35, 14, 'La Union'),
(36, 15, 'La Union'),
(37, 16, 'Apayao'),
(38, 16, 'Cagayan'),
(39, 16, 'Kalinga'),
(40, 17, 'Isabela'),
(41, 18, 'Isabela'),
(42, 19, 'Ifugao'),
(43, 19, 'Mountain Province'),
(44, 19, 'Nueva Vizcaya'),
(45, 19, 'Quirino'),
(46, 20, 'Bulacan'),
(47, 21, 'Aurora'),
(48, 21, 'Nueva Ecija'),
(49, 22, 'Bataan'),
(50, 22, 'Pampanga'),
(51, 22, 'Zambales'),
(52, 23, 'Nueva Ecija'),
(53, 24, 'Tarlac'),
(54, 25, 'Batangas'),
(55, 26, 'Laguna'),
(56, 27, 'Occidental Mindoro'),
(57, 28, 'Oriental Mindoro'),
(58, 29, 'Palawan'),
(59, 30, 'Quezon'),
(60, 31, 'Batangas'),
(61, 32, 'Zamboanga Del Sur'),
(62, 33, 'Zamboanga Del Norte'),
(63, 33, 'Zamboanga Del Sur'),
(64, 34, 'Albay'),
(65, 35, 'Camarines Sur'),
(66, 36, 'Albay'),
(67, 37, 'Masbate'),
(68, 37, 'Sorsogon'),
(69, 38, 'Aklan'),
(70, 38, 'Capiz'),
(71, 39, 'Antique'),
(72, 39, 'Guimaras'),
(73, 39, 'Iloilo'),
(74, 40, 'Negros Occ'),
(75, 41, 'Iloilo'),
(76, 42, 'Bohol'),
(77, 43, 'Cebu'),
(78, 44, 'Negros Oriental'),
(79, 44, 'Siquijor'),
(80, 45, 'Biliran'),
(81, 45, 'Leyte'),
(82, 46, 'Leyte'),
(83, 47, 'Samar'),
(84, 48, 'Bukidnon'),
(85, 49, 'Lanao Del Norte'),
(86, 49, 'Misamis Occidental'),
(87, 50, 'Cagayan De Oro'),
(88, 50, 'Camiguin'),
(89, 51, 'Cagayan De Oro, Misamis Oriental'),
(98, 52, 'Davao de Oro'),
(90, 52, 'Davao Del Norte'),
(91, 53, 'Davao Del Sur'),
(92, 54, 'Davao Del Sur'),
(93, 55, 'Davao Del Sur'),
(94, 56, 'North Cotabato'),
(95, 57, 'South Cotabato'),
(96, 58, 'South Cotabato'),
(97, 59, 'Sultan Kudarat');

-- --------------------------------------------------------

--
-- Table structure for table `regions`
--

DROP TABLE IF EXISTS `regions`;
CREATE TABLE `regions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(120) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `regions`
--

INSERT INTO `regions` (`id`, `name`) VALUES
(16, 'ARMM'),
(17, 'CARAGA'),
(18, 'NCR'),
(19, 'Region I'),
(20, 'Region II'),
(21, 'Region III'),
(22, 'Region IV'),
(23, 'Region IX'),
(24, 'Region V'),
(25, 'Region VI'),
(26, 'Region VII'),
(27, 'Region VIII'),
(28, 'Region X'),
(29, 'Region XI'),
(30, 'Region XII'),
(13, 'Region XIII'),
(14, 'Region XIV'),
(15, 'Region XV');

-- --------------------------------------------------------

--
-- Table structure for table `report_signatories`
--

DROP TABLE IF EXISTS `report_signatories`;
CREATE TABLE `report_signatories` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `full_name` varchar(160) NOT NULL,
  `designation` varchar(160) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `report_signatories`
--

INSERT INTO `report_signatories` (`id`, `user_id`, `full_name`, `designation`, `created_at`) VALUES
(2, 3, 'Signatory 1', 'Officer III', '2026-06-30 01:56:42');

-- --------------------------------------------------------

--
-- Table structure for table `support_tickets`
--

DROP TABLE IF EXISTS `support_tickets`;
CREATE TABLE `support_tickets` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `reporter_id` bigint(20) UNSIGNED NOT NULL,
  `title` varchar(180) NOT NULL,
  `category` varchar(80) NOT NULL,
  `description` text NOT NULL,
  `screenshot_path` varchar(255) DEFAULT NULL,
  `status` varchar(30) NOT NULL DEFAULT 'Open',
  `reporter_archived` tinyint(1) NOT NULL DEFAULT 0,
  `admin_archived` tinyint(1) NOT NULL DEFAULT 0,
  `resolved_by` bigint(20) UNSIGNED DEFAULT NULL,
  `resolved_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `support_ticket_messages`
--

DROP TABLE IF EXISTS `support_ticket_messages`;
CREATE TABLE `support_ticket_messages` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `ticket_id` bigint(20) UNSIGNED NOT NULL,
  `sender_id` bigint(20) UNSIGNED NOT NULL,
  `message` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `transactions`
--

DROP TABLE IF EXISTS `transactions`;
CREATE TABLE `transactions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `seller_type` enum('Individual','Farmer Organization') NOT NULL,
  `procurement_type` enum('In-Warehouse','Mobile Procurement') NOT NULL,
  `farmer_id` bigint(20) UNSIGNED DEFAULT NULL,
  `farmer_organization_id` bigint(20) UNSIGNED DEFAULT NULL,
  `representative_name` varchar(180) DEFAULT NULL,
  `total_members` int(10) UNSIGNED DEFAULT NULL,
  `verified_farm_area` decimal(10,2) DEFAULT NULL,
  `delivery_date` date NOT NULL,
  `warehouse_stock_receipt_number` varchar(80) NOT NULL,
  `price_per_kilogram` decimal(10,2) NOT NULL,
  `net_kilogram` decimal(12,2) NOT NULL,
  `bags_50kg` int(10) UNSIGNED NOT NULL,
  `warehouse_id` bigint(20) UNSIGNED DEFAULT NULL,
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_ip_group_delivery` tinyint(1) NOT NULL DEFAULT 0,
  `total_cost` decimal(20,2) GENERATED ALWAYS AS (round(`price_per_kilogram` * `net_kilogram`,2)) STORED,
  `client_control_number` varchar(96) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `transactions`
--

INSERT INTO `transactions` (`id`, `seller_type`, `procurement_type`, `farmer_id`, `farmer_organization_id`, `representative_name`, `total_members`, `verified_farm_area`, `delivery_date`, `warehouse_stock_receipt_number`, `price_per_kilogram`, `net_kilogram`, `bags_50kg`, `warehouse_id`, `created_by`, `created_at`, `is_ip_group_delivery`, `client_control_number`) VALUES
(1, 'Individual', 'In-Warehouse', 1, 1, NULL, NULL, 2.40, '2026-06-25', 'WSR-2026-0001', 23.00, 2400.00, 48, NULL, 2, '2026-06-25 13:45:17', 0, NULL),
(2, 'Individual', 'Mobile Procurement', 2, 2, NULL, NULL, 1.70, '2026-06-25', 'WSR-2026-0002', 23.00, 1700.00, 34, NULL, 2, '2026-06-25 13:45:17', 0, NULL),
(3, 'Farmer Organization', 'In-Warehouse', NULL, 1, 'Alma Reyes', 5, 7.50, '2026-01-16', 'FULLLIST-FO-01-Q1', 23.00, 6500.00, 130, 1, 1, '2026-06-25 13:49:51', 0, NULL),
(4, 'Farmer Organization', 'Mobile Procurement', NULL, 1, 'Alma Reyes', 5, 7.85, '2026-04-18', 'FULLLIST-FO-01-Q2', 23.25, 6925.00, 139, 1, 1, '2026-06-25 13:49:51', 0, NULL),
(5, 'Farmer Organization', 'In-Warehouse', NULL, 1, 'Alma Reyes', 5, 8.20, '2026-07-20', 'FULLLIST-FO-01-Q3', 23.50, 7350.00, 147, 1, 1, '2026-06-25 13:49:51', 0, NULL),
(6, 'Farmer Organization', 'Mobile Procurement', NULL, 1, 'Alma Reyes', 5, 8.55, '2026-10-22', 'FULLLIST-FO-01-Q4', 23.75, 7775.00, 156, 1, 1, '2026-06-25 13:49:51', 0, NULL),
(7, 'Farmer Organization', 'In-Warehouse', NULL, 2, 'Bernardo Cruz', 5, 8.50, '2026-01-16', 'FULLLIST-FO-02-Q1', 23.00, 7050.00, 141, 1, 1, '2026-06-25 13:49:51', 0, NULL),
(8, 'Farmer Organization', 'Mobile Procurement', NULL, 2, 'Bernardo Cruz', 5, 8.85, '2026-04-18', 'FULLLIST-FO-02-Q2', 23.25, 7475.00, 150, 1, 1, '2026-06-25 13:49:51', 0, NULL),
(9, 'Farmer Organization', 'In-Warehouse', NULL, 2, 'Bernardo Cruz', 5, 9.20, '2026-07-20', 'FULLLIST-FO-02-Q3', 23.50, 7900.00, 158, 1, 1, '2026-06-25 13:49:51', 0, NULL),
(10, 'Farmer Organization', 'Mobile Procurement', NULL, 2, 'Bernardo Cruz', 5, 9.55, '2026-10-22', 'FULLLIST-FO-02-Q4', 23.75, 8325.00, 167, 1, 1, '2026-06-25 13:49:51', 0, NULL),
(11, 'Farmer Organization', 'In-Warehouse', NULL, 5, 'Carina Santos', 5, 9.50, '2026-01-16', 'FULLLIST-FO-03-Q1', 23.00, 7600.00, 152, 1, 1, '2026-06-25 13:49:51', 0, NULL),
(12, 'Farmer Organization', 'Mobile Procurement', NULL, 5, 'Carina Santos', 5, 9.85, '2026-04-18', 'FULLLIST-FO-03-Q2', 23.25, 8025.00, 161, 1, 1, '2026-06-25 13:49:51', 0, NULL),
(13, 'Farmer Organization', 'In-Warehouse', NULL, 5, 'Carina Santos', 5, 10.20, '2026-07-20', 'FULLLIST-FO-03-Q3', 23.50, 8450.00, 169, 1, 1, '2026-06-25 13:49:51', 0, NULL),
(14, 'Farmer Organization', 'Mobile Procurement', NULL, 5, 'Carina Santos', 5, 10.55, '2026-10-22', 'FULLLIST-FO-03-Q4', 23.75, 8875.00, 178, 1, 1, '2026-06-25 13:49:51', 0, NULL),
(15, 'Farmer Organization', 'In-Warehouse', NULL, 6, 'Danilo Garcia', 5, 10.50, '2026-01-16', 'FULLLIST-FO-04-Q1', 23.00, 8150.00, 163, 1, 1, '2026-06-25 13:49:51', 0, NULL),
(16, 'Farmer Organization', 'Mobile Procurement', NULL, 6, 'Danilo Garcia', 5, 10.85, '2026-04-18', 'FULLLIST-FO-04-Q2', 23.25, 8575.00, 172, 1, 1, '2026-06-25 13:49:51', 0, NULL),
(17, 'Farmer Organization', 'In-Warehouse', NULL, 6, 'Danilo Garcia', 5, 11.20, '2026-07-20', 'FULLLIST-FO-04-Q3', 23.50, 9000.00, 180, 1, 1, '2026-06-25 13:49:51', 0, NULL),
(18, 'Farmer Organization', 'Mobile Procurement', NULL, 6, 'Danilo Garcia', 5, 11.55, '2026-10-22', 'FULLLIST-FO-04-Q4', 23.75, 9425.00, 189, 1, 1, '2026-06-25 13:49:51', 0, NULL),
(35, 'Individual', 'In-Warehouse', 43, NULL, NULL, NULL, 1.60, '2026-01-05', 'SEED-IND-2026-001', 23.25, 1285.00, 26, 2, 3, '2026-06-25 13:52:29', 0, NULL),
(36, 'Individual', 'Mobile Procurement', 44, NULL, NULL, NULL, 1.70, '2026-01-06', 'SEED-IND-2026-002', 23.50, 1370.00, 27, 21, 3, '2026-06-25 13:52:29', 0, NULL),
(37, 'Individual', 'In-Warehouse', 45, NULL, NULL, NULL, 1.80, '2026-01-07', 'SEED-IND-2026-003', 23.00, 1455.00, 29, 41, 3, '2026-06-25 13:52:29', 0, NULL),
(38, 'Individual', 'Mobile Procurement', 46, NULL, NULL, NULL, 1.90, '2026-01-08', 'SEED-IND-2026-004', 23.25, 1540.00, 31, 61, 3, '2026-06-25 13:52:29', 0, NULL),
(39, 'Individual', 'In-Warehouse', 47, NULL, NULL, NULL, 2.00, '2026-01-09', 'SEED-IND-2026-005', 23.50, 1625.00, 33, 81, 3, '2026-06-25 13:52:29', 0, NULL),
(40, 'Individual', 'Mobile Procurement', 48, NULL, NULL, NULL, 2.10, '2026-01-10', 'SEED-IND-2026-006', 23.00, 1710.00, 34, 101, 3, '2026-06-25 13:52:29', 0, NULL),
(41, 'Individual', 'In-Warehouse', 49, NULL, NULL, NULL, 2.20, '2026-01-11', 'SEED-IND-2026-007', 23.25, 1795.00, 36, 121, 3, '2026-06-25 13:52:29', 0, NULL),
(42, 'Individual', 'Mobile Procurement', 50, NULL, NULL, NULL, 2.30, '2026-01-12', 'SEED-IND-2026-008', 23.50, 1880.00, 38, 140, 3, '2026-06-25 13:52:29', 0, NULL),
(43, 'Individual', 'In-Warehouse', 51, NULL, NULL, NULL, 2.40, '2026-01-13', 'SEED-IND-2026-009', 23.00, 1965.00, 39, 160, 3, '2026-06-25 13:52:29', 0, NULL),
(44, 'Individual', 'Mobile Procurement', 52, NULL, NULL, NULL, 2.50, '2026-01-14', 'SEED-IND-2026-010', 23.25, 2050.00, 41, 180, 3, '2026-06-25 13:52:29', 0, NULL),
(45, 'Individual', 'In-Warehouse', 53, NULL, NULL, NULL, 2.60, '2026-02-05', 'SEED-IND-2026-011', 23.50, 2135.00, 43, 200, 3, '2026-06-25 13:52:29', 0, NULL),
(46, 'Individual', 'Mobile Procurement', 54, NULL, NULL, NULL, 2.70, '2026-02-06', 'SEED-IND-2026-012', 23.00, 2220.00, 44, 220, 3, '2026-06-25 13:52:29', 0, NULL),
(47, 'Individual', 'In-Warehouse', 55, NULL, NULL, NULL, 2.80, '2026-02-07', 'SEED-IND-2026-013', 23.25, 2305.00, 46, 240, 3, '2026-06-25 13:52:29', 0, NULL),
(48, 'Individual', 'Mobile Procurement', 56, NULL, NULL, NULL, 2.90, '2026-02-08', 'SEED-IND-2026-014', 23.50, 2390.00, 48, 259, 3, '2026-06-25 13:52:29', 0, NULL),
(49, 'Individual', 'In-Warehouse', 57, NULL, NULL, NULL, 3.00, '2026-02-09', 'SEED-IND-2026-015', 23.00, 2475.00, 50, 279, 3, '2026-06-25 13:52:29', 0, NULL),
(50, 'Individual', 'Mobile Procurement', 58, NULL, NULL, NULL, 3.10, '2026-02-10', 'SEED-IND-2026-016', 23.25, 2560.00, 51, 299, 3, '2026-06-25 13:52:29', 0, NULL),
(51, 'Individual', 'In-Warehouse', 59, NULL, NULL, NULL, 3.20, '2026-02-11', 'SEED-IND-2026-017', 23.50, 2645.00, 53, 319, 3, '2026-06-25 13:52:29', 0, NULL),
(52, 'Individual', 'Mobile Procurement', 60, NULL, NULL, NULL, 3.30, '2026-02-12', 'SEED-IND-2026-018', 23.00, 2730.00, 55, 339, 3, '2026-06-25 13:52:29', 0, NULL),
(53, 'Individual', 'In-Warehouse', 61, NULL, NULL, NULL, 3.40, '2026-02-13', 'SEED-IND-2026-019', 23.25, 2815.00, 56, 359, 3, '2026-06-25 13:52:29', 0, NULL),
(54, 'Individual', 'Mobile Procurement', 62, NULL, NULL, NULL, 3.50, '2026-02-14', 'SEED-IND-2026-020', 23.50, 2900.00, 58, 378, 3, '2026-06-25 13:52:29', 0, NULL),
(55, 'Farmer Organization', 'In-Warehouse', NULL, 11, 'Seed FO Representative', 10, 18.75, '2026-03-15', 'SEED-FO-2026-001', 23.50, 15250.00, 305, 398, 3, '2026-06-25 13:52:29', 0, NULL),
(72, 'Individual', 'Mobile Procurement', 123, NULL, NULL, NULL, 1.20, '2026-05-01', 'WSR-WM000222-2026-001', 23.00, 900.00, 18, 49, 3, '2026-06-25 13:52:56', 0, NULL),
(73, 'Individual', 'In-Warehouse', 124, NULL, NULL, NULL, 1.48, '2026-05-02', 'WSR-WM000222-2026-002', 23.15, 1250.00, 25, 49, 3, '2026-06-25 13:52:56', 0, NULL),
(74, 'Individual', 'In-Warehouse', 125, NULL, NULL, NULL, 1.76, '2026-05-03', 'WSR-WM000222-2026-003', 23.30, 1600.00, 32, 49, 3, '2026-06-25 13:52:56', 0, NULL),
(75, 'Individual', 'Mobile Procurement', 126, NULL, NULL, NULL, 2.04, '2026-05-04', 'WSR-WM000222-2026-004', 23.45, 1950.00, 39, 49, 3, '2026-06-25 13:52:56', 0, NULL),
(76, 'Individual', 'In-Warehouse', 127, NULL, NULL, NULL, 2.32, '2026-05-05', 'WSR-WM000222-2026-005', 23.60, 2300.00, 46, 49, 3, '2026-06-25 13:52:56', 0, NULL),
(77, 'Individual', 'In-Warehouse', 128, NULL, NULL, NULL, 2.60, '2026-05-06', 'WSR-WM000222-2026-006', 23.00, 2650.00, 53, 49, 3, '2026-06-25 13:52:56', 0, NULL),
(78, 'Individual', 'Mobile Procurement', 129, NULL, NULL, NULL, 2.88, '2026-05-07', 'WSR-WM000222-2026-007', 23.15, 3000.00, 60, 49, 3, '2026-06-25 13:52:56', 0, NULL),
(79, 'Individual', 'In-Warehouse', 130, NULL, NULL, NULL, 3.16, '2026-05-08', 'WSR-WM000222-2026-008', 23.30, 3350.00, 67, 49, 3, '2026-06-25 13:52:56', 0, NULL),
(80, 'Individual', 'In-Warehouse', 131, NULL, NULL, NULL, 3.44, '2026-05-09', 'WSR-WM000222-2026-009', 23.45, 950.00, 19, 49, 3, '2026-06-25 13:52:56', 0, NULL),
(81, 'Individual', 'Mobile Procurement', 132, NULL, NULL, NULL, 3.72, '2026-05-10', 'WSR-WM000222-2026-010', 23.60, 1300.00, 26, 49, 3, '2026-06-25 13:52:56', 0, NULL),
(82, 'Individual', 'In-Warehouse', 133, NULL, NULL, NULL, 4.00, '2026-05-11', 'WSR-WM000222-2026-011', 23.00, 1650.00, 33, 49, 3, '2026-06-25 13:52:56', 0, NULL),
(83, 'Individual', 'In-Warehouse', 134, NULL, NULL, NULL, 4.28, '2026-05-12', 'WSR-WM000222-2026-012', 23.15, 2000.00, 40, 49, 3, '2026-06-25 13:52:56', 0, NULL),
(84, 'Individual', 'Mobile Procurement', 135, NULL, NULL, NULL, 1.20, '2026-05-13', 'WSR-WM000222-2026-013', 23.30, 2350.00, 47, 49, 3, '2026-06-25 13:52:56', 0, NULL),
(85, 'Individual', 'In-Warehouse', 136, NULL, NULL, NULL, 1.48, '2026-05-14', 'WSR-WM000222-2026-014', 23.45, 2700.00, 54, 49, 3, '2026-06-25 13:52:56', 0, NULL),
(86, 'Individual', 'In-Warehouse', 137, NULL, NULL, NULL, 1.76, '2026-05-15', 'WSR-WM000222-2026-015', 23.60, 3050.00, 61, 49, 3, '2026-06-25 13:52:56', 0, NULL),
(87, 'Individual', 'Mobile Procurement', 138, NULL, NULL, NULL, 2.04, '2026-05-16', 'WSR-WM000222-2026-016', 23.00, 3400.00, 68, 49, 3, '2026-06-25 13:52:56', 0, NULL),
(88, 'Individual', 'In-Warehouse', 139, NULL, NULL, NULL, 2.32, '2026-05-17', 'WSR-WM000222-2026-017', 23.15, 1000.00, 20, 49, 3, '2026-06-25 13:52:56', 0, NULL),
(89, 'Individual', 'In-Warehouse', 140, NULL, NULL, NULL, 2.60, '2026-05-18', 'WSR-WM000222-2026-018', 23.30, 1350.00, 27, 49, 3, '2026-06-25 13:52:56', 0, NULL),
(90, 'Individual', 'Mobile Procurement', 141, NULL, NULL, NULL, 2.88, '2026-05-19', 'WSR-WM000222-2026-019', 23.45, 1700.00, 34, 49, 3, '2026-06-25 13:52:56', 0, NULL),
(91, 'Individual', 'In-Warehouse', 142, NULL, NULL, NULL, 3.16, '2026-05-20', 'WSR-WM000222-2026-020', 23.60, 2050.00, 41, 49, 3, '2026-06-25 13:52:56', 0, NULL),
(92, 'Individual', 'In-Warehouse', 143, NULL, NULL, NULL, 3.44, '2026-05-21', 'WSR-WM000222-2026-021', 23.00, 2400.00, 48, 49, 3, '2026-06-25 13:52:56', 0, NULL),
(93, 'Individual', 'Mobile Procurement', 144, NULL, NULL, NULL, 3.72, '2026-05-22', 'WSR-WM000222-2026-022', 23.15, 2750.00, 55, 49, 3, '2026-06-25 13:52:56', 0, NULL),
(94, 'Individual', 'In-Warehouse', 145, NULL, NULL, NULL, 4.00, '2026-05-23', 'WSR-WM000222-2026-023', 23.30, 3100.00, 62, 49, 3, '2026-06-25 13:52:56', 0, NULL),
(95, 'Individual', 'In-Warehouse', 146, NULL, NULL, NULL, 4.28, '2026-05-24', 'WSR-WM000222-2026-024', 23.45, 3450.00, 69, 49, 3, '2026-06-25 13:52:56', 0, NULL),
(96, 'Individual', 'Mobile Procurement', 147, NULL, NULL, NULL, 1.20, '2026-05-25', 'WSR-WM000222-2026-025', 23.60, 1050.00, 21, 49, 3, '2026-06-25 13:52:56', 0, NULL),
(97, 'Individual', 'In-Warehouse', 148, NULL, NULL, NULL, 1.48, '2026-06-01', 'WSR-WM000222-2026-026', 23.00, 1400.00, 28, 49, 3, '2026-06-25 13:52:56', 0, NULL),
(98, 'Individual', 'In-Warehouse', 149, NULL, NULL, NULL, 1.76, '2026-06-02', 'WSR-WM000222-2026-027', 23.15, 1750.00, 35, 49, 3, '2026-06-25 13:52:56', 0, NULL),
(99, 'Individual', 'Mobile Procurement', 150, NULL, NULL, NULL, 2.04, '2026-06-03', 'WSR-WM000222-2026-028', 23.30, 2100.00, 42, 49, 3, '2026-06-25 13:52:56', 0, NULL),
(100, 'Individual', 'In-Warehouse', 151, NULL, NULL, NULL, 2.32, '2026-06-04', 'WSR-WM000222-2026-029', 23.45, 2450.00, 49, 49, 3, '2026-06-25 13:52:56', 0, NULL),
(101, 'Individual', 'In-Warehouse', 152, NULL, NULL, NULL, 2.60, '2026-06-05', 'WSR-WM000222-2026-030', 23.60, 2800.00, 56, 49, 3, '2026-06-25 13:52:56', 0, NULL),
(102, 'Individual', 'Mobile Procurement', 123, NULL, NULL, NULL, 2.88, '2026-06-06', 'WSR-WM000222-2026-031', 23.00, 3150.00, 63, 49, 3, '2026-06-25 13:52:56', 0, NULL),
(103, 'Individual', 'In-Warehouse', 124, NULL, NULL, NULL, 3.16, '2026-06-07', 'WSR-WM000222-2026-032', 23.15, 3500.00, 70, 49, 3, '2026-06-25 13:52:56', 0, NULL),
(104, 'Individual', 'In-Warehouse', 125, NULL, NULL, NULL, 3.44, '2026-06-08', 'WSR-WM000222-2026-033', 23.30, 1100.00, 22, 49, 3, '2026-06-25 13:52:56', 0, NULL),
(105, 'Individual', 'Mobile Procurement', 126, NULL, NULL, NULL, 3.72, '2026-06-09', 'WSR-WM000222-2026-034', 23.45, 1450.00, 29, 49, 3, '2026-06-25 13:52:56', 0, NULL),
(106, 'Individual', 'In-Warehouse', 127, NULL, NULL, NULL, 4.00, '2026-06-10', 'WSR-WM000222-2026-035', 23.60, 1800.00, 36, 49, 3, '2026-06-25 13:52:56', 0, NULL),
(107, 'Individual', 'In-Warehouse', 128, NULL, NULL, NULL, 4.28, '2026-06-11', 'WSR-WM000222-2026-036', 23.00, 2150.00, 43, 49, 3, '2026-06-25 13:52:56', 0, NULL),
(108, 'Individual', 'Mobile Procurement', 129, NULL, NULL, NULL, 1.20, '2026-06-12', 'WSR-WM000222-2026-037', 23.15, 2500.00, 50, 49, 3, '2026-06-25 13:52:56', 0, NULL),
(109, 'Individual', 'In-Warehouse', 130, NULL, NULL, NULL, 1.48, '2026-06-13', 'WSR-WM000222-2026-038', 23.30, 2850.00, 57, 49, 3, '2026-06-25 13:52:56', 0, NULL),
(110, 'Individual', 'In-Warehouse', 131, NULL, NULL, NULL, 1.76, '2026-06-14', 'WSR-WM000222-2026-039', 23.45, 3200.00, 64, 49, 3, '2026-06-25 13:52:56', 0, NULL),
(111, 'Individual', 'Mobile Procurement', 132, NULL, NULL, NULL, 2.04, '2026-06-15', 'WSR-WM000222-2026-040', 23.60, 3550.00, 71, 49, 3, '2026-06-25 13:52:56', 0, NULL),
(112, 'Individual', 'In-Warehouse', 133, NULL, NULL, NULL, 2.32, '2026-06-16', 'WSR-WM000222-2026-041', 23.00, 1150.00, 23, 49, 3, '2026-06-25 13:52:56', 0, NULL),
(113, 'Individual', 'In-Warehouse', 134, NULL, NULL, NULL, 2.60, '2026-06-17', 'WSR-WM000222-2026-042', 23.15, 1500.00, 30, 49, 3, '2026-06-25 13:52:56', 0, NULL),
(114, 'Individual', 'Mobile Procurement', 135, NULL, NULL, NULL, 2.88, '2026-06-18', 'WSR-WM000222-2026-043', 23.30, 1850.00, 37, 49, 3, '2026-06-25 13:52:56', 0, NULL),
(115, 'Individual', 'In-Warehouse', 136, NULL, NULL, NULL, 3.16, '2026-06-19', 'WSR-WM000222-2026-044', 23.45, 2200.00, 44, 49, 3, '2026-06-25 13:52:56', 0, NULL),
(116, 'Individual', 'In-Warehouse', 137, NULL, NULL, NULL, 3.44, '2026-06-20', 'WSR-WM000222-2026-045', 23.60, 2550.00, 51, 49, 3, '2026-06-25 13:52:56', 0, NULL),
(117, 'Individual', 'Mobile Procurement', 138, NULL, NULL, NULL, 3.72, '2026-06-21', 'WSR-WM000222-2026-046', 23.00, 2900.00, 58, 49, 3, '2026-06-25 13:52:56', 0, NULL),
(118, 'Individual', 'In-Warehouse', 139, NULL, NULL, NULL, 4.00, '2026-06-22', 'WSR-WM000222-2026-047', 23.15, 3250.00, 65, 49, 3, '2026-06-25 13:52:56', 0, NULL),
(119, 'Individual', 'In-Warehouse', 140, NULL, NULL, NULL, 4.28, '2026-06-23', 'WSR-WM000222-2026-048', 23.30, 3600.00, 72, 49, 3, '2026-06-25 13:52:56', 0, NULL),
(120, 'Individual', 'Mobile Procurement', 141, NULL, NULL, NULL, 1.20, '2026-06-24', 'WSR-WM000222-2026-049', 23.45, 1200.00, 24, 49, 3, '2026-06-25 13:52:56', 0, NULL),
(121, 'Individual', 'In-Warehouse', 142, NULL, NULL, NULL, 1.48, '2026-06-25', 'WSR-WM000222-2026-050', 23.60, 1550.00, 31, 49, 3, '2026-06-25 13:52:56', 0, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `transaction_farmer_members`
--

DROP TABLE IF EXISTS `transaction_farmer_members`;
CREATE TABLE `transaction_farmer_members` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `transaction_id` bigint(20) UNSIGNED NOT NULL,
  `farmer_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `transaction_farmer_members`
--

INSERT INTO `transaction_farmer_members` (`id`, `transaction_id`, `farmer_id`, `created_at`) VALUES
(161, 3, 3, '2026-06-25 13:52:45'),
(162, 3, 4, '2026-06-25 13:52:45'),
(163, 3, 5, '2026-06-25 13:52:45'),
(164, 3, 6, '2026-06-25 13:52:45'),
(165, 3, 7, '2026-06-25 13:52:45'),
(166, 4, 3, '2026-06-25 13:52:45'),
(167, 4, 4, '2026-06-25 13:52:45'),
(168, 4, 5, '2026-06-25 13:52:45'),
(169, 4, 6, '2026-06-25 13:52:45'),
(170, 4, 7, '2026-06-25 13:52:45'),
(171, 5, 3, '2026-06-25 13:52:45'),
(172, 5, 4, '2026-06-25 13:52:45'),
(173, 5, 5, '2026-06-25 13:52:45'),
(174, 5, 6, '2026-06-25 13:52:45'),
(175, 5, 7, '2026-06-25 13:52:45'),
(176, 6, 3, '2026-06-25 13:52:45'),
(177, 6, 4, '2026-06-25 13:52:45'),
(178, 6, 5, '2026-06-25 13:52:45'),
(179, 6, 6, '2026-06-25 13:52:45'),
(180, 6, 7, '2026-06-25 13:52:45'),
(181, 7, 8, '2026-06-25 13:52:45'),
(182, 7, 9, '2026-06-25 13:52:45'),
(183, 7, 10, '2026-06-25 13:52:45'),
(184, 7, 11, '2026-06-25 13:52:45'),
(185, 7, 12, '2026-06-25 13:52:45'),
(186, 8, 8, '2026-06-25 13:52:45'),
(187, 8, 9, '2026-06-25 13:52:45'),
(188, 8, 10, '2026-06-25 13:52:45'),
(189, 8, 11, '2026-06-25 13:52:45'),
(190, 8, 12, '2026-06-25 13:52:45'),
(191, 9, 8, '2026-06-25 13:52:45'),
(192, 9, 9, '2026-06-25 13:52:45'),
(193, 9, 10, '2026-06-25 13:52:45'),
(194, 9, 11, '2026-06-25 13:52:45'),
(195, 9, 12, '2026-06-25 13:52:45'),
(196, 10, 8, '2026-06-25 13:52:45'),
(197, 10, 9, '2026-06-25 13:52:45'),
(198, 10, 10, '2026-06-25 13:52:45'),
(199, 10, 11, '2026-06-25 13:52:45'),
(200, 10, 12, '2026-06-25 13:52:45'),
(201, 11, 13, '2026-06-25 13:52:45'),
(202, 11, 14, '2026-06-25 13:52:45'),
(203, 11, 15, '2026-06-25 13:52:45'),
(204, 11, 16, '2026-06-25 13:52:45'),
(205, 11, 17, '2026-06-25 13:52:45'),
(206, 12, 13, '2026-06-25 13:52:45'),
(207, 12, 14, '2026-06-25 13:52:45'),
(208, 12, 15, '2026-06-25 13:52:45'),
(209, 12, 16, '2026-06-25 13:52:45'),
(210, 12, 17, '2026-06-25 13:52:45'),
(211, 13, 13, '2026-06-25 13:52:45'),
(212, 13, 14, '2026-06-25 13:52:45'),
(213, 13, 15, '2026-06-25 13:52:45'),
(214, 13, 16, '2026-06-25 13:52:45'),
(215, 13, 17, '2026-06-25 13:52:45'),
(216, 14, 13, '2026-06-25 13:52:45'),
(217, 14, 14, '2026-06-25 13:52:45'),
(218, 14, 15, '2026-06-25 13:52:45'),
(219, 14, 16, '2026-06-25 13:52:45'),
(220, 14, 17, '2026-06-25 13:52:45'),
(221, 15, 18, '2026-06-25 13:52:45'),
(222, 15, 19, '2026-06-25 13:52:45'),
(223, 15, 20, '2026-06-25 13:52:45'),
(224, 15, 21, '2026-06-25 13:52:45'),
(225, 15, 22, '2026-06-25 13:52:45'),
(226, 16, 18, '2026-06-25 13:52:45'),
(227, 16, 19, '2026-06-25 13:52:45'),
(228, 16, 20, '2026-06-25 13:52:45'),
(229, 16, 21, '2026-06-25 13:52:45'),
(230, 16, 22, '2026-06-25 13:52:45'),
(231, 17, 18, '2026-06-25 13:52:45'),
(232, 17, 19, '2026-06-25 13:52:45'),
(233, 17, 20, '2026-06-25 13:52:45'),
(234, 17, 21, '2026-06-25 13:52:45'),
(235, 17, 22, '2026-06-25 13:52:45'),
(236, 18, 18, '2026-06-25 13:52:45'),
(237, 18, 19, '2026-06-25 13:52:45'),
(238, 18, 20, '2026-06-25 13:52:45'),
(239, 18, 21, '2026-06-25 13:52:45'),
(240, 18, 22, '2026-06-25 13:52:45');

-- --------------------------------------------------------

--
-- Table structure for table `warehouse_offices`
--

DROP TABLE IF EXISTS `warehouse_offices`;
CREATE TABLE `warehouse_offices` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `branch_id` bigint(20) UNSIGNED NOT NULL,
  `province_id` bigint(20) UNSIGNED DEFAULT NULL,
  `name` varchar(160) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `warehouse_offices`
--

INSERT INTO `warehouse_offices` (`id`, `branch_id`, `province_id`, `name`) VALUES
(1, 1, 1, 'San Jose Warehouse'),
(2, 2, 2, 'GID BASILAN'),
(3, 2, 3, 'TAWI-TAWI SATELITE OFFICE'),
(4, 2, 4, 'BASILAN-BRANCH OFFICE'),
(5, 2, 5, 'SULU SATELITE OFFICE'),
(6, 2, 6, 'GID SULU'),
(7, 2, 7, 'GID TAWI-TAWI'),
(8, 3, 8, 'GID MARAWI'),
(9, 3, 9, 'LANAO DEL SUR - BRANCH OFFICE'),
(10, 3, 9, 'SHIN HEUNG RECIRCULATING GRAIN DRYER'),
(11, 4, 10, 'MAGUINDANAO BRANCH OFFICE'),
(12, 4, 11, 'GID SHARIFF AGUAK'),
(13, 4, 12, 'GID 6'),
(14, 5, 13, 'REGION XIV REGIONAL OFFICE (BARMM)'),
(15, 6, 14, 'GID LIBERTAD I WHSE.'),
(16, 6, 14, 'GID LIBERTAD II WHSE.'),
(17, 6, 15, 'AGUSAN DEL SUR BRANCH OFFICE'),
(18, 6, 15, 'GID ALEGRIA WHSE.'),
(19, 6, 15, 'GID BAYUGAN WHSE.'),
(20, 6, 15, 'GID TRENTO WHSE.'),
(21, 7, 16, 'CARAGA REGIONAL OFFICE'),
(22, 8, 17, 'GID SAN JOSE WHSE'),
(23, 8, 18, 'GID DAPA WHSE'),
(24, 8, 18, 'GID KM. 10 WHSE'),
(25, 8, 19, 'GID CANTILAN WHSE'),
(26, 8, 19, 'GID DUPLEX WHSE'),
(27, 8, 19, 'GID MANGAGOY WHSE'),
(28, 8, 19, 'SURIGAO DEL SUR BRANCH OFFICE'),
(29, 9, 20, 'Basco Warehouse'),
(30, 9, 20, 'Batanes Unit Office'),
(31, 9, 21, 'MFC #1'),
(32, 9, 21, 'MFC #2'),
(33, 9, 21, 'MFC #3'),
(34, 9, 21, 'MFC #4'),
(35, 9, 21, 'MFC #5'),
(36, 9, 21, 'MFC #7'),
(37, 9, 21, 'Minprocor'),
(38, 9, 22, 'MFC Office'),
(39, 10, 23, 'East District Branch Office'),
(40, 10, 23, 'EDBO Quality Assurance Office'),
(41, 10, 24, 'Cavite Office'),
(42, 10, 24, 'General Trias A'),
(43, 10, 24, 'General Trias B'),
(44, 10, 25, 'Marikina Office'),
(45, 10, 26, 'Marikina Warehouse'),
(46, 10, 27, 'Antipolo Warehouse 1'),
(47, 10, 27, 'Antipolo Warehouse 2'),
(48, 11, 28, 'NCR Regional Office'),
(49, 12, 29, 'Agricom, Batch Recirculating Dryer'),
(50, 12, 29, 'Asingan FLGC'),
(51, 12, 29, 'Binalonan DM'),
(52, 12, 29, 'Binalonan GID'),
(53, 12, 29, 'Binalonan Triplex'),
(54, 12, 29, 'Eastern Pangasinan Branch Office'),
(55, 12, 29, 'LSU Type, Batch Recirculating Dryer'),
(56, 12, 29, 'Maruyama, Batch Recirculating Dryer'),
(57, 12, 29, 'Naric'),
(58, 12, 29, 'Rosales I'),
(59, 12, 29, 'Rosales II'),
(60, 12, 29, 'San Quintuin FLGC'),
(61, 12, 29, 'Satake Ricemill'),
(62, 12, 30, 'Alaminos GID'),
(63, 12, 30, 'Corporate Farming-RPS 1 (Free rent)'),
(64, 12, 30, 'Mangatarem GID I'),
(65, 12, 30, 'Mangatarem GID II'),
(66, 12, 30, 'Unique 1 Warehouse (Leased)'),
(67, 12, 30, 'Unique 2 Warehouse (Leased)'),
(68, 13, 31, 'Abra Office'),
(69, 13, 31, 'AHTC Warehouse'),
(70, 13, 31, 'Bangued GID'),
(71, 13, 32, 'Agricom LSU-Type Recirculating Dryer'),
(72, 13, 32, 'AMCC LSU Recirculating Dryer'),
(73, 13, 32, 'Dingras Duplex'),
(74, 13, 32, 'Dingras Multi-Purpose'),
(75, 13, 32, 'Ilocos Norte Branch Office'),
(76, 13, 32, 'Laoag GID'),
(77, 13, 32, 'Satake Ricemill'),
(78, 13, 33, 'Bantay GID I'),
(79, 13, 33, 'Bantay GID II'),
(80, 13, 33, 'Candon City Warehouse'),
(81, 13, 33, 'FLGC III'),
(82, 13, 33, 'Ilocos Sur Office'),
(83, 13, 33, 'Maruyama, Batch Recirculating Dryer'),
(84, 13, 33, 'Tomato Paste Plant Warehouse'),
(85, 14, 34, 'Loakan GID Warehouse'),
(86, 14, 34, 'Loakan Staffhouse & Training Center'),
(87, 14, 35, 'La Union Branch Office'),
(88, 14, 35, 'San Juan GID Warehouse 1'),
(89, 14, 35, 'San Juan GID Warehouse 2'),
(90, 15, 36, 'NFA Regional Office 1'),
(91, 16, 37, 'Conner Buying Station'),
(92, 16, 37, 'Flora Buying Station'),
(93, 16, 37, 'Luna GID II Duplex'),
(94, 16, 38, 'Agricomp Dryer'),
(95, 16, 38, 'Aparri Buying Station'),
(96, 16, 38, 'Cagayan Branch Office'),
(97, 16, 38, 'Carig GID I'),
(98, 16, 38, 'Carig GID II'),
(99, 16, 38, 'Carig Triplex'),
(100, 16, 38, 'Gonzaga GID III'),
(101, 16, 38, 'Lasam GID III'),
(102, 16, 38, 'Matucay GID I Duplex'),
(103, 16, 38, 'NFA Allacapan Truckscale'),
(104, 16, 38, 'NFA Tuguegarao Truckscale'),
(105, 16, 38, 'Peñablanca Buying Station'),
(106, 16, 38, 'Shin Hueng Dryer'),
(107, 16, 38, 'Solana I Buying Station'),
(108, 16, 38, 'Solana II Buying Station'),
(109, 16, 39, 'Padiscor Dryer'),
(110, 16, 39, 'Rizal Buying Station'),
(111, 16, 39, 'Rizal FLGC'),
(112, 16, 39, 'Satake Ricemill Tabuk'),
(113, 16, 39, 'Shin Hueng Dryer'),
(114, 16, 39, 'Tabuk GID Duplex'),
(115, 17, 40, 'Agricomp Dryer'),
(116, 17, 40, 'Alicia FLGC'),
(117, 17, 40, 'Burgos FLGC'),
(118, 17, 40, 'Cabagan FLGC'),
(119, 17, 40, 'Cabatuan FLGC'),
(120, 17, 40, 'Cauayan FLGC'),
(121, 17, 40, 'Cimbria Dryer'),
(122, 17, 40, 'Cordon FSW'),
(123, 17, 40, 'Delfin Albano MLGC'),
(124, 17, 40, 'Echague NPGC Corn Center'),
(125, 17, 40, 'Echague NPGC Triplex-GREEN'),
(126, 17, 40, 'Echague NPGC Trplex-WHITE'),
(127, 17, 40, 'Gamu DUPLEX'),
(128, 17, 40, 'Isabela Branch Office'),
(129, 17, 40, 'Luna FLGC'),
(130, 17, 40, 'Mallig FLGC'),
(131, 17, 40, 'Mechapil Dryer'),
(132, 17, 40, 'NFA Santiago Truckscale'),
(133, 17, 40, 'NPGC Echague Truckscale'),
(134, 17, 40, 'Padiscor Dryer'),
(135, 17, 40, 'Palanan GID'),
(136, 17, 40, 'Quezon FLGC'),
(137, 17, 40, 'Ramon FLGC'),
(138, 17, 40, 'Roxas GID I'),
(139, 17, 40, 'Roxas GID II'),
(140, 17, 40, 'San Isidro FLGC'),
(141, 17, 40, 'San Manuel GID'),
(142, 17, 40, 'San Mateo GID DUPLEX'),
(143, 17, 40, 'Santiago GID'),
(144, 17, 40, 'Santiago TRIPLEX'),
(145, 17, 40, 'Satake Ricemill NPGC'),
(146, 17, 40, 'Satake Ricemill Roxas'),
(147, 17, 40, 'Satake Ricemill Santiago'),
(148, 17, 40, 'Shin Hueng Dryer'),
(149, 17, 40, 'Tumauini GID'),
(150, 18, 41, 'NFA Regional Office 2'),
(151, 19, 42, 'Alfonso Lista FLGC'),
(152, 19, 42, 'Lagawe GID'),
(153, 19, 42, 'Shin Hueng Dryer'),
(154, 19, 43, 'Bontoc GID'),
(155, 19, 44, 'Agricomp Dryer'),
(156, 19, 44, 'Bagabag FLGC'),
(157, 19, 44, 'Bayombong DUPLEX'),
(158, 19, 44, 'Bayombong GID I'),
(159, 19, 44, 'Bayombong GID II'),
(160, 19, 44, 'MGC Bayombong Truckscale'),
(161, 19, 44, 'Nueva Vizcaya Branch Office'),
(162, 19, 45, 'Agricomp Dryer'),
(163, 19, 45, 'Cabarroguis GID I'),
(164, 19, 45, 'Cabarroguis GID II'),
(165, 19, 45, 'Diffun FLGC'),
(166, 19, 45, 'Maddela FLGC'),
(167, 19, 45, 'Ricemill House'),
(168, 19, 45, 'Saguday FLGC'),
(169, 19, 45, 'Shin Hueng Dryer'),
(170, 20, 46, 'Balagtas 1'),
(171, 20, 46, 'Balagtas 2'),
(172, 20, 46, 'Balagtas 3'),
(173, 20, 46, 'Bulacan Branch Office'),
(174, 20, 46, 'San Ildefonso 1'),
(175, 20, 46, 'San Ildefonso 2'),
(176, 20, 46, 'Sta Rita 1A'),
(177, 20, 46, 'Sta Rita 2A'),
(178, 20, 46, 'Tikay'),
(179, 21, 47, 'Baler GID Annex B'),
(180, 21, 47, 'Baler GID Main & Annex A'),
(181, 21, 47, 'Bartolome Warehouse'),
(182, 21, 47, 'Casiguran Warehouse'),
(183, 21, 48, 'Aliaga Food Center'),
(184, 21, 48, 'Bongabon Food Center'),
(185, 21, 48, 'Cabanatuan Warehouse 1'),
(186, 21, 48, 'Cabanatuan Warehouse 11 (Duplex)'),
(187, 21, 48, 'Cabanatuan Warehouse 12 (Triplex)'),
(188, 21, 48, 'Cabanatuan Warehouse 13'),
(189, 21, 48, 'Cabanatuan Warehouse 2'),
(190, 21, 48, 'Cabanatuan Warehouse 3'),
(191, 21, 48, 'Gapan Food Center'),
(192, 21, 48, 'Guimba Drier House'),
(193, 21, 48, 'Guimba Warehouse 1 (Duplex)'),
(194, 21, 48, 'Guimba Warehouse 2'),
(195, 21, 48, 'Muñoz Warehouse 1'),
(196, 21, 48, 'Nueva Ecija Branch Office'),
(197, 21, 48, 'Valle FLGC'),
(198, 22, 49, 'Balanga 1'),
(199, 22, 49, 'Balanga 2'),
(200, 22, 49, 'Bataan Satellite Office'),
(201, 22, 49, 'Farm Level Grain Center III - Dinalupihan'),
(202, 22, 50, 'Farm Level Grain Center III - San Luis'),
(203, 22, 50, 'Pampanga Branch Office'),
(204, 22, 50, 'Sindalan 1'),
(205, 22, 50, 'Sindalan 2'),
(206, 22, 50, 'TSB Warehouse'),
(207, 22, 51, 'Castillejos'),
(208, 22, 51, 'Iba'),
(209, 22, 51, 'Mango Terminal'),
(210, 22, 51, 'Maruyama YC-100'),
(211, 22, 51, 'Zambales Satellite Office'),
(212, 23, 52, 'Central Luzon Regional Office'),
(213, 24, 53, 'Aguso Warehouse 1'),
(214, 24, 53, 'Aguso Warehouse 2'),
(215, 24, 53, 'Aguso Warehouse 3'),
(216, 24, 53, 'Aguso Warehouse 3-Annex'),
(217, 24, 53, 'Aguso Warehouse 5'),
(218, 24, 53, 'Camiling Productivity Center'),
(219, 24, 53, 'Concepcion GID'),
(220, 24, 53, 'Concepcion Office'),
(221, 24, 53, 'La Paz GID I'),
(222, 24, 53, 'La Paz GID II'),
(223, 24, 53, 'La Paz GID III'),
(224, 24, 53, 'La Paz Office'),
(225, 24, 53, 'New Construction of Modernized Whse'),
(226, 24, 53, 'San Manuel Warehouse'),
(227, 24, 53, 'ShinHeung Recirculating Grain Dryer'),
(228, 24, 53, 'Sta. Ines Productivity Center'),
(229, 24, 53, 'Talimundok FLGC'),
(230, 24, 53, 'Tarlac Branch Office'),
(231, 25, 54, 'Batangas Branch Office'),
(232, 25, 54, 'GID II Batangas'),
(233, 25, 54, 'GID IIA Batangas'),
(234, 25, 54, 'GID Romblon'),
(235, 25, 54, 'Suntons Warehouse (Leased Warehouse)'),
(236, 26, 55, 'FLGC III'),
(237, 26, 55, 'GID 1'),
(238, 26, 55, 'GID 2'),
(239, 26, 55, 'GID Infanta'),
(240, 26, 55, 'Laguna Branch Office'),
(241, 27, 56, 'ABIA Warehouse'),
(242, 27, 56, 'Almuete Warehouse'),
(243, 27, 56, 'AMMAN Warehouse'),
(244, 27, 56, 'GID 1'),
(245, 27, 56, 'GID 2'),
(246, 27, 56, 'GID 3'),
(247, 27, 56, 'GID 4'),
(248, 27, 56, 'GID 5'),
(249, 27, 56, 'GID 6'),
(250, 27, 56, 'GID 7'),
(251, 27, 56, 'GID 8'),
(252, 27, 56, 'GID Duplex'),
(253, 27, 56, 'GID Sablayan'),
(254, 27, 56, 'Jafpy Warehouse'),
(255, 27, 56, 'LIMFCO Warehouse'),
(256, 27, 56, 'Magsaysay First Christian MPC'),
(257, 27, 56, 'Magsaysay Orig Warehouse'),
(258, 27, 56, 'MAMAMUCO Warehouse'),
(259, 27, 56, 'Mamburao Office'),
(260, 27, 56, 'Manayan Warehouse'),
(261, 27, 56, 'Miller Warehouse'),
(262, 27, 56, 'NAWACO 1'),
(263, 27, 56, 'NAWACO 2'),
(264, 27, 56, 'New Life Warehouse'),
(265, 27, 56, 'New Pajayon Warehouse'),
(266, 27, 56, 'Occidental Mindoro Branch Office'),
(267, 27, 56, 'Pablo Warehouse'),
(268, 27, 56, 'Pacunla 2A Warehouse'),
(269, 27, 56, 'Pacunla 2B Warehouse'),
(270, 27, 56, 'PAKIKIBAGAI Warehouse'),
(271, 27, 56, 'SACAMUCO Warehouse'),
(272, 27, 56, 'Salvacion Warehouse'),
(273, 27, 56, 'Sebastian Warehouse'),
(274, 28, 57, 'GID Calapan Warehouse'),
(275, 28, 57, 'GID Naujan Warehouse'),
(276, 28, 57, 'GID Roxas Duplex Warehouse'),
(277, 28, 57, 'GID-I Pinamalayan Warehouse'),
(278, 28, 57, 'GID-II Pinamalayan Warehouse'),
(279, 28, 57, 'Oriental Mindoro Branch Office'),
(280, 28, 57, 'RGC Warehouse'),
(281, 29, 58, 'GID 01 Warehouse'),
(282, 29, 58, 'GID 02 Warehouse'),
(283, 29, 58, 'GID 02 Warehouse- Annex'),
(284, 29, 58, 'GID 03 Warehouse'),
(285, 29, 58, 'LGU-RAC Rizal Buying Station'),
(286, 29, 58, 'MCFA PRC 2 Warehouse'),
(287, 29, 58, 'NFA Owned'),
(288, 29, 58, 'Palawan (New) Branch Office'),
(289, 29, 58, 'Palawan (Old) Branch Office'),
(290, 29, 58, 'PARCOFED 1 Warehouse'),
(291, 29, 58, 'PARCOFED 2 Warehouse'),
(292, 29, 58, 'SAMAGMA Warehouse'),
(293, 30, 59, 'FLGC'),
(294, 30, 59, 'GID Lucena'),
(295, 30, 59, 'ONG Warehouse'),
(296, 30, 59, 'Quezon Branch Office'),
(297, 31, 60, 'NFA Regional Office IV'),
(298, 32, 61, 'Bangco Titay WHSE (For Repair/Improvement)'),
(299, 32, 61, 'Dryer House'),
(300, 32, 61, 'FLGC Siay WHSE'),
(301, 32, 61, 'GID Guilawa WHSE'),
(302, 32, 61, 'GID Taway WHSE'),
(303, 32, 61, 'GID/FOREMOST WHSE C/D'),
(304, 32, 61, 'GTM Leased Warehouse'),
(305, 32, 61, 'New GID WHSE'),
(306, 32, 61, 'Regional Office'),
(307, 32, 61, 'Taway, Ipil Unit Office(To be demolish)'),
(308, 32, 61, 'Truck Scale'),
(309, 32, 61, 'Warehouse A/B (Lease Warehouse)'),
(310, 32, 61, 'Zamboanga City Branch Office'),
(311, 33, 62, 'Dipolog Satelitte Branch Office'),
(312, 33, 62, 'Dryer House'),
(313, 33, 62, 'GID I Dipolog WHSE'),
(314, 33, 62, 'GID II Dipolog WHSE'),
(315, 33, 62, 'GID Siocon WHSE'),
(316, 33, 62, 'Liloy LFSC WHSE'),
(317, 33, 62, 'Truck Scale'),
(318, 33, 63, 'C & A Rice Mill'),
(319, 33, 63, 'Culo A WHSE'),
(320, 33, 63, 'Culo B WHSE'),
(321, 33, 63, 'Culo C WHSE'),
(322, 33, 63, 'Dumingag FLGC WHSE'),
(323, 33, 63, 'Fernandez Rice Mill & Buying Station'),
(324, 33, 63, 'Molave Satelitte Branch Office'),
(325, 33, 63, 'Osmena FSC WHSE'),
(326, 33, 63, 'San Miguel FSW'),
(327, 33, 63, 'Tiguma A WHSE'),
(328, 33, 63, 'Tiguma B WHSE'),
(329, 33, 63, 'Tiguma C WHSE'),
(330, 33, 63, 'Truck Scale'),
(331, 33, 63, 'Villoria Rice and Corn Mill'),
(332, 33, 63, 'Zamboanga del Sur Branch Office'),
(333, 34, 64, 'Albay Branch Office'),
(334, 34, 64, 'Catanduanes Field Office'),
(335, 34, 64, 'GT 345XL Batch Recirculating Dryer'),
(336, 34, 64, 'Kolbi Ricemill (Diesel Engine)'),
(337, 34, 64, 'Legazpi GID Warehouse'),
(338, 34, 64, 'Libon Warehouse'),
(339, 34, 64, 'Ligao Warehouse'),
(340, 34, 64, 'Tabaco Abacorp Warehouse'),
(341, 34, 64, 'Tabaco GID Warehouse'),
(342, 34, 64, 'Virac GID II Warehouse'),
(343, 35, 65, 'Agricom Batch Recirculating Dryer'),
(344, 35, 65, 'Buivanggo Multi-pass Ricemill'),
(345, 35, 65, 'Camarines Norte Field Office'),
(346, 35, 65, 'Camarines Sur Branch Office'),
(347, 35, 65, 'DA Flatbed Dryer'),
(348, 35, 65, 'Flatbed Dryer'),
(349, 35, 65, 'GID 1 Daet Warehouse'),
(350, 35, 65, 'GID 1 Warehouse Pili'),
(351, 35, 65, 'GID 2 Daet Warehouse'),
(352, 35, 65, 'GID 2 Warehouse Pili'),
(353, 35, 65, 'GID Libmanan Warehouse'),
(354, 35, 65, 'Kolbi Ricemill (Electric)'),
(355, 36, 66, 'NFA Regional Office No. V'),
(356, 37, 67, 'Flatbed Dryer'),
(357, 37, 67, 'GID 1 Warehouse Masbate'),
(358, 37, 67, 'GID Warehouse Masbate'),
(359, 37, 67, 'Kolbi Ricemill (Electric)'),
(360, 37, 67, 'Masbate Field Office'),
(361, 37, 68, 'GID 1 Warehouse Sorsogon'),
(362, 37, 68, 'GID 2 Warehouse Sorsogon'),
(363, 37, 68, 'GID 3 Warehouse Sorsogon'),
(364, 37, 68, 'Megasun Batch Recirculating Dryer'),
(365, 37, 68, 'Shin Heung Batch Recirculating Dryer'),
(366, 37, 68, 'Sorsogon Branch Office'),
(367, 38, 69, 'Aklan Grains Center'),
(368, 38, 69, 'Shin Heung Mechanical  Dryer'),
(369, 38, 70, 'Bolo Grains Center'),
(370, 38, 70, 'Dumalag Grains Center'),
(371, 38, 70, 'Maruyama Recirculating Dryer'),
(372, 38, 70, 'Satake Batch Recirculating Dryer'),
(373, 38, 70, 'Satake Ricemill'),
(374, 38, 70, 'Sigma Grains Center'),
(375, 39, 71, 'Agricum Biomass'),
(376, 39, 71, 'Agricum LSU Type-Circulating Dryer'),
(377, 39, 71, 'Antique Satake Ricemill'),
(378, 39, 71, 'GID Camp Fullon 1'),
(379, 39, 71, 'GID Camp Fullon 3'),
(380, 39, 71, 'GID Culasi'),
(381, 39, 71, 'Shin Heung Mechanical  Dryer'),
(382, 39, 71, 'Shun Kuan EC50 Recirculating Dryer'),
(383, 39, 72, 'Jordan Warehouse'),
(384, 39, 72, 'Millan JICA'),
(385, 39, 72, 'Suclaran JICA'),
(386, 39, 73, 'Dueñas MAFIM'),
(387, 39, 73, 'Dumangas Grains Center'),
(388, 39, 73, 'Dumangas MAFIM'),
(389, 39, 73, 'Dumangas Satake Ricemill'),
(390, 39, 73, 'Iloilo Branch Office'),
(391, 39, 73, 'Jaro MAFIM'),
(392, 39, 73, 'Jaro Satake Ricemill'),
(393, 39, 73, 'Jaro Triplex Warehouse'),
(394, 39, 73, 'Kabsaka Triplex Warehouse'),
(395, 39, 73, 'Maruyama Recirculating Dryer'),
(396, 39, 73, 'Modified Flatbed Dryer'),
(397, 39, 73, 'Modified Flatbed Dryer (TWIN)'),
(398, 39, 73, 'Pototan Grains Center'),
(399, 39, 73, 'San Dionisio Warehouse'),
(400, 39, 73, 'Shin Heung'),
(401, 39, 73, 'Shin Heung Mechanical Dryer'),
(402, 40, 74, 'GID Warehouse'),
(403, 40, 74, 'Malaluan Warehouse A (Leased)'),
(404, 40, 74, 'Malaluan Warehouse B (Leased)'),
(405, 40, 74, 'Negros Occidental Branch Office'),
(406, 41, 75, 'Western Visayas Regional Office'),
(407, 42, 76, 'Anihan Portable Recirculating Dryer'),
(408, 42, 76, 'Bohol Branch Office'),
(409, 42, 76, 'FLGC III Warehouse'),
(410, 42, 76, 'FSC Warehouse'),
(411, 42, 76, 'GID Warehouse (w/ extension)'),
(412, 42, 76, 'Green Mac Batch Recirculating Dryer'),
(413, 42, 76, 'Satake Rice Mill'),
(414, 42, 76, 'Shin Heung Batch Recirculating Dryer'),
(415, 43, 77, 'Asuki Truckscale'),
(416, 43, 77, 'Cebu Branch Office'),
(417, 43, 77, 'Centennial Warehouse (under usufruct agreement w/ FTI)'),
(418, 43, 77, 'GID I / Warehouse-19'),
(419, 43, 77, 'GID II / Bogo Unit Warehouse (proposed for major repair)'),
(420, 43, 77, 'GID III / Tudela Unit Warehouse (proposed for major repair)'),
(421, 43, 77, 'GID IV / Badian Unit Warehouse (under MAFIM repairs)'),
(422, 43, 77, 'GID V / Sta Fe Unit Warehouse (under MAFIM repairs)'),
(423, 43, 77, 'GID VIII Warehouse (under MAFIM repairs)'),
(424, 43, 77, 'Warehouse # 46 (under MAFIM repairs)'),
(425, 43, 77, 'Warehouse # 9'),
(426, 44, 78, 'Daichi Ricemill'),
(427, 44, 78, 'GID I Dumaguete'),
(428, 44, 78, 'GID II Guihulngan'),
(429, 44, 78, 'Negros Oriental Branch Office'),
(430, 44, 79, 'GID Siquijor'),
(431, 45, 80, 'GID Naval Warehouse'),
(432, 45, 80, 'Naval PHF Shedhouse'),
(433, 45, 81, 'Alangalang Maruyama Dryer'),
(434, 45, 81, 'Alangalang Warehouse- Ricemill'),
(435, 45, 81, 'Alangalang Warehouse-Millhouse'),
(436, 45, 81, 'Delta Leased Warehouse'),
(437, 45, 81, 'Dryers Modernized warehouse (on-going construction under MAFIM Program)'),
(438, 45, 81, 'FLGC LSU Mechanical Dryer'),
(439, 45, 81, 'FLGC Warehouse'),
(440, 45, 81, 'GID Alangalang Warehouse I'),
(441, 45, 81, 'GID Alangalang Warehouse II'),
(442, 45, 81, 'GID Baybay Warehouse'),
(443, 45, 81, 'GID Cogon Warehouse'),
(444, 45, 81, 'GID Maasin Warehouse'),
(445, 45, 81, 'GID Port Area Warehouse'),
(446, 45, 81, 'GID San Pablo Warehouse'),
(447, 45, 81, 'JICA Warehouse'),
(448, 45, 81, 'JK  Leased Warehouse'),
(449, 45, 81, 'Leyte Branch Office'),
(450, 45, 81, 'Modernized Warehouse (on-going construction under MAFIM Program)'),
(451, 45, 81, 'NFA Maasin Satellite Office'),
(452, 45, 81, 'NFA Naval Mechanical Dryers'),
(453, 45, 81, 'NFA Naval satellite Office'),
(454, 45, 81, 'Ricemill for Modernized Warehouse (on-going construction under MAFIM Program)'),
(455, 46, 82, 'Regional Office'),
(456, 47, 83, 'Borongan Satellite Office'),
(457, 47, 83, 'Catbalogan Dryer house'),
(458, 47, 83, 'Catbalogan Satellite Office'),
(459, 47, 83, 'GID Bobon Warehouse'),
(460, 47, 83, 'GID Borongan Warehouse'),
(461, 47, 83, 'GID Calbayog Warehouse'),
(462, 47, 83, 'GID Catbalogan Warehouse'),
(463, 47, 83, 'GID Catubig Warehouse'),
(464, 47, 83, 'GID Guiuan Warehouse'),
(465, 47, 83, 'GID Oras Warehouse'),
(466, 47, 83, 'GID Rawis Warehouse'),
(467, 47, 83, 'Leased Warehouse'),
(468, 47, 83, 'Maruyama Dryer'),
(469, 47, 83, 'Modernized Warehouse (on-going construction under MAFIM Program)'),
(470, 47, 83, 'NFA-Owned Duplex Warehouse'),
(471, 47, 83, 'Northern Samar Branch Office'),
(472, 48, 84, 'Alheed Batch Recirculating Mech. Grain Dryer (1 Unit)'),
(473, 48, 84, 'Alheed Batch Recirculating Mech. Grain Dryer (2 Units)'),
(474, 48, 84, 'GID Aglayan Warehouse'),
(475, 48, 84, 'GID Kalilangan Warehouse'),
(476, 48, 84, 'GID Maramag Annex Warehouse'),
(477, 48, 84, 'GID Maramag Main Warehouse'),
(478, 48, 84, 'GID Musuan Warehouse'),
(479, 48, 84, 'GID Valencia Warehouse'),
(480, 48, 84, 'GID Wao INF Warehouse'),
(481, 48, 84, 'GID Wao Warehouse'),
(482, 48, 84, 'Greenmac Batch Recirculating Mech. Grain Dryer (3 Units)'),
(483, 48, 84, 'Mechaphil Batch Recirculating Mech. Grain Dryer (2 Units)'),
(484, 48, 84, 'Mechaphil Batch Recirculating Mech. Grain Dryer (3 Units)'),
(485, 48, 84, 'NFA Bukidnon Branch Office'),
(486, 48, 84, 'Shin Neung Batch Recirculating Mech. Grain Dryer (1 Unit)'),
(487, 49, 85, 'GID 1 Iligan Warehouse'),
(488, 49, 85, 'GID 2 Lala Warehouse'),
(489, 49, 85, 'Hansung Recirculating Mech. Grain Dryer 6TPB'),
(490, 49, 85, 'LSU Agricom Recirculating Mech. Grain Dryer 6TB'),
(491, 49, 85, 'NFA Lanao del Norte Branch Office'),
(492, 49, 85, 'Suncue Recirculating Mech. Grain Dryer 6TPB'),
(493, 49, 85, 'Zemic Truckscale 60T'),
(494, 49, 86, 'GID 1 Ozamis Warehouse'),
(495, 49, 86, 'GID 2 Ozamis Warehouse'),
(496, 49, 86, 'GID 3 Ozamis Warehouse'),
(497, 49, 86, 'Mega Sun Recirculating Mech. Grain Dryer 6TPB'),
(498, 49, 86, 'Sato Ricemill 150 KG/HR'),
(499, 49, 86, 'Suncue Recirculating Mech. Grain Dryer 6TPB'),
(500, 49, 86, 'Zemic Truckscale 60T'),
(501, 50, 87, 'GID 1, Patag Warehouse'),
(502, 50, 87, 'GID 2, Baloy Warehouse'),
(503, 50, 87, 'GID 3, Baloy Warehouse'),
(504, 50, 87, 'GID 4, Baloy Warehouse'),
(505, 50, 87, 'NFA Misamis Oriental Branch Office'),
(506, 50, 88, '20TCC Warehouse'),
(507, 50, 88, 'FLGC Warehouse'),
(508, 50, 88, 'NFA Camiguin Sub- Office'),
(509, 51, 89, 'Regional Office'),
(510, 52, 90, 'Davao del Norte Branch Office'),
(511, 52, 90, 'FLGC Maragusan Warehouse'),
(512, 52, 90, 'GID 1 Warehouse'),
(513, 52, 90, 'GID 2 Warehouse'),
(514, 52, 90, 'GID Compostela Warehouse'),
(515, 52, 90, 'LSU Mechanical Dryer'),
(516, 52, 90, 'MAFIM Warehouse (on-going)'),
(517, 52, 90, 'Maruyama Mechanical Dryer'),
(518, 52, 90, 'Nabunturan Office'),
(519, 52, 90, 'Shin Heung Mechanical Dryer'),
(520, 52, 90, 'Sto. Tomas Warehouse'),
(521, 52, 90, 'Toledo Truck Scale'),
(522, 52, 90, 'Weightronix Truck Scale'),
(523, 53, 91, 'Davao del Sur Branch Office'),
(524, 53, 91, 'Dryer House'),
(525, 53, 91, 'GID Warehouse'),
(526, 53, 91, 'MAFIM Warehouse (on-going construction)'),
(527, 53, 91, 'OLD Warehouse'),
(528, 53, 91, 'Santa Ana Office'),
(529, 53, 91, 'Santa Ana Warehouse'),
(530, 53, 91, 'Satake Rice Mill'),
(531, 53, 91, 'Triplex Warehouse'),
(532, 53, 91, 'Truckscale'),
(533, 54, 92, 'Baganga Buying Station'),
(534, 54, 92, 'Davao Oriental Branch Office'),
(535, 54, 92, 'GID 1 WAREHOUSE'),
(536, 54, 92, 'GID 2 WAREHOUSE'),
(537, 54, 92, 'GID 3 warehouse'),
(538, 54, 92, 'GID 7 WAREHOUSE'),
(539, 54, 92, 'LSU TYPE MECH. DRYER WITH DRYER HOUSE'),
(540, 55, 93, 'Regional Office XI'),
(541, 56, 94, 'GID 1 KIDAPAWAN'),
(542, 56, 94, 'GID 2 M\'LANG'),
(543, 56, 94, 'GID 3 KIDAPAWAN'),
(544, 56, 94, 'GID 4 M\'LANG'),
(545, 56, 94, 'GID 5 KIDAPAWAN'),
(546, 56, 94, 'GID 6 KABACAN'),
(547, 56, 94, 'GID 7 BAGUER'),
(548, 56, 94, 'GID 8 KILADA'),
(549, 56, 94, 'Mechanical Dryer (MARUYAMA)'),
(550, 56, 94, 'Mechanical Dryer (PHILMEC)'),
(551, 56, 94, 'Mechanical Dryer (SHIN HUENG)'),
(552, 56, 94, 'Mechanical Dryer (SUNCUE)'),
(553, 56, 94, 'MILL HOUSE KIDAPAWAN'),
(554, 56, 94, 'NFA OWNED 1 KIDAPAWAN'),
(555, 56, 94, 'NFA OWNED 2 M\'LANG'),
(556, 56, 94, 'NFA OWNED 4 BAGUER'),
(557, 56, 94, 'NORTH COTABATO BRANCH OFFICE'),
(558, 56, 94, 'Ricemill (SATAKE Multi-pass)'),
(559, 56, 94, 'Truck Scale (Mettler Toledo)'),
(560, 57, 95, 'REGIONAL OFFICE 12 BLDG.'),
(561, 58, 96, 'FLGC Banga'),
(562, 58, 96, 'GID 1 GENERAL SANTOS CITY'),
(563, 58, 96, 'GID 2 GENERAL SANTOS CITY'),
(564, 58, 96, 'GID 3 GENERAL SANTOS CITY'),
(565, 58, 96, 'GID 4 GENERAL SANTOS CITY'),
(566, 58, 96, 'GID 5 MAITUM'),
(567, 58, 96, 'IBG KORONADAL CITY'),
(568, 58, 96, 'MLGC SURALLAH'),
(569, 58, 96, 'NABCOR BANGA'),
(570, 58, 96, 'NFA 1 GENERAL SANTOS CITY'),
(571, 58, 96, 'NFA 1 KORONADAL CITY'),
(572, 58, 96, 'NFA 2 GENERAL SANTOS CITY'),
(573, 58, 96, 'NFA Gen. Santos Main Building'),
(574, 58, 96, 'SCBO Koronadal City'),
(575, 58, 96, 'Truck Scale (Mettler Toledo)'),
(576, 58, 96, 'Truck Scale (Weightronix)'),
(577, 59, 97, 'Mechanical Dryer (GREENMAC)'),
(578, 59, 97, 'Mechanical Dryer (LSU-AGRICOM)'),
(579, 59, 97, 'Mechanical Dryer (SATAKE)'),
(580, 59, 97, 'Mechanical Dryer (SHIN HUENG)'),
(581, 59, 97, 'Mechanical Dryer (SUNCUE I)'),
(582, 59, 97, 'Mechanical Dryer (SUNCUE)'),
(583, 59, 97, 'NFA GID 1 LEBAK'),
(584, 59, 97, 'NFA GID 2 LEBAK'),
(585, 59, 97, 'NFA GID TRIPLEX ISULAN, (GID 1, GID 2, GID 3)'),
(586, 59, 97, 'NFA SKBO ISULAN'),
(587, 59, 97, 'NFA SPGC DUPLEX (GID7)'),
(588, 59, 97, 'NFA SPGC GID 8'),
(589, 59, 97, 'NFA SPGC QUINTUPLEX (GID 1)'),
(590, 59, 97, 'NFA SPGC QUINTUPLEX (GID 2)'),
(591, 59, 97, 'NFA SPGC QUINTUPLEX (GID 3)'),
(592, 59, 97, 'NFA SPGC QUINTUPLEX (GID 4)'),
(593, 59, 97, 'NFA SPGC QUINTUPLEX (GID 5)'),
(594, 59, 97, 'Ricemill (BUHLER MIAG Multi-pass)'),
(595, 59, 97, 'Truck Scale (Mettler Toledo)'),
(596, 59, 97, 'Truck Scale (Weightronix)');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `branch_offices`
--
ALTER TABLE `branch_offices`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `branch_region_unique` (`region_id`,`name`);

--
-- Indexes for table `central_departments`
--
ALTER TABLE `central_departments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `central_departments_name_unique` (`name`);

--
-- Indexes for table `central_divisions`
--
ALTER TABLE `central_divisions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `central_division_unique` (`department_id`,`name`);

--
-- Indexes for table `central_units`
--
ALTER TABLE `central_units`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `central_unit_unique` (`division_id`,`name`);

--
-- Indexes for table `display_photos`
--
ALTER TABLE `display_photos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `display_photo_status_position` (`status`,`position`),
  ADD KEY `submitted_by` (`submitted_by`);

--
-- Indexes for table `display_settings`
--
ALTER TABLE `display_settings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `farmers`
--
ALTER TABLE `farmers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `rsbsa_number` (`rsbsa_number`),
  ADD UNIQUE KEY `farmers_farmer_key_unique` (`farmer_key`),
  ADD KEY `farmer_organization_id` (`farmer_organization_id`),
  ADD KEY `warehouse_id` (`warehouse_id`);

--
-- Indexes for table `farmer_key_sequences`
--
ALTER TABLE `farmer_key_sequences`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `farmer_organizations`
--
ALTER TABLE `farmer_organizations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `landholdings`
--
ALTER TABLE `landholdings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `farmer_landholding_unique` (`farmer_id`);

--
-- Indexes for table `location_masterlist`
--
ALTER TABLE `location_masterlist`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `location_master_unique` (`region`,`branch`,`province`,`facility_name`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `province_offices`
--
ALTER TABLE `province_offices`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `province_branch_unique` (`branch_id`,`name`);

--
-- Indexes for table `regions`
--
ALTER TABLE `regions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `report_signatories`
--
ALTER TABLE `report_signatories`
  ADD PRIMARY KEY (`id`),
  ADD KEY `report_signatories_user_idx` (`user_id`);

--
-- Indexes for table `support_tickets`
--
ALTER TABLE `support_tickets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `support_tickets_reporter_idx` (`reporter_id`),
  ADD KEY `support_tickets_status_idx` (`status`),
  ADD KEY `resolved_by` (`resolved_by`);

--
-- Indexes for table `support_ticket_messages`
--
ALTER TABLE `support_ticket_messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `support_ticket_messages_ticket_idx` (`ticket_id`),
  ADD KEY `sender_id` (`sender_id`);

--
-- Indexes for table `transactions`
--
ALTER TABLE `transactions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `warehouse_stock_receipt_number` (`warehouse_stock_receipt_number`),
  ADD UNIQUE KEY `transactions_client_control_number_unique` (`client_control_number`),
  ADD KEY `farmer_id` (`farmer_id`),
  ADD KEY `farmer_organization_id` (`farmer_organization_id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `warehouse_id` (`warehouse_id`);

--
-- Indexes for table `transaction_farmer_members`
--
ALTER TABLE `transaction_farmer_members`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `transaction_farmer_unique` (`transaction_id`,`farmer_id`),
  ADD KEY `transaction_farmer_members_farmer_id_index` (`farmer_id`);

--
-- Indexes for table `warehouse_offices`
--
ALTER TABLE `warehouse_offices`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `warehouse_province_unique` (`province_id`,`name`),
  ADD KEY `warehouse_branch_idx` (`branch_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `audit_logs`
--
ALTER TABLE `audit_logs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=79;

--
-- AUTO_INCREMENT for table `branch_offices`
--
ALTER TABLE `branch_offices`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=60;

--
-- AUTO_INCREMENT for table `central_departments`
--
ALTER TABLE `central_departments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT for table `central_divisions`
--
ALTER TABLE `central_divisions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT for table `central_units`
--
ALTER TABLE `central_units`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `display_photos`
--
ALTER TABLE `display_photos`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `farmers`
--
ALTER TABLE `farmers`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=153;

--
-- AUTO_INCREMENT for table `farmer_key_sequences`
--
ALTER TABLE `farmer_key_sequences`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=113;

--
-- AUTO_INCREMENT for table `farmer_organizations`
--
ALTER TABLE `farmer_organizations`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=155;

--
-- AUTO_INCREMENT for table `landholdings`
--
ALTER TABLE `landholdings`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=93;

--
-- AUTO_INCREMENT for table `location_masterlist`
--
ALTER TABLE `location_masterlist`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=596;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `province_offices`
--
ALTER TABLE `province_offices`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=99;

--
-- AUTO_INCREMENT for table `regions`
--
ALTER TABLE `regions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT for table `report_signatories`
--
ALTER TABLE `report_signatories`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `support_tickets`
--
ALTER TABLE `support_tickets`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `support_ticket_messages`
--
ALTER TABLE `support_ticket_messages`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `transactions`
--
ALTER TABLE `transactions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=122;

--
-- AUTO_INCREMENT for table `transaction_farmer_members`
--
ALTER TABLE `transaction_farmer_members`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=241;

--
-- AUTO_INCREMENT for table `warehouse_offices`
--
ALTER TABLE `warehouse_offices`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=597;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD CONSTRAINT `audit_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `branch_offices`
--
ALTER TABLE `branch_offices`
  ADD CONSTRAINT `branch_offices_ibfk_1` FOREIGN KEY (`region_id`) REFERENCES `regions` (`id`);

--
-- Constraints for table `central_divisions`
--
ALTER TABLE `central_divisions`
  ADD CONSTRAINT `central_divisions_department_fk` FOREIGN KEY (`department_id`) REFERENCES `central_departments` (`id`);

--
-- Constraints for table `central_units`
--
ALTER TABLE `central_units`
  ADD CONSTRAINT `central_units_division_fk` FOREIGN KEY (`division_id`) REFERENCES `central_divisions` (`id`);

--
-- Constraints for table `display_photos`
--
ALTER TABLE `display_photos`
  ADD CONSTRAINT `display_photos_ibfk_1` FOREIGN KEY (`submitted_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `farmers`
--
ALTER TABLE `farmers`
  ADD CONSTRAINT `farmers_ibfk_1` FOREIGN KEY (`farmer_organization_id`) REFERENCES `farmer_organizations` (`id`),
  ADD CONSTRAINT `farmers_ibfk_2` FOREIGN KEY (`warehouse_id`) REFERENCES `warehouse_offices` (`id`);

--
-- Constraints for table `landholdings`
--
ALTER TABLE `landholdings`
  ADD CONSTRAINT `landholdings_ibfk_1` FOREIGN KEY (`farmer_id`) REFERENCES `farmers` (`id`);

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `province_offices`
--
ALTER TABLE `province_offices`
  ADD CONSTRAINT `province_offices_ibfk_1` FOREIGN KEY (`branch_id`) REFERENCES `branch_offices` (`id`);

--
-- Constraints for table `report_signatories`
--
ALTER TABLE `report_signatories`
  ADD CONSTRAINT `report_signatories_user_fk` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `support_tickets`
--
ALTER TABLE `support_tickets`
  ADD CONSTRAINT `support_tickets_ibfk_1` FOREIGN KEY (`reporter_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `support_tickets_ibfk_2` FOREIGN KEY (`resolved_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `support_ticket_messages`
--
ALTER TABLE `support_ticket_messages`
  ADD CONSTRAINT `support_ticket_messages_ibfk_1` FOREIGN KEY (`ticket_id`) REFERENCES `support_tickets` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `support_ticket_messages_ibfk_2` FOREIGN KEY (`sender_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `transactions`
--
ALTER TABLE `transactions`
  ADD CONSTRAINT `transactions_ibfk_1` FOREIGN KEY (`farmer_id`) REFERENCES `farmers` (`id`),
  ADD CONSTRAINT `transactions_ibfk_2` FOREIGN KEY (`farmer_organization_id`) REFERENCES `farmer_organizations` (`id`),
  ADD CONSTRAINT `transactions_ibfk_3` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `transactions_ibfk_4` FOREIGN KEY (`warehouse_id`) REFERENCES `warehouse_offices` (`id`);

--
-- Constraints for table `transaction_farmer_members`
--
ALTER TABLE `transaction_farmer_members`
  ADD CONSTRAINT `transaction_farmer_members_farmer_fk` FOREIGN KEY (`farmer_id`) REFERENCES `farmers` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `transaction_farmer_members_transaction_fk` FOREIGN KEY (`transaction_id`) REFERENCES `transactions` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `warehouse_offices`
--
ALTER TABLE `warehouse_offices`
  ADD CONSTRAINT `warehouse_offices_ibfk_1` FOREIGN KEY (`branch_id`) REFERENCES `branch_offices` (`id`),
  ADD CONSTRAINT `warehouse_offices_ibfk_2` FOREIGN KEY (`province_id`) REFERENCES `province_offices` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
