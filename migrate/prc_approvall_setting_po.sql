-- phpMyAdmin SQL Dump
-- version 5.1.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 30, 2022 at 02:21 PM
-- Server version: 10.4.18-MariaDB
-- PHP Version: 7.3.28

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
-- Table structure for table `prc_approvall_setting_po`
--

CREATE TABLE `prc_approvall_setting_po` (
  `id` int(11) NOT NULL,
  `lokasi_id` int(11) NOT NULL,
  `kode` text NOT NULL,
  `karyawan_id` int(11) NOT NULL,
  `is_finish` tinyint(1) NOT NULL DEFAULT 0,
  `is_need_approve` tinyint(1) NOT NULL DEFAULT 1,
  `max_amount` double NOT NULL,
  `min_amount` double NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `prc_approvall_setting_po`
--

INSERT INTO `prc_approvall_setting_po` (`id`, `lokasi_id`, `kode`, `karyawan_id`, `is_finish`, `is_need_approve`, `max_amount`, `min_amount`) VALUES
(3, 265, 'PP1', 187, 1, 1, 0, 0),
(7, 265, 'PP1', 150, 0, 1, 0, 0),
(8, 265, 'PP2', 150, 1, 1, 0, 0),
(9, 265, 'PP3', 150, 1, 1, 0, 0),
(10, 263, 'PP1', 150, 0, 1, 3000000, 2),
(11, 263, 'PP2', 150, 1, 1, 0, 0),
(12, 263, 'PP2', 288, 1, 1, 0, 0);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `prc_approvall_setting_po`
--
ALTER TABLE `prc_approvall_setting_po`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `prc_approvall_setting_po`
--
ALTER TABLE `prc_approvall_setting_po`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
