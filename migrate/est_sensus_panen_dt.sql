-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Server version:               10.4.21-MariaDB - mariadb.org binary distribution
-- Server OS:                    Win64
-- HeidiSQL Version:             11.3.0.6295
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

-- Dumping structure for table plantation.est_sensus_panen_dt
CREATE TABLE IF NOT EXISTS `est_sensus_panen_dt` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sensus_panen_id` int(11) DEFAULT NULL,
  `blok_id` int(11) DEFAULT NULL,
  `jjg` double DEFAULT NULL,
  `kg` double DEFAULT NULL,
  `bjr` double DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4;

-- Dumping data for table plantation.est_sensus_panen_dt: ~3 rows (approximately)
/*!40000 ALTER TABLE `est_sensus_panen_dt` DISABLE KEYS */;
INSERT INTO `est_sensus_panen_dt` (`id`, `sensus_panen_id`, `blok_id`, `jjg`, `kg`, `bjr`) VALUES
	(5, 4, 718, 12, 12, 12),
	(6, 4, 734, 11, 11, 11);
/*!40000 ALTER TABLE `est_sensus_panen_dt` ENABLE KEYS */;

/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
