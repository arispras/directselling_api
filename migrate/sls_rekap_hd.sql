-- phpMyAdmin SQL Dump
-- version 5.1.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 15, 2021 at 06:50 PM
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
-- Table structure for table `sls_rekap_hd`
--

CREATE TABLE `sls_rekap_hd` (
  `id` int(11) NOT NULL,
  `lokasi_id` int(11) NOT NULL,
  `no_rekap` varchar(50) NOT NULL,
  `tanggal` date NOT NULL,
  `customer_id` int(11) NOT NULL,
  `spk_id` int(11) NOT NULL,
  `periode_kt_dari` date NOT NULL,
  `periode_kt_sd` date NOT NULL,
  `item_id` int(11) NOT NULL,
  `total_berat_terima` float NOT NULL,
  `adj_berat_terima` int(11) NOT NULL,
  `total_berat_tagihan` float NOT NULL,
  `harga_satuan` int(11) NOT NULL,
  `total_tagihan` float NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `sls_rekap_hd`
--
ALTER TABLE `sls_rekap_hd`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `sls_rekap_hd`
--
ALTER TABLE `sls_rekap_hd`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
