-- phpMyAdmin SQL Dump
-- version 5.1.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 30, 2022 at 01:55 PM
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
-- Table structure for table `prc_po_dt`
--

CREATE TABLE `prc_po_dt` (
  `id` int(11) NOT NULL,
  `po_hd_id` int(11) NOT NULL,
  `pp_dt_id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `qty` double NOT NULL,
  `harga` double NOT NULL,
  `diskon` double NOT NULL,
  `total` double NOT NULL,
  `ket` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `prc_po_dt`
--

INSERT INTO `prc_po_dt` (`id`, `po_hd_id`, `pp_dt_id`, `item_id`, `qty`, `harga`, `diskon`, `total`, `ket`) VALUES
(4, 3, 0, 10, 10, 100, 0, 0, ''),
(7, 6, 0, 6, 7, 1000, 0, 7000, 'ee'),
(8, 6, 0, 14, 12, 2000, 0, 22000, 'ee'),
(11, 7, 0, 13, 4, 200, 0, 800, 'xx'),
(12, 7, 0, 13, 2, 100, 0, 200, 'xx'),
(15, 5, 0, 14, 3, 2000, 0, 6000, 'x'),
(16, 5, 0, 5, 11, 3000, 0, 33000, 'xxx'),
(17, 6, 22, 13, 4, 100, 0, 400, 's'),
(18, 6, 23, 13, 2, 3000, 0, 6000, 's'),
(19, 7, 22, 13, 4, 1000, 0, 4000, ''),
(20, 7, 21, 14, 3, 10000, 0, 30000, ''),
(24, 9, 0, 13, 4, 200, 0, 800, ''),
(25, 9, 0, 14, 3, 300, 0, 900, ''),
(26, 8, 0, 11, 3, 1000, 0, 3000, 'z'),
(27, 10, 21, 14, 3, 10000, 0, 30000, 'aaa'),
(28, 11, 20, 11, 7, 1321, 21323, 9247, '-');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `prc_po_dt`
--
ALTER TABLE `prc_po_dt`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `prc_po_dt`
--
ALTER TABLE `prc_po_dt`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
