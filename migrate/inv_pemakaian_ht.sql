-- phpMyAdmin SQL Dump
-- version 5.1.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Feb 21, 2022 at 06:48 AM
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
-- Table structure for table `inv_pemakaian_ht`
--

CREATE TABLE `inv_pemakaian_ht` (
  `id` int(11) NOT NULL,
  `lokasi_id` int(11) NOT NULL,
  `lokasi_afd_id` int(11) NOT NULL,
  `gudang_id` int(11) NOT NULL,
  `karyawan_id` int(11) NOT NULL,
  `inv_permintaan_id` int(11) NOT NULL,
  `no_transaksi` varchar(50) DEFAULT NULL,
  `tipe` varchar(50) DEFAULT NULL,
  `tanggal` date NOT NULL,
  `catatan` varchar(50) DEFAULT NULL,
  `dibuat_oleh` int(11) DEFAULT NULL,
  `dibuat_tanggal` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `inv_pemakaian_ht`
--
ALTER TABLE `inv_pemakaian_ht`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `inv_pemakaian_ht`
--
ALTER TABLE `inv_pemakaian_ht`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
