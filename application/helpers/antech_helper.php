<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

function get_header_script_report()
{
	$html ='	
		<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
		<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.2/js/bootstrap.min.js"></script>
		<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.2/css/bootstrap.min.css">
		<link rel="stylesheet" type="text/css" href="https://use.fontawesome.com/releases/v5.6.3/css/all.css">
		
		<style>
			* body {
				font-size: 11px;
			}

			#table_detail tr:hover {
				background-color: #F2F2F2;
			}

			#table_detail .hidden_row {
				display: none;
			}

			.add-btn {
				color: green;
				cursor: pointer;
				margin-right: 6px;
			}
		</style>
		';
	return $html;
}
function get_header_report()
{
	$html = "<style type='text/css'>
		h3.title {
			margin-bottom: 0px;
			line-height: 30px;
		}
		hr.top {
			border: none;
			border-bottom: 2px solid #333;
			margin-bottom: 10px;
			margin-top: 10px;
		}
		.kop-print {
		  width: 700px;
		  margin: auto;
	  }
  
	  .kop-print img {
		  float: left;
		  height: 60px;
		  margin-right: 20px;
	  }
  
	  .kop-print .kop-info {
		  font-size: 15px;
	  }
  
	  .kop-print .kop-nama {
		  font-size: 25px;
		  font-weight: bold;
		  line-height: 35px;
	  }
  
	  .kop-print-hr {
		  border-width: 2px;
		  border-color: black;
		  margin-bottom: 0px;
	  }
	  table {
		border-collapse: separate;
		border-spacing: 0;
		color: #4a4a4d;
		font: 14px/1.4 'Helvetica Neue', Helvetica, Arial, sans-serif;
	  }
	  th,
	  td {
		padding: 10px 15px;
		vertical-align: middle;
	  }
	  thead {
		background: #395870;
		background: linear-gradient(#49708f, #293f50);
		color: #fff;
		font-size: 11px;
		text-transform: uppercase;
	  }
	  th:first-child {
		border-top-left-radius: 5px;
		text-align: left;
	  }
	  th:last-child {
		border-top-right-radius: 5px;
	  }
	  tbody tr:nth-child(even) {
		background: #f0f0f2;
	  }
	  td {
		border-bottom: 1px solid #cecfd5;
		border-right: 1px solid #cecfd5;
	  }
	  td:first-child {
		border-left: 1px solid #cecfd5;
	  }
	  .book-title {
		color: #395870;
		display: block;
	  }
	  .text-offset {
		color: #7c7c80;
		font-size: 12px;
	  }
	  .item-stock,
	  .item-qty {
		text-align: center;
	  }
	  .item-price {
		text-align: right;
	  }
	  .item-multiple {
		display: block;
	  }
	  tfoot {
		text-align: right;
	  }
	  tfoot tr:last-child {
		background: #f0f0f2;
		color: #395870;
		font-weight: bold;
	  }
	  tfoot tr:last-child td:first-child {
		border-bottom-left-radius: 5px;
	  }
	  tfoot tr:last-child td:last-child {
		border-bottom-right-radius: 5px;
	  }
	  @page {
		/* margin-top: 149px;
        margin-left: 2px;
        margin-bottom: 40px;
        margin-right: 2px;
        size: landscape; */
		counter-increment: page;

		@bottom-left {
			padding-right: 20px;
			content: counter(page) ' of 'counter(pages);
		}

	}
  
	</style>
  ";
	return $html;
}
function get_header_report_v2($judul=''){
	$html="
	<head>
	". get_header_script_report() ."
	<title>".$judul."</title>
	<link rel='icon' type='image/png' href=".base_url('logo_antech.png')." />

	<style type='text/css'>
	* {
				font-size: 13px ;
				font-family: Arial;
		}
			
	table.no_border td {
				border-bottom: 0px;
				border-right: 0px;
				border-left: 0px;
				padding: 7px 7px;
				
		}	

/* ----------------------------------------------------- */

h3.title {
			margin-bottom: 0px;
			margin-left: 10px;
			line-height: 30px;
			text-align: center;
			font-size: 17px ;
		}
		hr.top {
			border: none;
			border-bottom: 2px solid #333;
			margin-bottom: 10px;
			margin-top: 10px;
		}
		.kop-print {
		  padding-left:10px;
		  margin: auto;
	  }
  
	  .kop-print img {
		  float: left;
		  height: 60px;
		  margin-right: 20px;
	  }
  
	  .kop-print .kop-info {
		  font-size: 15px;
	  }
  
	  .kop-print .kop-nama {
		  font-size: 25px;
		  font-weight: bold;
		  line-height: 35px;
	  }
  
	  .kop-print-hr {
		  border-width: 2px;
		  border-color: black;
		  margin-bottom: 0px;
	  }
	  table {
		border-collapse: separate;
		border-spacing: 0;
		color: #4a4a4d;
		font: 14px/1.4 'Helvetica Neue', Helvetica, Arial, sans-serif;
	  }
	  th,
	  td {
		padding: 10px 10px;
		vertical-align: middle;
	  }
	  th{
		font-size: 12px;
	  }
	  thead {
		background: #395870;
		background: linear-gradient(#49708f, #293f50);
		color: #fff;
		font-size: 11px;
		text-transform: uppercase;
	  }
	  th:first-child {
		border-top-left-radius: 5px;
		text-align: left;
	  }
	  th:last-child {
		border-top-right-radius: 5px;
	  }
	  tbody tr:nth-child(even) {
		background: #f0f0f2;
	  }
	  td {
		border-bottom: 1px solid #cecfd5;
		border-right: 1px solid #cecfd5;
		/* font-size: 12px; */
	  }
	  td:first-child {
		border-left: 1px solid #cecfd5;
	  }
	  .book-title {
		color: #395870;
		display: block;
	  }
	  .text-offset {
		color: #7c7c80;
		font-size: 12px;
	  }
	  .item-stock,
	  .item-qty {
		text-align: center;
	  }
	  .item-price {
		text-align: right;
	  }
	  .item-multiple {
		display: block;
	  }
	  tfoot {
		text-align: right;
	  }
	  tfoot tr:last-child {
		background: #f0f0f2;
		color: #395870;
		font-weight: bold;
	  }
	  tfoot tr:last-child td:first-child {
		border-bottom-left-radius: 5px;
	  }
	  tfoot tr:last-child td:last-child {
		border-bottom-right-radius: 5px;
	  }
	  @page {
		/* margin-top: 149px;
        margin-left: 2px;
        margin-bottom: 40px;
        margin-right: 2px;
        size: landscape; */
		counter-increment: page;

		@bottom-left {
			padding-right: 20px;
			content: counter(page) ' of 'counter(pages);
		}

	}
	</style> 
	</head>";
	return $html;
}
function get_header_pdf_report()
{
	$html = "<style type='text/css'>
		h3.title {
			margin-bottom: 0px;
			line-height: 30px;
		}
		hr.top {
			border: none;
			border-bottom: 2px solid #333;
			margin-bottom: 10px;
			margin-top: 10px;
		}
		.kop-print {
		  width: 700px;
		  margin: auto;
	  }
  
	  .kop-print img {
		  float: left;
		  height: 60px;
		  margin-right: 20px;
	  }
  
	  .kop-print .kop-info {
		  font-size: 15px;
	  }
  
	  .kop-print .kop-nama {
		  font-size: 25px;
		  font-weight: bold;
		  line-height: 35px;
	  }
  
	  .kop-print-hr {
		  border-width: 2px;
		  border-color: black;
		  margin-bottom: 0px;
	  }
	  table {
		border-collapse: separate;
		border-spacing: 0;
		color: #4a4a4d;
		font: 14px/1.4 'Helvetica Neue', Helvetica, Arial, sans-serif;
	  }
	  th,
	  td {
		padding: 10px 15px;
		vertical-align: middle;
	  }
	  thead {
		
		font-weight: bold;
		font-size: 11px;
		text-transform: uppercase;
	  }
	  th:first-child {
		
	  }
	  th:last-child {
		
	  }
	  tbody tr:nth-child(even) {
		background: #f0f0f2;
	  }
	  td {
		// border-bottom: 1px solid #cecfd5;
		// border-right: 1px solid #cecfd5;
	  }
	  td:first-child {
		// border-left: 1px solid #cecfd5;
	  }
	  .book-title {
		color: #395870;
		display: block;
	  }
	  .text-offset {
		color: #7c7c80;
		font-size: 12px;
	  }
	  .item-stock,
	  .item-qty {
		text-align: center;
	  }
	  .item-price {
		text-align: right;
	  }
	  .item-multiple {
		display: block;
	  }
	  tfoot {
		text-align: right;
	  }
	  tfoot tr:last-child {
		background: #f0f0f2;
		color: #395870;
		font-weight: bold;
	  }
	  tfoot tr:last-child td:first-child {
		border-bottom-left-radius: 5px;
	  }
	  tfoot tr:last-child td:last-child {
		border-bottom-right-radius: 5px;
	  }
	  @page {
		/* margin-top: 149px;
        margin-left: 2px;
        margin-bottom: 40px;
        margin-right: 2px;
        size: landscape; */
		counter-increment: page;

		@bottom-left {
			padding-right: 20px;
			content: counter(page) ' of 'counter(pages);
		}
	}
	</style>
  ";
	return $html;
}
function get_header_pdf_report_v2($judul='')
{
	$html = "
	<head>
	<title>".$judul."</title>
	<link rel='icon' type='image/png' href=".base_url('logo_antech.png')." />
	<style type='text/css'>
	body {
		background-color: white;
		font-family: Helvetica, arial, sans-serif;
	}

	body * {
		
		border-spacing: 0;
	}
		table.no_border td {
				border-top: 0px;
				border-bottom: 0px;
				border-right: 0px;
				border-left: 0px;
				padding: 7px 7px;
				
		}	
	
		  
		
		h3.title {
			margin-bottom: 0px;
			line-height: 30px;
		}
		hr.top {
			border: none;
			border-bottom: 2px solid #333;
			margin-bottom: 10px;
			margin-top: 10px;
		}
		/* KOP PRINT */
		.kop-print {
			width: 1000px;
			margin: auto;
		}
	
		.kop-print img {
			float: left;
			height: 60px;
			margin-right: 20px;
		}
	
		.kop-print .kop-info {
			font-size: 15px;
		}
	
		.kop-print .kop-nama {
			font-size: 25px;
			font-weight: bold;
			line-height: 35px;
		}
	
		.kop-print-hr {
			border-color: rgba(0, 0, 0, 0, 1);
			margin-bottom: 0px;
		}
	  /* TABLE */
	  table {
		  width: 100%;
		  break-inside: avoid;
	  }
  
	  table.border {
		  border: 0.5px solid rgba(0, 0, 0, 0.4);
	  }
  
	  th,
	  td {
		  font-size: 0.8em;
		  break-inside: avoid;
	  }
  
	  th {
		  color: black;
		  /* background: linear-gradient(to right, #9dc9fa, #9dc9fa); */
		  background: rgba(50, 50, 50, 0.1);
		  text-align: center !important;
		  font-weight: bolder;
		  text-transform: uppercase;
		  break-inside: avoid;
	  }
  
	  th,
	  td {
		  border: 0.8px solid rgba(0, 0, 0, 0.4);
		  padding: 5px 8px;
		  break-inside: avoid;
	  }
	</style>
	</head>
  ";
	return $html;
}
function get_header_pdf_report_gaji()
{
	$html = "<style type='text/css'>
	body {
		background-color: white;
		font-family: Helvetica, arial, sans-serif;
	}

	body * {
		
		border-spacing: 0;
	}
	table.no_border td, {
		border-top: 0px;
		border-bottom: 0px;
		border-right: 0px;
		border-left: 0px;
		padding: 7px 7px;
	}
	
		  
		
		h3.title {
			margin-bottom: 0px;
			line-height: 30px;
		}
		hr.top {
			border: none;
			border-bottom: 2px solid #333;
			margin-bottom: 10px;
			margin-top: 10px;
		}
		/* KOP PRINT */
		.kop-print {
			width: 1000px;
			margin: auto;
		}
	
		.kop-print img {
			float: left;
			height: 60px;
			margin-right: 20px;
		}
	
		.kop-print .kop-info {
			font-size: 15px;
		}
	
		.kop-print .kop-nama {
			font-size: 25px;
			font-weight: bold;
			line-height: 35px;
		}
	
		.kop-print-hr {
			border-color: rgba(0, 0, 0, 0, 1);
			margin-bottom: 0px;
		}
	  /* TABLE */
	  table {
		  width: 100%;
		  break-inside: avoid;
	  }
  
	  table.border {
		  border: 0.5px solid rgba(0, 0, 0, 0.4);
	  }
  
	  th,
	  td {
		  font-size: 0.6em;
		  break-inside: avoid;
	  }
  
	  th {
		  color: black;
		  /* background: linear-gradient(to right, #9dc9fa, #9dc9fa); */
		  background: rgba(50, 50, 50, 0.1);
		  text-align: center !important;
		  font-weight: bolder;
		  text-transform: uppercase;
		  break-inside: avoid;
	  }
  
	  th,
	  td {
		  border: 0.8px solid rgba(0, 0, 0, 0.4);
		  padding: 5px 8px;
		  break-inside: avoid;
	  }
	</style>
  ";
	return $html;
}
/**
 * Fungsi untuk cek koneksi, kalo error throw new
 * @return boolean
 */
function check_db_connection()
{
	$db_file = APPPATH . 'config/database.php';
	if (!is_file($db_file)) {
		throw new Exception('File database.php in application/config/ not exists');
	}

	# cek pengaturan database
	include APPPATH . 'config/database.php';

	$link = @mysqli_connect("{$db['default']['hostname']}", "{$db['default']['username']}", "{$db['default']['password']}");
	if (!$link) {
		throw new Exception('Failed to connect to the server: ' . mysqli_connect_error());
	}

	$select_db = @mysqli_select_db($link, "{$db['default']['database']}");
	if (!$select_db) {
		throw new Exception('Failed to connect to the database: ' . mysqli_error($link));
	}

	# ciptakan variable global supaya driver ci tidak melakukan konek-konek lagi
	$GLOBALS['el_mysqli_connect']   = $link;
	$GLOBALS['el_mysqli_select_db'] = $select_db;

	return true;
}




/**
 * Fungsi yang berguna untuk mendapatkan data tertentu dari model tertentu
 *
 * @param  string $model
 * @param  string $func
 * @param  array  $args
 * @param  string $field_name
 * @return array|string
 */
function get_row_data($model, $func, $args = array(), $field_name = '')
{
	$CI = &get_instance();
	$CI->load->model($model);

	$retrieve = call_user_func_array(array($CI->$model, $func), $args);

	if (empty($field_name)) {
		return $retrieve;
	} else {
		return isset($retrieve[$field_name]) ? $retrieve[$field_name] : '';
	}
}

/**
 * Method untuk mendapatkan data site config
 *
 * @param  string $id
 * @param  string $get   nama atau value
 * @return string data
 */
function get_pengaturan($id, $get = null)
{
	$result = get_row_data('ConfigModel', 'retrieve', array($id), $get);
	return $result;
}


function get_logo_url($size = 'small')
{
	return base_url('assets/images/logo-' . strtolower($size) . '.png');
}

/**
 * Method untuk mendapatkan logo yang diatur
 * @return string
 */
function get_logo_config()
{
	$config = get_pengaturan('logo-company', 'value');
	if (empty($config)) {
		return get_logo_url('medium');
	} else {
		return get_url_image($config);
	}
}


/**
 * Method untuk mendapatkan data session last time activity
 * @param  string $act get|renew
 * @return integer
 */
function last_time_activity_session($act)
{
	switch ($act) {
			// case 'get':
			//     return isset($_SESSION['login_' . APP_PREFIX]['last_time_activity']) ? $_SESSION['login_' . APP_PREFIX]['last_time_activity'] : "";
			// break;

			// case 'renew':
			//     $_SESSION['login_' . APP_PREFIX]['last_time_activity'] = time();
			// break;
	}
}


function get_url_image($img, $size = '')
{
	$_SERVER["HTTP_NAMAPATH"] = "plantation";
	if (empty($size)) {
		// return base_url( $_SERVER['DOCUMENT_ROOT'] . "/" . $_SERVER["HTTP_NAMAPATH"] . "/userfiles/images/".$img);
		return ($_SERVER['DOCUMENT_ROOT'] . "/" . $_SERVER["HTTP_NAMAPATH"] . "/userfiles/images/" . $img);
	} else {
		$pisah     = explode('.', $img);
		$ext       = end($pisah);
		$nama_file = $pisah[0];

		// return base_url( $_SERVER['DOCUMENT_ROOT'] . "/" . $_SERVER["HTTP_NAMAPATH"] . "/userfiles/images/".$nama_file.'_'.$size.'.'.$ext);
		return ($_SERVER['DOCUMENT_ROOT'] . "/" . $_SERVER["HTTP_NAMAPATH"] . "/userfiles/images/" . $nama_file . '_' . $size . '.' . $ext);
	}
}

/**
 * Method untuk mendapatkan link gambar  absesnsi QRCode
 *
 * @param  string $img
 * @param  string $size
 * @return string
 *
 */
function get_url_image_absensi($img)
{

	$pisah     = explode('.', $img);
	$ext       = end($pisah);
	$nama_file = $pisah[0];
	return base_url($_SERVER['DOCUMENT_ROOT'] . "/" . $_SERVER["HTTP_NAMAPATH"] . "/userfiles/absensi/" . $nama_file . '.' . $ext);
}


/**
 * Method untuk mendapatkan link gambar  absesnsi QRCode
 *
 * @param  string $img
 * @param  string $size
 * @return string
 *
 */
function get_url_image_absensi_scan($img)
{

	$pisah     = explode('.', $img);
	$ext       = end($pisah);
	$nama_file = $pisah[0];

	return base_url($_SERVER['DOCUMENT_ROOT'] . "/" . $_SERVER["HTTP_NAMAPATH"] . "/userfiles/absensiQRCode/" . $nama_file . '.' . $ext);
}

/**
 * Method untuk mendapatkan link foto siswa
 *
 * @param  string $img
 * @param  string $size
 * @param  string $jk
 * @return string url
 */
function get_url_image_siswa($img = '', $size = 'medium', $jk = 'Laki-laki')
{
	if (is_null($img) or empty($img)) {
		if ($jk == 'Laki-laki') {
			$img = 'default_siswa.png';
		} else {
			$img = 'default_siswi.png';
		}
		return get_url_image($img);
	} else {
		return get_url_image($img, $size);
	}
}

/**
 * Method untuk mendapatkan link foto pengajar
 *
 * @param  string $img
 * @param  string $size
 * @param  string $jk
 * @return string url
 */
function get_url_image_pengajar($img = '', $size = 'medium', $jk = 'Laki-laki')
{
	if (is_null($img) or empty($img)) {
		if ($jk == 'Laki-laki') {
			$img = 'default_pl.png';
		} else {
			$img = 'default_pp.png';
		}
		return get_url_image($img);
	} else {
		return get_url_image($img, $size);
	}
}

/**
 * Method untuk mendapatkan link foto pengajar/admin/siswa ketika sudah login
 *
 * @param  string $img
 * @param  string $size
 * @param  string $jk
 * @return string url
 */
function get_url_image_session($img = '', $size = 'medium', $jk = 'Laki-laki')
{
	// if (is_pengajar() OR is_admin()) {
	//     return get_url_image_pengajar($img, $size, $jk);
	// } elseif (is_siswa()) {
	//     return get_url_image_siswa($img, $size, $jk);
	// }
}

/**
 * Method untuk mendapatkan path image
 *
 * @param  string $img
 * @param  string $size medium|small, kalau aslinya di kosongkan
 * @return string paht
 */
function get_path_image($img = '', $size = '')
{
	if (empty($size)) {
		return './' . $_SERVER['DOCUMENT_ROOT'] . "/" . $_SERVER["HTTP_NAMAPATH"] . "/userfiles/images/" . $img;
	} else {
		$pisah = explode('.', $img);
		$ext = end($pisah);
		$nama_file = $pisah[0];

		return './' . $_SERVER['DOCUMENT_ROOT'] . "/" . $_SERVER["HTTP_NAMAPATH"] . "/userfiles/images/" . $nama_file . '_' . $size . '.' . $ext;
	}
}

/**
 * Deklarasi path file
 *
 * @param  string $file
 * @return string
 */
function get_path_file($file = '')
{
	//  return './'.USERFILES.'/files/'.$file;
	return	$_SERVER['DOCUMENT_ROOT'] . "/" . 'hcis_folder' . "/userfiles/files/" . $file;
}


/**
 * Method untuk mendapatkan flashdata
 *
 * @param  string $key
 * @return string
 */
function get_flashdata($key)
{
	$CI = &get_instance();

	return $CI->session->flashdata($key);
}

/**
 * Fungsi untuk mendapatkan bulan dengan nama indonesia
 *
 * @param  string $bln
 * @return string
 */
function get_indo_bulan($bln = '')
{
	$data = array(1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember');
	if (empty($bln)) {
		return $data;
	} else {
		$bln = (int)$bln;
		return isset($data[$bln]) ? $data[$bln] : "";
	}
}

/**
 * Fungsi untuk mendapatkan nama hari indonesia
 *
 * @param  string $hari
 * @return string
 */
function get_indo_hari($hari = '')
{
	$data = array(1 => 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jum\'at', 'Sabtu', 'Minggu');
	if (empty($hari)) {
		return $data;
	} else {
		$hari = (int)$hari;
		return $data[$hari];
	}
}

/**
 * Method untuk memformat tanggal ke indonesia
 *
 * @param  string $tgl
 * @return string
 */
function tgl_indo($tgl = '')
{
	if (!empty($tgl)) {
		$pisah = explode('-', $tgl);
		return $pisah[2] . ' ' . get_indo_bulan($pisah[1]) . ' ' . $pisah[0];
	}
}

function tgl_indo_normal($tgl = '')
{
	if (!empty($tgl)) {
		$pisah = explode('-', $tgl);
		return $pisah[2] . '-' . ($pisah[1]) . '-' . $pisah[0];
	}
}

/**
 * Method untuk memformat tanggal dan jam ke format indonesia
 *
 * @param  string $tgl_jam
 * @return string
 */
function tgl_jam_indo($tgl_jam = '')
{
	if (!empty($tgl_jam)) {
		$pisah = explode(' ', $tgl_jam);
		return tgl_indo($pisah[0]) . ' ' . date('H:i', strtotime($tgl_jam));
	}
}

/**
 * Method untuk memforamt tanggal dan jam supaya lebih enak dibaca
 * @param  datetime $datetime
 * @return string
 */
function format_datetime($datetime='')
{
	# format tanggal, jika hari ini
	if (date('Y-m-d') == date('Y-m-d', strtotime($datetime))) {
		$selisih = time() - strtotime($datetime);

		$detik = $selisih;
		$menit = round($selisih / 60);
		$jam   = round($selisih / 3600);

		if ($detik <= 60) {
			if ($detik == 0) {
				$waktu = "baru saja";
			} else {
				$waktu = $detik . ' detik yang lalu';
			}
		} else if ($menit <= 60) {
			$waktu = $menit . ' menit yang lalu';
		} else if ($jam <= 24) {
			$waktu = $jam . ' jam yang lalu';
		} else {
			$waktu = date('H:i', strtotime($datetime));
		}

		$datetime = $waktu;
	}
	# kemarin
	elseif (date('Y-m-d', strtotime('-1 day', strtotime(date('Y-m-d')))) == date('Y-m-d', strtotime($datetime))) {
		$datetime = 'Kemarin ' . date('H:i', strtotime($datetime));
	}
	# lusa
	elseif (date('Y-m-d', strtotime('-2 day', strtotime(date('Y-m-d')))) == date('Y-m-d', strtotime($datetime))) {
		$datetime = '2 hari yang lalu ' . date('H:i', strtotime($datetime));
	} else {
		$datetime = tgl_jam_indo($datetime);
	}

	return $datetime;
}

/**
 * Metho untuk mendapatkan array post
 *
 * @param  string $key
 * @return string
 */
function get_post_data($key = '')
{
	if (isset($_POST[$key])) {
		return $_POST[$key];
	}

	return;
}

/**
 * Method untuk mendapatkan huruf berdasarkan nomornya
 *
 * @param  integer $index
 * @return string
 */
function get_abjad($index)
{
	$abjad = array(1 => 'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z');
	return $abjad[$index];
}

/**
 * Method untuk enkripsi url
 *
 * @param  string $current_url
 * @return string
 */
function enurl_redirect($current_url)
{
	return str_replace(array("%2F", "%5C"), array("%252F", "%255C"), urlencode($current_url));
}

/**
 * Method untuk deskripsi url
 *
 * @param  string $url
 * @return string
 */
function deurl_redirect($url)
{
	return urldecode(urldecode($url));
}

function pr($array)
{
	echo '<pre>';
	print_r($array);
	echo '</pre>';
}

function get_data_array($array, $index1, $index2)
{
	return $array[$index1][$index2];
}

/**
 * Fungsi untuk mendapatkan nama panggilan
 *
 * @param  string $str_nama
 * @return string
 */
function nama_panggilan($str_nama)
{
	$split = explode(" ", $str_nama);
	return $split[0];
}

/**
 * Method untuk mengaktifkan natif session
 * http://stackoverflow.com/questions/6249707/check-if-php-session-has-already-started
 */
function start_native_session()
{
	if (session_id() == '') {
		session_start();
	}
}

/**
 * Method untuk mendapatkan satu record tambahan
 *
 * @param  string $id
 * @return array
 */
function retrieve_field($id)
{
	return get_row_data('ConfigModel', 'retrieve_field', array('id' => $id));
}

/**
 * Method untuk update field tambahan
 *
 * @param  string $id
 * @param  string $nama
 * @param  string $value
 * @return boolean
 */
function update_field($id, $nama = null, $value = null)
{
	return get_row_data('ConfigModel', 'update_field', array($id, $nama, $value));
}

/**
 * Method untuk menghapus field tambahan berdasarkan id
 *
 * @param  string $id
 * @return boolean
 */
function delete_field($id)
{
	return get_row_data('ConfigModel', 'delete_field', array('id' => $id));
}

/**
 * Method untuk membuat field tambahan
 *
 * @param  string $id
 * @param  string $nama
 * @param  string $value
 * @return boolean
 */
function create_field($id, $nama = null, $value = null)
{
	return get_row_data('ConfigModel', 'create_field', array('id' => $id, 'nama' => $nama, 'value' => $value));
}


function get_kunci_pilihan($pilihan)
{
	foreach ($pilihan as $value) {
		if ($value['kunci'] == 1) {
			return $value['id'];
		}
	}
}

/**
 * Method untuk mendapatkan ip pengakses
 * @return string
 */
function get_ip()
{
	return $_SERVER['REMOTE_ADDR'];
}

/**
 * Method untuk ngecek apakah siswa sudah mengerjakan tugas tertentu
 *
 * @param  integer $tugas_id
 * @param  integer $siswa_id
 * @return boolean
 */
function sudah_ngerjakan($tugas_id, $siswa_id)
{
	$sudah = false;

	# cek history, kalo sudah ada berarti sudah mengerjakan
	$check_history = retrieve_field('history-mengerjakan-' . $siswa_id . '-' . $tugas_id);
	if (!empty($check_history)) {
		# hapus field mengerjakan supaya lebih memastikan
		$mengerjakan_field_id = 'mengerjakan-' . $siswa_id . '-' . $tugas_id;
		delete_field($mengerjakan_field_id);

		$sudah = true;
	}

	return $sudah;
}

/**
 * Method untuk mendapatkan lama pengerjaan berdasarkan waktu mulai dan selesai
 *
 * @param  string $start    2017-01-29 1:14:44
 * @param  string $finish
 * @param  string $format
 * @return string
 */
function lama_pengerjaan($start, $finish, $format = "%h jam %i menit %s detik")
{
	$date_a = new DateTime($start);
	$date_b = new DateTime($finish);

	$interval = date_diff($date_a, $date_b);

	$result  = $interval->format($format);
	$result  = str_replace(array("0 jam", " 0 menit", " 0 detik"), '', $result);

	return trim($result);
}

/**
 * Method untuk mendapatkan semua data email user yang berkedudukan sebagai admin
 *
 * @return array
 */
function get_email_admin()
{
	$results = array();

	$retrieve_all = get_row_data('login_model', 'retrieve_all', array(10, 1, 1, false));
	foreach ($retrieve_all as $login) {
		# cari pengajar
		$pengajar = get_row_data('pengajar_model', 'retrieve', array($login['pengajar_id']));
		if ($pengajar['status_id'] != 1) {
			continue;
		}

		$results[] = array(
			'nama'  => $pengajar['nama'],
			'email' => $login['username']
		);
	}

	return $results;
}

function test_send_email($to = "aris_prs@yahoo.com")
{
	# cari email
	// $template = get_pengaturan($nama_email, 'value');
	// $template = json_decode($template, 1);
	// if (empty($template)) {
	//     return false;
	// }

	// $arr_old = array();
	// $arr_new = array();
	// foreach ((array)$array_data as $key => $value) {
	//     $arr_old[] = '{$'.$key.'}';
	//     $arr_new[] = $value;
	// }

	$email_subject = 'test';
	$email_body    = 'Hallo, ini test';
	$email_server  = 'arispras@gmail.com';
	$nama_company  = 'Antech';

	$CI = &get_instance();
	$CI->email->clear(true);

	$config['mailtype'] = 'html';
	# cek pakai smtp tidak
	// $smtp_host = get_pengaturan('smtp-host', 'value');
	// $smtp_user = get_pengaturan('smtp-username', 'value');
	// $smtp_pass = get_pengaturan('smtp-pass', 'value');
	// $smtp_port = get_pengaturan('smtp-port', 'value');
	$smtp_host = 'ssl://smtp.googlemail.com';
	$smtp_port = 465;
	$smtp_user = 'arispras';
	$smtp_pass = 'yusi1978';
	if (!empty($smtp_host)) {
		$config['protocol']  = 'smtp';
		$config['smtp_host'] = $smtp_host;
		$config['smtp_user'] = $smtp_user;
		$config['smtp_pass'] = $smtp_pass;

		# cek port
		if (!empty($smtp_port)) {
			$config['smtp_port'] = $smtp_port;
		}
	}
	$CI->email->initialize($config);

	$CI->email->to($to);
	$CI->email->from($email_server, '[antech] - ' . $nama_company);
	$CI->email->subject($email_subject);
	$CI->email->message($email_body);
	$CI->email->send();
	$CI->email->clear(true);

	return true;
}
/**
 * Method untuk mengirimkan email
 *
 * @param  string $nama_email
 * @param  string $to
 * @param  array  $array_data
 * @return boolean
 */
function kirim_email($nama_email, $to, $array_data = array())
{
	# cari email
	$template = get_pengaturan($nama_email, 'value');
	$template = json_decode($template, 1);
	if (empty($template)) {
		return false;
	}

	$arr_old = array();
	$arr_new = array();
	foreach ((array)$array_data as $key => $value) {
		$arr_old[] = '{$' . $key . '}';
		$arr_new[] = $value;
	}

	$email_subject = str_replace($arr_old, $arr_new, $template['subject']);
	$email_body    = str_replace($arr_old, $arr_new, $template['body']);
	$email_server  = get_pengaturan('email-server', 'value');
	$nama_company  = get_pengaturan('nama-company', 'value');

	$CI = &get_instance();
	$CI->email->clear(true);

	$config['mailtype'] = 'html';
	# cek pakai smtp tidak
	$smtp_host = get_pengaturan('smtp-host', 'value');
	$smtp_user = get_pengaturan('smtp-username', 'value');
	$smtp_pass = get_pengaturan('smtp-pass', 'value');
	$smtp_port = get_pengaturan('smtp-port', 'value');
	if (!empty($smtp_host)) {
		$config['protocol']  = 'smtp';
		$config['smtp_host'] = $smtp_host;
		$config['smtp_user'] = $smtp_user;
		$config['smtp_pass'] = $smtp_pass;

		# cek port
		if (!empty($smtp_port)) {
			$config['smtp_port'] = $smtp_port;
		}
	}
	$CI->email->initialize($config);

	$CI->email->to($to);
	$CI->email->from($email_server, '[antech] - ' . $nama_company);
	$CI->email->subject($email_subject);
	$CI->email->message($email_body);
	$CI->email->send();
	$CI->email->clear(true);

	return true;
}

/**
 * Method untuk mengirimkan email approve siswa
 *
 * @param  string $siswa_id
 */
function kirim_email_approve_siswa($siswa_id)
{
	$retrieve_siswa = get_row_data('siswa_model', 'retrieve', array($siswa_id));
	$login = get_row_data('login_model', 'retrieve', array(null, null, null, $siswa_id));

	$tabel_profil = '<table border="1" cellspacing="0" cellpadding="5">
        <tr>
            <td valign="top">NIS</td>
            <td>' . $retrieve_siswa['nis'] . '</td>
        </tr>
        <tr>
            <td valign="top">Nama</td>
            <td>' . $retrieve_siswa['nama'] . '</td>
        </tr>
        <tr>
            <td valign="top">Jenis kelamin</td>
            <td>' . $retrieve_siswa['jenis_kelamin'] . '</td>
        </tr>
        <tr>
            <td valign="top">Tempat lahir</td>
            <td>' . $retrieve_siswa['tempat_lahir'] . '</td>
        </tr>
        <tr>
            <td valign="top">Tgl. Lahir</td>
            <td>' . tgl_indo($retrieve_siswa['tgl_lahir']) . '</td>
        </tr>
        <tr>
            <td valign="top">Alamat</td>
            <td>' . $retrieve_siswa['alamat'] . '</td>
        </tr>
    </table>';

	@kirim_email('email-template-approve-siswa', $login['username'], array(
		// 'nama'         => $nama,
		'nama_company' => get_pengaturan('nama-company', 'value'),
		'tabel_profil' => $tabel_profil,
		'url_login'    => site_url('login')
	));
}

/**
 * Method untuk mengirimkan email approve pengajar
 *
 * @param  integer $pengajar_id
 */
function kirim_email_approve_pengajar($pengajar_id)
{
	$pengajar = get_row_data('pengajar_model', 'retrieve', array($pengajar_id));
	$login    = get_row_data('login_model', 'retrieve', array(null, null, null, null, $pengajar_id));

	$tabel_profil = '<table border="1" cellspacing="0" cellpadding="5">
        <tr>
            <td valign="top">NIP</td>
            <td>' . $pengajar['nip'] . '</td>
        </tr>
        <tr>
            <td valign="top">Nama</td>
            <td>' . $pengajar['nama'] . '</td>
        </tr>
        <tr>
            <td valign="top">Jenis kelamin</td>
            <td>' . $pengajar['jenis_kelamin'] . '</td>
        </tr>
        <tr>
            <td valign="top">Tempat lahir</td>
            <td>' . $pengajar['tempat_lahir'] . '</td>
        </tr>
        <tr>
            <td valign="top">Tgl. Lahir</td>
            <td>' . tgl_indo($pengajar['tgl_lahir']) . '</td>
        </tr>
        <tr>
            <td valign="top">Alamat</td>
            <td>' . $pengajar['alamat'] . '</td>
        </tr>
    </table>';

	@kirim_email('email-template-approve-pengajar', $login['username'], array(
		// 'nama'         => $nama,
		'nama_company' => get_pengaturan('nama-company', 'value'),
		'tabel_profil' => $tabel_profil,
		'url_login'    => site_url('login')
	));
}


/**
 * Method untuk mendapatkan email dari string
 *
 * @param  string $str
 * @return array
 */
function get_email_from_string($str)
{
	$pattern = '/[a-z\d._%+-]+@[a-z\d.-]+\.[a-z]{2,4}\b/i';

	preg_match_all($pattern, $str, $results);

	return $results[0];
}


/**
 * http://stackoverflow.com/questions/3475646/undefined-date-diff
 */
if (!function_exists('date_diff')) {
	function date_diff($date1, $date2)
	{
		$current = $date1;
		$datetime2 = date_create($date2);
		$count = 0;
		while (date_create($current) < $datetime2) {
			$current = gmdate("Y-m-d", strtotime("+1 day", strtotime($current)));
			$count++;
		}
		return $count;
	}
}

/**
 * Method untuk mendapatkan data dari url
 *
 * @param  string $url
 * @return string body
 */
function get_url_data($url)
{
	// # jika curl hidup
	// if (function_exists('curl_version')) {
	//     $ch = curl_init($url);
	//     curl_setopt($ch, CURLOPT_HEADER, 1);
	//     curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	//     curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

	//     curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
	//     $response    = curl_exec($ch);
	//     $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
	//     $header      = substr($response, 0, $header_size);
	//     $body        = substr($response, $header_size);
	//     $code        = curl_getinfo($ch, CURLINFO_HTTP_CODE);
	//     curl_close($ch);
	// } else {

	// }
	$body = file_get_contents($url);

	return $body;
}


/**
 * Method untuk menyimpan session tampilkan atau sembunyikan timeer saat ujian
 *
 * @param  string $act
 * @param  string $tugas_id
 * @param  string $hide
 */
function sess_hide_countdown($act, $tugas_id = "", $hide = "")
{
	$CI = &get_instance();
	$sess_name = 'hide_countdown';
	$currents  = $CI->session->userdata($sess_name);

	switch ($act) {
		case 'set':
			$currents[$tugas_id] = $hide;
			$CI->session->set_userdata($sess_name, $currents);
			break;

		case 'get':
			return !empty($currents[$tugas_id]) ? 1 : 0;
			break;
	}
}

/**
 * Method untuk ngecek tgljam tertentu sudah lewat sehari belum
 * @param  string $datetime
 * @return boolean
 */
function belum_sehari($datetime)
{
	$sekarang       = strtotime(date("Y-m-d H:i:s"));
	$sehari_yg_lalu = strtotime("-1 day", $sekarang);

	if (strtotime($datetime) > $sehari_yg_lalu) {
		return true;
	}

	return false;
}

/**
 * Untuk menciptakan datetime format ISO8601
 * @param  string $datetime
 * @return string
 */
function iso8601($datetime)
{
	return date(DateTime::ISO8601, strtotime($datetime));
}

/**
 * Method untuk mengambil fungsi autoload plugin
 */
function autoload_function_plugin()
{
	# ambil semua folder didalam src
	$base_load = './plugins/src';
	if (!is_dir($base_load)) {
		return true;
	}

	$objects = scandir($base_load);
	foreach ($objects as $object) {
		if ($object != "." && $object != "..") {
			$autoload_file = $base_load . '/' . $object . '/autoload.php';
			if (is_file($autoload_file)) {
				include_once $autoload_file;

				$autoload_function = "autoload_{$object}";
				if (function_exists($autoload_function)) {
					$autoload_function();
				}
			}
		}
	}

	return true;
}



function convert_year_month($yyyymm)
{
	$yyyy=substr($yyyymm,0,4);
	$mm=substr($yyyymm,4,2);
	$arr_month = array('01' => 'Jan', '02' => 'Feb', '03' => 'Mar', '04' => 'Apr', '05' => 'May', '06' => 'Jun', '07' => 'Jul', '08' => 'Aug', '09' => 'Sep', '10' => 'Oct', '11' => 'Nov', '12' => 'Dec');
    return $arr_month[$mm]." ". $yyyy;
	
}
function convert_month($mm)
{
	$m=sprintf("%02s", $mm);
	$arr_month = array('01' => 'Jan', '02' => 'Feb', '03' => 'Mar', '04' => 'Apr', '05' => 'May', '06' => 'Jun', '07' => 'Jul', '08' => 'Aug', '09' => 'Sep', '10' => 'Oct', '11' => 'Nov', '12' => 'Dec');
    return $arr_month[$m];
	
}
function convert_month_romawi($mm)
{
	$m=sprintf("%02s", $mm);
	$arr_month = array('01' => 'I', '02' => 'II', '03' => 'III', '04' => 'IV', '05' => 'V', '06' => 'VI', '07' => 'VII', '08' => 'VIII', '09' => 'IX', '10' => 'X', '11' => 'XI', '12' => 'XII');
    return $arr_month[$m];
	
}

function convert_date($date, $state = false)
{
	$filter_date = array('Jan' => '01', 'Feb' => '02', 'Mar' => '03', 'Apr' => '04', 'May' => '05', 'Jun' => '06', 'Jul' => '07', 'Aug' => '08', 'Sep' => '09', 'Oct' => '10', 'Nov' => '11', 'Dec' => '12');

	if ($state == false) {
		$date = explode(' ', $date);
		foreach ($filter_date as $x => $xx) {
			if ($date[0] == $x) {
				$date[0] = $xx;
			}
		}
		$date = implode('-', array_reverse($date));
		return $date;
	} else {
		$date = explode('-', $date);
		$date = array_reverse($date);
		if (count($date) == 3) {
			unset($date[0]);
			$date = array_values($date);
		}
		foreach ($filter_date as $x => $xx) {
			if ($date[0] == $xx) {
				$date[0] = $x;
			}
		}
		$date = implode(' ', $date);
		return $date;
	}
}


function cek_periode($tgl,$lokasi_id)
	{
		$hasil_cek = false; ///
		$message = ""; ///

		$CI =   &get_instance();
		$cek = $CI->db->query("SELECT * FROM acc_periode_akunting  WHERE '".$tgl."' between tgl_awal and tgl_akhir and lokasi_id=".$lokasi_id." ")->row_array();
		if (!empty($cek['nama'])) {
			if ($cek['status']=='1'){
				$hasil_cek = false;
				$message="Periode Akunting Sudah Ditutup"; 
			}else{
				$hasil_cek = true;
				$message=""; 

			}
			
		} else {
			$hasil_cek = false;
			$message="Periode Akunting tidak ada"; 
		}
		
		$ret=array();
		$ret=array("status"=>$hasil_cek,"message"=>$message);
		return $ret;
	}

	function cek_periode_by_id($id)
	{
		$hasil_cek = false; ///
		$message = ""; ///

		$CI =   &get_instance();
		$cek = $CI->db->query("SELECT * FROM acc_periode_akunting  WHERE id= ".$id." ")->row_array();
		if (!empty($cek['nama'])) {
			if ($cek['status']=='1'){
				$hasil_cek = false;
				$message="Periode Akunting Sudah Ditutup"; 
			}else{
				$hasil_cek = true;
				$message=""; 

			}
			
		} else {
			$hasil_cek = false;
			$message="Periode Akunting tidak ada"; 
		}
		
		$ret=array();
		$ret=array("status"=>$hasil_cek,"message"=>$message);
		return $ret;
	}

function	curl_get_content($url){
		
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4 );
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_ENCODING, '');
		curl_setopt($ch, CURLOPT_POSTREDIR, CURL_REDIR_POST_ALL);
		$output = curl_exec($ch);
		curl_close($ch);
		return $output;

	}
	function get_company()
	{
		$company = array();
		$logo = file_get_contents('./logo_perusahaan.png');
		$logo = base64_encode($logo); 
		
		$company['logo'] = $logo;
		$company['nama'] = "PT SAHABAT";
		$company['alamat'] = "Bogor";
		$company['telp'] = "-";
		return $company;
	}