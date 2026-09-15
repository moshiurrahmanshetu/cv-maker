-- MariaDB dump 10.19  Distrib 10.4.32-MariaDB, for Win64 (AMD64)
--
-- Host: localhost    Database: cv_maker
-- ------------------------------------------------------
-- Server version	10.4.32-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `ai_usage_logs`
--

DROP TABLE IF EXISTS `ai_usage_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ai_usage_logs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `cv_id` bigint(20) unsigned DEFAULT NULL,
  `feature` varchar(50) NOT NULL,
  `provider` varchar(50) NOT NULL DEFAULT 'mock',
  `model` varchar(50) NOT NULL DEFAULT 'default',
  `prompt_tokens` int(10) unsigned DEFAULT NULL,
  `completion_tokens` int(10) unsigned DEFAULT NULL,
  `total_tokens` int(10) unsigned DEFAULT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'success',
  `context_summary` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`context_summary`)),
  `error_message` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `ai_usage_logs_cv_id_foreign` (`cv_id`),
  KEY `ai_usage_logs_user_id_feature_index` (`user_id`,`feature`),
  KEY `ai_usage_logs_created_at_status_index` (`created_at`,`status`),
  KEY `ai_usage_logs_feature_index` (`feature`),
  KEY `ai_usage_logs_status_index` (`status`),
  CONSTRAINT `ai_usage_logs_cv_id_foreign` FOREIGN KEY (`cv_id`) REFERENCES `cvs` (`id`) ON DELETE CASCADE,
  CONSTRAINT `ai_usage_logs_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ai_usage_logs`
--

LOCK TABLES `ai_usage_logs` WRITE;
/*!40000 ALTER TABLE `ai_usage_logs` DISABLE KEYS */;
/*!40000 ALTER TABLE `ai_usage_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cv_awards`
--

DROP TABLE IF EXISTS `cv_awards`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cv_awards` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `cv_id` bigint(20) unsigned NOT NULL,
  `title` varchar(255) NOT NULL,
  `issuer` varchar(255) DEFAULT NULL,
  `issue_date` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `sort_order` smallint(5) unsigned NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `cv_awards_cv_id_foreign` (`cv_id`),
  CONSTRAINT `cv_awards_cv_id_foreign` FOREIGN KEY (`cv_id`) REFERENCES `cvs` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cv_awards`
--

LOCK TABLES `cv_awards` WRITE;
/*!40000 ALTER TABLE `cv_awards` DISABLE KEYS */;
/*!40000 ALTER TABLE `cv_awards` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cv_certifications`
--

DROP TABLE IF EXISTS `cv_certifications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cv_certifications` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `cv_id` bigint(20) unsigned NOT NULL,
  `name` varchar(255) NOT NULL,
  `issuing_organization` varchar(255) DEFAULT NULL,
  `issue_date` varchar(255) DEFAULT NULL,
  `expiration_date` varchar(255) DEFAULT NULL,
  `credential_id` varchar(255) DEFAULT NULL,
  `credential_url` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `sort_order` smallint(5) unsigned NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `cv_certifications_cv_id_foreign` (`cv_id`),
  CONSTRAINT `cv_certifications_cv_id_foreign` FOREIGN KEY (`cv_id`) REFERENCES `cvs` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cv_certifications`
--

LOCK TABLES `cv_certifications` WRITE;
/*!40000 ALTER TABLE `cv_certifications` DISABLE KEYS */;
/*!40000 ALTER TABLE `cv_certifications` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cv_custom_sections`
--

DROP TABLE IF EXISTS `cv_custom_sections`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cv_custom_sections` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `cv_id` bigint(20) unsigned NOT NULL,
  `section_title` varchar(255) NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `subtitle` varchar(255) DEFAULT NULL,
  `date_period` varchar(255) DEFAULT NULL,
  `content` text DEFAULT NULL,
  `sort_order` smallint(5) unsigned NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `cv_custom_sections_cv_id_foreign` (`cv_id`),
  CONSTRAINT `cv_custom_sections_cv_id_foreign` FOREIGN KEY (`cv_id`) REFERENCES `cvs` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cv_custom_sections`
--

LOCK TABLES `cv_custom_sections` WRITE;
/*!40000 ALTER TABLE `cv_custom_sections` DISABLE KEYS */;
/*!40000 ALTER TABLE `cv_custom_sections` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cv_educations`
--

DROP TABLE IF EXISTS `cv_educations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cv_educations` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `cv_id` bigint(20) unsigned NOT NULL,
  `institution` varchar(255) DEFAULT NULL,
  `degree` varchar(255) DEFAULT NULL,
  `field_of_study` varchar(255) DEFAULT NULL,
  `city` varchar(255) DEFAULT NULL,
  `country` varchar(255) DEFAULT NULL,
  `start_date` varchar(255) DEFAULT NULL,
  `end_date` varchar(255) DEFAULT NULL,
  `is_current` tinyint(1) NOT NULL DEFAULT 0,
  `grade_or_gpa` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `sort_order` smallint(5) unsigned NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `cv_educations_cv_id_foreign` (`cv_id`),
  CONSTRAINT `cv_educations_cv_id_foreign` FOREIGN KEY (`cv_id`) REFERENCES `cvs` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cv_educations`
--

LOCK TABLES `cv_educations` WRITE;
/*!40000 ALTER TABLE `cv_educations` DISABLE KEYS */;
/*!40000 ALTER TABLE `cv_educations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cv_experiences`
--

DROP TABLE IF EXISTS `cv_experiences`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cv_experiences` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `cv_id` bigint(20) unsigned NOT NULL,
  `job_title` varchar(255) DEFAULT NULL,
  `employer` varchar(255) DEFAULT NULL,
  `city` varchar(255) DEFAULT NULL,
  `country` varchar(255) DEFAULT NULL,
  `start_date` varchar(255) DEFAULT NULL,
  `end_date` varchar(255) DEFAULT NULL,
  `is_current` tinyint(1) NOT NULL DEFAULT 0,
  `description` text DEFAULT NULL,
  `sort_order` smallint(5) unsigned NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `cv_experiences_cv_id_foreign` (`cv_id`),
  CONSTRAINT `cv_experiences_cv_id_foreign` FOREIGN KEY (`cv_id`) REFERENCES `cvs` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cv_experiences`
--

LOCK TABLES `cv_experiences` WRITE;
/*!40000 ALTER TABLE `cv_experiences` DISABLE KEYS */;
/*!40000 ALTER TABLE `cv_experiences` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cv_languages`
--

DROP TABLE IF EXISTS `cv_languages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cv_languages` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `cv_id` bigint(20) unsigned NOT NULL,
  `language` varchar(255) NOT NULL,
  `proficiency` varchar(50) DEFAULT NULL,
  `sort_order` smallint(5) unsigned NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `cv_languages_cv_id_foreign` (`cv_id`),
  CONSTRAINT `cv_languages_cv_id_foreign` FOREIGN KEY (`cv_id`) REFERENCES `cvs` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cv_languages`
--

LOCK TABLES `cv_languages` WRITE;
/*!40000 ALTER TABLE `cv_languages` DISABLE KEYS */;
/*!40000 ALTER TABLE `cv_languages` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cv_personal_infos`
--

DROP TABLE IF EXISTS `cv_personal_infos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cv_personal_infos` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `cv_id` bigint(20) unsigned NOT NULL,
  `full_name` varchar(255) DEFAULT NULL,
  `job_title` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `city` varchar(255) DEFAULT NULL,
  `country` varchar(255) DEFAULT NULL,
  `postal_code` varchar(255) DEFAULT NULL,
  `website` varchar(255) DEFAULT NULL,
  `linkedin` varchar(255) DEFAULT NULL,
  `github` varchar(255) DEFAULT NULL,
  `other_url` varchar(255) DEFAULT NULL,
  `photo_path` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `cv_personal_infos_cv_id_unique` (`cv_id`),
  CONSTRAINT `cv_personal_infos_cv_id_foreign` FOREIGN KEY (`cv_id`) REFERENCES `cvs` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cv_personal_infos`
--

LOCK TABLES `cv_personal_infos` WRITE;
/*!40000 ALTER TABLE `cv_personal_infos` DISABLE KEYS */;
/*!40000 ALTER TABLE `cv_personal_infos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cv_projects`
--

DROP TABLE IF EXISTS `cv_projects`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cv_projects` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `cv_id` bigint(20) unsigned NOT NULL,
  `title` varchar(255) NOT NULL,
  `role` varchar(255) DEFAULT NULL,
  `project_url` varchar(255) DEFAULT NULL,
  `technologies` varchar(255) DEFAULT NULL,
  `start_date` varchar(255) DEFAULT NULL,
  `end_date` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `sort_order` smallint(5) unsigned NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `cv_projects_cv_id_foreign` (`cv_id`),
  CONSTRAINT `cv_projects_cv_id_foreign` FOREIGN KEY (`cv_id`) REFERENCES `cvs` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cv_projects`
--

LOCK TABLES `cv_projects` WRITE;
/*!40000 ALTER TABLE `cv_projects` DISABLE KEYS */;
/*!40000 ALTER TABLE `cv_projects` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cv_references`
--

DROP TABLE IF EXISTS `cv_references`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cv_references` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `cv_id` bigint(20) unsigned NOT NULL,
  `full_name` varchar(255) NOT NULL,
  `job_title` varchar(255) DEFAULT NULL,
  `company` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `relationship` varchar(255) DEFAULT NULL,
  `is_hidden` tinyint(1) NOT NULL DEFAULT 0,
  `sort_order` smallint(5) unsigned NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `cv_references_cv_id_foreign` (`cv_id`),
  CONSTRAINT `cv_references_cv_id_foreign` FOREIGN KEY (`cv_id`) REFERENCES `cvs` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cv_references`
--

LOCK TABLES `cv_references` WRITE;
/*!40000 ALTER TABLE `cv_references` DISABLE KEYS */;
/*!40000 ALTER TABLE `cv_references` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cv_skills`
--

DROP TABLE IF EXISTS `cv_skills`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cv_skills` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `cv_id` bigint(20) unsigned NOT NULL,
  `name` varchar(255) NOT NULL,
  `level` varchar(30) DEFAULT NULL,
  `rating` tinyint(3) unsigned NOT NULL DEFAULT 80,
  `category` varchar(50) DEFAULT NULL,
  `sort_order` smallint(5) unsigned NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `cv_skills_cv_id_foreign` (`cv_id`),
  CONSTRAINT `cv_skills_cv_id_foreign` FOREIGN KEY (`cv_id`) REFERENCES `cvs` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cv_skills`
--

LOCK TABLES `cv_skills` WRITE;
/*!40000 ALTER TABLE `cv_skills` DISABLE KEYS */;
/*!40000 ALTER TABLE `cv_skills` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cv_templates`
--

DROP TABLE IF EXISTS `cv_templates`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cv_templates` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `category_id` bigint(20) unsigned NOT NULL,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `key` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  `preview_image` varchar(255) DEFAULT NULL,
  `is_premium` tinyint(1) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `sort_order` smallint(5) unsigned NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `cv_templates_slug_unique` (`slug`),
  UNIQUE KEY `cv_templates_key_unique` (`key`),
  KEY `cv_templates_category_id_foreign` (`category_id`),
  CONSTRAINT `cv_templates_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `template_categories` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=209 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cv_templates`
--

LOCK TABLES `cv_templates` WRITE;
/*!40000 ALTER TABLE `cv_templates` DISABLE KEYS */;
/*!40000 ALTER TABLE `cv_templates` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cvs`
--

DROP TABLE IF EXISTS `cvs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cvs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `document_type_id` bigint(20) unsigned DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `slug` varchar(255) DEFAULT NULL,
  `summary` text DEFAULT NULL,
  `status` varchar(30) NOT NULL DEFAULT 'draft',
  `template_id` bigint(20) unsigned DEFAULT NULL,
  `template_key` varchar(50) NOT NULL DEFAULT 'classic',
  `primary_color` varchar(30) DEFAULT NULL,
  `font_family` varchar(50) DEFAULT NULL,
  `settings` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`settings`)),
  `completion_percentage` tinyint(3) unsigned NOT NULL DEFAULT 10,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `cvs_user_id_foreign` (`user_id`),
  KEY `cvs_slug_index` (`slug`),
  KEY `cvs_status_index` (`status`),
  KEY `cvs_template_id_foreign` (`template_id`),
  KEY `cvs_document_type_id_foreign` (`document_type_id`),
  CONSTRAINT `cvs_document_type_id_foreign` FOREIGN KEY (`document_type_id`) REFERENCES `document_types` (`id`) ON DELETE SET NULL,
  CONSTRAINT `cvs_template_id_foreign` FOREIGN KEY (`template_id`) REFERENCES `cv_templates` (`id`),
  CONSTRAINT `cvs_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=52 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cvs`
--

LOCK TABLES `cvs` WRITE;
/*!40000 ALTER TABLE `cvs` DISABLE KEYS */;
/*!40000 ALTER TABLE `cvs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `document_letter_details`
--

DROP TABLE IF EXISTS `document_letter_details`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `document_letter_details` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `cv_id` bigint(20) unsigned NOT NULL,
  `recipient_name` varchar(255) DEFAULT NULL,
  `recipient_title` varchar(255) DEFAULT NULL,
  `company_name` varchar(255) DEFAULT NULL,
  `company_address` text DEFAULT NULL,
  `letter_date` varchar(50) DEFAULT NULL,
  `subject` varchar(255) DEFAULT NULL,
  `salutation` varchar(255) DEFAULT NULL,
  `opening` text DEFAULT NULL,
  `body` longtext DEFAULT NULL,
  `call_to_action` text DEFAULT NULL,
  `closing` varchar(100) DEFAULT NULL,
  `sender_signature` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `document_letter_details_cv_id_unique` (`cv_id`),
  CONSTRAINT `document_letter_details_cv_id_foreign` FOREIGN KEY (`cv_id`) REFERENCES `cvs` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `document_letter_details`
--

LOCK TABLES `document_letter_details` WRITE;
/*!40000 ALTER TABLE `document_letter_details` DISABLE KEYS */;
/*!40000 ALTER TABLE `document_letter_details` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `document_type_template`
--

DROP TABLE IF EXISTS `document_type_template`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `document_type_template` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `document_type_id` bigint(20) unsigned NOT NULL,
  `cv_template_id` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `doc_type_template_unique` (`document_type_id`,`cv_template_id`),
  KEY `document_type_template_cv_template_id_foreign` (`cv_template_id`),
  CONSTRAINT `document_type_template_cv_template_id_foreign` FOREIGN KEY (`cv_template_id`) REFERENCES `cv_templates` (`id`) ON DELETE CASCADE,
  CONSTRAINT `document_type_template_document_type_id_foreign` FOREIGN KEY (`document_type_id`) REFERENCES `document_types` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=507 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `document_type_template`
--

LOCK TABLES `document_type_template` WRITE;
/*!40000 ALTER TABLE `document_type_template` DISABLE KEYS */;
/*!40000 ALTER TABLE `document_type_template` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `document_types`
--

DROP TABLE IF EXISTS `document_types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `document_types` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `icon` varchar(50) NOT NULL DEFAULT 'bi-file-earmark-text',
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `sort_order` smallint(5) unsigned NOT NULL DEFAULT 0,
  `configuration` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`configuration`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `document_types_slug_unique` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=198 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `document_types`
--

LOCK TABLES `document_types` WRITE;
/*!40000 ALTER TABLE `document_types` DISABLE KEYS */;
/*!40000 ALTER TABLE `document_types` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `failed_jobs`
--

DROP TABLE IF EXISTS `failed_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `failed_jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
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

--
-- Dumping data for table `failed_jobs`
--

LOCK TABLES `failed_jobs` WRITE;
/*!40000 ALTER TABLE `failed_jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `failed_jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `migrations`
--

DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `migrations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `migrations`
--

LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
INSERT INTO `migrations` VALUES (1,'2014_10_12_000000_create_users_table',1),(2,'2014_10_12_100000_create_password_reset_tokens_table',1),(3,'2019_08_19_000000_create_failed_jobs_table',1),(4,'2019_12_14_000001_create_personal_access_tokens_table',1),(5,'2024_01_01_000001_create_cvs_and_sections_tables',1),(6,'2024_01_02_000001_create_template_categories_and_cv_templates_tables',1),(7,'2024_01_03_000001_create_document_types_and_career_documents_architecture',1),(8,'2024_01_04_000001_create_ai_usage_logs_table',1);
/*!40000 ALTER TABLE `migrations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `password_reset_tokens`
--

DROP TABLE IF EXISTS `password_reset_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `password_reset_tokens`
--

LOCK TABLES `password_reset_tokens` WRITE;
/*!40000 ALTER TABLE `password_reset_tokens` DISABLE KEYS */;
/*!40000 ALTER TABLE `password_reset_tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `personal_access_tokens`
--

DROP TABLE IF EXISTS `personal_access_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `personal_access_tokens` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tokenable_type` varchar(255) NOT NULL,
  `tokenable_id` bigint(20) unsigned NOT NULL,
  `name` varchar(255) NOT NULL,
  `token` varchar(64) NOT NULL,
  `abilities` text DEFAULT NULL,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `personal_access_tokens`
--

LOCK TABLES `personal_access_tokens` WRITE;
/*!40000 ALTER TABLE `personal_access_tokens` DISABLE KEYS */;
/*!40000 ALTER TABLE `personal_access_tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `template_categories`
--

DROP TABLE IF EXISTS `template_categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `template_categories` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `sort_order` smallint(5) unsigned NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `template_categories_slug_unique` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=139 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `template_categories`
--

LOCK TABLES `template_categories` WRITE;
/*!40000 ALTER TABLE `template_categories` DISABLE KEYS */;
/*!40000 ALTER TABLE `template_categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `role` varchar(255) NOT NULL DEFAULT 'user',
  `avatar` varchar(255) DEFAULT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`),
  KEY `users_role_index` (`role`)
) ENGINE=InnoDB AUTO_INCREMENT=108 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
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

-- Dump completed on 2026-09-16  0:18:24
