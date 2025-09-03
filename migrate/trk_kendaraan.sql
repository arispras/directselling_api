-- phpMyAdmin SQL Dump
-- version 5.1.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Feb 24, 2022 at 05:28 PM
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
-- Table structure for table `trk_kendaraan`
--

CREATE TABLE `trk_kendaraan` (
  `id` int(11) NOT NULL,
  `traksi_id` int(11) NOT NULL,
  `kode` varchar(30) NOT NULL,
  `nama` varchar(60) NOT NULL,
  `jenis_id` int(11) NOT NULL,
  `tipe` varchar(10) DEFAULT NULL,
  `no_kendaraan` varchar(55) DEFAULT NULL,
  `no_mesin` varchar(55) DEFAULT NULL,
  `no_rangka` varchar(55) DEFAULT NULL,
  `tahun_perolehan` int(11) DEFAULT NULL,
  `berat_kosong` int(11) DEFAULT NULL,
  `dibuat_oleh` int(11) NOT NULL,
  `dibuat_tanggal` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `trk_kendaraan`
--

INSERT INTO `trk_kendaraan` (`id`, `traksi_id`, `kode`, `nama`, `jenis_id`, `tipe`, `no_kendaraan`, `no_mesin`, `no_rangka`, `tahun_perolehan`, `berat_kosong`, `dibuat_oleh`, `dibuat_tanggal`) VALUES
(1, 738, '001', 'HILUX  001', 0, 'KD', '', '', '', 0, 0, 0, '0000-00-00'),
(2, 738, '001', 'TRUK DT 002 ', 1, 'KD', '', '', '', 0, 0, 0, '0000-00-00'),
(3, 738, 'KD01', 'KD01', 1, NULL, '123', '123', '123', 2010, 4000, 0, '0000-00-00');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `trk_kendaraan`
--
ALTER TABLE `trk_kendaraan`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `trk_kendaraan`
--
ALTER TABLE `trk_kendaraan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
