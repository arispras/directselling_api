-- phpMyAdmin SQL Dump
-- version 5.1.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 10, 2022 at 06:20 AM
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
-- Table structure for table `gbm_supplier`
--

CREATE TABLE `gbm_supplier` (
  `id` int(11) NOT NULL,
  `kode_supplier` varchar(30) NOT NULL,
  `nama_supplier` varchar(50) NOT NULL,
  `kelompok_id` int(11) DEFAULT NULL,
  `tipe_supplier` varchar(2) DEFAULT NULL,
  `alamat` text DEFAULT NULL,
  `no_telpon` varchar(20) DEFAULT NULL,
  `contact_person` varchar(40) DEFAULT NULL,
  `no_hp` varchar(40) DEFAULT NULL,
  `diubah_oleh` int(11) DEFAULT NULL,
  `diubah_tanggal` datetime DEFAULT current_timestamp(),
  `nama_bank` varchar(50) DEFAULT NULL,
  `no_rekening` varchar(50) DEFAULT NULL,
  `atas_nama` varchar(50) DEFAULT NULL,
  `npwp` varchar(50) DEFAULT NULL,
  `alamat_npwp` varchar(50) DEFAULT NULL,
  `acc_akun_id` int(11) DEFAULT NULL,
  `tipe_pajak` varchar(20) NOT NULL,
  `cabang_bank` varchar(200) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `gbm_supplier`
--

INSERT INTO `gbm_supplier` (`id`, `kode_supplier`, `nama_supplier`, `kelompok_id`, `tipe_supplier`, `alamat`, `no_telpon`, `contact_person`, `no_hp`, `diubah_oleh`, `diubah_tanggal`, `nama_bank`, `no_rekening`, `atas_nama`, `npwp`, `alamat_npwp`, `acc_akun_id`, `tipe_pajak`, `cabang_bank`) VALUES
(4, 'S0005', 'CV.GABE PUTRA MANDIRI', 5, 'SP', 'Balikpapan', '0123', 'Mr X', NULL, 40, '2022-04-21 03:04:48', 'BRI', '12344', 'PT Gabe', NULL, NULL, 1937, '', ''),
(5, 'S0006', 'PT. Kalimantan Agro Nusantara', 5, 'SP', 'Kec. Rantaupulung', '021-1212', 'BPK ANWAR', '08122323', 40, '2022-03-29 08:30:10', 'BRI', '123455', 'PT KALIMANTAN', '444', '-', 1937, '', ''),
(6, 'S0007', 'CV. Farhan Salsabila', 5, 'SP', 'KM 110, Tepian Baru', NULL, NULL, NULL, NULL, '2022-01-21 05:30:02', NULL, NULL, NULL, NULL, NULL, 1937, '', ''),
(7, 'S0008', 'CV. Paris Indo Lisensi', 5, 'SP', 'KM. 93, Tepian Langsat', NULL, NULL, NULL, NULL, '2022-01-21 05:30:02', NULL, NULL, NULL, NULL, NULL, 1937, '', ''),
(8, 'S0009', 'CV.Bumi Indah Borneo', 5, 'SP', 'KM 93, Tepian Langsat', NULL, NULL, NULL, NULL, '2022-01-21 05:30:03', NULL, NULL, NULL, NULL, NULL, 1937, '', ''),
(9, 'S0010', 'CV. Koperasi Tepian Lestari Raya', 5, 'SP', '', NULL, NULL, NULL, NULL, '2022-01-21 05:30:03', NULL, NULL, NULL, NULL, NULL, 1937, '', ''),
(10, 'S0011', 'PT. Kutai Jaya Persada', 5, 'SP', 'KM 110, Tepian Baru', NULL, NULL, NULL, NULL, '2022-01-21 05:30:03', NULL, NULL, NULL, NULL, NULL, 1937, '', ''),
(11, 'S0012', 'CV. Eka Jaya Bengalon', 5, 'SP', 'Kec. Bengalon', NULL, NULL, NULL, NULL, '2022-01-21 05:30:03', NULL, NULL, NULL, NULL, NULL, 1937, '', ''),
(12, 'S0013', 'CV. Apurva (Borneo Cemerlang)', 5, 'SP', '', NULL, NULL, NULL, NULL, '2022-01-21 05:30:03', NULL, NULL, NULL, NULL, NULL, 1937, '', ''),
(13, 'S0014', 'CV. Muara', 5, 'SP', '', NULL, NULL, NULL, NULL, '2022-01-21 05:30:03', NULL, NULL, NULL, NULL, NULL, 1937, '', ''),
(14, 'S0015', 'CV.Karunia Jaya', 5, 'SP', 'KM 106, Tepian Indah', NULL, NULL, NULL, NULL, '2022-01-21 05:30:03', NULL, NULL, NULL, NULL, NULL, 1937, '', ''),
(15, 'S0016', 'CV.Borneo Manunggal Jaya', 5, 'SP', '', NULL, NULL, NULL, NULL, '2022-01-21 05:30:03', NULL, NULL, NULL, NULL, NULL, 1937, '', ''),
(16, 'S0017', 'CV.Semoi Athena Agro Lestari', 5, 'SP', '', NULL, NULL, NULL, NULL, '2022-01-21 05:30:03', NULL, NULL, NULL, NULL, NULL, 1937, '', ''),
(17, 'S0018', 'PT. Kutai Balian Nauli', 5, 'SP', '', NULL, NULL, NULL, NULL, '2022-01-21 05:30:03', NULL, NULL, NULL, NULL, NULL, 1937, '', ''),
(18, 'S0019', 'CV.Borneo Agrochemindo', 5, 'SP', '', NULL, NULL, NULL, NULL, '2022-01-21 05:30:04', NULL, NULL, NULL, NULL, NULL, 1937, '', ''),
(19, 'S0020', 'PT. NIKP', 5, 'SP', '', NULL, NULL, NULL, NULL, '2022-01-21 05:30:04', NULL, NULL, NULL, NULL, NULL, 1937, '', ''),
(20, 'S0021', 'PT. Etam Anugerah Ilahi', 5, 'SP', '', NULL, NULL, NULL, NULL, '2022-01-21 05:30:04', NULL, NULL, NULL, NULL, NULL, 1937, '', ''),
(21, 'S0022', 'PT.Tepian Borneo Sejahtera', 5, 'SP', '', NULL, NULL, NULL, NULL, '2022-01-21 05:30:04', NULL, NULL, NULL, NULL, NULL, 1937, '', ''),
(22, 'S0023', 'CV. Lintas Borneo Mandiri', 5, 'SP', '', NULL, NULL, NULL, NULL, '2022-01-21 05:30:04', NULL, NULL, NULL, NULL, NULL, 1937, '', ''),
(23, 'S0024', 'CV.karya Nauli', 5, 'SP', '', NULL, NULL, NULL, NULL, '2022-01-21 05:30:04', NULL, NULL, NULL, NULL, NULL, 1937, '', ''),
(24, 'S0025', 'CV. Bolon Anugrah Tepian ', 5, 'SP', '', NULL, NULL, NULL, NULL, '2022-01-21 05:30:04', NULL, NULL, NULL, NULL, NULL, 1937, '', ''),
(25, 'S0026', 'CV.Anugrah Makmur Mandiri', 5, 'SP', '', NULL, NULL, NULL, NULL, '2022-01-21 05:30:04', NULL, NULL, NULL, NULL, NULL, 1937, '', ''),
(26, 'S0027', 'CV.Ikhza Mandiri', 5, 'SP', '', NULL, NULL, NULL, NULL, '2022-01-21 05:30:04', NULL, NULL, NULL, NULL, NULL, 1937, '', ''),
(27, 'S0028', 'PT. Inti Mas Sejahtera', 5, 'SP', '', NULL, NULL, NULL, NULL, '2022-01-21 05:30:04', NULL, NULL, NULL, NULL, NULL, 1937, '', ''),
(28, 'S0029', 'CV.Nur Ebi', 5, 'SP', '', NULL, NULL, NULL, NULL, '2022-01-21 05:30:04', NULL, NULL, NULL, NULL, NULL, 1937, '', ''),
(29, 'S0030', 'KOPERASI LONG HOL JAYA', 5, 'SP', '', NULL, NULL, NULL, NULL, '2022-01-21 05:30:05', NULL, NULL, NULL, NULL, NULL, 1937, '', ''),
(30, 'S0031', 'CV. NAZAFA 86', 5, 'SP', '', NULL, NULL, NULL, NULL, '2022-01-21 05:30:05', NULL, NULL, NULL, NULL, NULL, 1937, '', ''),
(31, 'S0032', 'CV. BERKAH HIZRIAH', 5, 'SP', '', NULL, NULL, NULL, NULL, '2022-01-21 05:30:05', NULL, NULL, NULL, NULL, NULL, 1937, '', ''),
(32, 'S0033', 'PT.WEJAS IMANUEL', 5, 'SP', '', NULL, NULL, NULL, NULL, '2022-01-21 05:30:05', NULL, NULL, NULL, NULL, NULL, 1937, '', ''),
(33, 'S0034', 'DPD', 5, 'SP', 'DPA', '0', '0', '0', 40, '2022-01-22 01:22:16', NULL, NULL, NULL, NULL, NULL, 1937, '', ''),
(34, 'S0035', 'PT.DPA', 1, 'SP', 'PT.DPA', '0', '0', '0', 40, '2022-01-22 01:32:07', NULL, NULL, NULL, NULL, NULL, 1937, '', ''),
(35, 'S0036', 'Bali Jaya Transport', 1, 'SP', '.', '.', '.', '.', 40, '2022-01-25 03:34:59', NULL, NULL, NULL, NULL, NULL, 1937, '', ''),
(36, 'S0037', 'Arnes Jaya Mandiri', 5, 'SP', '.', '.', '.', '.', 40, '2022-01-25 03:36:19', NULL, NULL, NULL, NULL, NULL, 1937, '', ''),
(37, 'S0038', 'CV. Ikhza Mandiri', 5, 'SP', '.', '.', '.', '.', 40, '2022-01-25 03:36:57', NULL, NULL, NULL, NULL, NULL, 1937, '', ''),
(38, 'S0039', 'BUMDES', 1, 'SP', '.', '.', '.', '.', 40, '2022-01-25 03:37:35', NULL, NULL, NULL, NULL, NULL, 1937, '', ''),
(39, 'S0040', 'PUTRA JAYA MANDIRI', 5, 'SP', '.', '.', '.', '.', 40, '2022-01-25 03:39:14', NULL, NULL, NULL, NULL, NULL, 1937, '', ''),
(40, 'S0041', 'TEPIAN LESTARI MANDIRI', 5, 'SP', '.', '.', '.', '.', 40, '2022-01-25 03:39:01', NULL, NULL, NULL, NULL, NULL, 1937, '', ''),
(41, 'S0042', 'KOPERASI TITIAN PELANGI INDAH', 5, 'SP', '.', '.', '.', '.', 40, '2022-01-25 03:40:41', NULL, NULL, NULL, NULL, NULL, 1937, '', ''),
(42, 'S0043', 'CV. KUTAI MITRA SEJATI', 5, 'SP', '.', '.', '.', '.', 40, '2022-01-25 03:41:44', NULL, NULL, NULL, NULL, NULL, 1937, '', ''),
(43, 'S0044', 'BUMDES', 1, 'TR', '-', '-', '-', '-', 61, '2022-01-29 02:50:06', NULL, NULL, NULL, NULL, NULL, 1937, '', ''),
(44, 'S0045', 'CV. IKHZA MANDIRI', 1, 'TR', '-', '-', '-', '-', 61, '2022-01-29 02:50:31', NULL, NULL, NULL, NULL, NULL, 1937, '', ''),
(45, 'S0046', 'CV. PUTRA JAYA MANDIRI', 1, 'TR', '--', '-', '-', '-', 40, '2022-01-29 05:18:22', NULL, NULL, NULL, NULL, NULL, 1937, '', ''),
(46, 'S0047', 'ARNES JAYA MANDIRI', 1, 'TR', '-', '-', '-', '-', 61, '2022-01-31 08:44:20', NULL, NULL, NULL, NULL, NULL, 1937, '', ''),
(47, 'a', 'a', 1, 'SP', 'a', 'a', 'a', 'a', 54, '2022-05-10 06:18:57', 'a', 'a', 'a', 'a', 'a', 1538, 'NON PKP', '');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `gbm_supplier`
--
ALTER TABLE `gbm_supplier`
  ADD PRIMARY KEY (`id`),
  ADD KEY `kelompok_id` (`kelompok_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `gbm_supplier`
--
ALTER TABLE `gbm_supplier`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=48;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `gbm_supplier`
--
ALTER TABLE `gbm_supplier`
  ADD CONSTRAINT `gbm_supplier_ibfk_1` FOREIGN KEY (`kelompok_id`) REFERENCES `gbm_supplier_kelompok` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
