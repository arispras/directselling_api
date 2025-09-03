-- phpMyAdmin SQL Dump
-- version 5.1.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 16, 2022 at 10:49 AM
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
-- Table structure for table `inv_retur_pemakaian_ht`
--

CREATE TABLE `inv_retur_pemakaian_ht` (
  `id` int(11) NOT NULL,
  `lokasi_id` int(11) NOT NULL,
  `lokasi_afd_id` int(11) DEFAULT NULL,
  `lokasi_traksi_id` int(11) DEFAULT NULL,
  `gudang_id` int(11) NOT NULL,
  `karyawan_id` int(11) DEFAULT NULL,
  `inv_pemakaian_id` int(11) DEFAULT NULL,
  `no_transaksi` varchar(50) DEFAULT NULL,
  `tipe` varchar(50) DEFAULT NULL,
  `tanggal` date NOT NULL,
  `catatan` varchar(50) DEFAULT NULL,
  `dibuat_oleh` int(11) DEFAULT NULL,
  `dibuat_tanggal` datetime DEFAULT NULL,
  `is_posting` tinyint(1) DEFAULT 0,
  `diposting_oleh` int(11) DEFAULT NULL,
  `diposting_tanggal` datetime DEFAULT NULL,
  `diubah_oleh` int(11) DEFAULT NULL,
  `diubah_tanggal` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `inv_retur_pemakaian_ht`
--

INSERT INTO `inv_retur_pemakaian_ht` (`id`, `lokasi_id`, `lokasi_afd_id`, `lokasi_traksi_id`, `gudang_id`, `karyawan_id`, `inv_pemakaian_id`, `no_transaksi`, `tipe`, `tanggal`, `catatan`, `dibuat_oleh`, `dibuat_tanggal`, `is_posting`, `diposting_oleh`, `diposting_tanggal`, `diubah_oleh`, `diubah_tanggal`) VALUES
(7, 252, 253, NULL, 740, 186, NULL, '0001/RPB/SBNE/0722', 'UNIT', '2022-07-16', 'sdasasasa', 40, '2022-07-16 01:36:50', 0, NULL, NULL, 40, '2022-07-16 01:54:24');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `inv_retur_pemakaian_ht`
--
ALTER TABLE `inv_retur_pemakaian_ht`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `inv_retur_pemakaian_ht`
--
ALTER TABLE `inv_retur_pemakaian_ht`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
