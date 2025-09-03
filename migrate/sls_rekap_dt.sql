-- phpMyAdmin SQL Dump
-- version 5.1.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 05, 2022 at 02:02 PM
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
-- Table structure for table `sls_rekap_dt`
--

CREATE TABLE `sls_rekap_dt` (
  `id` int(11) NOT NULL,
  `rekap_id` int(11) NOT NULL,
  `sjpp_id` int(11) NOT NULL,
  `no_kartu_timbang` varchar(50) NOT NULL,
  `dt_tanggal` date NOT NULL,
  `bruto` int(11) NOT NULL,
  `tara` int(11) NOT NULL,
  `netto` int(11) NOT NULL,
  `berat_terima` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `sls_rekap_dt`
--

INSERT INTO `sls_rekap_dt` (`id`, `rekap_id`, `sjpp_id`, `no_kartu_timbang`, `dt_tanggal`, `bruto`, `tara`, `netto`, `berat_terima`) VALUES
(26, 16, 19, '', '0000-00-00', 0, 0, 0, 0),
(27, 16, 20, '', '0000-00-00', 0, 0, 0, 0);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `sls_rekap_dt`
--
ALTER TABLE `sls_rekap_dt`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `sls_rekap_dt`
--
ALTER TABLE `sls_rekap_dt`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
