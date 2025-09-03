-- phpMyAdmin SQL Dump
-- version 5.1.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 20, 2022 at 07:41 AM
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
-- Table structure for table `acc_permohonan_bayar_dt`
--

CREATE TABLE `acc_permohonan_bayar_dt` (
  `id` int(11) NOT NULL,
  `permohonan_bayar_id` int(11) NOT NULL,
  `keterangan` varchar(200) NOT NULL,
  `qty` double NOT NULL,
  `harga` double NOT NULL,
  `jumlah` double NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `acc_permohonan_bayar_dt`
--

INSERT INTO `acc_permohonan_bayar_dt` (`id`, `permohonan_bayar_id`, `keterangan`, `qty`, `harga`, `jumlah`) VALUES
(22, 4, 'Luigong Motor Greader. DP Pembayaran', 20, 20000, 400000);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `acc_permohonan_bayar_dt`
--
ALTER TABLE `acc_permohonan_bayar_dt`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `acc_permohonan_bayar_dt`
--
ALTER TABLE `acc_permohonan_bayar_dt`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
