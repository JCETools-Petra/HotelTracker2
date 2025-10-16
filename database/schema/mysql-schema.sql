/*M!999999\- enable the sandbox mode */ 
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
DROP TABLE IF EXISTS `activity_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `activity_logs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `property_id` bigint(20) unsigned DEFAULT NULL,
  `action` varchar(255) DEFAULT NULL,
  `description` varchar(255) NOT NULL,
  `changes` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`changes`)),
  `ip_address` varchar(255) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `loggable_type` varchar(255) DEFAULT NULL,
  `loggable_id` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `activity_logs_user_id_foreign` (`user_id`),
  KEY `activity_logs_loggable_type_loggable_id_index` (`loggable_type`,`loggable_id`),
  KEY `activity_logs_property_id_foreign` (`property_id`),
  CONSTRAINT `activity_logs_property_id_foreign` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `amenities`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `amenities` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `bookings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `bookings` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `booking_number` varchar(255) NOT NULL,
  `booking_date` date NOT NULL,
  `client_name` varchar(255) NOT NULL,
  `event_type` varchar(255) NOT NULL,
  `event_date` date NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `participants` int(11) NOT NULL,
  `property_id` bigint(20) unsigned NOT NULL,
  `person_in_charge` varchar(255) NOT NULL,
  `status` enum('Booking Sementara','Booking Pasti','Cancel') NOT NULL DEFAULT 'Booking Sementara',
  `payment_status` varchar(255) NOT NULL DEFAULT 'Unpaid',
  `total_price` decimal(15,2) NOT NULL DEFAULT 0.00,
  `down_payment` decimal(15,2) NOT NULL DEFAULT 0.00,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `mice_category_id` bigint(20) unsigned DEFAULT NULL,
  `room_id` bigint(20) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `bookings_booking_number_unique` (`booking_number`),
  KEY `bookings_property_id_foreign` (`property_id`),
  KEY `bookings_mice_category_id_foreign` (`mice_category_id`),
  KEY `bookings_room_id_foreign` (`room_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `cache`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `cache_locks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int(11) NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `categories` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `category_code` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `property_id` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `categories_category_code_unique` (`category_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `daily_incomes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `daily_incomes` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `property_id` bigint(20) unsigned NOT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `date` date NOT NULL,
  `offline_rooms` int(11) NOT NULL DEFAULT 0,
  `breakfast_income` decimal(15,2) NOT NULL DEFAULT 0.00,
  `lunch_income` decimal(15,2) NOT NULL DEFAULT 0.00,
  `dinner_income` decimal(15,2) NOT NULL DEFAULT 0.00,
  `offline_room_income` decimal(15,2) NOT NULL DEFAULT 0.00,
  `online_rooms` int(11) NOT NULL DEFAULT 0,
  `online_room_income` decimal(15,2) NOT NULL DEFAULT 0.00,
  `others_income` decimal(15,2) NOT NULL DEFAULT 0.00,
  `total_rooms_sold` int(11) NOT NULL DEFAULT 0,
  `total_rooms_revenue` decimal(15,2) NOT NULL DEFAULT 0.00,
  `total_fb_revenue` decimal(15,2) NOT NULL DEFAULT 0.00,
  `total_revenue` decimal(15,2) NOT NULL DEFAULT 0.00,
  `arr` decimal(15,2) NOT NULL DEFAULT 0.00,
  `occupancy` decimal(8,2) NOT NULL DEFAULT 0.00,
  `ta_rooms` int(11) NOT NULL DEFAULT 0,
  `ta_income` decimal(15,2) NOT NULL DEFAULT 0.00,
  `gov_rooms` int(11) NOT NULL DEFAULT 0,
  `gov_income` decimal(15,2) NOT NULL DEFAULT 0.00,
  `corp_rooms` int(11) NOT NULL DEFAULT 0,
  `corp_income` decimal(15,2) NOT NULL DEFAULT 0.00,
  `compliment_rooms` int(11) NOT NULL DEFAULT 0,
  `compliment_income` decimal(15,2) NOT NULL DEFAULT 0.00,
  `house_use_rooms` int(11) NOT NULL DEFAULT 0,
  `house_use_income` decimal(15,2) NOT NULL DEFAULT 0.00,
  `afiliasi_rooms` int(11) NOT NULL DEFAULT 0,
  `afiliasi_room_income` decimal(15,2) NOT NULL DEFAULT 0.00,
  `mice_rooms` int(11) NOT NULL DEFAULT 0,
  `mice_room_income` decimal(15,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `daily_incomes_property_id_date_unique` (`property_id`,`date`),
  KEY `daily_incomes_user_id_foreign` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `daily_occupancies`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `daily_occupancies` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `property_id` bigint(20) unsigned NOT NULL,
  `date` date NOT NULL,
  `occupied_rooms` int(11) NOT NULL,
  `reservasi_ota` int(11) NOT NULL DEFAULT 0,
  `reservasi_properti` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `daily_occupancies_property_id_date_unique` (`property_id`,`date`),
  CONSTRAINT `daily_occupancies_property_id_foreign` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `failed_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `failed_jobs` (
  `id` bigint(20) unsigned NOT NULL,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `function_sheets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `function_sheets` (
  `id` bigint(20) unsigned NOT NULL,
  `booking_id` bigint(20) unsigned NOT NULL,
  `beo_number` varchar(255) NOT NULL,
  `contact_phone` varchar(255) DEFAULT NULL,
  `dealed_by` varchar(255) DEFAULT NULL,
  `room_setup` varchar(255) DEFAULT NULL,
  `price_package_id` bigint(20) unsigned DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `event_segments` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`event_segments`)),
  `menu_details` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`menu_details`)),
  `equipment_details` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`equipment_details`)),
  `department_notes` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`department_notes`)),
  `mice_category_id` bigint(20) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `function_sheets_beo_number_unique` (`beo_number`),
  KEY `function_sheets_booking_id_foreign` (`booking_id`),
  KEY `function_sheets_price_package_id_foreign` (`price_package_id`),
  KEY `function_sheets_mice_category_id_foreign` (`mice_category_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `hk_assignments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `hk_assignments` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `room_id` bigint(20) unsigned DEFAULT NULL,
  `inventory_id` bigint(20) unsigned DEFAULT NULL,
  `quantity_used` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `hk_assignments_user_id_foreign` (`user_id`),
  KEY `hk_assignments_room_id_foreign` (`room_id`),
  KEY `hk_assignments_inventory_id_foreign` (`inventory_id`),
  CONSTRAINT `hk_assignments_inventory_id_foreign` FOREIGN KEY (`inventory_id`) REFERENCES `inventories` (`id`) ON DELETE SET NULL,
  CONSTRAINT `hk_assignments_room_id_foreign` FOREIGN KEY (`room_id`) REFERENCES `hotel_rooms` (`id`) ON DELETE SET NULL,
  CONSTRAINT `hk_assignments_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `hotel_rooms`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `hotel_rooms` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `property_id` bigint(20) unsigned NOT NULL,
  `room_number` varchar(255) NOT NULL,
  `room_type_id` bigint(20) unsigned DEFAULT NULL,
  `capacity` int(11) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `hotel_rooms_property_id_foreign` (`property_id`),
  KEY `hotel_rooms_room_type_id_foreign` (`room_type_id`),
  CONSTRAINT `hotel_rooms_property_id_foreign` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`) ON DELETE CASCADE,
  CONSTRAINT `hotel_rooms_room_type_id_foreign` FOREIGN KEY (`room_type_id`) REFERENCES `room_types` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `incomes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `incomes` (
  `id` bigint(20) unsigned NOT NULL,
  `property_id` bigint(20) unsigned NOT NULL,
  `description` varchar(255) NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `date` date NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `incomes_property_id_foreign` (`property_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `inventories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `inventories` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `item_code` varchar(255) NOT NULL,
  `property_id` bigint(20) unsigned DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `specification` varchar(255) DEFAULT NULL,
  `category_id` bigint(20) unsigned DEFAULT NULL,
  `stock` int(11) NOT NULL DEFAULT 0,
  `minimum_standard_quantity` int(11) NOT NULL DEFAULT 0,
  `unit` varchar(255) NOT NULL,
  `unit_price` decimal(15,2) NOT NULL DEFAULT 0.00,
  `condition` enum('baik','rusak') NOT NULL DEFAULT 'baik',
  `purchase_date` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `inventories_item_code_unique` (`item_code`),
  KEY `inventories_property_id_foreign` (`property_id`),
  KEY `inventories_category_id_foreign` (`category_id`),
  CONSTRAINT `inventories_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL,
  CONSTRAINT `inventories_property_id_foreign` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `inventory_transactions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `inventory_transactions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `inventory_id` bigint(20) unsigned NOT NULL,
  `type` enum('in','out') NOT NULL,
  `quantity` int(11) NOT NULL,
  `notes` text DEFAULT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `inventory_transactions_inventory_id_foreign` (`inventory_id`),
  KEY `inventory_transactions_user_id_foreign` (`user_id`),
  CONSTRAINT `inventory_transactions_inventory_id_foreign` FOREIGN KEY (`inventory_id`) REFERENCES `inventories` (`id`) ON DELETE CASCADE,
  CONSTRAINT `inventory_transactions_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `job_batches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `job_batches` (
  `id` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `total_jobs` int(11) NOT NULL,
  `pending_jobs` int(11) NOT NULL,
  `failed_jobs` int(11) NOT NULL,
  `failed_job_ids` longtext NOT NULL,
  `options` mediumtext DEFAULT NULL,
  `cancelled_at` int(11) DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  `finished_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `jobs` (
  `id` bigint(20) unsigned NOT NULL,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint(3) unsigned NOT NULL,
  `reserved_at` int(10) unsigned DEFAULT NULL,
  `available_at` int(10) unsigned NOT NULL,
  `created_at` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `mice_categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `mice_categories` (
  `id` bigint(20) unsigned NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `migrations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `password_reset_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `price_packages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `price_packages` (
  `id` bigint(20) unsigned NOT NULL,
  `name` varchar(255) NOT NULL,
  `price` decimal(15,2) NOT NULL,
  `description` text DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `pricing_rules`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `pricing_rules` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `room_type_id` bigint(20) unsigned NOT NULL,
  `publish_rate` decimal(10,2) NOT NULL DEFAULT 0.00,
  `bottom_rate` decimal(10,2) NOT NULL DEFAULT 0.00,
  `percentage_increase` decimal(5,2) NOT NULL DEFAULT 0.00,
  `starting_bar` tinyint(4) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `pricing_rules_room_type_id_foreign` (`room_type_id`),
  CONSTRAINT `pricing_rules_room_type_id_foreign` FOREIGN KEY (`room_type_id`) REFERENCES `room_types` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `properties`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `properties` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `address` text DEFAULT NULL,
  `total_rooms` int(11) NOT NULL DEFAULT 0,
  `chart_color` varchar(255) NOT NULL DEFAULT '#cccccc',
  `bar_1` int(11) NOT NULL DEFAULT 0,
  `bar_2` int(11) NOT NULL DEFAULT 0,
  `bar_3` int(11) NOT NULL DEFAULT 0,
  `bar_4` int(11) NOT NULL DEFAULT 0,
  `bar_5` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `reservations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `reservations` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `property_id` bigint(20) unsigned NOT NULL,
  `room_type_id` bigint(20) unsigned DEFAULT NULL,
  `source` varchar(255) DEFAULT NULL,
  `final_price` decimal(10,2) DEFAULT NULL,
  `guest_name` varchar(255) NOT NULL,
  `guest_email` varchar(255) DEFAULT NULL,
  `checkin_date` date NOT NULL,
  `checkout_date` date NOT NULL,
  `number_of_rooms` int(11) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `reservations_property_id_foreign` (`property_id`),
  KEY `reservations_user_id_foreign` (`user_id`),
  KEY `reservations_room_type_id_foreign` (`room_type_id`),
  CONSTRAINT `reservations_property_id_foreign` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`) ON DELETE CASCADE,
  CONSTRAINT `reservations_room_type_id_foreign` FOREIGN KEY (`room_type_id`) REFERENCES `room_types` (`id`) ON DELETE SET NULL,
  CONSTRAINT `reservations_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `revenue_targets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `revenue_targets` (
  `id` bigint(20) unsigned NOT NULL,
  `property_id` bigint(20) unsigned NOT NULL,
  `month_year` date NOT NULL,
  `target_amount` decimal(15,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `room_amenities`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `room_amenities` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `room_id` int(10) unsigned NOT NULL,
  `inventory_id` bigint(20) unsigned NOT NULL,
  `quantity` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `room_types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `room_types` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `property_id` bigint(20) unsigned NOT NULL,
  `name` varchar(255) NOT NULL,
  `type` varchar(255) NOT NULL DEFAULT 'hotel',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `room_types_property_id_foreign` (`property_id`),
  CONSTRAINT `room_types_property_id_foreign` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `rooms`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `rooms` (
  `id` bigint(20) unsigned NOT NULL,
  `room_number` varchar(255) DEFAULT NULL,
  `property_id` bigint(20) unsigned NOT NULL,
  `name` varchar(255) NOT NULL,
  `capacity` varchar(255) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `room_type_id` bigint(20) unsigned DEFAULT NULL,
  KEY `rooms_room_type_id_foreign` (`room_type_id`),
  CONSTRAINT `rooms_room_type_id_foreign` FOREIGN KEY (`room_type_id`) REFERENCES `room_types` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `settings` (
  `id` bigint(20) unsigned NOT NULL,
  `key` varchar(255) NOT NULL,
  `value` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `targets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `targets` (
  `id` bigint(20) unsigned NOT NULL,
  `property_id` bigint(20) unsigned NOT NULL,
  `year` year(4) NOT NULL,
  `month` tinyint(3) unsigned NOT NULL,
  `target_amount` decimal(15,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone_number` varchar(255) DEFAULT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `role` varchar(255) NOT NULL,
  `receives_whatsapp_notifications` tinyint(1) NOT NULL DEFAULT 0,
  `property_id` bigint(20) unsigned DEFAULT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`),
  KEY `users_property_id_foreign` (`property_id`),
  CONSTRAINT `users_property_id_foreign` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

/*M!999999\- enable the sandbox mode */ 
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (1,'2025_07_26_083529_create_hotel_tracker_schema',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (2,'2025_07_26_112803_safely_restructure_database',2);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (3,'2025_07_26_114222_fix_id_column_in_daily_incomes_table',3);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (4,'2025_07_26_121013_fix_id_column_in_activity_logs_table',4);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (5,'2025_07_26_121227_make_action_column_nullable_in_activity_logs_table',5);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (6,'2025_07_26_121453_make_loggable_columns_nullable_in_activity_logs_table',6);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (7,'2025_08_08_024948_modify_id_in_bookings_table',7);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (8,'2025_08_04_105544_add_reservation_types_to_daily_occupancies_table',8);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (9,'2025_08_09_042030_modify_id_in_bookings_table',8);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (10,'2025_08_11_003106_add_user_id_to_reservations_table',9);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (11,'2025_08_11_003329_add_room_type_id_to_reservations_table',10);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (12,'2025_08_12_091536_add_phone_number_to_users_table',11);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (13,'2025_08_12_093308_add_notification_flag_to_users_table',12);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (20,'2025_08_23_041200_create_inventories_table',13);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (21,'2025_08_23_041201_create_amenities_table',13);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (22,'2025_08_23_045238_add_category_to_inventories_table',14);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (23,'2025_08_23_045859_add_price_and_unit_to_inventories_table',15);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (24,'2025_08_23_060501_add_soft_deletes_to_rooms_table',16);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (25,'2025_08_23_062818_add_type_to_room_types_table',17);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (30,'2025_08_23_063915_add_room_type_id_to_rooms_table',18);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (34,'2025_08_23_064038_add_type_to_room_types_table',19);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (35,'2025_08_23_065704_create_hotel_rooms_table',19);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (39,'2025_08_25_065529_create_hk_assignments_table',20);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (40,'2025_08_28_064345_add_property_id_to_activity_logs_table',20);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (41,'2025_09_01_035740_add_property_id_to_inventories_table',21);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (42,'2025_09_01_042424_add_usage_details_to_hk_assignments_table',22);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (43,'2025_09_02_131858_add_total_rooms_to_properties_table',23);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (44,'2025_10_14_071740_create_categories_table',24);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (45,'2025_10_14_071836_create_inventory_transactions_table',24);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (46,'2025_10_14_072402_recreate_inventories_table',25);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (47,'2025_10_14_091127_add_unit_price_to_inventories_table',26);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (49,'2025_10_14_092837_add_msq_and_purchase_date_to_inventories_table',27);
