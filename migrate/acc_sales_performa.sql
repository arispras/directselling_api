-- phpMyAdmin SQL Dump
-- version 5.1.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 28, 2022 at 12:17 PM
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
-- Table structure for table `acc_sales_performa`
--

CREATE TABLE `acc_sales_performa` (
  `id` int(11) NOT NULL,
  `lokasi_id` int(11) NOT NULL,
  `no_performa` varchar(50) NOT NULL,
  `tanggal` date NOT NULL,
  `tanggal_tempo` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `jenis_performa` varchar(20) NOT NULL,
  `sls_kontrak_id` int(11) NOT NULL,
  `no_rekap` varchar(50) NOT NULL,
  `tanggal_rk` datetime DEFAULT NULL,
  `nama_produk` varchar(50) NOT NULL,
  `deskripsi` varchar(200) NOT NULL,
  `harga_satuan` int(11) NOT NULL,
  `qty` int(11) NOT NULL,
  `jumlah` int(11) NOT NULL,
  `uang_muka` int(11) NOT NULL,
  `diskon` decimal(11,0) NOT NULL,
  `ppn` int(11) NOT NULL,
  `grand_total` int(11) NOT NULL,
  `is_posting` tinyint(1) NOT NULL DEFAULT 0,
  `diposting_oleh` int(11) NOT NULL,
  `diposting_tanggal` datetime NOT NULL,
  `acc_akun_id_debet` int(11) NOT NULL,
  `acc_akun_id_kredit` int(11) NOT NULL,
  `dibuat_oleh` int(11) NOT NULL,
  `diubah_oleh` int(11) NOT NULL,
  `diubah_tanggal` datetime DEFAULT NULL,
  `dibuat_tanggal` datetime DEFAULT NULL,
  `nilai_dibayar` double DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `acc_sales_performa`
--

INSERT INTO `acc_sales_performa` (`id`, `lokasi_id`, `no_performa`, `tanggal`, `tanggal_tempo`, `customer_id`, `jenis_performa`, `sls_kontrak_id`, `no_rekap`, `tanggal_rk`, `nama_produk`, `deskripsi`, `harga_satuan`, `qty`, `jumlah`, `uang_muka`, `diskon`, `ppn`, `grand_total`, `is_posting`, `diposting_oleh`, `diposting_tanggal`, `acc_akun_id_debet`, `acc_akun_id_kredit`, `dibuat_oleh`, `diubah_oleh`, `diubah_tanggal`, `dibuat_tanggal`, `nilai_dibayar`) VALUES
(1, 252, '0182031', '2022-04-28', 2022, 10, 'PENGGANTIAN', 9, '', NULL, '', 'sample', 20000, 5000, 100000000, 0, '0', 2, 102000000, 0, 0, '0000-00-00 00:00:00', 0, 0, 0, 0, NULL, '2022-04-28 12:17:25', 0);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `acc_sales_performa`
--
ALTER TABLE `acc_sales_performa`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `acc_sales_performa`
--
ALTER TABLE `acc_sales_performa`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
