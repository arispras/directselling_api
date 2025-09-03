-- phpMyAdmin SQL Dump
-- version 5.1.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Aug 25, 2022 at 02:34 AM
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
-- Table structure for table `hrms_pengajuan_cuti`
--

CREATE TABLE `hrms_pengajuan_cuti` (
  `id` int(11) NOT NULL,
  `tanggal` date NOT NULL,
  `dari_tanggal` date NOT NULL,
  `sampai_tanggal` date NOT NULL,
  `cuti` text NOT NULL,
  `karyawan_id` int(11) NOT NULL,
  `jenis_absensi_id` int(11) NOT NULL,
  `dibuat_oleh` int(11) NOT NULL,
  `dibuat_tanggal` date NOT NULL,
  `diubah_oleh` int(11) NOT NULL,
  `diubah_tanggal` date NOT NULL,
  `is_posting` int(11) NOT NULL,
  `status` varchar(10) NOT NULL,
  `proses_approval` tinyint(1) NOT NULL DEFAULT 0,
  `last_approve_position` varchar(10) NOT NULL,
  `lokasi_id` int(11) NOT NULL,
  `last_approve_user` int(11) NOT NULL,
  `user_approve1` int(11) NOT NULL,
  `note_approve1` text NOT NULL,
  `tgl_approve1` datetime DEFAULT NULL,
  `status_approve1` varchar(10) NOT NULL,
  `user_approve2` int(11) NOT NULL,
  `note_approve2` text NOT NULL,
  `tgl_approve2` datetime DEFAULT NULL,
  `status_approve2` varchar(10) NOT NULL,
  `user_approve3` int(11) NOT NULL,
  `note_approve3` text NOT NULL,
  `tgl_approve3` datetime DEFAULT NULL,
  `status_approve3` varchar(10) NOT NULL,
  `user_approve4` int(11) NOT NULL,
  `note_approve4` text NOT NULL,
  `tgl_approve4` datetime DEFAULT NULL,
  `status_approve4` varchar(10) NOT NULL,
  `user_approve5` int(11) NOT NULL,
  `note_approve5` text NOT NULL,
  `tgl_approve5` datetime DEFAULT NULL,
  `status_approve5` varchar(10) NOT NULL,
  `catatan` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `hrms_pengajuan_cuti`
--

INSERT INTO `hrms_pengajuan_cuti` (`id`, `tanggal`, `dari_tanggal`, `sampai_tanggal`, `cuti`, `karyawan_id`, `jenis_absensi_id`, `dibuat_oleh`, `dibuat_tanggal`, `diubah_oleh`, `diubah_tanggal`, `is_posting`, `status`, `proses_approval`, `last_approve_position`, `lokasi_id`, `last_approve_user`, `user_approve1`, `note_approve1`, `tgl_approve1`, `status_approve1`, `user_approve2`, `note_approve2`, `tgl_approve2`, `status_approve2`, `user_approve3`, `note_approve3`, `tgl_approve3`, `status_approve3`, `user_approve4`, `note_approve4`, `tgl_approve4`, `status_approve4`, `user_approve5`, `note_approve5`, `tgl_approve5`, `status_approve5`, `catatan`) VALUES
(3, '2022-08-18', '2022-08-18', '2022-08-21', 'tester', 150, 3, 54, '2022-08-18', 0, '2022-08-23', 0, '', 1, 'PC1', 252, 186, 186, '', NULL, '', 0, '', NULL, '', 0, '', NULL, '', 0, '', NULL, '', 0, '', NULL, '', ''),
(6, '2022-08-23', '2022-08-23', '2022-08-23', 'tester', 150, 7, 54, '2022-08-23', 0, '0000-00-00', 0, '', 0, '', 252, 0, 0, '', NULL, '', 0, '', NULL, '', 0, '', NULL, '', 0, '', NULL, '', 0, '', NULL, '', ''),
(7, '2022-08-24', '2022-08-24', '2022-08-24', 'sdasdasd', 150, 6, 54, '2022-08-24', 54, '2022-08-25', 0, 'RELEASE', 1, '', 260, 0, 150, 'sample', '2022-08-24 17:01:50', 'APPROVED', 150, 'sample', '2022-08-25 07:32:17', 'APPROVED', 0, '', NULL, '', 0, '', NULL, '', 0, '', NULL, '', ''),
(8, '2022-08-25', '2022-08-25', '2022-08-25', 'sample', 150, 6, 54, '2022-08-25', 54, '2022-08-25', 0, '', 1, 'PC1', 260, 150, 150, '', NULL, '', 0, '', NULL, '', 0, '', NULL, '', 0, '', NULL, '', 0, '', NULL, '', '');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `hrms_pengajuan_cuti`
--
ALTER TABLE `hrms_pengajuan_cuti`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `hrms_pengajuan_cuti`
--
ALTER TABLE `hrms_pengajuan_cuti`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
