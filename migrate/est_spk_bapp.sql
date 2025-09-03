-- phpMyAdmin SQL Dump
-- version 5.1.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 11, 2022 at 06:04 PM
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
-- Table structure for table `est_spk_bapp`
--

CREATE TABLE `est_spk_bapp` (
  `id` int(11) NOT NULL,
  `spk_dt_id` int(11) NOT NULL,
  `no_bapp` varchar(100) NOT NULL,
  `tanggal` date NOT NULL,
  `real_hk` double NOT NULL,
  `real_volume` double NOT NULL,
  `real_harga` double NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `est_spk_bapp`
--

INSERT INTO `est_spk_bapp` (`id`, `spk_dt_id`, `no_bapp`, `tanggal`, `real_hk`, `real_volume`, `real_harga`) VALUES
(1, 2, '001', '2022-04-11', 50, 50, 50);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `est_spk_bapp`
--
ALTER TABLE `est_spk_bapp`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `est_spk_bapp`
--
ALTER TABLE `est_spk_bapp`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
