-- phpMyAdmin SQL Dump
-- version 5.1.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 13, 2022 at 09:14 PM
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
-- Table structure for table `inv_penerimaan_po_ht`
--

CREATE TABLE `inv_penerimaan_po_ht` (
  `id` int(11) NOT NULL,
  `lokasi_id` int(11) NOT NULL,
  `gudang_id` int(11) NOT NULL,
  `supplier_id` int(11) NOT NULL,
  `po_id` int(11) NOT NULL,
  `no_transaksi` varchar(50) NOT NULL DEFAULT '-',
  `no_surat_jalan_supplier` varchar(200) NOT NULL,
  `tanggal` date NOT NULL,
  `catatan` text NOT NULL,
  `dibuat_oleh` int(11) NOT NULL,
  `dibuat_tanggal` date NOT NULL,
  `is_posting` tinyint(1) NOT NULL DEFAULT 0,
  `diposting_oleh` int(11) NOT NULL,
  `diposting_tanggal` datetime NOT NULL,
  `upload_file` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `inv_penerimaan_po_ht`
--

INSERT INTO `inv_penerimaan_po_ht` (`id`, `lokasi_id`, `gudang_id`, `supplier_id`, `po_id`, `no_transaksi`, `no_surat_jalan_supplier`, `tanggal`, `catatan`, `dibuat_oleh`, `dibuat_tanggal`, `is_posting`, `diposting_oleh`, `diposting_tanggal`, `upload_file`) VALUES
(3, 263, 737, 0, 5, '-', '', '2022-03-02', '', 0, '0000-00-00', 1, 1, '2022-03-09 23:55:41', ''),
(4, 263, 737, 0, 8, '-', '', '2022-03-03', '', 0, '0000-00-00', 0, 1, '2022-03-09 09:57:46', ''),
(5, 263, 737, 0, 8, '-', '', '2022-03-09', 'asas', 0, '0000-00-00', 0, 0, '0000-00-00 00:00:00', ''),
(7, 0, 0, 0, 0, '', '', '0000-00-00', '', 0, '0000-00-00', 0, 0, '0000-00-00 00:00:00', ''),
(8, 0, 0, 0, 0, '', '', '0000-00-00', '', 0, '0000-00-00', 0, 0, '0000-00-00 00:00:00', 'invpenerimaanpo_1649877032.jpg'),
(9, 252, 740, 4, 38, '923', '323', '2022-04-13', 'sample', 0, '0000-00-00', 0, 0, '0000-00-00 00:00:00', 'invpenerimaanpo_1649877264.jpg');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `inv_penerimaan_po_ht`
--
ALTER TABLE `inv_penerimaan_po_ht`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `inv_penerimaan_po_ht`
--
ALTER TABLE `inv_penerimaan_po_ht`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
