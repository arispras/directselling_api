-- phpMyAdmin SQL Dump
-- version 5.1.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 16, 2022 at 10:50 AM
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
-- Table structure for table `prc_rekap_angkut_dt`
--

CREATE TABLE `prc_rekap_angkut_dt` (
  `id` int(11) NOT NULL,
  `rekap_id` int(11) NOT NULL,
  `sjpp_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `prc_rekap_angkut_dt`
--

INSERT INTO `prc_rekap_angkut_dt` (`id`, `rekap_id`, `sjpp_id`) VALUES
(1, 0, 116),
(2, 0, 116),
(3, 0, 116),
(14, 7, 116),
(15, 8, 116);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `prc_rekap_angkut_dt`
--
ALTER TABLE `prc_rekap_angkut_dt`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `prc_rekap_angkut_dt`
--
ALTER TABLE `prc_rekap_angkut_dt`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
