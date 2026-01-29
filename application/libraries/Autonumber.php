<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
class Autonumber
{
	function __construct() {}
	function pasien()
	{
		$startNumber = 1; /// Isi utk mulai 
		$kode_menu = "P";

		$CI =   &get_instance();
		$lastnumber = $CI->db->query("select  max(right(nip,6))as last from kln_pasien
		 ")->row_array();
		if (!empty($lastnumber['last'])) {
			// $str = (substr($lastnumber['last'], -6));
			$str = $lastnumber['last'];
			$snumber = (int)$str + 1;
		} else {
			$snumber = $startNumber;
		}
		$strnumber = sprintf("%06s", $snumber);
		$ret_format_number = $kode_menu . $strnumber;
		return $ret_format_number;
	}

	function rawat_jalan_diagnosa()
	{
		$startNumber = 1; /// Isi utk mulai 
		$kode_menu = "D";

		$CI =   &get_instance();
		$lastnumber = $CI->db->query("select  max(right(no_transaksi,9))as last from kln_diagnosa
		 ")->row_array();
		if (!empty($lastnumber['last'])) {
			// $str = (substr($lastnumber['last'], -6));
			$str = $lastnumber['last'];
			$snumber = (int)$str + 1;
		} else {
			$snumber = $startNumber;
		}
		$strnumber = sprintf("%09s", $snumber);
		$ret_format_number = $kode_menu . $strnumber;
		return $ret_format_number;
	}


	function rawat_jalan($lokasi_id, $tanggal)
	{
		$startNumber = 1; /// Isi utk mulai 
		$kode_menu = "RJ";
		$kode_lokasi = '';
		$CI =   &get_instance();
		// $yyyymm= date('Y-m', strtotime($tanggal));
		$yyyy = date('Y', strtotime($tanggal));
		$year = substr(date('Y', strtotime($tanggal)), 2, 2);
		$month = date('m', strtotime($tanggal));
		$lokasi = $CI->db->query("select kode from gbm_organisasi where id  =" . $lokasi_id . " ")->row_array();
		$kode_lokasi = $lokasi['kode'];
		if ($kode_lokasi == 'DPAM') {
			$kode_lokasi = 'MILL';
		}

		$str_cek = '/' . $kode_menu . '/' . $kode_lokasi . '/' . $month . $year;
		$sql_cek =	"select  max((SUBSTRING(no_transaksi,1,(LENGTH(no_transaksi)-LENGTH('" . $str_cek . "'))))*1) AS last
		  from kln_rawat_jalan 	where no_transaksi LIKE '%" . $str_cek . "' ;";
		$lastnumber = $CI->db->query($sql_cek)->row_array();
		if (!empty($lastnumber['last'])) {
			$str = $lastnumber['last'];
			$snumber = (int)$str + 1;
		} else {
			$snumber = $startNumber;
		}
		$strnumber = sprintf("%04s", $snumber);
		$ret_format_number = $strnumber . $str_cek;
		return $ret_format_number;
	}


	function rawat_jalan_resep($tanggal)
	{
		$startNumber = 1; /// Isi utk mulai 
		$kode_menu = "RS";
		$kode_lokasi = '';
		$CI =   &get_instance();
		// $yyyymm= date('Y-m', strtotime($tanggal));
		$yyyy = date('Y', strtotime($tanggal));
		$year = substr(date('Y', strtotime($tanggal)), 2, 2);
		$month = date('m', strtotime($tanggal));
		// $lokasi = $CI->db->query("select kode from gbm_organisasi where id  =" . $lokasi_id . " ")->row_array();
		// $kode_lokasi = $lokasi['kode'];
		// if ($kode_lokasi == 'DPAM') {
		// 	$kode_lokasi = 'MILL';
		// }

		$str_cek = '/' . $kode_menu .  '/' . $month . $year;
		$sql_cek =	"select  max((SUBSTRING(no_transaksi,1,(LENGTH(no_transaksi)-LENGTH('" . $str_cek . "'))))*1) AS last
		  from kln_resep_ht 	where no_transaksi LIKE '%" . $str_cek . "' ;";
		$lastnumber = $CI->db->query($sql_cek)->row_array();
		if (!empty($lastnumber['last'])) {
			$str = $lastnumber['last'];
			$snumber = (int)$str + 1;
		} else {
			$snumber = $startNumber;
		}
		$strnumber = sprintf("%04s", $snumber);
		$ret_format_number = $strnumber . $str_cek;
		return $ret_format_number;
	}
	function rawat_jalan_obat($tanggal)
	{
		$startNumber = 1; /// Isi utk mulai 
		$kode_menu = "PM";
		$kode_lokasi = '';
		$CI =   &get_instance();
		// $yyyymm= date('Y-m', strtotime($tanggal));
		$yyyy = date('Y', strtotime($tanggal));
		$year = substr(date('Y', strtotime($tanggal)), 2, 2);
		$month = date('m', strtotime($tanggal));
		// $lokasi = $CI->db->query("select kode from gbm_organisasi where id  =" . $lokasi_id . " ")->row_array();
		// $kode_lokasi = $lokasi['kode'];
		// if ($kode_lokasi == 'DPAM') {
		// 	$kode_lokasi = 'MILL';
		// }

		$str_cek = '/' . $kode_menu  . '/' . $month . $year;
		$sql_cek =	"select  max((SUBSTRING(no_transaksi,1,(LENGTH(no_transaksi)-LENGTH('" . $str_cek . "'))))*1) AS last
		  from kln_obat_rawat_jalan_ht 	where no_transaksi LIKE '%" . $str_cek . "' ;";
		$lastnumber = $CI->db->query($sql_cek)->row_array();
		if (!empty($lastnumber['last'])) {
			$str = $lastnumber['last'];
			$snumber = (int)$str + 1;
		} else {
			$snumber = $startNumber;
		}
		$strnumber = sprintf("%04s", $snumber);
		$ret_format_number = $strnumber . $str_cek;
		return $ret_format_number;
	}
	function rawat_jalan_invoice($lokasi_id, $tanggal)
	{
		$startNumber = 1; /// Isi utk mulai 
		$kode_menu = "INV";
		$kode_lokasi = '';
		$CI =   &get_instance();
		// $yyyymm= date('Y-m', strtotime($tanggal));
		$yyyy = date('Y', strtotime($tanggal));
		$year = substr(date('Y', strtotime($tanggal)), 2, 2);
		$month = date('m', strtotime($tanggal));
		$lokasi = $CI->db->query("select kode from gbm_organisasi where id  =" . $lokasi_id . " ")->row_array();
		$kode_lokasi = $lokasi['kode'];
		if ($kode_lokasi == 'DPAM') {
			$kode_lokasi = 'MILL';
		}

		$str_cek = '/' . $kode_menu . '/' . $kode_lokasi . '/' . $month . $year;
		$sql_cek =	"select  max((SUBSTRING(no_transaksi,1,(LENGTH(no_transaksi)-LENGTH('" . $str_cek . "'))))*1) AS last
		  from kln_invoice_rawat_jalan_ht 	where no_transaksi LIKE '%" . $str_cek . "' ;";
		$lastnumber = $CI->db->query($sql_cek)->row_array();
		if (!empty($lastnumber['last'])) {
			$str = $lastnumber['last'];
			$snumber = (int)$str + 1;
		} else {
			$snumber = $startNumber;
		}
		$strnumber = sprintf("%04s", $snumber);
		$ret_format_number = $strnumber . $str_cek;
		return $ret_format_number;
	}
	function janji_temu($lokasi_id, $tanggal)
	{
		$startNumber = 1; /// Isi utk mulai 
		$kode_menu = "AP";
		$kode_lokasi = '';
		$CI =   &get_instance();
		// $yyyymm= date('Y-m', strtotime($tanggal));
		$yyyy = date('Y', strtotime($tanggal));
		$year = substr(date('Y', strtotime($tanggal)), 2, 2);
		$month = date('m', strtotime($tanggal));
		$lokasi = $CI->db->query("select kode from gbm_organisasi where id  =" . $lokasi_id . " ")->row_array();
		$kode_lokasi = $lokasi['kode'];
		if ($kode_lokasi == 'DPAM') {
			$kode_lokasi = 'MILL';
		}

		$str_cek = '/' . $kode_menu . '/' . $kode_lokasi . '/' . $month . $year;
		$sql_cek =	"select  max((SUBSTRING(no_transaksi,1,(LENGTH(no_transaksi)-LENGTH('" . $str_cek . "'))))*1) AS last
		  from kln_janji_temu	where no_transaksi LIKE '%" . $str_cek . "' ;";
		$lastnumber = $CI->db->query($sql_cek)->row_array();
		if (!empty($lastnumber['last'])) {
			$str = $lastnumber['last'];
			$snumber = (int)$str + 1;
		} else {
			$snumber = $startNumber;
		}
		$strnumber = sprintf("%04s", $snumber);
		$ret_format_number = $strnumber . $str_cek;
		return $ret_format_number;
	}
	function gbm_supplier()
	{
		$startNumber = 1; /// Isi utk mulai 
		$kode_menu = "S";

		$CI =   &get_instance();
		$lastnumber = $CI->db->query("select  max(right(kode_supplier,4))as last from gbm_supplier
		 ")->row_array();
		if (!empty($lastnumber['last'])) {
			// $str = (substr($lastnumber['last'], -6));
			$str = $lastnumber['last'];
			$snumber = (int)$str + 1;
		} else {
			$snumber = $startNumber;
		}
		$strnumber = sprintf("%04s", $snumber);
		$ret_format_number = $kode_menu . $strnumber;
		return $ret_format_number;
	}
	function gbm_customer()
	{
		$startNumber = 1; /// Isi utk mulai 
		$kode_menu = "C";

		$CI =   &get_instance();
		$lastnumber = $CI->db->query("select  max(right(kode_customer,6))as last from gbm_customer
		 ")->row_array();
		if (!empty($lastnumber['last'])) {
			// $str = (substr($lastnumber['last'], -6));
			$str = $lastnumber['last'];
			$snumber = (int)$str + 1;
		} else {
			$snumber = $startNumber;
		}
		$strnumber = sprintf("%06s", $snumber);
		$ret_format_number = $kode_menu . $strnumber;
		return $ret_format_number;
	}

	function purchase_request($lokasi_id, $tanggal)
	{
		$startNumber = 1; /// Isi utk mulai 
		$kode_menu = "PR";
		$kode_lokasi = '';
		$CI =   &get_instance();
		// $yyyymm= date('Y-m', strtotime($tanggal));
		$yyyy = date('Y', strtotime($tanggal));
		$year = substr(date('Y', strtotime($tanggal)), 2, 2);
		$month = date('m', strtotime($tanggal));
		$lokasi = $CI->db->query("select kode from gbm_organisasi where id  =" . $lokasi_id . " ")->row_array();
		$kode_lokasi = $lokasi['kode'];
		if ($kode_lokasi == 'DPAM') {
			$kode_lokasi = 'MILL';
		}
		// $lastnumber = $CI->db->query("select  max(Left(no_pp,4))as last from prc_pp_ht
		// where DATE_FORMAT(tanggal,'%Y') ='" . $yyyy . "'  ")->row_array();
		// if (!empty($lastnumber['last'])) {
		// 	// $str = (substr($lastnumber['last'], -6));
		// 	$str = $lastnumber['last'];
		// 	$snumber = (int)$str + 1;
		// } else {
		// 	$snumber = $startNumber;
		// }
		// $strnumber = sprintf("%04s", $snumber);
		// $ret_format_number = $strnumber . '/' . $kode_menu . '/' . $kode_lokasi . '/' . $month . $year;
		// return $ret_format_number;
		$str_cek = '/' . $kode_menu . '/' . $kode_lokasi . '/' . $month . $year;
		$sql_cek =	"select  max((SUBSTRING(no_pp,1,(LENGTH(no_pp)-LENGTH('" . $str_cek . "'))))*1) AS last
		  from prc_pp_ht 	where no_pp LIKE '%" . $str_cek . "' ;";
		$lastnumber = $CI->db->query($sql_cek)->row_array();
		if (!empty($lastnumber['last'])) {
			$str = $lastnumber['last'];
			$snumber = (int)$str + 1;
		} else {
			$snumber = $startNumber;
		}
		$strnumber = sprintf("%04s", $snumber);
		$ret_format_number = $strnumber . $str_cek;
		return $ret_format_number;
	}

	function purchase_order($lokasi_id, $tanggal, $supplier_id)
	{
		$startNumber = 1; /// Isi utk mulai 
		$kode_menu = "PO";
		$kode_lokasi = '';
		$kode_supplier = '';
		$CI =   &get_instance();
		// $yyyymm= date('Y-m', strtotime($tanggal));
		$yyyy = date('Y', strtotime($tanggal));
		$year = substr(date('Y', strtotime($tanggal)), 2, 2);
		$month = date('m', strtotime($tanggal));
		$lokasi = $CI->db->query("select kode from gbm_organisasi where id  =" . $lokasi_id . " ")->row_array();
		$kode_lokasi = $lokasi['kode'];
		$supplier = $CI->db->query("select kode_supplier from gbm_supplier where id  =" . $supplier_id . " ")->row_array();
		$kode_supplier = $supplier['kode_supplier'];
		if ($kode_lokasi == 'DPAM') {
			$kode_lokasi = 'MILL';
		}
		$lastnumber = $CI->db->query("select  max(Left(no_po,4))as last from prc_po_ht
		where DATE_FORMAT(tanggal,'%Y') ='" . $yyyy . "'  ")->row_array();
		if (!empty($lastnumber['last'])) {
			// $str = (substr($lastnumber['last'], -6));
			$str = $lastnumber['last'];
			$snumber = (int)$str + 1;
		} else {
			$snumber = $startNumber;
		}
		$strnumber = sprintf("%04s", $snumber);
		$ret_format_number = $strnumber . '/' . $kode_menu . '-' . $kode_supplier . '/' . $kode_lokasi . '/' . $month . $year;
		return $ret_format_number;
	}
	function sales_order($lokasi_id, $tanggal)
	{
		$startNumber = 1; /// Isi utk mulai 
		$kode_menu = "SO";
		$kode_lokasi = '';
		$kode_customer = '';
		$CI =   &get_instance();
		// $yyyymm= date('Y-m', strtotime($tanggal));
		$yyyy = date('Y', strtotime($tanggal));
		$year = substr(date('Y', strtotime($tanggal)), 2, 2);
		$month = date('m', strtotime($tanggal));
		$lokasi = $CI->db->query("select kode from gbm_organisasi where id  =" . $lokasi_id . " ")->row_array();
		$kode_lokasi = $lokasi['kode'];

		$lastnumber = $CI->db->query("select  max(Left(no_so,4))as last from sls_so_ht
		where DATE_FORMAT(tanggal,'%Y') ='" . $yyyy . "'  ")->row_array();
		if (!empty($lastnumber['last'])) {
			// $str = (substr($lastnumber['last'], -6));
			$str = $lastnumber['last'];
			$snumber = (int)$str + 1;
		} else {
			$snumber = $startNumber;
		}
		$strnumber = sprintf("%04s", $snumber);
		// $ret_format_number = $strnumber . '/' . $kode_menu . '-' . $kode_customer . '/' . $kode_lokasi . '/' . $month . $year;
		$ret_format_number = $strnumber . '/' . $kode_menu . '/' . $kode_lokasi . '/' . $month . $year;

		return $ret_format_number;
	}
	function ttb($lokasi_id, $tanggal)
	{
		$startNumber = 1; /// Isi utk mulai 
		$kode_menu = "TTB";
		$kode_lokasi = '';
		$kode_customer = '';
		$CI =   &get_instance();
		// $yyyymm= date('Y-m', strtotime($tanggal));
		$yyyy = date('Y', strtotime($tanggal));
		$year = substr(date('Y', strtotime($tanggal)), 2, 2);
		$month = date('m', strtotime($tanggal));
		$lokasi = $CI->db->query("select kode from gbm_organisasi where id  =" . $lokasi_id . " ")->row_array();
		$kode_lokasi = $lokasi['kode'];

		$lastnumber = $CI->db->query("select  max(Left(no_ttb,4))as last from sls_ttb_ht
		where DATE_FORMAT(tanggal,'%Y') ='" . $yyyy . "'  ")->row_array();
		if (!empty($lastnumber['last'])) {
			// $str = (substr($lastnumber['last'], -6));
			$str = $lastnumber['last'];
			$snumber = (int)$str + 1;
		} else {
			$snumber = $startNumber;
		}
		$strnumber = sprintf("%04s", $snumber);
		// $ret_format_number = $strnumber . '/' . $kode_menu . '-' . $kode_customer . '/' . $kode_lokasi . '/' . $month . $year;
		$ret_format_number = $strnumber . '/' . $kode_menu . '/' . $kode_lokasi . '/' . $month . $year;

		return $ret_format_number;
	}
	function tarik_barang($lokasi_id, $tanggal)
	{
		$startNumber = 1; /// Isi utk mulai 
		$kode_menu = "TRB";
		$kode_lokasi = '';
		$kode_customer = '';
		$CI =   &get_instance();
		// $yyyymm= date('Y-m', strtotime($tanggal));
		$yyyy = date('Y', strtotime($tanggal));
		$year = substr(date('Y', strtotime($tanggal)), 2, 2);
		$month = date('m', strtotime($tanggal));
		$lokasi = $CI->db->query("select kode from gbm_organisasi where id  =" . $lokasi_id . " ")->row_array();
		$kode_lokasi = $lokasi['kode'];

		$lastnumber = $CI->db->query("select  max(Left(no_tarik_barang,4))as last from sls_tarik_barang_ht
		where DATE_FORMAT(tanggal,'%Y') ='" . $yyyy . "'  ")->row_array();
		if (!empty($lastnumber['last'])) {
			// $str = (substr($lastnumber['last'], -6));
			$str = $lastnumber['last'];
			$snumber = (int)$str + 1;
		} else {
			$snumber = $startNumber;
		}
		$strnumber = sprintf("%04s", $snumber);
		// $ret_format_number = $strnumber . '/' . $kode_menu . '-' . $kode_customer . '/' . $kode_lokasi . '/' . $month . $year;
		$ret_format_number = $strnumber . '/' . $kode_menu . '/' . $kode_lokasi . '/' . $month . $year;

		return $ret_format_number;
	}
	function lhi($lokasi_id, $tanggal)
	{
		$startNumber = 1; /// Isi utk mulai 
		$kode_menu = "LHI";
		$kode_lokasi = '';
		$CI =   &get_instance();
		// $yyyymm= date('Y-m', strtotime($tanggal));
		$yyyy = date('Y', strtotime($tanggal));
		$year = substr(date('Y', strtotime($tanggal)), 2, 2);
		$month = date('m', strtotime($tanggal));
		$lokasi = $CI->db->query("select kode from gbm_organisasi where id  =" . $lokasi_id . " ")->row_array();
		$kode_lokasi = $lokasi['kode'];

		$lastnumber = $CI->db->query("select  max(Left(no_lhi,4))as last from col_lhi_ht
		where DATE_FORMAT(tanggal,'%Y') ='" . $yyyy . "'  ")->row_array();
		if (!empty($lastnumber['last'])) {
			// $str = (substr($lastnumber['last'], -6));
			$str = $lastnumber['last'];
			$snumber = (int)$str + 1;
		} else {
			$snumber = $startNumber;
		}
		$strnumber = sprintf("%04s", $snumber);
		// $ret_format_number = $strnumber . '/' . $kode_menu . '-' . $kode_customer . '/' . $kode_lokasi . '/' . $month . $year;
		$ret_format_number = $strnumber . '/' . $kode_menu . '/' . $kode_lokasi . '/' . $month . $year;

		return $ret_format_number;
	}

	function sales_order_invoice($tanggal, $customer_id)
	{
		$startNumber = 1; /// Isi utk mulai 
		$kode_menu = "SO";
		$kode_lokasi = '';
		$kode_customer = '';
		$CI =   &get_instance();
		// $yyyymm= date('Y-m', strtotime($tanggal));
		$yyyy = date('Y', strtotime($tanggal));
		$year = substr(date('Y', strtotime($tanggal)), 2, 2);
		$month = date('m', strtotime($tanggal));
		$d = date('d', strtotime($tanggal));
		$day = sprintf("%02s", $d);
		$customer = $CI->db->query("select kode_customer from gbm_customer where id  =" . $customer_id . " ")->row_array();
		$kode_customer = $customer['kode_customer'];

		$lastnumber = $CI->db->query("select  max(Left(a.no_invoice,4))as last from sls_so_invoice a inner join sls_so_ht b
		on a.so_hd_id=b.id where b.customer_id  =" . $customer_id . " ")->row_array();
		if (!empty($lastnumber['last'])) {
			$str = $lastnumber['last'];
			$snumber = (int)$str + 1;
		} else {
			$snumber = $startNumber;
		}
		$strnumber = sprintf("%04s", $snumber);
		$ret_format_number = $strnumber . '/' . $year . '' . $month . '' . $day . '/' . $kode_customer;
		return $ret_format_number;
	}
	function jurnal_auto($lokasi_id, $tanggal, $prefix)
	{
		$startNumber = 1; /// Isi utk mulai 
		$snumber = 0;
		$kode_menu = $prefix;
		$kode_lokasi = '';
		$CI =   &get_instance();
		// $yyyymm= date('Y-m', strtotime($tanggal));
		$yyyy = date('Y', strtotime($tanggal));
		$year = substr(date('Y', strtotime($tanggal)), 2, 2);
		$month = date('m', strtotime($tanggal));
		$lokasi = $CI->db->query("select kode from gbm_organisasi where id  =" . $lokasi_id . " ")->row_array();
		$kode_lokasi = $lokasi['kode'];
		if ($kode_lokasi == 'DPAM') {
			$kode_lokasi = 'MILL';
		}
		// $lastnumber = $CI->db->query("select  max(Left(no_jurnal,6))as last from acc_jurnal_ht
		// where DATE_FORMAT(tanggal,'%Y') ='" . $yyyy . "'  ")->row_array();
		// if (!empty($lastnumber['last'])) {
		// 	// $str = (substr($lastnumber['last'], -6));
		// 	$str = $lastnumber['last'];
		// 	$snumber = (int)$str + 1;
		// } else {
		// 	$snumber = $startNumber;
		// }
		// $strnumber = sprintf("%06s", $snumber);
		// $ret_format_number = $strnumber . '/' . $kode_menu . '/' . $kode_lokasi . '/' . $month . $year;
		// return $ret_format_number;
		$str_cek = '/' . $kode_menu . '/' . $kode_lokasi . '/' . $month . $year;
		$sql_cek =	"select  max((SUBSTRING(no_jurnal,1,(LENGTH(no_jurnal)-LENGTH('" . $str_cek . "'))))*1) AS last
		  from acc_jurnal_ht 	where no_jurnal LIKE '%" . $str_cek . "' ;";
		$lastnumber = $CI->db->query($sql_cek)->row_array();
		if (!empty($lastnumber['last'])) {
			$str = $lastnumber['last'];
			$snumber = (int)$str + 1;
		} else {
			$snumber = $startNumber;
		}
		$strnumber = sprintf("%06s", $snumber);
		$ret_format_number = $strnumber . $str_cek;
		return $ret_format_number;
	}
	function jurnal_upah_auto($lokasi_id, $tanggal, $prefix)
	{
		$startNumber = 1; /// Isi utk mulai 
		$snumber = 0;
		$kode_menu = $prefix;
		$kode_lokasi = '';
		$CI =   &get_instance();
		// $yyyymm= date('Y-m', strtotime($tanggal));
		$yyyy = date('Y', strtotime($tanggal));
		$year = substr(date('Y', strtotime($tanggal)), 2, 2);
		$month = date('m', strtotime($tanggal));
		$lokasi = $CI->db->query("select kode from gbm_organisasi where id  =" . $lokasi_id . " ")->row_array();
		$kode_lokasi = $lokasi['kode'];
		if ($kode_lokasi == 'DPAM') {
			$kode_lokasi = 'MILL';
		}
		// $lastnumber = $CI->db->query("select  max(Left(no_jurnal,6))as last from acc_jurnal_upah_ht
		// where DATE_FORMAT(tanggal,'%Y') ='" . $yyyy . "'  ")->row_array();
		// if (!empty($lastnumber['last'])) {
		// 	// $str = (substr($lastnumber['last'], -6));
		// 	$str = $lastnumber['last'];
		// 	$snumber = (int)$str + 1;
		// } else {
		// 	$snumber = $startNumber;
		// }
		// $strnumber = sprintf("%06s", $snumber);
		// $ret_format_number = $strnumber . '/' . $kode_menu . '/' . $kode_lokasi . '/' . $month . $year;
		// return $ret_format_number;
		$str_cek = '/' . $kode_menu . '/' . $kode_lokasi . '/' . $month . $year;
		$sql_cek =	"select  max((SUBSTRING(no_jurnal,1,(LENGTH(no_jurnal)-LENGTH('" . $str_cek . "'))))*1) AS last
		  from acc_jurnal_upah_ht 	where no_jurnal LIKE '%" . $str_cek . "' ;";
		$lastnumber = $CI->db->query($sql_cek)->row_array();
		if (!empty($lastnumber['last'])) {
			$str = $lastnumber['last'];
			$snumber = (int)$str + 1;
		} else {
			$snumber = $startNumber;
		}
		$strnumber = sprintf("%06s", $snumber);
		$ret_format_number = $strnumber . $str_cek;
		return $ret_format_number;
	}
	function acc_jurnal_entry($lokasi_id, $tanggal)
	{
		$startNumber = 1; /// Isi utk mulai 
		$kode_menu = "JM";
		$kode_lokasi = '';
		$CI =   &get_instance();
		// $yyyymm= date('Y-m', strtotime($tanggal));
		$yyyy = date('Y', strtotime($tanggal));
		$year = substr(date('Y', strtotime($tanggal)), 2, 2);
		$month = date('m', strtotime($tanggal));
		$lokasi = $CI->db->query("select kode from gbm_organisasi where id  =" . $lokasi_id . " ")->row_array();
		$kode_lokasi = $lokasi['kode'];
		if ($kode_lokasi == 'DPAM') {
			$kode_lokasi = 'MILL';
		}
		// $lastnumber = $CI->db->query("select  max(Left(no_jurnal,4))as last from acc_jurnal_entry_ht
		// where DATE_FORMAT(tanggal,'%Y') ='" . $yyyy . "'  ")->row_array();
		// if (!empty($lastnumber['last'])) {
		// 	// $str = (substr($lastnumber['last'], -6));
		// 	$str = $lastnumber['last'];
		// 	$snumber = (int)$str + 1;
		// } else {
		// 	$snumber = $startNumber;
		// }
		// $strnumber = sprintf("%04s", $snumber);
		// $ret_format_number = $strnumber . '/' . $kode_menu . '/' . $kode_lokasi . '/' . $month . $year;
		// return $ret_format_number;
		$str_cek = '/' . $kode_menu . '/' . $kode_lokasi . '/' . $month . $year;
		$sql_cek =	"select  max((SUBSTRING(no_jurnal,1,(LENGTH(no_jurnal)-LENGTH('" . $str_cek . "'))))*1) AS last
		  from acc_jurnal_entry_ht 	where no_jurnal LIKE '%" . $str_cek . "' ;";
		$lastnumber = $CI->db->query($sql_cek)->row_array();
		if (!empty($lastnumber['last'])) {
			$str = $lastnumber['last'];
			$snumber = (int)$str + 1;
		} else {
			$snumber = $startNumber;
		}
		$strnumber = sprintf("%04s", $snumber);
		$ret_format_number = $strnumber . $str_cek;
		return $ret_format_number;
	}
	function acc_kasbank($lokasi_id, $tanggal)
	{
		$startNumber = 1; /// Isi utk mulai 
		$kode_menu = "KB";
		$kode_lokasi = '';
		$CI =   &get_instance();
		// $yyyymm= date('Y-m', strtotime($tanggal));
		$yyyy = date('Y', strtotime($tanggal));
		$year = substr(date('Y', strtotime($tanggal)), 2, 2);
		$month = date('m', strtotime($tanggal));
		$lokasi = $CI->db->query("select kode from gbm_organisasi where id  =" . $lokasi_id . " ")->row_array();
		$kode_lokasi = $lokasi['kode'];
		if ($kode_lokasi == 'DPAM') {
			$kode_lokasi = 'MILL';
		}
		// $lastnumber = $CI->db->query("select  max(Left(no_transaksi,4))as last from acc_kasbank_ht
		// where DATE_FORMAT(tanggal,'%Y') ='" . $yyyy . "'  ")->row_array();
		// if (!empty($lastnumber['last'])) {
		// 	// $str = (substr($lastnumber['last'], -6));
		// 	$str = $lastnumber['last'];
		// 	$snumber = (int)$str + 1;
		// } else {
		// 	$snumber = $startNumber;
		// }
		// $strnumber = sprintf("%04s", $snumber);
		// $ret_format_number = $strnumber . '/' . $kode_menu . '/' . $kode_lokasi . '/' . $month . $year;
		// return $ret_format_number;
		$str_cek = '/' . $kode_menu . '/' . $kode_lokasi . '/' . $month . $year;
		$sql_cek =	"select  max((SUBSTRING(no_transaksi,1,(LENGTH(no_transaksi)-LENGTH('" . $str_cek . "'))))*1) AS last
		  from acc_kasbank_ht 	where no_transaksi LIKE '%" . $str_cek . "' ;";
		$lastnumber = $CI->db->query($sql_cek)->row_array();
		if (!empty($lastnumber['last'])) {
			$str = $lastnumber['last'];
			$snumber = (int)$str + 1;
		} else {
			$snumber = $startNumber;
		}
		$strnumber = sprintf("%04s", $snumber);
		$ret_format_number = $strnumber . $str_cek;
		return $ret_format_number;
	}
	function acc_permintaan_dana($tanggal)
	{
		$startNumber = 1; /// Isi utk mulai 
		$kode_menu = "PDO";
		$kode_lokasi = '';
		$CI =   &get_instance();
		// $yyyymm= date('Y-m', strtotime($tanggal));
		$yyyy = date('Y', strtotime($tanggal));
		$year = substr(date('Y', strtotime($tanggal)), 2, 2);
		$month = date('m', strtotime($tanggal));
		// $lokasi = $CI->db->query("select kode from gbm_organisasi where id  =" . $lokasi_id . " ")->row_array();
		// $kode_lokasi = $lokasi['kode'];
		if ($kode_lokasi == 'DPAM') {
			$kode_lokasi = 'MILL';
		}
		// $lastnumber = $CI->db->query("select  max(Left(no_transaksi,4))as last from acc_permintaan_dana
		// where DATE_FORMAT(tanggal,'%Y') ='" . $yyyy . "'  ")->row_array();
		// if (!empty($lastnumber['last'])) {
		// 	// $str = (substr($lastnumber['last'], -6));
		// 	$str = $lastnumber['last'];
		// 	$snumber = (int)$str + 1;
		// } else {
		// 	$snumber = $startNumber;
		// }
		// $strnumber = sprintf("%04s", $snumber);
		// $ret_format_number = $strnumber . '/' . $kode_menu . '/' . $kode_lokasi . '/' . $month . $year;
		// return $ret_format_number;
		// $str_cek = '/' . $kode_menu . '/' . $kode_lokasi . '/' . $month . $year;
		$str_cek = '/' . $kode_menu . '/' . $month . $year;
		$sql_cek =	"select  max((SUBSTRING(no_transaksi,1,(LENGTH(no_transaksi)-LENGTH('" . $str_cek . "'))))*1) AS last
		  from acc_permintaan_dana 	where DATE_FORMAT(tanggal,'%Y') ='" . $yyyy . "' ;";
		$lastnumber = $CI->db->query($sql_cek)->row_array();
		if (!empty($lastnumber['last'])) {
			$str = $lastnumber['last'];
			$snumber = (int)$str + 1;
		} else {
			$snumber = $startNumber;
		}
		$strnumber = sprintf("%04s", $snumber);
		$ret_format_number = $strnumber . $str_cek;
		return $ret_format_number;
	}
	function acc_uangmuka($lokasi_id, $tanggal)
	{
		$startNumber = 1; /// Isi utk mulai 
		$kode_menu = "UM";
		$kode_lokasi = '';
		$CI =   &get_instance();
		// $yyyymm= date('Y-m', strtotime($tanggal));
		$yyyy = date('Y', strtotime($tanggal));
		$year = substr(date('Y', strtotime($tanggal)), 2, 2);
		$month = date('m', strtotime($tanggal));
		$lokasi = $CI->db->query("select kode from gbm_organisasi where id  =" . $lokasi_id . " ")->row_array();
		$kode_lokasi = $lokasi['kode'];
		if ($kode_lokasi == 'DPAM') {
			$kode_lokasi = 'MILL';
		}
		// $lastnumber = $CI->db->query("select  max(Left(no_transaksi,4))as last from acc_uang_muka
		// where DATE_FORMAT(tanggal,'%Y') ='" . $yyyy . "'  ")->row_array();
		// if (!empty($lastnumber['last'])) {
		// 	// $str = (substr($lastnumber['last'], -6));
		// 	$str = $lastnumber['last'];
		// 	$snumber = (int)$str + 1;
		// } else {
		// 	$snumber = $startNumber;
		// }
		// $strnumber = sprintf("%04s", $snumber);
		// $ret_format_number = $strnumber . '/' . $kode_menu . '/' . $kode_lokasi . '/' . $month . $year;
		// return $ret_format_number;
		$str_cek = '/' . $kode_menu . '/' . $kode_lokasi . '/' . $month . $year;
		$sql_cek =	"select  max((SUBSTRING(no_transaksi,1,(LENGTH(no_transaksi)-LENGTH('" . $str_cek . "'))))*1) AS last
		  from acc_uang_muka 	where no_transaksi LIKE '%" . $str_cek . "' ;";
		$lastnumber = $CI->db->query($sql_cek)->row_array();
		if (!empty($lastnumber['last'])) {
			$str = $lastnumber['last'];
			$snumber = (int)$str + 1;
		} else {
			$snumber = $startNumber;
		}
		$strnumber = sprintf("%04s", $snumber);
		$ret_format_number = $strnumber . $str_cek;
		return $ret_format_number;
	}
	function acc_uangmuka_realisasi($lokasi_id, $tanggal)
	{
		$startNumber = 1; /// Isi utk mulai 
		$kode_menu = "RS";
		$kode_lokasi = '';
		$CI =   &get_instance();
		// $yyyymm= date('Y-m', strtotime($tanggal));
		$yyyy = date('Y', strtotime($tanggal));
		$year = substr(date('Y', strtotime($tanggal)), 2, 2);
		$month = date('m', strtotime($tanggal));
		$lokasi = $CI->db->query("select kode from gbm_organisasi where id  =" . $lokasi_id . " ")->row_array();
		$kode_lokasi = $lokasi['kode'];
		if ($kode_lokasi == 'DPAM') {
			$kode_lokasi = 'MILL';
		}
		// $lastnumber = $CI->db->query("select  max(Left(no_transaksi,4))as last from acc_uang_muka_realisasi
		// where DATE_FORMAT(tanggal,'%Y') ='" . $yyyy . "'  ")->row_array();
		// if (!empty($lastnumber['last'])) {
		// 	// $str = (substr($lastnumber['last'], -6));
		// 	$str = $lastnumber['last'];
		// 	$snumber = (int)$str + 1;
		// } else {
		// 	$snumber = $startNumber;
		// }
		// $strnumber = sprintf("%04s", $snumber);
		// $ret_format_number = $strnumber . '/' . $kode_menu . '/' . $kode_lokasi . '/' . $month . $year;
		// return $ret_format_number;
		$str_cek = '/' . $kode_menu . '/' . $kode_lokasi . '/' . $month . $year;
		$sql_cek =	"select  max((SUBSTRING(no_transaksi,1,(LENGTH(no_transaksi)-LENGTH('" . $str_cek . "'))))*1) AS last
		  from acc_uang_muka_realisasi 	where no_transaksi LIKE '%" . $str_cek . "' ;";
		$lastnumber = $CI->db->query($sql_cek)->row_array();
		if (!empty($lastnumber['last'])) {
			$str = $lastnumber['last'];
			$snumber = (int)$str + 1;
		} else {
			$snumber = $startNumber;
		}
		$strnumber = sprintf("%04s", $snumber);
		$ret_format_number = $strnumber . $str_cek;
		return $ret_format_number;
	}
	function acc_ap_invoice($lokasi_id, $tanggal)
	{
		$startNumber = 1; /// Isi utk mulai 
		$kode_menu = "AP";
		$kode_lokasi = '';
		$CI =   &get_instance();
		// $yyyymm= date('Y-m', strtotime($tanggal));
		$yyyy = date('Y', strtotime($tanggal));
		$year = substr(date('Y', strtotime($tanggal)), 2, 2);
		$month = date('m', strtotime($tanggal));
		$lokasi = $CI->db->query("select kode from gbm_organisasi where id  ='" . $lokasi_id . " '")->row_array();
		$kode_lokasi = $lokasi['kode'];
		if ($kode_lokasi == 'DPAM') {
			$kode_lokasi = 'MILL';
		}
		// $lastnumber = $CI->db->query("select  max(Left(no_invoice,4))as last from acc_ap_invoice_ht
		// where DATE_FORMAT(tanggal,'%Y') ='" . $yyyy . "'  ")->row_array();
		// if (!empty($lastnumber['last'])) {
		// 	// $str = (substr($lastnumber['last'], -6));
		// 	$str = $lastnumber['last'];
		// 	$snumber = (int)$str + 1;
		// } else {
		// 	$snumber = $startNumber;
		// }
		// $strnumber = sprintf("%04s", $snumber);
		// $ret_format_number = $strnumber . '/' . $kode_menu . '/' . $kode_lokasi . '/' . $month . $year;
		// return $ret_format_number;
		$str_cek = '/' . $kode_menu . '/' . $kode_lokasi . '/' . $month . $year;
		$sql_cek =	"select  max((SUBSTRING(no_invoice,1,(LENGTH(no_invoice)-LENGTH('" . $str_cek . "'))))*1) AS last
		  from acc_ap_invoice_ht 	where no_invoice LIKE '%" . $str_cek . "' ;";
		$lastnumber = $CI->db->query($sql_cek)->row_array();
		if (!empty($lastnumber['last'])) {
			$str = $lastnumber['last'];
			$snumber = (int)$str + 1;
		} else {
			$snumber = $startNumber;
		}
		$strnumber = sprintf("%04s", $snumber);
		$ret_format_number = $strnumber . $str_cek;
		return $ret_format_number;
	}
	function acc_ar_invoice($lokasi_id, $tanggal)
	{
		$startNumber = 1; /// Isi utk mulai 
		$kode_menu = "AR";
		$kode_lokasi = '';
		$CI =   &get_instance();
		// $yyyymm= date('Y-m', strtotime($tanggal));
		$yyyy = date('Y', strtotime($tanggal));
		$year = substr(date('Y', strtotime($tanggal)), 2, 2);
		$month = date('m', strtotime($tanggal));
		$lokasi = $CI->db->query("select kode from gbm_organisasi where id  ='" . $lokasi_id . " '")->row_array();
		$kode_lokasi = $lokasi['kode'];

		$str_cek = '/' . $kode_menu . '/' . $kode_lokasi . '/' . $month . $year;
		$sql_cek =	"select  max((SUBSTRING(no_invoice,1,(LENGTH(no_invoice)-LENGTH('" . $str_cek . "'))))*1) AS last
		  from acc_ar_invoice_ht 	where no_invoice LIKE '%" . $str_cek . "' ;";
		$lastnumber = $CI->db->query($sql_cek)->row_array();
		if (!empty($lastnumber['last'])) {
			$str = $lastnumber['last'];
			$snumber = (int)$str + 1;
		} else {
			$snumber = $startNumber;
		}
		$strnumber = sprintf("%04s", $snumber);
		$ret_format_number = $strnumber . $str_cek;
		return $ret_format_number;
	}

	function acc_tbs_invoice($lokasi_id, $tanggal, $supplier_id)
	{
		$startNumber = 1; /// Isi utk mulai 
		// $kode_menu = "INVTBS";
		$kode_menu = "INV";
		$kode_lokasi = '';
		$CI =   &get_instance();
		// $yyyymm= date('Y-m', strtotime($tanggal));
		$yyyy = date('Y', strtotime($tanggal));
		$year = substr(date('Y', strtotime($tanggal)), 2, 2);
		$month = date('m', strtotime($tanggal));
		$lokasi = $CI->db->query("select kode from gbm_organisasi where id  =" . $lokasi_id . " ")->row_array();
		// $kode_lokasi = $lokasi['kode'];
		// if ($kode_lokasi == 'DPAM') {
		// 	$kode_lokasi = 'MILL';
		// }
		// $str_cek = '/' . $kode_menu . '/' . $kode_lokasi . '/' . $month . $year;
		// $sql_cek =	"select  max((SUBSTRING(no_invoice,1,(LENGTH(no_invoice)-LENGTH('" . $str_cek . "'))))*1) AS last
		//   from acc_tbs_invoice_ht 	where no_invoice LIKE '%".$str_cek."' ;";
		$supplier = $CI->db->query("select kode_supplier from gbm_supplier where id  =" . $supplier_id . " ")->row_array();
		$kode_supplier = $supplier['kode_supplier'];

		// $str_cek = '/' . $kode_menu . '/' . $kode_lokasi . '/'.$kode_supplier.'/' . $month . $year;
		$str_cek = '/' . $kode_menu . '/' . $kode_supplier . '/' . $month . $year;
		$str_cek2 = '/' . $kode_menu . '/' . $kode_supplier . '/';
		$sql_cek =	"select  max((SUBSTRING(no_invoice,1,(LENGTH(no_invoice)-LENGTH('" . $str_cek . "'))))*1) AS last
		  from acc_tbs_invoice_ht 	where no_invoice LIKE '%" . $str_cek2 . "%' and DATE_FORMAT(tanggal,'%Y') ='" . $yyyy . "' ;";

		$lastnumber = $CI->db->query($sql_cek)->row_array();
		if (!empty($lastnumber['last'])) {
			$str = $lastnumber['last'];
			$snumber = (int)$str + 1;
		} else {
			$snumber = $startNumber;
		}
		$strnumber = sprintf("%04s", $snumber);
		$ret_format_number = $strnumber . $str_cek;
		return $ret_format_number;
	}
	function acc_angkut_invoice($lokasi_id, $tanggal, $supplier_id)
	{
		$startNumber = 1; /// Isi utk mulai 
		// $kode_menu = "INVTBS";
		$kode_menu = "INV";
		$kode_lokasi = '';
		$CI =   &get_instance();
		// $yyyymm= date('Y-m', strtotime($tanggal));
		$yyyy = date('Y', strtotime($tanggal));
		$year = substr(date('Y', strtotime($tanggal)), 2, 2);
		$month = date('m', strtotime($tanggal));
		$lokasi = $CI->db->query("select kode from gbm_organisasi where id  =" . $lokasi_id . " ")->row_array();
		// $kode_lokasi = $lokasi['kode'];
		// if ($kode_lokasi == 'DPAM') {
		// 	$kode_lokasi = 'MILL';
		// }
		// $str_cek = '/' . $kode_menu . '/' . $kode_lokasi . '/' . $month . $year;
		// $sql_cek =	"select  max((SUBSTRING(no_invoice,1,(LENGTH(no_invoice)-LENGTH('" . $str_cek . "'))))*1) AS last
		//   from acc_tbs_invoice_ht 	where no_invoice LIKE '%".$str_cek."' ;";
		$supplier = $CI->db->query("select kode_supplier from gbm_supplier where id  =" . $supplier_id . " ")->row_array();
		$kode_supplier = $supplier['kode_supplier'];
		if ($kode_lokasi == 'DPAM') {
			$kode_lokasi = 'MILL';
		}
		// $str_cek = '/' . $kode_menu . '/' . $kode_lokasi . '/'.$kode_supplier.'/' . $month . $year;
		$str_cek = '/' . $kode_menu . '/' . $kode_supplier . '/' . $month . $year;
		$str_cek2 = '/' . $kode_menu . '/' . $kode_supplier . '/';
		$sql_cek =	"select  max((SUBSTRING(no_invoice,1,(LENGTH(no_invoice)-LENGTH('" . $str_cek . "'))))*1) AS last
		  from acc_angkut_invoice_ht 	where no_invoice LIKE '%" . $str_cek2 . "%' and DATE_FORMAT(tanggal,'%Y') ='" . $yyyy . "' ;";

		$lastnumber = $CI->db->query($sql_cek)->row_array();
		if (!empty($lastnumber['last'])) {
			$str = $lastnumber['last'];
			$snumber = (int)$str + 1;
		} else {
			$snumber = $startNumber;
		}
		$strnumber = sprintf("%04s", $snumber);
		$ret_format_number = $strnumber . $str_cek;
		return $ret_format_number;
	}


	function inv_adj($lokasi_id, $tanggal)
	{
		$startNumber = 1; /// Isi utk mulai 
		$kode_menu = "ADJ";
		$kode_lokasi = '';
		$CI =   &get_instance();
		// $yyyymm= date('Y-m', strtotime($tanggal));
		$yyyy = date('Y', strtotime($tanggal));
		$year = substr(date('Y', strtotime($tanggal)), 2, 2);
		$month = date('m', strtotime($tanggal));
		$lokasi = $CI->db->query("select kode from gbm_organisasi where id  =" . $lokasi_id . " ")->row_array();
		$kode_lokasi = $lokasi['kode'];
		if ($kode_lokasi == 'DPAM') {
			$kode_lokasi = 'MILL';
		}
		// $lastnumber = $CI->db->query("select  max(Left(no_transaksi,4))as last from inv_adj_ht
		// where DATE_FORMAT(tanggal,'%Y') ='" . $yyyy . "'  ")->row_array();
		// if (!empty($lastnumber['last'])) {
		// 	// $str = (substr($lastnumber['last'], -6));
		// 	$str = $lastnumber['last'];
		// 	$snumber = (int)$str + 1;
		// } else {
		// 	$snumber = $startNumber;
		// }
		// $strnumber = sprintf("%04s", $snumber);
		// $ret_format_number = $strnumber . '/' . $kode_menu . '/' . $kode_lokasi . '/' . $month . $year;
		// return $ret_format_number;
		$str_cek = '/' . $kode_menu . '/' . $kode_lokasi . '/' . $month . $year;
		$sql_cek =	"select  max((SUBSTRING(no_transaksi,1,(LENGTH(no_transaksi)-LENGTH('" . $str_cek . "'))))*1) AS last
		  from inv_adj_ht 	where no_transaksi LIKE '%" . $str_cek . "' ;";
		$lastnumber = $CI->db->query($sql_cek)->row_array();
		if (!empty($lastnumber['last'])) {
			$str = $lastnumber['last'];
			$snumber = (int)$str + 1;
		} else {
			$snumber = $startNumber;
		}
		$strnumber = sprintf("%04s", $snumber);
		$ret_format_number = $strnumber . $str_cek;
		return $ret_format_number;
	}



	function inv_penerimaan_tanpa_po($lokasi_id, $tanggal)
	{
		$startNumber = 1; /// Isi utk mulai 
		$kode_menu = "RC";
		$kode_lokasi = '';
		$CI =   &get_instance();
		// $yyyymm= date('Y-m', strtotime($tanggal));
		$yyyy = date('Y', strtotime($tanggal));
		$year = substr(date('Y', strtotime($tanggal)), 2, 2);
		$month = date('m', strtotime($tanggal));
		$lokasi = $CI->db->query("select kode from gbm_organisasi where id  =" . $lokasi_id . " ")->row_array();
		$kode_lokasi = $lokasi['kode'];
		if ($kode_lokasi == 'DPAM') {
			$kode_lokasi = 'MILL';
		}
		// $lastnumber = $CI->db->query("select  max(Left(no_transaksi,4))as last from inv_penerimaan_tanpa_po_ht
		// where DATE_FORMAT(tanggal,'%Y') ='" . $yyyy . "'  ")->row_array();
		// if (!empty($lastnumber['last'])) {
		// 	// $str = (substr($lastnumber['last'], -6));
		// 	$str = $lastnumber['last'];
		// 	$snumber = (int)$str + 1;
		// } else {
		// 	$snumber = $startNumber;
		// }
		// $strnumber = sprintf("%04s", $snumber);
		// $ret_format_number = $strnumber . '/' . $kode_menu . '/' . $kode_lokasi . '/' . $month . $year;
		// return $ret_format_number;
		$str_cek = '/' . $kode_menu . '/' . $kode_lokasi . '/' . $month . $year;
		$sql_cek =	"select  max((SUBSTRING(no_transaksi,1,(LENGTH(no_transaksi)-LENGTH('" . $str_cek . "'))))*1) AS last
		  from inv_penerimaan_tanpa_po_ht 	where no_transaksi LIKE '%" . $str_cek . "' ;";
		$lastnumber = $CI->db->query($sql_cek)->row_array();
		if (!empty($lastnumber['last'])) {
			$str = $lastnumber['last'];
			$snumber = (int)$str + 1;
		} else {
			$snumber = $startNumber;
		}
		$strnumber = sprintf("%04s", $snumber);
		$ret_format_number = $strnumber . $str_cek;
		return $ret_format_number;
	}

	function inv_penerimaan_po($lokasi_id, $tanggal)
	{
		$startNumber = 1; /// Isi utk mulai 
		$table = "inv_penerimaan_po_ht";
		$kode_menu = "RC";
		$kode_lokasi = '';
		$CI =   &get_instance();
		// $yyyymm= date('Y-m', strtotime($tanggal));
		$yyyy = date('Y', strtotime($tanggal));
		$year = substr(date('Y', strtotime($tanggal)), 2, 2);
		$month = date('m', strtotime($tanggal));
		$lokasi = $CI->db->query("select kode from gbm_organisasi where id  =" . $lokasi_id . " ")->row_array();
		$kode_lokasi = $lokasi['kode'];
		if ($kode_lokasi == 'DPAM') {
			$kode_lokasi = 'MILL';
		}
		// $lastnumber = $CI->db->query("select  max(Left(no_transaksi,4))as last from " . $table . "
		// where DATE_FORMAT(tanggal,'%Y') ='" . $yyyy . "'  ")->row_array();
		// if (!empty($lastnumber['last'])) {
		// 	$str = $lastnumber['last'];
		// 	$snumber = (int)$str + 1;
		// } else {
		// 	$snumber = $startNumber;
		// }
		// $strnumber = sprintf("%04s", $snumber);
		// $ret_format_number = $strnumber . '/' . $kode_menu . '/' . $kode_lokasi . '/' . $month . $year;
		// return $ret_format_number;
		$str_cek = '/' . $kode_menu . '/' . $kode_lokasi . '/' . $month . $year;
		$sql_cek =	"select  max((SUBSTRING(no_transaksi,1,(LENGTH(no_transaksi)-LENGTH('" . $str_cek . "'))))*1) AS last
		  from " . $table . " 	where no_transaksi LIKE '%" . $str_cek . "' ;";
		$lastnumber = $CI->db->query($sql_cek)->row_array();
		if (!empty($lastnumber['last'])) {
			$str = $lastnumber['last'];
			$snumber = (int)$str + 1;
		} else {
			$snumber = $startNumber;
		}
		$strnumber = sprintf("%04s", $snumber);
		$ret_format_number = $strnumber . $str_cek;
		return $ret_format_number;
	}
	function inv_pengiriman_so($lokasi_id, $tanggal)
	{
		$startNumber = 1; /// Isi utk mulai 
		$table = "inv_pengiriman_so_ht";
		$kode_menu = "SN";
		$kode_lokasi = '';
		$CI =   &get_instance();
		// $yyyymm= date('Y-m', strtotime($tanggal));
		$yyyy = date('Y', strtotime($tanggal));
		$year = substr(date('Y', strtotime($tanggal)), 2, 2);
		$month = date('m', strtotime($tanggal));
		$lokasi = $CI->db->query("select kode from gbm_organisasi where id  =" . $lokasi_id . " ")->row_array();
		$kode_lokasi = $lokasi['kode'];
		if ($kode_lokasi == 'DPAM') {
			$kode_lokasi = 'MILL';
		}
		// $lastnumber = $CI->db->query("select  max(Left(no_transaksi,4))as last from " . $table . "
		// where DATE_FORMAT(tanggal,'%Y') ='" . $yyyy . "'  ")->row_array();
		// if (!empty($lastnumber['last'])) {
		// 	$str = $lastnumber['last'];
		// 	$snumber = (int)$str + 1;
		// } else {
		// 	$snumber = $startNumber;
		// }
		// $strnumber = sprintf("%04s", $snumber);
		// $ret_format_number = $strnumber . '/' . $kode_menu . '/' . $kode_lokasi . '/' . $month . $year;
		// return $ret_format_number;
		$str_cek = '/' . $kode_menu . '/' . $kode_lokasi . '/' . $month . $year;
		$sql_cek =	"select  max((SUBSTRING(no_transaksi,1,(LENGTH(no_transaksi)-LENGTH('" . $str_cek . "'))))*1) AS last
		  from " . $table . " 	where no_transaksi LIKE '%" . $str_cek . "' ;";
		$lastnumber = $CI->db->query($sql_cek)->row_array();
		if (!empty($lastnumber['last'])) {
			$str = $lastnumber['last'];
			$snumber = (int)$str + 1;
		} else {
			$snumber = $startNumber;
		}
		$strnumber = sprintf("%04s", $snumber);
		$ret_format_number = $strnumber . $str_cek;
		return $ret_format_number;
	}

	function inv_permintaan_barang($lokasi_id, $tanggal)
	{
		$startNumber = 1; /// Isi utk mulai 
		$table = "inv_permintaan_ht";
		$kode_menu = "REQ";
		$kode_lokasi = '';
		$CI =   &get_instance();
		// $yyyymm= date('Y-m', strtotime($tanggal));
		$yyyy = date('Y', strtotime($tanggal));
		$year = substr(date('Y', strtotime($tanggal)), 2, 2);
		$month = date('m', strtotime($tanggal));
		$lokasi = $CI->db->query("select kode from gbm_organisasi where id  =" . $lokasi_id . " ")->row_array();
		$kode_lokasi = $lokasi['kode'];
		if ($kode_lokasi == 'DPAM') {
			$kode_lokasi = 'MILL';
		}
		// $lastnumber = $CI->db->query("select  max(Left(no_transaksi,4))as last from " . $table . "
		// where DATE_FORMAT(tanggal,'%Y') ='" . $yyyy . "'  ")->row_array();
		// if (!empty($lastnumber['last'])) {
		// 	$str = $lastnumber['last'];
		// 	$snumber = (int)$str + 1;
		// } else {
		// 	$snumber = $startNumber;
		// }
		// $strnumber = sprintf("%04s", $snumber);
		// $ret_format_number = $strnumber . '/' . $kode_menu . '/' . $kode_lokasi . '/' . $month . $year;
		// return $ret_format_number;
		$str_cek = '/' . $kode_menu . '/' . $kode_lokasi . '/' . $month . $year;
		$sql_cek =	"select  max((SUBSTRING(no_transaksi,1,(LENGTH(no_transaksi)-LENGTH('" . $str_cek . "'))))*1) AS last
		  from " . $table . " 	where no_transaksi LIKE '%" . $str_cek . "' ;";
		$lastnumber = $CI->db->query($sql_cek)->row_array();
		if (!empty($lastnumber['last'])) {
			$str = $lastnumber['last'];
			$snumber = (int)$str + 1;
		} else {
			$snumber = $startNumber;
		}
		$strnumber = sprintf("%04s", $snumber);
		$ret_format_number = $strnumber . $str_cek;
		return $ret_format_number;
	}
	function inv_pemakaian_barang($lokasi_id, $tanggal)
	{
		$startNumber = 1; /// Isi utk mulai 
		$table = "inv_pemakaian_ht";
		$kode_menu = "PMK";
		$kode_lokasi = '';
		$CI =   &get_instance();
		// $yyyymm= date('Y-m', strtotime($tanggal));
		$yyyy = date('Y', strtotime($tanggal));
		$year = substr(date('Y', strtotime($tanggal)), 2, 2);
		$month = date('m', strtotime($tanggal));
		$lokasi = $CI->db->query("select kode from gbm_organisasi where id  =" . $lokasi_id . " ")->row_array();
		$kode_lokasi = $lokasi['kode'];
		if ($kode_lokasi == 'DPAM') {
			$kode_lokasi = 'MILL';
		}
		$str_cek = '/' . $kode_menu . '/' . $kode_lokasi . '/' . $month . $year;
		$sql_cek =	"select  max((SUBSTRING(no_transaksi,1,(LENGTH(no_transaksi)-LENGTH('" . $str_cek . "'))))*1) AS last
		  from " . $table . " 	where no_transaksi LIKE '%" . $str_cek . "' ;";
		$lastnumber = $CI->db->query($sql_cek)->row_array();
		// $lastnumber = $CI->db->query("select  max(Left(no_transaksi,4))as last from ".$table."
		// where DATE_FORMAT(tanggal,'%Y') ='" . $yyyy . "'  ")->row_array();
		if (!empty($lastnumber['last'])) {
			$str = $lastnumber['last'];
			$snumber = (int)$str + 1;
		} else {
			$snumber = $startNumber;
		}
		// $strnumber = sprintf("%04s", $snumber);
		$strnumber = sprintf("%06s", $snumber);
		// $ret_format_number = $strnumber . '/' . $kode_menu . '/' . $kode_lokasi . '/' . $month . $year;
		$ret_format_number = $strnumber . $str_cek;
		return $ret_format_number;
	}
	function inv_retur_pemakaian_barang($lokasi_id, $tanggal)
	{
		$startNumber = 1; /// Isi utk mulai 
		$table = "inv_retur_pemakaian_ht";
		$kode_menu = "RPB";
		$kode_lokasi = '';
		$CI =   &get_instance();
		// $yyyymm= date('Y-m', strtotime($tanggal));
		$yyyy = date('Y', strtotime($tanggal));
		$year = substr(date('Y', strtotime($tanggal)), 2, 2);
		$month = date('m', strtotime($tanggal));
		$lokasi = $CI->db->query("select kode from gbm_organisasi where id  =" . $lokasi_id . " ")->row_array();
		$kode_lokasi = $lokasi['kode'];
		if ($kode_lokasi == 'DPAM') {
			$kode_lokasi = 'MILL';
		}
		$str_cek = '/' . $kode_menu . '/' . $kode_lokasi . '/' . $month . $year;
		$sql_cek =	"select  max((SUBSTRING(no_transaksi,1,(LENGTH(no_transaksi)-LENGTH('" . $str_cek . "'))))*1) AS last
		  from " . $table . " 	where no_transaksi LIKE '%" . $str_cek . "' ;";
		$lastnumber = $CI->db->query($sql_cek)->row_array();
		if (!empty($lastnumber['last'])) {
			$str = $lastnumber['last'];
			$snumber = (int)$str + 1;
		} else {
			$snumber = $startNumber;
		}
		$strnumber = sprintf("%04s", $snumber);
		$ret_format_number = $strnumber . $str_cek;
		return $ret_format_number;
	}
	function inv_permintaan_pindah_gudang($lokasi_id, $tanggal)
	{
		$startNumber = 1; /// Isi utk mulai 
		$table = "inv_permintaan_pindah_gudang_ht";
		$kode_menu = "INV";
		$kode_lokasi = '';
		$CI =   &get_instance();
		// $yyyymm= date('Y-m', strtotime($tanggal));
		$yyyy = date('Y', strtotime($tanggal));
		$year = substr(date('Y', strtotime($tanggal)), 2, 2);
		$month = date('m', strtotime($tanggal));
		$lokasi = $CI->db->query("select kode from gbm_organisasi where id  =" . $lokasi_id . " ")->row_array();
		$kode_lokasi = $lokasi['kode'];
		if ($kode_lokasi == 'DPAM') {
			$kode_lokasi = 'MILL';
		}
		$str_cek = '/' . $kode_menu . '/' . $kode_lokasi . '/' . $month . $year;
		$sql_cek =	"select  max((SUBSTRING(no_transaksi,1,(LENGTH(no_transaksi)-LENGTH('" . $str_cek . "'))))*1) AS last
		  from " . $table . " 	where no_transaksi LIKE '%" . $str_cek . "' ;";
		$lastnumber = $CI->db->query($sql_cek)->row_array();
		if (!empty($lastnumber['last'])) {
			$str = $lastnumber['last'];
			$snumber = (int)$str + 1;
		} else {
			$snumber = $startNumber;
		}
		$strnumber = sprintf("%04s", $snumber);
		$ret_format_number = $strnumber . $str_cek;
		return $ret_format_number;
	}
	function inv_pindah_gudang($lokasi_id, $tanggal)
	{
		$startNumber = 1; /// Isi utk mulai 
		$table = "inv_pindah_gudang_ht";
		$kode_menu = "INV";
		$kode_lokasi = '';
		$CI =   &get_instance();
		// $yyyymm= date('Y-m', strtotime($tanggal));
		$yyyy = date('Y', strtotime($tanggal));
		$year = substr(date('Y', strtotime($tanggal)), 2, 2);
		$month = date('m', strtotime($tanggal));
		$lokasi = $CI->db->query("select kode from gbm_organisasi where id  =" . $lokasi_id . " ")->row_array();
		$kode_lokasi = $lokasi['kode'];
		if ($kode_lokasi == 'DPAM') {
			$kode_lokasi = 'MILL';
		}
		$str_cek = '/' . $kode_menu . '/' . $kode_lokasi . '/' . $month . $year;
		$sql_cek =	"select  max((SUBSTRING(no_transaksi,1,(LENGTH(no_transaksi)-LENGTH('" . $str_cek . "'))))*1) AS last
		  from " . $table . " 	where no_transaksi LIKE '%" . $str_cek . "' ;";
		$lastnumber = $CI->db->query($sql_cek)->row_array();
		if (!empty($lastnumber['last'])) {
			$str = $lastnumber['last'];
			$snumber = (int)$str + 1;
		} else {
			$snumber = $startNumber;
		}
		$strnumber = sprintf("%04s", $snumber);
		$ret_format_number = $strnumber . $str_cek;
		return $ret_format_number;
	}
	function inv_penerimaan_pindah_gudang($lokasi_id, $tanggal)
	{
		$startNumber = 1; /// Isi utk mulai 
		$table = "inv_penerimaan_pindah_gudang_ht";
		$kode_menu = "INV";
		$kode_lokasi = '';
		$CI =   &get_instance();
		// $yyyymm= date('Y-m', strtotime($tanggal));
		$yyyy = date('Y', strtotime($tanggal));
		$year = substr(date('Y', strtotime($tanggal)), 2, 2);
		$month = date('m', strtotime($tanggal));
		$lokasi = $CI->db->query("select kode from gbm_organisasi where id  =" . $lokasi_id . " ")->row_array();
		$kode_lokasi = $lokasi['kode'];
		if ($kode_lokasi == 'DPAM') {
			$kode_lokasi = 'MILL';
		}
		$str_cek = '/' . $kode_menu . '/' . $kode_lokasi . '/' . $month . $year;
		$sql_cek =	"select  max((SUBSTRING(no_transaksi,1,(LENGTH(no_transaksi)-LENGTH('" . $str_cek . "'))))*1) AS last
		  from " . $table . " 	where no_transaksi LIKE '%" . $str_cek . "' ;";
		$lastnumber = $CI->db->query($sql_cek)->row_array();
		if (!empty($lastnumber['last'])) {
			$str = $lastnumber['last'];
			$snumber = (int)$str + 1;
		} else {
			$snumber = $startNumber;
		}
		$strnumber = sprintf("%04s", $snumber);
		$ret_format_number = $strnumber . $str_cek;
		return $ret_format_number;
	}

	function est_bkm_panen($lokasi_id, $tanggal)
	{
		$startNumber = 1; /// Isi utk mulai 
		$table = "est_bkm_panen_ht";
		$kode_menu = "PNN";
		$kode_lokasi = '';
		$CI =   &get_instance();
		// $yyyymm= date('Y-m', strtotime($tanggal));
		$yyyy = date('Y', strtotime($tanggal));
		$year = substr(date('Y', strtotime($tanggal)), 2, 2);
		$month = date('m', strtotime($tanggal));
		$lokasi = $CI->db->query("select kode from gbm_organisasi where id  =" . $lokasi_id . " ")->row_array();
		$kode_lokasi = $lokasi['kode'];
		if ($kode_lokasi == 'DPAM') {
			$kode_lokasi = 'MILL';
		}
		$str_cek = '/' . $kode_menu . '/' . $kode_lokasi . '/' . $month . $year;
		$sql_cek =	"select  max((SUBSTRING(no_transaksi,1,(LENGTH(no_transaksi)-LENGTH('" . $str_cek . "'))))*1) AS last
		  from " . $table . " 	where no_transaksi LIKE '%" . $str_cek . "' ;";
		$lastnumber = $CI->db->query($sql_cek)->row_array();
		if (!empty($lastnumber['last'])) {
			$str = $lastnumber['last'];
			$snumber = (int)$str + 1;
		} else {
			$snumber = $startNumber;
		}
		$strnumber = sprintf("%04s", $snumber);
		$ret_format_number = $strnumber . $str_cek;
		return $ret_format_number;
	}
	function est_bkm_pemeliharaan($lokasi_id, $tanggal)
	{
		$startNumber = 1; /// Isi utk mulai 
		$table = "est_bkm_pemeliharaan_ht";
		$kode_menu = "PRT";
		$kode_lokasi = '';
		$CI =   &get_instance();
		// $yyyymm= date('Y-m', strtotime($tanggal));
		$yyyy = date('Y', strtotime($tanggal));
		$year = substr(date('Y', strtotime($tanggal)), 2, 2);
		$month = date('m', strtotime($tanggal));
		$lokasi = $CI->db->query("select kode from gbm_organisasi where id  =" . $lokasi_id . " ")->row_array();
		$kode_lokasi = $lokasi['kode'];
		if ($kode_lokasi == 'DPAM') {
			$kode_lokasi = 'MILL';
		}
		$str_cek = '/' . $kode_menu . '/' . $kode_lokasi . '/' . $month . $year;
		$sql_cek =	"select  max((SUBSTRING(no_transaksi,1,(LENGTH(no_transaksi)-LENGTH('" . $str_cek . "'))))*1) AS last
		  from " . $table . " 	where no_transaksi LIKE '%" . $str_cek . "' ;";
		$lastnumber = $CI->db->query($sql_cek)->row_array();
		if (!empty($lastnumber['last'])) {
			$str = $lastnumber['last'];
			$snumber = (int)$str + 1;
		} else {
			$snumber = $startNumber;
		}
		$strnumber = sprintf("%04s", $snumber);
		$ret_format_number = $strnumber . $str_cek;
		return $ret_format_number;
	}

	function est_bkm_umum($lokasi_id, $tanggal)
	{
		$startNumber = 1; /// Isi utk mulai 
		$table = "est_bkm_umum_ht";
		$kode_menu = "EST";
		$kode_lokasi = '';
		$CI =   &get_instance();
		// $yyyymm= date('Y-m', strtotime($tanggal));
		$yyyy = date('Y', strtotime($tanggal));
		$year = substr(date('Y', strtotime($tanggal)), 2, 2);
		$month = date('m', strtotime($tanggal));
		$lokasi = $CI->db->query("select kode from gbm_organisasi where id  =" . $lokasi_id . " ")->row_array();
		$kode_lokasi = $lokasi['kode'];
		if ($kode_lokasi == 'DPAM') {
			$kode_lokasi = 'MILL';
		}
		$str_cek = '/' . $kode_menu . '/' . $kode_lokasi . '/' . $month . $year;
		$sql_cek =	"select  max((SUBSTRING(no_transaksi,1,(LENGTH(no_transaksi)-LENGTH('" . $str_cek . "'))))*1) AS last
		  from " . $table . " 	where no_transaksi LIKE '%" . $str_cek . "' ;";
		$lastnumber = $CI->db->query($sql_cek)->row_array();
		if (!empty($lastnumber['last'])) {
			$str = $lastnumber['last'];
			$snumber = (int)$str + 1;
		} else {
			$snumber = $startNumber;
		}
		$strnumber = sprintf("%04s", $snumber);
		$ret_format_number = $strnumber . $str_cek;
		return $ret_format_number;
	}

	function wrk_kegiatan($lokasi_id, $tanggal)
	{
		$startNumber = 1; /// Isi utk mulai 
		$table = "wrk_kegiatan_ht";
		$kode_menu = "WRK";
		$kode_lokasi = '';
		$CI =   &get_instance();
		// $yyyymm= date('Y-m', strtotime($tanggal));
		$yyyy = date('Y', strtotime($tanggal));
		$year = substr(date('Y', strtotime($tanggal)), 2, 2);
		$month = date('m', strtotime($tanggal));
		$lokasi = $CI->db->query("select kode from gbm_organisasi where id  =" . $lokasi_id . " ")->row_array();
		$kode_lokasi = $lokasi['kode'];
		if ($kode_lokasi == 'DPAM') {
			$kode_lokasi = 'MILL';
		}
		$str_cek = '/' . $kode_menu . '/' . $kode_lokasi . '/' . $month . $year;
		$sql_cek =	"select  max((SUBSTRING(no_transaksi,1,(LENGTH(no_transaksi)-LENGTH('" . $str_cek . "'))))*1) AS last
		  from " . $table . " 	where no_transaksi LIKE '%" . $str_cek . "' ;";
		$lastnumber = $CI->db->query($sql_cek)->row_array();
		if (!empty($lastnumber['last'])) {
			$str = $lastnumber['last'];
			$snumber = (int)$str + 1;
		} else {
			$snumber = $startNumber;
		}
		$strnumber = sprintf("%04s", $snumber);
		$ret_format_number = $strnumber . $str_cek;
		return $ret_format_number;
	}
	function wrk_kegiatan_mill($lokasi_id, $tanggal)
	{
		$startNumber = 1; /// Isi utk mulai 
		$table = "wrk_kegiatan_ht";
		$kode_menu = "WRK";
		$kode_lokasi = '';
		$CI =   &get_instance();
		// $yyyymm= date('Y-m', strtotime($tanggal));
		$yyyy = date('Y', strtotime($tanggal));
		$year = substr(date('Y', strtotime($tanggal)), 2, 2);
		$month = date('m', strtotime($tanggal));
		$lokasi = $CI->db->query("select kode from gbm_organisasi where id  =" . $lokasi_id . " ")->row_array();
		$kode_lokasi = $lokasi['kode'];
		if ($kode_lokasi == 'DPAM') {
			$kode_lokasi = 'MILL';
		}
		$str_cek = '/' . $kode_menu . '/' . $kode_lokasi . '/' . $month . $year;
		$sql_cek =	"select  max((SUBSTRING(no_transaksi,1,(LENGTH(no_transaksi)-LENGTH('" . $str_cek . "'))))*1) AS last
		  from " . $table . " 	where no_transaksi LIKE '%" . $str_cek . "' ;";
		$lastnumber = $CI->db->query($sql_cek)->row_array();
		if (!empty($lastnumber['last'])) {
			$str = $lastnumber['last'];
			$snumber = (int)$str + 1;
		} else {
			$snumber = $startNumber;
		}
		$strnumber = sprintf("%04s", $snumber);
		$ret_format_number = $strnumber . $str_cek;
		return $ret_format_number;
	}

	function trk_kegiatan_kendaraan($lokasi_id, $tanggal)
	{
		$startNumber = 1; /// Isi utk mulai 
		$table = "trk_kegiatan_kendaraan_ht";
		$kode_menu = "TRK";
		$kode_lokasi = '';
		$CI =   &get_instance();
		// $yyyymm= date('Y-m', strtotime($tanggal));
		$yyyy = date('Y', strtotime($tanggal));
		$year = substr(date('Y', strtotime($tanggal)), 2, 2);
		$month = date('m', strtotime($tanggal));
		$lokasi = $CI->db->query("select kode from gbm_organisasi where id  =" . $lokasi_id . " ")->row_array();
		$kode_lokasi = $lokasi['kode'];
		if ($kode_lokasi == 'DPAM') {
			$kode_lokasi = 'MILL';
		}
		$str_cek = '/' . $kode_menu . '/' . $kode_lokasi . '/' . $month . $year;
		$sql_cek =	"select  max((SUBSTRING(no_transaksi,1,(LENGTH(no_transaksi)-LENGTH('" . $str_cek . "'))))*1) AS last
		  from " . $table . " 	where no_transaksi LIKE '%" . $str_cek . "' ;";
		$lastnumber = $CI->db->query($sql_cek)->row_array();
		if (!empty($lastnumber['last'])) {
			$str = $lastnumber['last'];
			$snumber = (int)$str + 1;
		} else {
			$snumber = $startNumber;
		}
		$strnumber = sprintf("%04s", $snumber);
		$ret_format_number = $strnumber . $str_cek;
		return $ret_format_number;
	}

	function sls_rekap($lokasi_id, $tanggal)
	{
		$startNumber = 1; /// Isi utk mulai 
		$table = "sls_rekap_hd";
		$kode_menu = "SLS";
		$kode_lokasi = '';
		$CI =   &get_instance();
		// $yyyymm= date('Y-m', strtotime($tanggal));
		$yyyy = date('Y', strtotime($tanggal));
		$year = substr(date('Y', strtotime($tanggal)), 2, 2);
		$month = date('m', strtotime($tanggal));
		$lokasi = $CI->db->query("select kode from gbm_organisasi where id  =" . $lokasi_id . " ")->row_array();
		$kode_lokasi = $lokasi['kode'];
		if ($kode_lokasi == 'DPAM') {
			$kode_lokasi = 'MILL';
		}
		$str_cek = '/' . $kode_menu . '/' . $kode_lokasi . '/' . $month . $year;
		$sql_cek =	"select  max((SUBSTRING(no_rekap,1,(LENGTH(no_rekap)-LENGTH('" . $str_cek . "'))))*1) AS last
		  from " . $table . " 	where no_rekap LIKE '%" . $str_cek . "' ;";
		$lastnumber = $CI->db->query($sql_cek)->row_array();
		if (!empty($lastnumber['last'])) {
			$str = $lastnumber['last'];
			$snumber = (int)$str + 1;
		} else {
			$snumber = $startNumber;
		}
		$strnumber = sprintf("%04s", $snumber);
		$ret_format_number = $strnumber . $str_cek;
		return $ret_format_number;
	}
	function prc_rekap_angkut_old($lokasi_id, $tanggal)
	{
		$startNumber = 1; /// Isi utk mulai 
		$table = "prc_rekap_angkut_hd";
		$kode_menu = "BAPP";
		$kode_lokasi = '';
		$CI =   &get_instance();
		// $yyyymm= date('Y-m', strtotime($tanggal));
		$yyyy = date('Y', strtotime($tanggal));
		$year = substr(date('Y', strtotime($tanggal)), 2, 2);
		$month = date('m', strtotime($tanggal));
		$lokasi = $CI->db->query("select kode from gbm_organisasi where id  =" . $lokasi_id . " ")->row_array();
		$kode_lokasi = $lokasi['kode'];
		if ($kode_lokasi == 'DPAM') {
			$kode_lokasi = 'MILL';
		}
		$str_cek = '/' . $kode_menu . '/' . $kode_lokasi . '/' . $month . $year;
		$sql_cek =	"select  max((SUBSTRING(no_rekap,1,(LENGTH(no_rekap)-LENGTH('" . $str_cek . "'))))*1) AS last
		  from " . $table . " 	where no_rekap LIKE '%" . $str_cek . "' ;";
		$lastnumber = $CI->db->query($sql_cek)->row_array();
		if (!empty($lastnumber['last'])) {
			$str = $lastnumber['last'];
			$snumber = (int)$str + 1;
		} else {
			$snumber = $startNumber;
		}
		$strnumber = sprintf("%04s", $snumber);
		$ret_format_number = $strnumber . $str_cek;
		return $ret_format_number;
	}
	function prc_rekap_angkut($lokasi_id, $tanggal, $supplier_id = null, $produk_id = null)
	{
		$startNumber = 1; /// Isi utk mulai 
		$table = "prc_rekap_angkut_hd";
		// $kode_menu = "PRK";
		$kode_menu = "BA";
		$kode_lokasi = '';
		$kode_produk = '';
		$CI =   &get_instance();
		// $yyyymm= date('Y-m', strtotime($tanggal));
		$yyyy = date('Y', strtotime($tanggal));
		$year = substr(date('Y', strtotime($tanggal)), 2, 2);
		$month = date('m', strtotime($tanggal));
		$lokasi = $CI->db->query("select kode from gbm_organisasi where id  =" . $lokasi_id . " ")->row_array();
		$kode_lokasi = $lokasi['kode'];
		$produk = $CI->db->query("select * from inv_item where id  =" . $produk_id . " ")->row_array();
		$kode_produk = $produk['kode'];
		if ($kode_produk == 'CPO') {
			$kode_produk == 'CPO';
		} else if ($kode_produk == 'PK') {
			$kode_produk == 'PKO';
		} else {
			$kode_produk == 'OTH';
		}

		$supplier = $CI->db->query("select kode_supplier from gbm_supplier where id  =" . $supplier_id . " ")->row_array();
		$kode_supplier = $supplier['kode_supplier'];
		if ($kode_lokasi == 'DPAM') {
			$kode_lokasi = 'MILL';
		}
		// $str_cek = '/' . $kode_menu . '/'.$kode_supplier.'/' . $month . $year;
		$str_cek = '/' . $kode_menu . '/' . $kode_produk . '/' . $kode_supplier . '/' . $month . $year;
		// $str_cek2='/' . $kode_menu . '/'.$kode_supplier.'/';
		$str_cek2 = '/' . $kode_menu . '/' . $kode_produk . '/' . $kode_supplier . '/';
		$sql_cek =	"select  max((SUBSTRING(no_rekap,1,(LENGTH(no_rekap)-LENGTH('" . $str_cek . "'))))*1) AS last
		  from " . $table . " 	where no_rekap LIKE '%" . $str_cek2 . "%' and DATE_FORMAT(tanggal,'%Y') ='" . $yyyy . "'
		  and item_id=" . $produk_id . " ;";
		$lastnumber = $CI->db->query($sql_cek)->row_array();
		if (!empty($lastnumber['last'])) {
			$str = $lastnumber['last'];
			$snumber = (int)$str + 1;
		} else {
			$snumber = $startNumber;
		}
		$strnumber = sprintf("%04s", $snumber);
		$ret_format_number = $strnumber . $str_cek;
		return $ret_format_number;
	}

	function prc_rekap($lokasi_id, $tanggal, $supplier_id = null)
	{
		$startNumber = 1; /// Isi utk mulai 
		$table = "prc_rekap_ht";
		// $kode_menu = "PRK";
		$kode_menu = "BA";
		$kode_lokasi = '';
		$CI =   &get_instance();
		// $yyyymm= date('Y-m', strtotime($tanggal));
		$yyyy = date('Y', strtotime($tanggal));
		$year = substr(date('Y', strtotime($tanggal)), 2, 2);
		$month = date('m', strtotime($tanggal));
		$lokasi = $CI->db->query("select kode from gbm_organisasi where id  =" . $lokasi_id . " ")->row_array();
		$kode_lokasi = $lokasi['kode'];
		$supplier = $CI->db->query("select kode_supplier from gbm_supplier where id  =" . $supplier_id . " ")->row_array();
		$kode_supplier = $supplier['kode_supplier'];
		if ($kode_lokasi == 'DPAM') {
			$kode_lokasi = 'MILL';
		}
		// $str_cek = '/' . $kode_menu . '/' . $kode_lokasi . '/'.$kode_supplier.'/' . $month . $year;
		$str_cek = '/' . $kode_menu . '/' . $kode_supplier . '/' . $month . $year;
		$str_cek2 = '/' . $kode_menu . '/' . $kode_supplier . '/';
		$sql_cek =	"select  max((SUBSTRING(no_rekap,1,(LENGTH(no_rekap)-LENGTH('" . $str_cek . "'))))*1) AS last
		  from " . $table . " 	where no_rekap LIKE '%" . $str_cek2 . "%' and DATE_FORMAT(tanggal,'%Y') ='" . $yyyy . "' ;";
		$lastnumber = $CI->db->query($sql_cek)->row_array();
		if (!empty($lastnumber['last'])) {
			$str = $lastnumber['last'];
			$snumber = (int)$str + 1;
		} else {
			$snumber = $startNumber;
		}
		$strnumber = sprintf("%04s", $snumber);
		$ret_format_number = $strnumber . $str_cek;
		return $ret_format_number;
	}

	function pks_pengolahan($lokasi_id, $tanggal)
	{
		$startNumber = 1; /// Isi utk mulai 
		$table = "pks_pengolahan_ht";
		$kode_menu = "PKS";
		$kode_lokasi = '';
		$CI =   &get_instance();
		// $yyyymm= date('Y-m', strtotime($tanggal));
		$yyyy = date('Y', strtotime($tanggal));
		$year = substr(date('Y', strtotime($tanggal)), 2, 2);
		$month = date('m', strtotime($tanggal));
		$lokasi = $CI->db->query("select kode from gbm_organisasi where id  =" . $lokasi_id . " ")->row_array();
		$kode_lokasi = $lokasi['kode'];
		if ($kode_lokasi == 'DPAM') {
			$kode_lokasi = 'MILL';
		}
		$str_cek = '/' . $kode_menu . '/' . $kode_lokasi . '/' . $month . $year;
		$sql_cek =	"select  max((SUBSTRING(no_transaksi,1,(LENGTH(no_transaksi)-LENGTH('" . $str_cek . "'))))*1) AS last
		  from " . $table . " 	where no_transaksi LIKE '%" . $str_cek . "' ;";
		$lastnumber = $CI->db->query($sql_cek)->row_array();
		if (!empty($lastnumber['last'])) {
			$str = $lastnumber['last'];
			$snumber = (int)$str + 1;
		} else {
			$snumber = $startNumber;
		}
		$strnumber = sprintf("%04s", $snumber);
		$ret_format_number = $strnumber . $str_cek;
		return $ret_format_number;
	}
	function pks_lab_pengolahan($lokasi_id, $tanggal)
	{
		$startNumber = 1; /// Isi utk mulai 
		$table = "pks_lab_pengolahan";
		$kode_menu = "PKS";
		$kode_lokasi = '';
		$CI =   &get_instance();
		// $yyyymm= date('Y-m', strtotime($tanggal));
		$yyyy = date('Y', strtotime($tanggal));
		$year = substr(date('Y', strtotime($tanggal)), 2, 2);
		$month = date('m', strtotime($tanggal));
		$lokasi = $CI->db->query("select kode from gbm_organisasi where id  =" . $lokasi_id . " ")->row_array();
		$kode_lokasi = $lokasi['kode'];
		if ($kode_lokasi == 'DPAM') {
			$kode_lokasi = 'MILL';
		}
		$str_cek = '/' . $kode_menu . '/' . $kode_lokasi . '/' . $month . $year;
		$sql_cek =	"select  max((SUBSTRING(no_transaksi,1,(LENGTH(no_transaksi)-LENGTH('" . $str_cek . "'))))*1) AS last
		  from " . $table . " 	where no_transaksi LIKE '%" . $str_cek . "' ;";
		$lastnumber = $CI->db->query($sql_cek)->row_array();
		if (!empty($lastnumber['last'])) {
			$str = $lastnumber['last'];
			$snumber = (int)$str + 1;
		} else {
			$snumber = $startNumber;
		}
		$strnumber = sprintf("%04s", $snumber);
		$ret_format_number = $strnumber . $str_cek;
		return $ret_format_number;
	}
	function pks_produksi_harian($lokasi_id, $tanggal)
	{
		$startNumber = 1; /// Isi utk mulai 
		$table = "pks_produksi_harian";
		$kode_menu = "PKS";
		$kode_lokasi = '';
		$CI =   &get_instance();
		// $yyyymm= date('Y-m', strtotime($tanggal));
		$yyyy = date('Y', strtotime($tanggal));
		$year = substr(date('Y', strtotime($tanggal)), 2, 2);
		$month = date('m', strtotime($tanggal));
		$lokasi = $CI->db->query("select kode from gbm_organisasi where id  =" . $lokasi_id . " ")->row_array();
		$kode_lokasi = $lokasi['kode'];
		if ($kode_lokasi == 'DPAM') {
			$kode_lokasi = 'MILL';
		}
		$str_cek = '/' . $kode_menu . '/' . $kode_lokasi . '/' . $month . $year;
		$sql_cek =	"select  max((SUBSTRING(no_transaksi,1,(LENGTH(no_transaksi)-LENGTH('" . $str_cek . "'))))*1) AS last
		  from " . $table . " 	where no_transaksi LIKE '%" . $str_cek . "' ;";
		$lastnumber = $CI->db->query($sql_cek)->row_array();
		if (!empty($lastnumber['last'])) {
			$str = $lastnumber['last'];
			$snumber = (int)$str + 1;
		} else {
			$snumber = $startNumber;
		}
		$strnumber = sprintf("%04s", $snumber);
		$ret_format_number = $strnumber . $str_cek;
		return $ret_format_number;
	}
	function pks_sounding($lokasi_id, $tanggal)
	{
		$startNumber = 1; /// Isi utk mulai 
		$table = "pks_sounding";
		$kode_menu = "PKS";
		$kode_lokasi = '';
		$CI =   &get_instance();
		// $yyyymm= date('Y-m', strtotime($tanggal));
		$yyyy = date('Y', strtotime($tanggal));
		$year = substr(date('Y', strtotime($tanggal)), 2, 2);
		$month = date('m', strtotime($tanggal));
		$lokasi = $CI->db->query("select kode from gbm_organisasi where id  =" . $lokasi_id . " ")->row_array();
		$kode_lokasi = $lokasi['kode'];
		if ($kode_lokasi == 'DPAM') {
			$kode_lokasi = 'MILL';
		}
		$str_cek = '/' . $kode_menu . '/' . $kode_lokasi . '/' . $month . $year;
		$sql_cek =	"select  max((SUBSTRING(no_transaksi,1,(LENGTH(no_transaksi)-LENGTH('" . $str_cek . "'))))*1) AS last
		  from " . $table . " 	where no_transaksi LIKE '%" . $str_cek . "' ;";
		$lastnumber = $CI->db->query($sql_cek)->row_array();
		if (!empty($lastnumber['last'])) {
			$str = $lastnumber['last'];
			$snumber = (int)$str + 1;
		} else {
			$snumber = $startNumber;
		}
		$strnumber = sprintf("%04s", $snumber);
		$ret_format_number = $strnumber . $str_cek;
		return $ret_format_number;
	}
	function pks_sjpp($lokasi_id, $tanggal)
	{
		$startNumber = 1; /// Isi utk mulai 
		$table = "pks_sjpp";
		$kode_menu = "PKS";
		$kode_lokasi = '';
		$CI =   &get_instance();
		// $yyyymm= date('Y-m', strtotime($tanggal));
		$yyyy = date('Y', strtotime($tanggal));
		$year = substr(date('Y', strtotime($tanggal)), 2, 2);
		$month = date('m', strtotime($tanggal));
		$lokasi = $CI->db->query("select kode from gbm_organisasi where id  =" . $lokasi_id . " ")->row_array();
		$kode_lokasi = $lokasi['kode'];
		if ($kode_lokasi == 'DPAM') {
			$kode_lokasi = 'MILL';
		}
		$lastnumber = $CI->db->query("select  max(Left(no_surat,4))as last from " . $table . "
		where DATE_FORMAT(tanggal,'%Y') ='" . $yyyy . "'  ")->row_array();
		if (!empty($lastnumber['last'])) {
			$str = $lastnumber['last'];
			$snumber = (int)$str + 1;
		} else {
			$snumber = $startNumber;
		}
		$strnumber = sprintf("%04s", $snumber);
		$ret_format_number = $strnumber . '/' . $kode_menu . '/' . $kode_lokasi . '/' . $month . $year;
		return $ret_format_number;
	}
	function hrms_pengobatan($lokasi_id, $tanggal)
	{
		$startNumber = 1; /// Isi utk mulai 
		$table = "hrms_pengobatan_ht";
		$kode_menu = "KES";
		$kode_lokasi = '';
		$CI =   &get_instance();
		// $yyyymm= date('Y-m', strtotime($tanggal));
		$yyyy = date('Y', strtotime($tanggal));
		$year = substr(date('Y', strtotime($tanggal)), 2, 2);
		$month = date('m', strtotime($tanggal));
		$lokasi = $CI->db->query("select kode from gbm_organisasi where id  =" . $lokasi_id . " ")->row_array();
		$kode_lokasi = $lokasi['kode'];
		if ($kode_lokasi == 'DPAM') {
			$kode_lokasi = 'MILL';
		}
		$str_cek = '/' . $kode_menu . '/' . $kode_lokasi . '/' . $month . $year;
		$sql_cek =	"select  max((SUBSTRING(no_transaksi,1,(LENGTH(no_transaksi)-LENGTH('" . $str_cek . "'))))*1) AS last
		  from " . $table . " 	where no_transaksi LIKE '%" . $str_cek . "' ;";
		$lastnumber = $CI->db->query($sql_cek)->row_array();
		if (!empty($lastnumber['last'])) {
			$str = $lastnumber['last'];
			$snumber = (int)$str + 1;
		} else {
			$snumber = $startNumber;
		}
		$strnumber = sprintf("%04s", $snumber);
		$ret_format_number = $strnumber . $str_cek;
		return $ret_format_number;
	}
	function hrms_klaim_kendaraan($lokasi_id, $tanggal)
	{
		$startNumber = 1; /// Isi utk mulai 
		$table = "hrms_klaim_kendaraan_ht";
		$kode_menu = "KND";
		$kode_lokasi = '';
		$CI =   &get_instance();
		// $yyyymm= date('Y-m', strtotime($tanggal));
		$yyyy = date('Y', strtotime($tanggal));
		$year = substr(date('Y', strtotime($tanggal)), 2, 2);
		$month = date('m', strtotime($tanggal));
		$lokasi = $CI->db->query("select kode from gbm_organisasi where id  =" . $lokasi_id . " ")->row_array();
		$kode_lokasi = $lokasi['kode'];
		if ($kode_lokasi == 'DPAM') {
			$kode_lokasi = 'MILL';
		}
		$str_cek = '/' . $kode_menu . '/' . $kode_lokasi . '/' . $month . $year;
		$sql_cek =	"select  max((SUBSTRING(no_transaksi,1,(LENGTH(no_transaksi)-LENGTH('" . $str_cek . "'))))*1) AS last
		  from " . $table . " 	where no_transaksi LIKE '%" . $str_cek . "' ;";
		$lastnumber = $CI->db->query($sql_cek)->row_array();
		if (!empty($lastnumber['last'])) {
			$str = $lastnumber['last'];
			$snumber = (int)$str + 1;
		} else {
			$snumber = $startNumber;
		}
		$strnumber = sprintf("%04s", $snumber);
		$ret_format_number = $strnumber . $str_cek;
		return $ret_format_number;
	}
	function hrms_perjalanan_dinas($lokasi_id, $tanggal)
	{
		$startNumber = 1; /// Isi utk mulai 
		$table = "hrms_perjalanan_dinas_ht";
		$kode_menu = "DNS";
		$kode_lokasi = '';
		$CI =   &get_instance();
		// $yyyymm= date('Y-m', strtotime($tanggal));
		$yyyy = date('Y', strtotime($tanggal));
		$year = substr(date('Y', strtotime($tanggal)), 2, 2);
		$month = date('m', strtotime($tanggal));
		$lokasi = $CI->db->query("select kode from gbm_organisasi where id  =" . $lokasi_id . " ")->row_array();
		$kode_lokasi = $lokasi['kode'];
		if ($kode_lokasi == 'DPAM') {
			$kode_lokasi = 'MILL';
		}
		$str_cek = '/' . $kode_menu . '/' . $kode_lokasi . '/' . $month . $year;
		$sql_cek =	"select  max((SUBSTRING(no_transaksi,1,(LENGTH(no_transaksi)-LENGTH('" . $str_cek . "'))))*1) AS last
		  from " . $table . " 	where no_transaksi LIKE '%" . $str_cek . "' ;";
		$lastnumber = $CI->db->query($sql_cek)->row_array();
		if (!empty($lastnumber['last'])) {
			$str = $lastnumber['last'];
			$snumber = (int)$str + 1;
		} else {
			$snumber = $startNumber;
		}
		$strnumber = sprintf("%04s", $snumber);
		$ret_format_number = $strnumber . $str_cek;
		return $ret_format_number;
	}
	function hrms_realisasi_perjalanan_dinas($lokasi_id, $tanggal)
	{
		$startNumber = 1; /// Isi utk mulai 
		$table = "hrms_realisasi_perjalanan_dinas_ht";
		$kode_menu = "RLS";
		$kode_lokasi = '';
		$CI =   &get_instance();
		// $yyyymm= date('Y-m', strtotime($tanggal));
		$yyyy = date('Y', strtotime($tanggal));
		$year = substr(date('Y', strtotime($tanggal)), 2, 2);
		$month = date('m', strtotime($tanggal));
		$lokasi = $CI->db->query("select kode from gbm_organisasi where id  =" . $lokasi_id . " ")->row_array();
		$kode_lokasi = $lokasi['kode'];
		if ($kode_lokasi == 'DPAM') {
			$kode_lokasi = 'MILL';
		}
		$str_cek = '/' . $kode_menu . '/' . $kode_lokasi . '/' . $month . $year;
		$sql_cek =	"select  max((SUBSTRING(no_transaksi,1,(LENGTH(no_transaksi)-LENGTH('" . $str_cek . "'))))*1) AS last
		  from " . $table . " 	where no_transaksi LIKE '%" . $str_cek . "' ;";
		$lastnumber = $CI->db->query($sql_cek)->row_array();
		if (!empty($lastnumber['last'])) {
			$str = $lastnumber['last'];
			$snumber = (int)$str + 1;
		} else {
			$snumber = $startNumber;
		}
		$strnumber = sprintf("%04s", $snumber);
		$ret_format_number = $strnumber . $str_cek;
		return $ret_format_number;
	}
	function acc_permohonan_bayar($tanggal)
	{
		$startNumber = 1; /// Isi utk mulai 
		$kode_menu = "PP/DPA-P";
		$kode_lokasi = '';
		$CI =   &get_instance();
		// $yyyymm= date('Y-m', strtotime($tanggal));
		$yyyy = date('Y', strtotime($tanggal));
		$year = substr(date('Y', strtotime($tanggal)), 2, 2);
		$month = date('m', strtotime($tanggal));

		// $lokasi = $CI->db->query("select kode from gbm_organisasi where id  =" . $lokasi_id . " ")->row_array();
		// $kode_lokasi = $lokasi['kode'];
		// if ($kode_lokasi == 'DPAM') {
		// 	$kode_lokasi = 'MILL';
		// }
		$str_cek = '/' . $kode_menu . '/' . $month . $year;
		$sql_cek =	"select  max((SUBSTRING(no_transaksi,1,(LENGTH(no_transaksi)-LENGTH('" . $str_cek . "'))))*1) AS last
		  from acc_permohonan_bayar_ht 	where no_transaksi LIKE '%" . $str_cek . "' ;";
		$lastnumber = $CI->db->query($sql_cek)->row_array();
		if (!empty($lastnumber['last'])) {
			$str = $lastnumber['last'];
			$snumber = (int)$str + 1;
		} else {
			$snumber = $startNumber;
		}
		$strnumber = sprintf("%04s", $snumber);
		$ret_format_number = $strnumber . $str_cek;
		return $ret_format_number;
	}
	// function est_bkm_umum($lokasi_id, $tanggal)
	// {
	// 	$startNumber = 1; /// Isi utk mulai 
	// 	$table = "est_bkm_umum_ht";
	// 	$kode_menu = "UM";
	// 	$kode_lokasi = '';
	// 	$CI =   &get_instance();
	// 	// $yyyymm= date('Y-m', strtotime($tanggal));
	// 	$yyyy = date('Y', strtotime($tanggal));
	// 	$year = substr(date('Y', strtotime($tanggal)), 2, 2);
	// 	$month = date('m', strtotime($tanggal));
	// 	$lokasi = $CI->db->query("select kode from gbm_organisasi where id  =" . $lokasi_id . " ")->row_array();
	// 	$kode_lokasi = $lokasi['kode'];
	// 	if ($kode_lokasi == 'DPAM') {
	// 		$kode_lokasi = 'MILL';
	// 	}
	// 	$lastnumber = $CI->db->query("select  max(Left(no_transaksi,4))as last from ".$table."
	// 	where DATE_FORMAT(tanggal,'%Y') ='" . $yyyy . "'  ")->row_array();
	// 	if (!empty($lastnumber['last'])) {
	// 		$str =$lastnumber['last'];
	// 		$snumber = (int)$str + 1;
	// 	} else {
	// 		$snumber = $startNumber;
	// 	}
	// 	$strnumber = sprintf("%04s", $snumber);
	// 	$ret_format_number = $strnumber . '/' . $kode_menu . '/' . $kode_lokasi . '/' . $month . $year;
	// 	return $ret_format_number;
	// }
}
