-- phpMyAdmin SQL Dump
-- version 5.1.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 11, 2022 at 06:06 AM
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
-- Table structure for table `bgt_produksi_afd`
--

CREATE TABLE `bgt_produksi_afd` (
  `id` int(11) NOT NULL,
  `tahun` varchar(50) NOT NULL,
  `lokasi_id` int(11) NOT NULL,
  `afdeling_id` int(11) NOT NULL,
  `b01` int(11) NOT NULL,
  `b02` int(11) NOT NULL,
  `b03` int(11) NOT NULL,
  `b04` int(11) NOT NULL,
  `b05` int(11) NOT NULL,
  `b06` int(11) NOT NULL,
  `b07` int(11) NOT NULL,
  `b08` int(11) NOT NULL,
  `b09` int(11) NOT NULL,
  `b10` int(11) NOT NULL,
  `b11` int(11) NOT NULL,
  `b12` int(11) NOT NULL,
  `dibuat_oleh` int(11) NOT NULL,
  `dibuat_tanggal` date NOT NULL,
  `diubah_oleh` int(11) NOT NULL,
  `diubah_tanggal` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `bgt_produksi_afd`
--
ALTER TABLE `bgt_produksi_afd`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `bgt_produksi_afd`
--
ALTER TABLE `bgt_produksi_afd`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
