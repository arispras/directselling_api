-- phpMyAdmin SQL Dump
-- version 5.1.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Feb 10, 2022 at 07:00 PM
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
-- Table structure for table `est_premi_basis_panen`
--

CREATE TABLE `est_premi_basis_panen` (
  `id` int(11) NOT NULL,
  `blok_id` int(11) NOT NULL,
  `tanggal_efektif` date DEFAULT NULL,
  `bjr_dari` double DEFAULT NULL,
  `bjr_sd` double DEFAULT NULL,
  `basis_jjg` double DEFAULT NULL,
  `lebih_basis1` double DEFAULT NULL,
  `premi_lebih_basis1` double DEFAULT NULL,
  `lebih_basis2` double DEFAULT NULL,
  `premi_lebih_basis2` double DEFAULT NULL,
  `lebih_basis3` double DEFAULT NULL,
  `premi_lebih_basis3` double DEFAULT NULL,
  `premi_brondolan` double DEFAULT NULL,
  `hk_luas_panen` double DEFAULT NULL,
  `dibuat_oleh` int(11) DEFAULT NULL,
  `dibuat_tanggal` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `est_premi_basis_panen`
--

INSERT INTO `est_premi_basis_panen` (`id`, `blok_id`, `tanggal_efektif`, `bjr_dari`, `bjr_sd`, `basis_jjg`, `lebih_basis1`, `premi_lebih_basis1`, `lebih_basis2`, `premi_lebih_basis2`, `lebih_basis3`, `premi_lebih_basis3`, `premi_brondolan`, `hk_luas_panen`, `dibuat_oleh`, `dibuat_tanggal`) VALUES
(1, 417, '2022-02-09', 1, 1, 1, 1, 1, 2, 2, 3, 3, 2, 2, NULL, NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `est_premi_basis_panen`
--
ALTER TABLE `est_premi_basis_panen`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `est_premi_basis_panen`
--
ALTER TABLE `est_premi_basis_panen`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
