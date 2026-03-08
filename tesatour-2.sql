-- MySQL dump 10.14  Distrib 5.5.68-MariaDB, for Linux (x86_64)
--
-- Host: localhost    Database: tesatour
-- ------------------------------------------------------
-- Server version	5.5.68-MariaDB

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
-- Table structure for table `admin_logs`
--

DROP TABLE IF EXISTS `admin_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `admin_logs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `admin_id` int(10) unsigned NOT NULL,
  `action_type` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `entity_type` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `entity_id` int(10) unsigned DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_admin_id` (`admin_id`),
  KEY `idx_action_type` (`action_type`),
  KEY `idx_created_at` (`created_at`),
  CONSTRAINT `admin_logs_ibfk_1` FOREIGN KEY (`admin_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `admin_logs`
--

LOCK TABLES `admin_logs` WRITE;
/*!40000 ALTER TABLE `admin_logs` DISABLE KEYS */;
/*!40000 ALTER TABLE `admin_logs` ENABLE KEYS */;
UNLOCK TABLES;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = '' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`tesatour`@`localhost`*/ /*!50003 TRIGGER `admin_logs_before_insert` BEFORE INSERT ON `admin_logs` FOR EACH ROW
BEGIN
    SET NEW.created_at = IFNULL(NEW.created_at, NOW());
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `channel_files`
--

DROP TABLE IF EXISTS `channel_files`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `channel_files` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `message_id` bigint(20) unsigned NOT NULL,
  `file_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `file_path` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL,
  `file_type` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `file_size` int(10) unsigned NOT NULL,
  `uploaded_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_message_id` (`message_id`),
  CONSTRAINT `channel_files_ibfk_1` FOREIGN KEY (`message_id`) REFERENCES `channel_messages` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `channel_files`
--

LOCK TABLES `channel_files` WRITE;
/*!40000 ALTER TABLE `channel_files` DISABLE KEYS */;
INSERT INTO `channel_files` VALUES (2,4,'Screenshot_5.png','/uploads/channels/6996dbc644b69_1771494342.png','image/png',175326,'2026-02-19 12:45:42');
/*!40000 ALTER TABLE `channel_files` ENABLE KEYS */;
UNLOCK TABLES;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = '' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`tesatour`@`localhost`*/ /*!50003 TRIGGER `channel_files_before_insert` BEFORE INSERT ON `channel_files` FOR EACH ROW
BEGIN
    SET NEW.uploaded_at = IFNULL(NEW.uploaded_at, NOW());
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `channel_messages`
--

DROP TABLE IF EXISTS `channel_messages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `channel_messages` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `channel_id` int(10) unsigned NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  `message_text` text COLLATE utf8mb4_unicode_ci,
  `is_pinned` tinyint(1) DEFAULT '0',
  `is_edited` tinyint(1) DEFAULT '0',
  `edited_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_channel_id` (`channel_id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_created_at` (`created_at`),
  KEY `idx_is_pinned` (`is_pinned`),
  CONSTRAINT `channel_messages_ibfk_1` FOREIGN KEY (`channel_id`) REFERENCES `group_channels` (`id`) ON DELETE CASCADE,
  CONSTRAINT `channel_messages_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `channel_messages`
--

LOCK TABLES `channel_messages` WRITE;
/*!40000 ALTER TABLE `channel_messages` DISABLE KEYS */;
INSERT INTO `channel_messages` VALUES (1,1,12,'привет всем!',0,0,NULL,'2026-02-18 22:08:56','2026-02-18 22:08:56'),(3,4,15,'Собираемся завтра в 23:00',0,0,NULL,'2026-02-19 07:45:25','2026-02-19 07:45:25'),(4,5,11,'Сбор группы 20.02 в 10.00',0,0,NULL,'2026-02-19 12:45:42','2026-02-19 12:45:42');
/*!40000 ALTER TABLE `channel_messages` ENABLE KEYS */;
UNLOCK TABLES;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = '' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`tesatour`@`localhost`*/ /*!50003 TRIGGER `channel_messages_before_insert` BEFORE INSERT ON `channel_messages` FOR EACH ROW
BEGIN
    SET NEW.created_at = IFNULL(NEW.created_at, NOW());
    SET NEW.updated_at = IFNULL(NEW.updated_at, NOW());
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = '' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`tesatour`@`localhost`*/ /*!50003 TRIGGER `channel_messages_before_update` BEFORE UPDATE ON `channel_messages` FOR EACH ROW
BEGIN
    SET NEW.updated_at = NOW();
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `channel_reactions`
--

DROP TABLE IF EXISTS `channel_reactions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `channel_reactions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `message_id` bigint(20) unsigned NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  `reaction` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_message_user_reaction` (`message_id`,`user_id`,`reaction`),
  KEY `idx_message_id` (`message_id`),
  KEY `idx_user_id` (`user_id`),
  CONSTRAINT `channel_reactions_ibfk_1` FOREIGN KEY (`message_id`) REFERENCES `channel_messages` (`id`) ON DELETE CASCADE,
  CONSTRAINT `channel_reactions_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `channel_reactions`
--

LOCK TABLES `channel_reactions` WRITE;
/*!40000 ALTER TABLE `channel_reactions` DISABLE KEYS */;
INSERT INTO `channel_reactions` VALUES (1,1,12,'💩','2026-02-18 22:08:59'),(5,3,15,'👍','2026-02-19 07:45:29'),(6,4,11,'👍','2026-02-19 12:45:51');
/*!40000 ALTER TABLE `channel_reactions` ENABLE KEYS */;
UNLOCK TABLES;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = '' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`tesatour`@`localhost`*/ /*!50003 TRIGGER `channel_reactions_before_insert` BEFORE INSERT ON `channel_reactions` FOR EACH ROW
BEGIN
    SET NEW.created_at = IFNULL(NEW.created_at, NOW());
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `chat_reactions`
--

DROP TABLE IF EXISTS `chat_reactions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `chat_reactions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `message_id` bigint(20) unsigned NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  `reaction` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_message_user_reaction` (`message_id`,`user_id`,`reaction`),
  KEY `idx_message_id` (`message_id`),
  KEY `idx_user_id` (`user_id`),
  CONSTRAINT `chat_reactions_ibfk_1` FOREIGN KEY (`message_id`) REFERENCES `group_chat_messages` (`id`) ON DELETE CASCADE,
  CONSTRAINT `chat_reactions_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `chat_reactions`
--

LOCK TABLES `chat_reactions` WRITE;
/*!40000 ALTER TABLE `chat_reactions` DISABLE KEYS */;
/*!40000 ALTER TABLE `chat_reactions` ENABLE KEYS */;
UNLOCK TABLES;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = '' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`tesatour`@`localhost`*/ /*!50003 TRIGGER `chat_reactions_before_insert` BEFORE INSERT ON `chat_reactions` FOR EACH ROW
BEGIN
    SET NEW.created_at = IFNULL(NEW.created_at, NOW());
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `danger_zones`
--

DROP TABLE IF EXISTS `danger_zones`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `danger_zones` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `group_id` int(10) unsigned NOT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `latitude` decimal(10,8) NOT NULL,
  `longitude` decimal(11,8) NOT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_group_id` (`group_id`),
  KEY `created_by` (`created_by`),
  CONSTRAINT `danger_zones_ibfk_1` FOREIGN KEY (`group_id`) REFERENCES `groups` (`id`) ON DELETE CASCADE,
  CONSTRAINT `danger_zones_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `danger_zones`
--

LOCK TABLES `danger_zones` WRITE;
/*!40000 ALTER TABLE `danger_zones` DISABLE KEYS */;
INSERT INTO `danger_zones` VALUES (1,8,12,'тест','тест',55.75797575,37.61249348,'2026-02-18 22:08:37','2026-02-18 22:08:37'),(2,10,7,'Крутой спуск','Очень крутой спуск, можно травмироваться',55.74811564,37.62559017,'2026-02-18 22:18:01','2026-02-18 22:18:01'),(3,10,7,'Пожар','',55.72245982,37.61090202,'2026-02-19 07:05:49','2026-02-19 07:05:49'),(4,11,15,'Крутой спуск','Можно травмироваться',55.74442036,37.61393459,'2026-02-19 07:43:37','2026-02-19 07:43:37'),(5,6,11,'Крутой спуск','можно получить травму',55.75699067,37.61101719,'2026-02-19 12:42:54','2026-02-19 12:42:54');
/*!40000 ALTER TABLE `danger_zones` ENABLE KEYS */;
UNLOCK TABLES;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = '' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`tesatour`@`localhost`*/ /*!50003 TRIGGER `danger_zones_before_insert` BEFORE INSERT ON `danger_zones` FOR EACH ROW
BEGIN
    SET NEW.created_at = IFNULL(NEW.created_at, NOW());
    SET NEW.updated_at = IFNULL(NEW.updated_at, NOW());
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = '' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`tesatour`@`localhost`*/ /*!50003 TRIGGER `danger_zones_before_update` BEFORE UPDATE ON `danger_zones` FOR EACH ROW
BEGIN
    SET NEW.updated_at = NOW();
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `group_blacklist`
--

DROP TABLE IF EXISTS `group_blacklist`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `group_blacklist` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `group_id` int(10) unsigned NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  `banned_by` int(10) unsigned NOT NULL,
  `reason` text COLLATE utf8mb4_unicode_ci,
  `created_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_group_user_ban` (`group_id`,`user_id`),
  KEY `idx_group_id` (`group_id`),
  KEY `idx_user_id` (`user_id`),
  KEY `banned_by` (`banned_by`),
  CONSTRAINT `group_blacklist_ibfk_1` FOREIGN KEY (`group_id`) REFERENCES `groups` (`id`) ON DELETE CASCADE,
  CONSTRAINT `group_blacklist_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `group_blacklist_ibfk_3` FOREIGN KEY (`banned_by`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `group_blacklist`
--

LOCK TABLES `group_blacklist` WRITE;
/*!40000 ALTER TABLE `group_blacklist` DISABLE KEYS */;
INSERT INTO `group_blacklist` VALUES (1,4,8,7,'',NULL);
/*!40000 ALTER TABLE `group_blacklist` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `group_channels`
--

DROP TABLE IF EXISTS `group_channels`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `group_channels` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `group_id` int(10) unsigned NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `created_by` int(10) unsigned NOT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_group_id` (`group_id`),
  KEY `created_by` (`created_by`),
  CONSTRAINT `group_channels_ibfk_1` FOREIGN KEY (`group_id`) REFERENCES `groups` (`id`) ON DELETE CASCADE,
  CONSTRAINT `group_channels_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `group_channels`
--

LOCK TABLES `group_channels` WRITE;
/*!40000 ALTER TABLE `group_channels` DISABLE KEYS */;
INSERT INTO `group_channels` VALUES (1,8,'тест','',12,'2026-02-18 22:08:49','2026-02-18 22:08:49'),(2,8,'тест','',12,'2026-02-18 22:08:49','2026-02-18 22:08:49'),(3,10,'Основной','',7,'2026-02-19 06:35:32','2026-02-19 06:35:32'),(4,11,'Новости','',15,'2026-02-19 07:45:12','2026-02-19 07:45:12'),(5,6,'Объявления','',11,'2026-02-19 12:44:18','2026-02-19 12:44:18');
/*!40000 ALTER TABLE `group_channels` ENABLE KEYS */;
UNLOCK TABLES;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = '' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`tesatour`@`localhost`*/ /*!50003 TRIGGER `group_channels_before_insert` BEFORE INSERT ON `group_channels` FOR EACH ROW
BEGIN
    SET NEW.created_at = IFNULL(NEW.created_at, NOW());
    SET NEW.updated_at = IFNULL(NEW.updated_at, NOW());
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = '' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`tesatour`@`localhost`*/ /*!50003 TRIGGER `group_channels_before_update` BEFORE UPDATE ON `group_channels` FOR EACH ROW
BEGIN
    SET NEW.updated_at = NOW();
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `group_chat_messages`
--

DROP TABLE IF EXISTS `group_chat_messages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `group_chat_messages` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `group_id` int(10) unsigned NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  `message_text` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `reply_to_message_id` bigint(20) unsigned DEFAULT NULL,
  `is_pinned` tinyint(1) DEFAULT '0',
  `is_edited` tinyint(1) DEFAULT '0',
  `edited_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_group_id` (`group_id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_created_at` (`created_at`),
  KEY `idx_is_pinned` (`is_pinned`),
  KEY `idx_reply_to` (`reply_to_message_id`),
  CONSTRAINT `group_chat_messages_ibfk_1` FOREIGN KEY (`group_id`) REFERENCES `groups` (`id`) ON DELETE CASCADE,
  CONSTRAINT `group_chat_messages_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `group_chat_messages_ibfk_3` FOREIGN KEY (`reply_to_message_id`) REFERENCES `group_chat_messages` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `group_chat_messages`
--

LOCK TABLES `group_chat_messages` WRITE;
/*!40000 ALTER TABLE `group_chat_messages` DISABLE KEYS */;
INSERT INTO `group_chat_messages` VALUES (6,10,7,'Всем привет!',NULL,0,0,NULL,'2026-02-19 07:12:36','2026-02-19 07:12:36');
/*!40000 ALTER TABLE `group_chat_messages` ENABLE KEYS */;
UNLOCK TABLES;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = '' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`tesatour`@`localhost`*/ /*!50003 TRIGGER `group_chat_messages_before_insert` BEFORE INSERT ON `group_chat_messages` FOR EACH ROW
BEGIN
    SET NEW.created_at = IFNULL(NEW.created_at, NOW());
    SET NEW.updated_at = IFNULL(NEW.updated_at, NOW());
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = '' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`tesatour`@`localhost`*/ /*!50003 TRIGGER `group_chat_messages_before_update` BEFORE UPDATE ON `group_chat_messages` FOR EACH ROW
BEGIN
    SET NEW.updated_at = NOW();
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `group_join_requests`
--

DROP TABLE IF EXISTS `group_join_requests`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `group_join_requests` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `group_id` int(10) unsigned NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  `status` enum('pending','approved','rejected') COLLATE utf8mb4_unicode_ci DEFAULT 'pending',
  `message` text COLLATE utf8mb4_unicode_ci,
  `processed_by` int(10) unsigned DEFAULT NULL,
  `processed_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_group_user_request` (`group_id`,`user_id`),
  KEY `idx_group_id` (`group_id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_status` (`status`),
  KEY `processed_by` (`processed_by`),
  CONSTRAINT `group_join_requests_ibfk_1` FOREIGN KEY (`group_id`) REFERENCES `groups` (`id`) ON DELETE CASCADE,
  CONSTRAINT `group_join_requests_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `group_join_requests_ibfk_3` FOREIGN KEY (`processed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `group_join_requests`
--

LOCK TABLES `group_join_requests` WRITE;
/*!40000 ALTER TABLE `group_join_requests` DISABLE KEYS */;
INSERT INTO `group_join_requests` VALUES (1,4,8,'pending',NULL,NULL,NULL,NULL);
/*!40000 ALTER TABLE `group_join_requests` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `group_members`
--

DROP TABLE IF EXISTS `group_members`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `group_members` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `group_id` int(10) unsigned NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  `role` enum('owner','admin','member') COLLATE utf8mb4_unicode_ci DEFAULT 'member',
  `joined_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_group_user` (`group_id`,`user_id`),
  KEY `idx_group_id` (`group_id`),
  KEY `idx_user_id` (`user_id`),
  CONSTRAINT `group_members_ibfk_1` FOREIGN KEY (`group_id`) REFERENCES `groups` (`id`) ON DELETE CASCADE,
  CONSTRAINT `group_members_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `group_members`
--

LOCK TABLES `group_members` WRITE;
/*!40000 ALTER TABLE `group_members` DISABLE KEYS */;
INSERT INTO `group_members` VALUES (1,4,7,'owner',NULL),(3,5,9,'owner',NULL),(4,6,11,'owner',NULL),(5,7,7,'owner',NULL),(6,8,12,'owner',NULL),(7,9,12,'owner',NULL),(8,10,7,'owner',NULL),(9,11,15,'owner',NULL),(10,11,8,'member',NULL);
/*!40000 ALTER TABLE `group_members` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `groups`
--

DROP TABLE IF EXISTS `groups`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `groups` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `invite_code` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner_id` int(10) unsigned NOT NULL,
  `status` enum('active','closed') COLLATE utf8mb4_unicode_ci DEFAULT 'active',
  `require_approval` tinyint(1) DEFAULT '0',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `closed_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `invite_code` (`invite_code`),
  KEY `idx_invite_code` (`invite_code`),
  KEY `idx_owner_id` (`owner_id`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `groups`
--

LOCK TABLES `groups` WRITE;
/*!40000 ALTER TABLE `groups` DISABLE KEYS */;
INSERT INTO `groups` VALUES (4,'Поход на Эльбрус','Все едем на Эльбрус!!','fd34da5745a57839',7,'closed',0,NULL,NULL,'2026-01-27 00:00:34'),(5,'Тестовая','','5bce77274f5302b8',9,'active',0,NULL,NULL,NULL),(6,'Поход на Эльбрус','','bd67567d8a5f9490',11,'active',0,NULL,NULL,NULL),(7,'Вов','Чово','4e1e0f38ab665919',7,'closed',0,NULL,NULL,'2026-02-18 22:12:26'),(8,'Ehe','','bdf094b8f5e0c653',12,'active',0,NULL,NULL,NULL),(9,'Dbb','','c3fb836f7bee47f0',12,'active',0,NULL,NULL,NULL),(10,'Поход на Эльбрус','Все едем на Эльюрус!','03d043f139b5d1fe',7,'active',0,NULL,NULL,NULL),(11,'Поход на Эльбрус ','Все едем на Эльбрус!','260afbd4ba7b3c54',15,'active',1,NULL,NULL,NULL);
/*!40000 ALTER TABLE `groups` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `location_history`
--

DROP TABLE IF EXISTS `location_history`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `location_history` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `latitude` decimal(10,8) NOT NULL,
  `longitude` decimal(11,8) NOT NULL,
  `accuracy` float DEFAULT NULL,
  `recorded_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_recorded_at` (`recorded_at`)
) ENGINE=InnoDB AUTO_INCREMENT=1201 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `location_history`
--

LOCK TABLES `location_history` WRITE;
/*!40000 ALTER TABLE `location_history` DISABLE KEYS */;
INSERT INTO `location_history` VALUES (1,1,55.78632400,37.61234600,NULL,'2026-01-26 20:36:06'),(2,1,55.66017913,37.60998153,NULL,'2026-01-26 20:36:36'),(3,1,55.66017913,37.60998153,NULL,'2026-01-26 20:37:06'),(4,1,55.66017913,37.60998153,NULL,'2026-01-26 20:37:36'),(5,1,55.66017913,37.60998153,NULL,'2026-01-26 20:38:06'),(6,1,55.66017913,37.60998153,NULL,'2026-01-26 20:38:36'),(7,1,55.66017913,37.60998153,NULL,'2026-01-26 20:39:06'),(8,1,55.66017913,37.60998153,NULL,'2026-01-26 20:39:36'),(9,1,55.66017913,37.60998153,NULL,'2026-01-26 20:40:06'),(10,1,55.66017913,37.60998153,NULL,'2026-01-26 20:40:36'),(11,1,55.66017913,37.60998153,NULL,'2026-01-26 20:41:06'),(12,1,55.66017913,37.60998153,NULL,'2026-01-26 20:41:36'),(13,1,55.66017913,37.60998153,NULL,'2026-01-26 20:42:06'),(14,1,55.66017913,37.60998153,NULL,'2026-01-26 20:42:36'),(15,1,55.66017913,37.60998153,NULL,'2026-01-26 20:43:06'),(16,1,55.66017913,37.60998153,NULL,'2026-01-26 20:43:36'),(17,1,55.66017913,37.60998153,NULL,'2026-01-26 20:44:06'),(18,1,55.66017913,37.60998153,NULL,'2026-01-26 20:44:36'),(19,1,55.66017913,37.60998153,NULL,'2026-01-26 20:45:06'),(20,1,55.66017913,37.60998153,NULL,'2026-01-26 20:45:36'),(21,1,55.66017913,37.60998153,NULL,'2026-01-26 20:46:06'),(22,1,55.66017913,37.60998153,NULL,'2026-01-26 20:46:36'),(23,1,55.66017913,37.60998153,NULL,'2026-01-26 20:47:06'),(24,1,55.66017913,37.60998153,NULL,'2026-01-26 20:47:36'),(25,1,55.66017913,37.60998153,NULL,'2026-01-26 20:48:06'),(26,1,55.66017913,37.60998153,NULL,'2026-01-26 20:48:36'),(27,1,55.66017913,37.60998153,NULL,'2026-01-26 20:49:06'),(28,1,55.66017913,37.60998153,NULL,'2026-01-26 20:49:36'),(29,1,55.66017913,37.60998153,NULL,'2026-01-26 20:50:06'),(30,1,55.66017913,37.60998153,NULL,'2026-01-26 20:50:36'),(31,1,55.66017913,37.60998153,NULL,'2026-01-26 20:51:06'),(32,1,55.66017913,37.60998153,NULL,'2026-01-26 20:51:36'),(33,7,55.78632400,37.61234600,NULL,'2026-01-26 23:33:22'),(34,8,55.76332600,37.61366500,NULL,'2026-01-26 23:40:40'),(35,9,55.65997660,37.61026220,NULL,'2026-01-27 01:17:44'),(36,11,55.65351900,37.61196900,NULL,'2026-02-10 11:34:17'),(37,7,55.66046059,37.60997613,NULL,'2026-02-17 21:26:26'),(38,7,55.66046059,37.60997613,NULL,'2026-02-17 21:26:26'),(39,7,55.66049656,37.60999587,NULL,'2026-02-17 21:27:05'),(40,7,55.66049656,37.60999587,NULL,'2026-02-17 21:27:05'),(41,7,55.66049531,37.60999453,NULL,'2026-02-17 21:27:05'),(42,7,55.66049388,37.60999297,NULL,'2026-02-17 21:27:07'),(43,7,55.66049577,37.60999504,NULL,'2026-02-17 21:28:05'),(44,7,55.66043495,37.60995681,NULL,'2026-02-17 21:28:06'),(45,7,55.66040291,37.60993689,NULL,'2026-02-17 21:28:07'),(46,7,55.66062005,37.61007359,NULL,'2026-02-17 21:29:06'),(47,7,55.66049628,37.60999542,NULL,'2026-02-17 21:30:07'),(48,7,55.66049520,37.60999474,NULL,'2026-02-17 21:30:07'),(49,7,55.66049092,37.60999206,NULL,'2026-02-17 21:30:08'),(50,7,55.66037747,37.60992048,NULL,'2026-02-17 21:31:07'),(51,7,55.66037719,37.60992030,NULL,'2026-02-17 21:31:07'),(52,7,55.66037688,37.60992011,NULL,'2026-02-17 21:31:08'),(53,7,55.66037743,37.60992046,NULL,'2026-02-17 21:32:09'),(54,7,55.66037752,37.60992051,NULL,'2026-02-17 21:33:09'),(55,7,55.66037749,37.60992050,NULL,'2026-02-17 21:33:09'),(56,7,55.66037748,37.60992048,NULL,'2026-02-17 21:33:10'),(57,7,55.66045892,37.61001718,NULL,'2026-02-17 21:34:04'),(58,7,55.66030586,37.60993134,NULL,'2026-02-17 21:34:06'),(59,7,55.66030586,37.60993134,NULL,'2026-02-17 21:34:27'),(60,7,55.66030586,37.60993134,NULL,'2026-02-17 21:34:27'),(61,7,55.66030586,37.60993134,NULL,'2026-02-17 21:34:31'),(62,7,55.66030586,37.60993134,NULL,'2026-02-17 21:34:31'),(63,7,55.66030586,37.60993134,NULL,'2026-02-17 21:34:33'),(64,7,55.66030586,37.60993134,NULL,'2026-02-17 21:34:33'),(65,7,55.66030586,37.60993134,NULL,'2026-02-17 21:34:34'),(66,7,55.66030586,37.60993134,NULL,'2026-02-17 21:34:34'),(67,7,55.66030586,37.60993134,NULL,'2026-02-17 21:34:48'),(68,7,55.66030586,37.60993134,NULL,'2026-02-17 21:34:48'),(69,7,55.66030586,37.60993134,NULL,'2026-02-17 21:34:59'),(70,7,55.66030586,37.60993134,NULL,'2026-02-17 21:34:59'),(71,7,55.65997230,37.61042170,NULL,'2026-02-17 21:37:06'),(72,7,55.65997230,37.61042170,NULL,'2026-02-17 21:37:06'),(73,7,55.65999630,37.61011900,NULL,'2026-02-17 21:37:16'),(74,7,55.65999630,37.61011900,NULL,'2026-02-17 21:37:16'),(75,7,55.65997660,37.61004150,NULL,'2026-02-17 21:37:23'),(76,7,55.65997660,37.61004150,NULL,'2026-02-17 21:37:23'),(77,7,55.65994290,37.61037450,NULL,'2026-02-17 21:37:27'),(78,7,55.65994290,37.61037450,NULL,'2026-02-17 21:37:27'),(79,7,55.65997420,37.61008120,NULL,'2026-02-17 21:37:33'),(80,7,55.65997420,37.61008120,NULL,'2026-02-17 21:37:34'),(81,7,55.65994810,37.61033260,NULL,'2026-02-17 21:37:39'),(82,7,55.65994810,37.61033260,NULL,'2026-02-17 21:37:39'),(83,7,55.65995780,37.61041990,NULL,'2026-02-17 21:38:11'),(84,7,55.65995780,37.61041990,NULL,'2026-02-17 21:38:11'),(85,7,55.65995050,37.61037920,NULL,'2026-02-17 21:38:40'),(86,7,55.65995050,37.61037920,NULL,'2026-02-17 21:38:40'),(87,7,55.65991750,37.61011840,NULL,'2026-02-17 21:38:48'),(88,7,55.65991750,37.61011840,NULL,'2026-02-17 21:38:48'),(89,7,55.65999140,37.61015070,NULL,'2026-02-17 21:38:55'),(90,7,55.65999140,37.61015070,NULL,'2026-02-17 21:38:55'),(91,7,55.65996390,37.61034720,NULL,'2026-02-17 21:39:00'),(92,7,55.65996390,37.61034720,NULL,'2026-02-17 21:39:00'),(93,7,55.65994680,37.61037900,NULL,'2026-02-17 21:39:06'),(94,7,55.65994680,37.61037900,NULL,'2026-02-17 21:39:06'),(95,7,55.65995570,37.61040060,NULL,'2026-02-17 21:39:12'),(96,7,55.65995570,37.61040060,NULL,'2026-02-17 21:39:12'),(97,7,55.66000750,37.60995310,NULL,'2026-02-17 21:39:39'),(98,7,55.66000750,37.60995310,NULL,'2026-02-17 21:39:39'),(99,7,55.66016247,37.60887139,NULL,'2026-02-17 21:41:50'),(100,7,55.66016247,37.60887139,NULL,'2026-02-17 21:41:50'),(101,7,55.66016247,37.60887139,NULL,'2026-02-17 21:41:59'),(102,7,55.66016247,37.60887139,NULL,'2026-02-17 21:41:59'),(103,7,55.66016247,37.60887139,NULL,'2026-02-17 21:41:59'),(104,7,55.66016247,37.60887139,NULL,'2026-02-17 21:41:59'),(105,7,55.66016247,37.60887139,NULL,'2026-02-17 21:42:02'),(106,7,55.66016247,37.60887139,NULL,'2026-02-17 21:42:02'),(107,7,55.66016247,37.60887139,NULL,'2026-02-17 21:42:05'),(108,7,55.66016247,37.60887139,NULL,'2026-02-17 21:42:05'),(109,7,55.66016247,37.60887139,NULL,'2026-02-17 21:42:09'),(110,7,55.66016247,37.60887139,NULL,'2026-02-17 21:42:09'),(111,7,55.66016247,37.60887139,NULL,'2026-02-17 21:42:19'),(112,7,55.66016247,37.60887139,NULL,'2026-02-17 21:42:19'),(113,7,55.66016247,37.60887139,NULL,'2026-02-17 21:42:23'),(114,7,55.66016247,37.60887139,NULL,'2026-02-17 21:42:23'),(115,7,55.66016247,37.60887139,NULL,'2026-02-17 21:42:26'),(116,7,55.66016247,37.60887139,NULL,'2026-02-17 21:42:26'),(117,7,55.66025335,37.60965771,NULL,'2026-02-17 21:42:29'),(118,7,55.66025335,37.60965771,NULL,'2026-02-17 21:42:29'),(119,7,55.66025335,37.60965771,NULL,'2026-02-17 21:42:31'),(120,7,55.66025335,37.60965771,NULL,'2026-02-17 21:42:31'),(121,7,55.66025335,37.60965771,NULL,'2026-02-17 21:42:34'),(122,7,55.66025335,37.60965771,NULL,'2026-02-17 21:42:34'),(123,7,55.66025335,37.60965771,NULL,'2026-02-17 21:42:42'),(124,7,55.66025335,37.60965771,NULL,'2026-02-17 21:42:42'),(125,7,55.66025335,37.60965771,NULL,'2026-02-17 21:42:42'),(126,7,55.66025335,37.60965771,NULL,'2026-02-17 21:42:42'),(127,7,55.66025335,37.60965771,NULL,'2026-02-17 21:42:44'),(128,7,55.66025335,37.60965771,NULL,'2026-02-17 21:42:44'),(129,7,55.66025335,37.60965771,NULL,'2026-02-17 21:42:47'),(130,7,55.66025335,37.60965771,NULL,'2026-02-17 21:42:47'),(131,7,55.66025335,37.60965771,NULL,'2026-02-17 21:42:58'),(132,7,55.66025335,37.60965771,NULL,'2026-02-17 21:42:58'),(133,7,55.66025335,37.60965771,NULL,'2026-02-17 21:43:06'),(134,7,55.66025335,37.60965771,NULL,'2026-02-17 21:43:06'),(135,7,55.66025335,37.60965771,NULL,'2026-02-17 21:43:07'),(136,7,55.66025335,37.60965771,NULL,'2026-02-17 21:43:07'),(137,7,55.66025335,37.60965771,NULL,'2026-02-17 21:43:10'),(138,7,55.66025335,37.60965771,NULL,'2026-02-17 21:43:10'),(139,7,55.66025335,37.60965771,NULL,'2026-02-17 21:43:25'),(140,7,55.66025335,37.60965771,NULL,'2026-02-17 21:43:25'),(141,7,55.66023274,37.60990972,NULL,'2026-02-17 21:43:30'),(142,7,55.66023274,37.60990972,NULL,'2026-02-17 21:43:30'),(143,7,55.66023274,37.60990972,NULL,'2026-02-17 21:43:42'),(144,7,55.66023274,37.60990972,NULL,'2026-02-17 21:43:42'),(145,7,55.66023274,37.60990972,NULL,'2026-02-17 21:43:45'),(146,7,55.66023274,37.60990972,NULL,'2026-02-17 21:43:45'),(147,7,55.66023274,37.60990972,NULL,'2026-02-17 21:43:48'),(148,7,55.66023274,37.60990972,NULL,'2026-02-17 21:43:48'),(149,7,55.66023274,37.60990972,NULL,'2026-02-17 21:43:49'),(150,7,55.66023274,37.60990972,NULL,'2026-02-17 21:43:49'),(151,7,55.66023274,37.60990972,NULL,'2026-02-17 21:43:51'),(152,7,55.66023274,37.60990972,NULL,'2026-02-17 21:43:51'),(153,7,55.66023274,37.60990972,NULL,'2026-02-17 21:43:54'),(154,7,55.66023274,37.60990972,NULL,'2026-02-17 21:43:54'),(155,7,55.66023274,37.60990972,NULL,'2026-02-17 21:44:01'),(156,7,55.66023274,37.60990972,NULL,'2026-02-17 21:44:01'),(157,7,55.66023274,37.60990972,NULL,'2026-02-17 21:44:06'),(158,7,55.66023274,37.60990972,NULL,'2026-02-17 21:44:06'),(159,7,55.66023274,37.60990972,NULL,'2026-02-17 21:44:09'),(160,7,55.66023274,37.60990972,NULL,'2026-02-17 21:44:09'),(161,7,55.66023274,37.60990972,NULL,'2026-02-17 21:44:11'),(162,7,55.66023274,37.60990972,NULL,'2026-02-17 21:44:11'),(163,7,55.66023274,37.60990972,NULL,'2026-02-17 21:44:13'),(164,7,55.66023274,37.60990972,NULL,'2026-02-17 21:44:13'),(165,7,55.66023274,37.60990972,NULL,'2026-02-17 21:44:15'),(166,7,55.66023274,37.60990972,NULL,'2026-02-17 21:44:15'),(167,7,55.66023274,37.60990972,NULL,'2026-02-17 21:44:22'),(168,7,55.66023274,37.60990972,NULL,'2026-02-17 21:44:22'),(169,12,55.65998280,37.61009210,NULL,'2026-02-17 22:49:40'),(170,12,55.65998280,37.61009210,NULL,'2026-02-17 22:49:40'),(171,12,55.65994930,37.61025500,NULL,'2026-02-17 22:49:46'),(172,12,55.65994930,37.61025500,NULL,'2026-02-17 22:49:46'),(173,12,55.65994930,37.61025500,NULL,'2026-02-17 22:49:47'),(174,12,55.65994930,37.61025500,NULL,'2026-02-17 22:49:47'),(175,12,55.65994420,37.61041500,NULL,'2026-02-17 22:49:52'),(176,12,55.65994420,37.61041500,NULL,'2026-02-17 22:49:52'),(177,12,55.65998100,37.61005600,NULL,'2026-02-17 22:49:58'),(178,12,55.65998100,37.61005600,NULL,'2026-02-17 22:49:58'),(179,12,55.65992490,37.61035860,NULL,'2026-02-17 22:50:03'),(180,12,55.65992490,37.61035860,NULL,'2026-02-17 22:50:03'),(181,12,55.66039276,37.60989378,NULL,'2026-02-18 10:58:08'),(182,12,55.66039276,37.60989378,NULL,'2026-02-18 10:58:08'),(183,12,55.66039276,37.60989378,NULL,'2026-02-18 10:58:16'),(184,12,55.66039276,37.60989378,NULL,'2026-02-18 10:58:16'),(185,12,55.66039276,37.60989378,NULL,'2026-02-18 10:58:17'),(186,12,55.66039276,37.60989378,NULL,'2026-02-18 10:58:17'),(187,12,55.66039276,37.60989378,NULL,'2026-02-18 10:58:21'),(188,12,55.66039276,37.60989378,NULL,'2026-02-18 10:58:21'),(189,12,55.66039276,37.60989378,NULL,'2026-02-18 10:58:26'),(190,12,55.66039276,37.60989378,NULL,'2026-02-18 10:58:26'),(191,12,55.66039276,37.60989378,NULL,'2026-02-18 10:58:27'),(192,12,55.66039276,37.60989378,NULL,'2026-02-18 10:58:27'),(193,12,55.66039276,37.60989378,NULL,'2026-02-18 10:58:32'),(194,12,55.66039276,37.60989378,NULL,'2026-02-18 10:58:32'),(195,12,55.66039276,37.60989378,NULL,'2026-02-18 10:58:35'),(196,12,55.66039276,37.60989378,NULL,'2026-02-18 10:58:35'),(197,12,55.66039276,37.60989378,NULL,'2026-02-18 10:58:43'),(198,12,55.66039276,37.60989378,NULL,'2026-02-18 10:58:43'),(199,12,55.66039276,37.60989378,NULL,'2026-02-18 10:58:44'),(200,12,55.66039276,37.60989378,NULL,'2026-02-18 10:58:44'),(201,12,55.66039276,37.60989378,NULL,'2026-02-18 10:58:45'),(202,12,55.66039276,37.60989378,NULL,'2026-02-18 10:58:45'),(203,12,55.66039276,37.60989378,NULL,'2026-02-18 10:58:45'),(204,12,55.66039276,37.60989378,NULL,'2026-02-18 10:58:45'),(205,12,55.66039276,37.60989378,NULL,'2026-02-18 10:58:47'),(206,12,55.66039276,37.60989378,NULL,'2026-02-18 10:58:47'),(207,12,55.66039276,37.60989379,NULL,'2026-02-18 10:58:48'),(208,12,55.66039276,37.60989379,NULL,'2026-02-18 10:58:48'),(209,12,55.66039276,37.60989379,NULL,'2026-02-18 10:58:48'),(210,12,55.66039276,37.60989379,NULL,'2026-02-18 10:58:48'),(211,12,55.66031357,37.60978244,NULL,'2026-02-18 10:58:51'),(212,12,55.66031357,37.60978244,NULL,'2026-02-18 10:58:51'),(213,12,55.66031357,37.60978244,NULL,'2026-02-18 10:58:57'),(214,12,55.66031357,37.60978244,NULL,'2026-02-18 10:58:57'),(215,12,55.66031357,37.60978244,NULL,'2026-02-18 10:59:00'),(216,12,55.66031357,37.60978244,NULL,'2026-02-18 10:59:00'),(217,12,55.66031357,37.60978244,NULL,'2026-02-18 10:59:30'),(218,12,55.66031357,37.60978244,NULL,'2026-02-18 10:59:35'),(219,12,55.66031357,37.60978244,NULL,'2026-02-18 10:59:35'),(220,12,55.66031357,37.60978244,NULL,'2026-02-18 10:59:38'),(221,12,55.66031357,37.60978244,NULL,'2026-02-18 10:59:38'),(222,12,55.66031357,37.60978244,NULL,'2026-02-18 10:59:40'),(223,12,55.66031357,37.60978244,NULL,'2026-02-18 10:59:40'),(224,12,55.66031357,37.60978244,NULL,'2026-02-18 10:59:43'),(225,12,55.66031357,37.60978244,NULL,'2026-02-18 10:59:43'),(226,12,55.66029144,37.60975132,NULL,'2026-02-18 10:59:49'),(227,12,55.66029144,37.60975132,NULL,'2026-02-18 10:59:49'),(228,12,55.66029144,37.60975132,NULL,'2026-02-18 10:59:56'),(229,12,55.66029144,37.60975132,NULL,'2026-02-18 10:59:56'),(230,12,55.66029144,37.60975132,NULL,'2026-02-18 11:00:07'),(231,12,55.66029144,37.60975132,NULL,'2026-02-18 11:00:07'),(232,12,55.66029144,37.60975132,NULL,'2026-02-18 11:00:16'),(233,12,55.66029144,37.60975132,NULL,'2026-02-18 11:00:16'),(234,12,55.66029144,37.60975132,NULL,'2026-02-18 11:00:21'),(235,12,55.66029144,37.60975132,NULL,'2026-02-18 11:00:21'),(236,12,55.66029144,37.60975132,NULL,'2026-02-18 11:00:25'),(237,12,55.66029144,37.60975132,NULL,'2026-02-18 11:00:25'),(238,12,55.66029144,37.60975132,NULL,'2026-02-18 11:00:27'),(239,12,55.66029144,37.60975132,NULL,'2026-02-18 11:00:27'),(240,12,55.66029144,37.60975132,NULL,'2026-02-18 11:00:35'),(241,12,55.66029144,37.60975132,NULL,'2026-02-18 11:00:35'),(242,12,55.66029144,37.60975132,NULL,'2026-02-18 11:00:41'),(243,12,55.66029144,37.60975132,NULL,'2026-02-18 11:00:41'),(244,12,55.66029144,37.60975132,NULL,'2026-02-18 11:00:43'),(245,12,55.66029144,37.60975132,NULL,'2026-02-18 11:00:43'),(246,12,55.66034573,37.60967991,NULL,'2026-02-18 11:01:13'),(247,12,55.65991480,37.61008060,NULL,'2026-02-18 11:01:19'),(248,12,55.65991480,37.61008060,NULL,'2026-02-18 11:01:19'),(249,12,55.65991940,37.61008110,NULL,'2026-02-18 11:01:29'),(250,12,55.65991940,37.61008110,NULL,'2026-02-18 11:01:29'),(251,12,55.65993180,37.61023960,NULL,'2026-02-18 11:01:34'),(252,12,55.65993180,37.61023960,NULL,'2026-02-18 11:01:34'),(253,12,55.65993310,37.61042840,NULL,'2026-02-18 11:01:41'),(254,12,55.65993310,37.61042840,NULL,'2026-02-18 11:01:41'),(255,12,55.66037329,37.61001078,NULL,'2026-02-18 11:01:43'),(256,12,55.65997490,37.61013230,NULL,'2026-02-18 11:01:46'),(257,12,55.65997490,37.61013230,NULL,'2026-02-18 11:01:46'),(258,12,55.65994810,37.61044880,NULL,'2026-02-18 11:01:53'),(259,12,55.65994810,37.61044880,NULL,'2026-02-18 11:01:53'),(260,12,55.66037329,37.61001078,NULL,'2026-02-18 11:02:13'),(261,12,55.66018913,37.60977977,NULL,'2026-02-18 11:02:43'),(262,12,55.66027080,37.60990434,NULL,'2026-02-18 12:06:55'),(263,12,55.65973773,37.60725242,NULL,'2026-02-18 13:04:01'),(264,12,55.65973773,37.60725242,NULL,'2026-02-18 13:04:31'),(265,12,55.66017614,37.60940461,NULL,'2026-02-18 14:05:01'),(266,12,55.66017614,37.60940461,NULL,'2026-02-18 14:05:31'),(267,12,55.66016488,37.60969207,NULL,'2026-02-18 14:48:44'),(268,12,55.66016488,37.60969207,NULL,'2026-02-18 14:49:14'),(269,12,55.66022799,37.60988849,NULL,'2026-02-18 15:06:19'),(270,12,55.66023779,37.60990430,NULL,'2026-02-18 15:57:51'),(271,12,55.66031802,37.60989940,NULL,'2026-02-18 16:07:18'),(272,12,55.66022739,37.60990620,NULL,'2026-02-18 16:39:58'),(273,12,55.66031230,37.60990018,NULL,'2026-02-18 17:08:18'),(274,12,55.66048699,37.61001809,NULL,'2026-02-18 17:31:47'),(275,12,55.66048699,37.61001809,NULL,'2026-02-18 17:32:16'),(276,12,55.66035476,37.60990772,NULL,'2026-02-18 17:32:46'),(277,12,55.66035476,37.60990772,NULL,'2026-02-18 17:33:16'),(278,12,55.66036357,37.60991522,NULL,'2026-02-18 17:33:46'),(279,12,55.66024132,37.60990508,NULL,'2026-02-18 17:34:17'),(280,12,55.66024132,37.60990508,NULL,'2026-02-18 17:34:47'),(281,12,55.66049930,37.60961992,NULL,'2026-02-18 17:35:48'),(282,12,55.66049930,37.60961992,NULL,'2026-02-18 17:36:49'),(283,12,55.66045722,37.60999743,NULL,'2026-02-18 17:37:50'),(284,12,55.66043730,37.60976345,NULL,'2026-02-18 17:38:51'),(285,12,55.66039257,37.60989426,NULL,'2026-02-18 17:39:52'),(286,12,55.66046648,37.60999129,NULL,'2026-02-18 17:40:53'),(287,12,55.66057288,37.61013126,NULL,'2026-02-18 17:41:54'),(288,12,55.66057288,37.61013126,NULL,'2026-02-18 17:42:55'),(289,12,55.66033413,37.60989777,NULL,'2026-02-18 17:44:57'),(290,12,55.66023879,37.60990541,NULL,'2026-02-18 17:45:58'),(291,12,55.66024044,37.60990521,NULL,'2026-02-18 17:46:59'),(292,12,55.66031698,37.60989948,NULL,'2026-02-18 17:48:00'),(293,12,55.66028511,37.60988920,NULL,'2026-02-18 17:49:01'),(294,12,55.66052086,37.61036089,NULL,'2026-02-18 17:50:02'),(295,12,55.66030828,37.60976736,NULL,'2026-02-18 17:51:03'),(296,12,55.66035089,37.60992222,NULL,'2026-02-18 17:52:04'),(297,12,55.66043661,37.60998741,NULL,'2026-02-18 17:53:05'),(298,12,55.66057222,37.61013047,NULL,'2026-02-18 17:54:06'),(299,12,55.66057257,37.61013090,NULL,'2026-02-18 17:55:07'),(300,12,55.66057201,37.61013026,NULL,'2026-02-18 17:56:07'),(301,12,55.66039336,37.60989458,NULL,'2026-02-18 17:57:07'),(302,12,55.66041032,37.61008340,NULL,'2026-02-18 17:58:07'),(303,12,55.66041037,37.61008356,NULL,'2026-02-18 17:59:07'),(304,12,55.66039264,37.60989280,NULL,'2026-02-18 18:00:07'),(305,12,55.66039276,37.60989381,NULL,'2026-02-18 18:01:07'),(306,12,55.66039276,37.60989377,NULL,'2026-02-18 18:02:07'),(307,12,55.66039276,37.60989380,NULL,'2026-02-18 18:03:07'),(308,12,55.66031847,37.60989936,NULL,'2026-02-18 18:04:07'),(309,12,55.66034484,37.60989739,NULL,'2026-02-18 18:05:07'),(310,12,55.66039291,37.60989378,NULL,'2026-02-18 18:06:07'),(311,12,55.66024052,37.60990521,NULL,'2026-02-18 18:07:07'),(312,12,55.66044667,37.60958862,NULL,'2026-02-18 18:08:07'),(313,12,55.66031181,37.60977327,NULL,'2026-02-18 18:09:07'),(314,12,55.66044359,37.61006348,NULL,'2026-02-18 18:10:07'),(315,12,55.66022721,37.60980259,NULL,'2026-02-18 18:11:07'),(316,12,55.65992140,37.60955146,NULL,'2026-02-18 18:12:07'),(317,12,55.66024342,37.61028198,NULL,'2026-02-18 18:13:07'),(318,12,55.66032952,37.61005834,NULL,'2026-02-18 18:14:07'),(319,12,55.66039294,37.60989346,NULL,'2026-02-18 18:15:07'),(320,12,55.66003463,37.61045025,NULL,'2026-02-18 18:16:07'),(321,12,55.65978992,37.61064664,NULL,'2026-02-18 18:17:07'),(322,12,55.66037397,37.61004822,NULL,'2026-02-18 18:18:07'),(323,12,55.66036378,37.61009874,NULL,'2026-02-18 18:19:07'),(324,12,55.66039297,37.60989286,NULL,'2026-02-18 18:19:57'),(325,12,55.66039297,37.60989286,NULL,'2026-02-18 18:20:01'),(326,12,55.66039297,37.60989286,NULL,'2026-02-18 18:20:01'),(327,12,55.66039297,37.60989286,NULL,'2026-02-18 18:20:02'),(328,12,55.66039297,37.60989286,NULL,'2026-02-18 18:20:02'),(329,12,55.66039297,37.60989286,NULL,'2026-02-18 18:20:08'),(330,12,55.66039297,37.60989286,NULL,'2026-02-18 18:20:08'),(331,12,55.66039297,37.60989286,NULL,'2026-02-18 18:20:11'),(332,12,55.66039297,37.60989286,NULL,'2026-02-18 18:20:11'),(333,12,55.66039297,37.60989286,NULL,'2026-02-18 18:20:22'),(334,12,55.66039297,37.60989286,NULL,'2026-02-18 18:20:22'),(335,12,55.66039297,37.60989286,NULL,'2026-02-18 18:20:53'),(336,12,55.66039297,37.60989286,NULL,'2026-02-18 18:21:23'),(337,12,55.66039264,37.60989425,NULL,'2026-02-18 18:21:53'),(338,12,55.66039264,37.60989425,NULL,'2026-02-18 18:22:23'),(339,12,55.66039273,37.60989390,NULL,'2026-02-18 18:22:53'),(340,12,55.66039273,37.60989390,NULL,'2026-02-18 18:23:23'),(341,12,55.66039276,37.60989380,NULL,'2026-02-18 18:23:53'),(342,12,55.66070666,37.60938881,NULL,'2026-02-18 18:24:54'),(343,12,55.66024855,37.60989604,NULL,'2026-02-18 18:25:55'),(344,12,55.66024777,37.60989694,NULL,'2026-02-18 18:26:56'),(345,12,55.66031963,37.60989948,NULL,'2026-02-18 18:27:57'),(346,12,55.66038061,37.60995622,NULL,'2026-02-18 18:28:58'),(347,12,55.66039451,37.60993193,NULL,'2026-02-18 18:29:59'),(348,12,55.66039451,37.60993193,NULL,'2026-02-18 18:31:00'),(349,12,55.66023951,37.60990561,NULL,'2026-02-18 18:32:01'),(350,12,55.66031007,37.60980275,NULL,'2026-02-18 18:33:02'),(351,12,55.66025380,37.60988526,NULL,'2026-02-18 18:35:20'),(352,12,55.66031214,37.60998060,NULL,'2026-02-18 18:51:19'),(353,12,55.66024040,37.60990518,NULL,'2026-02-18 18:52:20'),(354,12,55.66006424,37.60916440,NULL,'2026-02-18 19:09:21'),(355,12,55.66013130,37.60898415,NULL,'2026-02-18 19:10:21'),(356,12,55.65930260,37.60549020,NULL,'2026-02-18 19:11:21'),(357,12,55.65930260,37.60549020,NULL,'2026-02-18 19:12:21'),(358,12,55.66057822,37.61010854,NULL,'2026-02-18 19:13:21'),(359,12,55.65988114,37.60860723,NULL,'2026-02-18 19:14:21'),(360,12,55.65987566,37.60873473,NULL,'2026-02-18 19:15:21'),(361,12,55.66022706,37.60986218,NULL,'2026-02-18 19:16:21'),(362,12,55.65987256,37.60803468,NULL,'2026-02-18 19:48:21'),(363,12,55.65995563,37.60820115,NULL,'2026-02-18 19:49:18'),(364,12,55.66026065,37.60954671,NULL,'2026-02-18 20:34:33'),(365,12,55.65992603,37.60863288,NULL,'2026-02-18 20:56:29'),(366,12,55.66021332,37.60986634,NULL,'2026-02-18 21:01:48'),(367,12,55.66024020,37.60990516,NULL,'2026-02-18 21:03:49'),(368,12,55.66024017,37.60990524,NULL,'2026-02-18 21:04:48'),(369,12,55.66024017,37.60990524,NULL,'2026-02-18 21:05:48'),(370,12,55.66024017,37.60990524,NULL,'2026-02-18 21:06:48'),(371,12,55.66024017,37.60990524,NULL,'2026-02-18 21:07:48'),(372,12,55.66024017,37.60990524,NULL,'2026-02-18 21:08:48'),(373,12,55.66039311,37.60989376,NULL,'2026-02-18 21:09:48'),(374,12,55.66039282,37.60989379,NULL,'2026-02-18 21:10:48'),(375,12,55.66039282,37.60989379,NULL,'2026-02-18 21:11:07'),(376,12,55.66039277,37.60989379,NULL,'2026-02-18 21:11:25'),(377,12,55.66039277,37.60989379,NULL,'2026-02-18 21:11:25'),(378,12,55.66039277,37.60989379,NULL,'2026-02-18 21:11:35'),(379,12,55.66039277,37.60989379,NULL,'2026-02-18 21:11:35'),(380,12,55.66039277,37.60989379,NULL,'2026-02-18 21:11:37'),(381,12,55.66039277,37.60989379,NULL,'2026-02-18 21:11:37'),(382,12,55.66039277,37.60989379,NULL,'2026-02-18 21:11:40'),(383,12,55.66039277,37.60989379,NULL,'2026-02-18 21:11:40'),(384,12,55.66039277,37.60989379,NULL,'2026-02-18 21:11:42'),(385,12,55.66039277,37.60989379,NULL,'2026-02-18 21:11:42'),(386,12,55.66039277,37.60989379,NULL,'2026-02-18 21:11:44'),(387,12,55.66039277,37.60989379,NULL,'2026-02-18 21:11:44'),(388,12,55.66039277,37.60989379,NULL,'2026-02-18 21:11:49'),(389,12,55.66039277,37.60989379,NULL,'2026-02-18 21:11:49'),(390,12,55.66054380,37.61006372,NULL,'2026-02-18 21:12:19'),(391,12,55.66054380,37.61006372,NULL,'2026-02-18 21:12:49'),(392,12,55.66057554,37.61009943,NULL,'2026-02-18 21:13:19'),(393,12,55.66057554,37.61009943,NULL,'2026-02-18 21:13:49'),(394,12,55.66057554,37.61009943,NULL,'2026-02-18 21:14:19'),(395,12,55.66057584,37.61009977,NULL,'2026-02-18 21:16:20'),(396,12,55.66057586,37.61009979,NULL,'2026-02-18 21:17:21'),(397,12,55.66047823,37.60998995,NULL,'2026-02-18 21:18:22'),(398,12,55.66038965,37.60989030,NULL,'2026-02-18 21:19:23'),(399,12,55.66046963,37.60998027,NULL,'2026-02-18 21:20:24'),(400,12,55.66057645,37.61010045,NULL,'2026-02-18 21:21:25'),(401,12,55.66024378,37.61028316,NULL,'2026-02-18 21:22:26'),(402,12,55.66045007,37.61016917,NULL,'2026-02-18 21:23:27'),(403,12,55.66021764,37.60986823,NULL,'2026-02-18 21:24:28'),(404,12,55.66021764,37.60986823,NULL,'2026-02-18 21:24:35'),(405,12,55.66021764,37.60986823,NULL,'2026-02-18 21:24:35'),(406,12,55.66021764,37.60986823,NULL,'2026-02-18 21:24:37'),(407,12,55.66021764,37.60986823,NULL,'2026-02-18 21:24:37'),(408,12,55.66021764,37.60986823,NULL,'2026-02-18 21:24:43'),(409,12,55.66021764,37.60986823,NULL,'2026-02-18 21:24:43'),(410,12,55.66021764,37.60986823,NULL,'2026-02-18 21:24:45'),(411,12,55.66021764,37.60986823,NULL,'2026-02-18 21:24:45'),(412,12,55.66021764,37.60986823,NULL,'2026-02-18 21:24:57'),(413,12,55.66021764,37.60986823,NULL,'2026-02-18 21:24:57'),(414,12,55.66021764,37.60986823,NULL,'2026-02-18 21:25:00'),(415,12,55.66021764,37.60986823,NULL,'2026-02-18 21:25:00'),(416,12,55.66021764,37.60986823,NULL,'2026-02-18 21:25:03'),(417,12,55.66021764,37.60986823,NULL,'2026-02-18 21:25:03'),(418,12,55.66021764,37.60986823,NULL,'2026-02-18 21:25:05'),(419,12,55.66021764,37.60986823,NULL,'2026-02-18 21:25:05'),(420,12,55.66021764,37.60986823,NULL,'2026-02-18 21:25:07'),(421,12,55.66021764,37.60986823,NULL,'2026-02-18 21:25:07'),(422,12,55.66039354,37.60992232,NULL,'2026-02-18 21:25:12'),(423,12,55.66039354,37.60992232,NULL,'2026-02-18 21:25:12'),(424,12,55.66039354,37.60992232,NULL,'2026-02-18 21:25:19'),(425,12,55.66039354,37.60992232,NULL,'2026-02-18 21:25:19'),(426,12,55.66039354,37.60992232,NULL,'2026-02-18 21:25:20'),(427,12,55.66039354,37.60992232,NULL,'2026-02-18 21:25:20'),(428,12,55.66039354,37.60992232,NULL,'2026-02-18 21:25:31'),(429,12,55.66039354,37.60992232,NULL,'2026-02-18 21:25:31'),(430,12,55.66039354,37.60992232,NULL,'2026-02-18 21:25:32'),(431,12,55.66039354,37.60992232,NULL,'2026-02-18 21:25:32'),(432,12,55.66039354,37.60992232,NULL,'2026-02-18 21:25:34'),(433,12,55.66039354,37.60992232,NULL,'2026-02-18 21:25:34'),(434,12,55.66039354,37.60992232,NULL,'2026-02-18 21:25:38'),(435,12,55.66039354,37.60992232,NULL,'2026-02-18 21:25:38'),(436,12,55.66039354,37.60992232,NULL,'2026-02-18 21:25:41'),(437,12,55.66039354,37.60992232,NULL,'2026-02-18 21:25:41'),(438,12,55.66039354,37.60992232,NULL,'2026-02-18 21:25:42'),(439,12,55.66039354,37.60992232,NULL,'2026-02-18 21:25:42'),(440,12,55.66039354,37.60992232,NULL,'2026-02-18 21:25:44'),(441,12,55.66039354,37.60992232,NULL,'2026-02-18 21:25:44'),(442,12,55.66024019,37.60990477,NULL,'2026-02-18 22:07:56'),(443,12,55.66024019,37.60990477,NULL,'2026-02-18 22:07:56'),(444,12,55.66024019,37.60990477,NULL,'2026-02-18 22:08:01'),(445,12,55.66024019,37.60990477,NULL,'2026-02-18 22:08:01'),(446,12,55.66024019,37.60990477,NULL,'2026-02-18 22:08:07'),(447,12,55.66024019,37.60990477,NULL,'2026-02-18 22:08:07'),(448,12,55.66024019,37.60990477,NULL,'2026-02-18 22:08:08'),(449,12,55.66024019,37.60990477,NULL,'2026-02-18 22:08:08'),(450,12,55.66024019,37.60990477,NULL,'2026-02-18 22:08:14'),(451,12,55.66024019,37.60990477,NULL,'2026-02-18 22:08:14'),(452,12,55.66024019,37.60990477,NULL,'2026-02-18 22:08:16'),(453,12,55.66024019,37.60990477,NULL,'2026-02-18 22:08:16'),(454,12,55.66024019,37.60990477,NULL,'2026-02-18 22:08:17'),(455,12,55.66024019,37.60990477,NULL,'2026-02-18 22:08:17'),(456,12,55.66024019,37.60990477,NULL,'2026-02-18 22:08:23'),(457,12,55.66024019,37.60990477,NULL,'2026-02-18 22:08:23'),(458,12,55.66023990,37.60990544,NULL,'2026-02-18 22:08:27'),(459,12,55.66023990,37.60990544,NULL,'2026-02-18 22:08:27'),(460,12,55.66023990,37.60990544,NULL,'2026-02-18 22:08:30'),(461,12,55.66023990,37.60990544,NULL,'2026-02-18 22:08:30'),(462,12,55.66023990,37.60990544,NULL,'2026-02-18 22:08:39'),(463,12,55.66023990,37.60990544,NULL,'2026-02-18 22:08:39'),(464,12,55.66023990,37.60990544,NULL,'2026-02-18 22:08:43'),(465,12,55.66023990,37.60990544,NULL,'2026-02-18 22:08:43'),(466,12,55.66023990,37.60990544,NULL,'2026-02-18 22:08:45'),(467,12,55.66023990,37.60990544,NULL,'2026-02-18 22:08:45'),(468,12,55.66023990,37.60990544,NULL,'2026-02-18 22:08:50'),(469,12,55.66023990,37.60990544,NULL,'2026-02-18 22:08:50'),(470,12,55.66023990,37.60990544,NULL,'2026-02-18 22:08:51'),(471,12,55.66023990,37.60990544,NULL,'2026-02-18 22:08:51'),(472,12,55.66023990,37.60990544,NULL,'2026-02-18 22:08:57'),(473,12,55.66023990,37.60990544,NULL,'2026-02-18 22:08:57'),(474,12,55.66023990,37.60990544,NULL,'2026-02-18 22:09:00'),(475,12,55.66023990,37.60990544,NULL,'2026-02-18 22:09:00'),(476,12,55.66035599,37.60989656,NULL,'2026-02-18 22:09:30'),(477,12,55.66035599,37.60989656,NULL,'2026-02-18 22:09:41'),(478,12,55.66035599,37.60989656,NULL,'2026-02-18 22:09:41'),(479,12,55.66035599,37.60989656,NULL,'2026-02-18 22:09:42'),(480,12,55.66035599,37.60989656,NULL,'2026-02-18 22:09:42'),(481,12,55.66035599,37.60989656,NULL,'2026-02-18 22:09:44'),(482,12,55.66035599,37.60989656,NULL,'2026-02-18 22:09:44'),(483,12,55.66035599,37.60989656,NULL,'2026-02-18 22:09:45'),(484,12,55.66035599,37.60989656,NULL,'2026-02-18 22:09:45'),(485,12,55.66035599,37.60989656,NULL,'2026-02-18 22:10:07'),(486,12,55.66035599,37.60989656,NULL,'2026-02-18 22:10:07'),(487,12,55.66035599,37.60989656,NULL,'2026-02-18 22:10:22'),(488,12,55.66035599,37.60989656,NULL,'2026-02-18 22:10:22'),(489,12,55.66035599,37.60989656,NULL,'2026-02-18 22:10:26'),(490,12,55.66035599,37.60989656,NULL,'2026-02-18 22:10:26'),(491,12,55.66039280,37.60989379,NULL,'2026-02-18 22:10:29'),(492,12,55.66039280,37.60989379,NULL,'2026-02-18 22:10:29'),(493,12,55.66039280,37.60989379,NULL,'2026-02-18 22:10:30'),(494,12,55.66039280,37.60989379,NULL,'2026-02-18 22:10:30'),(495,12,55.66039280,37.60989379,NULL,'2026-02-18 22:10:31'),(496,12,55.66039280,37.60989379,NULL,'2026-02-18 22:10:31'),(497,12,55.66039280,37.60989379,NULL,'2026-02-18 22:10:39'),(498,12,55.66039280,37.60989379,NULL,'2026-02-18 22:10:39'),(499,12,55.66039280,37.60989379,NULL,'2026-02-18 22:10:47'),(500,12,55.66039280,37.60989379,NULL,'2026-02-18 22:10:47'),(501,12,55.66039280,37.60989379,NULL,'2026-02-18 22:10:48'),(502,12,55.66039280,37.60989379,NULL,'2026-02-18 22:10:48'),(503,12,55.66039280,37.60989379,NULL,'2026-02-18 22:10:52'),(504,12,55.66039280,37.60989379,NULL,'2026-02-18 22:10:52'),(505,12,55.66039280,37.60989379,NULL,'2026-02-18 22:10:56'),(506,12,55.66039280,37.60989379,NULL,'2026-02-18 22:10:56'),(507,12,55.66039280,37.60989379,NULL,'2026-02-18 22:10:57'),(508,12,55.66039280,37.60989379,NULL,'2026-02-18 22:10:57'),(509,12,55.66039280,37.60989379,NULL,'2026-02-18 22:11:00'),(510,12,55.66039280,37.60989379,NULL,'2026-02-18 22:11:01'),(511,12,55.66039280,37.60989379,NULL,'2026-02-18 22:11:02'),(512,12,55.66039280,37.60989379,NULL,'2026-02-18 22:11:02'),(513,12,55.66039280,37.60989379,NULL,'2026-02-18 22:11:04'),(514,12,55.66039280,37.60989379,NULL,'2026-02-18 22:11:04'),(515,12,55.66039280,37.60989379,NULL,'2026-02-18 22:11:12'),(516,12,55.66039280,37.60989379,NULL,'2026-02-18 22:11:12'),(517,12,55.66039280,37.60989379,NULL,'2026-02-18 22:11:17'),(518,12,55.66039280,37.60989379,NULL,'2026-02-18 22:11:17'),(519,12,55.66039280,37.60989379,NULL,'2026-02-18 22:11:26'),(520,12,55.66039280,37.60989379,NULL,'2026-02-18 22:11:26'),(521,12,55.66030453,37.61013327,NULL,'2026-02-18 22:11:27'),(522,12,55.66030453,37.61013327,NULL,'2026-02-18 22:11:27'),(523,12,55.66016286,37.60980499,NULL,'2026-02-18 22:11:29'),(524,12,55.66016286,37.60980499,NULL,'2026-02-18 22:11:29'),(525,12,55.66016286,37.60980499,NULL,'2026-02-18 22:11:36'),(526,12,55.66016286,37.60980499,NULL,'2026-02-18 22:11:36'),(527,12,55.66016286,37.60980499,NULL,'2026-02-18 22:11:38'),(528,12,55.66016286,37.60980499,NULL,'2026-02-18 22:11:38'),(529,12,55.66016286,37.60980499,NULL,'2026-02-18 22:11:41'),(530,12,55.66016286,37.60980499,NULL,'2026-02-18 22:11:41'),(531,12,55.66016286,37.60980499,NULL,'2026-02-18 22:11:48'),(532,12,55.66016286,37.60980499,NULL,'2026-02-18 22:11:48'),(533,12,55.66016286,37.60980499,NULL,'2026-02-18 22:11:50'),(534,12,55.66016286,37.60980499,NULL,'2026-02-18 22:11:50'),(535,12,55.66016286,37.60980499,NULL,'2026-02-18 22:11:52'),(536,12,55.66016286,37.60980499,NULL,'2026-02-18 22:11:52'),(537,12,55.66016286,37.60980499,NULL,'2026-02-18 22:11:59'),(538,12,55.66016286,37.60980499,NULL,'2026-02-18 22:11:59'),(539,12,55.66016286,37.60980499,NULL,'2026-02-18 22:12:03'),(540,12,55.66016286,37.60980499,NULL,'2026-02-18 22:12:03'),(541,7,55.66016286,37.60980499,NULL,'2026-02-18 22:12:14'),(542,7,55.66016286,37.60980499,NULL,'2026-02-18 22:12:14'),(543,7,55.66016286,37.60980499,NULL,'2026-02-18 22:12:19'),(544,7,55.66016286,37.60980499,NULL,'2026-02-18 22:12:19'),(545,7,55.66016286,37.60980499,NULL,'2026-02-18 22:12:20'),(546,7,55.66016286,37.60980499,NULL,'2026-02-18 22:12:20'),(547,7,55.66016286,37.60980499,NULL,'2026-02-18 22:12:26'),(548,7,55.66016286,37.60980499,NULL,'2026-02-18 22:12:26'),(549,7,55.66077059,37.61066777,NULL,'2026-02-18 22:12:30'),(550,7,55.66077059,37.61066777,NULL,'2026-02-18 22:12:31'),(551,7,55.66077059,37.61066777,NULL,'2026-02-18 22:12:35'),(552,7,55.66077059,37.61066777,NULL,'2026-02-18 22:12:35'),(553,7,55.66077059,37.61066777,NULL,'2026-02-18 22:12:47'),(554,7,55.66077059,37.61066777,NULL,'2026-02-18 22:12:47'),(555,7,55.66077059,37.61066777,NULL,'2026-02-18 22:12:55'),(556,7,55.66077059,37.61066777,NULL,'2026-02-18 22:12:55'),(557,7,55.66077059,37.61066777,NULL,'2026-02-18 22:13:05'),(558,7,55.66077059,37.61066777,NULL,'2026-02-18 22:13:05'),(559,7,55.66077059,37.61066777,NULL,'2026-02-18 22:13:07'),(560,7,55.66077059,37.61066777,NULL,'2026-02-18 22:13:07'),(561,7,55.66067701,37.61047424,NULL,'2026-02-18 22:13:37'),(562,7,55.66067701,37.61047424,NULL,'2026-02-18 22:14:07'),(563,7,55.66033736,37.60985244,NULL,'2026-02-18 22:14:37'),(564,7,55.66033736,37.60985244,NULL,'2026-02-18 22:14:57'),(565,7,55.66033736,37.60985244,NULL,'2026-02-18 22:14:57'),(566,7,55.66033736,37.60985244,NULL,'2026-02-18 22:14:58'),(567,7,55.66033736,37.60985244,NULL,'2026-02-18 22:14:58'),(568,7,55.66033736,37.60985244,NULL,'2026-02-18 22:14:59'),(569,7,55.66033736,37.60985244,NULL,'2026-02-18 22:14:59'),(570,7,55.66033736,37.60985244,NULL,'2026-02-18 22:15:00'),(571,7,55.66033736,37.60985244,NULL,'2026-02-18 22:15:00'),(572,7,55.66033736,37.60985244,NULL,'2026-02-18 22:15:11'),(573,7,55.66033736,37.60985244,NULL,'2026-02-18 22:15:11'),(574,7,55.65992970,37.61044380,NULL,'2026-02-18 22:16:59'),(575,7,55.65992970,37.61044380,NULL,'2026-02-18 22:16:59'),(576,7,55.65999400,37.61012770,NULL,'2026-02-18 22:17:13'),(577,7,55.65999400,37.61012770,NULL,'2026-02-18 22:17:13'),(578,7,55.65995990,37.61039560,NULL,'2026-02-18 22:17:18'),(579,7,55.65995990,37.61039560,NULL,'2026-02-18 22:17:18'),(580,7,55.66000790,37.61004240,NULL,'2026-02-18 22:17:26'),(581,7,55.66000790,37.61004240,NULL,'2026-02-18 22:17:26'),(582,7,55.65997550,37.61032950,NULL,'2026-02-18 22:17:31'),(583,7,55.65997550,37.61032950,NULL,'2026-02-18 22:17:31'),(584,7,55.65990970,37.61044140,NULL,'2026-02-18 22:17:38'),(585,7,55.65990970,37.61044140,NULL,'2026-02-18 22:17:38'),(586,7,55.65991100,37.61044680,NULL,'2026-02-18 22:17:43'),(587,7,55.65991100,37.61044680,NULL,'2026-02-18 22:17:43'),(588,7,55.65992350,37.61043280,NULL,'2026-02-18 22:18:05'),(589,7,55.65992350,37.61043280,NULL,'2026-02-18 22:18:05'),(590,7,55.65991800,37.61018820,NULL,'2026-02-18 22:18:30'),(591,7,55.65991800,37.61018820,NULL,'2026-02-18 22:18:30'),(592,7,55.65992350,37.61043280,NULL,'2026-02-18 22:18:32'),(593,7,55.66010152,37.60959783,NULL,'2026-02-19 06:27:59'),(594,7,55.66010152,37.60959783,NULL,'2026-02-19 06:27:59'),(595,7,55.66010152,37.60959783,NULL,'2026-02-19 06:28:07'),(596,7,55.66010152,37.60959783,NULL,'2026-02-19 06:28:07'),(597,7,55.66010152,37.60959783,NULL,'2026-02-19 06:28:16'),(598,7,55.66010152,37.60959783,NULL,'2026-02-19 06:28:16'),(599,7,55.66003435,37.60943487,NULL,'2026-02-19 06:28:46'),(600,7,55.66032377,37.60991332,NULL,'2026-02-19 06:29:16'),(601,7,55.66032377,37.60991332,NULL,'2026-02-19 06:29:46'),(602,7,55.66031770,37.60990356,NULL,'2026-02-19 06:30:16'),(603,7,55.66031770,37.60990356,NULL,'2026-02-19 06:30:46'),(604,7,55.66031770,37.60990356,NULL,'2026-02-19 06:30:49'),(605,7,55.66031770,37.60990356,NULL,'2026-02-19 06:30:49'),(606,7,55.66031770,37.60990356,NULL,'2026-02-19 06:30:54'),(607,7,55.66031770,37.60990356,NULL,'2026-02-19 06:30:54'),(608,7,55.66029334,37.60958331,NULL,'2026-02-19 06:31:11'),(609,7,55.66029334,37.60958331,NULL,'2026-02-19 06:31:11'),(610,7,55.66029334,37.60958331,NULL,'2026-02-19 06:31:21'),(611,7,55.66029334,37.60958331,NULL,'2026-02-19 06:31:21'),(612,7,55.66029334,37.60958331,NULL,'2026-02-19 06:31:44'),(613,7,55.66029334,37.60958331,NULL,'2026-02-19 06:31:44'),(614,7,55.66029334,37.60958331,NULL,'2026-02-19 06:31:49'),(615,7,55.66029334,37.60958331,NULL,'2026-02-19 06:31:49'),(616,0,55.66029334,37.60958331,NULL,'2026-02-19 06:31:52'),(617,0,55.66029334,37.60958331,NULL,'2026-02-19 06:31:52'),(618,0,55.65996755,37.60929553,NULL,'2026-02-19 06:32:16'),(619,0,55.65996755,37.60929553,NULL,'2026-02-19 06:32:16'),(620,0,55.65996755,37.60929553,NULL,'2026-02-19 06:32:20'),(621,0,55.65996755,37.60929553,NULL,'2026-02-19 06:32:24'),(622,0,55.65996755,37.60929553,NULL,'2026-02-19 06:32:24'),(623,0,55.65996755,37.60929553,NULL,'2026-02-19 06:32:29'),(624,0,55.65996755,37.60929553,NULL,'2026-02-19 06:32:29'),(625,0,55.65996755,37.60929553,NULL,'2026-02-19 06:32:39'),(626,0,55.65996755,37.60929553,NULL,'2026-02-19 06:32:39'),(627,0,55.65996755,37.60929553,NULL,'2026-02-19 06:32:49'),(628,0,55.65996755,37.60929553,NULL,'2026-02-19 06:32:49'),(629,0,55.65996755,37.60929553,NULL,'2026-02-19 06:32:56'),(630,0,55.65996755,37.60929553,NULL,'2026-02-19 06:32:56'),(631,0,55.66039411,37.60990716,NULL,'2026-02-19 06:33:00'),(632,0,55.66039411,37.60990716,NULL,'2026-02-19 06:33:00'),(633,0,55.66039743,37.60991151,NULL,'2026-02-19 06:33:02'),(634,0,55.66039743,37.60991151,NULL,'2026-02-19 06:33:02'),(635,0,55.66039743,37.60991151,NULL,'2026-02-19 06:33:16'),(636,0,55.66039743,37.60991151,NULL,'2026-02-19 06:33:16'),(637,0,55.66039743,37.60991151,NULL,'2026-02-19 06:33:19'),(638,0,55.66039743,37.60991151,NULL,'2026-02-19 06:33:19'),(639,0,55.66039743,37.60991151,NULL,'2026-02-19 06:33:28'),(640,0,55.66039743,37.60991151,NULL,'2026-02-19 06:33:28'),(641,0,55.66039743,37.60991151,NULL,'2026-02-19 06:33:58'),(642,0,55.66039262,37.60990506,NULL,'2026-02-19 06:34:06'),(643,0,55.66039262,37.60990506,NULL,'2026-02-19 06:34:06'),(644,0,55.66039262,37.60990506,NULL,'2026-02-19 06:34:11'),(645,0,55.66039262,37.60990506,NULL,'2026-02-19 06:34:11'),(646,7,55.66039262,37.60990506,NULL,'2026-02-19 06:34:22'),(647,7,55.66039262,37.60990506,NULL,'2026-02-19 06:34:22'),(648,7,55.66039262,37.60990506,NULL,'2026-02-19 06:34:27'),(649,7,55.66039262,37.60990506,NULL,'2026-02-19 06:34:27'),(650,7,55.66039262,37.60990506,NULL,'2026-02-19 06:34:31'),(651,7,55.66039262,37.60990506,NULL,'2026-02-19 06:34:31'),(652,7,55.66033389,37.60990531,NULL,'2026-02-19 06:34:57'),(653,7,55.66033389,37.60990531,NULL,'2026-02-19 06:34:57'),(654,7,55.66033389,37.60990531,NULL,'2026-02-19 06:35:04'),(655,7,55.66033389,37.60990531,NULL,'2026-02-19 06:35:04'),(656,7,55.66033389,37.60990531,NULL,'2026-02-19 06:35:06'),(657,7,55.66033389,37.60990531,NULL,'2026-02-19 06:35:06'),(658,7,55.66033389,37.60990531,NULL,'2026-02-19 06:35:10'),(659,7,55.66033389,37.60990531,NULL,'2026-02-19 06:35:10'),(660,7,55.66033389,37.60990531,NULL,'2026-02-19 06:35:12'),(661,7,55.66033389,37.60990531,NULL,'2026-02-19 06:35:12'),(662,7,55.66033389,37.60990531,NULL,'2026-02-19 06:35:18'),(663,7,55.66033389,37.60990531,NULL,'2026-02-19 06:35:18'),(664,7,55.66033389,37.60990531,NULL,'2026-02-19 06:35:19'),(665,7,55.66033389,37.60990531,NULL,'2026-02-19 06:35:19'),(666,7,55.66033389,37.60990531,NULL,'2026-02-19 06:35:20'),(667,7,55.66033389,37.60990531,NULL,'2026-02-19 06:35:20'),(668,7,55.66033389,37.60990531,NULL,'2026-02-19 06:35:22'),(669,7,55.66033389,37.60990531,NULL,'2026-02-19 06:35:22'),(670,7,55.66033389,37.60990531,NULL,'2026-02-19 06:35:34'),(671,7,55.66033389,37.60990531,NULL,'2026-02-19 06:35:34'),(672,7,55.66033389,37.60990531,NULL,'2026-02-19 06:35:35'),(673,7,55.66033389,37.60990531,NULL,'2026-02-19 06:35:35'),(674,7,55.66031930,37.60990515,NULL,'2026-02-19 06:35:54'),(675,7,55.66031930,37.60990515,NULL,'2026-02-19 06:35:54'),(676,7,55.66031930,37.60990515,NULL,'2026-02-19 06:35:59'),(677,7,55.66031930,37.60990515,NULL,'2026-02-19 06:35:59'),(678,7,55.66031930,37.60990515,NULL,'2026-02-19 06:36:03'),(679,7,55.66031930,37.60990515,NULL,'2026-02-19 06:36:03'),(680,7,55.66031930,37.60990515,NULL,'2026-02-19 06:36:06'),(681,7,55.66031930,37.60990515,NULL,'2026-02-19 06:36:06'),(682,7,55.66031930,37.60990515,NULL,'2026-02-19 06:36:10'),(683,7,55.66031930,37.60990515,NULL,'2026-02-19 06:36:10'),(684,7,55.66031930,37.60990515,NULL,'2026-02-19 06:36:14'),(685,7,55.66031930,37.60990515,NULL,'2026-02-19 06:36:14'),(686,7,55.66031930,37.60990515,NULL,'2026-02-19 06:36:20'),(687,7,55.66031930,37.60990515,NULL,'2026-02-19 06:36:20'),(688,7,55.66031930,37.60990515,NULL,'2026-02-19 06:36:22'),(689,7,55.66031930,37.60990515,NULL,'2026-02-19 06:36:22'),(690,7,55.66010298,37.60948927,NULL,'2026-02-19 06:36:52'),(691,7,55.66010298,37.60948927,NULL,'2026-02-19 06:37:23'),(692,7,55.66033327,37.60981488,NULL,'2026-02-19 06:37:52'),(693,7,55.66033327,37.60981488,NULL,'2026-02-19 06:38:22'),(694,7,55.66039140,37.60990284,NULL,'2026-02-19 06:38:52'),(695,7,55.66039140,37.60990284,NULL,'2026-02-19 06:39:22'),(696,7,55.66027784,37.61019743,NULL,'2026-02-19 06:39:52'),(697,7,55.66027784,37.61019743,NULL,'2026-02-19 06:40:22'),(698,7,55.66037613,37.60994645,NULL,'2026-02-19 06:40:52'),(699,7,55.66037613,37.60994645,NULL,'2026-02-19 06:41:22'),(700,7,55.66034646,37.60987227,NULL,'2026-02-19 06:41:52'),(701,7,55.66034646,37.60987227,NULL,'2026-02-19 06:42:22'),(702,7,55.66052434,37.60977184,NULL,'2026-02-19 06:42:52'),(703,7,55.66052434,37.60977184,NULL,'2026-02-19 06:43:16'),(704,7,55.66052434,37.60977184,NULL,'2026-02-19 06:43:16'),(705,7,55.66052434,37.60977184,NULL,'2026-02-19 06:43:20'),(706,7,55.66052434,37.60977184,NULL,'2026-02-19 06:43:20'),(707,7,55.66052434,37.60977184,NULL,'2026-02-19 06:43:21'),(708,7,55.66052434,37.60977184,NULL,'2026-02-19 06:43:21'),(709,7,55.66052434,37.60977184,NULL,'2026-02-19 06:43:22'),(710,7,55.66052434,37.60977184,NULL,'2026-02-19 06:43:22'),(711,7,55.66052434,37.60977184,NULL,'2026-02-19 06:43:28'),(712,7,55.66052434,37.60977184,NULL,'2026-02-19 06:43:28'),(713,7,55.66052434,37.60977184,NULL,'2026-02-19 06:43:29'),(714,7,55.66052434,37.60977184,NULL,'2026-02-19 06:43:29'),(715,7,55.66052434,37.60977184,NULL,'2026-02-19 06:43:31'),(716,7,55.66052434,37.60977184,NULL,'2026-02-19 06:43:31'),(717,7,55.66052434,37.60977184,NULL,'2026-02-19 06:43:33'),(718,7,55.66052434,37.60977184,NULL,'2026-02-19 06:43:33'),(719,7,55.66043227,37.60984402,NULL,'2026-02-19 06:44:03'),(720,7,55.66043227,37.60984402,NULL,'2026-02-19 06:44:33'),(721,7,55.66039270,37.60990531,NULL,'2026-02-19 06:44:43'),(722,7,55.66039270,37.60990531,NULL,'2026-02-19 06:44:43'),(723,7,55.66039270,37.60990531,NULL,'2026-02-19 06:45:13'),(724,7,55.66039329,37.60990426,NULL,'2026-02-19 06:45:43'),(725,7,55.66039234,37.60990602,NULL,'2026-02-19 06:45:46'),(726,7,55.66039234,37.60990602,NULL,'2026-02-19 06:45:46'),(727,7,55.66039234,37.60990602,NULL,'2026-02-19 06:45:48'),(728,7,55.66039234,37.60990602,NULL,'2026-02-19 06:45:48'),(729,7,55.66039234,37.60990602,NULL,'2026-02-19 06:45:49'),(730,7,55.66039234,37.60990602,NULL,'2026-02-19 06:45:49'),(731,7,55.66032943,37.61005598,NULL,'2026-02-19 06:54:10'),(732,7,55.66032943,37.61005598,NULL,'2026-02-19 06:54:10'),(733,7,55.66032943,37.61005598,NULL,'2026-02-19 06:54:12'),(734,7,55.66032943,37.61005598,NULL,'2026-02-19 06:54:12'),(735,7,55.66032943,37.61005598,NULL,'2026-02-19 06:54:13'),(736,7,55.66032943,37.61005598,NULL,'2026-02-19 06:54:13'),(737,7,55.66032943,37.61005598,NULL,'2026-02-19 06:54:15'),(738,7,55.66032943,37.61005598,NULL,'2026-02-19 06:54:15'),(739,7,55.66032943,37.61005598,NULL,'2026-02-19 06:54:22'),(740,7,55.66032943,37.61005598,NULL,'2026-02-19 06:54:22'),(741,7,55.66032943,37.61005598,NULL,'2026-02-19 06:54:23'),(742,7,55.66032943,37.61005598,NULL,'2026-02-19 06:54:23'),(743,7,55.66033259,37.61005847,NULL,'2026-02-19 06:54:53'),(744,7,55.66033259,37.61005847,NULL,'2026-02-19 06:55:23'),(745,7,55.66021332,37.60883180,NULL,'2026-02-19 06:55:53'),(746,7,55.66021332,37.60883180,NULL,'2026-02-19 06:56:23'),(747,7,55.66052468,37.60934107,NULL,'2026-02-19 06:56:53'),(748,7,55.66052468,37.60934107,NULL,'2026-02-19 06:57:23'),(749,7,55.66053675,37.60948120,NULL,'2026-02-19 06:57:53'),(750,7,55.66053675,37.60948120,NULL,'2026-02-19 06:58:23'),(751,7,55.66031554,37.60947400,NULL,'2026-02-19 06:58:53'),(752,7,55.66031554,37.60947400,NULL,'2026-02-19 06:59:23'),(753,7,55.66038940,37.60990196,NULL,'2026-02-19 06:59:53'),(754,7,55.66038940,37.60990196,NULL,'2026-02-19 07:00:23'),(755,7,55.66039370,37.60990663,NULL,'2026-02-19 07:00:53'),(756,7,55.66039370,37.60990663,NULL,'2026-02-19 07:01:23'),(757,7,55.66039370,37.60990663,NULL,'2026-02-19 07:01:53'),(758,7,55.66039370,37.60990663,NULL,'2026-02-19 07:02:23'),(759,13,55.66033225,37.60990544,NULL,'2026-02-19 07:02:37'),(760,13,55.66033225,37.60990544,NULL,'2026-02-19 07:02:37'),(761,13,55.66033225,37.60990544,NULL,'2026-02-19 07:02:41'),(762,13,55.66033225,37.60990544,NULL,'2026-02-19 07:02:41'),(763,13,55.66033225,37.60990544,NULL,'2026-02-19 07:02:42'),(764,13,55.66033225,37.60990544,NULL,'2026-02-19 07:02:42'),(765,13,55.66033225,37.60990544,NULL,'2026-02-19 07:02:47'),(766,13,55.66033225,37.60990544,NULL,'2026-02-19 07:02:47'),(767,7,55.66033225,37.60990544,NULL,'2026-02-19 07:02:53'),(768,13,55.66033225,37.60990544,NULL,'2026-02-19 07:03:17'),(769,7,55.66033225,37.60990544,NULL,'2026-02-19 07:03:23'),(770,13,55.66033225,37.60990544,NULL,'2026-02-19 07:03:47'),(771,7,55.66031942,37.60990519,NULL,'2026-02-19 07:03:53'),(772,13,55.66031942,37.60990519,NULL,'2026-02-19 07:04:17'),(773,7,55.66031942,37.60990519,NULL,'2026-02-19 07:04:23'),(774,7,55.65992670,37.61006780,NULL,'2026-02-19 07:04:29'),(775,7,55.65992670,37.61006780,NULL,'2026-02-19 07:04:29'),(776,7,55.65992590,37.61038860,NULL,'2026-02-19 07:04:36'),(777,7,55.65992590,37.61038860,NULL,'2026-02-19 07:04:36'),(778,7,55.65994780,37.61024470,NULL,'2026-02-19 07:04:43'),(779,7,55.65994780,37.61024470,NULL,'2026-02-19 07:04:43'),(780,13,55.66031942,37.60990519,NULL,'2026-02-19 07:04:47'),(781,7,55.65994320,37.61027240,NULL,'2026-02-19 07:04:48'),(782,7,55.65994320,37.61027240,NULL,'2026-02-19 07:04:48'),(783,7,55.66010121,37.60945900,NULL,'2026-02-19 07:04:53'),(784,13,55.66010121,37.60945900,NULL,'2026-02-19 07:05:17'),(785,7,55.66010121,37.60945900,NULL,'2026-02-19 07:05:23'),(786,7,55.65993970,37.61015070,NULL,'2026-02-19 07:05:25'),(787,7,55.65994710,37.61035190,NULL,'2026-02-19 07:05:32'),(788,7,55.65994710,37.61035190,NULL,'2026-02-19 07:05:32'),(789,13,55.66029974,37.60999404,NULL,'2026-02-19 07:05:48'),(790,7,55.65993340,37.61046130,NULL,'2026-02-19 07:05:52'),(791,7,55.65993340,37.61046130,NULL,'2026-02-19 07:05:52'),(792,7,55.66029974,37.60999404,NULL,'2026-02-19 07:05:53'),(793,7,55.65996010,37.61036050,NULL,'2026-02-19 07:06:06'),(794,7,55.65996010,37.61036050,NULL,'2026-02-19 07:06:06'),(795,13,55.66029974,37.60999404,NULL,'2026-02-19 07:06:17'),(796,7,55.66029974,37.60999404,NULL,'2026-02-19 07:06:23'),(797,7,55.65996010,37.61036050,NULL,'2026-02-19 07:06:33'),(798,7,55.65992550,37.61044570,NULL,'2026-02-19 07:06:41'),(799,7,55.65992550,37.61044570,NULL,'2026-02-19 07:06:41'),(800,13,55.66039248,37.60990323,NULL,'2026-02-19 07:06:47'),(801,7,55.66001410,37.61001670,NULL,'2026-02-19 07:06:48'),(802,7,55.66001410,37.61001670,NULL,'2026-02-19 07:06:48'),(803,7,55.66039248,37.60990323,NULL,'2026-02-19 07:06:53'),(804,7,55.65993910,37.61043300,NULL,'2026-02-19 07:06:53'),(805,7,55.65993910,37.61043300,NULL,'2026-02-19 07:06:53'),(806,7,55.65995980,37.61045050,NULL,'2026-02-19 07:07:00'),(807,7,55.65995980,37.61045050,NULL,'2026-02-19 07:07:00'),(808,7,55.65993330,37.61039340,NULL,'2026-02-19 07:07:10'),(809,7,55.65993330,37.61039340,NULL,'2026-02-19 07:07:10'),(810,7,55.65992380,37.61046180,NULL,'2026-02-19 07:07:17'),(811,7,55.65992380,37.61046180,NULL,'2026-02-19 07:07:17'),(812,13,55.66039248,37.60990323,NULL,'2026-02-19 07:07:17'),(813,7,55.65993790,37.61042410,NULL,'2026-02-19 07:07:22'),(814,7,55.65993790,37.61042410,NULL,'2026-02-19 07:07:22'),(815,7,55.66039248,37.60990323,NULL,'2026-02-19 07:07:23'),(816,7,55.65994450,37.61035220,NULL,'2026-02-19 07:07:28'),(817,7,55.65994450,37.61035220,NULL,'2026-02-19 07:07:28'),(818,13,55.66045921,37.60997640,NULL,'2026-02-19 07:07:47'),(819,7,55.66045921,37.60997640,NULL,'2026-02-19 07:07:53'),(820,7,55.66045921,37.60997640,NULL,'2026-02-19 07:08:03'),(821,7,55.66045921,37.60997640,NULL,'2026-02-19 07:08:04'),(822,7,55.66045921,37.60997640,NULL,'2026-02-19 07:08:06'),(823,7,55.66045921,37.60997640,NULL,'2026-02-19 07:08:06'),(824,7,55.66045921,37.60997640,NULL,'2026-02-19 07:08:08'),(825,7,55.66045921,37.60997640,NULL,'2026-02-19 07:08:08'),(826,13,55.66045921,37.60997640,NULL,'2026-02-19 07:08:17'),(827,7,55.66045921,37.60997640,NULL,'2026-02-19 07:08:18'),(828,7,55.66045921,37.60997640,NULL,'2026-02-19 07:08:18'),(829,7,55.66045921,37.60997640,NULL,'2026-02-19 07:08:19'),(830,7,55.66045921,37.60997640,NULL,'2026-02-19 07:08:19'),(831,13,55.66055451,37.61007689,NULL,'2026-02-19 07:08:47'),(832,7,55.66055451,37.61007689,NULL,'2026-02-19 07:08:49'),(833,13,55.66055451,37.61007689,NULL,'2026-02-19 07:09:17'),(834,7,55.66055451,37.61007689,NULL,'2026-02-19 07:09:19'),(835,13,55.66042688,37.61017958,NULL,'2026-02-19 07:09:47'),(836,7,55.66042688,37.61017958,NULL,'2026-02-19 07:09:49'),(837,7,55.65994450,37.61035220,NULL,'2026-02-19 07:10:05'),(838,7,55.65993080,37.61012570,NULL,'2026-02-19 07:10:16'),(839,7,55.65993080,37.61012570,NULL,'2026-02-19 07:10:16'),(840,13,55.66042688,37.61017958,NULL,'2026-02-19 07:10:17'),(841,7,55.66042688,37.61017958,NULL,'2026-02-19 07:10:19'),(842,7,55.65993020,37.61044350,NULL,'2026-02-19 07:10:23'),(843,7,55.65993020,37.61044350,NULL,'2026-02-19 07:10:23'),(844,7,55.65993160,37.61045150,NULL,'2026-02-19 07:10:29'),(845,7,55.65993160,37.61045150,NULL,'2026-02-19 07:10:29'),(846,7,55.65994190,37.61040560,NULL,'2026-02-19 07:10:36'),(847,7,55.65994190,37.61040560,NULL,'2026-02-19 07:10:36'),(848,7,55.65992310,37.61042240,NULL,'2026-02-19 07:10:40'),(849,7,55.65992310,37.61042240,NULL,'2026-02-19 07:10:40'),(850,13,55.66048329,37.60999959,NULL,'2026-02-19 07:10:47'),(851,7,55.66048329,37.60999959,NULL,'2026-02-19 07:10:49'),(852,7,55.65994830,37.61032780,NULL,'2026-02-19 07:10:52'),(853,7,55.65994830,37.61032780,NULL,'2026-02-19 07:10:52'),(854,7,55.65993490,37.61045590,NULL,'2026-02-19 07:10:57'),(855,7,55.65993490,37.61045590,NULL,'2026-02-19 07:10:57'),(856,13,55.66048329,37.60999959,NULL,'2026-02-19 07:11:17'),(857,7,55.66048329,37.60999959,NULL,'2026-02-19 07:11:19'),(858,13,55.66039238,37.60990500,NULL,'2026-02-19 07:11:47'),(859,7,55.66039238,37.60990500,NULL,'2026-02-19 07:11:49'),(860,7,55.65993580,37.61042640,NULL,'2026-02-19 07:11:59'),(861,7,55.65993580,37.61042640,NULL,'2026-02-19 07:11:59'),(862,7,55.65992670,37.61041990,NULL,'2026-02-19 07:12:07'),(863,7,55.65992670,37.61041990,NULL,'2026-02-19 07:12:07'),(864,13,55.66039238,37.60990500,NULL,'2026-02-19 07:12:17'),(865,7,55.66039238,37.60990500,NULL,'2026-02-19 07:12:19'),(866,7,55.65991490,37.61041910,NULL,'2026-02-19 07:12:42'),(867,7,55.65991490,37.61041910,NULL,'2026-02-19 07:12:42'),(868,13,55.66039290,37.60990530,NULL,'2026-02-19 07:12:47'),(869,7,55.65992800,37.61044430,NULL,'2026-02-19 07:12:48'),(870,7,55.65992800,37.61044430,NULL,'2026-02-19 07:12:48'),(871,7,55.66039290,37.60990530,NULL,'2026-02-19 07:12:49'),(872,7,55.65993820,37.61045070,NULL,'2026-02-19 07:13:03'),(873,7,55.65993820,37.61045070,NULL,'2026-02-19 07:13:04'),(874,7,55.65993820,37.61045070,NULL,'2026-02-19 07:13:04'),(875,7,55.65993820,37.61045070,NULL,'2026-02-19 07:13:04'),(876,13,55.66039290,37.60990530,NULL,'2026-02-19 07:13:18'),(877,7,55.66039290,37.60990530,NULL,'2026-02-19 07:13:19'),(878,13,55.66021469,37.60957986,NULL,'2026-02-19 07:13:47'),(879,7,55.66021469,37.60957986,NULL,'2026-02-19 07:13:49'),(880,13,55.66021469,37.60957986,NULL,'2026-02-19 07:14:17'),(881,7,55.66021469,37.60957986,NULL,'2026-02-19 07:14:19'),(882,13,55.66062327,37.60963050,NULL,'2026-02-19 07:14:47'),(883,7,55.66062327,37.60963050,NULL,'2026-02-19 07:14:49'),(884,13,55.66062327,37.60963050,NULL,'2026-02-19 07:15:17'),(885,7,55.66062327,37.60963050,NULL,'2026-02-19 07:15:19'),(886,7,55.66062327,37.60963050,NULL,'2026-02-19 07:15:40'),(887,7,55.66062327,37.60963050,NULL,'2026-02-19 07:15:40'),(888,13,55.66084744,37.60937429,NULL,'2026-02-19 07:15:47'),(889,13,55.66084744,37.60937429,NULL,'2026-02-19 07:16:17'),(890,13,55.66054171,37.60975078,NULL,'2026-02-19 07:16:48'),(891,13,55.66054171,37.60975078,NULL,'2026-02-19 07:17:17'),(892,13,55.66030520,37.61000186,NULL,'2026-02-19 07:17:48'),(893,13,55.66030520,37.61000186,NULL,'2026-02-19 07:18:18'),(894,13,55.66034650,37.60970920,NULL,'2026-02-19 07:18:47'),(895,13,55.66034650,37.60970920,NULL,'2026-02-19 07:19:17'),(896,13,55.66039322,37.60990692,NULL,'2026-02-19 07:19:47'),(897,13,55.66039322,37.60990692,NULL,'2026-02-19 07:20:17'),(898,13,55.66078519,37.61041121,NULL,'2026-02-19 07:20:47'),(899,13,55.66078519,37.61041121,NULL,'2026-02-19 07:21:17'),(900,13,55.66115030,37.61088232,NULL,'2026-02-19 07:21:47'),(901,13,55.66115030,37.61088232,NULL,'2026-02-19 07:22:17'),(902,13,55.66118633,37.61092883,NULL,'2026-02-19 07:22:47'),(903,13,55.66118633,37.61092883,NULL,'2026-02-19 07:23:17'),(904,13,55.66042699,37.60977388,NULL,'2026-02-19 07:26:17'),(905,13,55.66042699,37.60977388,NULL,'2026-02-19 07:26:47'),(906,13,55.66042700,37.60977389,NULL,'2026-02-19 07:27:17'),(907,13,55.66042700,37.60977389,NULL,'2026-02-19 07:27:47'),(908,13,55.66058104,37.60963512,NULL,'2026-02-19 07:28:17'),(909,13,55.66058104,37.60963512,NULL,'2026-02-19 07:28:47'),(910,13,55.66088011,37.60931380,NULL,'2026-02-19 07:29:17'),(911,13,55.66088011,37.60931380,NULL,'2026-02-19 07:29:47'),(912,13,55.66143569,37.60844581,NULL,'2026-02-19 07:30:17'),(913,13,55.66143569,37.60844581,NULL,'2026-02-19 07:30:47'),(914,13,55.66138572,37.60854461,NULL,'2026-02-19 07:31:17'),(915,13,55.66138572,37.60854461,NULL,'2026-02-19 07:31:47'),(916,13,55.66026404,37.60982508,NULL,'2026-02-19 07:32:17'),(917,13,55.66026404,37.60982508,NULL,'2026-02-19 07:32:47'),(918,13,55.66028805,37.60960092,NULL,'2026-02-19 07:33:17'),(919,13,55.66028805,37.60960092,NULL,'2026-02-19 07:33:47'),(920,13,55.66028805,37.60960092,NULL,'2026-02-19 07:34:19'),(921,13,55.66062511,37.60963776,NULL,'2026-02-19 07:34:47'),(922,13,55.66062511,37.60963776,NULL,'2026-02-19 07:35:17'),(923,14,55.65990420,37.61007940,NULL,'2026-02-19 07:35:34'),(924,14,55.65990420,37.61007940,NULL,'2026-02-19 07:35:34'),(925,14,55.65991190,37.61007820,NULL,'2026-02-19 07:35:39'),(926,14,55.65991190,37.61007820,NULL,'2026-02-19 07:35:39'),(927,13,55.66062458,37.60963539,NULL,'2026-02-19 07:35:47'),(928,13,55.66062458,37.60963539,NULL,'2026-02-19 07:36:17'),(929,13,55.66039230,37.60990578,NULL,'2026-02-19 07:36:47'),(930,14,55.65997910,37.61034950,NULL,'2026-02-19 07:37:13'),(931,14,55.65997910,37.61034950,NULL,'2026-02-19 07:37:13'),(932,13,55.66039230,37.60990578,NULL,'2026-02-19 07:37:17'),(933,13,55.66039282,37.60990517,NULL,'2026-02-19 07:37:47'),(934,13,55.66039282,37.60990517,NULL,'2026-02-19 07:38:17'),(935,15,55.65991960,37.61045500,NULL,'2026-02-19 07:38:47'),(936,15,55.65991960,37.61045500,NULL,'2026-02-19 07:38:47'),(937,13,55.66057615,37.61010004,NULL,'2026-02-19 07:38:47'),(938,15,55.65993030,37.61043780,NULL,'2026-02-19 07:38:58'),(939,15,55.65993030,37.61043780,NULL,'2026-02-19 07:38:58'),(940,15,55.65995660,37.61039170,NULL,'2026-02-19 07:39:10'),(941,15,55.65995660,37.61039170,NULL,'2026-02-19 07:39:10'),(942,15,55.65995380,37.61042020,NULL,'2026-02-19 07:39:15'),(943,15,55.65995380,37.61042020,NULL,'2026-02-19 07:39:15'),(944,13,55.66057615,37.61010004,NULL,'2026-02-19 07:39:17'),(945,13,55.66031732,37.60990407,NULL,'2026-02-19 07:39:47'),(946,8,55.66027960,37.60990536,NULL,'2026-02-19 07:40:37'),(947,8,55.66027960,37.60990536,NULL,'2026-02-19 07:40:37'),(948,8,55.66027960,37.60990536,NULL,'2026-02-19 07:40:47'),(949,8,55.66027960,37.60990536,NULL,'2026-02-19 07:40:47'),(950,8,55.66027960,37.60990536,NULL,'2026-02-19 07:40:49'),(951,8,55.66027960,37.60990536,NULL,'2026-02-19 07:40:49'),(952,8,55.66027960,37.60990536,NULL,'2026-02-19 07:40:51'),(953,8,55.66027960,37.60990536,NULL,'2026-02-19 07:40:51'),(954,8,55.66027960,37.60990536,NULL,'2026-02-19 07:40:54'),(955,8,55.66027960,37.60990536,NULL,'2026-02-19 07:40:54'),(956,8,55.66027960,37.60990536,NULL,'2026-02-19 07:41:01'),(957,8,55.66027960,37.60990536,NULL,'2026-02-19 07:41:01'),(958,15,55.65995380,37.61042020,NULL,'2026-02-19 07:41:19'),(959,15,55.65993230,37.61014340,NULL,'2026-02-19 07:41:19'),(960,15,55.65990930,37.61041530,NULL,'2026-02-19 07:41:27'),(961,15,55.65990930,37.61041530,NULL,'2026-02-19 07:41:27'),(962,8,55.66024007,37.60990526,NULL,'2026-02-19 07:41:31'),(963,15,55.65992510,37.61009020,NULL,'2026-02-19 07:41:33'),(964,15,55.65992510,37.61009020,NULL,'2026-02-19 07:41:33'),(965,15,55.65995850,37.61034230,NULL,'2026-02-19 07:41:45'),(966,15,55.65995850,37.61034230,NULL,'2026-02-19 07:41:45'),(967,15,55.65990900,37.61021140,NULL,'2026-02-19 07:41:57'),(968,15,55.65990900,37.61021140,NULL,'2026-02-19 07:41:57'),(969,15,55.65993900,37.61013500,NULL,'2026-02-19 07:42:02'),(970,15,55.65993900,37.61013500,NULL,'2026-02-19 07:42:02'),(971,15,55.65998490,37.61013590,NULL,'2026-02-19 07:42:08'),(972,15,55.65998490,37.61013590,NULL,'2026-02-19 07:42:08'),(973,15,55.65998090,37.61020520,NULL,'2026-02-19 07:42:16'),(974,15,55.65998090,37.61020520,NULL,'2026-02-19 07:42:16'),(975,8,55.66039215,37.60990521,NULL,'2026-02-19 07:42:31'),(976,15,55.65998090,37.61020520,NULL,'2026-02-19 07:42:45'),(977,15,55.65991890,37.61040200,NULL,'2026-02-19 07:42:49'),(978,15,55.65991890,37.61040200,NULL,'2026-02-19 07:42:49'),(979,8,55.66039215,37.60990521,NULL,'2026-02-19 07:43:01'),(980,15,55.65998260,37.61031190,NULL,'2026-02-19 07:43:13'),(981,15,55.65998260,37.61031190,NULL,'2026-02-19 07:43:14'),(982,15,55.65998260,37.61031190,NULL,'2026-02-19 07:43:14'),(983,15,55.65993380,37.61034120,NULL,'2026-02-19 07:43:18'),(984,15,55.65993380,37.61034120,NULL,'2026-02-19 07:43:18'),(985,15,55.65991200,37.61042760,NULL,'2026-02-19 07:43:25'),(986,15,55.65991200,37.61042760,NULL,'2026-02-19 07:43:25'),(987,8,55.66033511,37.60990524,NULL,'2026-02-19 07:43:31'),(988,15,55.65993210,37.61038460,NULL,'2026-02-19 07:43:40'),(989,15,55.65993210,37.61038460,NULL,'2026-02-19 07:43:40'),(990,15,55.65994160,37.61037050,NULL,'2026-02-19 07:43:47'),(991,15,55.65994160,37.61037050,NULL,'2026-02-19 07:43:47'),(992,15,55.65999760,37.61009190,NULL,'2026-02-19 07:43:59'),(993,15,55.65999760,37.61009190,NULL,'2026-02-19 07:43:59'),(994,8,55.66033511,37.60990524,NULL,'2026-02-19 07:44:01'),(995,8,55.66031885,37.60990524,NULL,'2026-02-19 07:44:31'),(996,15,55.65999760,37.61009190,NULL,'2026-02-19 07:44:56'),(997,15,55.65999760,37.61009190,NULL,'2026-02-19 07:44:56'),(998,15,55.65993060,37.61010920,NULL,'2026-02-19 07:44:56'),(999,15,55.65993060,37.61010920,NULL,'2026-02-19 07:44:56'),(1000,8,55.66031885,37.60990524,NULL,'2026-02-19 07:45:01'),(1001,15,55.65993070,37.61033850,NULL,'2026-02-19 07:45:04'),(1002,15,55.65993070,37.61033850,NULL,'2026-02-19 07:45:04'),(1003,15,55.65995940,37.61027560,NULL,'2026-02-19 07:45:10'),(1004,15,55.65995940,37.61027560,NULL,'2026-02-19 07:45:10'),(1005,15,55.65996770,37.61027180,NULL,'2026-02-19 07:45:15'),(1006,15,55.65996770,37.61027180,NULL,'2026-02-19 07:45:15'),(1007,15,55.65994900,37.61025260,NULL,'2026-02-19 07:45:27'),(1008,15,55.65994900,37.61025260,NULL,'2026-02-19 07:45:27'),(1009,8,55.66010268,37.60944587,NULL,'2026-02-19 07:45:31'),(1010,15,55.65996810,37.61025730,NULL,'2026-02-19 07:45:32'),(1011,15,55.65996810,37.61025730,NULL,'2026-02-19 07:45:32'),(1012,15,55.65993800,37.61042100,NULL,'2026-02-19 07:45:39'),(1013,15,55.65993800,37.61042100,NULL,'2026-02-19 07:45:39'),(1014,15,55.65991270,37.61046750,NULL,'2026-02-19 07:45:51'),(1015,15,55.65991270,37.61046750,NULL,'2026-02-19 07:45:51'),(1016,15,55.65991270,37.61046750,NULL,'2026-02-19 07:45:51'),(1017,15,55.65991270,37.61046750,NULL,'2026-02-19 07:45:51'),(1018,8,55.66010268,37.60944587,NULL,'2026-02-19 07:46:01'),(1019,15,55.66000020,37.61003490,NULL,'2026-02-19 07:46:05'),(1020,15,55.66000020,37.61003490,NULL,'2026-02-19 07:46:05'),(1021,15,55.66000720,37.61000830,NULL,'2026-02-19 07:46:12'),(1022,15,55.66000720,37.61000830,NULL,'2026-02-19 07:46:12'),(1023,15,55.65998130,37.61007290,NULL,'2026-02-19 07:46:17'),(1024,15,55.65998130,37.61007290,NULL,'2026-02-19 07:46:17'),(1025,8,55.66038350,37.60988958,NULL,'2026-02-19 07:46:31'),(1026,15,55.65993220,37.61029110,NULL,'2026-02-19 07:46:37'),(1027,15,55.65993220,37.61029110,NULL,'2026-02-19 07:46:37'),(1028,15,55.65993220,37.61029110,NULL,'2026-02-19 07:46:42'),(1029,15,55.65993220,37.61029110,NULL,'2026-02-19 07:46:42'),(1030,8,55.66038350,37.60988958,NULL,'2026-02-19 07:47:02'),(1031,8,55.66039248,37.60990471,NULL,'2026-02-19 07:47:32'),(1032,15,55.65993530,37.61031940,NULL,'2026-02-19 07:47:39'),(1033,15,55.65993530,37.61031940,NULL,'2026-02-19 07:47:39'),(1034,8,55.66039248,37.60990471,NULL,'2026-02-19 07:48:01'),(1035,8,55.66039275,37.60990523,NULL,'2026-02-19 07:48:31'),(1036,8,55.66039275,37.60990523,NULL,'2026-02-19 07:49:01'),(1037,8,55.66033163,37.61005874,NULL,'2026-02-19 07:49:31'),(1038,8,55.66033163,37.61005874,NULL,'2026-02-19 07:50:01'),(1039,8,55.66060805,37.60980855,NULL,'2026-02-19 07:50:31'),(1040,8,55.66060805,37.60980855,NULL,'2026-02-19 07:51:01'),(1041,8,55.66047501,37.60999697,NULL,'2026-02-19 07:51:31'),(1042,8,55.66047501,37.60999697,NULL,'2026-02-19 07:52:01'),(1043,8,55.66039259,37.60990492,NULL,'2026-02-19 07:52:31'),(1044,8,55.66039259,37.60990492,NULL,'2026-02-19 07:53:01'),(1045,8,55.66034393,37.60969672,NULL,'2026-02-19 07:53:31'),(1046,8,55.66034393,37.60969672,NULL,'2026-02-19 07:54:01'),(1047,8,55.66096650,37.60923993,NULL,'2026-02-19 07:54:31'),(1048,8,55.66096650,37.60923993,NULL,'2026-02-19 07:55:01'),(1049,8,55.66122486,37.60895675,NULL,'2026-02-19 07:55:31'),(1050,8,55.66122486,37.60895675,NULL,'2026-02-19 07:56:02'),(1051,8,55.66024211,37.60990321,NULL,'2026-02-19 07:56:31'),(1052,8,55.66024211,37.60990321,NULL,'2026-02-19 07:57:01'),(1053,8,55.66023943,37.60990596,NULL,'2026-02-19 07:57:31'),(1054,8,55.66023943,37.60990596,NULL,'2026-02-19 07:58:01'),(1055,8,55.66023943,37.60990596,NULL,'2026-02-19 07:58:31'),(1056,8,55.66039284,37.60990536,NULL,'2026-02-19 07:59:01'),(1057,8,55.66039284,37.60990536,NULL,'2026-02-19 07:59:31'),(1058,8,55.66039275,37.60990521,NULL,'2026-02-19 08:00:02'),(1059,8,55.66039275,37.60990521,NULL,'2026-02-19 08:00:31'),(1060,8,55.66039275,37.60990522,NULL,'2026-02-19 08:01:02'),(1061,8,55.66039275,37.60990522,NULL,'2026-02-19 08:01:32'),(1062,8,55.66012566,37.60973360,NULL,'2026-02-19 08:04:21'),(1063,8,55.66028210,37.60988958,NULL,'2026-02-19 08:05:21'),(1064,8,55.66028210,37.60988958,NULL,'2026-02-19 08:06:24'),(1065,8,55.66028210,37.60988958,NULL,'2026-02-19 08:06:43'),(1066,8,55.66042400,37.61004950,NULL,'2026-02-19 08:24:18'),(1067,8,55.85885341,37.69431382,NULL,'2026-02-19 10:02:38'),(1068,8,55.85885341,37.69431382,NULL,'2026-02-19 10:03:06'),(1069,8,55.85888768,37.69437825,NULL,'2026-02-19 10:03:37'),(1070,8,55.85888768,37.69437825,NULL,'2026-02-19 10:04:06'),(1071,8,55.85887943,37.69435750,NULL,'2026-02-19 10:04:36'),(1072,8,55.85887943,37.69435750,NULL,'2026-02-19 10:05:06'),(1073,8,55.85875013,37.69404483,NULL,'2026-02-19 10:05:37'),(1074,8,55.85875013,37.69404483,NULL,'2026-02-19 10:06:06'),(1075,8,55.85899381,37.69458123,NULL,'2026-02-19 10:06:36'),(1076,8,55.85898591,37.69456555,NULL,'2026-02-19 10:07:06'),(1077,11,55.65351091,37.61241955,NULL,'2026-02-19 12:39:53'),(1078,11,55.65351091,37.61241955,NULL,'2026-02-19 12:39:53'),(1079,11,55.65351091,37.61241955,NULL,'2026-02-19 12:40:03'),(1080,11,55.65351091,37.61241955,NULL,'2026-02-19 12:40:03'),(1081,11,55.65351091,37.61241955,NULL,'2026-02-19 12:40:04'),(1082,11,55.65351091,37.61241955,NULL,'2026-02-19 12:40:04'),(1083,11,55.65351091,37.61241955,NULL,'2026-02-19 12:40:17'),(1084,11,55.65351091,37.61241955,NULL,'2026-02-19 12:40:17'),(1085,11,55.65351091,37.61241955,NULL,'2026-02-19 12:40:19'),(1086,11,55.65351091,37.61241955,NULL,'2026-02-19 12:40:19'),(1087,11,55.65351091,37.61241955,NULL,'2026-02-19 12:40:21'),(1088,11,55.65351091,37.61241955,NULL,'2026-02-19 12:40:21'),(1089,11,55.65351250,37.61231061,NULL,'2026-02-19 12:40:53'),(1090,11,55.65351250,37.61231061,NULL,'2026-02-19 12:41:21'),(1091,11,55.65351250,37.61231061,NULL,'2026-02-19 12:41:29'),(1092,11,55.65351250,37.61231061,NULL,'2026-02-19 12:41:29'),(1093,11,55.65351219,37.61246972,NULL,'2026-02-19 12:42:02'),(1094,11,55.65351219,37.61246972,NULL,'2026-02-19 12:42:20'),(1095,11,55.65351219,37.61246972,NULL,'2026-02-19 12:42:20'),(1096,11,55.65351219,37.61246972,NULL,'2026-02-19 12:42:24'),(1097,11,55.65351219,37.61246972,NULL,'2026-02-19 12:42:24'),(1098,11,55.65351219,37.61246972,NULL,'2026-02-19 12:42:53'),(1099,11,55.65351292,37.61249817,NULL,'2026-02-19 12:42:56'),(1100,11,55.65351292,37.61249817,NULL,'2026-02-19 12:42:56'),(1101,11,55.65351292,37.61249817,NULL,'2026-02-19 12:43:26'),(1102,11,55.65346900,37.61269489,NULL,'2026-02-19 12:43:51'),(1103,11,55.65346900,37.61269489,NULL,'2026-02-19 12:43:51'),(1104,11,55.65346900,37.61269489,NULL,'2026-02-19 12:43:52'),(1105,11,55.65346900,37.61269489,NULL,'2026-02-19 12:43:52'),(1106,11,55.65347072,37.61274336,NULL,'2026-02-19 12:44:19'),(1107,11,55.65347072,37.61274336,NULL,'2026-02-19 12:44:19'),(1108,11,55.65347072,37.61274336,NULL,'2026-02-19 12:44:26'),(1109,11,55.65347072,37.61274336,NULL,'2026-02-19 12:44:26'),(1110,11,55.65347072,37.61274336,NULL,'2026-02-19 12:44:55'),(1111,11,55.65346900,37.61269489,NULL,'2026-02-19 12:45:30'),(1112,11,55.65346900,37.61269489,NULL,'2026-02-19 12:45:43'),(1113,11,55.65346900,37.61269489,NULL,'2026-02-19 12:45:43'),(1114,11,55.65346900,37.61269489,NULL,'2026-02-19 12:45:51'),(1115,11,55.65346900,37.61269489,NULL,'2026-02-19 12:45:51'),(1116,11,55.65350773,37.61232327,NULL,'2026-02-19 12:46:22'),(1117,11,55.65350773,37.61232327,NULL,'2026-02-19 12:46:51'),(1118,11,55.65351347,37.61233859,NULL,'2026-02-19 12:47:23'),(1119,11,55.65351347,37.61233859,NULL,'2026-02-19 12:47:51'),(1120,11,55.65351456,37.61237006,NULL,'2026-02-19 12:48:22'),(1121,11,55.65351456,37.61237006,NULL,'2026-02-19 12:48:51'),(1122,11,55.65351371,37.61252930,NULL,'2026-02-19 12:49:24'),(1123,11,55.65351371,37.61252930,NULL,'2026-02-19 12:50:23'),(1124,11,55.65351371,37.61252930,NULL,'2026-02-19 12:50:51'),(1125,11,55.65350001,37.61246028,NULL,'2026-02-19 12:51:52'),(1126,11,55.65350001,37.61246028,NULL,'2026-02-19 12:52:21'),(1127,11,55.65345941,37.61259136,NULL,'2026-02-19 12:52:53'),(1128,11,55.65345941,37.61259136,NULL,'2026-02-19 12:53:21'),(1129,11,55.65345941,37.61259136,NULL,'2026-02-19 12:53:52'),(1130,11,55.65345941,37.61259136,NULL,'2026-02-19 12:54:21'),(1131,11,55.65350803,37.61247124,NULL,'2026-02-19 12:54:56'),(1132,11,55.65350803,37.61247124,NULL,'2026-02-19 12:55:21'),(1133,11,55.65350803,37.61247124,NULL,'2026-02-19 12:55:52'),(1134,11,55.65350803,37.61247124,NULL,'2026-02-19 12:56:21'),(1135,11,55.65347764,37.61235942,NULL,'2026-02-19 12:56:53'),(1136,11,55.65347764,37.61235942,NULL,'2026-02-19 12:57:22'),(1137,11,55.65347764,37.61235942,NULL,'2026-02-19 12:57:52'),(1138,11,55.65347764,37.61235942,NULL,'2026-02-19 12:58:21'),(1139,11,55.65351219,37.61246972,NULL,'2026-02-19 12:58:53'),(1140,11,55.65351219,37.61246972,NULL,'2026-02-19 12:59:22'),(1141,11,55.65351219,37.61246972,NULL,'2026-02-19 13:00:25'),(1142,11,55.65351721,37.61244650,NULL,'2026-02-19 13:00:56'),(1143,11,55.65351721,37.61244650,NULL,'2026-02-19 13:01:21'),(1144,11,55.65351456,37.61237006,NULL,'2026-02-19 13:01:53'),(1145,11,55.65351456,37.61237006,NULL,'2026-02-19 13:02:22'),(1146,11,55.65350700,37.61241664,NULL,'2026-02-19 13:03:27'),(1147,11,55.65350700,37.61241664,NULL,'2026-02-19 13:04:23'),(1148,11,55.65347500,37.61209169,NULL,'2026-02-19 13:05:25'),(1149,11,55.65347500,37.61209169,NULL,'2026-02-19 13:06:23'),(1150,11,55.65350043,37.61239424,NULL,'2026-02-19 13:07:24'),(1151,11,55.65350063,37.61241931,NULL,'2026-02-19 13:08:23'),(1152,11,55.65350773,37.61232327,NULL,'2026-02-19 13:09:25'),(1153,11,55.65350773,37.61232327,NULL,'2026-02-19 13:10:23'),(1154,11,55.65350803,37.61247124,NULL,'2026-02-19 13:11:24'),(1155,11,55.65350803,37.61247124,NULL,'2026-02-19 13:12:23'),(1156,11,55.65348089,37.61235492,NULL,'2026-02-19 13:13:27'),(1157,11,55.65348089,37.61235492,NULL,'2026-02-19 13:14:23'),(1158,11,55.65350700,37.61241664,NULL,'2026-02-19 13:15:23'),(1159,11,55.65350700,37.61229275,NULL,'2026-02-19 13:16:23'),(1160,11,55.65351219,37.61246972,NULL,'2026-02-19 13:17:24'),(1161,11,55.65351219,37.61246972,NULL,'2026-02-19 13:18:23'),(1162,11,55.65350803,37.61247124,NULL,'2026-02-19 13:19:24'),(1163,8,55.65364968,37.61261257,NULL,'2026-02-19 13:19:46'),(1164,11,55.65350803,37.61247124,NULL,'2026-02-19 13:20:23'),(1165,11,55.65350850,37.61236517,NULL,'2026-02-19 13:21:27'),(1166,11,55.65350850,37.61236517,NULL,'2026-02-19 13:22:23'),(1167,11,55.65350700,37.61229275,NULL,'2026-02-19 13:23:24'),(1168,11,55.65350700,37.61229275,NULL,'2026-02-19 13:24:23'),(1169,11,55.65350613,37.61237067,NULL,'2026-02-19 13:25:23'),(1170,11,55.65351153,37.61244360,NULL,'2026-02-19 13:26:23'),(1171,11,55.65350773,37.61232327,NULL,'2026-02-19 13:27:24'),(1172,11,55.65350773,37.61232327,NULL,'2026-02-19 13:28:23'),(1173,11,55.65350734,37.61245520,NULL,'2026-02-19 13:29:27'),(1174,11,55.65350734,37.61245520,NULL,'2026-02-19 13:30:23'),(1175,11,55.65351347,37.61233859,NULL,'2026-02-19 13:31:24'),(1176,11,55.65351347,37.61233859,NULL,'2026-02-19 13:32:23'),(1177,11,55.65344687,37.61261297,NULL,'2026-02-19 13:33:25'),(1178,11,55.65344687,37.61261297,NULL,'2026-02-19 13:34:23'),(1179,11,55.65351163,37.61228558,NULL,'2026-02-19 13:35:25'),(1180,8,55.65363482,37.61259164,NULL,'2026-02-19 13:35:47'),(1181,11,55.65351163,37.61228558,NULL,'2026-02-19 13:36:23'),(1182,11,55.65351219,37.61246972,NULL,'2026-02-19 13:37:24'),(1183,11,55.65351219,37.61246972,NULL,'2026-02-19 13:38:23'),(1184,11,55.65351219,37.61246972,NULL,'2026-02-19 13:39:27'),(1185,11,55.65351219,37.61246972,NULL,'2026-02-19 13:40:23'),(1186,11,55.65344531,37.61263758,NULL,'2026-02-19 13:41:24'),(1187,11,55.65344531,37.61263758,NULL,'2026-02-19 13:42:23'),(1188,11,55.65351292,37.61249817,NULL,'2026-02-19 13:43:24'),(1189,11,55.65351292,37.61249817,NULL,'2026-02-19 13:44:23'),(1190,11,55.65350749,37.61244271,NULL,'2026-02-19 13:45:24'),(1191,11,55.65350749,37.61244271,NULL,'2026-02-19 13:46:23'),(1192,11,55.65349517,37.61238867,NULL,'2026-02-19 13:47:25'),(1193,8,55.65359335,37.61253913,NULL,'2026-02-19 13:51:48'),(1194,8,55.66042216,37.60996776,NULL,'2026-02-19 14:21:42'),(1195,8,55.66022338,37.60999399,NULL,'2026-02-19 14:38:11'),(1196,8,55.66023364,37.61000552,NULL,'2026-02-19 14:54:35'),(1197,8,55.66037489,37.60998756,NULL,'2026-02-19 15:26:30'),(1198,8,55.66029991,37.60997669,NULL,'2026-02-19 15:42:56'),(1199,8,55.66039404,37.60990409,NULL,'2026-02-19 15:59:21'),(1200,8,55.66039273,37.60990526,NULL,'2026-02-19 16:11:38');
/*!40000 ALTER TABLE `location_history` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `payments`
--

DROP TABLE IF EXISTS `payments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `payments` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `currency` varchar(3) COLLATE utf8mb4_unicode_ci DEFAULT 'RUB',
  `payment_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('pending','succeeded','failed','cancelled') COLLATE utf8mb4_unicode_ci DEFAULT 'pending',
  `description` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_payment_id` (`payment_id`(191)),
  KEY `idx_status` (`status`),
  CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `payments`
--

LOCK TABLES `payments` WRITE;
/*!40000 ALTER TABLE `payments` DISABLE KEYS */;
/*!40000 ALTER TABLE `payments` ENABLE KEYS */;
UNLOCK TABLES;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = '' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`tesatour`@`localhost`*/ /*!50003 TRIGGER `payments_before_insert` BEFORE INSERT ON `payments` FOR EACH ROW
BEGIN
    SET NEW.created_at = IFNULL(NEW.created_at, NOW());
    SET NEW.updated_at = IFNULL(NEW.updated_at, NOW());
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = '' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`tesatour`@`localhost`*/ /*!50003 TRIGGER `payments_before_update` BEFORE UPDATE ON `payments` FOR EACH ROW
BEGIN
    SET NEW.updated_at = NOW();
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `route_plans`
--

DROP TABLE IF EXISTS `route_plans`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `route_plans` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `group_id` int(10) unsigned NOT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `title` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `is_active` tinyint(1) DEFAULT '1',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_group_id` (`group_id`),
  KEY `idx_is_active` (`is_active`),
  KEY `created_by` (`created_by`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `route_plans`
--

LOCK TABLES `route_plans` WRITE;
/*!40000 ALTER TABLE `route_plans` DISABLE KEYS */;
INSERT INTO `route_plans` VALUES (7,4,7,'Маршрут до Эльбруса','',1,NULL,NULL),(8,6,11,'Маршрут на гору Эльбрус ','',1,NULL,NULL),(9,7,7,'свв','вв',1,NULL,NULL),(10,11,15,'Маршрут Эльбрус ','',1,NULL,NULL);
/*!40000 ALTER TABLE `route_plans` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `route_points`
--

DROP TABLE IF EXISTS `route_points`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `route_points` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `route_plan_id` int(10) unsigned NOT NULL,
  `title` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `latitude` decimal(10,8) NOT NULL,
  `longitude` decimal(11,8) NOT NULL,
  `order_index` int(10) unsigned DEFAULT '0',
  `is_completed` tinyint(1) DEFAULT '0',
  `completed_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_route_plan_id` (`route_plan_id`),
  KEY `idx_order_index` (`order_index`),
  CONSTRAINT `route_points_ibfk_1` FOREIGN KEY (`route_plan_id`) REFERENCES `route_plans` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `route_points`
--

LOCK TABLES `route_points` WRITE;
/*!40000 ALTER TABLE `route_points` DISABLE KEYS */;
INSERT INTO `route_points` VALUES (3,7,'Лагерь','Место сбора',41.70660121,42.33218032,1,0,NULL,NULL),(4,7,'Точка 2','',47.32903382,30.93761250,2,0,NULL,NULL),(5,8,'Точка сбора','Прибыть в 12:00 всем!!',43.63229488,43.09660925,1,0,NULL,NULL),(6,8,'Привал 1','',42.95482290,43.07063428,2,0,NULL,NULL);
/*!40000 ALTER TABLE `route_points` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sessions`
--

DROP TABLE IF EXISTS `sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sessions` (
  `id` varchar(128) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` int(10) unsigned DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `data` text COLLATE utf8mb4_unicode_ci,
  `last_activity` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_last_activity` (`last_activity`),
  CONSTRAINT `sessions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
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
-- Table structure for table `sos_alerts`
--

DROP TABLE IF EXISTS `sos_alerts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sos_alerts` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `group_id` int(10) unsigned NOT NULL,
  `latitude` decimal(10,8) NOT NULL,
  `longitude` decimal(11,8) NOT NULL,
  `comment` text COLLATE utf8mb4_unicode_ci,
  `status` enum('active','resolved') COLLATE utf8mb4_unicode_ci DEFAULT 'active',
  `resolved_by` int(10) unsigned DEFAULT NULL,
  `resolved_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_group_id` (`group_id`),
  KEY `idx_status` (`status`),
  KEY `idx_created_at` (`created_at`),
  KEY `resolved_by` (`resolved_by`),
  CONSTRAINT `sos_alerts_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `sos_alerts_ibfk_2` FOREIGN KEY (`group_id`) REFERENCES `groups` (`id`) ON DELETE CASCADE,
  CONSTRAINT `sos_alerts_ibfk_3` FOREIGN KEY (`resolved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sos_alerts`
--

LOCK TABLES `sos_alerts` WRITE;
/*!40000 ALTER TABLE `sos_alerts` DISABLE KEYS */;
INSERT INTO `sos_alerts` VALUES (1,7,7,55.65991750,37.61011840,'Оо','active',NULL,NULL,NULL),(2,12,8,55.66039280,37.60989379,'аа','resolved',12,'2026-02-18 22:10:52',NULL),(3,7,10,55.65993910,37.61043300,'','resolved',7,'2026-02-19 07:07:21',NULL),(4,15,11,55.65994160,37.61037050,'Я потерялся!','active',NULL,NULL,NULL);
/*!40000 ALTER TABLE `sos_alerts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `subscriptions`
--

DROP TABLE IF EXISTS `subscriptions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `subscriptions` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `start_date` datetime NOT NULL,
  `end_date` datetime NOT NULL,
  `status` enum('active','cancelled','expired') COLLATE utf8mb4_unicode_ci DEFAULT 'active',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_status` (`status`),
  KEY `idx_end_date` (`end_date`),
  CONSTRAINT `subscriptions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `subscriptions`
--

LOCK TABLES `subscriptions` WRITE;
/*!40000 ALTER TABLE `subscriptions` DISABLE KEYS */;
/*!40000 ALTER TABLE `subscriptions` ENABLE KEYS */;
UNLOCK TABLES;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = '' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`tesatour`@`localhost`*/ /*!50003 TRIGGER `subscriptions_before_insert` BEFORE INSERT ON `subscriptions` FOR EACH ROW
BEGIN
    SET NEW.created_at = IFNULL(NEW.created_at, NOW());
    SET NEW.updated_at = IFNULL(NEW.updated_at, NOW());
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = '' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`tesatour`@`localhost`*/ /*!50003 TRIGGER `subscriptions_before_update` BEFORE UPDATE ON `subscriptions` FOR EACH ROW
BEGIN
    SET NEW.updated_at = NOW();
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `support_messages`
--

DROP TABLE IF EXISTS `support_messages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `support_messages` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `admin_id` int(10) unsigned DEFAULT NULL,
  `message_text` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_from_admin` tinyint(1) DEFAULT '0',
  `is_read` tinyint(1) DEFAULT '0',
  `created_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_admin_id` (`admin_id`),
  KEY `idx_created_at` (`created_at`),
  CONSTRAINT `support_messages_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `support_messages_ibfk_2` FOREIGN KEY (`admin_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `support_messages`
--

LOCK TABLES `support_messages` WRITE;
/*!40000 ALTER TABLE `support_messages` DISABLE KEYS */;
INSERT INTO `support_messages` VALUES (1,7,NULL,'привет',0,0,'2026-02-19 06:30:53'),(2,7,NULL,'у меня возник вопрос насчет чатов',0,0,'2026-02-19 06:31:10'),(4,7,7,'Здравствуйте! Чем могу помочь вам?',1,1,'2026-02-19 07:11:53'),(5,15,NULL,'Помогите мне создать группу',0,0,'2026-02-19 07:46:10');
/*!40000 ALTER TABLE `support_messages` ENABLE KEYS */;
UNLOCK TABLES;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = '' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`tesatour`@`localhost`*/ /*!50003 TRIGGER `support_messages_before_insert` BEFORE INSERT ON `support_messages` FOR EACH ROW
BEGIN
    SET NEW.created_at = IFNULL(NEW.created_at, NOW());
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `system_notifications`
--

DROP TABLE IF EXISTS `system_notifications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `system_notifications` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `message` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `target_type` enum('all','user','group') COLLATE utf8mb4_unicode_ci DEFAULT 'all',
  `target_id` int(10) unsigned DEFAULT NULL,
  `sent_by` int(10) unsigned NOT NULL,
  `sent_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_target` (`target_type`,`target_id`),
  KEY `idx_sent_by` (`sent_by`),
  CONSTRAINT `system_notifications_ibfk_1` FOREIGN KEY (`sent_by`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `system_notifications`
--

LOCK TABLES `system_notifications` WRITE;
/*!40000 ALTER TABLE `system_notifications` DISABLE KEYS */;
/*!40000 ALTER TABLE `system_notifications` ENABLE KEYS */;
UNLOCK TABLES;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = '' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`tesatour`@`localhost`*/ /*!50003 TRIGGER `system_notifications_before_insert` BEFORE INSERT ON `system_notifications` FOR EACH ROW
BEGIN
    SET NEW.sent_at = IFNULL(NEW.sent_at, NOW());
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `email` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password_hash` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `first_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `middle_name` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `birth_date` date NOT NULL,
  `avatar` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `telegram_id` bigint(20) DEFAULT NULL,
  `telegram_username` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_admin` tinyint(1) DEFAULT '0',
  `admin_mode` tinyint(1) DEFAULT '0',
  `user_status` enum('hobbyist','agency') COLLATE utf8mb4_unicode_ci DEFAULT 'hobbyist',
  `subscription_active` tinyint(1) DEFAULT '0',
  `subscription_expires_at` datetime DEFAULT NULL,
  `push_subscription` text COLLATE utf8mb4_unicode_ci,
  `account_type` enum('amateur','agency') COLLATE utf8mb4_unicode_ci DEFAULT 'amateur',
  `geolocation_enabled` tinyint(1) DEFAULT '0',
  `last_latitude` decimal(10,8) DEFAULT NULL,
  `last_longitude` decimal(11,8) DEFAULT NULL,
  `last_location_update` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  UNIQUE KEY `telegram_id` (`telegram_id`),
  KEY `idx_email` (`email`),
  KEY `idx_telegram_id` (`telegram_id`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (7,'ibrprofile@icloud.com','$2y$10$x8dd9oZr58V7y4oe1b54ru2OB0Ogrw66E6LYXIkmdRMlGp63xenMO','Азамат','Ибрагимов','Азимович','2000-03-31','avatars/8ba5280c9c02f8dcb08a759f68077389.jpg',11282993,'ibrprofile',1,0,'hobbyist',0,NULL,NULL,'amateur',1,55.66062327,37.60963050,'2026-02-19 07:15:40',NULL,NULL),(8,'ronaldo@gmail.com','$2y$10$J1qt7Fsx3MkUENgmcAI0V.kMbfNwy0yL2JbEJvSltwhqgWPgFbzLS','Роналду','Криштиану','Авейру','1984-02-07','avatars/08dbf2974434c56ec4e559aa91a1ce3d.jpg',2006363325,'ibrprofile2',0,0,'hobbyist',0,NULL,NULL,'amateur',1,55.66039273,37.60990526,'2026-02-19 16:11:38',NULL,NULL),(9,'trey_001@vk.com','$2y$10$zBFclIRuLih7axDQMF/8Q.B5ju3HRYGR5KvHsOhjgyI6wZzUwx.9a','Азамат','Ибрагимов ','Азимович','2026-01-03','avatars/e80622241aad49037a1c55e917377204.jpg',NULL,NULL,0,0,'hobbyist',0,NULL,NULL,'amateur',1,55.65997660,37.61026220,'2026-01-27 01:17:44',NULL,NULL),(10,'mts.gualia@gmail.com','$2y$10$Esq7Kn/U0cAxt7I4Cav93eXf4fUeShxDggJDU.cxsLUDCOS1rahQC','GUZALIYA','MUKHTAROVA','','2026-01-27',NULL,NULL,NULL,0,0,'hobbyist',0,NULL,NULL,'amateur',0,NULL,NULL,NULL,NULL,NULL),(11,'m4ail.mariyas@mail.ru','$2y$10$cQ2qBMWuFp1VLyvuRbzwHumKhgEf.5xZdKTIjlGquX8n8Lx0THgvO','Мария ','Смирнова ','Анатольевна ','1992-02-07',NULL,538798810,'Smir_nova92',0,0,'hobbyist',0,NULL,NULL,'amateur',1,55.65349517,37.61238867,'2026-02-19 13:47:25',NULL,NULL),(12,'ibr.azamat.0@gmail.com','$2y$10$9JMP3nF6rrIVo15VJnhcAuVZjj36ccVFYiuUdjurPjXmGl0TVfS4a','Shsh','Ibrprofile','Zhxh','2026-02-01',NULL,NULL,NULL,1,0,'hobbyist',0,NULL,NULL,'amateur',1,55.66016286,37.60980499,'2026-02-18 22:12:03',NULL,NULL),(13,'test1@test.test','$2y$10$21AACKQ/qvpkXjP4asG50eWA0uqr0FiKEe5lz3zDYEEOcVzZkHoh.','Фффф','Ифффф',NULL,'1111-11-11',NULL,NULL,NULL,0,0,'hobbyist',0,NULL,NULL,'amateur',1,55.66031732,37.60990407,'2026-02-19 07:39:47',NULL,NULL),(14,'shsh@jdjs.sjs','$2y$10$pnO/werdxhQB0uzo78sg5e//61c3PartWx33TgXA64J8gAP7mQ6vS','Аа','Аа',NULL,'2026-02-19',NULL,NULL,NULL,0,0,'hobbyist',0,NULL,NULL,'amateur',1,55.65997910,37.61034950,'2026-02-19 07:37:13',NULL,NULL),(15,'myemail@ru.ru','$2y$10$AFLr.Mvu5in396SQDqcugu5myuZan20aEBOBUmjqaJXSLrEzYsZn6','Азамат','Ибрагимов ',NULL,'2025-12-16',NULL,NULL,NULL,0,0,'hobbyist',0,NULL,NULL,'amateur',1,55.65993530,37.61031940,'2026-02-19 07:47:39',NULL,NULL);
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

-- Dump completed on 2026-03-08 13:32:27
