-- phpMyAdmin SQL Dump
-- version 5.1.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 20, 2022 at 01:57 AM
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
-- Table structure for table `pks_sounding`
--

CREATE TABLE `pks_sounding` (
  `id` int(11) NOT NULL,
  `mill_id` int(11) NOT NULL,
  `tanki_id` int(11) NOT NULL,
  `no_transaksi` varchar(50) NOT NULL,
  `tanggal` date NOT NULL,
  `sounding` double NOT NULL,
  `meja_ukur` double NOT NULL,
  `tinggi` double NOT NULL,
  `hasil_1` double NOT NULL,
  `hasil_2` double NOT NULL,
  `hasil_total` double NOT NULL,
  `kg` double NOT NULL DEFAULT 0,
  `suhu` double NOT NULL,
  `cal` double NOT NULL,
  `diubah_oleh` int(11) NOT NULL,
  `diubah_tanggal` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `pks_sounding`
--
ALTER TABLE `pks_sounding`
  ADD PRIMARY KEY (`id`),
  ADD KEY `mill_id` (`mill_id`),
  ADD KEY `tanki_id` (`tanki_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `pks_sounding`
--
ALTER TABLE `pks_sounding`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `pks_sounding`
--
ALTER TABLE `pks_sounding`
  ADD CONSTRAINT `pks_sounding_ibfk_1` FOREIGN KEY (`mill_id`) REFERENCES `gbm_organisasi` (`id`),
  ADD CONSTRAINT `pks_sounding_ibfk_2` FOREIGN KEY (`tanki_id`) REFERENCES `pks_tanki` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
