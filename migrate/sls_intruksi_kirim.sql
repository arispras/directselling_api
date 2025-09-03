-- phpMyAdmin SQL Dump
-- version 5.1.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 18, 2022 at 03:43 AM
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
-- Table structure for table `sls_intruksi_kirim`
--

CREATE TABLE `sls_intruksi_kirim` (
  `id` int(11) NOT NULL,
  `sales_lokasi_id` int(11) NOT NULL,
  `kepada_lokasi_id` int(11) NOT NULL,
  `spk_id` int(11) NOT NULL,
  `no_transaksi` varchar(45) NOT NULL,
  `tanggal` date NOT NULL,
  `alamat_pengiriman` varchar(45) NOT NULL,
  `keterangan` varchar(50) NOT NULL,
  `pic` varchar(50) NOT NULL,
  `periode_kirim_awal` varchar(45) NOT NULL,
  `periode_kirim_akhir` varchar(45) NOT NULL,
  `produk_id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `jumlah` int(11) NOT NULL,
  `diubah_oleh` int(11) NOT NULL,
  `diubah_tanggal` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `sls_intruksi_kirim`
--

INSERT INTO `sls_intruksi_kirim` (`id`, `sales_lokasi_id`, `kepada_lokasi_id`, `spk_id`, `no_transaksi`, `tanggal`, `alamat_pengiriman`, `keterangan`, `pic`, `periode_kirim_awal`, `periode_kirim_akhir`, `produk_id`, `customer_id`, `jumlah`, `diubah_oleh`, `diubah_tanggal`) VALUES
(13, 263, 260, 3, 'qw', '2022-01-15', 'qw', 'qw', 'qw', '2022-01-15', '2022-01-15', 6, 0, 0, 37, '2022-01-15'),
(14, 263, 260, 3, '001', '2022-01-15', 'wq', 'qw', 'qw', '2022-01-15', '2022-01-15', 6, 0, 1000, 37, '2022-01-15'),
(16, 263, 260, 4, 'ik-002', '2022-01-16', 'alamat 1', 'ket', 'aris', '2022-01-16', '2022-01-16', 6, 0, 1000, 37, '2022-01-16'),
(17, 263, 260, 3, '001', '2022-01-18', 'sample', 'sample', 'sample', '2022-01-18', '2022-01-18', 0, 3, 50, 40, '2022-01-18');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `sls_intruksi_kirim`
--
ALTER TABLE `sls_intruksi_kirim`
  ADD PRIMARY KEY (`id`),
  ADD KEY `spk_id` (`spk_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `sls_intruksi_kirim`
--
ALTER TABLE `sls_intruksi_kirim`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `sls_intruksi_kirim`
--
ALTER TABLE `sls_intruksi_kirim`
  ADD CONSTRAINT `sls_intruksi_kirim_ibfk_1` FOREIGN KEY (`spk_id`) REFERENCES `sls_kontrak` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
