-- phpMyAdmin SQL Dump
-- version 5.1.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 02, 2021 at 01:31 AM
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
-- Table structure for table `sls_kontrak`
--

CREATE TABLE `sls_kontrak` (
  `id` int(11) NOT NULL,
  `no_spk` varchar(45) NOT NULL,
  `no_ref` varchar(45) NOT NULL,
  `tanggal` date NOT NULL,
  `mill_id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `alamat_pengiriman` varchar(50) NOT NULL,
  `alamat_penagihan` varchar(50) NOT NULL,
  `pic` varchar(45) NOT NULL,
  `produk_id` int(11) NOT NULL,
  `jumlah` int(11) NOT NULL,
  `harga_satuan` int(11) NOT NULL,
  `sub_total` int(11) NOT NULL,
  `ppn` int(11) NOT NULL,
  `pph` int(11) NOT NULL,
  `total` int(11) NOT NULL,
  `periode_kirim_awal` varchar(50) NOT NULL,
  `periode_kirim_akhir` varchar(50) NOT NULL,
  `ffa` varchar(45) NOT NULL,
  `mi` varchar(45) NOT NULL,
  `impurities` varchar(45) NOT NULL,
  `dobi` varchar(45) NOT NULL,
  `moisture` varchar(45) NOT NULL,
  `grading` varchar(20) NOT NULL,
  `toleransi` varchar(50) NOT NULL,
  `keterangan` varchar(50) NOT NULL,
  `diubah_oleh` int(11) NOT NULL,
  `diubah_tanggal` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `sls_kontrak`
--

INSERT INTO `sls_kontrak` (`id`, `no_spk`, `no_ref`, `tanggal`, `mill_id`, `customer_id`, `alamat_pengiriman`, `alamat_penagihan`, `pic`, `produk_id`, `jumlah`, `harga_satuan`, `sub_total`, `ppn`, `pph`, `total`, `periode_kirim_awal`, `periode_kirim_akhir`, `ffa`, `mi`, `impurities`, `dobi`, `moisture`, `grading`, `toleransi`, `keterangan`, `diubah_oleh`, `diubah_tanggal`) VALUES
(1, 'SPK001', 'REF001', '2021-12-01', 256, 4, 'Jatiasih', 'Jakarta', 'PICC', 6, 10, 1000, 10000, 2, 3, 9902, 'Senin', 'Kamis', 'FFA', 'MII', 'Impurities ', 'Dobiii', 'Moisture', 'A', 'Toleransi', 'Ket', 40, '2021-12-01 17:58:48'),
(2, '0', 'as', '2021-12-02', 256, 4, 'asd', 'asd', 'asdads', 6, 1, 1, 1, 1, 1, 1, 'asd', 'asd', 'sad', 'asd', 'asd', 'sad', 'asd', 'asd', 'asd', 'asd', 40, '2021-12-01 22:57:56');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `sls_kontrak`
--
ALTER TABLE `sls_kontrak`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `sls_kontrak`
--
ALTER TABLE `sls_kontrak`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
