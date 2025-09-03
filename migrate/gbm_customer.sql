-- phpMyAdmin SQL Dump
-- version 5.1.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 11, 2022 at 10:28 AM
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
-- Table structure for table `gbm_customer`
--

CREATE TABLE `gbm_customer` (
  `id` int(11) NOT NULL,
  `kode_customer` varchar(30) NOT NULL,
  `nama_customer` varchar(50) NOT NULL,
  `kelompok_id` int(11) NOT NULL,
  `tipe_customer` varchar(2) NOT NULL,
  `alamat` text NOT NULL,
  `no_telpon` varchar(20) NOT NULL,
  `contact_person` varchar(40) NOT NULL,
  `no_hp` varchar(40) NOT NULL,
  `diubah_oleh` int(11) NOT NULL,
  `diubah_tanggal` datetime NOT NULL DEFAULT current_timestamp(),
  `acc_akun_id` int(11) DEFAULT NULL,
  `tipe_pajak` varchar(20) NOT NULL,
  `no_npwp` varchar(200) NOT NULL,
  `alamat_npwp` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `gbm_customer`
--

INSERT INTO `gbm_customer` (`id`, `kode_customer`, `nama_customer`, `kelompok_id`, `tipe_customer`, `alamat`, `no_telpon`, `contact_person`, `no_hp`, `diubah_oleh`, `diubah_tanggal`, `acc_akun_id`, `tipe_pajak`, `no_npwp`, `alamat_npwp`) VALUES
(3, 'C001', 'PT. ENERGI UNGGUL PERSADA', 0, '', 'BONTANG', '0', '0', '0', 40, '2022-01-21 06:29:01', 1566, '0', '', ''),
(8, 'C002', 'PT. INTI MAS SEJAHTERA', 0, '', '.', '0', '0', '0', 40, '2022-01-21 06:29:26', 1566, '0', '', ''),
(9, 'C003', 'PT. KUTAI REFINERY NUSANTARA', 0, '', 'BALIKPAPAN', '0', '0', '0', 40, '2022-01-21 06:29:58', 1566, '0', '', ''),
(10, 'C004', 'PT. TRITUNGGAL SENTRA BUANA', 0, '', 'BONTANG', '0', '0', '0', 40, '2022-01-21 06:30:21', 1566, '0', '', ''),
(11, 'C005', 'PT. ALAM PRIMA CITRA SETIA', 0, '', '.', '0', '0', '0', 40, '2022-01-21 06:30:48', 1566, '0', '', ''),
(13, 'C006', 'AR RASYID 143', 0, '', '-', '-', '-', '-', 61, '2022-01-29 01:59:55', 1566, '0', '', ''),
(14, 'C007', 'RAYON 1', 0, '', '-', '-', '-', '-', 61, '2022-01-29 02:11:32', 1566, '0', '', ''),
(15, 'C008', 'RAYON 2', 0, '', '-', '-', '-', '-', 61, '2022-01-29 02:11:52', 1566, '0', '', ''),
(16, 'C009', 'NR ROHMAN', 0, '', '-', '-', '-', '-', 61, '2022-01-29 02:12:25', 1566, '0', '', '');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `gbm_customer`
--
ALTER TABLE `gbm_customer`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `gbm_customer`
--
ALTER TABLE `gbm_customer`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
