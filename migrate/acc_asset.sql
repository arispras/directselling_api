-- phpMyAdmin SQL Dump
-- version 5.1.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Aug 08, 2022 at 02:44 PM
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
-- Table structure for table `acc_asset`
--

CREATE TABLE `acc_asset` (
  `id` int(11) NOT NULL,
  `lokasi_id` int(11) DEFAULT NULL,
  `kode` varchar(50) DEFAULT NULL,
  `nama` varchar(50) DEFAULT NULL,
  `asset_tipe_id` int(11) DEFAULT NULL,
  `tgl_beli` date DEFAULT NULL,
  `harga_beli` decimal(16,2) DEFAULT NULL,
  `tgl_mulai_pakai` date DEFAULT NULL,
  `nilai_asset` decimal(16,2) DEFAULT NULL,
  `nilai_residu` decimal(16,2) DEFAULT NULL,
  `posisi_asset_id` int(11) DEFAULT NULL,
  `status` varchar(30) DEFAULT NULL,
  `lama_bulan_penyusutan` int(11) DEFAULT NULL,
  `metode_penyusutan` varchar(30) DEFAULT NULL,
  `ket` varchar(50) DEFAULT NULL,
  `akun_penyusutan_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `acc_asset`
--
ALTER TABLE `acc_asset`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `acc_asset`
--
ALTER TABLE `acc_asset`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
