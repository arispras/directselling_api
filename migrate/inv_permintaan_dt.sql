-- phpMyAdmin SQL Dump
-- version 5.1.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Feb 18, 2022 at 07:40 AM
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
-- Table structure for table `inv_permintaan_dt`
--

CREATE TABLE `inv_permintaan_dt` (
  `id` int(11) NOT NULL,
  `inv_permintaan_id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `uom_id` int(11) NOT NULL,
  `qty` int(11) NOT NULL,
  `traksi_id` int(11) NOT NULL,
  `blok_id` int(11) NOT NULL,
  `kegiatan_id` int(11) NOT NULL,
  `ket` varchar(55) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `inv_permintaan_dt`
--

INSERT INTO `inv_permintaan_dt` (`id`, `inv_permintaan_id`, `item_id`, `uom_id`, `qty`, `traksi_id`, `blok_id`, `kegiatan_id`, `ket`) VALUES
(35, 5, 10, 1, 12, 1, 580, 1, 'abc'),
(36, 5, 6, 2, 20, 2, 581, 1, 'defg');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `inv_permintaan_dt`
--
ALTER TABLE `inv_permintaan_dt`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `inv_permintaan_dt`
--
ALTER TABLE `inv_permintaan_dt`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
