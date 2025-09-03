-- phpMyAdmin SQL Dump
-- version 5.1.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 06, 2022 at 11:04 AM
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
-- Table structure for table `est_spk_dt`
--

CREATE TABLE `est_spk_dt` (
  `id` int(11) NOT NULL,
  `est_spk_id` int(11) NOT NULL,
  `blok_id` int(11) NOT NULL,
  `kegiatan_id` int(11) NOT NULL,
  `hk` double NOT NULL,
  `volume` double NOT NULL,
  `total` double NOT NULL,
  `harga` double NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `est_spk_dt`
--

INSERT INTO `est_spk_dt` (`id`, `est_spk_id`, `blok_id`, `kegiatan_id`, `hk`, `volume`, `total`, `harga`) VALUES
(2, 6, 702, 2, 10, 10, 10, 10);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `est_spk_dt`
--
ALTER TABLE `est_spk_dt`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `est_spk_dt`
--
ALTER TABLE `est_spk_dt`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
