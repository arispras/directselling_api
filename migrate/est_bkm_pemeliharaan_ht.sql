-- phpMyAdmin SQL Dump
-- version 5.1.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 10, 2022 at 12:09 PM
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
-- Table structure for table `est_bkm_pemeliharaan_ht`
--

CREATE TABLE `est_bkm_pemeliharaan_ht` (
  `id` int(11) NOT NULL,
  `lokasi_id` int(11) NOT NULL,
  `rayon_afdeling_id` int(11) NOT NULL,
  `no_transaksi` varchar(50) NOT NULL,
  `tanggal` date NOT NULL,
  `no_ref` varchar(50) NOT NULL,
  `mandor_id` int(11) DEFAULT NULL,
  `kerani_id` int(11) NOT NULL,
  `asisten_id` int(11) NOT NULL,
  `mandor1_id` int(11) DEFAULT NULL,
  `tipe` varchar(10) DEFAULT NULL,
  `dibuat_oleh` int(11) DEFAULT NULL,
  `diubah_oleh` int(11) DEFAULT NULL,
  `dibuat_tanggal` date DEFAULT NULL,
  `diubah_tanggal` date DEFAULT NULL,
  `is_posting` tinyint(1) DEFAULT 0,
  `diposting_oleh` int(11) NOT NULL,
  `diposting_tanggal` datetime NOT NULL,
  `mandor_hasil_kerja` double NOT NULL,
  `mandor_jumlah_hk` double NOT NULL,
  `mandor_rupiah_hk` double NOT NULL,
  `mandor_premi` double NOT NULL,
  `kerani_hasil_kerja` double NOT NULL,
  `kerani_jumlah_hk` double NOT NULL,
  `kerani_rupiah_hk` double NOT NULL,
  `kerani_premi` double NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `est_bkm_pemeliharaan_ht`
--

INSERT INTO `est_bkm_pemeliharaan_ht` (`id`, `lokasi_id`, `rayon_afdeling_id`, `no_transaksi`, `tanggal`, `no_ref`, `mandor_id`, `kerani_id`, `asisten_id`, `mandor1_id`, `tipe`, `dibuat_oleh`, `diubah_oleh`, `dibuat_tanggal`, `diubah_tanggal`, `is_posting`, `diposting_oleh`, `diposting_tanggal`, `mandor_hasil_kerja`, `mandor_jumlah_hk`, `mandor_rupiah_hk`, `mandor_premi`, `kerani_hasil_kerja`, `kerani_jumlah_hk`, `kerani_rupiah_hk`, `kerani_premi`) VALUES
(2, 252, 271, '1', '2022-02-16', '', 188, 188, 188, NULL, NULL, NULL, NULL, NULL, NULL, 1, 1, '2022-03-09 19:36:59', 0, 0, 0, 0, 0, 0, 0, 0),
(3, 265, 269, '2', '2022-02-17', '', 187, 189, 189, NULL, NULL, NULL, NULL, NULL, NULL, 1, 1, '2022-03-10 10:08:59', 0, 0, 0, 0, 0, 0, 0, 0),
(4, 252, 267, '001', '2022-02-20', '', 187, 187, 187, NULL, NULL, NULL, NULL, NULL, NULL, 1, 1, '2022-04-06 14:48:31', 0, 0, 0, 0, 0, 0, 0, 0),
(5, 252, 253, '11', '2022-02-21', '', 188, 187, 188, NULL, NULL, NULL, NULL, NULL, NULL, 1, 1, '2022-04-06 14:54:20', 0, 0, 0, 0, 0, 0, 0, 0),
(11, 252, 270, '0003/EST/SBNE/0522', '2022-05-26', '', 297, 298, 297, NULL, NULL, 40, NULL, '2022-05-26', NULL, 0, 0, '0000-00-00 00:00:00', 0, 0, 0, 0, 0, 0, 0, 0),
(12, 252, 270, '0003/EST/SBNE/0522', '2022-05-26', '', 297, 298, 297, NULL, NULL, 40, NULL, '2022-05-26', NULL, 0, 0, '0000-00-00 00:00:00', 0, 0, 0, 0, 0, 0, 0, 0),
(14, 252, 269, '0003/EST/SBNE/0522', '2022-05-26', '', 297, 297, 298, NULL, NULL, 40, 40, '2022-05-26', '2022-05-26', 0, 0, '0000-00-00 00:00:00', 0, 0, 0, 0, 0, 0, 0, 0),
(15, 252, 253, '0003/PRT/SBNE/0622', '2022-06-10', '', 295, 295, 295, NULL, NULL, 54, 54, '2022-06-10', '2022-06-10', 0, 0, '0000-00-00 00:00:00', 100, 100, 0, 100, 100, 100, 0, 100);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `est_bkm_pemeliharaan_ht`
--
ALTER TABLE `est_bkm_pemeliharaan_ht`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `est_bkm_pemeliharaan_ht`
--
ALTER TABLE `est_bkm_pemeliharaan_ht`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
