-- phpMyAdmin SQL Dump
-- version 5.1.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Aug 25, 2022 at 02:34 AM
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
-- Table structure for table `hrms_approvall_setting_pengajuan_cuti`
--

CREATE TABLE `hrms_approvall_setting_pengajuan_cuti` (
  `id` int(11) NOT NULL,
  `lokasi_id` int(11) NOT NULL,
  `kode` text NOT NULL,
  `karyawan_id` int(11) NOT NULL,
  `is_finish` tinyint(1) NOT NULL,
  `is_need_approve` tinytext NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `hrms_approvall_setting_pengajuan_cuti`
--

INSERT INTO `hrms_approvall_setting_pengajuan_cuti` (`id`, `lokasi_id`, `kode`, `karyawan_id`, `is_finish`, `is_need_approve`) VALUES
(1, 252, 'PC3', 186, 0, ''),
(2, 252, 'PC2', 186, 1, ''),
(3, 260, 'PC4', 186, 0, ''),
(5, 252, 'PC1', 186, 0, ''),
(6, 260, 'PC1', 150, 0, ''),
(7, 260, 'PC2', 150, 1, '');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `hrms_approvall_setting_pengajuan_cuti`
--
ALTER TABLE `hrms_approvall_setting_pengajuan_cuti`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `hrms_approvall_setting_pengajuan_cuti`
--
ALTER TABLE `hrms_approvall_setting_pengajuan_cuti`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
