-- phpMyAdmin SQL Dump
-- version 5.1.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 02, 2022 at 02:05 PM
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
-- Table structure for table `pks_produksi_harian`
--

CREATE TABLE `pks_produksi_harian` (
  `id` int(11) NOT NULL,
  `mill_id` int(11) NOT NULL,
  `no_transaksi` varchar(50) NOT NULL,
  `tanggal` date NOT NULL,
  `tbs_olah` double NOT NULL,
  `tbs_kemarin` double NOT NULL,
  `tbs_masuk` double NOT NULL,
  `tbs_sisa` double NOT NULL,
  `cpo_kg` double NOT NULL,
  `cpo_moisture` double NOT NULL,
  `cpo_dobi` double NOT NULL,
  `cpo_ffa` double NOT NULL,
  `cpo_dirt` double NOT NULL,
  `kernel_kg` double NOT NULL,
  `kernel_moisture` double NOT NULL,
  `kernel_dobi` double NOT NULL,
  `kernel_ffa` double NOT NULL,
  `kernel_dirt` double NOT NULL,
  `cpo_los_usb` double NOT NULL,
  `cpo_los_fruit_empty_bunch` double NOT NULL,
  `cpo_los_empty_bunch_stalk` double NOT NULL,
  `cpo_los_fiber_fresh_cake` double NOT NULL,
  `cpo_los_nut_fresh_cake` double NOT NULL,
  `cpo_los_effluent` double NOT NULL,
  `cpo_los_solid_decanter` double NOT NULL,
  `kernel_los_usb` double NOT NULL,
  `kernel_los_fruit_empty_bunch` double NOT NULL,
  `kernel_fiber_cyclone` double NOT NULL,
  `kernel_los_ltds` double NOT NULL,
  `kernel_los_claybath` double NOT NULL,
  `kernel_los_hydrocyclone` double NOT NULL,
  `diubah_oleh` int(11) NOT NULL,
  `diubah_tanggal` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `pks_produksi_harian`
--
ALTER TABLE `pks_produksi_harian`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `pks_produksi_harian`
--
ALTER TABLE `pks_produksi_harian`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
