-- phpMyAdmin SQL Dump
-- version 5.1.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 17, 2022 at 10:58 AM
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
-- Table structure for table `inv_kategori`
--

CREATE TABLE `inv_kategori` (
  `id` int(11) NOT NULL,
  `nama` varchar(45) DEFAULT NULL,
  `kode` varchar(20) NOT NULL,
  `acc_akun_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `inv_kategori`
--

INSERT INTO `inv_kategori` (`id`, `nama`, `kode`, `acc_akun_id`) VALUES
(4, 'PUPUK', '001', 10),
(5, 'PRODUK', '', 0),
(6, 'SPAREPART', '', 0),
(7, 'Bangunan', '', 0),
(8, 'IT & KOMPUTER', '', 0),
(9, 'PERALATAN KANTOR', '', 0),
(17, 'BBM dan Pelumas', '1150131', 1598),
(18, 'Bahan Kimia', '1150132', 1599),
(19, 'Bahan Dan Suku Cadang', '1150135', 1602),
(20, 'Bahan Umum', '1150136', 1603),
(21, 'Perlengkapan APD', '1150139', 1606),
(22, 'Agrochemical', '1150122', 1589),
(23, 'Pupuk', '1150123', 1590),
(24, 'Kacangan', '1150124', 1591),
(25, 'Bahan Bangunan dan Umum', '1150126', 1593),
(26, 'Pembelian Lokal', '1150125', 1592);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `inv_kategori`
--
ALTER TABLE `inv_kategori`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `inv_kategori`
--
ALTER TABLE `inv_kategori`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
