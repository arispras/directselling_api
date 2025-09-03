-- phpMyAdmin SQL Dump
-- version 5.1.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 01, 2022 at 10:17 AM
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
-- Table structure for table `est_bapp_spk_kendaraan_dt`
--

CREATE TABLE `est_bapp_spk_kendaraan_dt` (
  `id` int(11) NOT NULL,
  `est_bapp_spk_kendaraan_id` int(11) NOT NULL,
  `tanggal_operasi` date NOT NULL,
  `blok_id` int(11) NOT NULL,
  `kegiatan_id` int(11) NOT NULL,
  `hm_km_awal` double NOT NULL,
  `hm_km_akhir` double NOT NULL,
  `jml_hm_km` double NOT NULL,
  `harga_satuan` double NOT NULL,
  `jumlah` double NOT NULL,
  `keterangan` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `est_bapp_spk_kendaraan_dt`
--
ALTER TABLE `est_bapp_spk_kendaraan_dt`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `est_bapp_spk_kendaraan_dt`
--
ALTER TABLE `est_bapp_spk_kendaraan_dt`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
