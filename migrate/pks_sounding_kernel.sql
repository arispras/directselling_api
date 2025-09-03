-- phpMyAdmin SQL Dump
-- version 5.1.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Feb 03, 2022 at 01:04 PM
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
-- Table structure for table `pks_sounding_kernel`
--

CREATE TABLE `pks_sounding_kernel` (
  `id` int(11) NOT NULL,
  `name` varchar(45) NOT NULL,
  `tanggal` date NOT NULL,
  `tanki_id` int(11) NOT NULL,
  `hasil_ukur_a` double NOT NULL,
  `hasil_ukur_b` double NOT NULL,
  `hasil_ukur_c` double NOT NULL,
  `hasil_ukur_d` double NOT NULL,
  `stok_a` double NOT NULL,
  `stok_b` double NOT NULL,
  `stok_c` double NOT NULL,
  `stok_d` double NOT NULL,
  `hasil_sounding` double NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `pks_sounding_kernel`
--

INSERT INTO `pks_sounding_kernel` (`id`, `name`, `tanggal`, `tanki_id`, `hasil_ukur_a`, `hasil_ukur_b`, `hasil_ukur_c`, `hasil_ukur_d`, `stok_a`, `stok_b`, `stok_c`, `stok_d`, `hasil_sounding`) VALUES
(2, 'Riann', '2022-02-03', 19, 1, 2, 3, 4, 1, 2, 3, 4, 5);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `pks_sounding_kernel`
--
ALTER TABLE `pks_sounding_kernel`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `pks_sounding_kernel`
--
ALTER TABLE `pks_sounding_kernel`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
