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

-- Dumping structure for table plantation.est_sensus_panen_ht
CREATE TABLE IF NOT EXISTS `est_sensus_panen_ht` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `lokasi_id` int(11) DEFAULT NULL,
  `afdeling_id` int(11) DEFAULT NULL,
  `tahun` varchar(50) DEFAULT NULL,
  `bulan` varchar(50) DEFAULT NULL,
  `ket` varchar(50) DEFAULT NULL,
  `dibuat_oleh` int(11) DEFAULT NULL,
  `dibuat_tanggal` date DEFAULT NULL,
  `diubah_oleh` int(11) DEFAULT NULL,
  `diubah_tanggal` date DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4;

-- Dumping data for table plantation.est_sensus_panen_ht: ~2 rows (approximately)
/*!40000 ALTER TABLE `est_sensus_panen_ht` DISABLE KEYS */;
INSERT INTO `est_sensus_panen_ht` (`id`, `lokasi_id`, `afdeling_id`, `tahun`, `bulan`, `ket`, `dibuat_oleh`, `dibuat_tanggal`, `diubah_oleh`, `diubah_tanggal`) VALUES
	(4, 265, 271, '2023', '01', 'yan', 40, '2023-07-09', 40, '2023-07-09');
/*!40000 ALTER TABLE `est_sensus_panen_ht` ENABLE KEYS */;

/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
