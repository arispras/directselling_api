-- phpMyAdmin SQL Dump
-- version 5.1.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 06, 2022 at 11:04 AM
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
-- Table structure for table `est_spk_ht`
--

CREATE TABLE `est_spk_ht` (
  `id` int(11) NOT NULL,
  `lokasi_id` int(11) NOT NULL,
  `supplier_id` int(11) NOT NULL,
  `no_spk` varchar(100) NOT NULL,
  `tanggal` date NOT NULL,
  `estimasi` double NOT NULL,
  `tgl_mulai` date NOT NULL,
  `tgl_akhir` date NOT NULL,
  `dibuat_oleh` int(11) NOT NULL,
  `dibuat_tanggal` date NOT NULL,
  `diubah_oleh` int(11) NOT NULL,
  `diubah_Tanggal` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `est_spk_ht`
--

INSERT INTO `est_spk_ht` (`id`, `lokasi_id`, `supplier_id`, `no_spk`, `tanggal`, `estimasi`, `tgl_mulai`, `tgl_akhir`, `dibuat_oleh`, `dibuat_tanggal`, `diubah_oleh`, `diubah_Tanggal`) VALUES
(6, 252, 7, '0', '2022-04-06', 0, '2022-04-06', '2022-04-06', 0, '0000-00-00', 0, '0000-00-00');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `est_spk_ht`
--
ALTER TABLE `est_spk_ht`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `est_spk_ht`
--
ALTER TABLE `est_spk_ht`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
