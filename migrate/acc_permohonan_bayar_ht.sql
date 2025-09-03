-- phpMyAdmin SQL Dump
-- version 5.1.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 20, 2022 at 07:39 AM
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
-- Table structure for table `acc_permohonan_bayar_ht`
--

CREATE TABLE `acc_permohonan_bayar_ht` (
  `id` int(11) NOT NULL,
  `no_transaksi` varchar(100) NOT NULL,
  `tanggal` date NOT NULL,
  `supplier_id` varchar(50) DEFAULT '',
  `no_referensi` varchar(100) DEFAULT NULL,
  `diminta_oleh` varchar(150) DEFAULT NULL,
  `divisi` varchar(150) DEFAULT NULL,
  `periode` varchar(150) DEFAULT NULL,
  `ket` varchar(200) DEFAULT NULL,
  `nama_bank` varchar(50) DEFAULT NULL,
  `no_rek` varchar(100) DEFAULT NULL,
  `atas_nama` varchar(150) DEFAULT NULL,
  `subtotal` double NOT NULL,
  `diskon` double NOT NULL,
  `dpp` double NOT NULL,
  `ppn` double NOT NULL,
  `pph` double NOT NULL,
  `total` double NOT NULL,
  `dibuat_oleh` int(11) DEFAULT NULL,
  `dibuat_tanggal` date DEFAULT NULL,
  `diubah_oleh` int(11) DEFAULT NULL,
  `diubah_tanggal` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `acc_permohonan_bayar_ht`
--

INSERT INTO `acc_permohonan_bayar_ht` (`id`, `no_transaksi`, `tanggal`, `supplier_id`, `no_referensi`, `diminta_oleh`, `divisi`, `periode`, `ket`, `nama_bank`, `no_rek`, `atas_nama`, `subtotal`, `diskon`, `dpp`, `ppn`, `pph`, `total`, `dibuat_oleh`, `dibuat_tanggal`, `diubah_oleh`, `diubah_tanggal`) VALUES
(4, '160/PP/DPA-P/1222', '2022-12-20', 'SYAHID SYAIFUL ANWAR', '0359/PO-S0049/SBNE/0822', 'D\'Elaisa Agriputri P.', 'SBNE', 'Desember 2022', 'Pembelian Liugong Motor Greader', 'BRI', '0563-0104-75660-258', 'SYAHID SYAIFUL ANWAR', 400000, 50000, 350000, 2, 2, 350000, 40, '2022-12-20', NULL, '2022-12-20');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `acc_permohonan_bayar_ht`
--
ALTER TABLE `acc_permohonan_bayar_ht`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `acc_permohonan_bayar_ht`
--
ALTER TABLE `acc_permohonan_bayar_ht`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
