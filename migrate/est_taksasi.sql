-- phpMyAdmin SQL Dump
-- version 5.1.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 11, 2022 at 09:44 AM
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
-- Table structure for table `est_taksasi`
--

CREATE TABLE `est_taksasi` (
  `id` int(11) NOT NULL,
  `lokasi_id` int(11) NOT NULL,
  `afdeling_id` int(11) NOT NULL,
  `blok_id` int(11) NOT NULL,
  `tanggal` date NOT NULL,
  `ha_sisa` int(11) NOT NULL,
  `ha_besok` int(11) NOT NULL,
  `jumlah_pokok` int(11) NOT NULL,
  `persen_buah_matang` int(11) NOT NULL,
  `jjg_output` int(11) NOT NULL,
  `hk` int(11) NOT NULL,
  `bjr` int(11) NOT NULL,
  `berat_kg` int(11) NOT NULL,
  `seksi_panen` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `est_taksasi`
--

INSERT INTO `est_taksasi` (`id`, `lokasi_id`, `afdeling_id`, `blok_id`, `tanggal`, `ha_sisa`, `ha_besok`, `jumlah_pokok`, `persen_buah_matang`, `jjg_output`, `hk`, `bjr`, `berat_kg`, `seksi_panen`) VALUES
(2, 252, 271, 703, '2022-03-11', 1, 2, 3, 4, 5, 6, 7, 8, 9);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `est_taksasi`
--
ALTER TABLE `est_taksasi`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `est_taksasi`
--
ALTER TABLE `est_taksasi`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
