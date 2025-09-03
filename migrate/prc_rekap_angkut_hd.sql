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
-- Table structure for table `prc_rekap_angkut_hd`
--

CREATE TABLE `prc_rekap_angkut_hd` (
  `id` int(11) NOT NULL,
  `lokasi_id` int(11) NOT NULL,
  `no_rekap` varchar(50) NOT NULL,
  `tanggal` date NOT NULL,
  `supplier_id` int(11) NOT NULL,
  `spk_id` int(11) NOT NULL,
  `periode_kt_dari` date NOT NULL,
  `periode_kt_sd` date NOT NULL,
  `item_id` int(11) NOT NULL,
  `total_berat_terima` float NOT NULL,
  `adj_berat_terima` int(11) NOT NULL,
  `total_berat_tagihan` double NOT NULL,
  `harga_satuan` double NOT NULL,
  `total_tagihan` double NOT NULL,
  `sub_total` double DEFAULT NULL,
  `dibuat_oleh` int(11) DEFAULT NULL,
  `diubah_oleh` int(11) DEFAULT NULL,
  `dibuat_tanggal` datetime DEFAULT NULL,
  `diubah_tanggal` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `prc_rekap_angkut_hd`
--

INSERT INTO `prc_rekap_angkut_hd` (`id`, `lokasi_id`, `no_rekap`, `tanggal`, `supplier_id`, `spk_id`, `periode_kt_dari`, `periode_kt_sd`, `item_id`, `total_berat_terima`, `adj_berat_terima`, `total_berat_tagihan`, `harga_satuan`, `total_tagihan`, `sub_total`, `dibuat_oleh`, `diubah_oleh`, `dibuat_tanggal`, `diubah_tanggal`) VALUES
(7, 252, '0001/PRC/SBNE/0722', '2022-07-14', 6, 18, '2017-02-01', '2022-07-14', 5, 15620, 0, 10, 0, 0, 1562000, 40, 40, '2022-07-14 13:30:57', '2022-07-14 13:30:57'),
(8, 252, '0002/PRC/SBNE/0722', '2022-07-14', 6, 18, '2017-01-02', '2022-07-14', 5, 15620, 10, 10, 0, 0, 1562000, 40, 40, '2022-07-14 13:35:50', '2022-07-14 13:35:50');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `prc_rekap_angkut_hd`
--
ALTER TABLE `prc_rekap_angkut_hd`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `prc_rekap_angkut_hd`
--
ALTER TABLE `prc_rekap_angkut_hd`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
