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
