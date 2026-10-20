-- MariaDB dump 10.19  Distrib 10.4.32-MariaDB, for Win64 (AMD64)
--
-- Host: localhost    Database: db_changeme
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
-- Table structure for table `cache`
--

DROP TABLE IF EXISTS `cache`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache`
--

LOCK TABLES `cache` WRITE;
/*!40000 ALTER TABLE `cache` DISABLE KEYS */;
/*!40000 ALTER TABLE `cache` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cache_locks`
--

DROP TABLE IF EXISTS `cache_locks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int(11) NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_locks_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache_locks`
--

LOCK TABLES `cache_locks` WRITE;
/*!40000 ALTER TABLE `cache_locks` DISABLE KEYS */;
/*!40000 ALTER TABLE `cache_locks` ENABLE KEYS */;
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
-- Table structure for table `job_batches`
--

DROP TABLE IF EXISTS `job_batches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
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

--
-- Dumping data for table `job_batches`
--

LOCK TABLES `job_batches` WRITE;
/*!40000 ALTER TABLE `job_batches` DISABLE KEYS */;
/*!40000 ALTER TABLE `job_batches` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jobs`
--

DROP TABLE IF EXISTS `jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
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

--
-- Dumping data for table `jobs`
--

LOCK TABLES `jobs` WRITE;
/*!40000 ALTER TABLE `jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `login_otps`
--

DROP TABLE IF EXISTS `login_otps`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `login_otps` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `uacc_reference` varchar(50) NOT NULL,
  `otp_hash` varchar(255) NOT NULL,
  `attempts` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `expires_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `verified_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `login_otps_uacc_reference_expires_at_index` (`uacc_reference`,`expires_at`),
  KEY `login_otps_uacc_reference_index` (`uacc_reference`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `login_otps`
--

LOCK TABLES `login_otps` WRITE;
/*!40000 ALTER TABLE `login_otps` DISABLE KEYS */;
/*!40000 ALTER TABLE `login_otps` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `maintenance_center`
--

DROP TABLE IF EXISTS `maintenance_center`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `maintenance_center` (
  `mc_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `mc_reference` text NOT NULL,
  `mc_name` varchar(255) NOT NULL,
  `mc_head_uaccid` varchar(255) DEFAULT NULL,
  `mc_head_position` varchar(255) DEFAULT NULL,
  `mc_status` enum('Active','Inactive','Deleted') NOT NULL DEFAULT 'Active',
  `mc_created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `mc_updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`mc_id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `maintenance_center`
--

LOCK TABLES `maintenance_center` WRITE;
/*!40000 ALTER TABLE `maintenance_center` DISABLE KEYS */;
INSERT INTO `maintenance_center` VALUES (1,'MC2026-00001','Office of the President',NULL,NULL,'Active','2026-10-19 02:41:13','2026-10-19 10:41:13'),(2,'MC2026-00002','College for Academic Affairs',NULL,NULL,'Active','2026-10-19 02:41:13','2026-10-19 10:41:13'),(3,'MC2026-00003','Center for Cadet Affairs',NULL,NULL,'Active','2026-10-19 02:41:13','2026-10-19 10:41:13'),(4,'MC2026-00004','Center for Research and Extension Programs',NULL,NULL,'Active','2026-10-19 02:41:13','2026-10-19 10:41:13'),(5,'MC2026-00005','Support Center',NULL,NULL,'Active','2026-10-19 02:41:13','2026-10-19 10:41:13'),(6,'MC2026-00006','Finance Center',NULL,NULL,'Active','2026-10-19 02:41:13','2026-10-19 10:41:13'),(7,'MC2026-00007','Registrar',NULL,NULL,'Active','2026-10-19 02:41:13','2026-10-19 10:41:13'),(8,'MC2026-00008','Quality Standards Office',NULL,NULL,'Active','2026-10-19 02:41:13','2026-10-19 10:41:13'),(9,'MC2026-00009','Transformative Leaders Unit',NULL,NULL,'Active','2026-10-19 02:41:13','2026-10-19 10:41:13'),(10,'MC2026-00010','NA',NULL,NULL,'Active','2026-10-19 02:41:13','2026-10-19 10:41:13');
/*!40000 ALTER TABLE `maintenance_center` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `maintenance_company`
--

DROP TABLE IF EXISTS `maintenance_company`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `maintenance_company` (
  `mco_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `mco_reference` text NOT NULL,
  `mco_name` varchar(255) NOT NULL,
  `mco_status` enum('Active','Inactive','Deleted') NOT NULL DEFAULT 'Active',
  `mco_created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `mco_updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`mco_id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `maintenance_company`
--

LOCK TABLES `maintenance_company` WRITE;
/*!40000 ALTER TABLE `maintenance_company` DISABLE KEYS */;
INSERT INTO `maintenance_company` VALUES (1,'MCO2026-00001','NYK Transnational Land Corp.','Active','2026-10-19 02:41:13','2026-10-19 10:41:13'),(2,'MCO2026-00002','NYK Transnational Properties Corp.','Active','2026-10-19 02:41:13','2026-10-19 10:41:13'),(3,'MCO2026-00003','NYK-TDG Maritime Academy','Active','2026-10-19 02:41:13','2026-10-19 10:41:13');
/*!40000 ALTER TABLE `maintenance_company` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `maintenance_department`
--

DROP TABLE IF EXISTS `maintenance_department`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `maintenance_department` (
  `md_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `md_reference` text NOT NULL,
  `md_center_reference` text NOT NULL,
  `md_name` varchar(255) NOT NULL,
  `md_status` enum('Active','Inactive','Deleted') NOT NULL DEFAULT 'Active',
  `md_created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `md_updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`md_id`)
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `maintenance_department`
--

LOCK TABLES `maintenance_department` WRITE;
/*!40000 ALTER TABLE `maintenance_department` DISABLE KEYS */;
INSERT INTO `maintenance_department` VALUES (1,'MD2026-00001','MC2026-00001','Office of the President','Active','2026-10-19 02:41:13','2026-10-19 02:41:13'),(2,'MD2026-00002','MC2026-00002','Department of Marine Transportation','Active','2026-10-19 02:41:13','2026-10-19 02:41:13'),(3,'MD2026-00003','MC2026-00002','Department of Marine Engineering','Active','2026-10-19 02:41:13','2026-10-19 02:41:13'),(4,'MD2026-00004','MC2026-00002','Department of General Education','Active','2026-10-19 02:41:13','2026-10-19 02:41:13'),(5,'MD2026-00005','MC2026-00002','Nihongo and Kumon Learning','Active','2026-10-19 02:41:13','2026-10-19 02:41:13'),(6,'MD2026-00006','MC2026-00002','Knowledge Resources Center','Active','2026-10-19 02:41:13','2026-10-19 02:41:13'),(7,'MD2026-00007','MC2026-00003','Onboard Training Office','Active','2026-10-19 02:41:13','2026-10-19 02:41:13'),(8,'MD2026-00008','MC2026-00003','Guidance and Counselling Office (Safe Harbor Hub)','Active','2026-10-19 02:41:13','2026-10-19 02:41:13'),(9,'MD2026-00009','MC2026-00003','Cadet Support Services','Active','2026-10-19 02:41:13','2026-10-19 02:41:13'),(10,'MD2026-00010','MC2026-00003','Cadet Coordinating Office','Active','2026-10-19 02:41:13','2026-10-18 19:27:52'),(11,'MD2026-00011','MC2026-00004','CREP','Active','2026-10-19 02:41:13','2026-10-19 02:41:13'),(12,'MD2026-00012','MC2026-00005','Network Operation Center','Active','2026-10-19 02:41:13','2026-10-19 02:41:13'),(13,'MD2026-00013','MC2026-00005','General Services','Active','2026-10-19 02:41:13','2026-10-19 02:41:13'),(14,'MD2026-00014','MC2026-00005','Human Resource','Active','2026-10-19 02:41:13','2026-10-19 02:41:13'),(15,'MD2026-00015','MC2026-00005','Purchasing','Active','2026-10-19 02:41:13','2026-10-19 02:41:13'),(16,'MD2026-00016','MC2026-00005','Department head','Active','2026-10-19 02:41:13','2026-10-19 02:41:13'),(17,'MD2026-00017','MC2026-00006','Finance','Active','2026-10-19 02:41:13','2026-10-19 02:41:13'),(18,'MD2026-00018','MC2026-00006','Finance (SLAF)','Active','2026-10-19 02:41:13','2026-10-19 02:41:13'),(19,'MD2026-00019','MC2026-00007','Office of the Registrar','Active','2026-10-19 02:41:13','2026-10-19 02:41:13'),(20,'MD2026-00020','MC2026-00008','Quality Standards Office','Active','2026-10-19 02:41:13','2026-10-19 02:41:13'),(21,'MD2026-00021','MC2026-00009','Transformative Leaders Unit','Active','2026-10-19 02:41:13','2026-10-19 02:41:13');
/*!40000 ALTER TABLE `maintenance_department` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `maintenance_position`
--

DROP TABLE IF EXISTS `maintenance_position`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `maintenance_position` (
  `mp_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `mp_reference` text NOT NULL,
  `mp_department_reference` text NOT NULL,
  `mp_name` varchar(255) NOT NULL,
  `mp_status` enum('Active','Inactive','Deleted') NOT NULL DEFAULT 'Active',
  `mp_created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `mp_updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`mp_id`)
) ENGINE=InnoDB AUTO_INCREMENT=65 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `maintenance_position`
--

LOCK TABLES `maintenance_position` WRITE;
/*!40000 ALTER TABLE `maintenance_position` DISABLE KEYS */;
INSERT INTO `maintenance_position` VALUES (1,'MP2026-00001','MD2026-00001','President','Active','2026-10-19 02:41:13','2026-10-19 02:41:13'),(2,'MP2026-00002','MD2026-00001','Vice President','Active','2026-10-19 02:41:13','2026-10-19 02:41:13'),(3,'MP2026-00003','MD2026-00002','Department Head','Active','2026-10-19 02:41:13','2026-10-19 02:41:13'),(4,'MP2026-00004','MD2026-00002','Instructor 1','Active','2026-10-19 02:41:13','2026-10-19 02:41:13'),(5,'MP2026-00005','MD2026-00002','Deck Laboratory Technician','Active','2026-10-19 02:41:13','2026-10-19 02:41:13'),(6,'MP2026-00006','MD2026-00003','Department Head','Active','2026-10-19 02:41:13','2026-10-19 02:41:13'),(7,'MP2026-00007','MD2026-00003','Instructor 1','Active','2026-10-19 02:41:13','2026-10-19 02:41:13'),(8,'MP2026-00008','MD2026-00003','Engine Laboratory Technician','Active','2026-10-19 02:41:13','2026-10-19 02:41:13'),(9,'MP2026-00009','MD2026-00003','Staff CAA','Active','2026-10-19 02:41:13','2026-10-19 02:41:13'),(10,'MP2026-00010','MD2026-00004','Department Head','Active','2026-10-19 02:41:13','2026-10-19 02:41:13'),(11,'MP2026-00011','MD2026-00004','Assistant Instructor','Active','2026-10-19 02:41:13','2026-10-19 02:41:13'),(12,'MP2026-00012','MD2026-00004','NSTP Instructor','Active','2026-10-19 02:41:13','2026-10-19 02:41:13'),(13,'MP2026-00013','MD2026-00004','Associate Professor','Active','2026-10-19 02:41:13','2026-10-19 02:41:13'),(14,'MP2026-00014','MD2026-00004','ICT Instructor','Active','2026-10-19 02:41:13','2026-10-19 02:41:13'),(15,'MP2026-00015','MD2026-00004','Mathematics Instructor','Active','2026-10-19 02:41:13','2026-10-19 02:41:13'),(16,'MP2026-00016','MD2026-00004','Assistant Instructor / NSTP Instructor','Active','2026-10-19 02:41:13','2026-10-19 02:41:13'),(17,'MP2026-00017','MD2026-00005','Nihongo Instructor','Active','2026-10-19 02:41:13','2026-10-19 02:41:13'),(18,'MP2026-00018','MD2026-00006','Chief Librarian','Active','2026-10-19 02:41:13','2026-10-19 02:41:13'),(19,'MP2026-00019','MD2026-00006','Junior Librarian','Active','2026-10-19 02:41:13','2026-10-19 02:41:13'),(20,'MP2026-00020','MD2026-00006','Staff','Active','2026-10-19 02:41:13','2026-10-19 02:41:13'),(21,'MP2026-00021','MD2026-00007','Onboard Training Supervisor','Active','2026-10-19 02:41:13','2026-10-19 02:41:13'),(22,'MP2026-00022','MD2026-00007','Onboard Training Officer','Active','2026-10-19 02:41:13','2026-10-19 02:41:13'),(23,'MP2026-00023','MD2026-00007','Staff','Active','2026-10-19 02:41:13','2026-10-19 02:41:13'),(24,'MP2026-00024','MD2026-00008','Guidance Counselor','Active','2026-10-19 02:41:13','2026-10-19 02:41:13'),(25,'MP2026-00025','MD2026-00008','Junior Guidance Associate','Active','2026-10-19 02:41:13','2026-10-19 02:41:13'),(26,'MP2026-00026','MD2026-00009','Cadet Support Services Officer','Active','2026-10-19 02:41:13','2026-10-19 02:41:13'),(27,'MP2026-00027','MD2026-00009','Nurse ( Men )','Active','2026-10-19 02:41:13','2026-10-19 02:41:13'),(28,'MP2026-00028','MD2026-00009','Nurse ( Women )','Active','2026-10-19 02:41:13','2026-10-19 02:41:13'),(29,'MP2026-00029','MD2026-00009','Cadet Support Services Staff','Active','2026-10-19 02:41:13','2026-10-19 02:41:13'),(30,'MP2026-00030','MD2026-00009','Chef','Active','2026-10-19 02:41:13','2026-10-19 02:41:13'),(31,'MP2026-00031','MD2026-00009','Assistant Cook / Slop Chest / Cafetorium Cashier','Active','2026-10-19 02:41:13','2026-10-19 02:41:13'),(32,'MP2026-00032','MD2026-00010','Cadet Coordinating Officer','Active','2026-10-19 02:41:13','2026-10-19 02:41:13'),(33,'MP2026-00033','MD2026-00010','Cadet Coordinating Staff','Active','2026-10-19 02:41:13','2026-10-19 02:41:13'),(34,'MP2026-00034','MD2026-00010','Physical Fitness Coordinator','Active','2026-10-19 02:41:13','2026-10-19 02:41:13'),(35,'MP2026-00035','MD2026-00011','Research and Extension Coordinator','Active','2026-10-19 02:41:13','2026-10-19 02:41:13'),(36,'MP2026-00036','MD2026-00011','Technical Writer','Active','2026-10-19 02:41:13','2026-10-19 02:41:13'),(37,'MP2026-00037','MD2026-00011','Data Analyst','Active','2026-10-19 02:41:13','2026-10-19 02:41:13'),(38,'MP2026-00038','MD2026-00012','IT Supervisor','Active','2026-10-19 02:41:13','2026-10-19 02:41:13'),(39,'MP2026-00039','MD2026-00012','Senior IT Staff','Active','2026-10-19 02:41:13','2026-10-19 02:41:13'),(40,'MP2026-00040','MD2026-00012','IT Programmer','Active','2026-10-19 02:41:13','2026-10-19 02:41:13'),(41,'MP2026-00041','MD2026-00012','Cybersecurity Staff','Active','2026-10-19 02:41:13','2026-10-19 02:41:13'),(42,'MP2026-00042','MD2026-00013','Assistant Manager','Active','2026-10-19 02:41:13','2026-10-19 02:41:13'),(43,'MP2026-00043','MD2026-00013','General Services Officer','Active','2026-10-19 02:41:13','2026-10-19 02:41:13'),(44,'MP2026-00044','MD2026-00013','Messenger','Active','2026-10-19 02:41:13','2026-10-19 02:41:13'),(45,'MP2026-00045','MD2026-00013','Maintenance Staff','Active','2026-10-19 02:41:13','2026-10-19 02:41:13'),(46,'MP2026-00046','MD2026-00014','Human Resource Officer','Active','2026-10-19 02:41:13','2026-10-19 02:41:13'),(47,'MP2026-00047','MD2026-00014','Human Resource Staff','Active','2026-10-19 02:41:13','2026-10-19 02:41:13'),(48,'MP2026-00048','MD2026-00015','Purchasing Officer','Active','2026-10-19 02:41:13','2026-10-19 02:41:13'),(49,'MP2026-00049','MD2026-00015','Purchasing Staff','Active','2026-10-19 02:41:13','2026-10-19 02:41:13'),(50,'MP2026-00050','MD2026-00016','General Manager','Active','2026-10-19 02:41:13','2026-10-19 02:41:13'),(51,'MP2026-00051','MD2026-00017','Chief Accountant','Active','2026-10-19 02:41:13','2026-10-19 02:41:13'),(52,'MP2026-00052','MD2026-00017','Accounting Supervisor','Active','2026-10-19 02:41:13','2026-10-19 02:41:13'),(53,'MP2026-00053','MD2026-00017','Senior Accountant','Active','2026-10-19 02:41:13','2026-10-19 02:41:13'),(54,'MP2026-00054','MD2026-00017','Accountant','Active','2026-10-19 02:41:13','2026-10-19 02:41:13'),(55,'MP2026-00055','MD2026-00017','Cashier','Active','2026-10-19 02:41:13','2026-10-19 02:41:13'),(56,'MP2026-00056','MD2026-00018','Admin Supervisor, SLAF','Active','2026-10-19 02:41:13','2026-10-19 02:41:13'),(57,'MP2026-00057','MD2026-00018','Staff, SLAF','Active','2026-10-19 02:41:13','2026-10-19 02:41:13'),(58,'MP2026-00058','MD2026-00019','Registrar','Active','2026-10-19 02:41:13','2026-10-19 02:41:13'),(59,'MP2026-00059','MD2026-00019','Admission Officer','Active','2026-10-19 02:41:13','2026-10-19 02:41:13'),(60,'MP2026-00060','MD2026-00019','Staff','Active','2026-10-19 02:41:13','2026-10-19 02:41:13'),(61,'MP2026-00061','MD2026-00020','Assistant Manager, QSO','Active','2026-10-19 02:41:13','2026-10-19 02:41:13'),(62,'MP2026-00062','MD2026-00020','Staff, QSO','Active','2026-10-19 02:41:13','2026-10-19 02:41:13'),(63,'MP2026-00063','MD2026-00020','Quality Manager','Active','2026-10-19 02:41:13','2026-10-19 02:41:13'),(64,'MP2026-00064','MD2026-00021','Technical Officer','Active','2026-10-19 02:41:13','2026-10-19 02:41:13');
/*!40000 ALTER TABLE `maintenance_position` ENABLE KEYS */;
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
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `migrations`
--

LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
INSERT INTO `migrations` VALUES (1,'0001_01_01_000000_create_users_account_table',1),(2,'0001_01_01_000001_create_cache_table',1),(3,'0001_01_01_000002_create_jobs_table',1),(4,'2026_08_31_061430_create_personal_access_tokens_table',1),(5,'2026_08_31_064759_create_maintenance_center_table',1),(6,'2026_09_03_021722_create_maintenance_department_table',1),(7,'2026_09_03_061251_create_maintenance_position_table',1),(8,'2026_09_05_092827_create_user_details_table',1),(9,'2026_09_17_031649_create_system_setting_table',1),(10,'2026_09_30_005137_create_otplogins_table',1),(11,'2026_10_16_025633_create_maintenance_company_table',1);
/*!40000 ALTER TABLE `migrations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `password_reset_tokens`
--

DROP TABLE IF EXISTS `password_reset_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `password_reset_tokens` (
  `uacc_email` varchar(255) NOT NULL,
  `uacc_token` varchar(255) NOT NULL,
  `uacc_created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`uacc_email`)
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
  `name` text NOT NULL,
  `token` varchar(64) NOT NULL,
  `abilities` text DEFAULT NULL,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`),
  KEY `personal_access_tokens_expires_at_index` (`expires_at`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `personal_access_tokens`
--

LOCK TABLES `personal_access_tokens` WRITE;
/*!40000 ALTER TABLE `personal_access_tokens` DISABLE KEYS */;
INSERT INTO `personal_access_tokens` VALUES (1,'App\\Models\\UsersAccount',1,'auth_token','c9ce20882894e253c5780ee8d943dd6db576a5492ef3dadbf2c8666d97ed046c','[\"*\"]',NULL,NULL,'2026-10-18 18:41:35','2026-10-18 18:41:35');
/*!40000 ALTER TABLE `personal_access_tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sessions`
--

DROP TABLE IF EXISTS `sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sessions` (
  `uacc_session_id` varchar(255) NOT NULL,
  `uacc_id` bigint(20) unsigned DEFAULT NULL,
  `uacc_type` enum('Super Admin','Admin','User') DEFAULT NULL,
  `uacc_ip_address` varchar(45) DEFAULT NULL,
  `uacc_user_agent` text DEFAULT NULL,
  `uacc_payload` longtext NOT NULL,
  `uacc_last_activity` int(11) NOT NULL,
  PRIMARY KEY (`uacc_session_id`),
  KEY `sessions_uacc_id_index` (`uacc_id`),
  KEY `sessions_uacc_type_index` (`uacc_type`),
  KEY `sessions_uacc_last_activity_index` (`uacc_last_activity`),
  CONSTRAINT `sessions_uacc_id_foreign` FOREIGN KEY (`uacc_id`) REFERENCES `users_account` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sessions`
--

LOCK TABLES `sessions` WRITE;
/*!40000 ALTER TABLE `sessions` DISABLE KEYS */;
/*!40000 ALTER TABLE `sessions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `system_setting`
--

DROP TABLE IF EXISTS `system_setting`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `system_setting` (
  `ss_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `ss_field` text DEFAULT NULL,
  `ss_value` text DEFAULT NULL,
  `ss_type` enum('text','password','email','number','url','tel','date','time','datetime-local','month','week','select','textarea','quill','checkbox','switch','color','range','image','video') NOT NULL DEFAULT 'text',
  PRIMARY KEY (`ss_id`)
) ENGINE=InnoDB AUTO_INCREMENT=35 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `system_setting`
--

LOCK TABLES `system_setting` WRITE;
/*!40000 ALTER TABLE `system_setting` DISABLE KEYS */;
INSERT INTO `system_setting` VALUES (1,'site_logo','files/setting_image_starter/Logo_20261007070847.png','image'),(2,'site_corseal_img','files/setting_image_starter/Firefly_RemoveBackground_20261006082050.png','image'),(3,'site_loading_img','files/setting_image_starter/loading_1_20261006020115.gif','image'),(4,'site_name','Laravel & React Template','text'),(5,'site_short_name','LRT','text'),(6,'site_email',NULL,'text'),(7,'site_contact','+63','text'),(8,'site_address',NULL,'text'),(9,'site_copyright',NULL,'text'),(10,'site_mission',NULL,'quill'),(11,'site_vision',NULL,'quill'),(12,'site_data_privacy',NULL,'quill'),(13,'login_background','files/setting_image_starter/pngtree-product-presentation-mockup-background-featuring-3d-studio-and-podium-on-floor-image_3651559_20261001102014.jpg','image'),(14,'login_enable_recaptcha','1','switch'),(15,'login_recaptcha_site_key','6Le4q5wtAAAAAL2jbijpemWKt6IbdeHmy0J72Fsm','text'),(16,'login_enable_registration','1','switch'),(17,'login_enable_forgot_password','1','switch'),(18,'login_enable_otp',NULL,'switch'),(19,'login_remember_me','1','switch'),(20,'login_otp_length','6','text'),(21,'login_otp_expiry_minutes','5','text'),(22,'login_otp_resend_seconds','60','text'),(23,'login_otp_max_attempts','5','text'),(24,'login_max_attempts','5','text'),(25,'login_lockout_minutes','15','text'),(26,'maintenance_timezone','Asia/Manila','select'),(27,'maintenance_date_format','F d, Y','select'),(28,'maintenance_time_format','h:i A','select'),(29,'maintenance_mode',NULL,'switch'),(30,'maintenance_start_time',NULL,'datetime-local'),(31,'maintenance_end_time',NULL,'datetime-local'),(32,'maintenance_title',NULL,'text'),(33,'maintenance_subject',NULL,'text'),(34,'maintenance_message',NULL,'quill');
/*!40000 ALTER TABLE `system_setting` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_account_login_logs`
--

DROP TABLE IF EXISTS `user_account_login_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_account_login_logs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `uacc_id` bigint(20) unsigned DEFAULT NULL,
  `uacc_reference` varchar(255) DEFAULT NULL,
  `uacc_email` varchar(255) DEFAULT NULL,
  `uacc_type` enum('Super Admin','Admin','User') DEFAULT NULL,
  `uacc_login_status` enum('Success','Failed','Locked','Blocked','Logout') NOT NULL,
  `uacc_failure_reason` varchar(255) DEFAULT NULL,
  `uacc_ip_address` varchar(45) DEFAULT NULL,
  `uacc_user_agent` text DEFAULT NULL,
  `uacc_device` varchar(255) DEFAULT NULL,
  `uacc_browser` varchar(255) DEFAULT NULL,
  `uacc_platform` varchar(255) DEFAULT NULL,
  `uacc_session_id` varchar(255) DEFAULT NULL,
  `uacc_logged_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_account_login_logs_uacc_id_index` (`uacc_id`),
  KEY `user_account_login_logs_uacc_reference_index` (`uacc_reference`),
  KEY `user_account_login_logs_uacc_email_index` (`uacc_email`),
  KEY `user_account_login_logs_uacc_type_index` (`uacc_type`),
  KEY `user_account_login_logs_uacc_login_status_index` (`uacc_login_status`),
  KEY `user_account_login_logs_uacc_session_id_index` (`uacc_session_id`),
  CONSTRAINT `user_account_login_logs_uacc_id_foreign` FOREIGN KEY (`uacc_id`) REFERENCES `users_account` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_account_login_logs`
--

LOCK TABLES `user_account_login_logs` WRITE;
/*!40000 ALTER TABLE `user_account_login_logs` DISABLE KEYS */;
INSERT INTO `user_account_login_logs` VALUES (1,1,'UACC029B77','ntmawebcontrol@ntmacad.edu.ph','Super Admin','Success',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/155.0.0.0 Safari/537.36','Desktop','Google Chrome','Windows','CpkzcmjBDZOes1I4w235LMLbUk7e93JH3nU0Pv1J','2026-10-18 18:41:35','2026-10-18 18:41:35','2026-10-18 18:41:35');
/*!40000 ALTER TABLE `user_account_login_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_account_security_logs`
--

DROP TABLE IF EXISTS `user_account_security_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_account_security_logs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `uacc_id` bigint(20) unsigned DEFAULT NULL,
  `uacc_reference` varchar(255) DEFAULT NULL,
  `uacc_type` enum('Super Admin','Admin','User') DEFAULT NULL,
  `uacc_action` enum('Password Changed','Password Reset','Email Changed','Account Locked','Account Unlocked','Account Suspended','Account Activated','2FA Enabled','2FA Disabled','2FA Verified','Session Revoked','Security Settings Changed') NOT NULL,
  `uacc_description` text DEFAULT NULL,
  `uacc_ip_address` varchar(45) DEFAULT NULL,
  `uacc_user_agent` text DEFAULT NULL,
  `uacc_performed_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_account_security_logs_uacc_id_index` (`uacc_id`),
  KEY `user_account_security_logs_uacc_reference_index` (`uacc_reference`),
  KEY `user_account_security_logs_uacc_type_index` (`uacc_type`),
  KEY `user_account_security_logs_uacc_action_index` (`uacc_action`),
  CONSTRAINT `user_account_security_logs_uacc_id_foreign` FOREIGN KEY (`uacc_id`) REFERENCES `users_account` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_account_security_logs`
--

LOCK TABLES `user_account_security_logs` WRITE;
/*!40000 ALTER TABLE `user_account_security_logs` DISABLE KEYS */;
/*!40000 ALTER TABLE `user_account_security_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_details`
--

DROP TABLE IF EXISTS `user_details`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_details` (
  `ud_id` int(11) NOT NULL AUTO_INCREMENT,
  `ud_uniqid` varchar(100) DEFAULT NULL,
  `ud_avatar` text DEFAULT NULL,
  `ud_firstname` varchar(50) DEFAULT NULL,
  `ud_middlename` varchar(50) DEFAULT NULL,
  `ud_lastname` varchar(50) DEFAULT NULL,
  `ud_email` varchar(100) DEFAULT NULL,
  `ud_center_head` enum('Yes','No') NOT NULL DEFAULT 'No',
  `ud_center_reference` text DEFAULT NULL,
  `ud_department_reference` text DEFAULT NULL,
  `ud_position_reference` text DEFAULT NULL,
  `ud_status` enum('Active','Inactive','Deleted') NOT NULL DEFAULT 'Active',
  `ud_is_approver` enum('Yes','No') NOT NULL DEFAULT 'No',
  `ud_esign` text DEFAULT NULL,
  `ud_created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `ud_updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`ud_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_details`
--

LOCK TABLES `user_details` WRITE;
/*!40000 ALTER TABLE `user_details` DISABLE KEYS */;
INSERT INTO `user_details` VALUES (1,'UACC029B77',NULL,'System','','Administrator','ntmawebcontrol@ntmacad.edu.ph','No','MC2026-00005','MD2026-00012','MP2026-00040','Active','No',NULL,'2026-10-18 18:41:13',NULL);
/*!40000 ALTER TABLE `user_details` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users_account`
--

DROP TABLE IF EXISTS `users_account`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users_account` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `uacc_reference` varchar(255) NOT NULL,
  `uacc_email` varchar(255) NOT NULL,
  `uacc_email_verified_at` timestamp NULL DEFAULT NULL,
  `uacc_password` varchar(255) NOT NULL,
  `uacc_type` enum('Super Admin','Admin','User') NOT NULL DEFAULT 'User',
  `uacc_status` enum('Active','Inactive','Locked','Suspended','Deleted') NOT NULL DEFAULT 'Active',
  `uacc_failed_login_attempts` int(10) unsigned NOT NULL DEFAULT 0,
  `uacc_locked_at` timestamp NULL DEFAULT NULL,
  `uacc_locked_until` timestamp NULL DEFAULT NULL,
  `uacc_last_login_at` timestamp NULL DEFAULT NULL,
  `uacc_last_login_ip` varchar(45) DEFAULT NULL,
  `uacc_password_changed_at` timestamp NULL DEFAULT NULL,
  `uacc_password_expires_at` timestamp NULL DEFAULT NULL,
  `uacc_two_factor_enabled` tinyint(1) NOT NULL DEFAULT 0,
  `uacc_two_factor_secret` text DEFAULT NULL,
  `uacc_two_factor_confirmed_at` timestamp NULL DEFAULT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_account_uacc_reference_unique` (`uacc_reference`),
  UNIQUE KEY `users_account_uacc_email_unique` (`uacc_email`),
  KEY `users_account_uacc_type_index` (`uacc_type`),
  KEY `users_account_uacc_status_index` (`uacc_status`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users_account`
--

LOCK TABLES `users_account` WRITE;
/*!40000 ALTER TABLE `users_account` DISABLE KEYS */;
INSERT INTO `users_account` VALUES (1,'UACC029B77','ntmawebcontrol@ntmacad.edu.ph','2026-09-24 09:33:24','$2y$12$WfA9vDUvcM4vXIsf9d.dX./p6FHLEbBHm/ePwlouzwI3qQxYYEn8m','Super Admin','Active',0,NULL,NULL,'2026-10-18 18:41:35','127.0.0.1',NULL,NULL,0,NULL,NULL,NULL,NULL,'2026-10-18 18:41:35');
/*!40000 ALTER TABLE `users_account` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-10-19 12:00:01
