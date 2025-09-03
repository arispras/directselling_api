-- phpMyAdmin SQL Dump
-- version 5.1.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 22, 2022 at 09:34 PM
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
-- Table structure for table `pks_pengolahan_dt`
--

CREATE TABLE `pks_pengolahan_dt` (
  `id` int(11) NOT NULL,
  `pengolahan_id` int(11) NOT NULL,
  `shift_id` int(11) NOT NULL,
  `jam_masuk` varchar(10) NOT NULL,
  `jam_selesai` varchar(10) NOT NULL,
  `mandor_id` int(11) NOT NULL,
  `asisten_id` int(11) NOT NULL,
  `jam_proses` double NOT NULL,
  `jumlah_rebusan` double NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `pks_pengolahan_dt`
--
ALTER TABLE `pks_pengolahan_dt`
  ADD PRIMARY KEY (`id`),
  ADD KEY `shift_id` (`shift_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `pks_pengolahan_dt`
--
ALTER TABLE `pks_pengolahan_dt`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
