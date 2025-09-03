-- phpMyAdmin SQL Dump
-- version 5.1.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 16, 2022 at 10:49 AM
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
-- Table structure for table `inv_retur_pemakaian_dt`
--

CREATE TABLE `inv_retur_pemakaian_dt` (
  `id` int(11) NOT NULL,
  `inv_retur_pemakaian_id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `uom_id` int(11) NOT NULL,
  `qty` int(11) NOT NULL,
  `traksi_id` int(11) DEFAULT NULL,
  `blok_id` int(11) DEFAULT NULL,
  `kegiatan_id` int(11) DEFAULT NULL,
  `ket` varchar(55) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `inv_retur_pemakaian_dt`
--

INSERT INTO `inv_retur_pemakaian_dt` (`id`, `inv_retur_pemakaian_id`, `item_id`, `uom_id`, `qty`, `traksi_id`, `blok_id`, `kegiatan_id`, `ket`) VALUES
(7, 7, 4376, 0, 1, NULL, 580, 18, 'sda'),
(8, 7, 4376, 0, 1, NULL, 580, 20, 'sdasd');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `inv_retur_pemakaian_dt`
--
ALTER TABLE `inv_retur_pemakaian_dt`
  ADD PRIMARY KEY (`id`),
  ADD KEY `FK_inv_pemakaian_dt_inv_item` (`item_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `inv_retur_pemakaian_dt`
--
ALTER TABLE `inv_retur_pemakaian_dt`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
