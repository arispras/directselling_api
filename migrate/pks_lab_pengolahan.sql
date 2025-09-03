-- phpMyAdmin SQL Dump
-- version 5.1.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 23, 2022 at 12:08 PM
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
-- Table structure for table `pks_lab_pengolahan`
--

CREATE TABLE `pks_lab_pengolahan` (
  `id` int(11) NOT NULL,
  `mill_id` int(11) NOT NULL,
  `no_transaksi` varchar(50) NOT NULL,
  `tanggal` date NOT NULL,
  `cpo_moisture` double NOT NULL,
  `cpo_dobi` double NOT NULL,
  `cpo_ffa` double NOT NULL,
  `cpo_dirt` double NOT NULL,
  `kernel_moisture` double NOT NULL,
  `kernel_dobi` double NOT NULL,
  `kernel_ffa` double NOT NULL,
  `kernel_dirt` double NOT NULL,
  `cpo_los_fruit` double NOT NULL,
  `cpo_los_press` double NOT NULL,
  `cpo_los_nut` double NOT NULL,
  `cpo_los_e_bunch` double NOT NULL,
  `cpo_los_effluent` double NOT NULL,
  `kernel_los_fruit` double NOT NULL,
  `kernel_los_fiber_cyclone` double NOT NULL,
  `kernel_los_ltds1` double NOT NULL,
  `kernel_los_ltds2` double NOT NULL,
  `kernel_los_claybath` double NOT NULL,
  `diubah_oleh` int(11) NOT NULL,
  `diubah_tanggal` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `pks_lab_pengolahan`
--
ALTER TABLE `pks_lab_pengolahan`
  ADD PRIMARY KEY (`id`),
  ADD KEY `mill_id` (`mill_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `pks_lab_pengolahan`
--
ALTER TABLE `pks_lab_pengolahan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `pks_lab_pengolahan`
--
ALTER TABLE `pks_lab_pengolahan`
  ADD CONSTRAINT `pks_lab_pengolahan_ibfk_1` FOREIGN KEY (`mill_id`) REFERENCES `gbm_organisasi` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
