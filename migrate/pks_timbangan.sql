-- phpMyAdmin SQL Dump
-- version 5.1.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 27, 2021 at 10:36 AM
-- Server version: 10.4.18-MariaDB
-- PHP Version: 7.3.28

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
-- Table structure for table `pks_timbangan`
--

CREATE TABLE `pks_timbangan` (
  `id` int(11) NOT NULL,
  `mill_id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `estate_id` int(11) NOT NULL,
  `tipe` varchar(3) NOT NULL,
  `no_tiket` varchar(50) NOT NULL,
  `no_spat` varchar(50) NOT NULL,
  `tanggal` date NOT NULL,
  `berat_bersih` double NOT NULL,
  `berat_kosong` double NOT NULL,
  `berat_isi` double NOT NULL,
  `jumlah_item` double NOT NULL,
  `jumlah_berondolan` double NOT NULL,
  `supplier_id` int(11) NOT NULL,
  `transportir_id` int(11) DEFAULT NULL,
  `divisi_id` int(11) DEFAULT NULL,
  `blok` varchar(100) NOT NULL,
  `no_plat` varchar(20) NOT NULL,
  `nama_supir` varchar(50) NOT NULL,
  `jam_masuk` varchar(10) NOT NULL,
  `jam_keluar` varchar(10) NOT NULL,
  `uoid` varchar(50) DEFAULT NULL,
  `keterangan` varchar(100) NOT NULL,
  `diubah_oleh` int(11) NOT NULL,
  `diubah_tanggal` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `pks_timbangan`
--

INSERT INTO `pks_timbangan` (`id`, `mill_id`, `item_id`, `estate_id`, `tipe`, `no_tiket`, `no_spat`, `tanggal`, `berat_bersih`, `berat_kosong`, `berat_isi`, `jumlah_item`, `jumlah_berondolan`, `supplier_id`, `transportir_id`, `divisi_id`, `blok`, `no_plat`, `nama_supir`, `jam_masuk`, `jam_keluar`, `uoid`, `keterangan`, `diubah_oleh`, `diubah_tanggal`) VALUES
(110, 260, 5, 252, 'INT', '00021', '121', '2021-12-20', 8000, 10000, 2000, 0, 0, 2, 0, 1, '', 'b1205k', 'aaa', '17:39', '17:39', 'ab3961f1-be90-4bfb-90f5-43ff3bb4b4e9', '', 37, '2021-12-25 11:36:46'),
(111, 1, 0, 2, 'INT', '005', '008', '2021-12-19', 8890, 10000, 1110, 0, 0, 0, 1, 1, '', 'KT2323BE', 'Asep', '2021-12-19', '2021-12-19', '66a9285b-a3f6-406b-bed8-cac742cd5230', '', 1, '2021-12-25 08:01:04'),
(112, 1, 0, 2, 'INT', '1', '2', '2021-12-18', 900, 1000, 100, 0, 0, 0, 1, 1, '', '33', '33', '2021-12-18', '2021-12-18', 'e4ebd7e8-330f-4431-b29d-12702065f086', '', 1, '2021-12-25 08:01:04'),
(113, 1, 0, 2, 'INT', '2', '3', '2021-12-18', 220, 2220, 2000, 0, 0, 0, 3, 1, '', '33', 'www', '2021-12-18', '2021-12-18', '807a0521-cb7f-48a7-935e-95dbc72e7581', '', 1, '2021-12-25 08:01:04'),
(114, 1, 0, 2, 'INT', 'T/2021/12/0001', '123', '2021-12-20', 6400, 7000, 600, 0, 0, 0, 1, 1, '', 'b234', 'ss', '2021-12-20', '2021-12-20', '33804bd6-0243-44d0-b72e-164438e0e5db', '', 1, '2021-12-25 08:01:04'),
(115, 1, 0, 2, 'INT', 'T/2021/12/0002', '33', '2021-12-20', 7200, 8000, 800, 0, 0, 0, 1, 1, '', 'd', 'ee44', '2021-12-20', '2021-12-20', 'cab0477b-1034-4c41-8f8f-27a0c9e602d7', '', 1, '2021-12-25 08:01:05'),
(116, 1, 0, 2, 'INT', 'T/2021/12/0003', '11', '2021-12-20', 6000, 8000, 2000, 0, 0, 0, 5, 2, '', '222', 'asep', '2021-12-20', '2021-12-20', '60c898ba-bc0b-405b-a655-0e0a18601213', '', 1, '2021-12-25 08:01:05'),
(117, 1, 0, 2, 'INT', 'T/2021/12/0004', '11', '2021-12-20', 2500, 5500, 3000, 0, 0, 0, 3, 1, '', '111', 'asep', '2021-12-20', '2021-12-20', 'da9444f4-ce22-4156-be3b-df89017125e3', '', 1, '2021-12-25 08:01:05'),
(118, 1, 0, 2, 'INT', 'T/2021/12/0005', '1212', '2021-12-20', 10000, 12000, 2000, 0, 0, 0, 1, 1, '', '33', 'wewe', '2021-12-20', '2021-12-20', '788095ca-f4dd-478f-854f-7998da2ea352', '', 1, '2021-12-25 08:01:05'),
(119, 1, 0, 0, 'EXT', 'T/2021/12/0006', 'qwq', '2021-12-20', 3100, 3330, 230, 0, 0, 3, 3, 0, '', '2323', 'wew', '2021-12-20', '2021-12-20', 'a93286c6-aac5-4de4-8242-2fe1eb6451c9', '', 1, '2021-12-25 08:01:05'),
(120, 1, 0, 0, 'EXT', 'T/2021/12/0007', '1212', '2021-12-20', 6000, 8000, 2000, 0, 0, 3, 5, 0, '', '121', '121', '2021-12-20', '2021-12-20', 'e50beb34-20b5-48b7-b997-a5eed4d9561a', '', 1, '2021-12-25 08:01:05'),
(121, 1, 1, 2, 'INT', '00021', '121', '2021-12-20', 8000, 10000, 2000, 0, 0, 0, 0, 1, '', 'b1205k', 'aaa', '2021-12-20', '2021-12-20', 'ab3961f1-be90-4bfb-90f5-43ff3bb4b4e9', '', 1, '2021-12-25 08:11:40'),
(122, 1, 1, 2, 'INT', '005', '008', '2021-12-19', 8890, 10000, 1110, 0, 0, 0, 1, 1, '', 'KT2323BE', 'Asep', '2021-12-19', '2021-12-19', '66a9285b-a3f6-406b-bed8-cac742cd5230', '', 1, '2021-12-25 08:11:40'),
(123, 1, 1, 2, 'INT', '1', '2', '2021-12-18', 900, 1000, 100, 0, 0, 0, 1, 1, '', '33', '33', '2021-12-18', '2021-12-18', 'e4ebd7e8-330f-4431-b29d-12702065f086', '', 1, '2021-12-25 08:11:40'),
(124, 1, 1, 2, 'INT', '2', '3', '2021-12-18', 220, 2220, 2000, 0, 0, 0, 3, 1, '', '33', 'www', '2021-12-18', '2021-12-18', '807a0521-cb7f-48a7-935e-95dbc72e7581', '', 1, '2021-12-25 08:11:40'),
(125, 1, 1, 2, 'INT', 'T/2021/12/0001', '123', '2021-12-20', 6400, 7000, 600, 0, 0, 0, 1, 1, '', 'b234', 'ss', '2021-12-20', '2021-12-20', '33804bd6-0243-44d0-b72e-164438e0e5db', '', 1, '2021-12-25 08:11:40'),
(126, 1, 1, 2, 'INT', 'T/2021/12/0002', '33', '2021-12-20', 7200, 8000, 800, 0, 0, 0, 1, 1, '', 'd', 'ee44', '2021-12-20', '2021-12-20', 'cab0477b-1034-4c41-8f8f-27a0c9e602d7', '', 1, '2021-12-25 08:11:40'),
(127, 1, 1, 2, 'INT', 'T/2021/12/0003', '11', '2021-12-20', 6000, 8000, 2000, 0, 0, 0, 5, 2, '', '222', 'asep', '2021-12-20', '2021-12-20', '60c898ba-bc0b-405b-a655-0e0a18601213', '', 1, '2021-12-25 08:11:40'),
(128, 1, 1, 2, 'INT', 'T/2021/12/0005', '1212', '2021-12-20', 10000, 12000, 2000, 0, 0, 0, 1, 1, '', '33', 'wewe', '2021-12-20', '2021-12-20', '788095ca-f4dd-478f-854f-7998da2ea352', '', 1, '2021-12-25 08:11:40'),
(129, 1, 1, 0, 'EXT', 'T/2021/12/0006', 'qwq', '2021-12-20', 3100, 3330, 230, 0, 0, 3, 3, 0, '', '2323', 'wew', '2021-12-20', '2021-12-20', 'a93286c6-aac5-4de4-8242-2fe1eb6451c9', '', 1, '2021-12-25 08:11:40'),
(130, 1, 1, 0, 'EXT', 'T/2021/12/0007', '1212', '2021-12-20', 6000, 8000, 2000, 0, 0, 3, 5, 0, '', '121', '121', '2021-12-20', '2021-12-20', 'e50beb34-20b5-48b7-b997-a5eed4d9561a', '', 1, '2021-12-25 08:11:40'),
(131, 1, 1, 2, 'INT', '1', '2', '2021-12-18', 900, 1000, 100, 0, 0, 0, 1, 1, '', '33', '33', '2021-12-18', '2021-12-18', 'e4ebd7e8-330f-4431-b29d-12702065f086', '', 1, '2021-12-25 10:53:35'),
(132, 1, 1, 2, 'INT', 'T/2021/12/0001', '123', '2021-12-20', 6400, 7000, 600, 0, 0, 0, 1, 1, 'c1', 'b234', 'ss', '2021-12-20', '2021-12-20', '33804bd6-0243-44d0-b72e-164438e0e5db', '', 1, '2021-12-25 10:53:35'),
(133, 1, 1, 2, 'INT', 'T/2021/12/0003', '11', '2021-12-20', 6000, 8000, 2000, 0, 0, 0, 5, 2, 'c1', '222', 'asep', '2021-12-20', '2021-12-20', '60c898ba-bc0b-405b-a655-0e0a18601213', '', 1, '2021-12-25 10:53:35'),
(134, 256, 5, 252, 'INT', 'notiket', 'spat', '2021-12-24', 2, 0, 2, 1, 0, 1, 2, 253, 'a', 'ss', 'supir', '17:24', '17:24', NULL, 'ket', 37, '2021-12-25 11:33:25');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `pks_timbangan`
--
ALTER TABLE `pks_timbangan`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `pks_timbangan`
--
ALTER TABLE `pks_timbangan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=135;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
