-- phpMyAdmin SQL Dump
-- version 5.1.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 21, 2022 at 02:19 AM
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
-- Table structure for table `pks_tanki_formula2`
--

CREATE TABLE `pks_tanki_formula2` (
  `id` int(11) NOT NULL,
  `tanki_id` int(11) NOT NULL,
  `awal` double NOT NULL,
  `akhir` double NOT NULL,
  `simbol` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `pks_tanki_formula2`
--

INSERT INTO `pks_tanki_formula2` (`id`, `tanki_id`, `awal`, `akhir`, `simbol`) VALUES
(2, 18, 370, 1539, 'A'),
(3, 18, 1539, 3075, 'B'),
(4, 18, 3075, 4617, 'C'),
(5, 18, 4617, 6157, 'D'),
(6, 18, 6157, 7695, 'E'),
(7, 19, 296, 1540, 'A'),
(8, 19, 1540, 3081, 'B'),
(9, 19, 3081, 4618, 'C'),
(10, 19, 4618, 6155, 'D'),
(11, 19, 6155, 7688, 'E'),
(12, 19, 7688, 9239, 'F'),
(13, 19, 9239, 10779, 'G'),
(14, 19, 10779, 12331, 'H'),
(15, 22, 307, 1542, 'A'),
(16, 22, 1542, 3074, 'B'),
(17, 22, 3074, 4621, 'C'),
(18, 22, 4621, 6160, 'D'),
(19, 22, 6160, 7698, 'E'),
(20, 22, 7698, 9236, 'F'),
(21, 22, 9236, 10776, 'G'),
(22, 22, 10776, 12338, 'H');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `pks_tanki_formula2`
--
ALTER TABLE `pks_tanki_formula2`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `pks_tanki_formula2`
--
ALTER TABLE `pks_tanki_formula2`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
