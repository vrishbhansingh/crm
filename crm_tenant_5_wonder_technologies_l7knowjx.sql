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
-- Database: `crm_tenant_5_wonder_technologies_l7knowjx`
--

-- --------------------------------------------------------

--
-- Table structure for table `audit_logs`
--

CREATE TABLE `audit_logs` (
  `id` bigint UNSIGNED NOT NULL,
  `tenant_id` bigint UNSIGNED DEFAULT NULL,
  `actor_id` bigint UNSIGNED DEFAULT NULL,
  `event` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `auditable_type` varchar(120) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `auditable_id` bigint UNSIGNED NOT NULL,
  `old_values` json DEFAULT NULL,
  `new_values` json DEFAULT NULL,
  `ip_address` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `audit_logs`
--

INSERT INTO `audit_logs` (`id`, `tenant_id`, `actor_id`, `event`, `auditable_type`, `auditable_id`, `old_values`, `new_values`, `ip_address`, `user_agent`, `created_at`, `updated_at`) VALUES
(1, 5, 2, 'created', 'Pipeline', 1, NULL, '{\"id\": 1, \"name\": \"Sales Pipeline\", \"is_active\": true, \"tenant_id\": 5, \"created_at\": \"2026-09-06 19:05:04\", \"is_default\": true, \"sort_order\": 0, \"updated_at\": \"2026-09-06 19:05:04\"}', '157.49.17.231', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-06 19:05:04', '2026-09-06 19:05:04'),
(2, 5, 5, 'created', 'Lead', 1, NULL, '{\"id\": 1, \"city\": \"Mundka\", \"score\": 60, \"state\": \"Delhi\", \"budget\": \"500000\", \"country\": \"India\", \"product\": \"Crm\", \"remarks\": null, \"service\": null, \"priority\": \"high\", \"lead_type\": \"cold\", \"tenant_id\": 5, \"created_at\": \"2026-09-07 04:43:51\", \"updated_at\": \"2026-09-07 04:43:51\", \"assigned_at\": \"2026-09-07T04:43:51.929299Z\", \"assigned_by\": 5, \"assigned_to\": \"5\", \"lead_number\": 1, \"lead_source\": \"website\", \"lead_status\": \"new\", \"requirement\": null, \"converted_at\": null, \"is_converted\": \"No\", \"internal_note\": null, \"status_reason\": null, \"follow_up_date\": null, \"follow_up_note\": null, \"follow_up_time\": null, \"conversion_value\": null}', '157.49.158.87', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '2026-09-07 04:43:51', '2026-09-07 04:43:51'),
(3, 5, 5, 'created', 'Company', 1, NULL, '{\"id\": 1, \"city\": \"Mundka\", \"name\": \"Bajaj finance\", \"state\": \"Delhi\", \"status\": \"prospect\", \"country\": \"India\", \"owner_id\": \"5\", \"tenant_id\": 5, \"created_at\": \"2026-09-07 04:43:51\", \"gst_number\": null, \"updated_at\": \"2026-09-07 04:43:51\"}', '157.49.158.87', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '2026-09-07 04:43:51', '2026-09-07 04:43:51'),
(4, 5, 5, 'created', 'Contact', 1, NULL, '{\"id\": 1, \"city\": \"Mundka\", \"name\": \"Jai yadav\", \"email\": \"singhvrishbhan2001@gmail.com\", \"phone\": \"8700530833\", \"source\": \"lead\", \"status\": \"active\", \"owner_id\": \"5\", \"tenant_id\": 5, \"company_id\": 1, \"created_at\": \"2026-09-07 04:43:51\", \"is_primary\": true, \"updated_at\": \"2026-09-07 04:43:51\", \"designation\": \"Manager\", \"alternate_phone\": null}', '157.49.158.87', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '2026-09-07 04:43:51', '2026-09-07 04:43:51'),
(5, 5, 5, 'updated', 'Lead', 1, '{\"name\": null, \"email\": null, \"phone\": null, \"gst_no\": null, \"company_id\": null, \"contact_id\": null, \"company_name\": null, \"alternate_phone\": null}', '{\"name\": \"Jai yadav\", \"email\": \"singhvrishbhan2001@gmail.com\", \"phone\": \"8700530833\", \"gst_no\": null, \"company_id\": 1, \"contact_id\": 1, \"company_name\": \"Bajaj finance\", \"alternate_phone\": null}', '157.49.158.87', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '2026-09-07 04:43:52', '2026-09-07 04:43:52');

-- --------------------------------------------------------

--
-- Table structure for table `companies`
--

CREATE TABLE `companies` (
  `id` bigint UNSIGNED NOT NULL,
  `tenant_id` bigint UNSIGNED DEFAULT NULL,
  `owner_id` bigint UNSIGNED DEFAULT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `legal_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `website` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `industry` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `company_size` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `gst_number` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `pan_number` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `city` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `state` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `country` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `pincode` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'prospect',
  `notes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `companies`
--

INSERT INTO `companies` (`id`, `tenant_id`, `owner_id`, `name`, `legal_name`, `website`, `industry`, `company_size`, `email`, `phone`, `gst_number`, `pan_number`, `address`, `city`, `state`, `country`, `pincode`, `status`, `notes`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 5, 5, 'Bajaj finance', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Mundka', 'Delhi', 'India', NULL, 'prospect', NULL, '2026-09-07 04:43:51', '2026-09-07 04:43:51', NULL);

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

--
-- Dumping data for table `company_details`
--

INSERT INTO `company_details` (`id`, `tenant_id`, `company_name`, `company_logo`, `email`, `phone`, `address`, `city`, `state`, `country`, `pincode`, `gst_number`, `pan_number`, `bank_name`, `account_name`, `account_number`, `ifsc_code`, `status`, `created_at`, `updated_at`) VALUES
(1, 5, 'Wonder technologies', NULL, 'vrishbhansingh321@gmail.com', NULL, NULL, NULL, NULL, 'India', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Active', '2026-09-06 19:05:04', '2026-09-06 19:05:04');

-- --------------------------------------------------------

--
-- Table structure for table `contacts`
--

CREATE TABLE `contacts` (
  `id` bigint UNSIGNED NOT NULL,
  `tenant_id` bigint UNSIGNED DEFAULT NULL,
  `company_id` bigint UNSIGNED DEFAULT NULL,
  `owner_id` bigint UNSIGNED DEFAULT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `alternate_phone` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `designation` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `department` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `city` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `state` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `country` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_primary` tinyint(1) NOT NULL DEFAULT '0',
  `source` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `notes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `legacy_customer_contact_id` int UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `contacts`
--

INSERT INTO `contacts` (`id`, `tenant_id`, `company_id`, `owner_id`, `name`, `email`, `phone`, `alternate_phone`, `designation`, `department`, `city`, `state`, `country`, `is_primary`, `source`, `status`, `notes`, `legacy_customer_contact_id`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 5, 1, 5, 'Jai yadav', 'singhvrishbhan2001@gmail.com', '8700530833', NULL, 'Manager', NULL, 'Mundka', NULL, NULL, 1, 'lead', 'active', NULL, NULL, '2026-09-07 04:43:51', '2026-09-07 04:43:51', NULL);

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
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `amount` double(10,2) NOT NULL DEFAULT '0.00',
  `currency` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `expected_close_date` date DEFAULT NULL,
  `closed_at` timestamp NULL DEFAULT NULL,
  `status` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'open',
  `notes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
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
-- Table structure for table `email_campaigns`
--

CREATE TABLE `email_campaigns` (
  `id` bigint UNSIGNED NOT NULL,
  `tenant_id` bigint UNSIGNED DEFAULT NULL,
  `created_by` bigint UNSIGNED DEFAULT NULL,
  `email_template_id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `subject` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `audience_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `audience_filters` json DEFAULT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draft',
  `scheduled_at` timestamp NULL DEFAULT NULL,
  `sent_at` timestamp NULL DEFAULT NULL,
  `total_recipients` int UNSIGNED NOT NULL DEFAULT '0',
  `sent_count` int UNSIGNED NOT NULL DEFAULT '0',
  `failed_count` int UNSIGNED NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `email_campaign_recipients`
--

CREATE TABLE `email_campaign_recipients` (
  `id` bigint UNSIGNED NOT NULL,
  `tenant_id` bigint UNSIGNED DEFAULT NULL,
  `email_campaign_id` bigint UNSIGNED NOT NULL,
  `recipient_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `recipient_id` bigint UNSIGNED NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `error` text COLLATE utf8mb4_unicode_ci,
  `sent_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `email_templates`
--

CREATE TABLE `email_templates` (
  `id` bigint UNSIGNED NOT NULL,
  `tenant_id` bigint UNSIGNED DEFAULT NULL,
  `created_by` bigint UNSIGNED DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `subject` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `body` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
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

--
-- Dumping data for table `leads`
--

INSERT INTO `leads` (`id`, `tenant_id`, `company_id`, `contact_id`, `lead_number`, `lead_type`, `lead_source`, `name`, `company_name`, `phone`, `alternate_phone`, `email`, `gst_no`, `city`, `state`, `country`, `product`, `service`, `budget`, `requirement`, `lead_status`, `final_status`, `priority`, `follow_up_date`, `follow_up_time`, `follow_up_note`, `assigned_to`, `assigned_by`, `assigned_at`, `remarks`, `internal_note`, `is_converted`, `converted_at`, `conversion_value`, `score`, `status_reason`, `last_contacted_at`, `last_contacted_by`, `status`, `created_at`, `updated_at`) VALUES
(1, 5, 1, 1, 1, 'cold', 'website', 'Jai yadav', 'Bajaj finance', '8700530833', NULL, 'singhvrishbhan2001@gmail.com', NULL, 'Mundka', 'Delhi', 'India', 'Crm', NULL, 500000.00, NULL, 'new', NULL, 'high', NULL, NULL, NULL, 5, 5, '2026-09-07 04:43:51', NULL, NULL, 'No', NULL, NULL, 60, NULL, NULL, NULL, 'Active', '2026-09-07 04:43:51', '2026-09-07 04:43:52');

-- --------------------------------------------------------

--
-- Table structure for table `lead_activities`
--

CREATE TABLE `lead_activities` (
  `id` bigint UNSIGNED NOT NULL,
  `tenant_id` bigint UNSIGNED DEFAULT NULL,
  `lead_id` int UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED DEFAULT NULL,
  `type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `lead_activities`
--

INSERT INTO `lead_activities` (`id`, `tenant_id`, `lead_id`, `user_id`, `type`, `description`, `created_at`) VALUES
(1, 5, 1, 5, 'created', 'Lead created', '2026-09-07 04:43:51'),
(2, 5, 1, 5, 'assigned', 'Assigned to Vrishbhan Singh', '2026-09-07 04:43:51');

-- --------------------------------------------------------

--
-- Table structure for table `lead_attachments`
--

CREATE TABLE `lead_attachments` (
  `id` bigint UNSIGNED NOT NULL,
  `tenant_id` bigint UNSIGNED DEFAULT NULL,
  `lead_id` int UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED DEFAULT NULL,
  `original_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `stored_path` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `size` bigint UNSIGNED NOT NULL,
  `mime_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
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
  `code` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
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
  `code` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `label` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `color` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
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
(57, 11, NULL, 'other', 'Other', NULL, 5, 1, '2026-09-04 09:01:19', '2026-09-04 09:01:19');

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
(1, '2026_08_15_100001_create_tenant_tier0_tables', 1),
(2, '2026_08_15_100002_create_tenant_tier1_tables', 1),
(3, '2026_08_15_100003_create_tenant_tier2_leads_table', 1),
(4, '2026_08_15_100004_create_tenant_tier3_tables', 1),
(5, '2026_08_15_100005_create_tenant_tier4_tables', 1),
(6, '2026_08_15_100006_create_tenant_tier5_deal_stage_history_table', 1),
(7, '2026_09_04_100001_create_email_templates_table', 1),
(8, '2026_09_04_100002_create_email_campaigns_table', 1),
(9, '2026_09_04_100003_create_email_campaign_recipients_table', 1);

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
-- Table structure for table `pipelines`
--

CREATE TABLE `pipelines` (
  `id` bigint UNSIGNED NOT NULL,
  `tenant_id` bigint UNSIGNED DEFAULT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_default` tinyint(1) NOT NULL DEFAULT '0',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `sort_order` int UNSIGNED NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `pipelines`
--

INSERT INTO `pipelines` (`id`, `tenant_id`, `name`, `is_default`, `is_active`, `sort_order`, `created_at`, `updated_at`) VALUES
(1, 5, 'Sales Pipeline', 1, 1, 0, '2026-09-06 19:05:04', '2026-09-06 19:05:04');

-- --------------------------------------------------------

--
-- Table structure for table `pipeline_stages`
--

CREATE TABLE `pipeline_stages` (
  `id` bigint UNSIGNED NOT NULL,
  `tenant_id` bigint UNSIGNED DEFAULT NULL,
  `pipeline_id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `sort_order` int UNSIGNED NOT NULL DEFAULT '0',
  `is_won` tinyint(1) NOT NULL DEFAULT '0',
  `is_lost` tinyint(1) NOT NULL DEFAULT '0',
  `probability` tinyint UNSIGNED DEFAULT NULL,
  `color` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `pipeline_stages`
--

INSERT INTO `pipeline_stages` (`id`, `tenant_id`, `pipeline_id`, `name`, `sort_order`, `is_won`, `is_lost`, `probability`, `color`, `created_at`, `updated_at`) VALUES
(1, 5, 1, 'Prospecting', 0, 0, 0, NULL, NULL, '2026-09-06 19:05:04', '2026-09-06 19:05:04'),
(2, 5, 1, 'Proposal', 1, 0, 0, NULL, NULL, '2026-09-06 19:05:04', '2026-09-06 19:05:04'),
(3, 5, 1, 'Negotiation', 2, 0, 0, NULL, NULL, '2026-09-06 19:05:04', '2026-09-06 19:05:04'),
(4, 5, 1, 'Won', 3, 1, 0, NULL, NULL, '2026-09-06 19:05:04', '2026-09-06 19:05:04'),
(5, 5, 1, 'Lost', 4, 0, 1, NULL, NULL, '2026-09-06 19:05:04', '2026-09-06 19:05:04');

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
-- Table structure for table `tags`
--

CREATE TABLE `tags` (
  `id` bigint UNSIGNED NOT NULL,
  `tenant_id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `color` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
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
  `related_type` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `related_id` bigint UNSIGNED DEFAULT NULL,
  `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `priority` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'medium',
  `status` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'todo',
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
-- Table structure for table `tenant_schema_versions`
--

CREATE TABLE `tenant_schema_versions` (
  `version` int UNSIGNED NOT NULL,
  `applied_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `tenant_schema_versions`
--

INSERT INTO `tenant_schema_versions` (`version`, `applied_at`) VALUES
(1, '2026-09-06 19:05:29');

-- --------------------------------------------------------

--
-- Table structure for table `tenant_sequences`
--

CREATE TABLE `tenant_sequences` (
  `name` varchar(80) COLLATE utf8mb4_unicode_ci NOT NULL,
  `current_value` bigint UNSIGNED NOT NULL DEFAULT '0',
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `tenant_sequences`
--

INSERT INTO `tenant_sequences` (`name`, `current_value`, `updated_at`) VALUES
('lead_number', 1, '2026-09-07 04:43:51');

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

--
-- Indexes for dumped tables
--

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
-- Indexes for table `email_campaigns`
--
ALTER TABLE `email_campaigns`
  ADD PRIMARY KEY (`id`),
  ADD KEY `email_campaigns_email_template_id_foreign` (`email_template_id`),
  ADD KEY `email_campaigns_tenant_id_index` (`tenant_id`);

--
-- Indexes for table `email_campaign_recipients`
--
ALTER TABLE `email_campaign_recipients`
  ADD PRIMARY KEY (`id`),
  ADD KEY `email_campaign_recipients_email_campaign_id_foreign` (`email_campaign_id`),
  ADD KEY `email_campaign_recipients_recipient_type_recipient_id_index` (`recipient_type`,`recipient_id`),
  ADD KEY `email_campaign_recipients_tenant_id_index` (`tenant_id`);

--
-- Indexes for table `email_templates`
--
ALTER TABLE `email_templates`
  ADD PRIMARY KEY (`id`),
  ADD KEY `email_templates_tenant_id_index` (`tenant_id`);

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
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `orders_order_number_unique` (`order_number`),
  ADD UNIQUE KEY `orders_invoice_id_unique` (`invoice_id`),
  ADD KEY `lead_id` (`lead_id`),
  ADD KEY `project_id` (`project_id`),
  ADD KEY `orders_tenant_id_foreign` (`tenant_id`),
  ADD KEY `orders_users_id_foreign` (`user_id`);

--
-- Indexes for table `payment_details`
--
ALTER TABLE `payment_details`
  ADD PRIMARY KEY (`id`),
  ADD KEY `payment_details_tenant_id_index` (`tenant_id`),
  ADD KEY `payment_details_order_id_foreign` (`order_id`);

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
-- Indexes for table `project_info`
--
ALTER TABLE `project_info`
  ADD PRIMARY KEY (`id`),
  ADD KEY `project_info_tenant_id_foreign` (`tenant_id`);

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
-- Indexes for table `tenant_schema_versions`
--
ALTER TABLE `tenant_schema_versions`
  ADD PRIMARY KEY (`version`);

--
-- Indexes for table `tenant_sequences`
--
ALTER TABLE `tenant_sequences`
  ADD PRIMARY KEY (`name`);

--
-- Indexes for table `user_attendance`
--
ALTER TABLE `user_attendance`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_attendance_user_id` (`user_id`),
  ADD KEY `user_attendance_tenant_id_foreign` (`tenant_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `audit_logs`
--
ALTER TABLE `audit_logs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `companies`
--
ALTER TABLE `companies`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `company_details`
--
ALTER TABLE `company_details`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `contacts`
--
ALTER TABLE `contacts`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

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
-- AUTO_INCREMENT for table `email_campaigns`
--
ALTER TABLE `email_campaigns`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `email_campaign_recipients`
--
ALTER TABLE `email_campaign_recipients`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `email_templates`
--
ALTER TABLE `email_templates`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `leads`
--
ALTER TABLE `leads`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `lead_activities`
--
ALTER TABLE `lead_activities`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

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
-- AUTO_INCREMENT for table `master_types`
--
ALTER TABLE `master_types`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `master_values`
--
ALTER TABLE `master_values`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=58;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

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
-- AUTO_INCREMENT for table `pipelines`
--
ALTER TABLE `pipelines`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `pipeline_stages`
--
ALTER TABLE `pipeline_stages`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `project_info`
--
ALTER TABLE `project_info`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

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
-- AUTO_INCREMENT for table `user_attendance`
--
ALTER TABLE `user_attendance`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `contacts`
--
ALTER TABLE `contacts`
  ADD CONSTRAINT `contacts_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `deals`
--
ALTER TABLE `deals`
  ADD CONSTRAINT `deals_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `deals_contact_id_foreign` FOREIGN KEY (`contact_id`) REFERENCES `contacts` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `deals_lead_id_foreign` FOREIGN KEY (`lead_id`) REFERENCES `leads` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `deals_lost_reason_id_foreign` FOREIGN KEY (`lost_reason_id`) REFERENCES `master_values` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `deals_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `deals_pipeline_id_foreign` FOREIGN KEY (`pipeline_id`) REFERENCES `pipelines` (`id`),
  ADD CONSTRAINT `deals_stage_id_foreign` FOREIGN KEY (`stage_id`) REFERENCES `pipeline_stages` (`id`);

--
-- Constraints for table `deal_stage_history`
--
ALTER TABLE `deal_stage_history`
  ADD CONSTRAINT `deal_stage_history_deal_id_foreign` FOREIGN KEY (`deal_id`) REFERENCES `deals` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `deal_stage_history_from_stage_id_foreign` FOREIGN KEY (`from_stage_id`) REFERENCES `pipeline_stages` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `deal_stage_history_to_stage_id_foreign` FOREIGN KEY (`to_stage_id`) REFERENCES `pipeline_stages` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `email_campaigns`
--
ALTER TABLE `email_campaigns`
  ADD CONSTRAINT `email_campaigns_email_template_id_foreign` FOREIGN KEY (`email_template_id`) REFERENCES `email_templates` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `email_campaign_recipients`
--
ALTER TABLE `email_campaign_recipients`
  ADD CONSTRAINT `email_campaign_recipients_email_campaign_id_foreign` FOREIGN KEY (`email_campaign_id`) REFERENCES `email_campaigns` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `leads`
--
ALTER TABLE `leads`
  ADD CONSTRAINT `leads_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `leads_contact_id_foreign` FOREIGN KEY (`contact_id`) REFERENCES `contacts` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `lead_activities`
--
ALTER TABLE `lead_activities`
  ADD CONSTRAINT `lead_activities_lead_id_foreign` FOREIGN KEY (`lead_id`) REFERENCES `leads` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `lead_attachments`
--
ALTER TABLE `lead_attachments`
  ADD CONSTRAINT `lead_attachments_lead_id_foreign` FOREIGN KEY (`lead_id`) REFERENCES `leads` (`id`) ON DELETE CASCADE;

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
  ADD CONSTRAINT `master_values_master_type_id_foreign` FOREIGN KEY (`master_type_id`) REFERENCES `master_types` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`lead_id`) REFERENCES `leads` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `orders_ibfk_2` FOREIGN KEY (`project_id`) REFERENCES `project_info` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `payment_details`
--
ALTER TABLE `payment_details`
  ADD CONSTRAINT `payment_details_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `pipeline_stages`
--
ALTER TABLE `pipeline_stages`
  ADD CONSTRAINT `pipeline_stages_pipeline_id_foreign` FOREIGN KEY (`pipeline_id`) REFERENCES `pipelines` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
