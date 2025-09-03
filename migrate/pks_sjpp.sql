-- phpMyAdmin SQL Dump
-- version 5.1.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 30, 2022 at 10:00 AM
-- Server version: 10.4.21-MariaDB
-- PHP Version: 7.4.25

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `plantation`
--

-- --------------------------------------------------------

--
-- Table structure for table `pks_sjpp`
--

CREATE TABLE `pks_sjpp` (
  `id` int(11) NOT NULL,
  `pks_timbangan_kirim_id` int(11) NOT NULL,
  `mill_id` int(11) NOT NULL,
  `intruksi_id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `tanggal` date NOT NULL,
  `no_surat` varchar(255) NOT NULL,
  `no_ktp_sim` varchar(50) NOT NULL,
  `alamat_pengiriman` varchar(255) DEFAULT NULL,
  `tanggal_customer` date DEFAULT NULL,
  `moisture` double NOT NULL,
  `tara_customer` double DEFAULT NULL,
  `bruto_customer` double DEFAULT NULL,
  `netto_customer` double DEFAULT NULL,
  `diubah_oleh` int(11) DEFAULT NULL,
  `diubah_tanggal` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `pks_sjpp`
--
ALTER TABLE `pks_sjpp`
  ADD PRIMARY KEY (`id`),
  ADD KEY `intruksi_id` (`intruksi_id`),
  ADD KEY `pks_timbangan_kirim_id` (`pks_timbangan_kirim_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `pks_sjpp`
--
ALTER TABLE `pks_sjpp`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=52;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `pks_sjpp`
--
ALTER TABLE `pks_sjpp`
  ADD CONSTRAINT `pks_sjpp_ibfk_1` FOREIGN KEY (`intruksi_id`) REFERENCES `sls_intruksi_kirim` (`id`),
  ADD CONSTRAINT `pks_sjpp_ibfk_2` FOREIGN KEY (`pks_timbangan_kirim_id`) REFERENCES `pks_timbangan_kirim` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
