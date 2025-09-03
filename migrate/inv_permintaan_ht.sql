-- phpMyAdmin SQL Dump
-- version 5.1.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Feb 18, 2022 at 07:40 AM
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
-- Table structure for table `inv_permintaan_ht`
--

CREATE TABLE `inv_permintaan_ht` (
  `id` int(11) NOT NULL,
  `lokasi_id` int(11) NOT NULL,
  `lokasi_afd_id` int(11) NOT NULL,
  `gudang_id` int(11) NOT NULL,
  `karyawan_id` int(11) NOT NULL,
  `no_transaksi` varchar(50) DEFAULT NULL,
  `tipe` varchar(50) DEFAULT NULL,
  `tanggal` date NOT NULL,
  `catatan` varchar(50) DEFAULT NULL,
  `dibuat_oleh` int(11) DEFAULT NULL,
  `dibuat_tanggal` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `inv_permintaan_ht`
--

INSERT INTO `inv_permintaan_ht` (`id`, `lokasi_id`, `lokasi_afd_id`, `gudang_id`, `karyawan_id`, `no_transaksi`, `tipe`, `tanggal`, `catatan`, `dibuat_oleh`, `dibuat_tanggal`) VALUES
(5, 260, 267, 737, 186, 'A011', '1', '2022-02-18', 'ket', NULL, NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `inv_permintaan_ht`
--
ALTER TABLE `inv_permintaan_ht`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `inv_permintaan_ht`
--
ALTER TABLE `inv_permintaan_ht`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
