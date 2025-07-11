/*M!999999\- enable the sandbox mode */ 
-- MariaDB dump 10.19-11.7.2-MariaDB, for osx10.19 (x86_64)
--
-- Host: localhost    Database: commodium_copia
-- ------------------------------------------------------
-- Server version	11.7.2-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*M!100616 SET @OLD_NOTE_VERBOSITY=@@NOTE_VERBOSITY, NOTE_VERBOSITY=0 */;

--
-- Table structure for table `cache`
--

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

--
-- Dumping data for table `cache`
--

LOCK TABLES `cache` WRITE;
/*!40000 ALTER TABLE `cache` DISABLE KEYS */;
INSERT INTO `cache` VALUES
('spatie.permission.cache','a:3:{s:5:\"alias\";a:4:{s:1:\"a\";s:2:\"id\";s:1:\"b\";s:4:\"name\";s:1:\"c\";s:10:\"guard_name\";s:1:\"r\";s:5:\"roles\";}s:11:\"permissions\";a:16:{i:0;a:4:{s:1:\"a\";i:1;s:1:\"b\";s:10:\"view users\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:3;}}i:1;a:4:{s:1:\"a\";i:2;s:1:\"b\";s:12:\"create users\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:2;a:4:{s:1:\"a\";i:3;s:1:\"b\";s:10:\"edit users\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:3;a:4:{s:1:\"a\";i:4;s:1:\"b\";s:12:\"delete users\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:4;a:4:{s:1:\"a\";i:5;s:1:\"b\";s:11:\"block users\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:5;a:4:{s:1:\"a\";i:6;s:1:\"b\";s:10:\"view roles\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:6;a:4:{s:1:\"a\";i:7;s:1:\"b\";s:12:\"assign roles\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:7;a:4:{s:1:\"a\";i:8;s:1:\"b\";s:15:\"manage settings\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:8;a:4:{s:1:\"a\";i:9;s:1:\"b\";s:11:\"manage news\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:9;a:4:{s:1:\"a\";i:10;s:1:\"b\";s:17:\"manage promotions\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:10;a:4:{s:1:\"a\";i:11;s:1:\"b\";s:14:\"manage banners\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:11;a:4:{s:1:\"a\";i:12;s:1:\"b\";s:8:\"view cms\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:12;a:4:{s:1:\"a\";i:13;s:1:\"b\";s:12:\"edit content\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:13;a:4:{s:1:\"a\";i:14;s:1:\"b\";s:17:\"manage categories\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:14;a:4:{s:1:\"a\";i:15;s:1:\"b\";s:20:\"manage subcategories\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:15;a:4:{s:1:\"a\";i:16;s:1:\"b\";s:15:\"manage products\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}}s:5:\"roles\";a:3:{i:0;a:3:{s:1:\"a\";i:1;s:1:\"b\";s:5:\"admin\";s:1:\"c\";s:3:\"web\";}i:1;a:3:{s:1:\"a\";i:3;s:1:\"b\";s:8:\"customer\";s:1:\"c\";s:3:\"web\";}i:2;a:3:{s:1:\"a\";i:2;s:1:\"b\";s:6:\"editor\";s:1:\"c\";s:3:\"web\";}}}',1752094672);
/*!40000 ALTER TABLE `cache` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cache_locks`
--

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

--
-- Dumping data for table `cache_locks`
--

LOCK TABLES `cache_locks` WRITE;
/*!40000 ALTER TABLE `cache_locks` DISABLE KEYS */;
/*!40000 ALTER TABLE `cache_locks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cart_items`
--

DROP TABLE IF EXISTS `cart_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `cart_items` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `product_id` bigint(20) unsigned NOT NULL,
  `quantity` int(11) NOT NULL,
  `price` decimal(8,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `cart_items_user_id_product_id_unique` (`user_id`,`product_id`),
  KEY `cart_items_product_id_foreign` (`product_id`),
  CONSTRAINT `cart_items_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  CONSTRAINT `cart_items_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cart_items`
--

LOCK TABLES `cart_items` WRITE;
/*!40000 ALTER TABLE `cart_items` DISABLE KEYS */;
INSERT INTO `cart_items` VALUES
(1,2,1,1,1.45,'2025-06-07 17:48:21','2025-06-07 18:07:53'),
(2,2,24,2,3.99,'2025-06-08 19:42:09','2025-06-08 19:42:09'),
(3,2,17,2,2.49,'2025-06-08 19:42:09','2025-06-08 19:42:09');
/*!40000 ALTER TABLE `cart_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `categories`
--

DROP TABLE IF EXISTS `categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `categories` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `banner_path` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `image_path` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `categories_name_unique` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `categories`
--

LOCK TABLES `categories` WRITE;
/*!40000 ALTER TABLE `categories` DISABLE KEYS */;
INSERT INTO `categories` VALUES
(1,'Groenten en Fruit','images/subcategories/banners/groentenFruitBanner.jpg','2025-02-04 16:23:18','2025-02-04 16:23:18',NULL,'Ontdek de lekkerste groenten en fruit van het seizoen.','images/categories/groenten-fruit.jpg'),
(2,'Bakkerij en Brood','images/subcategories/banners/bakkerijBroodBanner.jpg','2025-02-04 16:23:18','2025-02-04 16:23:18',NULL,'Vers brood en gebak, elke dag vers gebakken.','images/categories/bakkerij-brood.jpg'),
(3,'Zuivel en Eieren','images/subcategories/banners/zuivelEierenBanner.jpg','2025-02-04 16:23:18','2025-02-04 16:23:18',NULL,'Verse zuivel en eieren, rechtstreeks van de boerderij.','images/categories/zuivel-eieren.jpg');
/*!40000 ALTER TABLE `categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `delivery_slots`
--

DROP TABLE IF EXISTS `delivery_slots`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `delivery_slots` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `date` date NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `price` decimal(5,2) NOT NULL,
  `available_slots` int(11) NOT NULL,
  `current_available` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=221 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `delivery_slots`
--

LOCK TABLES `delivery_slots` WRITE;
/*!40000 ALTER TABLE `delivery_slots` DISABLE KEYS */;
INSERT INTO `delivery_slots` VALUES
(61,'2025-06-19','08:00:00','12:00:00',4.95,10,0,'2025-06-19 19:06:36','2025-06-19 19:06:36'),
(62,'2025-06-19','12:00:00','16:00:00',4.95,10,0,'2025-06-19 19:06:36','2025-06-19 19:06:36'),
(63,'2025-06-19','16:00:00','20:00:00',6.95,8,0,'2025-06-19 19:06:36','2025-06-19 19:06:36'),
(64,'2025-06-19','19:00:00','21:00:00',7.50,5,0,'2025-06-19 19:06:36','2025-06-19 19:06:36'),
(65,'2025-06-19','20:00:00','22:00:00',7.50,5,0,'2025-06-19 19:06:36','2025-06-19 19:06:36'),
(66,'2025-06-20','08:00:00','12:00:00',4.95,10,0,'2025-06-19 19:06:36','2025-06-19 19:06:36'),
(67,'2025-06-20','12:00:00','16:00:00',4.95,10,0,'2025-06-19 19:06:36','2025-06-19 19:06:36'),
(68,'2025-06-20','16:00:00','20:00:00',6.95,8,0,'2025-06-19 19:06:36','2025-06-19 19:06:36'),
(69,'2025-06-20','19:00:00','21:00:00',7.50,5,0,'2025-06-19 19:06:36','2025-06-19 19:06:36'),
(70,'2025-06-20','20:00:00','22:00:00',7.50,5,0,'2025-06-19 19:06:36','2025-06-19 19:06:36'),
(71,'2025-06-21','08:00:00','12:00:00',4.95,10,0,'2025-06-19 19:06:36','2025-06-19 19:06:36'),
(72,'2025-06-21','12:00:00','16:00:00',4.95,10,0,'2025-06-19 19:06:36','2025-06-19 19:06:36'),
(73,'2025-06-21','16:00:00','20:00:00',6.95,8,0,'2025-06-19 19:06:36','2025-06-19 19:06:36'),
(74,'2025-06-21','19:00:00','21:00:00',7.50,5,0,'2025-06-19 19:06:36','2025-06-19 19:06:36'),
(75,'2025-06-21','20:00:00','22:00:00',7.50,5,0,'2025-06-19 19:06:36','2025-06-19 19:06:36'),
(76,'2025-06-23','08:00:00','12:00:00',4.95,10,0,'2025-06-19 19:06:36','2025-06-19 19:06:36'),
(77,'2025-06-23','12:00:00','16:00:00',4.95,10,0,'2025-06-19 19:06:36','2025-06-19 19:06:36'),
(78,'2025-06-23','16:00:00','20:00:00',6.95,8,0,'2025-06-19 19:06:36','2025-06-19 19:06:36'),
(79,'2025-06-23','19:00:00','21:00:00',7.50,5,0,'2025-06-19 19:06:36','2025-06-19 19:06:36'),
(80,'2025-06-23','20:00:00','22:00:00',7.50,5,0,'2025-06-19 19:06:36','2025-06-19 19:06:36'),
(171,'2025-06-24','09:00:00','12:00:00',4.95,10,0,'2025-06-24 14:34:23','2025-06-24 14:34:23'),
(172,'2025-06-24','12:00:00','15:00:00',4.95,10,0,'2025-06-24 14:34:23','2025-06-24 14:34:23'),
(173,'2025-06-24','15:00:00','18:00:00',4.95,10,0,'2025-06-24 14:34:23','2025-06-24 14:34:23'),
(174,'2025-06-24','18:00:00','21:00:00',6.95,8,0,'2025-06-24 14:34:23','2025-06-24 14:34:23'),
(175,'2025-06-25','09:00:00','12:00:00',4.95,10,0,'2025-06-24 14:34:23','2025-06-24 14:34:23'),
(176,'2025-06-25','12:00:00','15:00:00',4.95,10,0,'2025-06-24 14:34:23','2025-06-24 14:34:23'),
(177,'2025-06-25','15:00:00','18:00:00',4.95,10,0,'2025-06-24 14:34:23','2025-06-24 14:34:23'),
(178,'2025-06-25','18:00:00','21:00:00',6.95,8,0,'2025-06-24 14:34:23','2025-06-24 14:34:23'),
(179,'2025-06-26','09:00:00','12:00:00',4.95,10,0,'2025-06-24 14:34:23','2025-06-24 14:34:23'),
(180,'2025-06-26','12:00:00','15:00:00',4.95,10,0,'2025-06-24 14:34:23','2025-06-24 14:34:23'),
(181,'2025-06-26','15:00:00','18:00:00',4.95,10,0,'2025-06-24 14:34:23','2025-06-24 14:34:23'),
(182,'2025-06-26','18:00:00','21:00:00',6.95,8,0,'2025-06-24 14:34:23','2025-06-24 14:34:23'),
(183,'2025-06-27','09:00:00','12:00:00',4.95,10,0,'2025-06-24 14:34:23','2025-06-24 14:34:23'),
(184,'2025-06-27','12:00:00','15:00:00',4.95,10,0,'2025-06-24 14:34:23','2025-06-24 14:34:23'),
(185,'2025-06-27','15:00:00','18:00:00',4.95,10,0,'2025-06-24 14:34:23','2025-06-24 14:34:23'),
(186,'2025-06-27','18:00:00','21:00:00',6.95,8,0,'2025-06-24 14:34:23','2025-06-24 14:34:23'),
(187,'2025-06-28','09:00:00','12:00:00',4.95,10,0,'2025-06-24 14:34:23','2025-06-24 14:34:23'),
(188,'2025-06-28','12:00:00','15:00:00',4.95,10,0,'2025-06-24 14:34:23','2025-06-24 14:34:23'),
(189,'2025-06-28','15:00:00','18:00:00',4.95,10,0,'2025-06-24 14:34:23','2025-06-24 14:34:23'),
(190,'2025-06-28','18:00:00','21:00:00',6.95,8,0,'2025-06-24 14:34:23','2025-06-24 14:34:23'),
(191,'2025-06-28','08:00:00','11:00:00',7.95,5,0,'2025-06-24 14:34:23','2025-06-24 14:34:23'),
(192,'2025-06-30','09:00:00','12:00:00',4.95,9,0,'2025-06-24 14:34:23','2025-06-30 20:48:03'),
(193,'2025-06-30','12:00:00','15:00:00',4.95,10,0,'2025-06-24 14:34:23','2025-06-24 14:34:23'),
(194,'2025-06-30','15:00:00','18:00:00',4.95,10,0,'2025-06-24 14:34:23','2025-06-24 14:34:23'),
(195,'2025-06-30','18:00:00','21:00:00',6.95,8,0,'2025-06-24 14:34:23','2025-06-24 14:34:23'),
(196,'2025-07-01','09:00:00','12:00:00',4.95,10,0,'2025-06-24 14:34:23','2025-06-24 14:34:23'),
(197,'2025-07-01','12:00:00','15:00:00',4.95,10,0,'2025-06-24 14:34:23','2025-06-24 14:34:23'),
(198,'2025-07-01','15:00:00','18:00:00',4.95,10,0,'2025-06-24 14:34:23','2025-06-24 14:34:23'),
(199,'2025-07-01','18:00:00','21:00:00',6.95,8,0,'2025-06-24 14:34:23','2025-06-24 14:34:23'),
(200,'2025-07-02','09:00:00','12:00:00',4.95,10,0,'2025-06-24 14:34:23','2025-06-24 14:34:23'),
(201,'2025-07-02','12:00:00','15:00:00',4.95,10,0,'2025-06-24 14:34:23','2025-06-24 14:34:23'),
(202,'2025-07-02','15:00:00','18:00:00',4.95,10,0,'2025-06-24 14:34:23','2025-06-24 14:34:23'),
(203,'2025-07-02','18:00:00','21:00:00',6.95,8,0,'2025-06-24 14:34:23','2025-06-24 14:34:23'),
(204,'2025-07-03','09:00:00','12:00:00',4.95,10,0,'2025-06-24 14:34:23','2025-06-24 14:34:23'),
(205,'2025-07-03','12:00:00','15:00:00',4.95,10,0,'2025-06-24 14:34:23','2025-06-24 14:34:23'),
(206,'2025-07-03','15:00:00','18:00:00',4.95,10,0,'2025-06-24 14:34:23','2025-06-24 14:34:23'),
(207,'2025-07-03','18:00:00','21:00:00',6.95,8,0,'2025-06-24 14:34:23','2025-06-24 14:34:23'),
(208,'2025-07-04','09:00:00','12:00:00',4.95,10,0,'2025-06-24 14:34:23','2025-06-24 14:34:23'),
(209,'2025-07-04','12:00:00','15:00:00',4.95,10,0,'2025-06-24 14:34:23','2025-06-24 14:34:23'),
(210,'2025-07-04','15:00:00','18:00:00',4.95,10,0,'2025-06-24 14:34:23','2025-06-24 14:34:23'),
(211,'2025-07-04','18:00:00','21:00:00',6.95,8,0,'2025-06-24 14:34:23','2025-06-24 14:34:23'),
(212,'2025-07-05','09:00:00','12:00:00',4.95,10,0,'2025-06-24 14:34:23','2025-06-24 14:34:23'),
(213,'2025-07-05','12:00:00','15:00:00',4.95,10,0,'2025-06-24 14:34:23','2025-06-24 14:34:23'),
(214,'2025-07-05','15:00:00','18:00:00',4.95,10,0,'2025-06-24 14:34:23','2025-06-24 14:34:23'),
(215,'2025-07-05','18:00:00','21:00:00',6.95,8,0,'2025-06-24 14:34:23','2025-06-24 14:34:23'),
(216,'2025-07-05','08:00:00','11:00:00',7.95,5,0,'2025-06-24 14:34:23','2025-06-24 14:34:23'),
(217,'2025-07-07','09:00:00','12:00:00',4.95,10,0,'2025-06-24 14:34:23','2025-06-24 14:34:23'),
(218,'2025-07-07','12:00:00','15:00:00',4.95,10,0,'2025-06-24 14:34:23','2025-06-24 14:34:23'),
(219,'2025-07-07','15:00:00','18:00:00',4.95,10,0,'2025-06-24 14:34:23','2025-06-24 14:34:23'),
(220,'2025-07-07','18:00:00','21:00:00',6.95,8,0,'2025-06-24 14:34:23','2025-06-24 14:34:23');
/*!40000 ALTER TABLE `delivery_slots` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `failed_jobs`
--

DROP TABLE IF EXISTS `failed_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
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
/*!40101 SET character_set_client = utf8mb4 */;
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
-- Table structure for table `migrations`
--

DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `migrations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=27 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `migrations`
--

LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
INSERT INTO `migrations` VALUES
(1,'0001_01_01_000000_create_users_table',1),
(2,'0001_01_01_000001_create_cache_table',1),
(3,'0001_01_01_000002_create_jobs_table',1),
(4,'2024_12_25_172333_create_categories_table',1),
(5,'2024_12_25_173125_create_subcategories_table',1),
(6,'2024_12_25_174650_create_products_table',1),
(7,'2025_01_04_112516_create_permission_tables',1),
(8,'2025_01_08_163328_add_status_to_users',1),
(9,'2025_01_25_124550_create_promotions_table',1),
(10,'2025_01_25_124620_create_news_articles_table',1),
(11,'2025_02_01_215118_cleanup_duplicate_categories_and_add_unique_constraint',1),
(12,'2025_02_01_231248_cleanup_duplicate_subcategories_and_add_unique_constraint',1),
(13,'2025_02_02_112653_add_image_path_to_categories',1),
(14,'2025_02_04_150205_create_delivery_slots_table',1),
(15,'2025_02_04_150228_create_orders_table',1),
(16,'2025_02_04_150246_create_order_items_table',1),
(17,'2025_05_30_183757_enhance_orders_table',2),
(18,'2025_05_30_183959_enhance_order_items_table',2),
(19,'2025_05_30_184014_enhance_products_table',2),
(20,'2025_06_05_151152_create_cart_items_table',3),
(21,'2025_06_06_211118_modify_stock_quantity_column_on_products_table',4),
(22,'2025_06_12_184707_create_user_addresses_table',5),
(23,'2025_06_23_192442_add_delivery_fee_to_orders_table',6),
(24,'2025_06_24_163029_add_house_number_to_user_addresses_table',7),
(25,'2025_06_30_190512_enhance_orders_and_delivery_slots_for_checkout',8),
(26,'2025_06_30_225941_fix_orders_status_enum',9);
/*!40000 ALTER TABLE `migrations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `model_has_permissions`
--

DROP TABLE IF EXISTS `model_has_permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `model_has_permissions` (
  `permission_id` bigint(20) unsigned NOT NULL,
  `model_type` varchar(255) NOT NULL,
  `model_id` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`permission_id`,`model_id`,`model_type`),
  KEY `model_has_permissions_model_id_model_type_index` (`model_id`,`model_type`),
  CONSTRAINT `model_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `model_has_permissions`
--

LOCK TABLES `model_has_permissions` WRITE;
/*!40000 ALTER TABLE `model_has_permissions` DISABLE KEYS */;
/*!40000 ALTER TABLE `model_has_permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `model_has_roles`
--

DROP TABLE IF EXISTS `model_has_roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `model_has_roles` (
  `role_id` bigint(20) unsigned NOT NULL,
  `model_type` varchar(255) NOT NULL,
  `model_id` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`role_id`,`model_id`,`model_type`),
  KEY `model_has_roles_model_id_model_type_index` (`model_id`,`model_type`),
  CONSTRAINT `model_has_roles_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `model_has_roles`
--

LOCK TABLES `model_has_roles` WRITE;
/*!40000 ALTER TABLE `model_has_roles` DISABLE KEYS */;
INSERT INTO `model_has_roles` VALUES
(1,'App\\Models\\User',1),
(3,'App\\Models\\User',1),
(2,'App\\Models\\User',2),
(3,'App\\Models\\User',2);
/*!40000 ALTER TABLE `model_has_roles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `news_articles`
--

DROP TABLE IF EXISTS `news_articles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `news_articles` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `image_path` varchar(255) DEFAULT NULL,
  `is_published` tinyint(1) NOT NULL DEFAULT 0,
  `published_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `news_articles`
--

LOCK TABLES `news_articles` WRITE;
/*!40000 ALTER TABLE `news_articles` DISABLE KEYS */;
/*!40000 ALTER TABLE `news_articles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `order_items`
--

DROP TABLE IF EXISTS `order_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `order_items` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `order_id` bigint(20) unsigned NOT NULL,
  `product_id` bigint(20) unsigned NOT NULL,
  `quantity` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `product_name` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `order_items_order_id_foreign` (`order_id`),
  KEY `order_items_product_id_foreign` (`product_id`),
  CONSTRAINT `order_items_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  CONSTRAINT `order_items_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `order_items`
--

LOCK TABLES `order_items` WRITE;
/*!40000 ALTER TABLE `order_items` DISABLE KEYS */;
/*!40000 ALTER TABLE `order_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `orders`
--

DROP TABLE IF EXISTS `orders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `orders` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `total` decimal(10,2) NOT NULL,
  `payment_method` varchar(255) DEFAULT NULL,
  `payment_status` enum('pending','processing','completed','failed','cancelled') NOT NULL DEFAULT 'pending',
  `status` enum('pending','confirmed','processing','out_for_delivery','delivered','cancelled') NOT NULL DEFAULT 'pending',
  `subtotal` decimal(10,2) NOT NULL COMMENT 'Subtotal without delivery fee',
  `delivery_fee` decimal(8,2) NOT NULL DEFAULT 0.00 COMMENT 'Delivery fee amount',
  `delivery_slot_id` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `delivery_address` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`delivery_address`)),
  `order_notes` text DEFAULT NULL,
  `order_date` timestamp NULL DEFAULT NULL,
  `order_number` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `orders_order_number_unique` (`order_number`),
  KEY `orders_user_id_foreign` (`user_id`),
  KEY `orders_delivery_slot_id_foreign` (`delivery_slot_id`),
  KEY `orders_status_index` (`status`),
  CONSTRAINT `orders_delivery_slot_id_foreign` FOREIGN KEY (`delivery_slot_id`) REFERENCES `delivery_slots` (`id`),
  CONSTRAINT `orders_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `orders`
--

LOCK TABLES `orders` WRITE;
/*!40000 ALTER TABLE `orders` DISABLE KEYS */;
/*!40000 ALTER TABLE `orders` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `password_reset_tokens`
--

DROP TABLE IF EXISTS `password_reset_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
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
INSERT INTO `password_reset_tokens` VALUES
('fvreede@gmail.com','$2y$12$htJ1G8Bn0OleaFaG2rNPrOHEpdXxXSQx.A7UVCe3G.tetq49HKywW','2025-05-28 10:15:04');
/*!40000 ALTER TABLE `password_reset_tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `permissions`
--

DROP TABLE IF EXISTS `permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `permissions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `guard_name` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `permissions_name_guard_name_unique` (`name`,`guard_name`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `permissions`
--

LOCK TABLES `permissions` WRITE;
/*!40000 ALTER TABLE `permissions` DISABLE KEYS */;
INSERT INTO `permissions` VALUES
(1,'view users','web','2025-02-04 16:23:18','2025-02-04 16:23:18'),
(2,'create users','web','2025-02-04 16:23:18','2025-02-04 16:23:18'),
(3,'edit users','web','2025-02-04 16:23:18','2025-02-04 16:23:18'),
(4,'delete users','web','2025-02-04 16:23:18','2025-02-04 16:23:18'),
(5,'block users','web','2025-02-04 16:23:18','2025-02-04 16:23:18'),
(6,'view roles','web','2025-02-04 16:23:18','2025-02-04 16:23:18'),
(7,'assign roles','web','2025-02-04 16:23:18','2025-02-04 16:23:18'),
(8,'manage settings','web','2025-02-04 16:23:18','2025-02-04 16:23:18'),
(9,'manage news','web','2025-02-04 16:23:18','2025-02-04 16:23:18'),
(10,'manage promotions','web','2025-02-04 16:23:18','2025-02-04 16:23:18'),
(11,'manage banners','web','2025-02-04 16:23:18','2025-02-04 16:23:18'),
(12,'view cms','web','2025-02-04 16:23:18','2025-02-04 16:23:18'),
(13,'edit content','web','2025-02-04 16:23:18','2025-02-04 16:23:18'),
(14,'manage categories','web','2025-02-04 16:23:18','2025-02-04 16:23:18'),
(15,'manage subcategories','web','2025-02-04 16:23:18','2025-02-04 16:23:18'),
(16,'manage products','web','2025-02-04 16:23:18','2025-02-04 16:23:18');
/*!40000 ALTER TABLE `permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `products`
--

DROP TABLE IF EXISTS `products`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `products` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `subcategory_id` bigint(20) unsigned NOT NULL,
  `name` varchar(255) NOT NULL,
  `short_description` text NOT NULL,
  `full_description` text NOT NULL,
  `price` decimal(8,2) NOT NULL,
  `image_path` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `stock_quantity` int(11) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  KEY `products_subcategory_id_foreign` (`subcategory_id`),
  CONSTRAINT `products_subcategory_id_foreign` FOREIGN KEY (`subcategory_id`) REFERENCES `subcategories` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=32 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `products`
--

LOCK TABLES `products` WRITE;
/*!40000 ALTER TABLE `products` DISABLE KEYS */;
INSERT INTO `products` VALUES
(1,1,'Wortelen','Vers van het land, knapperige wortelen boordevol vitamines','Deze wortelen, ook wel winterpeen genoemd, zijn stevig en knapperig. De fel oranje kleur en zoete smaak maken ze perfect voor een gezonde snack of als aanvulling in diverse gerechten. Je kunt ze rauw eten, koken, roerbakken of zelfs pureren. Voor gebruik, spoel ze af en schil ze. Of je ze nu in stukjes snijdt voor een dipsaus of kookt tot ze zacht zijn, deze wortelen passen bij elke maaltijd. Ze bevatten veel vitamines die goed zijn voor je gezondheid.',1.45,'images/products/groenten_fruit/groenten/wortelen.jpg','2025-02-04 16:23:18','2025-06-07 17:48:14',NULL,99,1),
(2,1,'Biologische pompoen','Heerlijke biologische pompoen, perfect voor soep, stoofpotjes en herfstgerechten.','Deze biologische pompoen is niet alleen heerlijk maar ook supergezond! Het oranje vruchtvlees zit vol vezels en betacaroteen, wat goed is voor je ogen en huid. De pompoen kan in allerlei gerechten worden gebruikt, van soep tot geroosterde schijfjes in de oven. Met zijn stevige schil is hij perfect om zowel te bakken als te koken. Verwerk het vruchtvlees in stoofpotjes of maak er een heerlijke herfstsoep van. Bewaar hele pompoenen op een koele plek, en geniet wekenlang van hun smaak.',2.99,'images/products/groenten_fruit/groenten/biologischePompoen.jpg','2025-02-04 16:23:18','2025-06-30 20:48:03',NULL,66,1),
(3,1,'Knolselderij','Heerlijke, stevige knolselderij met een rijke smaak','Knolselderij is een veelzijdige groente met een unieke smaak en een stevige textuur. De knol wordt vaak gebruikt in soepen, stoofschotels of als basis voor purees. De bladeren zijn trouwens ook eetbaar en kunnen rauw aan salades worden toegevoegd. Eenmaal gekookt of gebakken, krijg je een zachte, licht nootachtige smaak die perfect past bij andere herfstgroenten. Deze knol is het geheime wapen voor een diepe, hartige smaak in jouw gerechten.',1.49,'images/products/groenten_fruit/groenten/knolselderij.jpg','2025-02-04 16:23:18','2025-06-08 15:25:02',NULL,99,1),
(4,1,'Pastinaak','Zoete, aromatische pastinaak, perfect voor puree of ovengerechten','Pastinaak heeft een licht zoete, anijsachtige smaak die goed past in herfst- en wintergerechten. Deze crème-witte wortel kan worden geroosterd, gekookt of gestampt voor een romige puree. Het lekkerste is om ze te oogsten na de eerste vorst, want dat maakt de smaak nog zoeter. Door zijn stevige penwortelstructuur is pastinaak niet alleen lekker, maar ook voedzaam en een ideale aanvulling op een winterse maaltijd.',1.99,'images/products/groenten_fruit/groenten/pastinaak.jpg','2025-02-04 16:23:18','2025-06-08 15:25:20',NULL,99,1),
(5,1,'Zoete aardappels','Verfrissende komkommer, perfect voor salades of snacks','Zoete aardappels, ook wel bataat genoemd, hebben een zoete, aardse smaak en een zachte, romige textuur wanneer ze gekookt of gebakken zijn. Deze veelzijdige groente is rijk aan vitamines, vooral vitamine A, en past perfect bij allerlei gerechten. Gebruik ze als frietjes, in een salade of puree, of bak ze tot ze goudbruin en knapperig zijn. Ze voegen een heerlijk zoete toets toe aan je maaltijd en zijn ook nog eens gezond!',1.49,'images/products/groenten_fruit/groenten/zoeteAardappels.jpg','2025-02-04 16:23:18','2025-06-08 15:25:32',NULL,99,1),
(6,2,'Granny Smith appels','Heerlijke frisse en knapperige appels, perfect voor een gezonde snack of in een salade.','Deze Granny Smith appels staan bekend om hun knapperige, frisse textuur en pittige smaak. Ze zijn niet alleen heerlijk als snack, maar ook perfect voor salades of om mee te bakken. Of je nu houdt van een gezonde hap tussendoor of een smaakvolle toevoeging aan je appeltaart zoekt, deze groene appels zijn altijd een verfrissende keuze.',2.99,'images/products/groenten_fruit/fruit/grannySmithAppels.jpg','2025-02-04 16:23:18','2025-06-08 15:25:45',NULL,99,1),
(7,2,'Handsinaasappels','Zonnig en sappig, deze Spaanse sinaasappels brengen de Mediterrane zon op je bord.','Met hun sappige vruchtvlees en frisse, zoete smaak zijn deze Spaanse sinaasappels een must voor elke fruitliefhebber. Ze brengen de zonnige Mediterrane sfeer direct op je bord. Heerlijk als snack, in een fruitsalade of als basis voor een vers glas sinaasappelsap. Een bron van vitamine C, perfect om je dag gezond te beginnen!',3.29,'images/products/groenten_fruit/fruit/handSinaasappels.jpg','2025-02-04 16:23:18','2025-06-08 15:26:03',NULL,99,1),
(8,2,'Chiquita bananen family pack','Zoete en voedzame bananen, ideaal voor smoothies of als tussendoortje voor de hele familie.','Dit family pack biedt voldoende bananen voor het hele gezin. Elke banaan is zorgvuldig geselecteerd op rijpheid en smaak, zodat je altijd kunt genieten van hun zachte textuur en zoete smaak. Bananen zijn rijk aan kalium, wat goed is voor je spieren en hart. Ze zijn niet alleen een gezonde snack, maar ook ideaal voor smoothies, desserts of als snelle energieboost!',2.19,'images/products/groenten_fruit/fruit/chiquitaBananen.jpeg','2025-02-04 16:23:18','2025-06-08 15:26:30',NULL,99,1),
(9,3,'Wit brood','Versgebakken wit brood met een knapperige korst','Dit wit brood is versgebakken met een knapperige korst en een zachte, luchtige binnenkant. Het is ideaal voor een stevig ontbijt of een lekkere lunch. Belegd met zoet of hartig, het blijft een klassieker op de eettafel. Dit brood wordt gebakken bij een temperatuur van 200-230 graden Celsius, zodat elke hap knapperig en smaakvol is.',1.99,'images/products/bakkerij_brood/brood/witBrood.jpg','2025-02-04 16:23:18','2025-06-08 15:26:47',NULL,99,1),
(10,3,'Bruin brood','Gezond bruin brood, rijk aan vezels','Ons bruine brood zit boordevol vezels en heeft een heerlijke, stevige korst. Het is perfect voor wie houdt van een gezond en voedzaam brood bij het ontbijt of de lunch. Dit brood wordt met zorg gebakken voor een volle, rijke smaak die je dag voedzaam en smakelijk maakt.',2.99,'images/products/bakkerij_brood/brood/bruinBrood.jpg','2025-02-04 16:23:18','2025-06-30 20:48:03',NULL,53,1),
(11,3,'Croissant','Luchtige croissants, warm en vers uit de oven','Deze luchtige croissants zijn versgebakken en smelten in je mond. Ideaal voor bij het ontbijt of als tussendoortje. Hun zachte, boterachtige smaak in combinatie met de knapperige buitenkant zorgt voor een authentieke Franse eetervaring. Lekker met jam of gewoon zo!',1.49,'images/products/bakkerij_brood/brood/croissants.jpg','2025-02-04 16:23:18','2025-06-30 20:48:03',NULL,98,1),
(12,4,'Red Velvet Muffins','Luchtige muffins met de klassieke red velvet smaak, afgewerkt met een romige topping','Deze muffins brengen de luxe van red velvet naar jouw bord. Ze zijn heerlijk luchtig en afgewerkt met een romige topping van roomkaas. Met een subtiele cacaosmaak en hun dieprode kleur zijn ze perfect voor een speciale traktatie. Of je ze nu serveert bij de koffie of als dessert, deze muffins zijn altijd een hit!',1.69,'images/products/bakkerij_brood/gebak/redVelvetMuffin.jpg','2025-02-04 16:23:18','2025-06-30 20:48:03',NULL,87,1),
(13,4,'Luxe Blueberry Muffins','Zachte muffins boordevol sappige blauwe bessen, een perfecte zoete traktatie','Boordevol sappige blauwe bessen zijn deze muffins een absolute verwennerij. Ze zijn zacht en luchtig, en de bessen zorgen voor een heerlijke frisse smaakexplosie bij elke hap. Perfect als tussendoortje of als zoete traktatie bij een kop koffie of thee.',1.69,'images/products/bakkerij_brood/gebak/luxeBlueberryMuffin.jpg','2025-02-04 16:23:18','2025-06-08 15:27:54',NULL,99,1),
(14,4,'Espresso Brownies','Chocoladerijke brownies met een shot espresso voor een intense herfstboost','Voor de echte chocoholics en koffieliefhebbers zijn deze espresso brownies een droom. Ze combineren de rijke smaak van chocolade met de intensiteit van espresso. Elke hap is vol van smaak en smelt heerlijk weg in je mond. Deze brownies zijn de ultieme zoete oppepper!',3.99,'images/products/bakkerij_brood/gebak/espressoBrownies.jpg','2025-02-04 16:23:18','2025-06-08 15:28:11',NULL,99,1),
(15,5,'Campina volle melk','Romige, volle melk, perfect voor een gezond ontbijt of koffie','Campina volle melk is een romige, volle melk die perfect past bij een gezond ontbijt of in de koffie. Het zit boordevol calcium en essentiële voedingsstoffen die bijdragen aan sterke botten en een evenwichtige levensstijl. Geniet dagelijks van de rijke, romige smaak.',1.99,'images/products/zuivel_eieren/melk/campinaVolleMelk.jpg','2025-02-04 16:23:18','2025-06-08 15:28:27',NULL,99,1),
(16,5,'Campina halfvolle melk','Frisse, lichte melk, een goede balans tussen romig en mager','Campina halfvolle melk biedt een lichte en verfrissende smaak die de perfecte balans vormt tussen romigheid en minder vet. Rijk aan calcium en andere belangrijke voedingsstoffen, deze melk is ideaal voor iedereen die gezond wil leven zonder in te leveren op smaak.',1.79,'images/products/zuivel_eieren/melk/campinaHalfvolleMelk.jpg','2025-02-04 16:23:18','2025-06-08 15:29:05',NULL,99,1),
(17,5,'Campina volle yoghurt','Rijke, romige yoghurt, ideaal voor smoothies of ontbijt','Campina volle yoghurt is een romige en volle yoghurt, ideaal voor je ontbijt, tussendoor of in smoothies. Deze yoghurt is rijk aan calcium en helpt bij het ondersteunen van een gezonde spijsvertering en een evenwichtige levensstijl.',2.49,'images/products/zuivel_eieren/melk/campinaVolleYogurt.jpg','2025-02-04 16:23:18','2025-06-30 20:48:03',NULL,83,1),
(18,5,'Campina halfvolle yoghurt','Lichte, frisse yoghurt, perfect als tussendoortje of dessert','Campina halfvolle yoghurt is een lichte, frisse yoghurt die perfect past bij een tussendoortje of dessert. Het bevat veel calcium en voedingsstoffen die bijdragen aan een gezonde levensstijl, zonder dat het zwaar op de maag ligt.',2.29,'images/products/zuivel_eieren/melk/campinaHalfvolleYogurt.jpg','2025-02-04 16:23:18','2025-06-30 20:48:03',NULL,98,1),
(19,5,'Campina magere yoghurt','Verfrissende, magere yoghurt met een volle smaak zonder schuldgevoel','Campina magere yoghurt biedt een verfrissende smaak zonder de schuldgevoelens van vet. Rijk aan calcium en essentiële voedingsstoffen, deze yoghurt helpt je gezond te blijven terwijl je geniet van de volle smaak van yoghurt.',1.99,'images/products/zuivel_eieren/melk/campinaMagereYogurt.jpg','2025-02-04 16:23:18','2025-06-08 15:29:58',NULL,99,1),
(20,5,'Campina Griekse Stijl 5%','Romige Griekse yoghurt, perfect voor gezonde snacks','Campina Griekse yoghurt met 5% vet biedt de perfecte balans tussen romigheid en gezondheid. Deze yoghurt is rijk aan calcium en is ideaal als tussendoortje, voor in salades, of als basis voor smoothies.',2.99,'images/products/zuivel_eieren/melk/campinaGriekseYogurt5.jpg','2025-02-04 16:23:18','2025-06-08 15:30:12',NULL,99,1),
(21,5,'Campina Griekse Stijl 10%','Extra romige Griekse yoghurt, rijk van smaak','Deze extra romige Griekse yoghurt van Campina heeft een volle, rijke smaak. Met 10% vet is het de perfecte keuze voor liefhebbers van een meer indulgente yoghurt die toch voedzaam blijft, boordevol calcium en essentiële voedingsstoffen.',3.49,'images/products/zuivel_eieren/melk/campinaGriekseYogurt10.jpg','2025-02-04 16:23:18','2025-06-08 15:30:31',NULL,99,1),
(22,6,'Beemster Jong','Zachte, romige Gouda kaas met 6 plakken.','Beemster Jong is een zachte, romige Gouda kaas die rijk is aan eiwitten en calcium. De milde smaak en romige textuur maken het een perfecte keuze voor op brood of als snack. Deze kaas draagt bij aan een gezonde levensstijl met zijn rijke voedingswaarde.',1.99,'images/products/zuivel_eieren/kaas/beemsterJongKaas.jpg','2025-02-04 16:23:18','2025-06-08 15:30:52',NULL,99,1),
(23,6,'Beemster Extra Oud','Volle oude Gouda kaas met 5 plakken.','Beemster Extra Oud is een krachtige Gouda kaas met een uitgesproken smaak. Het biedt een romige textuur en is rijk aan eiwitten en calcium, wat het een voedzame keuze maakt voor elk moment van de dag.',2.49,'images/products/zuivel_eieren/kaas/beemsterExtraOudKaas.jpg','2025-02-04 16:23:18','2025-06-08 15:31:41',NULL,99,1),
(24,6,'Old Amsterdam','Pittige oude kaas met 8 plakken.','Old Amsterdam is een pittige, oude kaas die intens van smaak is. Deze kaas heeft een romige textuur en is een bron van hoogwaardige eiwitten en calcium, wat bijdraagt aan een gezonde levensstijl. Ideaal als snack of in gerechten.',3.99,'images/products/zuivel_eieren/kaas/oldAmsterdamKaas.jpg','2025-02-04 16:23:18','2025-06-08 15:31:13',NULL,99,1),
(25,6,'Salakis Feta','Ziltige Griekse feta kaas.','Salakis Feta is een ziltige, Griekse feta kaas met een romige textuur en pittige smaak. Deze kaas is een rijke bron van eiwitten en calcium, perfect voor salades, wraps of gewoon als tussendoortje.',2.99,'images/products/zuivel_eieren/kaas/salakisFetaKaas.jpg','2025-02-04 16:23:18','2025-06-08 15:31:27',NULL,99,1),
(26,6,'Président Le Brie','Romige zachte brie','Président Le Brie is een zachte, romige kaas met een delicaat karakter. Deze brie smelt in de mond en is ideaal voor op brood, crackers of als onderdeel van een kaasplankje. Rijk aan eiwitten en calcium, een voedzame en smaakvolle keuze.',1.49,'images/products/zuivel_eieren/kaas/presidentLeBrieKaas.jpg','2025-02-04 16:23:18','2025-06-08 15:32:07',NULL,99,1),
(27,7,'Scharreleieren','Vers geplukte scharreleieren van blije kippen','Scharreleieren komen van kippen die vrij kunnen rondlopen, wat bijdraagt aan hun welzijn en de kwaliteit van de eieren. Deze eieren zijn rijk aan eiwitten en voedingsstoffen die essentieel zijn voor een gebalanceerd dieet. Perfect voor koken, bakken of als gezond tussendoortje.',1.99,'images/products/zuivel_eieren/eieren/scharreleieren.jpg','2025-02-04 16:23:18','2025-06-08 15:32:25',NULL,99,1),
(28,1,'Ananas','KKKKK','Koefoefjvj',122.00,'images/products/groenten_en_fruit/fruit/ukwvr9IHC6hRAZgpUnTRKD9hAi2HOgqPrZX9hMyO.jpg','2025-05-22 18:22:27','2025-05-22 18:43:19','2025-05-22 18:43:19',0,1),
(29,2,'Ananas','Ajiejiej','fjiwjfiej',2222.00,'images/products/groenten_en_fruit/fruit/7AVlRnTGQiSW4OnmKmPUoWI35eW6DE7J6WGIt5VF.jpg','2025-05-22 18:55:22','2025-05-23 11:33:12','2025-05-23 11:33:12',0,1),
(30,2,'Ananas','Lekkere ananas','Gaaf man',122.00,'images/products/groenten_en_fruit/fruit/4YrNUdelrmmGyYglrb2i2INyYoyoFeoZXTLA8orC.jpg','2025-05-23 16:33:22','2025-05-25 07:21:56','2025-05-25 07:21:56',0,1),
(31,1,'test','test','test',21.00,'images/products/groenten_en_fruit/groenten/BmCKMHt4rILEooCI7DGkBhWTcdKQxlTmgTyo7hjM.jpg','2025-05-27 06:17:01','2025-05-27 06:17:50','2025-05-27 06:17:50',0,1);
/*!40000 ALTER TABLE `products` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `promotion_products`
--

DROP TABLE IF EXISTS `promotion_products`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `promotion_products` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `promotion_id` bigint(20) unsigned NOT NULL,
  `product_id` bigint(20) unsigned NOT NULL,
  `discount_price` decimal(8,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `promotion_products_promotion_id_foreign` (`promotion_id`),
  KEY `promotion_products_product_id_foreign` (`product_id`),
  CONSTRAINT `promotion_products_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  CONSTRAINT `promotion_products_promotion_id_foreign` FOREIGN KEY (`promotion_id`) REFERENCES `promotions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `promotion_products`
--

LOCK TABLES `promotion_products` WRITE;
/*!40000 ALTER TABLE `promotion_products` DISABLE KEYS */;
INSERT INTO `promotion_products` VALUES
(1,1,1,2.00,'2025-07-03 15:27:58','2025-07-03 15:27:58'),
(2,1,3,2.00,'2025-07-03 15:27:58','2025-07-03 15:27:58'),
(3,1,2,2.00,'2025-07-03 15:27:58','2025-07-03 15:27:58');
/*!40000 ALTER TABLE `promotion_products` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `promotions`
--

DROP TABLE IF EXISTS `promotions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `promotions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `cta_text` varchar(255) NOT NULL,
  `image_path` varchar(255) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 0,
  `valid_until` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `promotions`
--

LOCK TABLES `promotions` WRITE;
/*!40000 ALTER TABLE `promotions` DISABLE KEYS */;
INSERT INTO `promotions` VALUES
(1,'test','test','test','images/promotions/WZ2rWCH7gaas9UCp2YLI6vPdV4FbHL8LFi4BaFCz.jpg',1,'2000-10-19 22:00:00',NULL,'2025-07-03 15:27:58','2025-07-03 15:27:58');
/*!40000 ALTER TABLE `promotions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `role_has_permissions`
--

DROP TABLE IF EXISTS `role_has_permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `role_has_permissions` (
  `permission_id` bigint(20) unsigned NOT NULL,
  `role_id` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`permission_id`,`role_id`),
  KEY `role_has_permissions_role_id_foreign` (`role_id`),
  CONSTRAINT `role_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `role_has_permissions_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `role_has_permissions`
--

LOCK TABLES `role_has_permissions` WRITE;
/*!40000 ALTER TABLE `role_has_permissions` DISABLE KEYS */;
INSERT INTO `role_has_permissions` VALUES
(1,1),
(2,1),
(3,1),
(4,1),
(5,1),
(6,1),
(7,1),
(8,1),
(9,1),
(10,1),
(11,1),
(12,1),
(13,1),
(14,1),
(15,1),
(16,1),
(9,2),
(10,2),
(11,2),
(12,2),
(13,2),
(14,2),
(15,2),
(16,2),
(1,3);
/*!40000 ALTER TABLE `role_has_permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `roles`
--

DROP TABLE IF EXISTS `roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `roles` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `guard_name` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `roles_name_guard_name_unique` (`name`,`guard_name`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `roles`
--

LOCK TABLES `roles` WRITE;
/*!40000 ALTER TABLE `roles` DISABLE KEYS */;
INSERT INTO `roles` VALUES
(1,'admin','web','2025-02-04 16:23:18','2025-02-04 16:23:18'),
(2,'editor','web','2025-02-04 16:23:18','2025-02-04 16:23:18'),
(3,'customer','web','2025-02-04 16:23:18','2025-02-04 16:23:18');
/*!40000 ALTER TABLE `roles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sessions`
--

DROP TABLE IF EXISTS `sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sessions`
--

LOCK TABLES `sessions` WRITE;
/*!40000 ALTER TABLE `sessions` DISABLE KEYS */;
INSERT INTO `sessions` VALUES
('AcAkutu6WebF5Qc1gUWxEXhNKy3HurzanZR1ya9h',NULL,'127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoienRUeVpJMFZ6TFJiS2dZUkdvZm9TMzVUOU5yZXVkOHI0azF2eDM4NCI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mjc6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMC9sb2dpbiI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=',1752187495),
('bKwfYZZgNNBFqAwxvpjGx6nMD3mLbXYabvcqjeu0',NULL,'127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_11_1) AppleWebKit/601.2.4 (KHTML, like Gecko) Version/9.0.1 Safari/601.2.4 facebookexternalhit/1.1 Facebot Twitterbot/1.0','YTozOntzOjY6Il90b2tlbiI7czo0MDoicUpLdHZ3RVVabXV1akVLZmJqWlBxY1pFaGtSWWIzdlFNNXp6N1JwTSI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MjE6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMCI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=',1752266465),
('cgDV2Jc92qIhYmHUrDpwBnDibNEJQ7lWCe0TQEll',NULL,'127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiUXZrZnNaZ1I5azRvSjR5UGVIMG1MWmY1RWQ2dnFZWnY4c2VPU291YiI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mjc6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMC9sb2dpbiI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=',1752084182),
('DCmAWCma86JjlqkYpJrORjyluWZnirhJuQNXpHLT',NULL,'127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiVEc2dUFVZWppcEFlbzZQVlM4WGVUWU9tU0tvR0NYVzFidmlkMW5sbiI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mjc6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMC9sb2dpbiI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=',1752227523),
('dJ0pYzQvQEPo0JsNvVwFFVlf6ndBQY6HeuzNsA4J',NULL,'127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoieWFaU04yRVExSm95enZJRGxYS2FoZ1BSS003QXBJbzcwNmxQcEZFQSI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mjc6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMC9sb2dpbiI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=',1752270604),
('fcYBh5X3Rtm0Q5xQ6tTP30Za5zPzfPSJ1iDGzVFE',NULL,'127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','YTo0OntzOjY6Il90b2tlbiI7czo0MDoiUXJEdE9BU25FU2V6MWRkUDVBbE5RYTFWeWNRTXlaczlPVmNtVWVXTSI7czozOiJ1cmwiO2E6MTp7czo4OiJpbnRlbmRlZCI7czozMzoiaHR0cDovL2xvY2FsaG9zdDo4MDAwL2FkbWluL3VzZXJzIjt9czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mjc6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMC9sb2dpbiI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=',1752055331),
('hvn057yt46Q5dp0WECUjShZr95R1q8TXw2JWMDDj',NULL,'127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoidWkycXRITnFJRWR4dE9FWmYyWUZXdnAyRDhOTTdBNk5HMnZkVkJLcyI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mjc6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMC9sb2dpbiI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=',1752106043),
('I5cVY5PMkUkwsnxpy4WEeYktY1OoRbwz74B0cUtH',NULL,'127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiQXhlaGxDSDBSVVNkSU9WRjlPR0RjU3c2bzZUZGltV1FqRUdibDVCYyI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mjc6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMC9sb2dpbiI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=',1752065684),
('izTwC6SlLOg29jcenb7KDVm6l1wPBXETBDGG3yIc',NULL,'127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoieTRJNVJuZVd6dG5LNUpHUHRrRGgxYll3Wjl2UkY1T2JLYmZjUzNpWSI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mjc6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMC9sb2dpbiI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=',1752134175),
('j7jKnuLJpbCSs21yDSxnvcoPPkwfT8S9HLA4Kts1',NULL,'127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiMnRXc0htZHNEZXlhT2I4czFxUnJ6UUJ5NUE3dmhOZFN1SFdxTUlWUCI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mjc6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMC9sb2dpbiI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=',1752174938),
('JtVXEeSbU4Cd0tux4oM0SrfvS5VdXETstjevvIKt',NULL,'127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiZnM1VVBxMkJzU0lIaTJqdE1ndXRtdGFKZTFlRXkzanM2emk5Um55WCI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mjc6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMC9sb2dpbiI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=',1752262719),
('k2cOTOdMROH2KpV3kimpuOhdNrX0YKvn87j1RrBO',NULL,'127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiYkpPQlVUQVRieFBkTm8xNGd1eDFOUnVTNXBnUnIxdjEySXdLcHlXMiI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mjc6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMC9sb2dpbiI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=',1752079160),
('pXGFT2dTGlli5FPcPRGjv3kFoZDQYscVmKhpJBzM',NULL,'127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoielVDN25NNlpIdDNhbjZXUWU0RjJKNG10TzRiNFdPNEdMcUxNdkhpRyI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mjc6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMC9sb2dpbiI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=',1752233423),
('SYqKRxGjbKciG1i9gmoNIAAC6H26qlZ27zJHRuoI',NULL,'127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiUDk2a0pNSjRvTUdvcmt5RDBmYUs4VUJvN2JaZTZ6bTg0eVRPODhleCI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mjc6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMC9sb2dpbiI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=',1752259174),
('VsS2sm4LXRZ4AxA0wDlGNVn69Qmt17Peu6MHozaD',NULL,'127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiOTNPdjVMZXVyR2UxSDFXaUh2OFRRbGRid0pnR1FSWXk1OHJucGhOeSI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mjc6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMC9sb2dpbiI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=',1752072179),
('wxsWvdDbsR62UusFzkKct8nxl4WcErzphtrARLPA',NULL,'127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:141.0) Gecko/20100101 Firefox/141.0','YTozOntzOjY6Il90b2tlbiI7czo0MDoiVVdZOXBtT1BpdmlIZHdBazBiVXRGU1VQeE5NdHMzQ1g1alZKeVNrTiI7czo0OiJjYXJ0IjthOjA6e31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1752105736),
('YIlz9JyuRODcyTJOupvnAaFSWc7hr0uTfnU4J29p',NULL,'127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiM1UwWHZwbm0xWEo1eDZwQXpyOUVuQlBoMGZqeTVVdGxOSEdKRVZtSSI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mjc6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMC9sb2dpbiI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=',1752247840),
('Zd7smeSSe5nEcO8gmLszClj1FnmUFO3ud4znolRL',NULL,'127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiU0dBRzAwSEx2c0k0OEw3RkRjcXFhN1VkSlRXOVV1Y3dtYkFHdHdGZSI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mjc6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMC9sb2dpbiI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=',1752149140),
('ZzAUvNYGvP4eLnLrmda9zblRMGETwmDPmax0aRef',1,'127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','YTo1OntzOjY6Il90b2tlbiI7czo0MDoiRk15YjRzbE5McmhZNUxjYzZyUkxMZXBLNTRqWlYwRE1MNllLTUVnQyI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzM6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMC9hZG1pbi91c2VycyI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fXM6NDoiY2FydCI7YTowOnt9czo1MDoibG9naW5fd2ViXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiO2k6MTt9',1752009242);
/*!40000 ALTER TABLE `sessions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `subcategories`
--

DROP TABLE IF EXISTS `subcategories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `subcategories` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `category_id` bigint(20) unsigned NOT NULL,
  `name` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `subcategories_name_category_id_unique` (`name`,`category_id`),
  KEY `subcategories_category_id_foreign` (`category_id`),
  CONSTRAINT `subcategories_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `subcategories`
--

LOCK TABLES `subcategories` WRITE;
/*!40000 ALTER TABLE `subcategories` DISABLE KEYS */;
INSERT INTO `subcategories` VALUES
(1,1,'Groenten','2025-02-04 16:23:18','2025-02-04 16:23:18',NULL),
(2,1,'Fruit','2025-02-04 16:23:18','2025-02-04 16:23:18',NULL),
(3,2,'Brood','2025-02-04 16:23:18','2025-02-04 16:23:18',NULL),
(4,2,'Gebak','2025-02-04 16:23:18','2025-02-04 16:23:18',NULL),
(5,3,'Zuivel','2025-02-04 16:23:18','2025-02-04 16:23:18',NULL),
(6,3,'Kaas','2025-02-04 16:23:18','2025-02-04 16:23:18',NULL),
(7,3,'Eieren','2025-02-04 16:23:18','2025-02-04 16:23:18',NULL);
/*!40000 ALTER TABLE `subcategories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_addresses`
--

DROP TABLE IF EXISTS `user_addresses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `user_addresses` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `street` varchar(255) NOT NULL,
  `house_number` varchar(255) DEFAULT NULL,
  `city` varchar(255) NOT NULL,
  `postal_code` varchar(255) NOT NULL,
  `country` varchar(255) NOT NULL DEFAULT 'Netherlands',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_addresses_user_id_foreign` (`user_id`),
  CONSTRAINT `user_addresses_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_addresses`
--

LOCK TABLES `user_addresses` WRITE;
/*!40000 ALTER TABLE `user_addresses` DISABLE KEYS */;
/*!40000 ALTER TABLE `user_addresses` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `status` enum('active','suspended') NOT NULL DEFAULT 'active',
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES
(1,'Admin User','admin@cc.nl',NULL,'$2y$12$N6LGAxVbnQPoN//tbE0P6ugYZ3HQX/chnEpzdAb9SSSJvXMcjBP/W',NULL,'2025-02-04 16:23:18','2025-02-04 16:23:18','active'),
(2,'Editor User','editor@cc.nl',NULL,'$2y$12$Kbwn90wN7p2E/brPW9vGbuczK8QccnYccKstXLH5rNIp9GNKwQT/C',NULL,'2025-02-04 16:23:18','2025-07-03 18:24:38','active');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*M!100616 SET NOTE_VERBOSITY=@OLD_NOTE_VERBOSITY */;

-- Dump completed on 2025-07-12  0:05:41
