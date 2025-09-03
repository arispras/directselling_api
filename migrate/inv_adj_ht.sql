-- phpMyAdmin SQL Dump
-- version 5.1.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 28, 2022 at 05:40 PM
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
-- Table structure for table `inv_adj_ht`
--

CREATE TABLE `inv_adj_ht` (
  `id` int(11) NOT NULL,
  `lokasi_id` int(11) NOT NULL,
  `gudang_id` int(11) NOT NULL,
  `no_transaksi` varchar(50) NOT NULL,
  `tanggal` date NOT NULL,
  `no_ref` varchar(200) NOT NULL,
  `catatan` text NOT NULL,
  `dibuat_oleh` int(11) NOT NULL,
  `dibuat_tanggal` datetime NOT NULL,
  `is_posting` tinyint(1) NOT NULL DEFAULT 0,
  `diposting_oleh` int(11) NOT NULL,
  `diposting_tanggal` datetime NOT NULL,
  `diubah_oleh` int(11) DEFAULT NULL,
  `diubah_tanggal` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `inv_adj_ht`
--

INSERT INTO `inv_adj_ht` (`id`, `lokasi_id`, `gudang_id`, `no_transaksi`, `tanggal`, `no_ref`, `catatan`, `dibuat_oleh`, `dibuat_tanggal`, `is_posting`, `diposting_oleh`, `diposting_tanggal`, `diubah_oleh`, `diubah_tanggal`) VALUES
(1, 252, 740, '0001/ADJ/SBNE/0722', '2022-07-28', 'asdasda', 'x', 40, '2022-07-28 14:54:11', 0, 0, '0000-00-00 00:00:00', 40, '2022-07-28 14:55:31');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `inv_adj_ht`
--
ALTER TABLE `inv_adj_ht`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `inv_adj_ht`
--
ALTER TABLE `inv_adj_ht`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
