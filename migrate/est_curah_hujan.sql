-- phpMyAdmin SQL Dump
-- version 5.1.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Feb 25, 2022 at 07:53 PM
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
-- Table structure for table `est_curah_hujan`
--

CREATE TABLE `est_curah_hujan` (
  `id` int(11) NOT NULL,
  `tanggal` date NOT NULL,
  `lokasi_id` int(11) NOT NULL,
  `afdeling_id` int(11) NOT NULL,
  `pagi` int(11) DEFAULT NULL,
  `sore` int(11) DEFAULT NULL,
  `malam` int(11) DEFAULT NULL,
  `dibuat_oleh` int(11) DEFAULT NULL,
  `dibuat_tanggal` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `est_curah_hujan`
--

INSERT INTO `est_curah_hujan` (`id`, `tanggal`, `lokasi_id`, `afdeling_id`, `pagi`, `sore`, `malam`, `dibuat_oleh`, `dibuat_tanggal`) VALUES
(1, '2022-02-24', 252, 253, 12, 12, 120, NULL, NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `est_curah_hujan`
--
ALTER TABLE `est_curah_hujan`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `est_curah_hujan`
--
ALTER TABLE `est_curah_hujan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
