-- phpMyAdmin SQL Dump
-- version 5.1.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 29, 2021 at 08:22 AM
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
-- Table structure for table `pks_timbangan_kirim`
--

CREATE TABLE `pks_timbangan_kirim` (
  `id` int(11) NOT NULL,
  `mill_id` int(11) DEFAULT NULL,
  `item_id` int(11) DEFAULT NULL,
  `customer_id` int(11) DEFAULT NULL,
  `tipe` varchar(3) DEFAULT NULL,
  `no_tiket` varchar(50) DEFAULT NULL,
  `no_referensi` varchar(50) DEFAULT NULL,
  `tanggal` date NOT NULL,
  `jam_masuk` varchar(10) NOT NULL,
  `jam_keluar` varchar(10) NOT NULL,
  `jumlah_item` double DEFAULT NULL,
  `no_kendaraan` varchar(20) DEFAULT NULL,
  `nama_supir` varchar(50) NOT NULL,
  `tara_kirim` double NOT NULL DEFAULT 0,
  `bruto_kirim` double NOT NULL DEFAULT 0,
  `netto_kirim` double NOT NULL DEFAULT 0,
  `keterangan` text DEFAULT NULL,
  `diubah_oleh` int(11) NOT NULL,
  `diubah_tanggal` datetime NOT NULL,
  `transportir_id` int(11) NOT NULL,
  `no_kontrak_timbangan` varchar(100) DEFAULT NULL,
  `no_do_timbangan` int(100) DEFAULT NULL,
  `suhu` int(11) NOT NULL,
  `jumlah_segel` int(11) NOT NULL DEFAULT 0,
  `no_segel` varchar(100) NOT NULL,
  `tangki_id` int(11) NOT NULL,
  `ffa` int(11) NOT NULL,
  `moisture` int(11) NOT NULL,
  `dirt` int(11) NOT NULL,
  `dobi` int(11) NOT NULL,
  `uoid` varchar(50) DEFAULT NULL,
  `segel_1` varchar(255) NOT NULL,
  `segel_2` varchar(255) NOT NULL,
  `segel_3` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `pks_timbangan_kirim`
--
ALTER TABLE `pks_timbangan_kirim`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `pks_timbangan_kirim`
--
ALTER TABLE `pks_timbangan_kirim`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
