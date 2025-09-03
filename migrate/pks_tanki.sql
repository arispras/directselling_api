-- phpMyAdmin SQL Dump
-- version 5.1.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 21, 2022 at 02:19 AM
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
-- Table structure for table `pks_tanki`
--

CREATE TABLE `pks_tanki` (
  `id` int(11) NOT NULL,
  `mill_id` int(11) NOT NULL,
  `produk_id` int(11) NOT NULL,
  `kode_tanki` varchar(50) NOT NULL,
  `nama_tanki` varchar(50) NOT NULL,
  `meja_ukur` int(11) NOT NULL,
  `tinggi_meja_ukur` int(11) NOT NULL,
  `cal` int(11) NOT NULL,
  `suhu` int(11) NOT NULL,
  `tinggi_dari` float NOT NULL,
  `tinggi_sd` float NOT NULL,
  `kapasitas` int(11) DEFAULT NULL,
  `volume` int(11) NOT NULL,
  `diubah_oleh` int(11) NOT NULL,
  `diubah_tanggal` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `pks_tanki`
--

INSERT INTO `pks_tanki` (`id`, `mill_id`, `produk_id`, `kode_tanki`, `nama_tanki`, `meja_ukur`, `tinggi_meja_ukur`, `cal`, `suhu`, `tinggi_dari`, `tinggi_sd`, `kapasitas`, `volume`, `diubah_oleh`, `diubah_tanggal`) VALUES
(15, 256, 6, '001', 'Tangki I', 370, 3, 0, 0, 0, 0, NULL, 0, 0, '0000-00-00 00:00:00'),
(16, 256, 6, '002', 'Tangki 2', 1, 10, 0, 0, 0, 0, NULL, 0, 0, '0000-00-00 00:00:00'),
(18, 260, 6, 'T001', 'TANGKI (500)', 370, 200, 0, 0, 0, 0, 500, 0, 0, '0000-00-00 00:00:00'),
(19, 260, 6, 'T002', 'TANGKI (2000)', 296, 3, 0, 0, 0, 0, 2000, 0, 0, '0000-00-00 00:00:00'),
(22, 260, 6, 'T003', 'TANGKI (2000)', 307, 0, 0, 0, 0, 0, 2000, 0, 0, '0000-00-00 00:00:00');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `pks_tanki`
--
ALTER TABLE `pks_tanki`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `pks_tanki`
--
ALTER TABLE `pks_tanki`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
