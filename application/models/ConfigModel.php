<?php

/**
 * Class Model untuk Config
 *
 * @package Elearning Dokumenary
 * @link    http://www.dokumenary.net
 * @author  Almazari <almazary@gmail.com>
 */
class ConfigModel extends CI_Model
{
  /**
   * Method untuk membuat tabel-tabel default pada aplikasi
   * @param  string $table  bisa all atau nama_tabelnya tanpa prefix
   * @return boolean
   */
  public function create_default_table($table = "all")
  {
    $default_table = array(

      "kelas",
      "kelas_siswa",
      "login",
      "mapel",
      "mapel_ajar",
      "mapel_kelas",
      "materi",
      "materi_kelas",
      "nilai_tugas",
      "pengajar",
      "pengaturan",
      "pilihan",
      "siswa",
      "tugas",
      "tugas_kelas",
      "tugas_pertanyaan",
      "field_tambahan",
      "messages",
      "komentar",
      "login_log",
      "pengumuman",
      "chat_parent",
      "ijin_online",
      "ortu",
      "ortu_siswa",
      "presensi",
      "presensi_mapel",
      "semester",
      "tbl_bank",
      "tbl_biaya",
      "tbl_pembayaran",
      "tbl_rincianbiaya",
      "tbl_rpp",
      "timeschedule",
      "tr_trackrecord",
    );

    if ($table == "all") {
      foreach ($default_table as $tb) {
        $nama_fungsi = "create_tb_{$tb}";
        $this->$nama_fungsi();
      }
    } elseif (in_array($table, $default_table)) {
      $nama_fungsi = "create_tb_{$table}";
      $this->$nama_fungsi();
    }

    return true;
  }

  /**
   * Method untuk nambahin index tabel-table default
   */
  public function update_index_default_table()
  {
    $this->db->query("ALTER TABLE `{$this->db->dbprefix}pengumuman` ADD INDEX(`pengajar_id`);");
    $this->db->query("ALTER TABLE `{$this->db->dbprefix}login_log` ADD INDEX(`login_id`);");
    $this->db->query("ALTER TABLE `{$this->db->dbprefix}komentar` ADD INDEX(`login_id`, `materi_id`);");
    $this->db->query("ALTER TABLE `{$this->db->dbprefix}messages` ADD INDEX(`sesi_id`,`type_id`, `owner_id`, `sender_receiver_id`);");
    $this->db->query("ALTER TABLE `{$this->db->dbprefix}kelas` ADD INDEX(`parent_id`);");
    $this->db->query("ALTER TABLE `{$this->db->dbprefix}kelas_siswa` ADD INDEX(`kelas_id`, `siswa_id`);");
    $this->db->query("ALTER TABLE `{$this->db->dbprefix}login` ADD INDEX(`username`, `siswa_id`, `pengajar_id`);");
    $this->db->query("ALTER TABLE `{$this->db->dbprefix}mapel_ajar` ADD INDEX(`hari_id`, `pengajar_id`, `mapel_kelas_id`);");
    $this->db->query("ALTER TABLE `{$this->db->dbprefix}mapel_kelas` ADD INDEX(`kelas_id`, `mapel_id`);");
    $this->db->query("ALTER TABLE `{$this->db->dbprefix}materi` ADD INDEX(`mapel_id`, `pengajar_id`, `siswa_id`);");
    $this->db->query("ALTER TABLE `{$this->db->dbprefix}materi_kelas` ADD INDEX(`materi_id`, `kelas_id`);");
    $this->db->query("ALTER TABLE `{$this->db->dbprefix}nilai_tugas` ADD INDEX(`tugas_id`, `siswa_id`);");
    $this->db->query("ALTER TABLE `{$this->db->dbprefix}pengajar` ADD INDEX(`nip`);");
    $this->db->query("ALTER TABLE `{$this->db->dbprefix}pilihan` ADD INDEX(`pertanyaan_id`, `kunci`);");
    $this->db->query("ALTER TABLE `{$this->db->dbprefix}siswa` ADD INDEX(`nis`, `nama`, `status_id`);");
    $this->db->query("ALTER TABLE `{$this->db->dbprefix}tugas` ADD INDEX(`mapel_id`, `pengajar_id`, `type_id`);");
    $this->db->query("ALTER TABLE `{$this->db->dbprefix}tugas_kelas` ADD INDEX(`tugas_id`, `kelas_id`);");
    $this->db->query("ALTER TABLE `{$this->db->dbprefix}tugas_pertanyaan` ADD INDEX(`tugas_id`);");
    $this->db->query("ALTER TABLE `{$this->db->dbprefix}ijin_online` ADD INDEX(`id_siswa`);");
    $this->db->query("ALTER TABLE `{$this->db->dbprefix}ortu` ADD INDEX(`id`,`nama`,`status_id`);");
    $this->db->query("ALTER TABLE `{$this->db->dbprefix}presensi` ADD INDEX(`Siswa`);");
    $this->db->query("ALTER TABLE `{$this->db->dbprefix}presensi_mapel` ADD INDEX(`Siswa`);");
    $this->db->query("ALTER TABLE `{$this->db->dbprefix}semester` ADD INDEX(`semester_code`,`semester_name`);");
    $this->db->query("ALTER TABLE `{$this->db->dbprefix}tbl_bank` ADD INDEX(`namabank`);");
    $this->db->query("ALTER TABLE `{$this->db->dbprefix}tbl_biaya` ADD INDEX(`kategori`,`id_kelas`);");
    $this->db->query("ALTER TABLE `{$this->db->dbprefix}tbl_pembayaran` ADD INDEX(`id_pembayaran`,`id_siswa`,`id_rincian`);");
    $this->db->query("ALTER TABLE `{$this->db->dbprefix}tbl_rincianbiaya` ADD INDEX(`id_biaya`,`komponen`);");
    $this->db->query("ALTER TABLE `{$this->db->dbprefix}tbl_rpp` ADD INDEX(`id_rpp`,`kelas_id`,`mapel_id`);");
    $this->db->query("ALTER TABLE `{$this->db->dbprefix}timeschedule` ADD INDEX(`IDTimeSchedule`,`SignIn`,`SignOut`);");
    $this->db->query("ALTER TABLE `{$this->db->dbprefix}tr_trackrecord` ADD INDEX(`id_trackrecord`,`siswa_id`);");


    return true;
  }

  /**
   * Method untuk membuat tabel chat_parent
   */
  public function create_tb_chat_parent()
  {
    $this->db->query("CREATE TABLE IF NOT EXISTS `{$this->db->dbprefix}chat_parent` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `sesi_id` int(11) NOT NULL,
          `last_chat` datetime DEFAULT NULL,
          PRIMARY KEY (`id`)
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8;");
  }

  /**
   * Method untuk membuat tabel ijin_online
   */
  public function create_tb_ijin_online()
  {
    $this->db->query("CREATE TABLE IF NOT EXISTS `{$this->db->dbprefix}ijin_online` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `id_siswa` int(11) NOT NULL,
          `tanggal_ijin` datetime NOT NULL,
          `keterangan` text,
          `foto` text,
          PRIMARY KEY (`id`)
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8;");

    $this->db->query("ALTER TABLE `{$this->db->dbprefix}ijin_online` ADD INDEX(`id_siswa`);");
  }

  /**
   * Method untuk membuat tabel ortu
   */
  public function create_tb_ortu()
  {
    $this->db->query("CREATE TABLE IF NOT EXISTS `{$this->db->dbprefix}ortu` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `nama` varchar(50) NOT NULL,
          `jenis_kelamin` varchar(10) NOT NULL,
          `alamat` varchar(255) NOT NULL,
          `telp` varchar(50) NOT NULL,
          `foto` text ,
          `fcm_token` text ,
          `status_id` tinyint(1) NOT NULL COMMENT '0=pending, 1=aktif, 2=blok',
          PRIMARY KEY (`id`)
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8;");

    $this->db->query("ALTER TABLE `{$this->db->dbprefix}ortu` ADD INDEX(`id`,`nama`,`status_id`);");
  }

  /**
   * Method untuk membuat tabel ortu_siswa
   */
  public function create_tb_ortu_siswa()
  {
    $this->db->query("CREATE TABLE IF NOT EXISTS `{$this->db->dbprefix}ortu_siswa` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `ortu_id` int(11)  NOT NULL,
          `siswa_id` int(11) NOT NULL,
          `status_id` tinyint(1) NOT NULL DEFAULT '1' COMMENT '0=tidak aktif,1=aktif',
          PRIMARY KEY (`id`)
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8;");

    $this->db->query("ALTER TABLE `{$this->db->dbprefix}ortu_siswa` ADD INDEX(`ortu_id`,`siswa_id`,`status_id`);");
  }

  /**
   * Method untuk membuat tabel presensi
   */
  public function create_tb_presensi()
  {
    $this->db->query("CREATE TABLE IF NOT EXISTS `{$this->db->dbprefix}presensi` (
          `IDPresensi` int(11) NOT NULL AUTO_INCREMENT,
          `Type` int(11)  NOT NULL COMMENT '0=Finger ,1=Digital,2=Ijin ',
          `Siswa` int(11) NOT NULL,
          `Date` DATE NOT NULL,
          `Time` TIME NOT NULL,
          `Status` int(11) NOT NULL  COMMENT '0=Masuk ,1=Keluar',
          `Remarks` varchar(255) NOT NULL,
          `file` varchar(255),
          PRIMARY KEY (`IDPresensi`)
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8;");

    $this->db->query("ALTER TABLE `{$this->db->dbprefix}presensi` ADD INDEX(`Siswa`);");
  }

  /**
   * Method untuk membuat tabel presensi_mapel
   */
  public function create_tb_presensi_mapel()
  {
    $this->db->query("CREATE TABLE IF NOT EXISTS `{$this->db->dbprefix}presensi_mapel` (
          `IDPresensi` int(11) NOT NULL AUTO_INCREMENT,
          `Type` int(11)  NOT NULL COMMENT '0=Finger ,1=Digital,2=Ijin ',
          `Siswa` int(11) NOT NULL,
          `Date` DATE NOT NULL,
          `Time` TIME NOT NULL,
          `Status` int(11) NOT NULL  COMMENT '0=Masuk ,1=Keluar',
          `Remarks` varchar(255) NOT NULL,
          `file` varchar(255),
          PRIMARY KEY (`IDPresensi`)
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8;");

    $this->db->query("ALTER TABLE `{$this->db->dbprefix}presensi_mapel` ADD INDEX(`Siswa`);");
  }

  /**
   * Method untuk membuat tabel semester
   */
  public function create_tb_semester()
  {
    $this->db->query("CREATE TABLE IF NOT EXISTS `{$this->db->dbprefix}semester` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `semester_code` varchar(30),
          `semester_name` varchar(50),
          `description` varchar(255),
          PRIMARY KEY (`id`)
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8;");

    $this->db->query("ALTER TABLE `{$this->db->dbprefix}semester` ADD INDEX(`semester_code`,`semester_name`);");
  }

  /**
   * Method untuk membuat tabel tbl_bank
   */
  public function create_tb_tbl_bank()
  {
    $this->db->query("CREATE TABLE IF NOT EXISTS `{$this->db->dbprefix}tbl_bank` (
          `id_bank` int(11) NOT NULL AUTO_INCREMENT,
          `namabank` varchar(255),
          `pemilik` varchar(255),
          `norek` varchar(255),
          PRIMARY KEY (`id_bank`)
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8;");

    $this->db->query("ALTER TABLE `{$this->db->dbprefix}tbl_bank` ADD INDEX(`namabank`);");
  }

  /**
   * Method untuk membuat tabel tbl_biaya
   */
  public function create_tb_tbl_biaya()
  {
    $this->db->query("CREATE TABLE IF NOT EXISTS `{$this->db->dbprefix}tbl_biaya` (
          `id_biaya` int(11) NOT NULL AUTO_INCREMENT,
          `kategori` varchar(255) NOT NULL,
          `id_kelas` int(11) NOT NULL,
          `tahun` int(11) NOT NULL,
          PRIMARY KEY (`id_biaya`)
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8;");

    $this->db->query("ALTER TABLE `{$this->db->dbprefix}tbl_biaya` ADD INDEX(`kategori`,`id_kelas`);");
  }

  /**
   * Method untuk membuat tabel tbl_pembayaran
   */
  public function create_tb_tbl_pembayaran()
  {
    $this->db->query("CREATE TABLE IF NOT EXISTS `{$this->db->dbprefix}tbl_pembayaran` (
          `id_pembayaran` int(11) NOT NULL AUTO_INCREMENT,
          `id_siswa` int(50),
          `id_rincian` int(11),
          `jumlah` varchar(50),
          `tanggal` text(0),
          `metode` varchar(50),
          `file` text(0),
          `ket` text(0),
          PRIMARY KEY (`id_pembayaran`)
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8;");
    $this->db->query("ALTER TABLE `{$this->db->dbprefix}tbl_pembayaran` ADD INDEX(`id_pembayaran`,`id_siswa`,`id_rincian`);");
  }

  /**
   * Method untuk membuat tabel tbl_rincianbiaya
   */
  public function create_tb_tbl_rincianbiaya()
  {
    $this->db->query("CREATE TABLE IF NOT EXISTS `{$this->db->dbprefix}tbl_rincianbiaya` (
          `id_rincian` int(11) NOT NULL AUTO_INCREMENT,
          `id_biaya` int(11),
          `komponen` varchar(50),
          `biaya` varchar(50),
          PRIMARY KEY (`id_rincian`)
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8;");

    $this->db->query("ALTER TABLE `{$this->db->dbprefix}tbl_rincianbiaya` ADD INDEX(`id_rincian`,`id_biaya`);");
  }

  /**
   * Method untuk membuat tabel tbl_rpp
   */
  public function create_tb_tbl_rpp()
  {
    $this->db->query("CREATE TABLE IF NOT EXISTS `{$this->db->dbprefix}tbl_rpp` (
          `id_rpp` int(11) NOT NULL AUTO_INCREMENT,
          `judul` varchar(255) NOT NULL,
          `deskripsi` text(0) NOT NULL,
          `kelas_id` int(11) NOT NULL,
          `mapel_id` int(11) NOT NULL,
          `lampiran` text(0),
          PRIMARY KEY (`id_rpp`)
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8;");

    $this->db->query("ALTER TABLE `{$this->db->dbprefix}tbl_rpp` ADD INDEX(`id_rpp`,`kelas_id`,`mapel_id`);");
  }

  /**
   * Method untuk membuat tabel timeschedule
   */
  public function create_tb_timeschedule()
  {
    $this->db->query("CREATE TABLE IF NOT EXISTS `{$this->db->dbprefix}timeschedule` (
          `IDTimeSchedule` int(11) NOT NULL AUTO_INCREMENT,
          `Days` varchar(3) NOT NULL,
          `SignIn` time(0) NOT NULL,
          `SignOut` time(0) NOT NULL,
          `SignInTolerance` int(11) NOT NULL,
          `SignOutTolerance` int(11) NOT NULL,
          `Remarks` text(0),
          PRIMARY KEY (`IDTimeSchedule`)
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8;");

    $this->db->query("ALTER TABLE `{$this->db->dbprefix}timeschedule` ADD INDEX(`IDTimeSchedule`,`SignIn`,`SignOut`);");
  }
  /**
   * Method untuk membuat tabel tr_trackrecord
   */
  public function create_tb_tr_trackrecord()
  {
    $this->db->query("CREATE TABLE IF NOT EXISTS `{$this->db->dbprefix}tr_trackrecord` (
          `id_trackrecord` int(11) NOT NULL AUTO_INCREMENT,
          `siswa_id` int(13) NOT NULL,
          `param_satu` text(0),
          `param_dua` text(0),
          PRIMARY KEY (`id_trackrecord`)
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8;");

    $this->db->query("ALTER TABLE `{$this->db->dbprefix}tr_trackrecord` ADD INDEX(`id_trackrecord`,`siswa_id`);");
  }

  /**
   * Method untuk membuat tabel pengumuman
   */
  public function create_tb_pengumuman()
  {
    $this->db->query("CREATE TABLE IF NOT EXISTS `{$this->db->dbprefix}pengumuman` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `judul` varchar(255) NOT NULL,
          `konten` text NOT NULL,
          `tgl_tampil` date NOT NULL,
          `tgl_tutup` date NOT NULL,
          `tampil_siswa` tinyint(1) NOT NULL DEFAULT '1',
          `tampil_pengajar` tinyint(1) NOT NULL DEFAULT '1',
          `pengajar_id` int(11) NOT NULL,
          PRIMARY KEY (`id`)
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8;");

    $this->db->query("ALTER TABLE `{$this->db->dbprefix}pengumuman` ADD INDEX(`pengajar_id`);");
  }

  /**
   * Method untuk membuat tabel login log
   */
  public function create_tb_login_log()
  {
    $this->db->query("CREATE TABLE IF NOT EXISTS `{$this->db->dbprefix}login_log` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `login_id` int(11) NOT NULL,
            `lasttime` datetime NOT NULL,
            `agent` text NOT NULL,
            PRIMARY KEY (`id`)
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8;");

    $this->db->query("ALTER TABLE `{$this->db->dbprefix}login_log` ADD INDEX(`login_id`);");
  }



  /**
   * Method untuk membuat tabel komentar
   */
  public function create_tb_komentar()
  {
    $this->db->query("CREATE TABLE IF NOT EXISTS `{$this->db->dbprefix}komentar` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `login_id` int(11) NOT NULL,
          `materi_id` int(11) NOT NULL,
          `tampil` tinyint(1) NOT NULL DEFAULT '1' COMMENT '0=tidak,1=tampil',
          `konten` text,
          `tgl_posting` datetime DEFAULT NULL,
          PRIMARY KEY (`id`)
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8;");

    $this->db->query("ALTER TABLE `{$this->db->dbprefix}komentar` ADD INDEX(`login_id`, `materi_id`);");
  }

  /**
   * Method untuk membuat tabel messages
   */
  public function create_tb_messages()
  {
    $this->db->query("CREATE TABLE IF NOT EXISTS `{$this->db->dbprefix}messages` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `sesi_id` int(11) NOT NULL,
          `type_id` varchar(20) NOT NULL,
          `content` text NOT NULL,
          `owner_id` int(11) NOT NULL,
          `sender_receiver_id` int(11) NOT NULL,
          `date` datetime NOT NULL,
          `opened` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0=belum,1=sudah',
          PRIMARY KEY (`id`)
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8;");

    $this->db->query("ALTER TABLE `{$this->db->dbprefix}messages` ADD INDEX(`sesi_id`, `type_id`, `owner_id`, `sender_receiver_id`);");
  }

  /**
   * Method untuk membuat tabel kelas
   */
  public function create_tb_kelas()
  {
    $this->db->query("CREATE TABLE IF NOT EXISTS `{$this->db->dbprefix}kelas` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `nama` varchar(45) NOT NULL,
          `parent_id` int(11) DEFAULT NULL,
          `urutan` int(11) NOT NULL,
          `aktif` tinyint(1) NOT NULL DEFAULT '1' COMMENT '1=aktif 0=tidak',
          PRIMARY KEY (`id`)
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8;");

    $this->db->query("ALTER TABLE `{$this->db->dbprefix}kelas` ADD INDEX(`parent_id`);");
  }

  /**
   * Method untuk membuat tabel kelas_siswa
   */
  public function create_tb_kelas_siswa()
  {
    $this->db->query("CREATE TABLE IF NOT EXISTS `{$this->db->dbprefix}kelas_siswa` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `kelas_id` int(11) NOT NULL,
          `siswa_id` int(11) NOT NULL,
          `aktif` tinyint(1) NOT NULL COMMENT '0 jika bukan, 1 jika ya',
          PRIMARY KEY (`id`)
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8;");

    $this->db->query("ALTER TABLE `{$this->db->dbprefix}kelas_siswa` ADD INDEX(`kelas_id`, `siswa_id`);");
  }

  /**
   * Method untuk membuat tabel login
   */
  public function create_tb_login()
  {
    $this->db->query("CREATE TABLE IF NOT EXISTS `{$this->db->dbprefix}login` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `username` varchar(255) NOT NULL,
          `password` varchar(255) NOT NULL,
          `siswa_id` int(11) DEFAULT NULL,
          `pengajar_id` int(11) DEFAULT NULL,
          `is_admin` tinyint(1) NOT NULL COMMENT '0=tidak,1=ya',
          `reset_kode` varchar(255) DEFAULT NULL,
          PRIMARY KEY (`id`)
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8;");

    $this->db->query("ALTER TABLE `{$this->db->dbprefix}login` ADD INDEX(`username`, `siswa_id`, `pengajar_id`);");
  }

  /**
   * Method untuk membuat tabel mapel
   */
  public function create_tb_mapel()
  {
    $this->db->query("CREATE TABLE IF NOT EXISTS `{$this->db->dbprefix}mapel` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `nama` varchar(255) NOT NULL,
          `info` text,
          `aktif` tinyint(1) NOT NULL DEFAULT '1' COMMENT '1 = ya, 0 = tidak',
          PRIMARY KEY (`id`)
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8;");
  }

  /**
   * Method untuk membuat tabel mapel_ajar
   */
  public function create_tb_mapel_ajar()
  {
    $this->db->query("CREATE TABLE IF NOT EXISTS `{$this->db->dbprefix}mapel_ajar` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `hari_id` tinyint(1) NOT NULL COMMENT '1=senin,2=selasa,3=rabu,4=kamis,5=jum''at,6=sabtu,7=minggu',
          `jam_mulai` time NOT NULL,
          `jam_selesai` time NOT NULL,
          `pengajar_id` int(11) NOT NULL,
          `mapel_kelas_id` int(11) NOT NULL,
          `aktif` tinyint(1) NOT NULL DEFAULT '1' COMMENT '1 = aktif 0 = tidak',
          PRIMARY KEY (`id`)
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8;");

    $this->db->query("ALTER TABLE `{$this->db->dbprefix}mapel_ajar` ADD INDEX(`hari_id`, `pengajar_id`, `mapel_kelas_id`);");
  }

  /**
   * Method untuk membuat tabel mapel_kelas
   */
  public function create_tb_mapel_kelas()
  {
    $this->db->query("CREATE TABLE IF NOT EXISTS `{$this->db->dbprefix}mapel_kelas` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `kelas_id` int(11) NOT NULL,
          `mapel_id` int(11) NOT NULL,
          `aktif` tinyint(1) NOT NULL DEFAULT '1',
          PRIMARY KEY (`id`)
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8;");

    $this->db->query("ALTER TABLE `{$this->db->dbprefix}mapel_kelas` ADD INDEX(`kelas_id`, `mapel_id`);");
  }

  /**
   * Method untuk membuat tabel materi
   */
  public function create_tb_materi()
  {
    $this->db->query("CREATE TABLE IF NOT EXISTS `{$this->db->dbprefix}materi` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `mapel_id` int(11) NOT NULL,
          `pengajar_id` int(11) DEFAULT NULL,
          `siswa_id` int(11) DEFAULT NULL,
          `judul` varchar(255) NOT NULL,
          `konten` text,
          `file` text,
          `tgl_posting` datetime NOT NULL,
          `publish` tinyint(1) NOT NULL DEFAULT '0',
          `views` int(11) NOT NULL DEFAULT '1',
          PRIMARY KEY (`id`)
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8;");

    $this->db->query("ALTER TABLE `{$this->db->dbprefix}materi` ADD INDEX(`mapel_id`, `pengajar_id`, `siswa_id`);");
  }

  /**
   * Method untuk membuat tabel materi_kelas
   */
  public function create_tb_materi_kelas()
  {
    $this->db->query("CREATE TABLE IF NOT EXISTS `{$this->db->dbprefix}materi_kelas` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `materi_id` int(11) NOT NULL,
          `kelas_id` int(11) NOT NULL,
          PRIMARY KEY (`id`)
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8;");

    $this->db->query("ALTER TABLE `{$this->db->dbprefix}materi_kelas` ADD INDEX(`materi_id`, `kelas_id`);");
  }

  /**
   * Method untuk membuat tabel nilai_tugas
   */
  public function create_tb_nilai_tugas()
  {
    $this->db->query("CREATE TABLE IF NOT EXISTS `{$this->db->dbprefix}nilai_tugas` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `nilai` float NOT NULL,
          `tugas_id` int(11) NOT NULL,
          `siswa_id` int(11) NOT NULL,
          PRIMARY KEY (`id`)
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8;");

    $this->db->query("ALTER TABLE `{$this->db->dbprefix}nilai_tugas` ADD INDEX(`tugas_id`, `siswa_id`);");
  }

  /**
   * Method untuk membua tabel pengajar
   */
  public function create_tb_pengajar()
  {
    $this->db->query("CREATE TABLE IF NOT EXISTS `{$this->db->dbprefix}pengajar` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `nip` varchar(45) DEFAULT NULL,
          `nama` varchar(100) NOT NULL,
          `jenis_kelamin` varchar(9) NOT NULL,
          `tempat_lahir` varchar(45) NOT NULL,
          `tgl_lahir` date DEFAULT NULL,
          `alamat` varchar(255) NOT NULL,
          `foto` text,
          `status_id` tinyint(1) NOT NULL COMMENT '0=pending, 1=aktif, 2=blok',
          PRIMARY KEY (`id`)
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8;");

    $this->db->query("ALTER TABLE `{$this->db->dbprefix}pengajar` ADD INDEX(`nip`);");
  }

  /**
   * Method untuk membuat tabel pengaturan
   */
  public function create_tb_pengaturan()
  {
    $this->db->query("CREATE TABLE IF NOT EXISTS `{$this->db->dbprefix}pengaturan` (
          `id` varchar(255) NOT NULL,
          `nama` varchar(255) DEFAULT NULL,
          `value` text,
          PRIMARY KEY (`id`)
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8;");
  }

  /**
   * Method untuk membuat tabel pilihan
   */
  public function create_tb_pilihan()
  {
    $this->db->query("CREATE TABLE IF NOT EXISTS `{$this->db->dbprefix}pilihan` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `pertanyaan_id` int(11) NOT NULL,
          `konten` text NOT NULL,
          `kunci` tinyint(4) NOT NULL DEFAULT '0' COMMENT '0=tidak',
          `urutan` int(11) NOT NULL,
          `aktif` tinyint(1) NOT NULL DEFAULT '1',
          PRIMARY KEY (`id`)
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8;");

    $this->db->query("ALTER TABLE `{$this->db->dbprefix}pilihan` ADD INDEX(`pertanyaan_id`);");
  }

  /**
   * Method untuk membuat tabel siswa
   */
  public function create_tb_siswa()
  {
    $this->db->query("CREATE TABLE IF NOT EXISTS `{$this->db->dbprefix}siswa` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `nis` varchar(45) DEFAULT NULL,
          `nama` varchar(100) NOT NULL,
          `jenis_kelamin` varchar(9) NOT NULL COMMENT 'Laki-laki dan Perempuan',
          `tempat_lahir` varchar(45) NOT NULL,
          `tgl_lahir` date DEFAULT NULL,
          `agama` char(7) DEFAULT NULL,
          `alamat` varchar(255) NOT NULL,
          `tahun_masuk` year(4) NOT NULL,
          `foto` text,
          `status_id` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0=pending, 1=aktif, 2=blok, 3=alumni, 4=deleted',
          PRIMARY KEY (`id`)
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8;");

    $this->db->query("ALTER TABLE `{$this->db->dbprefix}siswa` ADD INDEX(`nis`, `nama`, `status_id`);");
  }

  /**
   * Method untuk membuat tabel tugas
   */
  public function create_tb_tugas()
  {
    $this->db->query("CREATE TABLE IF NOT EXISTS `{$this->db->dbprefix}tugas` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `mapel_id` int(11) NOT NULL,
          `pengajar_id` int(11) NOT NULL,
          `type_id` tinyint(1) NOT NULL COMMENT '1=upload,2=essay,3=ganda',
          `judul` varchar(255) NOT NULL,
          `durasi` int(11) DEFAULT NULL COMMENT 'lama pengerjaan dalam menit',
          `info` text,
          `aktif` tinyint(1) NOT NULL DEFAULT '0',
          `tgl_buat` datetime DEFAULT NULL,
          `tampil_siswa` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0=tidak tampil di siswa, 1=tampil siswa',
          PRIMARY KEY (`id`)
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8;");

    $this->db->query("ALTER TABLE `{$this->db->dbprefix}tugas` ADD INDEX(`mapel_id`, `pengajar_id`, `type_id`);");
  }

  /**
   * Method untuk membuat tabel tugas_kelas
   */
  public function create_tb_tugas_kelas()
  {
    $this->db->query("CREATE TABLE IF NOT EXISTS `{$this->db->dbprefix}tugas_kelas` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `tugas_id` int(11) NOT NULL,
          `kelas_id` int(11) NOT NULL,
          PRIMARY KEY (`id`)
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8;");

    $this->db->query("ALTER TABLE `{$this->db->dbprefix}tugas_kelas` ADD INDEX(`tugas_id`, `kelas_id`);");
  }

  /**
   * Method untuk membuat tabel tugas_pertanyaan
   */
  public function create_tb_tugas_pertanyaan()
  {
    $this->db->query("CREATE TABLE IF NOT EXISTS `{$this->db->dbprefix}tugas_pertanyaan` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `pertanyaan` text NOT NULL,
          `urutan` int(11) NOT NULL,
          `tugas_id` int(11) NOT NULL,
          `aktif` tinyint(1) NOT NULL DEFAULT '1',
          PRIMARY KEY (`id`)
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8;");

    $this->db->query("ALTER TABLE `{$this->db->dbprefix}tugas_pertanyaan` ADD INDEX(`tugas_id`);");
  }

  /**
   * Method untuk membuat tabel field_tambahan
   */
  public function create_tb_field_tambahan()
  {
    $this->db->query("CREATE TABLE IF NOT EXISTS `{$this->db->dbprefix}field_tambahan` (
          `id` varchar(255) NOT NULL,
          `nama` varchar(255) DEFAULT NULL,
          `value` longtext,
          PRIMARY KEY (`id`)
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8;");
  }

  /**
   * Method untuk mendapatkan semua gambar slider
   *
   * @return array
   */
  public function get_all_slider_img()
  {
    $this->db->where('id', 'img-slide-1');
    $this->db->or_where('id', 'img-slide-2');
    $this->db->or_where('id', 'img-slide-3');
    $this->db->or_where('id', 'img-slide-4');
    $this->db->order_by('id');
    $results = $this->db->get('fwk_setting');

    $data = array();
    foreach ($results->result_array() as $val) {
      if (empty($val['value'])) {
        continue;
      }

      # cari title nya sekalian
      $title_id = substr($val['id'], -1);

      $retrieve     = $this->retrieve('info-slide-' . $title_id);
      $val['title'] = empty($retrieve['value']) ? '' : $retrieve['value'];
      $data[]       = $val;
    }

    return $data;
  }

  /**
   * Method untuk menghapus field tambahan
   *
   * @param  integer $id
   * @return boolean
   * @author Almazari <almazary@gmail.com>
   */
  public function delete_field($id)
  {
    $this->db->where('id', $id);
    return $this->db->delete('field_tambahan');
  }

  /**
   * Method untuk mendapatkan informasi pengaturan
   *
   * @param  string $id
   * @return array
   * @author Almazari <almazary@gmail.com>
   */
  public function retrieve($id)
  {
    $this->db->where('id', $id);
    $result = $this->db->get('fwk_setting', 1);
    return $result->row_array();
  }

  /**
   * Method untuk mendapatkan informasi field tambahan
   *
   * @param  string $id
   * @return array
   * @author Almazari <almazary@gmail.com>
   */
  public function retrieve_field($id)
  {
    $this->db->where('id', $id);
    $result = $this->db->get('field_tambahan', 1);
    return $result->row_array();
  }

  /**
   * Method untuk nambah pengaturan
   *
   * @param  string $id
   * @param  string $nama
   * @param  string $value
   * @return boolean
   *
   * @author Almazari <almazary@gmail.com>
   */
  public function create($id, $nama = null, $value = null)
  {
    $this->db->insert('fwk_setting', array(
      'id'    => $id,
      'nama'  => $nama,
      'value' => $value
    ));

    return true;
  }

  /**
   * Method untuk nambah field
   *
   * @param  string $id
   * @param  string $nama
   * @param  string $value
   * @return boolean
   *
   * @author Almazari <almazary@gmail.com>
   */
  public function create_field($id, $nama = null, $value = null)
  {
    $this->db->insert('field_tambahan', array(
      'id'    => $id,
      'nama'  => $nama,
      '`value`' => $value
    ));

    return true;
  }

  /**
   * Method untuk update pengaturan
   *
   * @param  string $id
   * @param  string $nama
   * @param  string $value
   * @return boolean
   *
   * @author Almazari <almazary@gmail.com>
   */
  public function update($id, $nama = null, $value = null)
  {
    $this->db->update('fwk_setting', array('nama' => $nama, 'value' => $value), array('id' => $id));
    return true;
  }

  /**
   * Method untuk update field tambahan
   *
   * @param  string $id
   * @param  string $nama
   * @param  string $value
   * @return boolean
   *
   * @author Almazari <almazary@gmail.com>
   */
  public function update_field($id, $nama = null, $value = null)
  {
    $this->db->update('field_tambahan', array('nama' => $nama, 'value' => $value), array('id' => $id));
    return true;
  }
}
