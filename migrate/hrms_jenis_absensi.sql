-- phpMyAdmin SQL Dump
-- version 5.1.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 05, 2022 at 04:17 PM
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
-- Table structure for table `hrms_jenis_absensi`
--

CREATE TABLE `hrms_jenis_absensi` (
  `id` int(11) NOT NULL,
  `kode` varchar(100) NOT NULL,
  `keterangan` text NOT NULL,
  `tipe` varchar(50) NOT NULL,
  `jumlah_hk` double NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `hrms_jenis_absensi`
--

INSERT INTO `hrms_jenis_absensi` (`id`, `kode`, `keterangan`, `tipe`, `jumlah_hk`) VALUES
(1, '001', 'sample from pma', 'BELUM', 20),
(2, 'TR001', 'sample', 'SUDAH', 70);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `hrms_jenis_absensi`
--
ALTER TABLE `hrms_jenis_absensi`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `hrms_jenis_absensi`
--
ALTER TABLE `hrms_jenis_absensi`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
