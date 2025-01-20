DROP USER IF EXISTS 'openaiot-admin'@'%';
CREATE USER 'openaiot-admin'@'%' IDENTIFIED BY 'OpenAIoT-mysql-password';
GRANT ALL PRIVILEGES ON *.* TO 'openaiot-admin'@'%';
FLUSH PRIVILEGES;

CREATE DATABASE IF NOT EXISTS `db_main` /*!40100 DEFAULT CHARACTER SET utf8mb3 */ /*!80016 DEFAULT ENCRYPTION='N' */;
USE `db_main`;

-- Dumping structure for table db_main.plants
CREATE TABLE IF NOT EXISTS `plants` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `plant_name` varchar(50) DEFAULT NULL,
  `applist1` bigint unsigned DEFAULT NULL,
  `applist2` bigint unsigned DEFAULT NULL,
  `applist3` bigint unsigned DEFAULT NULL,
  `logo` varchar(50) DEFAULT NULL,
  `add_date` datetime DEFAULT CURRENT_TIMESTAMP,
  `update_date` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- Data exporting was unselected.

-- Dumping structure for table db_main.users
CREATE TABLE IF NOT EXISTS `users` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(50) DEFAULT NULL,
  `password` varchar(500) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `fullname` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `user_role` enum('Admin','Manager','Operator') DEFAULT NULL,
  `plant_id` int unsigned DEFAULT NULL,
  `last_app` tinyint unsigned DEFAULT NULL,
  `token` varchar(500) DEFAULT NULL,
  `add_date` datetime DEFAULT CURRENT_TIMESTAMP,
  `update_date` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `user_img` varchar(50) DEFAULT NULL,
  `isactive` tinyint DEFAULT '1',
  `last_login` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb3;

INSERT INTO `users` (`id`, `username`, `password`, `fullname`, `user_role`, `plant_id`, `last_app`, `token`, `add_date`, `update_date`, `user_img`, `isactive`, `last_login`) VALUES (3, 'admin', '$2b$10$I/Vs/mDsuohyG2rzFbcqpuAVHMSAvVHRlfwbqoU7FIkb0k7DctMna', 'Admin', 'Admin', 100, NULL, NULL, '2025-01-11 07:59:38', '2025-01-12 13:49:03', NULL, 1, NULL);

-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Server version:               8.4.3 - MySQL Community Server - GPL
-- Server OS:                    Linux
-- HeidiSQL Version:             12.3.0.6589
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


-- Dumping database structure for db_sii
CREATE DATABASE IF NOT EXISTS `db_sii` /*!40100 DEFAULT CHARACTER SET utf8mb3 */ /*!80016 DEFAULT ENCRYPTION='N' */;
USE `db_sii`;

-- Dumping structure for table db_sii.admin_pass
CREATE TABLE IF NOT EXISTS `admin_pass` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nama` varchar(50) DEFAULT NULL,
  `pass` varchar(50) DEFAULT NULL,
  `tanggal` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `plant_id` int DEFAULT NULL,
  `isvalid` tinyint DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Data exporting was unselected.

-- Dumping structure for table db_sii.alarm_module
CREATE TABLE IF NOT EXISTS `alarm_module` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `isvalid` tinyint unsigned NOT NULL DEFAULT '1',
  `unit_name` varchar(50) DEFAULT NULL,
  `unit_ip` varchar(50) DEFAULT NULL,
  `last_access` varchar(50) DEFAULT NULL,
  `unit_port` smallint unsigned DEFAULT NULL,
  `plant_id` int unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unit_name` (`unit_name`,`plant_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb3;

-- Data exporting was unselected.

-- Dumping structure for table db_sii.input_module
CREATE TABLE IF NOT EXISTS `input_module` (
  `id` tinyint NOT NULL AUTO_INCREMENT,
  `modul` bigint DEFAULT '0',
  `c1` tinyint DEFAULT NULL,
  `c2` tinyint DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `modul` (`modul`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb3;

-- Data exporting was unselected.

-- Dumping structure for table db_sii.manifest_data
CREATE TABLE IF NOT EXISTS `manifest_data` (
  `id` int NOT NULL AUTO_INCREMENT,
  `isvalid` tinyint NOT NULL DEFAULT '1',
  `proses` decimal(10,2) DEFAULT '0.00',
  `manifest` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `arrival_date` date DEFAULT NULL,
  `arrival_time` time DEFAULT NULL,
  `outtime` datetime DEFAULT NULL,
  `dock_code` varchar(10) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `pline_code` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `pline_no` varchar(10) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `supplier_name` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `supplier_code` varchar(10) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `order_no` varchar(50) DEFAULT NULL,
  `subroute` varchar(10) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `part_no` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `part_name` varchar(200) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `unique_no` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `box_type` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `qty_per_kanban` int DEFAULT NULL,
  `qty_kanban` int DEFAULT NULL,
  `qty_order` int DEFAULT NULL,
  `plant_id` int DEFAULT NULL,
  `submit_time` datetime DEFAULT CURRENT_TIMESTAMP,
  `update_time` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `user_id` bigint DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `manifest` (`manifest`,`part_no`)
) ENGINE=InnoDB AUTO_INCREMENT=92 DEFAULT CHARSET=utf8mb3;

-- Data exporting was unselected.

-- Dumping structure for table db_sii.manifest_halt
CREATE TABLE IF NOT EXISTS `manifest_halt` (
  `kode` varchar(50) DEFAULT NULL,
  `nama` varchar(50) DEFAULT NULL,
  `alasan` text,
  `tanggal` datetime DEFAULT NULL,
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `plant_id` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Data exporting was unselected.

-- Dumping structure for table db_sii.master_data
CREATE TABLE IF NOT EXISTS `master_data` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `isvalid` tinyint NOT NULL DEFAULT '1',
  `timestamp` datetime DEFAULT CURRENT_TIMESTAMP,
  `kanban_cus` varchar(50) DEFAULT NULL,
  `kanban_sii` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `desc` text,
  `model` varchar(50) DEFAULT NULL,
  `cust` varchar(50) DEFAULT NULL,
  `qty` int unsigned DEFAULT NULL,
  `box_type` varchar(50) DEFAULT NULL,
  `box_remark` varchar(50) DEFAULT NULL,
  `addedby` bigint unsigned DEFAULT NULL,
  `plant_id` int unsigned DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=50 DEFAULT CHARSET=utf8mb3;

-- Data exporting was unselected.

-- Dumping structure for table db_sii.role_set
CREATE TABLE IF NOT EXISTS `role_set` (
  `id` bigint NOT NULL AUTO_INCREMENT,
  `user_id` bigint DEFAULT NULL,
  `role_id` tinyint DEFAULT NULL COMMENT '1 admin, 2 operator',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb3;

-- Data exporting was unselected.

-- Dumping structure for table db_sii.scan_manifest
CREATE TABLE IF NOT EXISTS `scan_manifest` (
  `id` bigint NOT NULL AUTO_INCREMENT,
  `timestamp` datetime DEFAULT CURRENT_TIMESTAMP,
  `manifest_id` varchar(50) DEFAULT NULL,
  `scan_part` varchar(50) DEFAULT NULL,
  `scan_sii` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `note` text,
  `scanby` bigint DEFAULT NULL,
  `result` tinyint DEFAULT '0',
  `plant_id` int DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb3 ROW_FORMAT=DYNAMIC;

-- Data exporting was unselected.

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
