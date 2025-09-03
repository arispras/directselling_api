-- phpMyAdmin SQL Dump
-- version 5.1.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 14, 2021 at 04:52 PM
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
-- Table structure for table `pks_timbangan_customer`
--

CREATE TABLE `pks_timbangan_customer` (
  `id` int(11) NOT NULL,
  `mill_id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `estate_id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `tipe` varchar(3) NOT NULL,
  `no_tiket` varchar(50) NOT NULL,
  `no_referensi` varchar(50) NOT NULL,
  `tanggal` date NOT NULL,
  `jam_masuk` varchar(10) NOT NULL,
  `jam_keluar` varchar(10) NOT NULL,
  `jumlah_item` double NOT NULL,
  `jumlah_berondolan` double NOT NULL,
  `no_kendaraan` varchar(20) NOT NULL,
  `nama_supir` varchar(50) NOT NULL,
  `tara_kirim` double NOT NULL,
  `bruto_kirim` double NOT NULL,
  `netto_kirim` double NOT NULL,
  `keterangan` text NOT NULL,
  `diubah_oleh` int(11) NOT NULL,
  `diubah_tanggal` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `pks_timbangan_customer`
--

INSERT INTO `pks_timbangan_customer` (`id`, `mill_id`, `item_id`, `estate_id`, `customer_id`, `tipe`, `no_tiket`, `no_referensi`, `tanggal`, `jam_masuk`, `jam_keluar`, `jumlah_item`, `jumlah_berondolan`, `no_kendaraan`, `nama_supir`, `tara_kirim`, `bruto_kirim`, `netto_kirim`, `keterangan`, `diubah_oleh`, `diubah_tanggal`) VALUES
(4, 256, 5, 252, 3, 'INT', '0001', '0005', '2021-12-14', '21:37', '21:37', 50, 50, 'F8080', 'Jhon Drive', 50, 50, 50, 'sample keterangan', 0, '2021-12-14 16:01:56'),
(5, 260, 5, 252, 4, 'INT', '005', '0010', '2021-12-14', '22:04', '22:04', 10, 10, 'F8008', 'Jhon Drive', 10, 10, 10, 'sample keterangan', 0, '2021-12-14 16:05:10');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `pks_timbangan_customer`
--
ALTER TABLE `pks_timbangan_customer`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `pks_timbangan_customer`
--
ALTER TABLE `pks_timbangan_customer`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
