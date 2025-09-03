-- phpMyAdmin SQL Dump
-- version 5.1.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 03, 2022 at 11:44 AM
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
-- Table structure for table `prc_po_ht`
--

CREATE TABLE `prc_po_ht` (
  `id` int(11) NOT NULL,
  `lokasi_id` int(11) NOT NULL,
  `supplier_id` int(11) NOT NULL,
  `no_po` varchar(150) NOT NULL,
  `tanggal` date NOT NULL,
  `syarat_bayar_id` int(11) NOT NULL,
  `franco_id` int(11) NOT NULL,
  `catatan` text NOT NULL,
  `sub_total` double NOT NULL,
  `disc` double NOT NULL,
  `ppn` double NOT NULL,
  `pph` double NOT NULL,
  `grand_total` double NOT NULL,
  `dibuat_oleh` int(11) NOT NULL,
  `diubah_tanggal` datetime NOT NULL,
  `is_posting` tinyint(1) NOT NULL DEFAULT 0,
  `diposting_oleh` int(11) NOT NULL,
  `diposting_tanggal` datetime NOT NULL,
  `mata_uang_id` int(11) DEFAULT NULL,
  `ttd_peminta` varchar(50) DEFAULT NULL,
  `ttd_penyetuju` varchar(50) DEFAULT NULL,
  `diubah_oleh` int(11) DEFAULT NULL,
  `dibuat_tanggal` datetime DEFAULT NULL,
  `proses_approval` tinyint(1) NOT NULL DEFAULT 0,
  `status` varchar(10) NOT NULL DEFAULT 'CREATED',
  `last_approve_position` varchar(10) DEFAULT '',
  `last_approve_user` int(11) DEFAULT NULL,
  `user_approve1` int(11) DEFAULT NULL,
  `user_approve2` int(11) DEFAULT NULL,
  `user_approve3` int(11) DEFAULT NULL,
  `user_approve4` int(11) DEFAULT NULL,
  `user_approve5` int(11) DEFAULT NULL,
  `note_approve1` varchar(50) DEFAULT NULL,
  `note_approve2` varchar(50) DEFAULT NULL,
  `note_approve3` varchar(50) DEFAULT NULL,
  `note_approve4` varchar(50) DEFAULT NULL,
  `note_approve5` varchar(50) DEFAULT NULL,
  `tgl_approve1` datetime DEFAULT NULL,
  `tgl_approve2` datetime DEFAULT NULL,
  `tgl_approve3` datetime DEFAULT NULL,
  `tgl_approve4` datetime DEFAULT NULL,
  `tgl_approve5` datetime DEFAULT NULL,
  `status_approve1` varchar(10) DEFAULT NULL,
  `status_approve2` varchar(10) DEFAULT NULL,
  `status_approve3` varchar(10) DEFAULT NULL,
  `status_approve4` varchar(10) DEFAULT NULL,
  `status_approve5` varchar(10) DEFAULT NULL,
  `quotation_id` int(11) DEFAULT NULL,
  `biaya_kirim` double DEFAULT 0,
  `ppbkb` double DEFAULT 0,
  `status_stok` varchar(20) DEFAULT NULL,
  `lokasi_pp_id` int(11) DEFAULT NULL,
  `diskon` double NOT NULL,
  `biaya_lain` double NOT NULL,
  `pph_nilai` double NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `prc_po_ht`
--

INSERT INTO `prc_po_ht` (`id`, `lokasi_id`, `supplier_id`, `no_po`, `tanggal`, `syarat_bayar_id`, `franco_id`, `catatan`, `sub_total`, `disc`, `ppn`, `pph`, `grand_total`, `dibuat_oleh`, `diubah_tanggal`, `is_posting`, `diposting_oleh`, `diposting_tanggal`, `mata_uang_id`, `ttd_peminta`, `ttd_penyetuju`, `diubah_oleh`, `dibuat_tanggal`, `proses_approval`, `status`, `last_approve_position`, `last_approve_user`, `user_approve1`, `user_approve2`, `user_approve3`, `user_approve4`, `user_approve5`, `note_approve1`, `note_approve2`, `note_approve3`, `note_approve4`, `note_approve5`, `tgl_approve1`, `tgl_approve2`, `tgl_approve3`, `tgl_approve4`, `tgl_approve5`, `status_approve1`, `status_approve2`, `status_approve3`, `status_approve4`, `status_approve5`, `quotation_id`, `biaya_kirim`, `ppbkb`, `status_stok`, `lokasi_pp_id`, `diskon`, `biaya_lain`, `pph_nilai`) VALUES
(38, 263, 4, '0001/PO/DPA/001', '2022-04-03', 1, 2, 'PEMBELIAN TEST', 12500000, 0, 0, 0, 12500000, 40, '2022-04-02 23:03:30', 0, 0, '0000-00-00 00:00:00', 1, NULL, NULL, 40, '2022-04-02 22:51:39', 1, '', 'PO1', 150, 150, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 23, 0, 0, NULL, NULL, 0, 0, 0),
(39, 264, 5, '0002/PO-S0006/SBNE/0522', '2022-05-13', 1, 3, 'ok', 350000, 0, 11, 0, 388500, 40, '2022-05-12 23:01:57', 0, 0, '0000-00-00 00:00:00', 1, NULL, NULL, 72, '2022-05-12 23:00:37', 1, 'RELEASE', NULL, NULL, 1249, NULL, NULL, NULL, NULL, 'ok', NULL, NULL, NULL, NULL, '2022-05-12 23:01:57', NULL, NULL, NULL, NULL, 'APPROVED', NULL, NULL, NULL, NULL, 26, 0, 0, 'Ready Stok', 252, 0, 0, 0),
(42, 264, 6, '0003/PO-S0007/MILL/0522', '2022-05-23', 1, 3, 'sdasd', 0, 0, 0, 0, 0, 54, '2022-05-23 06:11:03', 0, 0, '0000-00-00 00:00:00', 2, NULL, NULL, 54, '2022-05-23 06:11:03', 0, 'CREATED', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 26, 0, 0, 'Ready Stok', 260, 0, 0, 0),
(43, 252, 6, '0004/PO-S0007/SBNE/0522', '2022-05-31', 1, 3, 'sdasd', 100000, 0, 10, 2, 97200, 54, '0000-00-00 00:00:00', 0, 0, '0000-00-00 00:00:00', 1, NULL, NULL, 54, '2022-05-31 15:00:37', 0, 'CREATED', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 26, 0, 0, 'Ready Stok', 252, 10000, 0, 0),
(44, 252, 7, '0005/PO-S0008/SBNE/0622', '2022-06-03', 2, 3, 'sda', 50000, 0, 10, 2, 106500, 54, '0000-00-00 00:00:00', 0, 0, '0000-00-00 00:00:00', 1, NULL, NULL, NULL, '2022-06-03 16:32:38', 0, 'CREATED', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 26, 0, 5, 'Indent', 252, 0, 50, 1),
(45, 252, 7, '0006/PO-S0008/SBNE/0622', '2022-06-03', 2, 3, 'xxx', 50000, 0, 10, 4, 53094, 54, '0000-00-00 00:00:00', 0, 0, '0000-00-00 00:00:00', 1, NULL, NULL, NULL, '2022-06-03 16:38:57', 0, 'CREATED', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 26, 100, 0, 'Indent', 252, 100, 100, 1996);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `prc_po_ht`
--
ALTER TABLE `prc_po_ht`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `prc_po_ht`
--
ALTER TABLE `prc_po_ht`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=46;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
