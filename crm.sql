-- phpMyAdmin SQL Dump
-- version 5.2.1deb3
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Sep 14, 2026 at 02:15 PM
-- Server version: 8.0.46-0ubuntu0.24.04.4
-- PHP Version: 8.3.6

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `crm`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin_details`
--

CREATE TABLE `admin_details` (
  `id` int NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `username` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `status` enum('Active','Inactive') NOT NULL DEFAULT 'Active',
  `created_At` timestamp NOT NULL,
  `updated_At` timestamp NOT NULL,
  `session_token` varchar(255) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `audit_logs`
--

CREATE TABLE `audit_logs` (
  `id` bigint UNSIGNED NOT NULL,
  `tenant_id` bigint UNSIGNED DEFAULT NULL,
  `actor_id` bigint UNSIGNED DEFAULT NULL,
  `event` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `auditable_type` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `auditable_id` bigint UNSIGNED NOT NULL,
  `old_values` json DEFAULT NULL,
  `new_values` json DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `companies`
--

CREATE TABLE `companies` (
  `id` bigint UNSIGNED NOT NULL,
  `tenant_id` bigint UNSIGNED DEFAULT NULL,
  `owner_id` bigint UNSIGNED DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `legal_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `website` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `industry` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `company_size` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `gst_number` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `pan_number` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address` text COLLATE utf8mb4_unicode_ci,
  `city` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `state` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `country` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `pincode` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'prospect',
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `company_details`
--

CREATE TABLE `company_details` (
  `id` int NOT NULL,
  `tenant_id` bigint UNSIGNED DEFAULT NULL,
  `company_name` varchar(255) DEFAULT NULL,
  `company_logo` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `city` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `state` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `country` varchar(255) NOT NULL DEFAULT 'India',
  `pincode` int DEFAULT NULL,
  `gst_number` varchar(255) DEFAULT NULL,
  `pan_number` varchar(255) DEFAULT NULL,
  `bank_name` varchar(255) DEFAULT NULL,
  `account_name` varchar(255) DEFAULT NULL,
  `account_number` int DEFAULT NULL,
  `ifsc_code` varchar(50) DEFAULT NULL,
  `status` enum('Active','Inactive') NOT NULL DEFAULT 'Active',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `contacts`
--

CREATE TABLE `contacts` (
  `id` bigint UNSIGNED NOT NULL,
  `tenant_id` bigint UNSIGNED DEFAULT NULL,
  `company_id` bigint UNSIGNED DEFAULT NULL,
  `owner_id` bigint UNSIGNED DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `alternate_phone` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `designation` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `department` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `city` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `state` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `country` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_primary` tinyint(1) NOT NULL DEFAULT '0',
  `source` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `notes` text COLLATE utf8mb4_unicode_ci,
  `legacy_customer_contact_id` int UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `customer_contact`
--

CREATE TABLE `customer_contact` (
  `id` int NOT NULL,
  `tenant_id` bigint UNSIGNED DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `phone` varchar(50) NOT NULL,
  `email` varchar(255) NOT NULL,
  `designation` varchar(255) NOT NULL,
  `budget` double(10,2) NOT NULL,
  `city` varchar(255) NOT NULL,
  `lead_id` int UNSIGNED NOT NULL,
  `status` enum('Active','Inactive') NOT NULL DEFAULT 'Active',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `deals`
--

CREATE TABLE `deals` (
  `id` bigint UNSIGNED NOT NULL,
  `tenant_id` bigint UNSIGNED DEFAULT NULL,
  `pipeline_id` bigint UNSIGNED NOT NULL,
  `stage_id` bigint UNSIGNED NOT NULL,
  `lead_id` int UNSIGNED DEFAULT NULL,
  `company_id` bigint UNSIGNED DEFAULT NULL,
  `contact_id` bigint UNSIGNED DEFAULT NULL,
  `order_id` int DEFAULT NULL,
  `owner_id` bigint UNSIGNED DEFAULT NULL,
  `lost_reason_id` bigint UNSIGNED DEFAULT NULL,
  `created_by` bigint UNSIGNED DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `amount` double(10,2) NOT NULL DEFAULT '0.00',
  `currency` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `expected_close_date` date DEFAULT NULL,
  `closed_at` timestamp NULL DEFAULT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'open',
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `deal_stage_history`
--

CREATE TABLE `deal_stage_history` (
  `id` bigint UNSIGNED NOT NULL,
  `deal_id` bigint UNSIGNED NOT NULL,
  `from_stage_id` bigint UNSIGNED DEFAULT NULL,
  `to_stage_id` bigint UNSIGNED DEFAULT NULL,
  `changed_by` bigint UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint UNSIGNED NOT NULL,
  `uuid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `leads`
--

CREATE TABLE `leads` (
  `id` int UNSIGNED NOT NULL,
  `tenant_id` bigint UNSIGNED DEFAULT NULL,
  `company_id` bigint UNSIGNED DEFAULT NULL,
  `contact_id` bigint UNSIGNED DEFAULT NULL,
  `lead_number` int DEFAULT NULL,
  `lead_type` varchar(50) DEFAULT 'inquiry',
  `lead_source` varchar(50) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `company_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `alternate_phone` varchar(20) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `gst_no` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `city` varchar(255) DEFAULT NULL,
  `state` varchar(255) DEFAULT NULL,
  `country` varchar(255) DEFAULT NULL,
  `product` varchar(255) DEFAULT NULL,
  `service` varchar(255) DEFAULT NULL,
  `budget` decimal(10,2) DEFAULT NULL,
  `requirement` text,
  `lead_status` varchar(50) NOT NULL DEFAULT 'new',
  `final_status` enum('won','lost') CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `priority` varchar(50) NOT NULL DEFAULT 'high',
  `follow_up_date` date DEFAULT NULL,
  `follow_up_time` time DEFAULT NULL,
  `follow_up_note` varchar(255) DEFAULT NULL,
  `assigned_to` bigint DEFAULT NULL,
  `assigned_by` int UNSIGNED DEFAULT NULL,
  `assigned_at` timestamp NULL DEFAULT NULL,
  `remarks` text,
  `internal_note` text,
  `is_converted` enum('Yes','No') DEFAULT 'No',
  `converted_at` datetime DEFAULT NULL,
  `conversion_value` decimal(10,2) DEFAULT NULL,
  `score` tinyint UNSIGNED DEFAULT NULL,
  `status_reason` varchar(255) DEFAULT NULL,
  `last_contacted_at` datetime DEFAULT NULL,
  `last_contacted_by` bigint DEFAULT NULL,
  `status` enum('Active','Inactive') CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL DEFAULT 'Active',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `lead_activities`
--

CREATE TABLE `lead_activities` (
  `id` bigint UNSIGNED NOT NULL,
  `tenant_id` bigint UNSIGNED DEFAULT NULL,
  `lead_id` int UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED DEFAULT NULL,
  `type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `lead_attachments`
--

CREATE TABLE `lead_attachments` (
  `id` bigint UNSIGNED NOT NULL,
  `tenant_id` bigint UNSIGNED DEFAULT NULL,
  `lead_id` int UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED DEFAULT NULL,
  `original_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `stored_path` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `size` bigint UNSIGNED NOT NULL,
  `mime_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `lead_follow_up`
--

CREATE TABLE `lead_follow_up` (
  `id` int NOT NULL,
  `tenant_id` bigint UNSIGNED DEFAULT NULL,
  `lead_id` int UNSIGNED DEFAULT NULL,
  `user_id` int UNSIGNED DEFAULT NULL,
  `lead_response` enum('interested','callback','not_interested','meeting_scheduled') CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `follow_up_date` date DEFAULT NULL,
  `follow_up_time` time DEFAULT NULL,
  `call_status` enum('call_connected','not_reachable','switched_off','busy','wrong_number') DEFAULT NULL,
  `call_note` text,
  `status` enum('Active','Inactive') NOT NULL DEFAULT 'Active',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `lead_integrations`
--

CREATE TABLE `lead_integrations` (
  `id` bigint UNSIGNED NOT NULL,
  `tenant_id` bigint UNSIGNED NOT NULL,
  `platform` varchar(40) COLLATE utf8mb4_unicode_ci NOT NULL,
  `default_lead_type` varchar(40) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `default_lead_status` varchar(40) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `default_priority` varchar(40) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `default_assigned_to` bigint UNSIGNED DEFAULT NULL,
  `pipeline_id` bigint UNSIGNED DEFAULT NULL,
  `field_mapping` json DEFAULT NULL,
  `name` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `secret` text COLLATE utf8mb4_unicode_ci,
  `verify_token` varchar(80) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `leads_created_count` int UNSIGNED NOT NULL DEFAULT '0',
  `last_received_at` timestamp NULL DEFAULT NULL,
  `created_by` bigint UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `lead_integrations`
--

INSERT INTO `lead_integrations` (`id`, `tenant_id`, `platform`, `default_lead_type`, `default_lead_status`, `default_priority`, `default_assigned_to`, `pipeline_id`, `field_mapping`, `name`, `token`, `secret`, `verify_token`, `is_active`, `leads_created_count`, `last_received_at`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 5, 'website', NULL, NULL, NULL, NULL, NULL, NULL, 'trying', 'plF7NQCw8LMEbaPEGmYkqkYIF28POACbG32IAT3PTwxCJm7l', NULL, NULL, 1, 0, NULL, 5, '2026-09-12 16:27:38', '2026-09-12 16:27:38');

-- --------------------------------------------------------

--
-- Table structure for table `lead_integration_logs`
--

CREATE TABLE `lead_integration_logs` (
  `id` bigint UNSIGNED NOT NULL,
  `lead_integration_id` bigint UNSIGNED NOT NULL,
  `tenant_id` bigint UNSIGNED NOT NULL,
  `status` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `external_ref` varchar(190) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_lead_id` bigint UNSIGNED DEFAULT NULL,
  `message` text COLLATE utf8mb4_unicode_ci,
  `payload` longtext COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `lead_tag`
--

CREATE TABLE `lead_tag` (
  `lead_id` int UNSIGNED NOT NULL,
  `tag_id` bigint UNSIGNED NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `master_types`
--

CREATE TABLE `master_types` (
  `id` bigint UNSIGNED NOT NULL,
  `code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `master_types`
--

INSERT INTO `master_types` (`id`, `code`, `name`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'lead_type', 'Lead Type', 1, '2026-09-04 09:01:18', '2026-09-04 09:01:18'),
(2, 'lead_source', 'Lead Source', 1, '2026-09-04 09:01:18', '2026-09-04 09:01:18'),
(3, 'lead_status', 'Lead Status', 1, '2026-09-04 09:01:18', '2026-09-04 09:01:18'),
(4, 'lead_priority', 'Lead Priority', 1, '2026-09-04 09:01:18', '2026-09-04 09:01:18'),
(5, 'order_status', 'Order Status', 1, '2026-09-04 09:01:18', '2026-09-04 09:01:18'),
(6, 'payment_terms', 'Payment Terms', 1, '2026-09-04 09:01:18', '2026-09-04 09:01:18'),
(7, 'payment_status', 'Payment Status', 1, '2026-09-04 09:01:18', '2026-09-04 09:01:18'),
(8, 'currency', 'Currency', 1, '2026-09-04 09:01:18', '2026-09-04 09:01:18'),
(9, 'project_priority', 'Project Priority', 1, '2026-09-04 09:01:18', '2026-09-04 09:01:18'),
(10, 'payment_mode', 'Payment Mode', 1, '2026-09-04 09:01:19', '2026-09-04 09:01:19'),
(11, 'lost_reason', 'Lost Reason', 1, '2026-09-04 09:01:19', '2026-09-04 09:01:19');

-- --------------------------------------------------------

--
-- Table structure for table `master_values`
--

CREATE TABLE `master_values` (
  `id` bigint UNSIGNED NOT NULL,
  `master_type_id` bigint UNSIGNED NOT NULL,
  `tenant_id` bigint UNSIGNED DEFAULT NULL,
  `code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `label` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `color` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sort_order` int UNSIGNED NOT NULL DEFAULT '0',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `master_values`
--

INSERT INTO `master_values` (`id`, `master_type_id`, `tenant_id`, `code`, `label`, `color`, `sort_order`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 1, NULL, 'cold', 'Cold', NULL, 0, 1, '2026-09-04 09:01:18', '2026-09-04 09:01:18'),
(2, 1, NULL, 'warm', 'Warm', NULL, 1, 1, '2026-09-04 09:01:18', '2026-09-04 09:01:18'),
(3, 1, NULL, 'hot', 'Hot', NULL, 2, 1, '2026-09-04 09:01:18', '2026-09-04 09:01:18'),
(4, 1, NULL, 'inquiry', 'Inquiry', NULL, 3, 1, '2026-09-04 09:01:18', '2026-09-04 09:01:18'),
(5, 1, NULL, 'existing_customer', 'Existing Customer', NULL, 4, 1, '2026-09-04 09:01:18', '2026-09-04 09:01:18'),
(6, 2, NULL, 'website', 'Website', NULL, 0, 1, '2026-09-04 09:01:18', '2026-09-04 09:01:18'),
(7, 2, NULL, 'facebook_ads', 'Facebook Ads', NULL, 1, 1, '2026-09-04 09:01:18', '2026-09-04 09:01:18'),
(8, 2, NULL, 'google_ads', 'Google Ads', NULL, 2, 1, '2026-09-04 09:01:18', '2026-09-04 09:01:18'),
(9, 2, NULL, 'referral', 'Referral', NULL, 3, 1, '2026-09-04 09:01:18', '2026-09-04 09:01:18'),
(10, 2, NULL, 'cold_call', 'Cold Call', NULL, 4, 1, '2026-09-04 09:01:18', '2026-09-04 09:01:18'),
(11, 2, NULL, 'email', 'Email', NULL, 5, 1, '2026-09-04 09:01:18', '2026-09-04 09:01:18'),
(12, 2, NULL, 'partner', 'Partner', NULL, 6, 1, '2026-09-04 09:01:18', '2026-09-04 09:01:18'),
(13, 2, NULL, 'linkedIn', 'LinkedIn', NULL, 7, 1, '2026-09-04 09:01:18', '2026-09-04 09:01:18'),
(14, 2, NULL, 'other', 'Other', NULL, 8, 1, '2026-09-04 09:01:18', '2026-09-04 09:01:18'),
(15, 3, NULL, 'new', 'New', NULL, 0, 1, '2026-09-04 09:01:18', '2026-09-04 09:01:18'),
(16, 3, NULL, 'contacted', 'Contacted', NULL, 1, 1, '2026-09-04 09:01:18', '2026-09-04 09:01:18'),
(17, 3, NULL, 'interested', 'Interested', NULL, 2, 1, '2026-09-04 09:01:18', '2026-09-04 09:01:18'),
(18, 3, NULL, 'follow_up', 'Follow Up', NULL, 3, 1, '2026-09-04 09:01:18', '2026-09-04 09:01:18'),
(19, 3, NULL, 'not_interested', 'Not Interested', NULL, 4, 1, '2026-09-04 09:01:18', '2026-09-04 09:01:18'),
(20, 3, NULL, 'converted', 'Converted', NULL, 5, 1, '2026-09-04 09:01:18', '2026-09-04 09:01:18'),
(21, 3, NULL, 'closed', 'Closed', NULL, 6, 1, '2026-09-04 09:01:18', '2026-09-04 09:01:18'),
(22, 4, NULL, 'high', 'High', NULL, 0, 1, '2026-09-04 09:01:18', '2026-09-04 09:01:18'),
(23, 4, NULL, 'medium', 'Medium', NULL, 1, 1, '2026-09-04 09:01:18', '2026-09-04 09:01:18'),
(24, 4, NULL, 'low', 'Low', NULL, 2, 1, '2026-09-04 09:01:18', '2026-09-04 09:01:18'),
(25, 5, NULL, 'new', 'New', NULL, 0, 1, '2026-09-04 09:01:18', '2026-09-04 09:01:18'),
(26, 5, NULL, 'approved', 'Approved', NULL, 1, 1, '2026-09-04 09:01:18', '2026-09-04 09:01:18'),
(27, 5, NULL, 'in_progress', 'In Progress', NULL, 2, 1, '2026-09-04 09:01:18', '2026-09-04 09:01:18'),
(28, 5, NULL, 'on_hold', 'On Hold', NULL, 3, 1, '2026-09-04 09:01:18', '2026-09-04 09:01:18'),
(29, 5, NULL, 'delivered', 'Delivered', NULL, 4, 1, '2026-09-04 09:01:18', '2026-09-04 09:01:18'),
(30, 5, NULL, 'closed', 'Closed', NULL, 5, 1, '2026-09-04 09:01:18', '2026-09-04 09:01:18'),
(31, 5, NULL, 'cancelled', 'Cancelled', NULL, 6, 1, '2026-09-04 09:01:18', '2026-09-04 09:01:18'),
(32, 6, NULL, 'advance', 'Advance', NULL, 0, 1, '2026-09-04 09:01:18', '2026-09-04 09:01:18'),
(33, 6, NULL, 'partial_advance', 'Partial Advance', NULL, 1, 1, '2026-09-04 09:01:18', '2026-09-04 09:01:18'),
(34, 6, NULL, 'on_delivery', 'On Delivery', NULL, 2, 1, '2026-09-04 09:01:18', '2026-09-04 09:01:18'),
(35, 6, NULL, 'net_15', 'Net 15', NULL, 3, 1, '2026-09-04 09:01:18', '2026-09-04 09:01:18'),
(36, 6, NULL, 'net_30', 'Net 30', NULL, 4, 1, '2026-09-04 09:01:18', '2026-09-04 09:01:18'),
(37, 7, NULL, 'pending', 'Pending', NULL, 0, 1, '2026-09-04 09:01:18', '2026-09-04 09:01:18'),
(38, 7, NULL, 'partial', 'Partial', NULL, 1, 1, '2026-09-04 09:01:18', '2026-09-04 09:01:18'),
(39, 7, NULL, 'paid', 'Paid', NULL, 2, 1, '2026-09-04 09:01:18', '2026-09-04 09:01:18'),
(40, 8, NULL, 'INR', 'Indian Rupee (INR)', NULL, 0, 1, '2026-09-04 09:01:18', '2026-09-04 09:01:18'),
(41, 8, NULL, 'USD', 'US Dollar (USD)', NULL, 1, 1, '2026-09-04 09:01:18', '2026-09-04 09:01:18'),
(42, 8, NULL, 'EUR', 'Euro (EUR)', NULL, 2, 1, '2026-09-04 09:01:18', '2026-09-04 09:01:18'),
(43, 9, NULL, 'low', 'Low', NULL, 0, 1, '2026-09-04 09:01:18', '2026-09-04 09:01:18'),
(44, 9, NULL, 'medium', 'Medium', NULL, 1, 1, '2026-09-04 09:01:19', '2026-09-04 09:01:19'),
(45, 9, NULL, 'high', 'High', NULL, 2, 1, '2026-09-04 09:01:19', '2026-09-04 09:01:19'),
(46, 9, NULL, 'urgent', 'Urgent', NULL, 3, 1, '2026-09-04 09:01:19', '2026-09-04 09:01:19'),
(47, 10, NULL, 'cash', 'Cash', NULL, 0, 1, '2026-09-04 09:01:19', '2026-09-04 09:01:19'),
(48, 10, NULL, 'upi', 'UPI', NULL, 1, 1, '2026-09-04 09:01:19', '2026-09-04 09:01:19'),
(49, 10, NULL, 'bank_transfer', 'Bank Transfer', NULL, 2, 1, '2026-09-04 09:01:19', '2026-09-04 09:01:19'),
(50, 10, NULL, 'cheque', 'Cheque', NULL, 3, 1, '2026-09-04 09:01:19', '2026-09-04 09:01:19'),
(51, 10, NULL, 'card', 'Card', NULL, 4, 1, '2026-09-04 09:01:19', '2026-09-04 09:01:19'),
(52, 11, NULL, 'budget', 'Budget Constraints', NULL, 0, 1, '2026-09-04 09:01:19', '2026-09-04 09:01:19'),
(53, 11, NULL, 'no_response', 'No Response', NULL, 1, 1, '2026-09-04 09:01:19', '2026-09-04 09:01:19'),
(54, 11, NULL, 'competitor', 'Chose a Competitor', NULL, 2, 1, '2026-09-04 09:01:19', '2026-09-04 09:01:19'),
(55, 11, NULL, 'not_interested', 'Not Interested', NULL, 3, 1, '2026-09-04 09:01:19', '2026-09-04 09:01:19'),
(56, 11, NULL, 'bad_timing', 'Bad Timing', NULL, 4, 1, '2026-09-04 09:01:19', '2026-09-04 09:01:19'),
(57, 11, NULL, 'other', 'Other', NULL, 5, 1, '2026-09-04 09:01:19', '2026-09-04 09:01:19'),
(58, 2, NULL, 'indiamart', 'IndiaMART', NULL, 8, 1, '2026-09-12 16:25:47', '2026-09-12 16:25:47'),
(59, 2, NULL, 'justdial', 'JustDial', NULL, 9, 1, '2026-09-12 16:25:47', '2026-09-12 16:25:47'),
(60, 2, NULL, 'whatsapp', 'WhatsApp', NULL, 10, 1, '2026-09-12 16:25:47', '2026-09-12 16:25:47'),
(61, 2, NULL, 'zapier', 'Zapier / Make', NULL, 11, 1, '2026-09-12 16:25:47', '2026-09-12 16:25:47');

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int UNSIGNED NOT NULL,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '2014_10_12_000000_create_users_table', 1),
(2, '2014_10_12_100000_create_password_reset_tokens_table', 1),
(3, '2019_08_19_000000_create_failed_jobs_table', 1),
(4, '2019_12_14_000001_create_personal_access_tokens_table', 1),
(5, '2026_08_03_100001_create_admin_details_table', 1),
(6, '2026_08_03_100002_create_company_details_table', 1),
(7, '2026_08_03_100003_create_user_list_table', 1),
(8, '2026_08_03_100004_create_leads_table', 1),
(9, '2026_08_03_100005_create_customer_contact_table', 1),
(10, '2026_08_03_100006_create_lead_follow_up_table', 1),
(11, '2026_08_03_100007_create_project_info_table', 1),
(12, '2026_08_03_100008_create_orders_table', 1),
(13, '2026_08_03_100009_create_payment_details_table', 1),
(14, '2026_08_03_100010_create_user_attendance_table', 1),
(15, '2026_08_03_100011_create_tenants_table', 1),
(16, '2026_08_03_100012_add_tenant_id_to_business_tables', 1),
(17, '2026_08_03_100013_backfill_tenant_data', 1),
(18, '2026_08_03_162351_create_permission_tables', 1),
(19, '2026_08_03_163000_add_identity_columns_to_users_table', 1),
(20, '2026_08_03_164000_align_user_id_columns_with_users_table', 1),
(21, '2026_08_03_164500_add_last_login_to_users_table', 1),
(22, '2026_08_04_100001_create_master_types_table', 1),
(23, '2026_08_04_100002_create_master_values_table', 1),
(24, '2026_08_04_100003_convert_master_data_enum_columns_to_varchar', 1),
(25, '2026_08_05_100001_create_lead_activities_table', 1),
(26, '2026_08_05_100002_create_lead_attachments_table', 1),
(27, '2026_08_05_100003_create_tags_table', 1),
(28, '2026_08_05_100004_add_score_to_leads_table', 1),
(29, '2026_08_07_100001_create_pipelines_table', 1),
(30, '2026_08_07_100002_create_pipeline_stages_table', 1),
(31, '2026_08_07_100003_create_deals_table', 1),
(32, '2026_08_07_100004_create_deal_stage_history_table', 1),
(33, '2026_08_11_100001_enforce_sales_conversion_integrity', 1),
(34, '2026_08_11_100002_create_crm_companies_and_contacts', 1),
(35, '2026_08_11_100003_modernize_legacy_storage_engines', 1),
(36, '2026_08_11_100004_add_saas_fields_to_tenants', 1),
(37, '2026_08_11_100005_normalize_tenant_timezone', 1),
(38, '2026_08_11_100006_create_tasks_table', 1),
(39, '2026_08_11_100007_create_audit_logs_table', 1),
(40, '2026_08_11_100008_create_notifications_and_track_task_reminders', 1),
(41, '2026_08_11_100009_enforce_tenant_lead_numbers', 1),
(42, '2026_08_11_100010_add_tenant_onboarding_fields', 1),
(43, '2026_08_11_100011_add_database_tenancy_fields', 1),
(44, '2026_08_11_100012_create_platform_audit_logs_table', 1),
(45, '2026_08_11_100013_enable_tenant_scoped_roles', 1),
(46, '2026_08_15_100014_add_smtp_settings_to_tenants_table', 1),
(47, '2026_08_15_100015_create_platform_mail_settings_table', 1),
(48, '2026_09_04_100010_add_description_to_roles_table', 2),
(49, '2026_09_05_100001_create_tenant_mail_settings_table', 3),
(50, '2026_09_06_100001_add_avatar_to_users_table', 4),
(51, '2026_09_12_100001_create_lead_integrations_table', 5),
(52, '2026_09_12_100002_create_lead_integration_logs_table', 5),
(53, '2026_09_12_100003_add_routing_fields_to_lead_integrations_table', 6),
(54, '2026_09_13_100001_create_whatsapp_accounts_table', 7),
(55, '2026_09_13_100002_create_whatsapp_account_logs_table', 7);

-- --------------------------------------------------------

--
-- Table structure for table `model_has_permissions`
--

CREATE TABLE `model_has_permissions` (
  `permission_id` bigint UNSIGNED NOT NULL,
  `model_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_id` bigint UNSIGNED NOT NULL,
  `tenant_id` bigint UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `model_has_roles`
--

CREATE TABLE `model_has_roles` (
  `role_id` bigint UNSIGNED NOT NULL,
  `model_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_id` bigint UNSIGNED NOT NULL,
  `tenant_id` bigint UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `model_has_roles`
--

INSERT INTO `model_has_roles` (`role_id`, `model_type`, `model_id`, `tenant_id`) VALUES
(1, 'App\\Models\\User', 1, 0),
(2, 'App\\Models\\User', 2, 2),
(3, 'App\\Models\\User', 3, 3),
(4, 'App\\Models\\User', 4, 4),
(5, 'App\\Models\\User', 5, 5);

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `notifiable_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `notifiable_id` bigint UNSIGNED NOT NULL,
  `data` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `read_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int NOT NULL,
  `tenant_id` bigint UNSIGNED DEFAULT NULL,
  `order_number` varchar(255) DEFAULT NULL,
  `lead_id` int UNSIGNED DEFAULT NULL,
  `invoice_date` datetime DEFAULT NULL,
  `invoice_id` varchar(10) DEFAULT NULL,
  `project_id` int UNSIGNED DEFAULT NULL,
  `user_id` bigint UNSIGNED DEFAULT NULL,
  `sub_total` double(10,2) DEFAULT NULL,
  `discount` double(10,2) DEFAULT NULL,
  `gst` int DEFAULT NULL,
  `total_amount` double(10,2) DEFAULT NULL,
  `order_status` varchar(50) NOT NULL DEFAULT 'new',
  `payment_terms` varchar(50) DEFAULT NULL,
  `payment_status` varchar(50) DEFAULT NULL,
  `currency` varchar(50) NOT NULL DEFAULT 'INR',
  `due_amount` double(10,2) DEFAULT NULL,
  `net_amount` double(10,2) DEFAULT NULL,
  `paid_amount` double(10,2) DEFAULT NULL,
  `status` enum('Active','Inactive') NOT NULL DEFAULT 'Active',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payment_details`
--

CREATE TABLE `payment_details` (
  `id` int NOT NULL,
  `tenant_id` bigint UNSIGNED DEFAULT NULL,
  `order_id` int DEFAULT NULL,
  `payment_mode` varchar(50) DEFAULT NULL,
  `payment_date` datetime DEFAULT NULL,
  `paid_amount` float(12,2) DEFAULT NULL,
  `status` enum('Active','Inactive') NOT NULL DEFAULT 'Active',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `permissions`
--

CREATE TABLE `permissions` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `guard_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `permissions`
--

INSERT INTO `permissions` (`id`, `name`, `guard_name`, `created_at`, `updated_at`) VALUES
(1, 'leads.view', 'web', '2026-09-04 09:01:18', '2026-09-04 09:01:18'),
(2, 'leads.create', 'web', '2026-09-04 09:01:18', '2026-09-04 09:01:18'),
(3, 'leads.edit', 'web', '2026-09-04 09:01:18', '2026-09-04 09:01:18'),
(4, 'leads.delete', 'web', '2026-09-04 09:01:18', '2026-09-04 09:01:18'),
(5, 'leads.import', 'web', '2026-09-04 09:01:18', '2026-09-04 09:01:18'),
(6, 'leads.export', 'web', '2026-09-04 09:01:18', '2026-09-04 09:01:18'),
(7, 'leads.assign', 'web', '2026-09-04 09:01:18', '2026-09-04 09:01:18'),
(8, 'leads.approve', 'web', '2026-09-04 09:01:18', '2026-09-04 09:01:18'),
(9, 'leads.reject', 'web', '2026-09-04 09:01:18', '2026-09-04 09:01:18'),
(10, 'leads.share', 'web', '2026-09-04 09:01:18', '2026-09-04 09:01:18'),
(11, 'leads.manage-settings', 'web', '2026-09-04 09:01:18', '2026-09-04 09:01:18'),
(12, 'orders.view', 'web', '2026-09-04 09:01:18', '2026-09-04 09:01:18'),
(13, 'orders.create', 'web', '2026-09-04 09:01:18', '2026-09-04 09:01:18'),
(14, 'orders.edit', 'web', '2026-09-04 09:01:18', '2026-09-04 09:01:18'),
(15, 'orders.delete', 'web', '2026-09-04 09:01:18', '2026-09-04 09:01:18'),
(16, 'orders.import', 'web', '2026-09-04 09:01:18', '2026-09-04 09:01:18'),
(17, 'orders.export', 'web', '2026-09-04 09:01:18', '2026-09-04 09:01:18'),
(18, 'orders.assign', 'web', '2026-09-04 09:01:18', '2026-09-04 09:01:18'),
(19, 'orders.approve', 'web', '2026-09-04 09:01:18', '2026-09-04 09:01:18'),
(20, 'orders.reject', 'web', '2026-09-04 09:01:18', '2026-09-04 09:01:18'),
(21, 'orders.share', 'web', '2026-09-04 09:01:18', '2026-09-04 09:01:18'),
(22, 'orders.manage-settings', 'web', '2026-09-04 09:01:18', '2026-09-04 09:01:18'),
(23, 'companies.view', 'web', '2026-09-04 09:01:18', '2026-09-04 09:01:18'),
(24, 'companies.create', 'web', '2026-09-04 09:01:18', '2026-09-04 09:01:18'),
(25, 'companies.edit', 'web', '2026-09-04 09:01:18', '2026-09-04 09:01:18'),
(26, 'companies.delete', 'web', '2026-09-04 09:01:18', '2026-09-04 09:01:18'),
(27, 'companies.import', 'web', '2026-09-04 09:01:18', '2026-09-04 09:01:18'),
(28, 'companies.export', 'web', '2026-09-04 09:01:18', '2026-09-04 09:01:18'),
(29, 'companies.assign', 'web', '2026-09-04 09:01:18', '2026-09-04 09:01:18'),
(30, 'companies.approve', 'web', '2026-09-04 09:01:18', '2026-09-04 09:01:18'),
(31, 'companies.reject', 'web', '2026-09-04 09:01:18', '2026-09-04 09:01:18'),
(32, 'companies.share', 'web', '2026-09-04 09:01:18', '2026-09-04 09:01:18'),
(33, 'companies.manage-settings', 'web', '2026-09-04 09:01:18', '2026-09-04 09:01:18'),
(34, 'contacts.view', 'web', '2026-09-04 09:01:18', '2026-09-04 09:01:18'),
(35, 'contacts.create', 'web', '2026-09-04 09:01:18', '2026-09-04 09:01:18'),
(36, 'contacts.edit', 'web', '2026-09-04 09:01:18', '2026-09-04 09:01:18'),
(37, 'contacts.delete', 'web', '2026-09-04 09:01:18', '2026-09-04 09:01:18'),
(38, 'contacts.import', 'web', '2026-09-04 09:01:18', '2026-09-04 09:01:18'),
(39, 'contacts.export', 'web', '2026-09-04 09:01:18', '2026-09-04 09:01:18'),
(40, 'contacts.assign', 'web', '2026-09-04 09:01:18', '2026-09-04 09:01:18'),
(41, 'contacts.approve', 'web', '2026-09-04 09:01:18', '2026-09-04 09:01:18'),
(42, 'contacts.reject', 'web', '2026-09-04 09:01:18', '2026-09-04 09:01:18'),
(43, 'contacts.share', 'web', '2026-09-04 09:01:18', '2026-09-04 09:01:18'),
(44, 'contacts.manage-settings', 'web', '2026-09-04 09:01:18', '2026-09-04 09:01:18'),
(45, 'tasks.view', 'web', '2026-09-04 09:01:18', '2026-09-04 09:01:18'),
(46, 'tasks.create', 'web', '2026-09-04 09:01:18', '2026-09-04 09:01:18'),
(47, 'tasks.edit', 'web', '2026-09-04 09:01:18', '2026-09-04 09:01:18'),
(48, 'tasks.delete', 'web', '2026-09-04 09:01:18', '2026-09-04 09:01:18'),
(49, 'tasks.import', 'web', '2026-09-04 09:01:18', '2026-09-04 09:01:18'),
(50, 'tasks.export', 'web', '2026-09-04 09:01:18', '2026-09-04 09:01:18'),
(51, 'tasks.assign', 'web', '2026-09-04 09:01:18', '2026-09-04 09:01:18'),
(52, 'tasks.approve', 'web', '2026-09-04 09:01:18', '2026-09-04 09:01:18'),
(53, 'tasks.reject', 'web', '2026-09-04 09:01:18', '2026-09-04 09:01:18'),
(54, 'tasks.share', 'web', '2026-09-04 09:01:18', '2026-09-04 09:01:18'),
(55, 'tasks.manage-settings', 'web', '2026-09-04 09:01:18', '2026-09-04 09:01:18'),
(56, 'reports.view', 'web', '2026-09-04 09:01:18', '2026-09-04 09:01:18'),
(57, 'reports.create', 'web', '2026-09-04 09:01:18', '2026-09-04 09:01:18'),
(58, 'reports.edit', 'web', '2026-09-04 09:01:18', '2026-09-04 09:01:18'),
(59, 'reports.delete', 'web', '2026-09-04 09:01:18', '2026-09-04 09:01:18'),
(60, 'reports.import', 'web', '2026-09-04 09:01:18', '2026-09-04 09:01:18'),
(61, 'reports.export', 'web', '2026-09-04 09:01:18', '2026-09-04 09:01:18'),
(62, 'reports.assign', 'web', '2026-09-04 09:01:18', '2026-09-04 09:01:18'),
(63, 'reports.approve', 'web', '2026-09-04 09:01:18', '2026-09-04 09:01:18'),
(64, 'reports.reject', 'web', '2026-09-04 09:01:18', '2026-09-04 09:01:18'),
(65, 'reports.share', 'web', '2026-09-04 09:01:18', '2026-09-04 09:01:18'),
(66, 'reports.manage-settings', 'web', '2026-09-04 09:01:18', '2026-09-04 09:01:18'),
(67, 'audit.view', 'web', '2026-09-04 09:01:18', '2026-09-04 09:01:18'),
(68, 'audit.create', 'web', '2026-09-04 09:01:18', '2026-09-04 09:01:18'),
(69, 'audit.edit', 'web', '2026-09-04 09:01:18', '2026-09-04 09:01:18'),
(70, 'audit.delete', 'web', '2026-09-04 09:01:18', '2026-09-04 09:01:18'),
(71, 'audit.import', 'web', '2026-09-04 09:01:18', '2026-09-04 09:01:18'),
(72, 'audit.export', 'web', '2026-09-04 09:01:18', '2026-09-04 09:01:18'),
(73, 'audit.assign', 'web', '2026-09-04 09:01:18', '2026-09-04 09:01:18'),
(74, 'audit.approve', 'web', '2026-09-04 09:01:18', '2026-09-04 09:01:18'),
(75, 'audit.reject', 'web', '2026-09-04 09:01:18', '2026-09-04 09:01:18'),
(76, 'audit.share', 'web', '2026-09-04 09:01:18', '2026-09-04 09:01:18'),
(77, 'audit.manage-settings', 'web', '2026-09-04 09:01:18', '2026-09-04 09:01:18'),
(78, 'users.view', 'web', '2026-09-04 09:01:18', '2026-09-04 09:01:18'),
(79, 'users.create', 'web', '2026-09-04 09:01:18', '2026-09-04 09:01:18'),
(80, 'users.edit', 'web', '2026-09-04 09:01:18', '2026-09-04 09:01:18'),
(81, 'users.delete', 'web', '2026-09-04 09:01:18', '2026-09-04 09:01:18'),
(82, 'users.import', 'web', '2026-09-04 09:01:18', '2026-09-04 09:01:18'),
(83, 'users.export', 'web', '2026-09-04 09:01:18', '2026-09-04 09:01:18'),
(84, 'users.assign', 'web', '2026-09-04 09:01:18', '2026-09-04 09:01:18'),
(85, 'users.approve', 'web', '2026-09-04 09:01:18', '2026-09-04 09:01:18'),
(86, 'users.reject', 'web', '2026-09-04 09:01:18', '2026-09-04 09:01:18'),
(87, 'users.share', 'web', '2026-09-04 09:01:18', '2026-09-04 09:01:18'),
(88, 'users.manage-settings', 'web', '2026-09-04 09:01:18', '2026-09-04 09:01:18'),
(89, 'roles.view', 'web', '2026-09-04 09:01:18', '2026-09-04 09:01:18'),
(90, 'roles.create', 'web', '2026-09-04 09:01:18', '2026-09-04 09:01:18'),
(91, 'roles.edit', 'web', '2026-09-04 09:01:18', '2026-09-04 09:01:18'),
(92, 'roles.delete', 'web', '2026-09-04 09:01:18', '2026-09-04 09:01:18'),
(93, 'roles.import', 'web', '2026-09-04 09:01:18', '2026-09-04 09:01:18'),
(94, 'roles.export', 'web', '2026-09-04 09:01:18', '2026-09-04 09:01:18'),
(95, 'roles.assign', 'web', '2026-09-04 09:01:18', '2026-09-04 09:01:18'),
(96, 'roles.approve', 'web', '2026-09-04 09:01:18', '2026-09-04 09:01:18'),
(97, 'roles.reject', 'web', '2026-09-04 09:01:18', '2026-09-04 09:01:18'),
(98, 'roles.share', 'web', '2026-09-04 09:01:18', '2026-09-04 09:01:18'),
(99, 'roles.manage-settings', 'web', '2026-09-04 09:01:18', '2026-09-04 09:01:18'),
(100, 'company.view', 'web', '2026-09-04 09:01:18', '2026-09-04 09:01:18'),
(101, 'company.create', 'web', '2026-09-04 09:01:18', '2026-09-04 09:01:18'),
(102, 'company.edit', 'web', '2026-09-04 09:01:18', '2026-09-04 09:01:18'),
(103, 'company.delete', 'web', '2026-09-04 09:01:18', '2026-09-04 09:01:18'),
(104, 'company.import', 'web', '2026-09-04 09:01:18', '2026-09-04 09:01:18'),
(105, 'company.export', 'web', '2026-09-04 09:01:18', '2026-09-04 09:01:18'),
(106, 'company.assign', 'web', '2026-09-04 09:01:18', '2026-09-04 09:01:18'),
(107, 'company.approve', 'web', '2026-09-04 09:01:18', '2026-09-04 09:01:18'),
(108, 'company.reject', 'web', '2026-09-04 09:01:18', '2026-09-04 09:01:18'),
(109, 'company.share', 'web', '2026-09-04 09:01:18', '2026-09-04 09:01:18'),
(110, 'company.manage-settings', 'web', '2026-09-04 09:01:18', '2026-09-04 09:01:18'),
(111, 'attendance.view', 'web', '2026-09-04 09:01:18', '2026-09-04 09:01:18'),
(112, 'attendance.create', 'web', '2026-09-04 09:01:18', '2026-09-04 09:01:18'),
(113, 'attendance.edit', 'web', '2026-09-04 09:01:18', '2026-09-04 09:01:18'),
(114, 'attendance.delete', 'web', '2026-09-04 09:01:18', '2026-09-04 09:01:18'),
(115, 'attendance.import', 'web', '2026-09-04 09:01:18', '2026-09-04 09:01:18'),
(116, 'attendance.export', 'web', '2026-09-04 09:01:18', '2026-09-04 09:01:18'),
(117, 'attendance.assign', 'web', '2026-09-04 09:01:18', '2026-09-04 09:01:18'),
(118, 'attendance.approve', 'web', '2026-09-04 09:01:18', '2026-09-04 09:01:18'),
(119, 'attendance.reject', 'web', '2026-09-04 09:01:18', '2026-09-04 09:01:18'),
(120, 'attendance.share', 'web', '2026-09-04 09:01:18', '2026-09-04 09:01:18'),
(121, 'attendance.manage-settings', 'web', '2026-09-04 09:01:18', '2026-09-04 09:01:18'),
(122, 'masters.view', 'web', '2026-09-04 09:01:18', '2026-09-04 09:01:18'),
(123, 'masters.create', 'web', '2026-09-04 09:01:18', '2026-09-04 09:01:18'),
(124, 'masters.edit', 'web', '2026-09-04 09:01:18', '2026-09-04 09:01:18'),
(125, 'masters.delete', 'web', '2026-09-04 09:01:18', '2026-09-04 09:01:18'),
(126, 'masters.import', 'web', '2026-09-04 09:01:18', '2026-09-04 09:01:18'),
(127, 'masters.export', 'web', '2026-09-04 09:01:18', '2026-09-04 09:01:18'),
(128, 'masters.assign', 'web', '2026-09-04 09:01:18', '2026-09-04 09:01:18'),
(129, 'masters.approve', 'web', '2026-09-04 09:01:18', '2026-09-04 09:01:18'),
(130, 'masters.reject', 'web', '2026-09-04 09:01:18', '2026-09-04 09:01:18'),
(131, 'masters.share', 'web', '2026-09-04 09:01:18', '2026-09-04 09:01:18'),
(132, 'masters.manage-settings', 'web', '2026-09-04 09:01:18', '2026-09-04 09:01:18'),
(133, 'deals.view', 'web', '2026-09-04 09:01:18', '2026-09-04 09:01:18'),
(134, 'deals.create', 'web', '2026-09-04 09:01:18', '2026-09-04 09:01:18'),
(135, 'deals.edit', 'web', '2026-09-04 09:01:18', '2026-09-04 09:01:18'),
(136, 'deals.delete', 'web', '2026-09-04 09:01:18', '2026-09-04 09:01:18'),
(137, 'deals.import', 'web', '2026-09-04 09:01:18', '2026-09-04 09:01:18'),
(138, 'deals.export', 'web', '2026-09-04 09:01:18', '2026-09-04 09:01:18'),
(139, 'deals.assign', 'web', '2026-09-04 09:01:18', '2026-09-04 09:01:18'),
(140, 'deals.approve', 'web', '2026-09-04 09:01:18', '2026-09-04 09:01:18'),
(141, 'deals.reject', 'web', '2026-09-04 09:01:18', '2026-09-04 09:01:18'),
(142, 'deals.share', 'web', '2026-09-04 09:01:18', '2026-09-04 09:01:18'),
(143, 'deals.manage-settings', 'web', '2026-09-04 09:01:18', '2026-09-04 09:01:18'),
(144, 'platform.manage-tenants', 'web', '2026-09-04 09:01:18', '2026-09-04 09:01:18'),
(145, 'users.impersonate', 'web', '2026-09-04 09:01:18', '2026-09-04 09:01:18'),
(146, 'leads.send', 'web', '2026-09-04 11:15:00', '2026-09-04 11:15:00'),
(147, 'orders.send', 'web', '2026-09-04 11:15:00', '2026-09-04 11:15:00'),
(148, 'companies.send', 'web', '2026-09-04 11:15:00', '2026-09-04 11:15:00'),
(149, 'contacts.send', 'web', '2026-09-04 11:15:00', '2026-09-04 11:15:00'),
(150, 'tasks.send', 'web', '2026-09-04 11:15:00', '2026-09-04 11:15:00'),
(151, 'reports.send', 'web', '2026-09-04 11:15:00', '2026-09-04 11:15:00'),
(152, 'audit.send', 'web', '2026-09-04 11:15:00', '2026-09-04 11:15:00'),
(153, 'users.send', 'web', '2026-09-04 11:15:00', '2026-09-04 11:15:00'),
(154, 'roles.send', 'web', '2026-09-04 11:15:00', '2026-09-04 11:15:00'),
(155, 'company.send', 'web', '2026-09-04 11:15:00', '2026-09-04 11:15:00'),
(156, 'attendance.send', 'web', '2026-09-04 11:15:00', '2026-09-04 11:15:00'),
(157, 'masters.send', 'web', '2026-09-04 11:15:00', '2026-09-04 11:15:00'),
(158, 'deals.send', 'web', '2026-09-04 11:15:00', '2026-09-04 11:15:00'),
(159, 'templates.view', 'web', '2026-09-04 11:15:00', '2026-09-04 11:15:00'),
(160, 'templates.create', 'web', '2026-09-04 11:15:00', '2026-09-04 11:15:00'),
(161, 'templates.edit', 'web', '2026-09-04 11:15:00', '2026-09-04 11:15:00'),
(162, 'templates.delete', 'web', '2026-09-04 11:15:00', '2026-09-04 11:15:00'),
(163, 'templates.import', 'web', '2026-09-04 11:15:00', '2026-09-04 11:15:00'),
(164, 'templates.export', 'web', '2026-09-04 11:15:00', '2026-09-04 11:15:00'),
(165, 'templates.assign', 'web', '2026-09-04 11:15:00', '2026-09-04 11:15:00'),
(166, 'templates.approve', 'web', '2026-09-04 11:15:00', '2026-09-04 11:15:00'),
(167, 'templates.reject', 'web', '2026-09-04 11:15:00', '2026-09-04 11:15:00'),
(168, 'templates.share', 'web', '2026-09-04 11:15:00', '2026-09-04 11:15:00'),
(169, 'templates.manage-settings', 'web', '2026-09-04 11:15:00', '2026-09-04 11:15:00'),
(170, 'templates.send', 'web', '2026-09-04 11:15:00', '2026-09-04 11:15:00'),
(171, 'campaigns.view', 'web', '2026-09-04 11:15:00', '2026-09-04 11:15:00'),
(172, 'campaigns.create', 'web', '2026-09-04 11:15:00', '2026-09-04 11:15:00'),
(173, 'campaigns.edit', 'web', '2026-09-04 11:15:00', '2026-09-04 11:15:00'),
(174, 'campaigns.delete', 'web', '2026-09-04 11:15:00', '2026-09-04 11:15:00'),
(175, 'campaigns.import', 'web', '2026-09-04 11:15:00', '2026-09-04 11:15:00'),
(176, 'campaigns.export', 'web', '2026-09-04 11:15:00', '2026-09-04 11:15:00'),
(177, 'campaigns.assign', 'web', '2026-09-04 11:15:00', '2026-09-04 11:15:00'),
(178, 'campaigns.approve', 'web', '2026-09-04 11:15:00', '2026-09-04 11:15:00'),
(179, 'campaigns.reject', 'web', '2026-09-04 11:15:01', '2026-09-04 11:15:01'),
(180, 'campaigns.share', 'web', '2026-09-04 11:15:01', '2026-09-04 11:15:01'),
(181, 'campaigns.manage-settings', 'web', '2026-09-04 11:15:01', '2026-09-04 11:15:01'),
(182, 'campaigns.send', 'web', '2026-09-04 11:15:01', '2026-09-04 11:15:01'),
(183, 'calendar.view', 'web', '2026-09-06 10:10:44', '2026-09-06 10:10:44'),
(184, 'calendar.create', 'web', '2026-09-06 10:10:44', '2026-09-06 10:10:44'),
(185, 'calendar.edit', 'web', '2026-09-06 10:10:44', '2026-09-06 10:10:44'),
(186, 'calendar.delete', 'web', '2026-09-06 10:10:44', '2026-09-06 10:10:44'),
(187, 'calendar.import', 'web', '2026-09-06 10:10:44', '2026-09-06 10:10:44'),
(188, 'calendar.export', 'web', '2026-09-06 10:10:44', '2026-09-06 10:10:44'),
(189, 'calendar.assign', 'web', '2026-09-06 10:10:44', '2026-09-06 10:10:44'),
(190, 'calendar.approve', 'web', '2026-09-06 10:10:44', '2026-09-06 10:10:44'),
(191, 'calendar.reject', 'web', '2026-09-06 10:10:44', '2026-09-06 10:10:44'),
(192, 'calendar.share', 'web', '2026-09-06 10:10:44', '2026-09-06 10:10:44'),
(193, 'calendar.manage-settings', 'web', '2026-09-06 10:10:44', '2026-09-06 10:10:44'),
(194, 'calendar.send', 'web', '2026-09-06 10:10:44', '2026-09-06 10:10:44'),
(195, 'integrations.view', 'web', '2026-09-12 16:25:45', '2026-09-12 16:25:45'),
(196, 'integrations.create', 'web', '2026-09-12 16:25:45', '2026-09-12 16:25:45'),
(197, 'integrations.edit', 'web', '2026-09-12 16:25:45', '2026-09-12 16:25:45'),
(198, 'integrations.delete', 'web', '2026-09-12 16:25:45', '2026-09-12 16:25:45'),
(199, 'integrations.import', 'web', '2026-09-12 16:25:45', '2026-09-12 16:25:45'),
(200, 'integrations.export', 'web', '2026-09-12 16:25:45', '2026-09-12 16:25:45'),
(201, 'integrations.assign', 'web', '2026-09-12 16:25:45', '2026-09-12 16:25:45'),
(202, 'integrations.approve', 'web', '2026-09-12 16:25:45', '2026-09-12 16:25:45'),
(203, 'integrations.reject', 'web', '2026-09-12 16:25:45', '2026-09-12 16:25:45'),
(204, 'integrations.share', 'web', '2026-09-12 16:25:45', '2026-09-12 16:25:45'),
(205, 'integrations.manage-settings', 'web', '2026-09-12 16:25:45', '2026-09-12 16:25:45'),
(206, 'integrations.send', 'web', '2026-09-12 16:25:45', '2026-09-12 16:25:45'),
(207, 'whatsapp.view', 'web', '2026-09-13 15:34:07', '2026-09-13 15:34:07'),
(208, 'whatsapp.create', 'web', '2026-09-13 15:34:07', '2026-09-13 15:34:07'),
(209, 'whatsapp.edit', 'web', '2026-09-13 15:34:07', '2026-09-13 15:34:07'),
(210, 'whatsapp.delete', 'web', '2026-09-13 15:34:07', '2026-09-13 15:34:07'),
(211, 'whatsapp.import', 'web', '2026-09-13 15:34:07', '2026-09-13 15:34:07'),
(212, 'whatsapp.export', 'web', '2026-09-13 15:34:07', '2026-09-13 15:34:07'),
(213, 'whatsapp.assign', 'web', '2026-09-13 15:34:07', '2026-09-13 15:34:07'),
(214, 'whatsapp.approve', 'web', '2026-09-13 15:34:07', '2026-09-13 15:34:07'),
(215, 'whatsapp.reject', 'web', '2026-09-13 15:34:07', '2026-09-13 15:34:07'),
(216, 'whatsapp.share', 'web', '2026-09-13 15:34:07', '2026-09-13 15:34:07'),
(217, 'whatsapp.manage-settings', 'web', '2026-09-13 15:34:07', '2026-09-13 15:34:07'),
(218, 'whatsapp.send', 'web', '2026-09-13 15:34:07', '2026-09-13 15:34:07');

-- --------------------------------------------------------

--
-- Table structure for table `personal_access_tokens`
--

CREATE TABLE `personal_access_tokens` (
  `id` bigint UNSIGNED NOT NULL,
  `tokenable_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tokenable_id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `abilities` text COLLATE utf8mb4_unicode_ci,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pipelines`
--

CREATE TABLE `pipelines` (
  `id` bigint UNSIGNED NOT NULL,
  `tenant_id` bigint UNSIGNED DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_default` tinyint(1) NOT NULL DEFAULT '0',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `sort_order` int UNSIGNED NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pipeline_stages`
--

CREATE TABLE `pipeline_stages` (
  `id` bigint UNSIGNED NOT NULL,
  `tenant_id` bigint UNSIGNED DEFAULT NULL,
  `pipeline_id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `sort_order` int UNSIGNED NOT NULL DEFAULT '0',
  `is_won` tinyint(1) NOT NULL DEFAULT '0',
  `is_lost` tinyint(1) NOT NULL DEFAULT '0',
  `probability` tinyint UNSIGNED DEFAULT NULL,
  `color` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `platform_audit_logs`
--

CREATE TABLE `platform_audit_logs` (
  `id` bigint UNSIGNED NOT NULL,
  `actor_id` bigint UNSIGNED DEFAULT NULL,
  `tenant_id` bigint UNSIGNED DEFAULT NULL,
  `target_user_id` bigint UNSIGNED DEFAULT NULL,
  `event` varchar(80) COLLATE utf8mb4_unicode_ci NOT NULL,
  `metadata` json DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `platform_audit_logs`
--

INSERT INTO `platform_audit_logs` (`id`, `actor_id`, `tenant_id`, `target_user_id`, `event`, `metadata`, `ip_address`, `created_at`, `updated_at`) VALUES
(1, NULL, NULL, NULL, 'tenant.registered', '{\"source\": \"self_service\"}', '157.49.127.138', '2026-09-04 09:18:55', '2026-09-04 09:18:55'),
(2, NULL, NULL, NULL, 'tenant.provisioned', '{\"source\": \"self_service\"}', '157.49.127.138', '2026-09-04 09:18:56', '2026-09-04 09:18:56'),
(3, NULL, NULL, NULL, 'tenant.approved', '{\"source\": \"self_service_auto\"}', '157.49.127.138', '2026-09-04 09:18:56', '2026-09-04 09:18:56'),
(4, 1, NULL, NULL, 'tenant.provisioned', '{\"database_name\": \"crm_tenant_1_default_company\"}', '157.49.17.231', '2026-09-06 16:32:39', '2026-09-06 16:32:39'),
(5, 1, NULL, NULL, 'user.impersonation_started', NULL, '157.49.17.231', '2026-09-06 16:35:51', '2026-09-06 16:35:51'),
(6, 1, NULL, NULL, 'user.updated', '[]', '157.49.17.231', '2026-09-06 17:54:18', '2026-09-06 17:54:18'),
(7, 1, NULL, NULL, 'tenant.deleted', '{\"backup_path\": \"/var/www/crm/storage/app/tenant-backups/default-company-1-2026-09-06_175617.sql\", \"users_removed\": 0}', '157.49.17.231', '2026-09-06 17:56:18', '2026-09-06 17:56:18'),
(8, 1, NULL, NULL, 'tenant.provisioned', '{\"database_name\": \"crm_tenant_2_wonder_tech_xuvzuh9g\"}', '157.49.17.231', '2026-09-06 17:57:12', '2026-09-06 17:57:12'),
(9, 1, NULL, NULL, 'tenant.health_checked', '{\"healthy\": true}', '157.49.17.231', '2026-09-06 17:57:20', '2026-09-06 17:57:20'),
(10, 1, NULL, NULL, 'tenant.updated', '{\"after\": {\"name\": \"Wonder tech\", \"plan\": \"trial\", \"status\": \"Active\", \"max_users\": \"1\", \"contact_email\": \"vrishbhansingh321@gmail.com\", \"trial_ends_at\": \"2026-09-18T00:00:00.000000Z\"}, \"before\": {\"name\": \"Wonder tech\", \"plan\": \"trial\", \"status\": \"Active\", \"max_users\": null, \"contact_email\": \"vrishbhansingh321@gmail.com\", \"trial_ends_at\": \"2026-09-18T09:18:56.000000Z\"}}', '157.49.17.231', '2026-09-06 18:15:59', '2026-09-06 18:15:59'),
(11, 1, NULL, NULL, 'tenant.deleted', '{\"backup_path\": \"/var/www/crm/storage/app/tenant-backups/wonder-tech-xuvzuh9g-2-2026-09-06_181734.sql\", \"users_removed\": 1}', '157.49.17.231', '2026-09-06 18:17:34', '2026-09-06 18:17:34'),
(14, 1, NULL, NULL, 'tenant.deleted', '{\"backup_path\": null, \"users_removed\": 0}', '157.49.17.231', '2026-09-06 18:43:51', '2026-09-06 18:43:51'),
(15, 1, NULL, NULL, 'tenant.approved', '[]', '157.49.17.231', '2026-09-06 18:44:56', '2026-09-06 18:44:56'),
(16, 1, NULL, NULL, 'tenant.deleted', '{\"backup_path\": \"/var/www/crm/storage/app/tenant-backups/wonder-tech-dxipz8pu-4-2026-09-06_184536.sql\", \"users_removed\": 0}', '157.49.17.231', '2026-09-06 18:45:36', '2026-09-06 18:45:36'),
(20, 1, 5, NULL, 'tenant.provisioned', '{\"database_name\": \"crm_tenant_5_wonder_technologies_l7knowjx\"}', '157.49.17.231', '2026-09-06 19:05:29', '2026-09-06 19:05:29'),
(21, 1, 5, NULL, 'tenant.health_checked', '{\"healthy\": true}', '157.49.17.231', '2026-09-06 19:05:33', '2026-09-06 19:05:33'),
(22, 1, 5, NULL, 'tenant.updated', '{\"after\": {\"name\": \"Wonder technologies\", \"plan\": \"trial\", \"status\": \"Active\", \"max_users\": null, \"contact_email\": \"vrishbhansingh321@gmail.com\", \"trial_ends_at\": \"2026-09-20T00:00:00.000000Z\"}, \"before\": {\"name\": \"Wonder technologies\", \"plan\": \"trial\", \"status\": \"Active\", \"max_users\": null, \"contact_email\": \"vrishbhansingh321@gmail.com\", \"trial_ends_at\": \"2026-09-20T19:05:04.000000Z\"}}', '157.49.17.231', '2026-09-06 19:05:48', '2026-09-06 19:05:48'),
(23, 5, 5, NULL, 'role.created', '{\"role\": \"Agent\"}', '157.49.17.231', '2026-09-06 19:07:43', '2026-09-06 19:07:43'),
(24, 5, 5, NULL, 'role.created', '{\"role\": \"Manager\"}', '157.49.17.231', '2026-09-06 19:08:49', '2026-09-06 19:08:49');

-- --------------------------------------------------------

--
-- Table structure for table `platform_mail_settings`
--

CREATE TABLE `platform_mail_settings` (
  `id` bigint UNSIGNED NOT NULL,
  `smtp_host` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `smtp_port` smallint UNSIGNED DEFAULT NULL,
  `smtp_encryption` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `smtp_username` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `smtp_password` text COLLATE utf8mb4_unicode_ci,
  `smtp_from_address` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `smtp_from_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `platform_mail_settings`
--

INSERT INTO `platform_mail_settings` (`id`, `smtp_host`, `smtp_port`, `smtp_encryption`, `smtp_username`, `smtp_password`, `smtp_from_address`, `smtp_from_name`, `created_at`, `updated_at`) VALUES
(1, 'smtp.gmail.com', 587, 'tls', 'vrishbhansingh321@gmail.com', 'eyJpdiI6IlQ4K3ZMUmFTS25qNVdHQUZMQmpOV3c9PSIsInZhbHVlIjoiTUVDNGhhWi9uQnY4Ym5ib1ozbXVKNkEzRnlrV1JLbk02S0RGblAwdXRMOD0iLCJtYWMiOiI1NDFlOGY1OTk3ZmZjYWNiYjg5NmNjOWI2MDUxMjg0NmY4NWZhZWI1ODkzZjE0MzI3YmZhOGM2MzUzYjg4NTViIiwidGFnIjoiIn0=', 'vrishbhansingh321@gmail.com', 'CRM', '2026-09-05 05:47:03', '2026-09-06 15:17:29');

-- --------------------------------------------------------

--
-- Table structure for table `project_info`
--

CREATE TABLE `project_info` (
  `id` int UNSIGNED NOT NULL,
  `tenant_id` bigint UNSIGNED DEFAULT NULL,
  `project_name` varchar(255) DEFAULT NULL,
  `tech_stack` varchar(255) DEFAULT NULL,
  `expected_start_date` date DEFAULT NULL,
  `expected_delivery_date` date DEFAULT NULL,
  `actual_delivery_date` date DEFAULT NULL,
  `priority` varchar(50) DEFAULT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci,
  `status` enum('Active','Inactive') NOT NULL DEFAULT 'Active',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `id` bigint UNSIGNED NOT NULL,
  `tenant_id` bigint UNSIGNED DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `guard_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`id`, `tenant_id`, `name`, `description`, `guard_name`, `created_at`, `updated_at`) VALUES
(1, 0, 'Super Admin', NULL, 'web', '2026-09-04 09:01:18', '2026-09-04 09:01:18'),
(2, 2, 'Admin', NULL, 'web', '2026-09-04 09:18:55', '2026-09-04 09:18:55'),
(3, 3, 'Admin', NULL, 'web', '2026-09-06 18:23:43', '2026-09-06 18:23:43'),
(4, 4, 'Admin', NULL, 'web', '2026-09-06 18:38:18', '2026-09-06 18:38:18'),
(5, 5, 'Admin', NULL, 'web', '2026-09-06 19:05:02', '2026-09-06 19:05:02'),
(6, 5, 'Agent', NULL, 'web', '2026-09-06 19:07:43', '2026-09-06 19:07:43'),
(7, 5, 'Manager', 'manages', 'web', '2026-09-06 19:08:49', '2026-09-06 19:08:49');

-- --------------------------------------------------------

--
-- Table structure for table `role_has_permissions`
--

CREATE TABLE `role_has_permissions` (
  `permission_id` bigint UNSIGNED NOT NULL,
  `role_id` bigint UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `role_has_permissions`
--

INSERT INTO `role_has_permissions` (`permission_id`, `role_id`) VALUES
(1, 1),
(2, 1),
(3, 1),
(4, 1),
(5, 1),
(6, 1),
(7, 1),
(8, 1),
(9, 1),
(10, 1),
(11, 1),
(12, 1),
(13, 1),
(14, 1),
(15, 1),
(16, 1),
(17, 1),
(18, 1),
(19, 1),
(20, 1),
(21, 1),
(22, 1),
(23, 1),
(24, 1),
(25, 1),
(26, 1),
(27, 1),
(28, 1),
(29, 1),
(30, 1),
(31, 1),
(32, 1),
(33, 1),
(34, 1),
(35, 1),
(36, 1),
(37, 1),
(38, 1),
(39, 1),
(40, 1),
(41, 1),
(42, 1),
(43, 1),
(44, 1),
(45, 1),
(46, 1),
(47, 1),
(48, 1),
(49, 1),
(50, 1),
(51, 1),
(52, 1),
(53, 1),
(54, 1),
(55, 1),
(56, 1),
(57, 1),
(58, 1),
(59, 1),
(60, 1),
(61, 1),
(62, 1),
(63, 1),
(64, 1),
(65, 1),
(66, 1),
(67, 1),
(68, 1),
(69, 1),
(70, 1),
(71, 1),
(72, 1),
(73, 1),
(74, 1),
(75, 1),
(76, 1),
(77, 1),
(78, 1),
(79, 1),
(80, 1),
(81, 1),
(82, 1),
(83, 1),
(84, 1),
(85, 1),
(86, 1),
(87, 1),
(88, 1),
(89, 1),
(90, 1),
(91, 1),
(92, 1),
(93, 1),
(94, 1),
(95, 1),
(96, 1),
(97, 1),
(98, 1),
(99, 1),
(100, 1),
(101, 1),
(102, 1),
(103, 1),
(104, 1),
(105, 1),
(106, 1),
(107, 1),
(108, 1),
(109, 1),
(110, 1),
(111, 1),
(112, 1),
(113, 1),
(114, 1),
(115, 1),
(116, 1),
(117, 1),
(118, 1),
(119, 1),
(120, 1),
(121, 1),
(122, 1),
(123, 1),
(124, 1),
(125, 1),
(126, 1),
(127, 1),
(128, 1),
(129, 1),
(130, 1),
(131, 1),
(132, 1),
(133, 1),
(134, 1),
(135, 1),
(136, 1),
(137, 1),
(138, 1),
(139, 1),
(140, 1),
(141, 1),
(142, 1),
(143, 1),
(144, 1),
(145, 1),
(146, 1),
(147, 1),
(148, 1),
(149, 1),
(150, 1),
(151, 1),
(152, 1),
(153, 1),
(154, 1),
(155, 1),
(156, 1),
(157, 1),
(158, 1),
(159, 1),
(160, 1),
(161, 1),
(162, 1),
(163, 1),
(164, 1),
(165, 1),
(166, 1),
(167, 1),
(168, 1),
(169, 1),
(170, 1),
(171, 1),
(172, 1),
(173, 1),
(174, 1),
(175, 1),
(176, 1),
(177, 1),
(178, 1),
(179, 1),
(180, 1),
(181, 1),
(182, 1),
(183, 1),
(184, 1),
(185, 1),
(186, 1),
(187, 1),
(188, 1),
(189, 1),
(190, 1),
(191, 1),
(192, 1),
(193, 1),
(194, 1),
(195, 1),
(196, 1),
(197, 1),
(198, 1),
(199, 1),
(200, 1),
(201, 1),
(202, 1),
(203, 1),
(204, 1),
(205, 1),
(206, 1),
(207, 1),
(208, 1),
(209, 1),
(210, 1),
(211, 1),
(212, 1),
(213, 1),
(214, 1),
(215, 1),
(216, 1),
(217, 1),
(218, 1),
(1, 2),
(2, 2),
(3, 2),
(4, 2),
(5, 2),
(6, 2),
(7, 2),
(8, 2),
(9, 2),
(10, 2),
(11, 2),
(12, 2),
(13, 2),
(14, 2),
(15, 2),
(16, 2),
(17, 2),
(18, 2),
(19, 2),
(20, 2),
(21, 2),
(22, 2),
(23, 2),
(24, 2),
(25, 2),
(26, 2),
(27, 2),
(28, 2),
(29, 2),
(30, 2),
(31, 2),
(32, 2),
(33, 2),
(34, 2),
(35, 2),
(36, 2),
(37, 2),
(38, 2),
(39, 2),
(40, 2),
(41, 2),
(42, 2),
(43, 2),
(44, 2),
(45, 2),
(46, 2),
(47, 2),
(48, 2),
(49, 2),
(50, 2),
(51, 2),
(52, 2),
(53, 2),
(54, 2),
(55, 2),
(56, 2),
(57, 2),
(58, 2),
(59, 2),
(60, 2),
(61, 2),
(62, 2),
(63, 2),
(64, 2),
(65, 2),
(66, 2),
(67, 2),
(68, 2),
(69, 2),
(70, 2),
(71, 2),
(72, 2),
(73, 2),
(74, 2),
(75, 2),
(76, 2),
(77, 2),
(78, 2),
(79, 2),
(80, 2),
(81, 2),
(82, 2),
(83, 2),
(84, 2),
(85, 2),
(86, 2),
(87, 2),
(88, 2),
(89, 2),
(90, 2),
(91, 2),
(92, 2),
(93, 2),
(94, 2),
(95, 2),
(96, 2),
(97, 2),
(98, 2),
(99, 2),
(100, 2),
(101, 2),
(102, 2),
(103, 2),
(104, 2),
(105, 2),
(106, 2),
(107, 2),
(108, 2),
(109, 2),
(110, 2),
(111, 2),
(112, 2),
(113, 2),
(114, 2),
(115, 2),
(116, 2),
(117, 2),
(118, 2),
(119, 2),
(120, 2),
(121, 2),
(122, 2),
(123, 2),
(124, 2),
(125, 2),
(126, 2),
(127, 2),
(128, 2),
(129, 2),
(130, 2),
(131, 2),
(132, 2),
(133, 2),
(134, 2),
(135, 2),
(136, 2),
(137, 2),
(138, 2),
(139, 2),
(140, 2),
(141, 2),
(142, 2),
(143, 2),
(145, 2),
(146, 2),
(147, 2),
(148, 2),
(149, 2),
(150, 2),
(151, 2),
(152, 2),
(153, 2),
(154, 2),
(155, 2),
(156, 2),
(157, 2),
(158, 2),
(159, 2),
(160, 2),
(161, 2),
(162, 2),
(163, 2),
(164, 2),
(165, 2),
(166, 2),
(167, 2),
(168, 2),
(169, 2),
(170, 2),
(171, 2),
(172, 2),
(173, 2),
(174, 2),
(175, 2),
(176, 2),
(177, 2),
(178, 2),
(179, 2),
(180, 2),
(181, 2),
(182, 2),
(183, 2),
(184, 2),
(185, 2),
(186, 2),
(187, 2),
(188, 2),
(189, 2),
(190, 2),
(191, 2),
(192, 2),
(193, 2),
(194, 2),
(1, 3),
(2, 3),
(3, 3),
(4, 3),
(5, 3),
(6, 3),
(7, 3),
(8, 3),
(9, 3),
(10, 3),
(11, 3),
(12, 3),
(13, 3),
(14, 3),
(15, 3),
(16, 3),
(17, 3),
(18, 3),
(19, 3),
(20, 3),
(21, 3),
(22, 3),
(23, 3),
(24, 3),
(25, 3),
(26, 3),
(27, 3),
(28, 3),
(29, 3),
(30, 3),
(31, 3),
(32, 3),
(33, 3),
(34, 3),
(35, 3),
(36, 3),
(37, 3),
(38, 3),
(39, 3),
(40, 3),
(41, 3),
(42, 3),
(43, 3),
(44, 3),
(45, 3),
(46, 3),
(47, 3),
(48, 3),
(49, 3),
(50, 3),
(51, 3),
(52, 3),
(53, 3),
(54, 3),
(55, 3),
(56, 3),
(57, 3),
(58, 3),
(59, 3),
(60, 3),
(61, 3),
(62, 3),
(63, 3),
(64, 3),
(65, 3),
(66, 3),
(67, 3),
(68, 3),
(69, 3),
(70, 3),
(71, 3),
(72, 3),
(73, 3),
(74, 3),
(75, 3),
(76, 3),
(77, 3),
(78, 3),
(79, 3),
(80, 3),
(81, 3),
(82, 3),
(83, 3),
(84, 3),
(85, 3),
(86, 3),
(87, 3),
(88, 3),
(89, 3),
(90, 3),
(91, 3),
(92, 3),
(93, 3),
(94, 3),
(95, 3),
(96, 3),
(97, 3),
(98, 3),
(99, 3),
(100, 3),
(101, 3),
(102, 3),
(103, 3),
(104, 3),
(105, 3),
(106, 3),
(107, 3),
(108, 3),
(109, 3),
(110, 3),
(111, 3),
(112, 3),
(113, 3),
(114, 3),
(115, 3),
(116, 3),
(117, 3),
(118, 3),
(119, 3),
(120, 3),
(121, 3),
(122, 3),
(123, 3),
(124, 3),
(125, 3),
(126, 3),
(127, 3),
(128, 3),
(129, 3),
(130, 3),
(131, 3),
(132, 3),
(133, 3),
(134, 3),
(135, 3),
(136, 3),
(137, 3),
(138, 3),
(139, 3),
(140, 3),
(141, 3),
(142, 3),
(143, 3),
(145, 3),
(146, 3),
(147, 3),
(148, 3),
(149, 3),
(150, 3),
(151, 3),
(152, 3),
(153, 3),
(154, 3),
(155, 3),
(156, 3),
(157, 3),
(158, 3),
(159, 3),
(160, 3),
(161, 3),
(162, 3),
(163, 3),
(164, 3),
(165, 3),
(166, 3),
(167, 3),
(168, 3),
(169, 3),
(170, 3),
(171, 3),
(172, 3),
(173, 3),
(174, 3),
(175, 3),
(176, 3),
(177, 3),
(178, 3),
(179, 3),
(180, 3),
(181, 3),
(182, 3),
(183, 3),
(184, 3),
(185, 3),
(186, 3),
(187, 3),
(188, 3),
(189, 3),
(190, 3),
(191, 3),
(192, 3),
(193, 3),
(194, 3),
(1, 4),
(2, 4),
(3, 4),
(4, 4),
(5, 4),
(6, 4),
(7, 4),
(8, 4),
(9, 4),
(10, 4),
(11, 4),
(12, 4),
(13, 4),
(14, 4),
(15, 4),
(16, 4),
(17, 4),
(18, 4),
(19, 4),
(20, 4),
(21, 4),
(22, 4),
(23, 4),
(24, 4),
(25, 4),
(26, 4),
(27, 4),
(28, 4),
(29, 4),
(30, 4),
(31, 4),
(32, 4),
(33, 4),
(34, 4),
(35, 4),
(36, 4),
(37, 4),
(38, 4),
(39, 4),
(40, 4),
(41, 4),
(42, 4),
(43, 4),
(44, 4),
(45, 4),
(46, 4),
(47, 4),
(48, 4),
(49, 4),
(50, 4),
(51, 4),
(52, 4),
(53, 4),
(54, 4),
(55, 4),
(56, 4),
(57, 4),
(58, 4),
(59, 4),
(60, 4),
(61, 4),
(62, 4),
(63, 4),
(64, 4),
(65, 4),
(66, 4),
(67, 4),
(68, 4),
(69, 4),
(70, 4),
(71, 4),
(72, 4),
(73, 4),
(74, 4),
(75, 4),
(76, 4),
(77, 4),
(78, 4),
(79, 4),
(80, 4),
(81, 4),
(82, 4),
(83, 4),
(84, 4),
(85, 4),
(86, 4),
(87, 4),
(88, 4),
(89, 4),
(90, 4),
(91, 4),
(92, 4),
(93, 4),
(94, 4),
(95, 4),
(96, 4),
(97, 4),
(98, 4),
(99, 4),
(100, 4),
(101, 4),
(102, 4),
(103, 4),
(104, 4),
(105, 4),
(106, 4),
(107, 4),
(108, 4),
(109, 4),
(110, 4),
(111, 4),
(112, 4),
(113, 4),
(114, 4),
(115, 4),
(116, 4),
(117, 4),
(118, 4),
(119, 4),
(120, 4),
(121, 4),
(122, 4),
(123, 4),
(124, 4),
(125, 4),
(126, 4),
(127, 4),
(128, 4),
(129, 4),
(130, 4),
(131, 4),
(132, 4),
(133, 4),
(134, 4),
(135, 4),
(136, 4),
(137, 4),
(138, 4),
(139, 4),
(140, 4),
(141, 4),
(142, 4),
(143, 4),
(145, 4),
(146, 4),
(147, 4),
(148, 4),
(149, 4),
(150, 4),
(151, 4),
(152, 4),
(153, 4),
(154, 4),
(155, 4),
(156, 4),
(157, 4),
(158, 4),
(159, 4),
(160, 4),
(161, 4),
(162, 4),
(163, 4),
(164, 4),
(165, 4),
(166, 4),
(167, 4),
(168, 4),
(169, 4),
(170, 4),
(171, 4),
(172, 4),
(173, 4),
(174, 4),
(175, 4),
(176, 4),
(177, 4),
(178, 4),
(179, 4),
(180, 4),
(181, 4),
(182, 4),
(183, 4),
(184, 4),
(185, 4),
(186, 4),
(187, 4),
(188, 4),
(189, 4),
(190, 4),
(191, 4),
(192, 4),
(193, 4),
(194, 4),
(1, 5),
(2, 5),
(3, 5),
(4, 5),
(5, 5),
(6, 5),
(7, 5),
(8, 5),
(9, 5),
(10, 5),
(11, 5),
(12, 5),
(13, 5),
(14, 5),
(15, 5),
(16, 5),
(17, 5),
(18, 5),
(19, 5),
(20, 5),
(21, 5),
(22, 5),
(23, 5),
(24, 5),
(25, 5),
(26, 5),
(27, 5),
(28, 5),
(29, 5),
(30, 5),
(31, 5),
(32, 5),
(33, 5),
(34, 5),
(35, 5),
(36, 5),
(37, 5),
(38, 5),
(39, 5),
(40, 5),
(41, 5),
(42, 5),
(43, 5),
(44, 5),
(45, 5),
(46, 5),
(47, 5),
(48, 5),
(49, 5),
(50, 5),
(51, 5),
(52, 5),
(53, 5),
(54, 5),
(55, 5),
(56, 5),
(57, 5),
(58, 5),
(59, 5),
(60, 5),
(61, 5),
(62, 5),
(63, 5),
(64, 5),
(65, 5),
(66, 5),
(67, 5),
(68, 5),
(69, 5),
(70, 5),
(71, 5),
(72, 5),
(73, 5),
(74, 5),
(75, 5),
(76, 5),
(77, 5),
(78, 5),
(79, 5),
(80, 5),
(81, 5),
(82, 5),
(83, 5),
(84, 5),
(85, 5),
(86, 5),
(87, 5),
(88, 5),
(89, 5),
(90, 5),
(91, 5),
(92, 5),
(93, 5),
(94, 5),
(95, 5),
(96, 5),
(97, 5),
(98, 5),
(99, 5),
(100, 5),
(101, 5),
(102, 5),
(103, 5),
(104, 5),
(105, 5),
(106, 5),
(107, 5),
(108, 5),
(109, 5),
(110, 5),
(111, 5),
(112, 5),
(113, 5),
(114, 5),
(115, 5),
(116, 5),
(117, 5),
(118, 5),
(119, 5),
(120, 5),
(121, 5),
(122, 5),
(123, 5),
(124, 5),
(125, 5),
(126, 5),
(127, 5),
(128, 5),
(129, 5),
(130, 5),
(131, 5),
(132, 5),
(133, 5),
(134, 5),
(135, 5),
(136, 5),
(137, 5),
(138, 5),
(139, 5),
(140, 5),
(141, 5),
(142, 5),
(143, 5),
(145, 5),
(146, 5),
(147, 5),
(148, 5),
(149, 5),
(150, 5),
(151, 5),
(152, 5),
(153, 5),
(154, 5),
(155, 5),
(156, 5),
(157, 5),
(158, 5),
(159, 5),
(160, 5),
(161, 5),
(162, 5),
(163, 5),
(164, 5),
(165, 5),
(166, 5),
(167, 5),
(168, 5),
(169, 5),
(170, 5),
(171, 5),
(172, 5),
(173, 5),
(174, 5),
(175, 5),
(176, 5),
(177, 5),
(178, 5),
(179, 5),
(180, 5),
(181, 5),
(182, 5),
(183, 5),
(184, 5),
(185, 5),
(186, 5),
(187, 5),
(188, 5),
(189, 5),
(190, 5),
(191, 5),
(192, 5),
(193, 5),
(194, 5),
(195, 5),
(196, 5),
(197, 5),
(198, 5),
(199, 5),
(200, 5),
(201, 5),
(202, 5),
(203, 5),
(204, 5),
(205, 5),
(206, 5),
(207, 5),
(208, 5),
(209, 5),
(210, 5),
(211, 5),
(212, 5),
(213, 5),
(214, 5),
(215, 5),
(216, 5),
(217, 5),
(218, 5);

-- --------------------------------------------------------

--
-- Table structure for table `tags`
--

CREATE TABLE `tags` (
  `id` bigint UNSIGNED NOT NULL,
  `tenant_id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `color` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tasks`
--

CREATE TABLE `tasks` (
  `id` bigint UNSIGNED NOT NULL,
  `tenant_id` bigint UNSIGNED NOT NULL,
  `assigned_to` bigint UNSIGNED DEFAULT NULL,
  `created_by` bigint UNSIGNED DEFAULT NULL,
  `related_type` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `related_id` bigint UNSIGNED DEFAULT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `priority` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'medium',
  `status` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'todo',
  `due_at` timestamp NULL DEFAULT NULL,
  `remind_at` timestamp NULL DEFAULT NULL,
  `notification_sent_at` timestamp NULL DEFAULT NULL,
  `completed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tenants`
--

CREATE TABLE `tenants` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` enum('Active','Inactive') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Active',
  `approval_status` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'approved',
  `signup_source` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'platform',
  `contact_email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `plan` varchar(40) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'standard',
  `timezone` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Asia/Kolkata',
  `locale` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'en',
  `max_users` int UNSIGNED DEFAULT NULL,
  `trial_ends_at` timestamp NULL DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `approved_by` bigint UNSIGNED DEFAULT NULL,
  `admin_user_id` bigint UNSIGNED DEFAULT NULL,
  `database_name` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `database_host` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `database_port` smallint UNSIGNED DEFAULT NULL,
  `database_username` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `database_password` text COLLATE utf8mb4_unicode_ci,
  `provision_status` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `schema_version` int UNSIGNED NOT NULL DEFAULT '1',
  `provisioned_at` timestamp NULL DEFAULT NULL,
  `last_health_check_at` timestamp NULL DEFAULT NULL,
  `last_health_status` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `provision_error` text COLLATE utf8mb4_unicode_ci,
  `rejection_reason` text COLLATE utf8mb4_unicode_ci,
  `settings` json DEFAULT NULL,
  `smtp_enabled` tinyint(1) NOT NULL DEFAULT '0',
  `smtp_host` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `smtp_port` smallint UNSIGNED DEFAULT NULL,
  `smtp_encryption` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `smtp_username` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `smtp_password` text COLLATE utf8mb4_unicode_ci,
  `smtp_from_address` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `smtp_from_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `tenants`
--

INSERT INTO `tenants` (`id`, `name`, `slug`, `status`, `approval_status`, `signup_source`, `contact_email`, `plan`, `timezone`, `locale`, `max_users`, `trial_ends_at`, `approved_at`, `approved_by`, `admin_user_id`, `database_name`, `database_host`, `database_port`, `database_username`, `database_password`, `provision_status`, `schema_version`, `provisioned_at`, `last_health_check_at`, `last_health_status`, `provision_error`, `rejection_reason`, `settings`, `smtp_enabled`, `smtp_host`, `smtp_port`, `smtp_encryption`, `smtp_username`, `smtp_password`, `smtp_from_address`, `smtp_from_name`, `created_at`, `updated_at`) VALUES
(5, 'Wonder technologies', 'wonder-technologies-l7knowjx', 'Active', 'approved', 'self_service', 'vrishbhansingh321@gmail.com', 'trial', 'Asia/Kolkata', 'en', NULL, '2026-09-20 00:00:00', '2026-09-06 19:05:04', NULL, 5, 'crm_tenant_5_wonder_technologies_l7knowjx', '127.0.0.1', 3306, 'root', 'eyJpdiI6IkhsdHFPZzFWcnZib3JEdnhZR2Vranc9PSIsInZhbHVlIjoiamZLUUk4MXZDdlVyN09LTXZRczRUUT09IiwibWFjIjoiNDIzYjRjNmRkMmFkYzNhMmViZTEwMjhiM2I5ZTJjOWNiOTc2NjM3ZWU2MDBlMjdjYzYyNTdjYTZlM2E4ODQ0YSIsInRhZyI6IiJ9', 'ready', 1, '2026-09-06 19:05:29', '2026-09-06 19:05:33', 'healthy', NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-06 19:05:02', '2026-09-06 19:05:48');

-- --------------------------------------------------------

--
-- Table structure for table `tenant_mail_settings`
--

CREATE TABLE `tenant_mail_settings` (
  `id` bigint UNSIGNED NOT NULL,
  `tenant_id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '0',
  `smtp_host` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `smtp_port` smallint UNSIGNED DEFAULT NULL,
  `smtp_encryption` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `smtp_username` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `smtp_password` text COLLATE utf8mb4_unicode_ci,
  `smtp_from_address` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `smtp_from_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint UNSIGNED NOT NULL,
  `tenant_id` bigint UNSIGNED DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `username` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `avatar` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` enum('Active','Inactive','Block') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Active',
  `last_login` timestamp NULL DEFAULT NULL,
  `session_token` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `legacy_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `legacy_id` int UNSIGNED DEFAULT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `tenant_id`, `name`, `username`, `email`, `phone`, `avatar`, `email_verified_at`, `password`, `status`, `last_login`, `session_token`, `legacy_type`, `legacy_id`, `remember_token`, `created_at`, `updated_at`) VALUES
(1, NULL, 'Super Admin', NULL, 'superadmin@crm.com', NULL, NULL, NULL, '$2y$10$RNLcSnYFkbqt3uzYMNo4O.c9FhJA81uxRm5M902hZ5daj1zBDgO5u', 'Active', '2026-09-06 17:15:13', 'dwr4SFvd41yv4KNIb9dh0AojWPqO1eoW5uwPJEWfUYSmd1VTXTpU6k4QPPUc', NULL, NULL, NULL, '2026-09-04 09:01:19', '2026-09-06 17:15:13'),
(5, 5, 'Vrishbhan Singh', NULL, 'vrishbhansingh321@gmail.com', '9560843891', NULL, NULL, '$2y$10$YOb1UzS3Fy7WYWkB5XuN3OwGomFELi7C2OV6gYSLDRkHN3Ir6lAWa', 'Active', '2026-09-14 14:10:45', 'dziB4q2H2cY3tl7Sbn6MCPlsenLe1XONydTDwwccNpgFAEmN7AT7nh8I8ddo', NULL, NULL, NULL, '2026-09-06 19:05:02', '2026-09-14 14:10:45');

-- --------------------------------------------------------

--
-- Table structure for table `user_attendance`
--

CREATE TABLE `user_attendance` (
  `id` int NOT NULL,
  `tenant_id` bigint UNSIGNED DEFAULT NULL,
  `user_id` bigint UNSIGNED DEFAULT NULL,
  `date` datetime DEFAULT NULL,
  `check_out` datetime DEFAULT NULL,
  `status` enum('Active','Inactive') NOT NULL DEFAULT 'Active',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user_list`
--

CREATE TABLE `user_list` (
  `id` int UNSIGNED NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `role` enum('agent') CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `backup` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `last_login` datetime DEFAULT NULL,
  `status` enum('Active','Inactive','Block') CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `created_at` timestamp NOT NULL,
  `updated_at` timestamp NOT NULL,
  `session_token` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `whatsapp_accounts`
--

CREATE TABLE `whatsapp_accounts` (
  `id` bigint UNSIGNED NOT NULL,
  `tenant_id` bigint UNSIGNED NOT NULL,
  `channel_type` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone_number` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `is_default` tinyint(1) NOT NULL DEFAULT '0',
  `webhook_token` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `verify_token` varchar(80) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `credentials` text COLLATE utf8mb4_unicode_ci,
  `messages_sent_count` int UNSIGNED NOT NULL DEFAULT '0',
  `messages_received_count` int UNSIGNED NOT NULL DEFAULT '0',
  `last_used_at` timestamp NULL DEFAULT NULL,
  `created_by` bigint UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `whatsapp_account_logs`
--

CREATE TABLE `whatsapp_account_logs` (
  `id` bigint UNSIGNED NOT NULL,
  `whatsapp_account_id` bigint UNSIGNED NOT NULL,
  `tenant_id` bigint UNSIGNED NOT NULL,
  `direction` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `external_ref` varchar(190) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `message` text COLLATE utf8mb4_unicode_ci,
  `payload` longtext COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin_details`
--
ALTER TABLE `admin_details`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `audit_logs_tenant_id_created_at_index` (`tenant_id`,`created_at`),
  ADD KEY `audit_logs_auditable_type_auditable_id_index` (`auditable_type`,`auditable_id`),
  ADD KEY `audit_logs_actor_id_created_at_index` (`actor_id`,`created_at`);

--
-- Indexes for table `companies`
--
ALTER TABLE `companies`
  ADD PRIMARY KEY (`id`),
  ADD KEY `companies_owner_id_foreign` (`owner_id`),
  ADD KEY `companies_tenant_id_name_index` (`tenant_id`,`name`),
  ADD KEY `companies_tenant_id_status_index` (`tenant_id`,`status`),
  ADD KEY `companies_tenant_id_owner_id_index` (`tenant_id`,`owner_id`);

--
-- Indexes for table `company_details`
--
ALTER TABLE `company_details`
  ADD PRIMARY KEY (`id`),
  ADD KEY `company_details_tenant_id_index` (`tenant_id`);

--
-- Indexes for table `contacts`
--
ALTER TABLE `contacts`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `contacts_legacy_customer_contact_id_unique` (`legacy_customer_contact_id`),
  ADD KEY `contacts_company_id_foreign` (`company_id`),
  ADD KEY `contacts_owner_id_foreign` (`owner_id`),
  ADD KEY `contacts_tenant_id_company_id_index` (`tenant_id`,`company_id`),
  ADD KEY `contacts_tenant_id_owner_id_index` (`tenant_id`,`owner_id`),
  ADD KEY `contacts_tenant_id_email_index` (`tenant_id`,`email`);

--
-- Indexes for table `customer_contact`
--
ALTER TABLE `customer_contact`
  ADD PRIMARY KEY (`id`),
  ADD KEY `lead_id` (`lead_id`),
  ADD KEY `customer_contact_tenant_id_foreign` (`tenant_id`);

--
-- Indexes for table `deals`
--
ALTER TABLE `deals`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `deals_lead_id_unique` (`lead_id`),
  ADD UNIQUE KEY `deals_order_id_unique` (`order_id`),
  ADD KEY `deals_tenant_id_foreign` (`tenant_id`),
  ADD KEY `deals_pipeline_id_foreign` (`pipeline_id`),
  ADD KEY `deals_stage_id_foreign` (`stage_id`),
  ADD KEY `deals_owner_id_foreign` (`owner_id`),
  ADD KEY `deals_lost_reason_id_foreign` (`lost_reason_id`),
  ADD KEY `deals_created_by_foreign` (`created_by`),
  ADD KEY `deals_company_id_foreign` (`company_id`),
  ADD KEY `deals_contact_id_foreign` (`contact_id`);

--
-- Indexes for table `deal_stage_history`
--
ALTER TABLE `deal_stage_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `deal_stage_history_deal_id_foreign` (`deal_id`),
  ADD KEY `deal_stage_history_from_stage_id_foreign` (`from_stage_id`),
  ADD KEY `deal_stage_history_to_stage_id_foreign` (`to_stage_id`),
  ADD KEY `deal_stage_history_changed_by_foreign` (`changed_by`);

--
-- Indexes for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Indexes for table `leads`
--
ALTER TABLE `leads`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `leads_tenant_number_unique` (`tenant_id`,`lead_number`),
  ADD KEY `idx_user_attendance_assigned_to` (`assigned_to`),
  ADD KEY `leads_company_id_foreign` (`company_id`),
  ADD KEY `leads_contact_id_foreign` (`contact_id`);

--
-- Indexes for table `lead_activities`
--
ALTER TABLE `lead_activities`
  ADD PRIMARY KEY (`id`),
  ADD KEY `lead_activities_tenant_id_foreign` (`tenant_id`),
  ADD KEY `lead_activities_user_id_foreign` (`user_id`),
  ADD KEY `lead_activities_lead_id_created_at_index` (`lead_id`,`created_at`);

--
-- Indexes for table `lead_attachments`
--
ALTER TABLE `lead_attachments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `lead_attachments_tenant_id_foreign` (`tenant_id`),
  ADD KEY `lead_attachments_lead_id_foreign` (`lead_id`),
  ADD KEY `lead_attachments_user_id_foreign` (`user_id`);

--
-- Indexes for table `lead_follow_up`
--
ALTER TABLE `lead_follow_up`
  ADD PRIMARY KEY (`id`),
  ADD KEY `lead_follow_up_tenant_id_foreign` (`tenant_id`);

--
-- Indexes for table `lead_integrations`
--
ALTER TABLE `lead_integrations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `lead_integrations_token_unique` (`token`),
  ADD KEY `lead_integrations_tenant_id_foreign` (`tenant_id`),
  ADD KEY `lead_integrations_created_by_foreign` (`created_by`),
  ADD KEY `lead_integrations_default_assigned_to_foreign` (`default_assigned_to`);

--
-- Indexes for table `lead_integration_logs`
--
ALTER TABLE `lead_integration_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `lead_integration_logs_lead_integration_id_external_ref_index` (`lead_integration_id`,`external_ref`),
  ADD KEY `lead_integration_logs_tenant_id_index` (`tenant_id`);

--
-- Indexes for table `lead_tag`
--
ALTER TABLE `lead_tag`
  ADD PRIMARY KEY (`lead_id`,`tag_id`),
  ADD KEY `lead_tag_tag_id_foreign` (`tag_id`);

--
-- Indexes for table `master_types`
--
ALTER TABLE `master_types`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `master_types_code_unique` (`code`);

--
-- Indexes for table `master_values`
--
ALTER TABLE `master_values`
  ADD PRIMARY KEY (`id`),
  ADD KEY `master_values_tenant_id_foreign` (`tenant_id`),
  ADD KEY `master_values_master_type_id_tenant_id_index` (`master_type_id`,`tenant_id`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `model_has_permissions`
--
ALTER TABLE `model_has_permissions`
  ADD PRIMARY KEY (`tenant_id`,`permission_id`,`model_id`,`model_type`),
  ADD KEY `model_has_permissions_model_id_model_type_index` (`model_id`,`model_type`),
  ADD KEY `model_has_permissions_team_foreign_key_index` (`tenant_id`),
  ADD KEY `model_has_permissions_permission_id_index` (`permission_id`),
  ADD KEY `model_has_permissions_tenant_id_index` (`tenant_id`);

--
-- Indexes for table `model_has_roles`
--
ALTER TABLE `model_has_roles`
  ADD PRIMARY KEY (`tenant_id`,`role_id`,`model_id`,`model_type`),
  ADD KEY `model_has_roles_model_id_model_type_index` (`model_id`,`model_type`),
  ADD KEY `model_has_roles_team_foreign_key_index` (`tenant_id`),
  ADD KEY `model_has_roles_role_id_index` (`role_id`),
  ADD KEY `model_has_roles_tenant_id_index` (`tenant_id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `notifications_notifiable_type_notifiable_id_index` (`notifiable_type`,`notifiable_id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `orders_order_number_unique` (`order_number`),
  ADD UNIQUE KEY `orders_invoice_id_unique` (`invoice_id`),
  ADD KEY `lead_id` (`lead_id`),
  ADD KEY `project_id` (`project_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `orders_tenant_id_foreign` (`tenant_id`);

--
-- Indexes for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Indexes for table `payment_details`
--
ALTER TABLE `payment_details`
  ADD PRIMARY KEY (`id`),
  ADD KEY `payment_details_tenant_id_index` (`tenant_id`),
  ADD KEY `payment_details_order_id_foreign` (`order_id`);

--
-- Indexes for table `permissions`
--
ALTER TABLE `permissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `permissions_name_guard_name_unique` (`name`,`guard_name`);

--
-- Indexes for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  ADD KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`);

--
-- Indexes for table `pipelines`
--
ALTER TABLE `pipelines`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pipelines_tenant_id_foreign` (`tenant_id`);

--
-- Indexes for table `pipeline_stages`
--
ALTER TABLE `pipeline_stages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pipeline_stages_tenant_id_foreign` (`tenant_id`),
  ADD KEY `pipeline_stages_pipeline_id_foreign` (`pipeline_id`);

--
-- Indexes for table `platform_audit_logs`
--
ALTER TABLE `platform_audit_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `platform_audit_logs_actor_id_foreign` (`actor_id`),
  ADD KEY `platform_audit_logs_tenant_id_foreign` (`tenant_id`),
  ADD KEY `platform_audit_logs_target_user_id_foreign` (`target_user_id`),
  ADD KEY `platform_audit_logs_event_index` (`event`);

--
-- Indexes for table `platform_mail_settings`
--
ALTER TABLE `platform_mail_settings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `project_info`
--
ALTER TABLE `project_info`
  ADD PRIMARY KEY (`id`),
  ADD KEY `project_info_tenant_id_foreign` (`tenant_id`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `roles_tenant_id_name_guard_name_unique` (`tenant_id`,`name`,`guard_name`),
  ADD KEY `roles_team_foreign_key_index` (`tenant_id`);

--
-- Indexes for table `role_has_permissions`
--
ALTER TABLE `role_has_permissions`
  ADD PRIMARY KEY (`permission_id`,`role_id`),
  ADD KEY `role_has_permissions_role_id_foreign` (`role_id`);

--
-- Indexes for table `tags`
--
ALTER TABLE `tags`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `tags_tenant_id_name_unique` (`tenant_id`,`name`);

--
-- Indexes for table `tasks`
--
ALTER TABLE `tasks`
  ADD PRIMARY KEY (`id`),
  ADD KEY `tasks_assigned_to_foreign` (`assigned_to`),
  ADD KEY `tasks_created_by_foreign` (`created_by`),
  ADD KEY `tasks_tenant_id_status_due_at_index` (`tenant_id`,`status`,`due_at`),
  ADD KEY `tasks_related_type_related_id_index` (`related_type`,`related_id`),
  ADD KEY `tasks_notification_sent_at_index` (`notification_sent_at`);

--
-- Indexes for table `tenants`
--
ALTER TABLE `tenants`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `tenants_slug_unique` (`slug`),
  ADD UNIQUE KEY `tenants_admin_user_id_unique` (`admin_user_id`),
  ADD UNIQUE KEY `tenants_database_name_unique` (`database_name`),
  ADD KEY `tenants_approved_by_foreign` (`approved_by`),
  ADD KEY `tenants_approval_status_index` (`approval_status`),
  ADD KEY `tenants_provision_status_index` (`provision_status`);

--
-- Indexes for table `tenant_mail_settings`
--
ALTER TABLE `tenant_mail_settings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `tenant_mail_settings_tenant_id_is_active_index` (`tenant_id`,`is_active`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`),
  ADD KEY `users_tenant_id_foreign` (`tenant_id`),
  ADD KEY `users_legacy_type_legacy_id_index` (`legacy_type`,`legacy_id`);

--
-- Indexes for table `user_attendance`
--
ALTER TABLE `user_attendance`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_attendance_user_id` (`user_id`),
  ADD KEY `user_attendance_tenant_id_foreign` (`tenant_id`);

--
-- Indexes for table `user_list`
--
ALTER TABLE `user_list`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `whatsapp_accounts`
--
ALTER TABLE `whatsapp_accounts`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `whatsapp_accounts_webhook_token_unique` (`webhook_token`),
  ADD KEY `whatsapp_accounts_tenant_id_foreign` (`tenant_id`);

--
-- Indexes for table `whatsapp_account_logs`
--
ALTER TABLE `whatsapp_account_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `whatsapp_account_logs_whatsapp_account_id_external_ref_index` (`whatsapp_account_id`,`external_ref`),
  ADD KEY `whatsapp_account_logs_tenant_id_index` (`tenant_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin_details`
--
ALTER TABLE `admin_details`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `audit_logs`
--
ALTER TABLE `audit_logs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `companies`
--
ALTER TABLE `companies`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `company_details`
--
ALTER TABLE `company_details`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `contacts`
--
ALTER TABLE `contacts`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `customer_contact`
--
ALTER TABLE `customer_contact`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `deals`
--
ALTER TABLE `deals`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `deal_stage_history`
--
ALTER TABLE `deal_stage_history`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `leads`
--
ALTER TABLE `leads`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `lead_activities`
--
ALTER TABLE `lead_activities`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `lead_attachments`
--
ALTER TABLE `lead_attachments`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `lead_follow_up`
--
ALTER TABLE `lead_follow_up`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `lead_integrations`
--
ALTER TABLE `lead_integrations`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `lead_integration_logs`
--
ALTER TABLE `lead_integration_logs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `master_types`
--
ALTER TABLE `master_types`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `master_values`
--
ALTER TABLE `master_values`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=62;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=56;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `payment_details`
--
ALTER TABLE `payment_details`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `permissions`
--
ALTER TABLE `permissions`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=219;

--
-- AUTO_INCREMENT for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pipelines`
--
ALTER TABLE `pipelines`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pipeline_stages`
--
ALTER TABLE `pipeline_stages`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `platform_audit_logs`
--
ALTER TABLE `platform_audit_logs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `platform_mail_settings`
--
ALTER TABLE `platform_mail_settings`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `project_info`
--
ALTER TABLE `project_info`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `tags`
--
ALTER TABLE `tags`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tasks`
--
ALTER TABLE `tasks`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tenants`
--
ALTER TABLE `tenants`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `tenant_mail_settings`
--
ALTER TABLE `tenant_mail_settings`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `user_attendance`
--
ALTER TABLE `user_attendance`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `user_list`
--
ALTER TABLE `user_list`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `whatsapp_accounts`
--
ALTER TABLE `whatsapp_accounts`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `whatsapp_account_logs`
--
ALTER TABLE `whatsapp_account_logs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD CONSTRAINT `audit_logs_actor_id_foreign` FOREIGN KEY (`actor_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `audit_logs_tenant_id_foreign` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `companies`
--
ALTER TABLE `companies`
  ADD CONSTRAINT `companies_owner_id_foreign` FOREIGN KEY (`owner_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `companies_tenant_id_foreign` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `company_details`
--
ALTER TABLE `company_details`
  ADD CONSTRAINT `company_details_tenant_id_foreign` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `contacts`
--
ALTER TABLE `contacts`
  ADD CONSTRAINT `contacts_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `contacts_owner_id_foreign` FOREIGN KEY (`owner_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `contacts_tenant_id_foreign` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `customer_contact`
--
ALTER TABLE `customer_contact`
  ADD CONSTRAINT `customer_contact_tenant_id_foreign` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `deals`
--
ALTER TABLE `deals`
  ADD CONSTRAINT `deals_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `deals_contact_id_foreign` FOREIGN KEY (`contact_id`) REFERENCES `contacts` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `deals_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `deals_lead_id_foreign` FOREIGN KEY (`lead_id`) REFERENCES `leads` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `deals_lost_reason_id_foreign` FOREIGN KEY (`lost_reason_id`) REFERENCES `master_values` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `deals_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `deals_owner_id_foreign` FOREIGN KEY (`owner_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `deals_pipeline_id_foreign` FOREIGN KEY (`pipeline_id`) REFERENCES `pipelines` (`id`),
  ADD CONSTRAINT `deals_stage_id_foreign` FOREIGN KEY (`stage_id`) REFERENCES `pipeline_stages` (`id`),
  ADD CONSTRAINT `deals_tenant_id_foreign` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `deal_stage_history`
--
ALTER TABLE `deal_stage_history`
  ADD CONSTRAINT `deal_stage_history_changed_by_foreign` FOREIGN KEY (`changed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `deal_stage_history_deal_id_foreign` FOREIGN KEY (`deal_id`) REFERENCES `deals` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `deal_stage_history_from_stage_id_foreign` FOREIGN KEY (`from_stage_id`) REFERENCES `pipeline_stages` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `deal_stage_history_to_stage_id_foreign` FOREIGN KEY (`to_stage_id`) REFERENCES `pipeline_stages` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `leads`
--
ALTER TABLE `leads`
  ADD CONSTRAINT `leads_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `leads_contact_id_foreign` FOREIGN KEY (`contact_id`) REFERENCES `contacts` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `leads_tenant_id_foreign` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `lead_activities`
--
ALTER TABLE `lead_activities`
  ADD CONSTRAINT `lead_activities_lead_id_foreign` FOREIGN KEY (`lead_id`) REFERENCES `leads` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `lead_activities_tenant_id_foreign` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `lead_activities_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `lead_attachments`
--
ALTER TABLE `lead_attachments`
  ADD CONSTRAINT `lead_attachments_lead_id_foreign` FOREIGN KEY (`lead_id`) REFERENCES `leads` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `lead_attachments_tenant_id_foreign` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `lead_attachments_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `lead_follow_up`
--
ALTER TABLE `lead_follow_up`
  ADD CONSTRAINT `lead_follow_up_tenant_id_foreign` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `lead_integrations`
--
ALTER TABLE `lead_integrations`
  ADD CONSTRAINT `lead_integrations_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `lead_integrations_default_assigned_to_foreign` FOREIGN KEY (`default_assigned_to`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `lead_integrations_tenant_id_foreign` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `lead_integration_logs`
--
ALTER TABLE `lead_integration_logs`
  ADD CONSTRAINT `lead_integration_logs_lead_integration_id_foreign` FOREIGN KEY (`lead_integration_id`) REFERENCES `lead_integrations` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `lead_tag`
--
ALTER TABLE `lead_tag`
  ADD CONSTRAINT `lead_tag_lead_id_foreign` FOREIGN KEY (`lead_id`) REFERENCES `leads` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `lead_tag_tag_id_foreign` FOREIGN KEY (`tag_id`) REFERENCES `tags` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `master_values`
--
ALTER TABLE `master_values`
  ADD CONSTRAINT `master_values_master_type_id_foreign` FOREIGN KEY (`master_type_id`) REFERENCES `master_types` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `master_values_tenant_id_foreign` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `model_has_permissions`
--
ALTER TABLE `model_has_permissions`
  ADD CONSTRAINT `model_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `model_has_roles`
--
ALTER TABLE `model_has_roles`
  ADD CONSTRAINT `model_has_roles_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`lead_id`) REFERENCES `leads` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `orders_ibfk_2` FOREIGN KEY (`project_id`) REFERENCES `project_info` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `orders_tenant_id_foreign` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `orders_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `payment_details`
--
ALTER TABLE `payment_details`
  ADD CONSTRAINT `payment_details_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `payment_details_tenant_id_foreign` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `pipelines`
--
ALTER TABLE `pipelines`
  ADD CONSTRAINT `pipelines_tenant_id_foreign` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `pipeline_stages`
--
ALTER TABLE `pipeline_stages`
  ADD CONSTRAINT `pipeline_stages_pipeline_id_foreign` FOREIGN KEY (`pipeline_id`) REFERENCES `pipelines` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `pipeline_stages_tenant_id_foreign` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `platform_audit_logs`
--
ALTER TABLE `platform_audit_logs`
  ADD CONSTRAINT `platform_audit_logs_actor_id_foreign` FOREIGN KEY (`actor_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `platform_audit_logs_target_user_id_foreign` FOREIGN KEY (`target_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `platform_audit_logs_tenant_id_foreign` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `project_info`
--
ALTER TABLE `project_info`
  ADD CONSTRAINT `project_info_tenant_id_foreign` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `role_has_permissions`
--
ALTER TABLE `role_has_permissions`
  ADD CONSTRAINT `role_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `role_has_permissions_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `tags`
--
ALTER TABLE `tags`
  ADD CONSTRAINT `tags_tenant_id_foreign` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `tasks`
--
ALTER TABLE `tasks`
  ADD CONSTRAINT `tasks_assigned_to_foreign` FOREIGN KEY (`assigned_to`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `tasks_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `tasks_tenant_id_foreign` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `tenants`
--
ALTER TABLE `tenants`
  ADD CONSTRAINT `tenants_admin_user_id_foreign` FOREIGN KEY (`admin_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `tenants_approved_by_foreign` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `tenant_mail_settings`
--
ALTER TABLE `tenant_mail_settings`
  ADD CONSTRAINT `tenant_mail_settings_tenant_id_foreign` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_tenant_id_foreign` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `user_attendance`
--
ALTER TABLE `user_attendance`
  ADD CONSTRAINT `user_attendance_tenant_id_foreign` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `user_attendance_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `whatsapp_accounts`
--
ALTER TABLE `whatsapp_accounts`
  ADD CONSTRAINT `whatsapp_accounts_tenant_id_foreign` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `whatsapp_account_logs`
--
ALTER TABLE `whatsapp_account_logs`
  ADD CONSTRAINT `whatsapp_account_logs_whatsapp_account_id_foreign` FOREIGN KEY (`whatsapp_account_id`) REFERENCES `whatsapp_accounts` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
