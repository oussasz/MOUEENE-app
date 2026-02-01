-- MySQL dump 10.13  Distrib 9.2.0, for Linux (x86_64)
--
-- Host: localhost    Database: moueene_db
-- ------------------------------------------------------
-- Server version	9.2.0

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `admin_users`
--

DROP TABLE IF EXISTS `admin_users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `admin_users` (
  `admin_id` int NOT NULL AUTO_INCREMENT,
  `username` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password_hash` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `full_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `role` enum('super_admin','admin','moderator','support') COLLATE utf8mb4_unicode_ci DEFAULT 'admin',
  `permissions` text COLLATE utf8mb4_unicode_ci,
  `is_active` tinyint(1) DEFAULT '1',
  `last_login` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`admin_id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`),
  KEY `idx_username` (`username`),
  KEY `idx_email` (`email`),
  KEY `idx_role` (`role`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `admin_users`
--

LOCK TABLES `admin_users` WRITE;
/*!40000 ALTER TABLE `admin_users` DISABLE KEYS */;
INSERT INTO `admin_users` VALUES (1,'admin','admin@moueene.com','$2y$10$IWj8lYzxH4HIEzOa9gpveuh.I73TqkU11XQvu5LlV2jp1ihYrQL86','System Administrator','super_admin',NULL,1,'2026-01-28 12:06:14','2026-01-26 15:06:06','2026-01-28 12:06:14');
/*!40000 ALTER TABLE `admin_users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `booking_history`
--

DROP TABLE IF EXISTS `booking_history`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `booking_history` (
  `history_id` int NOT NULL AUTO_INCREMENT,
  `booking_id` int NOT NULL,
  `previous_status` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `new_status` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `changed_by_type` enum('user','provider','admin','system') COLLATE utf8mb4_unicode_ci NOT NULL,
  `changed_by_id` int DEFAULT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`history_id`),
  KEY `idx_booking` (`booking_id`),
  KEY `idx_created_at` (`created_at`),
  CONSTRAINT `booking_history_ibfk_1` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`booking_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `booking_history`
--

LOCK TABLES `booking_history` WRITE;
/*!40000 ALTER TABLE `booking_history` DISABLE KEYS */;
/*!40000 ALTER TABLE `booking_history` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `bookings`
--

DROP TABLE IF EXISTS `bookings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `bookings` (
  `booking_id` int NOT NULL AUTO_INCREMENT,
  `booking_reference` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` int NOT NULL,
  `provider_id` int NOT NULL,
  `service_id` int NOT NULL,
  `booking_date` date NOT NULL,
  `booking_time` time NOT NULL,
  `duration_minutes` int DEFAULT '60',
  `service_address` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `service_city` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `service_zip_code` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL,
  `service_price` decimal(10,2) NOT NULL,
  `service_fee` decimal(10,2) DEFAULT '0.00',
  `total_price` decimal(10,2) NOT NULL,
  `discount_amount` decimal(10,2) DEFAULT '0.00',
  `final_price` decimal(10,2) NOT NULL,
  `booking_status` enum('pending','confirmed','in_progress','completed','cancelled','rejected','refunded') COLLATE utf8mb4_unicode_ci DEFAULT 'pending',
  `payment_status` enum('pending','paid','failed','refunded') COLLATE utf8mb4_unicode_ci DEFAULT 'pending',
  `special_instructions` text COLLATE utf8mb4_unicode_ci,
  `cancellation_reason` text COLLATE utf8mb4_unicode_ci,
  `cancellation_date` timestamp NULL DEFAULT NULL,
  `cancelled_by` enum('user','provider','admin') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `provider_notes` text COLLATE utf8mb4_unicode_ci,
  `admin_notes` text COLLATE utf8mb4_unicode_ci,
  `confirmed_at` timestamp NULL DEFAULT NULL,
  `started_at` timestamp NULL DEFAULT NULL,
  `completed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`booking_id`),
  UNIQUE KEY `booking_reference` (`booking_reference`),
  KEY `idx_user` (`user_id`),
  KEY `idx_provider` (`provider_id`),
  KEY `idx_service` (`service_id`),
  KEY `idx_reference` (`booking_reference`),
  KEY `idx_status` (`booking_status`),
  KEY `idx_payment_status` (`payment_status`),
  KEY `idx_booking_date` (`booking_date`),
  KEY `idx_created_at` (`created_at`),
  CONSTRAINT `bookings_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  CONSTRAINT `bookings_ibfk_2` FOREIGN KEY (`provider_id`) REFERENCES `providers` (`provider_id`) ON DELETE CASCADE,
  CONSTRAINT `bookings_ibfk_3` FOREIGN KEY (`service_id`) REFERENCES `services` (`service_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bookings`
--

LOCK TABLES `bookings` WRITE;
/*!40000 ALTER TABLE `bookings` DISABLE KEYS */;
/*!40000 ALTER TABLE `bookings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `content_page_translations`
--

DROP TABLE IF EXISTS `content_page_translations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `content_page_translations` (
  `page_id` int NOT NULL,
  `language_code` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `page_title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `page_content` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `meta_title` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `meta_description` text COLLATE utf8mb4_unicode_ci,
  PRIMARY KEY (`page_id`,`language_code`),
  KEY `idx_language` (`language_code`),
  CONSTRAINT `content_page_translations_ibfk_1` FOREIGN KEY (`page_id`) REFERENCES `content_pages` (`page_id`) ON DELETE CASCADE,
  CONSTRAINT `content_page_translations_ibfk_2` FOREIGN KEY (`language_code`) REFERENCES `languages` (`language_code`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `content_page_translations`
--

LOCK TABLES `content_page_translations` WRITE;
/*!40000 ALTER TABLE `content_page_translations` DISABLE KEYS */;
INSERT INTO `content_page_translations` VALUES (1,'en','About Us','About Moueene content goes here.','About Moueene','Learn more about Moueene.'),(2,'en','How It Works','How Moueene works content goes here.','How Moueene Works','Discover how to book services on Moueene.'),(3,'en','FAQ','Frequently asked questions content goes here.','Moueene FAQ','Answers to common questions.'),(4,'en','Terms & Conditions','Terms and conditions content goes here.','Terms & Conditions','Read Moueene terms and conditions.'),(5,'en','Privacy Policy','Privacy policy content goes here.','Privacy Policy','Read Moueene privacy policy.'),(6,'en','Contact Us','Contact information content goes here.','Contact Moueene','Get in touch with Moueene support.'),(7,'en','Help Center','Help center content goes here.','Moueene Help Center','Find help and support resources.');
/*!40000 ALTER TABLE `content_page_translations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `content_pages`
--

DROP TABLE IF EXISTS `content_pages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `content_pages` (
  `page_id` int NOT NULL AUTO_INCREMENT,
  `page_slug` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_active` tinyint(1) DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`page_id`),
  UNIQUE KEY `page_slug` (`page_slug`),
  KEY `idx_slug` (`page_slug`),
  KEY `idx_active` (`is_active`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `content_pages`
--

LOCK TABLES `content_pages` WRITE;
/*!40000 ALTER TABLE `content_pages` DISABLE KEYS */;
INSERT INTO `content_pages` VALUES (1,'about',1,'2026-01-26 15:06:06','2026-01-26 15:06:06'),(2,'how-it-works',1,'2026-01-26 15:06:06','2026-01-26 15:06:06'),(3,'faq',1,'2026-01-26 15:06:06','2026-01-26 15:06:06'),(4,'terms',1,'2026-01-26 15:06:06','2026-01-26 15:06:06'),(5,'privacy',1,'2026-01-26 15:06:06','2026-01-26 15:06:06'),(6,'contact',1,'2026-01-26 15:06:06','2026-01-26 15:06:06'),(7,'help',1,'2026-01-26 15:06:06','2026-01-26 15:06:06');
/*!40000 ALTER TABLE `content_pages` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `favorites`
--

DROP TABLE IF EXISTS `favorites`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `favorites` (
  `favorite_id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `provider_id` int NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`favorite_id`),
  UNIQUE KEY `unique_favorite` (`user_id`,`provider_id`),
  KEY `idx_user` (`user_id`),
  KEY `idx_provider` (`provider_id`),
  CONSTRAINT `favorites_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  CONSTRAINT `favorites_ibfk_2` FOREIGN KEY (`provider_id`) REFERENCES `providers` (`provider_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `favorites`
--

LOCK TABLES `favorites` WRITE;
/*!40000 ALTER TABLE `favorites` DISABLE KEYS */;
/*!40000 ALTER TABLE `favorites` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `languages`
--

DROP TABLE IF EXISTS `languages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `languages` (
  `language_code` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `native_name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `direction` enum('ltr','rtl') COLLATE utf8mb4_unicode_ci DEFAULT 'ltr',
  `is_active` tinyint(1) DEFAULT '1',
  `is_default` tinyint(1) DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`language_code`),
  KEY `idx_active` (`is_active`),
  KEY `idx_default` (`is_default`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `languages`
--

LOCK TABLES `languages` WRITE;
/*!40000 ALTER TABLE `languages` DISABLE KEYS */;
INSERT INTO `languages` VALUES ('ar','Arabic','العربية','rtl',1,0,'2026-01-26 15:06:06','2026-01-26 15:06:06'),('en','English','English','ltr',1,1,'2026-01-26 15:06:06','2026-01-26 15:06:06'),('fr','French','Français','ltr',1,0,'2026-01-26 15:06:06','2026-01-26 15:06:06');
/*!40000 ALTER TABLE `languages` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `messages`
--

DROP TABLE IF EXISTS `messages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `messages` (
  `message_id` int NOT NULL AUTO_INCREMENT,
  `sender_type` enum('user','provider','admin') COLLATE utf8mb4_unicode_ci NOT NULL,
  `sender_id` int NOT NULL,
  `receiver_type` enum('user','provider','admin') COLLATE utf8mb4_unicode_ci NOT NULL,
  `receiver_id` int NOT NULL,
  `booking_id` int DEFAULT NULL,
  `subject` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `message` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `attachment_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_read` tinyint(1) DEFAULT '0',
  `read_at` timestamp NULL DEFAULT NULL,
  `is_deleted_by_sender` tinyint(1) DEFAULT '0',
  `is_deleted_by_receiver` tinyint(1) DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`message_id`),
  KEY `idx_sender` (`sender_type`,`sender_id`),
  KEY `idx_receiver` (`receiver_type`,`receiver_id`),
  KEY `idx_booking` (`booking_id`),
  KEY `idx_created_at` (`created_at`),
  KEY `idx_read_status` (`is_read`),
  CONSTRAINT `messages_ibfk_1` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`booking_id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `messages`
--

LOCK TABLES `messages` WRITE;
/*!40000 ALTER TABLE `messages` DISABLE KEYS */;
/*!40000 ALTER TABLE `messages` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `notifications`
--

DROP TABLE IF EXISTS `notifications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `notifications` (
  `notification_id` int NOT NULL AUTO_INCREMENT,
  `recipient_type` enum('user','provider','admin') COLLATE utf8mb4_unicode_ci NOT NULL,
  `recipient_id` int NOT NULL,
  `notification_type` enum('booking_confirmed','booking_cancelled','booking_reminder','booking_completed','payment_received','review_received','message_received','account_verification','promotion','system_alert') COLLATE utf8mb4_unicode_ci NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `message` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `action_url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `related_id` int DEFAULT NULL,
  `is_read` tinyint(1) DEFAULT '0',
  `read_at` timestamp NULL DEFAULT NULL,
  `is_sent` tinyint(1) DEFAULT '0',
  `sent_at` timestamp NULL DEFAULT NULL,
  `sent_via_email` tinyint(1) DEFAULT '0',
  `sent_via_sms` tinyint(1) DEFAULT '0',
  `sent_via_push` tinyint(1) DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`notification_id`),
  KEY `idx_recipient` (`recipient_type`,`recipient_id`),
  KEY `idx_type` (`notification_type`),
  KEY `idx_read_status` (`is_read`),
  KEY `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `notifications`
--

LOCK TABLES `notifications` WRITE;
/*!40000 ALTER TABLE `notifications` DISABLE KEYS */;
/*!40000 ALTER TABLE `notifications` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `payments`
--

DROP TABLE IF EXISTS `payments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `payments` (
  `payment_id` int NOT NULL AUTO_INCREMENT,
  `booking_id` int NOT NULL,
  `user_id` int NOT NULL,
  `provider_id` int NOT NULL,
  `transaction_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `payment_method` enum('credit_card','debit_card','paypal','stripe','cash','bank_transfer') COLLATE utf8mb4_unicode_ci NOT NULL,
  `payment_gateway` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `amount` decimal(10,2) NOT NULL,
  `service_fee` decimal(10,2) DEFAULT '0.00',
  `provider_amount` decimal(10,2) NOT NULL,
  `platform_fee` decimal(10,2) DEFAULT '0.00',
  `payment_status` enum('pending','processing','completed','failed','refunded','cancelled') COLLATE utf8mb4_unicode_ci DEFAULT 'pending',
  `currency` varchar(3) COLLATE utf8mb4_unicode_ci DEFAULT 'MAD',
  `payment_description` text COLLATE utf8mb4_unicode_ci,
  `failure_reason` text COLLATE utf8mb4_unicode_ci,
  `refund_amount` decimal(10,2) DEFAULT NULL,
  `refund_reason` text COLLATE utf8mb4_unicode_ci,
  `refund_date` timestamp NULL DEFAULT NULL,
  `card_last_four` varchar(4) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `card_brand` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `paid_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`payment_id`),
  UNIQUE KEY `transaction_id` (`transaction_id`),
  KEY `idx_booking` (`booking_id`),
  KEY `idx_user` (`user_id`),
  KEY `idx_provider` (`provider_id`),
  KEY `idx_transaction` (`transaction_id`),
  KEY `idx_status` (`payment_status`),
  KEY `idx_created_at` (`created_at`),
  CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`booking_id`) ON DELETE CASCADE,
  CONSTRAINT `payments_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  CONSTRAINT `payments_ibfk_3` FOREIGN KEY (`provider_id`) REFERENCES `providers` (`provider_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `payments`
--

LOCK TABLES `payments` WRITE;
/*!40000 ALTER TABLE `payments` DISABLE KEYS */;
/*!40000 ALTER TABLE `payments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `provider_documents`
--

DROP TABLE IF EXISTS `provider_documents`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `provider_documents` (
  `document_id` int NOT NULL AUTO_INCREMENT,
  `provider_id` int NOT NULL,
  `document_type` enum('id_card','passport','driver_license','business_license','certificate','insurance','other') COLLATE utf8mb4_unicode_ci NOT NULL,
  `document_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `document_path` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `document_number` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `issue_date` date DEFAULT NULL,
  `expiry_date` date DEFAULT NULL,
  `verification_status` enum('pending','verified','rejected') COLLATE utf8mb4_unicode_ci DEFAULT 'pending',
  `verified_by` int DEFAULT NULL,
  `verified_at` timestamp NULL DEFAULT NULL,
  `rejection_reason` text COLLATE utf8mb4_unicode_ci,
  `uploaded_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`document_id`),
  KEY `idx_provider` (`provider_id`),
  KEY `idx_verification_status` (`verification_status`),
  CONSTRAINT `provider_documents_ibfk_1` FOREIGN KEY (`provider_id`) REFERENCES `providers` (`provider_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `provider_documents`
--

LOCK TABLES `provider_documents` WRITE;
/*!40000 ALTER TABLE `provider_documents` DISABLE KEYS */;
/*!40000 ALTER TABLE `provider_documents` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `provider_services`
--

DROP TABLE IF EXISTS `provider_services`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `provider_services` (
  `provider_service_id` int NOT NULL AUTO_INCREMENT,
  `provider_id` int NOT NULL,
  `service_id` int NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `price_type` enum('fixed','hourly','per_item','custom') COLLATE utf8mb4_unicode_ci DEFAULT 'fixed',
  `description` text COLLATE utf8mb4_unicode_ci,
  `is_active` tinyint(1) DEFAULT '1',
  `total_bookings` int DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`provider_service_id`),
  UNIQUE KEY `unique_provider_service` (`provider_id`,`service_id`),
  KEY `idx_provider` (`provider_id`),
  KEY `idx_service` (`service_id`),
  KEY `idx_active` (`is_active`),
  CONSTRAINT `provider_services_ibfk_1` FOREIGN KEY (`provider_id`) REFERENCES `providers` (`provider_id`) ON DELETE CASCADE,
  CONSTRAINT `provider_services_ibfk_2` FOREIGN KEY (`service_id`) REFERENCES `services` (`service_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `provider_services`
--

LOCK TABLES `provider_services` WRITE;
/*!40000 ALTER TABLE `provider_services` DISABLE KEYS */;
/*!40000 ALTER TABLE `provider_services` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `providers`
--

DROP TABLE IF EXISTS `providers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `providers` (
  `provider_id` int NOT NULL AUTO_INCREMENT,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password_hash` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `business_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `first_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `profile_picture` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bio` text COLLATE utf8mb4_unicode_ci,
  `address` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `city` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `state` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `zip_code` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `country` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT 'Algeria',
  `date_of_birth` date DEFAULT NULL,
  `gender` enum('male','female','other','prefer_not_to_say') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `experience_years` int DEFAULT '0',
  `certification` text COLLATE utf8mb4_unicode_ci,
  `specialization` text COLLATE utf8mb4_unicode_ci,
  `languages_spoken` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `service_radius` int DEFAULT '10',
  `commercial_registry_number` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nif` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nis` text COLLATE utf8mb4_unicode_ci,
  `email_verified` tinyint(1) DEFAULT '0',
  `phone_verified` tinyint(1) DEFAULT '0',
  `identity_verified` tinyint(1) DEFAULT '0',
  `background_check_verified` tinyint(1) DEFAULT '0',
  `verification_status` enum('pending','verified','rejected') COLLATE utf8mb4_unicode_ci DEFAULT 'pending',
  `verification_date` timestamp NULL DEFAULT NULL,
  `account_status` enum('active','inactive','suspended','deactivated','pending') COLLATE utf8mb4_unicode_ci DEFAULT 'pending',
  `average_rating` decimal(3,2) DEFAULT '0.00',
  `total_reviews` int DEFAULT '0',
  `total_bookings` int DEFAULT '0',
  `completed_bookings` int DEFAULT '0',
  `cancelled_bookings` int DEFAULT '0',
  `response_rate` decimal(5,2) DEFAULT '0.00',
  `acceptance_rate` decimal(5,2) DEFAULT '0.00',
  `service_fee_percentage` decimal(5,2) DEFAULT '15.00',
  `availability_status` enum('available','busy','offline') COLLATE utf8mb4_unicode_ci DEFAULT 'offline',
  `registration_date` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `last_login` timestamp NULL DEFAULT NULL,
  `reset_token` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `reset_token_expires` timestamp NULL DEFAULT NULL,
  `verification_token` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `preferred_language` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT 'en',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`provider_id`),
  UNIQUE KEY `email` (`email`),
  KEY `idx_email` (`email`),
  KEY `idx_phone` (`phone`),
  KEY `idx_city` (`city`),
  KEY `idx_verification_status` (`verification_status`),
  KEY `idx_account_status` (`account_status`),
  KEY `idx_average_rating` (`average_rating`),
  KEY `idx_availability` (`availability_status`),
  KEY `idx_preferred_language` (`preferred_language`),
  CONSTRAINT `providers_ibfk_1` FOREIGN KEY (`preferred_language`) REFERENCES `languages` (`language_code`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `providers`
--

LOCK TABLES `providers` WRITE;
/*!40000 ALTER TABLE `providers` DISABLE KEYS */;
INSERT INTO `providers` VALUES (2,'brahimi.oussamaa@gmaol.com','$2y$10$ojlcxnrYYcwciUKcmu/vle3rGZKGEO0mBLyK7MFE.P7.E9qBiOi3a',NULL,'Brahimi','Oussama','+213540537886',NULL,NULL,'','',NULL,NULL,'Morocco',NULL,NULL,0,NULL,NULL,NULL,10,NULL,NULL,NULL,0,0,0,0,'rejected','2026-01-28 10:02:35','deactivated',0.00,0,0,0,0,0.00,0.00,15.00,'offline','2026-01-26 15:18:40','2026-01-27 22:17:03',NULL,NULL,'a01b701234841238d2d61675fe1e47b0','en','2026-01-26 15:18:40','2026-01-28 10:11:13'),(3,'rabahkhadoussi70@gmail.com','$2y$10$zFCDCev1aUoNEeGoWgLJk.uQiRYiAjgYRp425lWPus8vZijZvR9cG',NULL,'Rabah','Khadoussi','0549462250','/assets/images/default-avatar.jpg',NULL,'Beni Maouche, Bejaia','Bejaia',NULL,NULL,'Morocco',NULL,NULL,0,NULL,NULL,NULL,10,NULL,NULL,NULL,0,0,0,0,'verified','2026-01-28 10:02:37','active',0.00,0,0,0,0,0.00,0.00,15.00,'offline','2026-01-27 23:51:53','2026-01-28 14:03:41',NULL,NULL,'59626e3cd1136821302a6d26123c7d28','en','2026-01-27 23:51:53','2026-01-28 14:03:41');
/*!40000 ALTER TABLE `providers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `reviews`
--

DROP TABLE IF EXISTS `reviews`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `reviews` (
  `review_id` int NOT NULL AUTO_INCREMENT,
  `booking_id` int NOT NULL,
  `user_id` int NOT NULL,
  `provider_id` int NOT NULL,
  `service_id` int NOT NULL,
  `rating` int NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `comment` text COLLATE utf8mb4_unicode_ci,
  `professionalism_rating` int DEFAULT NULL,
  `punctuality_rating` int DEFAULT NULL,
  `quality_rating` int DEFAULT NULL,
  `value_rating` int DEFAULT NULL,
  `provider_response` text COLLATE utf8mb4_unicode_ci,
  `provider_response_date` timestamp NULL DEFAULT NULL,
  `is_verified` tinyint(1) DEFAULT '0',
  `is_featured` tinyint(1) DEFAULT '0',
  `is_visible` tinyint(1) DEFAULT '1',
  `flagged` tinyint(1) DEFAULT '0',
  `flag_reason` text COLLATE utf8mb4_unicode_ci,
  `helpful_count` int DEFAULT '0',
  `not_helpful_count` int DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`review_id`),
  UNIQUE KEY `unique_booking_review` (`booking_id`),
  KEY `idx_provider` (`provider_id`),
  KEY `idx_user` (`user_id`),
  KEY `idx_service` (`service_id`),
  KEY `idx_rating` (`rating`),
  KEY `idx_created_at` (`created_at`),
  KEY `idx_visible` (`is_visible`),
  CONSTRAINT `reviews_ibfk_1` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`booking_id`) ON DELETE CASCADE,
  CONSTRAINT `reviews_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  CONSTRAINT `reviews_ibfk_3` FOREIGN KEY (`provider_id`) REFERENCES `providers` (`provider_id`) ON DELETE CASCADE,
  CONSTRAINT `reviews_ibfk_4` FOREIGN KEY (`service_id`) REFERENCES `services` (`service_id`) ON DELETE CASCADE,
  CONSTRAINT `reviews_chk_1` CHECK (((`rating` >= 1) and (`rating` <= 5))),
  CONSTRAINT `reviews_chk_2` CHECK (((`professionalism_rating` >= 1) and (`professionalism_rating` <= 5))),
  CONSTRAINT `reviews_chk_3` CHECK (((`punctuality_rating` >= 1) and (`punctuality_rating` <= 5))),
  CONSTRAINT `reviews_chk_4` CHECK (((`quality_rating` >= 1) and (`quality_rating` <= 5))),
  CONSTRAINT `reviews_chk_5` CHECK (((`value_rating` >= 1) and (`value_rating` <= 5)))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `reviews`
--

LOCK TABLES `reviews` WRITE;
/*!40000 ALTER TABLE `reviews` DISABLE KEYS */;
/*!40000 ALTER TABLE `reviews` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `service_availability`
--

DROP TABLE IF EXISTS `service_availability`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `service_availability` (
  `availability_id` int NOT NULL AUTO_INCREMENT,
  `provider_id` int NOT NULL,
  `day_of_week` enum('monday','tuesday','wednesday','thursday','friday','saturday','sunday') COLLATE utf8mb4_unicode_ci NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `is_available` tinyint(1) DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`availability_id`),
  KEY `idx_provider` (`provider_id`),
  KEY `idx_day` (`day_of_week`),
  CONSTRAINT `service_availability_ibfk_1` FOREIGN KEY (`provider_id`) REFERENCES `providers` (`provider_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `service_availability`
--

LOCK TABLES `service_availability` WRITE;
/*!40000 ALTER TABLE `service_availability` DISABLE KEYS */;
/*!40000 ALTER TABLE `service_availability` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `service_categories`
--

DROP TABLE IF EXISTS `service_categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `service_categories` (
  `category_id` int NOT NULL AUTO_INCREMENT,
  `category_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `category_slug` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `icon` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `parent_category_id` int DEFAULT NULL,
  `display_order` int DEFAULT '0',
  `is_active` tinyint(1) DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`category_id`),
  UNIQUE KEY `category_name` (`category_name`),
  UNIQUE KEY `category_slug` (`category_slug`),
  KEY `idx_slug` (`category_slug`),
  KEY `idx_active` (`is_active`),
  KEY `idx_parent` (`parent_category_id`),
  CONSTRAINT `service_categories_ibfk_1` FOREIGN KEY (`parent_category_id`) REFERENCES `service_categories` (`category_id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `service_categories`
--

LOCK TABLES `service_categories` WRITE;
/*!40000 ALTER TABLE `service_categories` DISABLE KEYS */;
INSERT INTO `service_categories` VALUES (1,'Cleaning Services','cleaning','Professional home and office cleaning services',NULL,NULL,NULL,1,1,'2026-01-26 15:06:06','2026-01-26 15:06:06'),(2,'Gardening & Landscaping','gardening','Garden maintenance and landscaping services',NULL,NULL,NULL,2,1,'2026-01-26 15:06:06','2026-01-26 15:06:06'),(3,'Childcare & Babysitting','childcare','Professional childcare and babysitting services',NULL,NULL,NULL,3,1,'2026-01-26 15:06:06','2026-01-26 15:06:06'),(4,'Tutoring & Education','tutoring','Educational tutoring and coaching services',NULL,NULL,NULL,4,1,'2026-01-26 15:06:06','2026-01-26 15:06:06'),(5,'Pet Care','pet-care','Pet sitting, walking, and grooming services',NULL,NULL,NULL,5,1,'2026-01-26 15:06:06','2026-01-26 15:06:06'),(6,'Elderly Care','elderly-care','Professional elderly and senior care services',NULL,NULL,NULL,6,1,'2026-01-26 15:06:06','2026-01-26 15:06:06'),(7,'Nursing & Medical','nursing','Home nursing and medical care services',NULL,NULL,NULL,7,1,'2026-01-26 15:06:06','2026-01-26 15:06:06'),(8,'Home Repairs','home-repairs','General home repair and maintenance services',NULL,NULL,NULL,8,1,'2026-01-26 15:06:06','2026-01-26 15:06:06'),(9,'Beauty & Wellness','beauty-wellness','At-home beauty and wellness services',NULL,NULL,NULL,9,1,'2026-01-26 15:06:06','2026-01-26 15:06:06'),(10,'Moving & Delivery','moving-delivery','Moving and delivery assistance services',NULL,NULL,NULL,10,1,'2026-01-26 15:06:06','2026-01-26 15:06:06');
/*!40000 ALTER TABLE `service_categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `service_category_translations`
--

DROP TABLE IF EXISTS `service_category_translations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `service_category_translations` (
  `category_id` int NOT NULL,
  `language_code` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `translated_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `translated_description` text COLLATE utf8mb4_unicode_ci,
  PRIMARY KEY (`category_id`,`language_code`),
  KEY `idx_language` (`language_code`),
  CONSTRAINT `service_category_translations_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `service_categories` (`category_id`) ON DELETE CASCADE,
  CONSTRAINT `service_category_translations_ibfk_2` FOREIGN KEY (`language_code`) REFERENCES `languages` (`language_code`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `service_category_translations`
--

LOCK TABLES `service_category_translations` WRITE;
/*!40000 ALTER TABLE `service_category_translations` DISABLE KEYS */;
INSERT INTO `service_category_translations` VALUES (1,'en','Cleaning Services','Professional home and office cleaning services'),(2,'en','Gardening & Landscaping','Garden maintenance and landscaping services'),(3,'en','Childcare & Babysitting','Professional childcare and babysitting services'),(4,'en','Tutoring & Education','Educational tutoring and coaching services'),(5,'en','Pet Care','Pet sitting, walking, and grooming services'),(6,'en','Elderly Care','Professional elderly and senior care services'),(7,'en','Nursing & Medical','Home nursing and medical care services'),(8,'en','Home Repairs','General home repair and maintenance services'),(9,'en','Beauty & Wellness','At-home beauty and wellness services'),(10,'en','Moving & Delivery','Moving and delivery assistance services');
/*!40000 ALTER TABLE `service_category_translations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `service_translations`
--

DROP TABLE IF EXISTS `service_translations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `service_translations` (
  `service_id` int NOT NULL,
  `language_code` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `translated_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `translated_description` text COLLATE utf8mb4_unicode_ci,
  `translated_detailed_description` longtext COLLATE utf8mb4_unicode_ci,
  PRIMARY KEY (`service_id`,`language_code`),
  KEY `idx_language` (`language_code`),
  CONSTRAINT `service_translations_ibfk_1` FOREIGN KEY (`service_id`) REFERENCES `services` (`service_id`) ON DELETE CASCADE,
  CONSTRAINT `service_translations_ibfk_2` FOREIGN KEY (`language_code`) REFERENCES `languages` (`language_code`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `service_translations`
--

LOCK TABLES `service_translations` WRITE;
/*!40000 ALTER TABLE `service_translations` DISABLE KEYS */;
/*!40000 ALTER TABLE `service_translations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `services`
--

DROP TABLE IF EXISTS `services`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `services` (
  `service_id` int NOT NULL AUTO_INCREMENT,
  `category_id` int NOT NULL,
  `service_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `service_slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `detailed_description` longtext COLLATE utf8mb4_unicode_ci,
  `service_image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `duration_minutes` int DEFAULT '60',
  `base_price` decimal(10,2) NOT NULL,
  `price_type` enum('fixed','hourly','per_item','custom') COLLATE utf8mb4_unicode_ci DEFAULT 'fixed',
  `is_popular` tinyint(1) DEFAULT '0',
  `is_featured` tinyint(1) DEFAULT '0',
  `is_active` tinyint(1) DEFAULT '1',
  `total_providers` int DEFAULT '0',
  `total_bookings` int DEFAULT '0',
  `average_rating` decimal(3,2) DEFAULT '0.00',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`service_id`),
  UNIQUE KEY `service_slug` (`service_slug`),
  KEY `idx_category` (`category_id`),
  KEY `idx_slug` (`service_slug`),
  KEY `idx_active` (`is_active`),
  KEY `idx_popular` (`is_popular`),
  KEY `idx_featured` (`is_featured`),
  CONSTRAINT `services_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `service_categories` (`category_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `services`
--

LOCK TABLES `services` WRITE;
/*!40000 ALTER TABLE `services` DISABLE KEYS */;
/*!40000 ALTER TABLE `services` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `user_id` int NOT NULL AUTO_INCREMENT,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password_hash` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `first_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `profile_picture` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address` text COLLATE utf8mb4_unicode_ci,
  `city` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `state` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `zip_code` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `country` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT 'Algeria',
  `date_of_birth` date DEFAULT NULL,
  `gender` enum('male','female','other','prefer_not_to_say') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email_verified` tinyint(1) DEFAULT '0',
  `phone_verified` tinyint(1) DEFAULT '0',
  `account_status` enum('active','suspended','deactivated','deleted') COLLATE utf8mb4_unicode_ci DEFAULT 'active',
  `registration_date` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `last_login` timestamp NULL DEFAULT NULL,
  `reset_token` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `reset_token_expires` timestamp NULL DEFAULT NULL,
  `verification_token` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `two_factor_enabled` tinyint(1) DEFAULT '0',
  `two_factor_secret` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `preferred_language` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT 'en',
  `timezone` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT 'Africa/Casablanca',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `email` (`email`),
  KEY `idx_email` (`email`),
  KEY `idx_phone` (`phone`),
  KEY `idx_account_status` (`account_status`),
  KEY `idx_registration_date` (`registration_date`),
  KEY `idx_preferred_language` (`preferred_language`),
  CONSTRAINT `users_ibfk_1` FOREIGN KEY (`preferred_language`) REFERENCES `languages` (`language_code`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'probe_user@example.com','$2y$10$gRfbB4sQ11sRwaDgjcPTVuaOnCiKOAIlY0UccDikMeHU5wmfLqW.6','Probe','User','+212600000000',NULL,NULL,NULL,NULL,NULL,'Morocco',NULL,NULL,0,0,'suspended','2026-01-26 15:12:12',NULL,NULL,NULL,'0cdf906d2e729a8ee69e9932256a4498',0,NULL,'en','Africa/Casablanca','2026-01-26 15:12:12','2026-01-28 09:56:58'),(2,'brahimi.oussamaa@gmaol.com','$2y$10$LtIGlnwJDrO.Ed9pfkHbF.IpEZKBFKSZBa5X6e7ZvzT1WaSt8Cc0m','Brahimi','Oussama','+213540537886',NULL,NULL,NULL,NULL,NULL,'Morocco',NULL,NULL,0,0,'deactivated','2026-01-26 21:23:35','2026-01-27 22:39:57',NULL,NULL,'bcc53bfc01bfad6433428702672a9dce',0,NULL,'en','Africa/Casablanca','2026-01-26 21:23:35','2026-01-28 10:10:56');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-01-28 17:34:04
