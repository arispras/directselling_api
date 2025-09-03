-- phpMyAdmin SQL Dump
-- version 5.1.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 17, 2022 at 11:00 AM
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
-- Table structure for table `gbm_uom`
--

CREATE TABLE `gbm_uom` (
  `id` int(11) NOT NULL,
  `kode` varchar(50) NOT NULL,
  `nama` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `gbm_uom`
--

INSERT INTO `gbm_uom` (`id`, `kode`, `nama`) VALUES
(1, 'pcs', 'PCS'),
(2, 'kg', 'Kilogram'),
(4, 'ltr', '-'),
(5, 'btl', '-'),
(6, 'gr', '-'),
(7, 'ml', '-'),
(8, 'set', '-'),
(9, 'box', '-'),
(10, 'unit', '-'),
(11, 'pcs', '-'),
(12, 'roll', '-'),
(13, 'lbr', '-'),
(14, 'klg', '-'),
(15, 'bks', '-'),
(16, 'ktk', '-'),
(17, 'mtr', '-'),
(18, 'btg', '-'),
(19, 'pack', '-'),
(20, 'ball', '-'),
(21, 'psg', '-'),
(22, 'pail', '-'),
(23, 'set ', '-'),
(24, 'ser', '-'),
(25, 'psc', '-'),
(26, 'btg ', '-'),
(27, 'tube', '-'),
(28, 'buku', '-'),
(29, 'pak', '-'),
(30, 'karung', '-'),
(31, 'rim', '-'),
(32, 'bks ', '-'),
(33, 'bk', '-'),
(34, 'lsn', '-'),
(36, 'ken', '-');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `gbm_uom`
--
ALTER TABLE `gbm_uom`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `gbm_uom`
--
ALTER TABLE `gbm_uom`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
