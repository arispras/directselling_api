-- phpMyAdmin SQL Dump
-- version 5.1.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 20, 2021 at 03:26 PM
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
-- Table structure for table `sls_invoice`
--

CREATE TABLE `sls_invoice` (
  `id` int(11) NOT NULL,
  `no_invoice` varchar(50) NOT NULL,
  `tanggal` date NOT NULL,
  `no_rekap` varchar(50) NOT NULL,
  `customer` varchar(50) NOT NULL,
  `tanggal_rk` date NOT NULL,
  `total_berat_terima` int(11) NOT NULL,
  `nama_produk` varchar(50) NOT NULL,
  `harga_satuan` int(11) NOT NULL,
  `jumlah` int(11) NOT NULL,
  `sub_total` int(11) NOT NULL,
  `disc` int(11) NOT NULL,
  `uang_muka` int(11) NOT NULL,
  `dpp` int(11) NOT NULL,
  `ppn` int(11) NOT NULL,
  `grand_total` int(11) NOT NULL,
  `diubah_oleh` int(11) NOT NULL,
  `diubah_tanggal` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `sls_invoice`
--

INSERT INTO `sls_invoice` (`id`, `no_invoice`, `tanggal`, `no_rekap`, `customer`, `tanggal_rk`, `total_berat_terima`, `nama_produk`, `harga_satuan`, `jumlah`, `sub_total`, `disc`, `uang_muka`, `dpp`, `ppn`, `grand_total`, `diubah_oleh`, `diubah_tanggal`) VALUES
(1, 'INV01', '2021-12-18', 'NRKP', 'sample', '2021-12-18', 0, '', 1000, 10, 10000, 10, 5000, 10, 100, 100000, 0, '2021-12-20'),
(2, 'NoNIN', '2021-12-20', '', 'Cust', '2021-12-20', 1, '', 1, 1, 1, 1, 11, 1, 1, 1, 0, '2021-12-20'),
(3, 'qweq', '2021-12-20', 'NRKP', 'sample', '2021-12-18', 0, '', 1000, 10, 10000, 1, 1, 11, 1, 1, 0, '2021-12-20');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `sls_invoice`
--
ALTER TABLE `sls_invoice`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `sls_invoice`
--
ALTER TABLE `sls_invoice`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
