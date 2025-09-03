<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

use PhpOffice\PhpSpreadsheet\Shared\Date;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Carbon\Carbon;
use Restserver\Libraries\REST_Controller;

class AccJurnal extends BD_Controller //Rest_Controller
{
	public $user_id;
	public $theCredential;
	public $data = array();
	function __construct()
	{
		parent::__construct();
		date_default_timezone_set("Asia/Jakarta");
		$this->load->model('GbmOrganisasiModel');
		$this->load->model('AccJurnalModel');
		$this->load->model('M_DatatablesModel');
		$this->load->library('pdfgenerator');
		$this->load->library('image_lib');
		$this->load->library('upload');
		$this->auth();
		$this->theCredential = $this->user_data;
		$this->user_id = $this->user_data->id;
	}
	public function list_post()
	{
		$post = $this->post();

		$query  = "SELECT a.*,b.nama as lokasi FROM `acc_jurnal_ht` a inner join gbm_organisasi b on a.lokasi_id=b.id
		";
		$search = array('a.no_jurnal', 'a.tanggal', 'b.nama', 'a.keterangan');
		$where  = null;

		$isWhere = "a.tipe_jurnal='UMUM'";


		$data = $this->M_DatatablesModel->get_tables_query($query, $search, $where, $isWhere, $post);
		$this->set_response($data, REST_Controller::HTTP_OK);
	}

	function index_get($id = '')
	{
		$retrieve = array();
		$retrieve = $this->AccJurnalModel->retrieve_by_id($id);
		$retrieve_detail = $this->AccJurnalModel->retrieve_detail($id);
		//$detail = array();
		// foreach ($retrieve_detail as $key => $value) {
		// 	$dtl = array();
		// 	$retrieve_denda = $this->AccJurnalModel->retrieve_detail_denda($value['id']);
		// 	$dtl = $value;
		// 	$dtl['denda'] = array();
		// 	foreach ($retrieve_denda as $key1 => $value_denda) {
		// 		$dtl['denda'][] = $value_denda;
		// 	}
		// 	$detail[] = $dtl;
		// }

		$retrieve['detail'] = $retrieve_detail;

		if (!empty($retrieve)) {
			$this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
	}

	function getDetail_get($id = '')
	{
		$retrieve = $this->AccJurnalModel->retrieve_detail($id);
		if (!empty($retrieve)) {
			$this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
	}

	function index_post()
	{
		$input = $this->post();
		//$input['no_spat'] = $this->getLastNumber('pks_pengolahan_ht', 'no_spat', 'SPAT');
		// var_dump($input);

		$res = $this->AccJurnalModel->create($input);
		if (!empty($res)) {
			$this->set_response(array("status" => "OK", "data" => $res), REST_Controller::HTTP_CREATED);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
	}

	function index_put($segment_3 = null)
	{
		$id = (int)$segment_3;
		$data = $this->put();

		$res = $this->AccJurnalModel->update($id, $data);
		if (!empty($res)) {
			$this->set_response(array("status" => "OK", "data" => $res), REST_Controller::HTTP_CREATED);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
	}
	public function index_delete($id)
	{

		$res = $this->AccJurnalModel->delete($id);
		if (!empty($res)) {
			$this->set_response(array("status" => "OK", "data" => $res), REST_Controller::HTTP_CREATED);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
	}
	function posting_post($segment_3 = null)
	{
		$id = (int)$segment_3;
		$data = $this->post();

		$res = $this->AccJurnalModel->posting($id, $data);
		if (!empty($res)) {
			$this->set_response(array("status" => "OK", "data" => $res), REST_Controller::HTTP_CREATED);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
	}
	function get_path_file($img = '', $size = '')
	{
		if (empty($size)) {
			return  $_SERVER['DOCUMENT_ROOT'] . "/"  . 'hcis_folder' . '/userfiles/files/' . $img;
		} else {
			$pisah = explode('.', $img);
			$ext = end($pisah);
			$nama_file = $pisah[0];

			return  $_SERVER['DOCUMENT_ROOT'] . "/"  . 'hcis_folder' . '/userfiles/files/' . $nama_file . '_' . $size . '.' . $ext;
		}
	}
	function import_detail_post()
	{
		
		$config['upload_path']   = $this->get_path_file();
		$config['allowed_types'] = 'Xls|xls';
		// $config['max_size']      = '0';
		// $config['max_width']     = '0';
		// $config['max_height']    = '0';
		$config['overwrite'] = true;
		$config['file_name']     = 'import_jurnal.xls';
		$this->upload->initialize($config);
		
		if ($this->upload->do_upload()) {
			$upload_data = $this->upload->data();
			$filename = $upload_data['file_name'];

			$excel = array();
			$excel = $this->import($config['upload_path'] . '/' . $filename);
			$arrDetail = array();
			$arrDetail = [];
			if (count($excel) > 0) {
				for ($i = 2; $i < (count($excel) + 1); $i++) {
					$data = $excel[$i];
					if (!empty($data['A'])) {
						$kode_lokasi          =  $data['A'];
						$ket = $data['B'];
						$kode_akun         =  $data['C'];
						$debet =  $data['D'];
						$kredit  =  $data['E'];

						$lokasi = $this->db->query("select * from gbm_organisasi where kode='" . $kode_lokasi . "'")->row_array();
						$lokasi_id = $lokasi['id'];
						$akun = $this->db->query("select * from acc_akun  where kode='" . $kode_akun . "'")->row_array();
						$akun_id = $akun['id'];
						$arrDetail[] = array(
							"acc_akun_id" => $akun_id,
							"lokasi_id" => $lokasi_id,
							"ket" => $ket,
							"debet" => $debet,
							"kredit" => $kredit,
						);
					}
				}
			}
			// var_dump($excel);
			//  exit();
			// $this->set_response([
			// 	'status' => 'OK',
			// 	'message' => 'Data berhasil diimport',
			// ], REST_Controller::HTTP_CREATED);
			$this->set_response(array("status" => "OK", "data" => $arrDetail), REST_Controller::HTTP_OK);
		} else {
			if (!empty($_FILES['userfile']['tmp_name'])) {
				$this->set_response([
					'status' => 'NOT OK',
					'message' => 'Gagal import' ,
					'data'=>$this->upload->display_errors(),
				], REST_Controller::HTTP_OK);
			}
		}
	}
	function import($path_file)
	{
		// $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx(); //Excel 2007 or higher
		$reader = new \PhpOffice\PhpSpreadsheet\Reader\Xls(); //Excel 2003
		$spreadsheet = $reader->load($path_file);
		$sheetData = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);
		return $sheetData;
	}
	function print_slip_get($segment_3 = '')
	{


		$id = (int)$segment_3;
		$data = [];

		$hd = $this->db->query("select a.*
		from acc_jurnal_ht a  where a.id=" . $id)->row_array();
		$data['hd'] = $hd;

		$dt = $this->db->query("select a.*, b.kode as kode_akun,b.nama as nama_akun from acc_jurnal_dt a left join
		acc_akun b on a.acc_akun_id=b.id where a.jurnal_id=" . $id)->result_array();
		$data['dt'] = $dt;

		$html = $this->load->view('AccSlipJurnal', $data, true);

		$filename = 'AccSlipJurnal_' . time();
		$this->pdfgenerator->generate($html, $filename, true, 'A4', 'portrait');
		echo $html;
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


	function getLastNumber($table_name = '', $field = '', $prefix = '')
	{
		$lastnumber = $this->db->query("select  max(" . $field . ")as last from " . $table_name . "")->row_array();
		// var_dump($lastnumber);exit();
		if (!empty($lastnumber['last'])) {
			$str = (substr($lastnumber['last'], -6));
			$snumber = (int)$str + 1;
		} else {
			$snumber = 1;
		}
		$strnumber = sprintf("%06s", $snumber);
		return  $prefix . $strnumber;
		// $index = 11;
		// $prefix = 'B';
		// echo sprintf("%s%011s", $prefix, $index);


	}


	/* Neraca saldo Mutasi */
	function laporan_neraca_saldo_mutasi_post()
	{

		$versi_laporan =  $this->post('versi_laporan', true);
		if ($versi_laporan == 'v1') {
			//  ALL
			$this->laporan_neraca_saldo_mutasi_all();
		} else if ($versi_laporan == 'v2') {
			// INTI
			$this->laporan_neraca_saldo_mutasi_inti();
		} else if ($versi_laporan == 'v3') {
			// PLASMA
			$this->laporan_neraca_saldo_mutasi_plasma();
		}
	}
	/* Neraca saldo  */
	function laporan_neraca_saldo_post()
	{
		$this->laporan_neraca_saldo_v1();
	}
	// function laporan_neraca_saldo_inti_post()
	// {

	// 	$versi_laporan =  $this->post('versi_laporan', true);
	// 	if ($versi_laporan == 'v1') {
	// 		$this->laporan_neraca_saldo_inti_v1();
	// 	} else {
	// 		$this->laporan_neraca_saldo_inti_v2();
	// 	}
	// }
	function laporan_neraca_saldo_v1()
	{

		$format_laporan =  $this->post('format_laporan', true);
		$lokasi_id     = $this->post('lokasi_id', true);
		$tanggal_mulai =  $this->post('tgl_mulai', true);
		$tanggal_akhir =  $this->post('tgl_akhir', true);

		$akun_dari = $this->post('akun_dari', true);
		$akun_sampai = $this->post('akun_sampai', true);

		$akun_1 = $this->db->query("select * from acc_akun where id=" . $akun_dari . "")->row_array();
		$akun_2 = $this->db->query("select * from acc_akun where id=" . $akun_sampai . "")->row_array();

		$judulLokasi = 'Semua';
		if ($lokasi_id) {
			$lokasi   = $this->db->query("select * from gbm_organisasi where id=" . $lokasi_id . "")->row_array();
			$judulLokasi = $lokasi['nama'];

			/* Hanya akun lokasi bersangkutan yg muncul */
			// $queryAkun = "select a.* from acc_akun a inner join 
			// acc_akun_dt b on a.id=b.acc_akun_id
			// 	where is_transaksi_akun=1 
			// 	  and (kode between '" . $akun_1['kode']  . "' and '" . $akun_2['kode'] . "')  
			// 	  and b.lokasi_id=" . $lokasi_id . "
			// 	  order by kode ";
			$queryAkun = "select * from acc_akun
       			where is_transaksi_akun=1 
				  and (kode between '" . $akun_1['kode']  . "' and '" . $akun_2['kode'] . "')  
				   order by kode ";
		} else {
			$queryAkun = "select * from acc_akun
       			where is_transaksi_akun=1 
				  and (kode between '" . $akun_1['kode']  . "' and '" . $akun_2['kode'] . "')  
				   order by kode ";
		}



		$res = array();
		$akuns   = $this->db->query($queryAkun)->result_array();
		foreach ($akuns as $key => $akun) {
			if ($lokasi_id) {
				$querySaldoAwal = "SELECT b.lokasi_id,b.acc_akun_id,sum(debet-kredit)as saldo FROM acc_jurnal_ht a inner join acc_jurnal_dt b 
				on a.id=b.jurnal_id
				where b.acc_akun_id=" . $akun['id'] . " and  b.lokasi_id=" . $lokasi_id . " and a.tanggal < '" . $tanggal_mulai . "'
				group by b.lokasi_id,b.acc_akun_id ;";
			} else {
				$querySaldoAwal = "SELECT b.acc_akun_id,sum(debet-kredit)as saldo FROM acc_jurnal_ht a inner join acc_jurnal_dt b 
				on a.id=b.jurnal_id
				where b.acc_akun_id=" . $akun['id'] . " and a.tanggal < '" . $tanggal_mulai . "'
				group by b.acc_akun_id ;";
			}
			$awal   = $this->db->query($querySaldoAwal)->row_array();
			$akun['saldo_awal'] = (!empty($awal)) ? $awal['saldo'] : 0;
			if ($lokasi_id) {
				$query1 = "SELECT b.lokasi_id,b.acc_akun_id,sum(debet)as debet,sum(kredit)as kredit FROM acc_jurnal_ht a inner join acc_jurnal_dt b 
				on a.id=b.jurnal_id
				where b.acc_akun_id=" . $akun['id'] . " and  b.lokasi_id=" . $lokasi_id . "
				and a.tanggal >= '" . $tanggal_mulai . "'  and a.tanggal <= '" . $tanggal_akhir . "'
				group by b.lokasi_id,b.acc_akun_id ;";
			} else {
				$query1 = "SELECT b.acc_akun_id,sum(debet)as debet,sum(kredit)as kredit FROM acc_jurnal_ht a inner join acc_jurnal_dt b 
				on a.id=b.jurnal_id
				where b.acc_akun_id=" . $akun['id'] . " 
				and a.tanggal >= '" . $tanggal_mulai . "'  and a.tanggal <= '" . $tanggal_akhir . "'
				group by b.acc_akun_id ;";
			}

			$resD  = $this->db->query($query1)->row_array();
			$akun['debet'] = (!empty($resD['debet'])) ? $resD['debet'] : 0;
			$akun['kredit'] = (!empty($resD['kredit'])) ? $resD['kredit'] : 0;
			$res[] = $akun;
		}

		$this->load->helper("terbilang");
		$this->load->helper("antech_helper");
		$this->load->library('pdfgenerator');
		if ($format_laporan == 'pdf') {
			$html = get_header_pdf_report();
		} else {
			$html = get_header_report_v2();
		}

		$html = $html . '<div class="row">
  <div class="span12">
	  <br>
	<div class="kop-print">
    <div class="kop-nama">PT. ANNAJAH TECHNOLOGY INDONESIA</div>
    <div class="kop-info"> Komplek Vila Sehati B12 , Tapos - Depok</div>
    <div class="kop-info">Telp : 081387373939</div>
  </div>
	  <hr class="kop-print-hr">
  </div>
  </div>
  <h3 class="title">TRIAL BALANCE</h3>
  <table class="no_border" style="width:30%">
			
			<tr>
					<td>Lokasi</td>
					<td>:</td>
					<td>' . $judulLokasi . '</td>
			</tr>
			<tr>	
					<td>Periode</td>
					<td>:</td>
					<td>' . $tanggal_mulai . ' s/d ' . $tanggal_akhir . '</td>
			</tr>
			<tr>	
					<td>Akun</td>
					<td>:</td>
					<td>' . $akun_1['kode'] . ' s/d ' . $akun_2['kode'] . '</td>
			</tr>

			
	</table>
	<br>
  ';
		$html = $html . ' <table  border="1" width="100%" style="border-collapse: collapse;" >
			<thead>
				<tr>
					<th rowspan="2" width="4%">No.</th>
					<th rowspan="2">Kode</th>
					<th rowspan="2">Nama</th>
					<th rowspan="2" style="text-align: center;">Saldo awal</th>
					<th colspan ="2" style="text-align: center;">Transaksi</th>
					<th rowspan="2" style="text-align: center;">Saldo Akhir</th>			
				</tr>
				<tr>
					<th  style="text-align: right;">Dr</th>
					<th  style="text-align: right;">Cr</th>
			
				</tr>
			</thead>
			<tbody>';


		$no = 0;
		$jumlah = 0;
		$tot_saldo_awal = 0;
		$tot_saldo_akhir = 0;
		$tot_debet = 0;
		$tot_kredit = 0;

		foreach ($res as $key => $m) {
			$no++;
			$jumlah = $m['saldo_awal'] + $m['debet'] - $m['kredit'];
			$tot_saldo_akhir = $tot_saldo_akhir + $jumlah;
			$tot_saldo_awal = $tot_saldo_awal +  $m['saldo_awal'];
			$tot_debet = $tot_debet +  $m['debet'];
			$tot_kredit = $tot_kredit +  $m['kredit'];
			$actual_link = "http://$_SERVER[HTTP_HOST]" . "/klinik_api/api/GlobalReport/acc_laporan_buku_besar/" . $tanggal_mulai . "/" . $tanggal_akhir .  "/" . $m['id'] .  "/" . $lokasi_id . "";
			$html = $html . ' 	<tr class=":: arc-content">
						<td style="position:relative;">
							' . ($no) . '

						</td>
						<td><a href="' . $actual_link  . '" target="_blank"> ' . $m['kode'] . ' </a>
						</td>
						<td><a href="' . $actual_link  . '" target="_blank"> ' . $m['nama'] . ' </a>
						</td>
											
						<td style="text-align: right;">' . $this->format_number_report($m['saldo_awal']) . ' 
						<td style="text-align: right;">' . $this->format_number_report($m['debet']) . ' 
						<td style="text-align: right;">' . $this->format_number_report($m['kredit']) . ' 
						<td style="text-align: right;">' . $this->format_number_report($jumlah) . ' 
						
						</td>';

			$html = $html . '
						
						
					</tr>';
		}

		$html = $html . ' 	
						<tr class=":: arc-content">
						<td style="position:relative;">
							&nbsp;

						</td>
						
						
						<td>
							&nbsp;
						</td>


						<td style="text-align: right;">
							
						</td>
						<td style="text-align: right;">
						' . $this->format_number_report($tot_saldo_awal) . ' 
						</td>
						<td style="text-align: right;">
						' . $this->format_number_report($tot_debet) . ' 
						</td>
						<td style="text-align: right;">
						' . $this->format_number_report($tot_kredit) . ' 
						</td>
						
						<td style="text-align: right;">
						' . $this->format_number_report($tot_saldo_akhir) . ' 
						</td>
						
						</tr>
								</tbody>
							</table>
						';
		// $filename = 'report_' . time();
		// $this->pdfgenerator->generate($html, $filename, true, 'A4', 'landscape');
		if ($format_laporan == 'xls') {
			$reader = new \PhpOffice\PhpSpreadsheet\Reader\Html;
			$spreadsheet = $reader->loadFromString($html);
			// $reader->setSheetIndex(1);
			//$spreadhseet = $reader->loadFromString($secondHtmlString, $spreadsheet);
			$objWriter = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
			header("Pragma: public");
			header("Expires: 0");
			header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
			header("Content-Type: application/force-download");
			header("Content-Type: application/octet-stream");
			header("Content-Type: application/download");
			header("Content-Disposition: attachment;filename=test.xlsx");
			header("Content-Transfer-Encoding: binary ");

			ob_end_clean();
			ob_start();
			$objWriter->save('php://output');
		} else if ($format_laporan == 'view') {
			echo $html;
		} else {
			$filename = 'report_' . time();
			$this->pdfgenerator->generate($html, $filename, true, 'A4', 'landscape');
		}
	}
	function laporan_neraca_saldo_inti_v1()
	{

		$format_laporan =  $this->post('format_laporan', true);
		$lokasi_id     = $this->post('lokasi_id', true);
		$tanggal_mulai =  $this->post('tgl_mulai', true);
		$tanggal_akhir =  $this->post('tgl_akhir', true);

		$akun_dari = $this->post('akun_dari', true);
		$akun_sampai = $this->post('akun_sampai', true);

		$akun_1 = $this->db->query("select * from acc_akun where id=" . $akun_dari . "")->row_array();
		$akun_2 = $this->db->query("select * from acc_akun where id=" . $akun_sampai . "")->row_array();

		$judulLokasi = 'Semua';
		if ($lokasi_id) {
			$lokasi   = $this->db->query("select * from gbm_organisasi where id=" . $lokasi_id . "")->row_array();
			$judulLokasi = $lokasi['nama'];

			/* Hanya akun lokasi bersangkutan yg muncul */
			// $queryAkun = "select a.* from acc_akun a inner join 
			// acc_akun_dt b on a.id=b.acc_akun_id
			// 	where is_transaksi_akun=1 
			// 	  and (kode between '" . $akun_1['kode']  . "' and '" . $akun_2['kode'] . "')  
			// 	  and b.lokasi_id=" . $lokasi_id . "
			// 	  order by kode ";
			$queryAkun = "select * from acc_akun
       			where is_transaksi_akun=1 
				  and (kode between '" . $akun_1['kode']  . "' and '" . $akun_2['kode'] . "')  
				   order by kode ";
		} else {
			$queryAkun = "select * from acc_akun
       			where is_transaksi_akun=1 
				  and (kode between '" . $akun_1['kode']  . "' and '" . $akun_2['kode'] . "')  
				  and kode<>'SBME'
				   order by kode ";
		}



		$res = array();
		$akuns   = $this->db->query($queryAkun)->result_array();
		foreach ($akuns as $key => $akun) {
			if ($lokasi_id) {
				$querySaldoAwal = "SELECT b.lokasi_id,b.acc_akun_id,sum(debet-kredit)as saldo FROM acc_jurnal_ht a inner join acc_jurnal_dt b 
				on a.id=b.jurnal_id
				where b.acc_akun_id=" . $akun['id'] . " and  b.lokasi_id=" . $lokasi_id . " and a.tanggal < '" . $tanggal_mulai . "'
				group by b.lokasi_id,b.acc_akun_id ;";
			} else {
				$querySaldoAwal = "SELECT b.acc_akun_id,sum(debet-kredit)as saldo FROM acc_jurnal_ht a inner join acc_jurnal_dt b 
				on a.id=b.jurnal_id
				where b.acc_akun_id=" . $akun['id'] . " and a.tanggal < '" . $tanggal_mulai . "'
				group by b.acc_akun_id ;";
			}
			$awal   = $this->db->query($querySaldoAwal)->row_array();
			$akun['saldo_awal'] = (!empty($awal)) ? $awal['saldo'] : 0;
			if ($lokasi_id) {
				$query1 = "SELECT b.lokasi_id,b.acc_akun_id,sum(debet)as debet,sum(kredit)as kredit FROM acc_jurnal_ht a inner join acc_jurnal_dt b 
				on a.id=b.jurnal_id
				where b.acc_akun_id=" . $akun['id'] . " and  b.lokasi_id=" . $lokasi_id . "
				and a.tanggal >= '" . $tanggal_mulai . "'  and a.tanggal <= '" . $tanggal_akhir . "'
				group by b.lokasi_id,b.acc_akun_id ;";
			} else {
				$query1 = "SELECT b.acc_akun_id,sum(debet)as debet,sum(kredit)as kredit FROM acc_jurnal_ht a inner join acc_jurnal_dt b 
				on a.id=b.jurnal_id
				where b.acc_akun_id=" . $akun['id'] . " 
				and a.tanggal >= '" . $tanggal_mulai . "'  and a.tanggal <= '" . $tanggal_akhir . "'
				group by b.acc_akun_id ;";
			}

			$resD  = $this->db->query($query1)->row_array();
			$akun['debet'] = (!empty($resD['debet'])) ? $resD['debet'] : 0;
			$akun['kredit'] = (!empty($resD['kredit'])) ? $resD['kredit'] : 0;
			$res[] = $akun;
		}

		$this->load->helper("terbilang");
		$this->load->helper("antech_helper");
		$this->load->library('pdfgenerator');
		if ($format_laporan == 'pdf') {
			$html = get_header_pdf_report();
		} else {
			$html = get_header_report_v2();
		}

		$html = $html . '<div class="row">
  <div class="span12">
	  <br>
	<div class="kop-print">
    <div class="kop-nama">PT. ANNAJAH TECHNOLOGY INDONESIA</div>
    <div class="kop-info"> Komplek Vila Sehati B12 , Tapos - Depok</div>
    <div class="kop-info">Telp : 081387373939</div>
  </div>
	  <hr class="kop-print-hr">
  </div>
  </div>
  <h3 class="title">TRIAL BALANCE</h3>
  <table class="no_border" style="width:30%">
			
			<tr>
					<td>Lokasi</td>
					<td>:</td>
					<td>' . $judulLokasi . '</td>
			</tr>
			<tr>	
					<td>Periode</td>
					<td>:</td>
					<td>' . $tanggal_mulai . ' s/d ' . $tanggal_akhir . '</td>
			</tr>
			<tr>	
					<td>Akun</td>
					<td>:</td>
					<td>' . $akun_1['kode'] . ' s/d ' . $akun_2['kode'] . '</td>
			</tr>

			
	</table>
	<br>
  ';
		$html = $html . ' <table  border="1" width="100%" style="border-collapse: collapse;" >
			<thead>
				<tr>
					<th rowspan="2" width="4%">No.</th>
					<th rowspan="2">Kode</th>
					<th rowspan="2">Nama</th>
					<th rowspan="2" style="text-align: center;">Saldo awal</th>
					<th colspan ="2" style="text-align: center;">Transaksi</th>
					<th rowspan="2" style="text-align: center;">Saldo Akhir</th>			
				</tr>
				<tr>
					<th  style="text-align: right;">Dr</th>
					<th  style="text-align: right;">Cr</th>
			
				</tr>
			</thead>
			<tbody>';


		$no = 0;
		$jumlah = 0;
		$tot_saldo_awal = 0;
		$tot_saldo_akhir = 0;
		$tot_debet = 0;
		$tot_kredit = 0;

		foreach ($res as $key => $m) {
			$no++;
			$jumlah = $m['saldo_awal'] + $m['debet'] - $m['kredit'];
			$tot_saldo_akhir = $tot_saldo_akhir + $jumlah;
			$tot_saldo_awal = $tot_saldo_awal +  $m['saldo_awal'];
			$tot_debet = $tot_debet +  $m['debet'];
			$tot_kredit = $tot_kredit +  $m['kredit'];
			$actual_link = "http://$_SERVER[HTTP_HOST]" . "/klinik_api/api/GlobalReport/acc_laporan_buku_besar/" . $tanggal_mulai . "/" . $tanggal_akhir .  "/" . $m['id'] .  "/" . $lokasi_id . "";
			$html = $html . ' 	<tr class=":: arc-content">
						<td style="position:relative;">
							' . ($no) . '

						</td>
						<td><a href="' . $actual_link  . '" target="_blank"> ' . $m['kode'] . ' </a>
						</td>
						<td><a href="' . $actual_link  . '" target="_blank"> ' . $m['nama'] . ' </a>
						</td>
											
						<td style="text-align: right;">' . $this->format_number_report($m['saldo_awal']) . ' 
						<td style="text-align: right;">' . $this->format_number_report($m['debet']) . ' 
						<td style="text-align: right;">' . $this->format_number_report($m['kredit']) . ' 
						<td style="text-align: right;">' . $this->format_number_report($jumlah) . ' 
						
						</td>';

			$html = $html . '
						
						
					</tr>';
		}

		$html = $html . ' 	
						<tr class=":: arc-content">
						<td style="position:relative;">
							&nbsp;

						</td>
						
						
						<td>
							&nbsp;
						</td>


						<td style="text-align: right;">
							
						</td>
						<td style="text-align: right;">
						' . $this->format_number_report($tot_saldo_awal) . ' 
						</td>
						<td style="text-align: right;">
						' . $this->format_number_report($tot_debet) . ' 
						</td>
						<td style="text-align: right;">
						' . $this->format_number_report($tot_kredit) . ' 
						</td>
						
						<td style="text-align: right;">
						' . $this->format_number_report($tot_saldo_akhir) . ' 
						</td>
						
						</tr>
								</tbody>
							</table>
						';
		// $filename = 'report_' . time();
		// $this->pdfgenerator->generate($html, $filename, true, 'A4', 'landscape');
		if ($format_laporan == 'xls') {
			$reader = new \PhpOffice\PhpSpreadsheet\Reader\Html;
			$spreadsheet = $reader->loadFromString($html);
			// $reader->setSheetIndex(1);
			//$spreadhseet = $reader->loadFromString($secondHtmlString, $spreadsheet);
			$objWriter = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
			header("Pragma: public");
			header("Expires: 0");
			header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
			header("Content-Type: application/force-download");
			header("Content-Type: application/octet-stream");
			header("Content-Type: application/download");
			header("Content-Disposition: attachment;filename=test.xlsx");
			header("Content-Transfer-Encoding: binary ");

			ob_end_clean();
			ob_start();
			$objWriter->save('php://output');
		} else if ($format_laporan == 'view') {
			echo $html;
		} else {
			$filename = 'report_' . time();
			$this->pdfgenerator->generate($html, $filename, true, 'A4', 'landscape');
		}
	}


	function laporan_neraca_saldo_mutasi_all()
	{
		error_reporting(0);
		$format_laporan =  $this->post('format_laporan', true);
		$lokasi_id     = $this->post('lokasi_id', true);
		$tanggal_mulai =  $this->post('tgl_mulai', true);
		$tanggal_akhir =  $this->post('tgl_akhir', true);

		$akun_dari = $this->post('akun_dari', true);
		$akun_sampai = $this->post('akun_sampai', true);

		$akun_1 = $this->db->query("select * from acc_akun where id=" . $akun_dari . "")->row_array();
		$akun_2 = $this->db->query("select * from acc_akun where id=" . $akun_sampai . "")->row_array();

		$judulLokasi = 'Semua';
		/* Supaya lokasi urut HO,RO,MILL,ESTATE */
		$lokasi = array();
		$res_lokasi   = $this->db->query("select * from gbm_organisasi where tipe in ('HO')")->result_array();
		foreach ($res_lokasi as $key => $value) {
			$lokasi[] = $value;
		}
		$res_lokasi   = $this->db->query("select * from gbm_organisasi where tipe in ('RO')")->result_array();
		foreach ($res_lokasi as $key => $value) {
			$lokasi[] = $value;
		}
		$res_lokasi   = $this->db->query("select * from gbm_organisasi where tipe in ('MILL')")->result_array();
		foreach ($res_lokasi as $key => $value) {
			$lokasi[] = $value;
		}
		$res_lokasi   = $this->db->query("select * from gbm_organisasi where tipe in ('ESTATE')")->result_array();
		foreach ($res_lokasi as $key => $value) {
			$lokasi[] = $value;
		}
		/* END  Supaya lokasi urut HO,RO,MILL,ESTATE */
		$jumlah_lokasi = count($lokasi);

		/* Ambil AKUN */
		$queryAkun = "select * from acc_akun
       			where is_transaksi_akun=1 
				  and (kode between '" . $akun_1['kode']  . "' and '" . $akun_2['kode'] . "')  
				   order by kode ";

		$res = array();


		// var_dump($results);
		$this->load->helper("terbilang");
		$this->load->helper("antech_helper");
		$this->load->library('pdfgenerator');
		if ($format_laporan == 'view') {
			$html = get_header_report_v2();
		} else {
			$html = get_header_pdf_report();
		}
		$html = $html . '<div class="row">
<div class="span12">
	<br>
	<div class="kop-print">
    <div class="kop-nama">PT. ANNAJAH TECHNOLOGY INDONESIA</div>
    <div class="kop-info"> Komplek Vila Sehati B12 , Tapos - Depok</div>
    <div class="kop-info">Telp : 081387373939</div>
  </div>
	  <hr class="kop-print-hr">
</div>
</div>
<h3 class="title">TRIAL BALANCE (MUTASI) PER UNIT </h3>
  <table class="no_border" style="width:30%">
			
			<tr>
					<td>Akun</td>
					<td>:</td>
					<td>' . $akun_1['kode'] . ' s/d ' . $akun_2['kode'] . '</td>
			</tr>
			<tr>	
					<td>Periode</td>
					<td>:</td>
					<td>' . $tanggal_mulai . ' s/d ' . $tanggal_akhir . '</td>
			</tr>

			
	</table>
	<br>';
		$html = $html . "
<table   border='1' width='100%' style='border-collapse: collapse;'>
<thead>
<tr>
	<th rowspan=2>Kode Akun</td>
	<th rowspan=2>Kode Akun</th>
	<th colspan=" . $jumlah_lokasi . "  style='text-align: center'> Unit </th>
	<th rowspan=2  style='text-align: center'>TOTAL</th>
</tr>
";

		$html = $html . "<tr>";
		/* BUAT JUDUL TABEL BERDASARKAN LOKASI */
		foreach ($lokasi as $key => $value) {
			$html = $html . "<th style='text-align: center'>" . $value['nama'] . "</th>";
		}
		$html = $html . "</tr> </thead>";

		$nourut = 0;

		$grandtotal = 0;
		$total_per_lokasi = array();
		$akuns   = $this->db->query($queryAkun)->result_array();
		foreach ($akuns as $key => $akun) {
			$nourut = $nourut + 1;
			$total = 0;
			$html = $html . "<tr> ";
			$html = $html . "<th style='text-align: left'>" . $akun['kode'] . " </th>";
			$html = $html . "<th style='text-align: left'>" . $akun['nama'] . " </th>";
			foreach ($lokasi as $key_lok => $lokasi_array) {
				$query1 = "SELECT b.acc_akun_id,sum(debet-kredit)as jumlah FROM acc_jurnal_ht a inner join acc_jurnal_dt b 
				on a.id=b.jurnal_id
				where b.acc_akun_id=" . $akun['id'] . " 
				and a.tanggal >= '" . $tanggal_mulai . "'
				and a.tanggal <= '" . $tanggal_akhir . "'
				and b.lokasi_id=" . $lokasi_array['id'] . "
				group by b.acc_akun_id ;";

				$resJumlah  = $this->db->query($query1)->row_array();
				$jumlah = (!empty($resJumlah['jumlah'])) ? $resJumlah['jumlah'] : 0;
				if (array_key_exists($lokasi_array['id'], $total_per_lokasi)) {
					$total_per_lokasi[$lokasi_array['id']] = $total_per_lokasi[$lokasi_array['id']] + $jumlah;
				} else {
					$total_per_lokasi[$lokasi_array['id']] = $jumlah;
				}

				$total = $total + $jumlah;
				$grandtotal = $grandtotal + $jumlah;
				$html = $html . "<td style='text-align: center'>" . $this->format_number_report($jumlah) . " </td>";
			}
			$html = $html . "<td style='text-align: center'>" . $this->format_number_report($total) . " </td>";
			$html = $html . "</tr>";
		}


		$html = $html . "<tr>";
		$html = $html . "<td style='text-align: center'> </td>";
		$html = $html . "<td style='text-align: center'></td>";
		foreach ($lokasi as $key_lok => $lokasi_array) {

			$html = $html . "<td style='text-align: center'>" . $this->format_number_report($total_per_lokasi[$lokasi_array['id']]) . " </td>";
		}
		$html = $html . "<td style='text-align: center'>" . $this->format_number_report($grandtotal) . " </td>";
		$html = $html . "</tr>";
		$html = $html . "</table>";



		// $filename = 'report_' . time();
		// $this->pdfgenerator->generate($html, $filename, true, 'A4', 'landscape');
		if ($format_laporan == 'xls') {
			$reader = new \PhpOffice\PhpSpreadsheet\Reader\Html;
			$spreadsheet = $reader->loadFromString($html);
			// $reader->setSheetIndex(1);
			//$spreadhseet = $reader->loadFromString($secondHtmlString, $spreadsheet);
			$objWriter = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
			header("Pragma: public");
			header("Expires: 0");
			header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
			header("Content-Type: application/force-download");
			header("Content-Type: application/octet-stream");
			header("Content-Type: application/download");
			header("Content-Disposition: attachment;filename=test.xlsx");
			header("Content-Transfer-Encoding: binary ");

			ob_end_clean();
			ob_start();
			$objWriter->save('php://output');
		} else if ($format_laporan == 'view') {
			echo $html;
		} else {
			$filename = 'report_' . time();
			$this->pdfgenerator->generate($html, $filename, true, 'A3', 'landscape');
		}
	}
	function laporan_neraca_saldo_mutasi_inti()
	{
		error_reporting(0);
		$format_laporan =  $this->post('format_laporan', true);
		$lokasi_id     = $this->post('lokasi_id', true);
		$tanggal_mulai =  $this->post('tgl_mulai', true);
		$tanggal_akhir =  $this->post('tgl_akhir', true);

		$akun_dari = $this->post('akun_dari', true);
		$akun_sampai = $this->post('akun_sampai', true);

		$akun_1 = $this->db->query("select * from acc_akun where id=" . $akun_dari . "")->row_array();
		$akun_2 = $this->db->query("select * from acc_akun where id=" . $akun_sampai . "")->row_array();

		$judulLokasi = 'Semua';
		/* Supaya lokasi urut HO,RO,MILL,ESTATE */
		$lokasi = array();
		$res_lokasi   = $this->db->query("select * from gbm_organisasi where tipe in ('HO')")->result_array();
		foreach ($res_lokasi as $key => $value) {
			$lokasi[] = $value;
		}
		$res_lokasi   = $this->db->query("select * from gbm_organisasi where tipe in ('RO')")->result_array();
		foreach ($res_lokasi as $key => $value) {
			$lokasi[] = $value;
		}
		$res_lokasi   = $this->db->query("select * from gbm_organisasi where tipe in ('MILL')")->result_array();
		foreach ($res_lokasi as $key => $value) {
			$lokasi[] = $value;
		}
		$res_lokasi   = $this->db->query("select * from gbm_organisasi where tipe in ('ESTATE')  and kode='SBNE'")->result_array();
		foreach ($res_lokasi as $key => $value) {
			$lokasi[] = $value;
		}
		/* END  Supaya lokasi urut HO,RO,MILL,ESTATE */
		$jumlah_lokasi = count($lokasi);

		/* Ambil AKUN */
		$queryAkun = "select * from acc_akun
       			where is_transaksi_akun=1 
				  and (kode between '" . $akun_1['kode']  . "' and '" . $akun_2['kode'] . "')  
				   order by kode ";

		$res = array();


		// var_dump($results);
		$this->load->helper("terbilang");
		$this->load->helper("antech_helper");
		$this->load->library('pdfgenerator');
		if ($format_laporan == 'view') {
			$html = get_header_report_v2();
		} else {
			$html = get_header_pdf_report();
		}
		$html = $html . '<div class="row">
<div class="span12">
	<br>
	<div class="kop-print">
    <div class="kop-nama">PT. ANNAJAH TECHNOLOGY INDONESIA</div>
    <div class="kop-info"> Komplek Vila Sehati B12 , Tapos - Depok</div>
    <div class="kop-info">Telp : 081387373939</div>
  </div>
	  <hr class="kop-print-hr">
</div>
</div>
<h3 class="title">TRIAL BALANCE (MUTASI) PER UNIT </h3>
  <table class="no_border" style="width:30%">
			
			<tr>
					<td>Akun</td>
					<td>:</td>
					<td>' . $akun_1['kode'] . ' s/d ' . $akun_2['kode'] . '</td>
			</tr>
			<tr>	
					<td>Periode</td>
					<td>:</td>
					<td>' . $tanggal_mulai . ' s/d ' . $tanggal_akhir . '</td>
			</tr>

			
	</table>
	<br>';
		$html = $html . "
<table   border='1' width='100%' style='border-collapse: collapse;'>
<thead>
<tr>
	<th rowspan=2>Kode Akun</td>
	<th rowspan=2>Kode Akun</th>
	<th colspan=" . $jumlah_lokasi . "  style='text-align: center'> Unit </th>
	<th rowspan=2  style='text-align: center'>TOTAL</th>
</tr>
";

		$html = $html . "<tr>";
		/* BUAT JUDUL TABEL BERDASARKAN LOKASI */
		foreach ($lokasi as $key => $value) {
			$html = $html . "<th style='text-align: center'>" . $value['nama'] . "</th>";
		}
		$html = $html . "</tr> </thead>";

		$nourut = 0;

		$grandtotal = 0;
		$total_per_lokasi = array();
		$akuns   = $this->db->query($queryAkun)->result_array();
		foreach ($akuns as $key => $akun) {
			$nourut = $nourut + 1;
			$total = 0;
			$html = $html . "<tr> ";
			$html = $html . "<th style='text-align: left'>" . $akun['kode'] . " </th>";
			$html = $html . "<th style='text-align: left'>" . $akun['nama'] . " </th>";
			foreach ($lokasi as $key_lok => $lokasi_array) {
				$query1 = "SELECT b.acc_akun_id,sum(debet-kredit)as jumlah FROM acc_jurnal_ht a inner join acc_jurnal_dt b 
				on a.id=b.jurnal_id
				where b.acc_akun_id=" . $akun['id'] . " 
				and a.tanggal >= '" . $tanggal_mulai . "'
				and a.tanggal <= '" . $tanggal_akhir . "'
				and b.lokasi_id=" . $lokasi_array['id'] . "
				group by b.acc_akun_id ;";

				$resJumlah  = $this->db->query($query1)->row_array();
				$jumlah = (!empty($resJumlah['jumlah'])) ? $resJumlah['jumlah'] : 0;
				if (array_key_exists($lokasi_array['id'], $total_per_lokasi)) {
					$total_per_lokasi[$lokasi_array['id']] = $total_per_lokasi[$lokasi_array['id']] + $jumlah;
				} else {
					$total_per_lokasi[$lokasi_array['id']] = $jumlah;
				}

				$total = $total + $jumlah;
				$grandtotal = $grandtotal + $jumlah;
				$html = $html . "<td style='text-align: center'>" . $this->format_number_report($jumlah) . " </td>";
			}
			$html = $html . "<td style='text-align: center'>" . $this->format_number_report($total) . " </td>";
			$html = $html . "</tr>";
		}


		$html = $html . "<tr>";
		$html = $html . "<td style='text-align: center'> </td>";
		$html = $html . "<td style='text-align: center'></td>";
		foreach ($lokasi as $key_lok => $lokasi_array) {

			$html = $html . "<td style='text-align: center'>" . $this->format_number_report($total_per_lokasi[$lokasi_array['id']]) . " </td>";
		}
		$html = $html . "<td style='text-align: center'>" . $this->format_number_report($grandtotal) . " </td>";
		$html = $html . "</tr>";
		$html = $html . "</table>";



		// $filename = 'report_' . time();
		// $this->pdfgenerator->generate($html, $filename, true, 'A4', 'landscape');
		if ($format_laporan == 'xls') {
			$reader = new \PhpOffice\PhpSpreadsheet\Reader\Html;
			$spreadsheet = $reader->loadFromString($html);
			// $reader->setSheetIndex(1);
			//$spreadhseet = $reader->loadFromString($secondHtmlString, $spreadsheet);
			$objWriter = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
			header("Pragma: public");
			header("Expires: 0");
			header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
			header("Content-Type: application/force-download");
			header("Content-Type: application/octet-stream");
			header("Content-Type: application/download");
			header("Content-Disposition: attachment;filename=test.xlsx");
			header("Content-Transfer-Encoding: binary ");

			ob_end_clean();
			ob_start();
			$objWriter->save('php://output');
		} else if ($format_laporan == 'view') {
			echo $html;
		} else {
			$filename = 'report_' . time();
			$this->pdfgenerator->generate($html, $filename, true, 'A3', 'landscape');
		}
	}
	function laporan_neraca_saldo_mutasi_plasma()
	{
		error_reporting(0);
		$format_laporan =  $this->post('format_laporan', true);
		$lokasi_id     = $this->post('lokasi_id', true);
		$tanggal_mulai =  $this->post('tgl_mulai', true);
		$tanggal_akhir =  $this->post('tgl_akhir', true);

		$akun_dari = $this->post('akun_dari', true);
		$akun_sampai = $this->post('akun_sampai', true);

		$akun_1 = $this->db->query("select * from acc_akun where id=" . $akun_dari . "")->row_array();
		$akun_2 = $this->db->query("select * from acc_akun where id=" . $akun_sampai . "")->row_array();

		$judulLokasi = 'Semua';
		/* Supaya lokasi urut HO,RO,MILL,ESTATE */
		$lokasi = array();
		// $res_lokasi   = $this->db->query("select * from gbm_organisasi where tipe in ('HO')")->result_array();
		// foreach ($res_lokasi as $key => $value) {
		// 	$lokasi[] = $value;
		// }
		// $res_lokasi   = $this->db->query("select * from gbm_organisasi where tipe in ('RO')")->result_array();
		// foreach ($res_lokasi as $key => $value) {
		// 	$lokasi[] = $value;
		// }
		// $res_lokasi   = $this->db->query("select * from gbm_organisasi where tipe in ('MILL')")->result_array();
		// foreach ($res_lokasi as $key => $value) {
		// 	$lokasi[] = $value;
		// }
		$res_lokasi   = $this->db->query("select * from gbm_organisasi where tipe in ('ESTATE')  and kode='SBME'")->result_array();
		foreach ($res_lokasi as $key => $value) {
			$lokasi[] = $value;
		}
		/* END  Supaya lokasi urut HO,RO,MILL,ESTATE */
		$jumlah_lokasi = count($lokasi);

		/* Ambil AKUN */
		$queryAkun = "select * from acc_akun
       			where is_transaksi_akun=1 
				  and (kode between '" . $akun_1['kode']  . "' and '" . $akun_2['kode'] . "')  
				   order by kode ";

		$res = array();


		// var_dump($results);
		$this->load->helper("terbilang");
		$this->load->helper("antech_helper");
		$this->load->library('pdfgenerator');
		if ($format_laporan == 'view') {
			$html = get_header_report_v2();
		} else {
			$html = get_header_pdf_report();
		}
		$html = $html . '<div class="row">
<div class="span12">
	<br>
	<div class="kop-print">
    <div class="kop-nama">PT. ANNAJAH TECHNOLOGY INDONESIA</div>
    <div class="kop-info"> Komplek Vila Sehati B12 , Tapos - Depok</div>
    <div class="kop-info">Telp : 081387373939</div>
  </div>
	  <hr class="kop-print-hr">
</div>
</div>
<h3 class="title">TRIAL BALANCE (MUTASI) PER UNIT </h3>
  <table class="no_border" style="width:30%">
			
			<tr>
					<td>Akun</td>
					<td>:</td>
					<td>' . $akun_1['kode'] . ' s/d ' . $akun_2['kode'] . '</td>
			</tr>
			<tr>	
					<td>Periode</td>
					<td>:</td>
					<td>' . $tanggal_mulai . ' s/d ' . $tanggal_akhir . '</td>
			</tr>

			
	</table>
	<br>';
		$html = $html . "
<table   border='1' width='100%' style='border-collapse: collapse;'>
<thead>
<tr>
	<th rowspan=2>Kode Akun</td>
	<th rowspan=2>Kode Akun</th>
	<th colspan=" . $jumlah_lokasi . "  style='text-align: center'> Unit </th>
	<th rowspan=2  style='text-align: center'>TOTAL</th>
</tr>
";

		$html = $html . "<tr>";
		/* BUAT JUDUL TABEL BERDASARKAN LOKASI */
		foreach ($lokasi as $key => $value) {
			$html = $html . "<th style='text-align: center'>" . $value['nama'] . "</th>";
		}
		$html = $html . "</tr> </thead>";

		$nourut = 0;

		$grandtotal = 0;
		$total_per_lokasi = array();
		$akuns   = $this->db->query($queryAkun)->result_array();
		foreach ($akuns as $key => $akun) {
			$nourut = $nourut + 1;
			$total = 0;
			$html = $html . "<tr> ";
			$html = $html . "<th style='text-align: left'>" . $akun['kode'] . " </th>";
			$html = $html . "<th style='text-align: left'>" . $akun['nama'] . " </th>";
			foreach ($lokasi as $key_lok => $lokasi_array) {
				$query1 = "SELECT b.acc_akun_id,sum(debet-kredit)as jumlah FROM acc_jurnal_ht a inner join acc_jurnal_dt b 
				on a.id=b.jurnal_id
				where b.acc_akun_id=" . $akun['id'] . " 
				and a.tanggal >= '" . $tanggal_mulai . "'
				and a.tanggal <= '" . $tanggal_akhir . "'
				and b.lokasi_id=" . $lokasi_array['id'] . "
				group by b.acc_akun_id ;";

				$resJumlah  = $this->db->query($query1)->row_array();
				$jumlah = (!empty($resJumlah['jumlah'])) ? $resJumlah['jumlah'] : 0;
				if (array_key_exists($lokasi_array['id'], $total_per_lokasi)) {
					$total_per_lokasi[$lokasi_array['id']] = $total_per_lokasi[$lokasi_array['id']] + $jumlah;
				} else {
					$total_per_lokasi[$lokasi_array['id']] = $jumlah;
				}

				$total = $total + $jumlah;
				$grandtotal = $grandtotal + $jumlah;
				$html = $html . "<td style='text-align: center'>" . $this->format_number_report($jumlah) . " </td>";
			}
			$html = $html . "<td style='text-align: center'>" . $this->format_number_report($total) . " </td>";
			$html = $html . "</tr>";
		}


		$html = $html . "<tr>";
		$html = $html . "<td style='text-align: center'> </td>";
		$html = $html . "<td style='text-align: center'></td>";
		foreach ($lokasi as $key_lok => $lokasi_array) {

			$html = $html . "<td style='text-align: center'>" . $this->format_number_report($total_per_lokasi[$lokasi_array['id']]) . " </td>";
		}
		$html = $html . "<td style='text-align: center'>" . $this->format_number_report($grandtotal) . " </td>";
		$html = $html . "</tr>";
		$html = $html . "</table>";



		// $filename = 'report_' . time();
		// $this->pdfgenerator->generate($html, $filename, true, 'A4', 'landscape');
		if ($format_laporan == 'xls') {
			$reader = new \PhpOffice\PhpSpreadsheet\Reader\Html;
			$spreadsheet = $reader->loadFromString($html);
			// $reader->setSheetIndex(1);
			//$spreadhseet = $reader->loadFromString($secondHtmlString, $spreadsheet);
			$objWriter = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
			header("Pragma: public");
			header("Expires: 0");
			header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
			header("Content-Type: application/force-download");
			header("Content-Type: application/octet-stream");
			header("Content-Type: application/download");
			header("Content-Disposition: attachment;filename=test.xlsx");
			header("Content-Transfer-Encoding: binary ");

			ob_end_clean();
			ob_start();
			$objWriter->save('php://output');
		} else if ($format_laporan == 'view') {
			echo $html;
		} else {
			$filename = 'report_' . time();
			$this->pdfgenerator->generate($html, $filename, true, 'A3', 'landscape');
		}
	}
	/* Neraca saldo By Unit */
	function laporan_neraca_saldo_all_unit_post()
	{

		$versi_laporan =  $this->post('versi_laporan', true);
		if ($versi_laporan == 'v1') {
			//  ALL
			$this->laporan_neraca_saldo_all_unit_all();
		} else if ($versi_laporan == 'v2') {
			// INTI
			$this->laporan_neraca_saldo_all_unit_inti();
		} else if ($versi_laporan == 'v3') {
			// PLASMA
			$this->laporan_neraca_saldo_all_unit_plasma();
		}
	}
	function laporan_neraca_saldo_all_unit_all()
	{
		error_reporting(0);
		$format_laporan =  $this->post('format_laporan', true);
		$tanggal_akhir =  $this->post('tgl_akhir', true);

		$akun_dari = $this->post('akun_dari', true);
		$akun_sampai = $this->post('akun_sampai', true);

		$akun_1 = $this->db->query("select * from acc_akun where id=" . $akun_dari . "")->row_array();
		$akun_2 = $this->db->query("select * from acc_akun where id=" . $akun_sampai . "")->row_array();

		$judulLokasi = 'Semua';
		/* Supaya lokasi urut HO,RO,MILL,ESTATE */
		$lokasi = array();
		$res_lokasi   = $this->db->query("select * from gbm_organisasi where tipe in ('HO')")->result_array();
		foreach ($res_lokasi as $key => $value) {
			$lokasi[] = $value;
		}
		$res_lokasi   = $this->db->query("select * from gbm_organisasi where tipe in ('RO')")->result_array();
		foreach ($res_lokasi as $key => $value) {
			$lokasi[] = $value;
		}
		$res_lokasi   = $this->db->query("select * from gbm_organisasi where tipe in ('MILL')")->result_array();
		foreach ($res_lokasi as $key => $value) {
			$lokasi[] = $value;
		}
		$res_lokasi   = $this->db->query("select * from gbm_organisasi where tipe in ('ESTATE')")->result_array();
		foreach ($res_lokasi as $key => $value) {
			$lokasi[] = $value;
		}
		/* END  Supaya lokasi urut HO,RO,MILL,ESTATE */
		$jumlah_lokasi = count($lokasi);

		/* Ambil AKUN */
		$queryAkun = "select * from acc_akun
       			where is_transaksi_akun=1 
				  and (kode between '" . $akun_1['kode']  . "' and '" . $akun_2['kode'] . "')  
				   order by kode ";

		$res = array();


		// var_dump($results);
		$this->load->helper("terbilang");
		$this->load->helper("antech_helper");
		$this->load->library('pdfgenerator');
		if ($format_laporan == 'view') {
			$html = get_header_report_v2();
		} else {
			$html = get_header_pdf_report();
		}
		$html = $html . '<div class="row">
<div class="span12">
	<br>
	<div class="kop-print">
    <div class="kop-nama">PT. ANNAJAH TECHNOLOGY INDONESIA</div>
    <div class="kop-info"> Komplek Vila Sehati B12 , Tapos - Depok</div>
    <div class="kop-info">Telp : 081387373939</div>
  </div>
	  <hr class="kop-print-hr">
</div>
</div>
<h3 class="title">TRIAL BALANCE PER UNIT</h3>
  <table class="no_border" style="width:30%">
			
			<tr>
					<td>Akun</td>
					<td>:</td>
					<td>' . $akun_1['kode'] . ' s/d ' . $akun_2['kode'] . '</td>
			</tr>
			<tr>	
					<td>Periode</td>
					<td>:</td>
					<td>' . $tanggal_akhir . '</td>
			</tr>

			
	</table>
	<br>';
		$html = $html . "
<table   border='1' width='100%' style='border-collapse: collapse;'>
<thead>
<tr>
	<th rowspan=2>Kode Akun</td>
	<th rowspan=2>Kode Akun</th>
	<th colspan=" . $jumlah_lokasi . "  style='text-align: center'> Unit </th>
	<th rowspan=2  style='text-align: center'>TOTAL</th>
</tr>
";

		$html = $html . "<tr>";
		/* BUAT JUDUL TABEL BERDASARKAN LOKASI */
		foreach ($lokasi as $key => $value) {
			$html = $html . "<th style='text-align: center'>" . $value['nama'] . "</th>";
		}
		$html = $html . "</tr> </thead>";

		$nourut = 0;

		$grandtotal = 0;
		$total_per_lokasi = array();
		$akuns   = $this->db->query($queryAkun)->result_array();
		foreach ($akuns as $key => $akun) {
			$nourut = $nourut + 1;
			$total = 0;
			$html = $html . "<tr> ";
			$html = $html . "<th style='text-align: left'>" . $akun['kode'] . " </th>";
			$html = $html . "<th style='text-align: left'>" . $akun['nama'] . " </th>";
			foreach ($lokasi as $key_lok => $lokasi_array) {
				$query1 = "SELECT b.acc_akun_id,sum(debet-kredit)as jumlah FROM acc_jurnal_ht a inner join acc_jurnal_dt b 
				on a.id=b.jurnal_id
				where b.acc_akun_id=" . $akun['id'] . " 
				and a.tanggal <= '" . $tanggal_akhir . "' and b.lokasi_id=" . $lokasi_array['id'] . "
				group by b.acc_akun_id ;";

				$resJumlah  = $this->db->query($query1)->row_array();
				$jumlah = (!empty($resJumlah['jumlah'])) ? $resJumlah['jumlah'] : 0;
				if (array_key_exists($lokasi_array['id'], $total_per_lokasi)) {
					$total_per_lokasi[$lokasi_array['id']] = $total_per_lokasi[$lokasi_array['id']] + $jumlah;
				} else {
					$total_per_lokasi[$lokasi_array['id']] = $jumlah;
				}

				$total = $total + $jumlah;
				$grandtotal = $grandtotal + $jumlah;
				$html = $html . "<td style='text-align: center'>" . $this->format_number_report($jumlah) . " </td>";
			}
			$html = $html . "<td style='text-align: center'>" . $this->format_number_report($total) . " </td>";
			$html = $html . "</tr>";
		}


		$html = $html . "<tr>";
		$html = $html . "<td style='text-align: center'> </td>";
		$html = $html . "<td style='text-align: center'></td>";
		foreach ($lokasi as $key_lok => $lokasi_array) {

			$html = $html . "<td style='text-align: center'>" . $this->format_number_report($total_per_lokasi[$lokasi_array['id']]) . " </td>";
		}
		$html = $html . "<td style='text-align: center'>" . $this->format_number_report($grandtotal) . " </td>";
		$html = $html . "</tr>";
		$html = $html . "</table>";



		// $filename = 'report_' . time();
		// $this->pdfgenerator->generate($html, $filename, true, 'A4', 'landscape');
		if ($format_laporan == 'xls') {
			$reader = new \PhpOffice\PhpSpreadsheet\Reader\Html;
			$spreadsheet = $reader->loadFromString($html);
			// $reader->setSheetIndex(1);
			//$spreadhseet = $reader->loadFromString($secondHtmlString, $spreadsheet);
			$objWriter = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
			header("Pragma: public");
			header("Expires: 0");
			header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
			header("Content-Type: application/force-download");
			header("Content-Type: application/octet-stream");
			header("Content-Type: application/download");
			header("Content-Disposition: attachment;filename=test.xlsx");
			header("Content-Transfer-Encoding: binary ");

			ob_end_clean();
			ob_start();
			$objWriter->save('php://output');
		} else if ($format_laporan == 'view') {
			echo $html;
		} else {
			$filename = 'report_' . time();
			$this->pdfgenerator->generate($html, $filename, true, 'A3', 'landscape');
		}
	}
	function laporan_neraca_saldo_all_unit_inti()
	{
		error_reporting(0);
		$format_laporan =  $this->post('format_laporan', true);
		$tanggal_akhir =  $this->post('tgl_akhir', true);

		$akun_dari = $this->post('akun_dari', true);
		$akun_sampai = $this->post('akun_sampai', true);

		$akun_1 = $this->db->query("select * from acc_akun where id=" . $akun_dari . "")->row_array();
		$akun_2 = $this->db->query("select * from acc_akun where id=" . $akun_sampai . "")->row_array();

		$judulLokasi = 'Semua';
		/* Supaya lokasi urut HO,RO,MILL,ESTATE */
		$lokasi = array();
		$res_lokasi   = $this->db->query("select * from gbm_organisasi where tipe in ('HO')")->result_array();
		foreach ($res_lokasi as $key => $value) {
			$lokasi[] = $value;
		}
		$res_lokasi   = $this->db->query("select * from gbm_organisasi where tipe in ('RO')")->result_array();
		foreach ($res_lokasi as $key => $value) {
			$lokasi[] = $value;
		}
		$res_lokasi   = $this->db->query("select * from gbm_organisasi where tipe in ('MILL')")->result_array();
		foreach ($res_lokasi as $key => $value) {
			$lokasi[] = $value;
		}
		$res_lokasi   = $this->db->query("select * from gbm_organisasi where tipe in ('ESTATE') and kode='SBNE'")->result_array();
		foreach ($res_lokasi as $key => $value) {
			$lokasi[] = $value;
		}
		/* END  Supaya lokasi urut HO,RO,MILL,ESTATE */
		$jumlah_lokasi = count($lokasi);

		/* Ambil AKUN */
		$queryAkun = "select * from acc_akun
       			where is_transaksi_akun=1 
				  and (kode between '" . $akun_1['kode']  . "' and '" . $akun_2['kode'] . "')  
				   order by kode ";

		$res = array();


		// var_dump($results);
		$this->load->helper("terbilang");
		$this->load->helper("antech_helper");
		$this->load->library('pdfgenerator');
		if ($format_laporan == 'view') {
			$html = get_header_report_v2();
		} else {
			$html = get_header_pdf_report();
		}
		$html = $html . '<div class="row">
<div class="span12">
	<br>
	<div class="kop-print">
    <div class="kop-nama">PT. ANNAJAH TECHNOLOGY INDONESIA</div>
    <div class="kop-info"> Komplek Vila Sehati B12 , Tapos - Depok</div>
    <div class="kop-info">Telp : 081387373939</div>
  </div>
	  <hr class="kop-print-hr">
</div>
</div>
<h3 class="title">TRIAL BALANCE PER UNIT INTI</h3>
  <table class="no_border" style="width:30%">
			
			<tr>
					<td>Akun</td>
					<td>:</td>
					<td>' . $akun_1['kode'] . ' s/d ' . $akun_2['kode'] . '</td>
			</tr>
			<tr>	
					<td>Periode</td>
					<td>:</td>
					<td>' . $tanggal_akhir . '</td>
			</tr>

			
	</table>
	<br>';
		$html = $html . "
<table   border='1' width='100%' style='border-collapse: collapse;'>
<thead>
<tr>
	<th rowspan=2>Kode Akun</td>
	<th rowspan=2>Kode Akun</th>
	<th colspan=" . $jumlah_lokasi . "  style='text-align: center'> Unit </th>
	<th rowspan=2  style='text-align: center'>TOTAL</th>
</tr>
";

		$html = $html . "<tr>";
		/* BUAT JUDUL TABEL BERDASARKAN LOKASI */
		foreach ($lokasi as $key => $value) {
			$html = $html . "<th style='text-align: center'>" . $value['nama'] . "</th>";
		}
		$html = $html . "</tr> </thead>";

		$nourut = 0;

		$grandtotal = 0;
		$total_per_lokasi = array();
		$akuns   = $this->db->query($queryAkun)->result_array();
		foreach ($akuns as $key => $akun) {
			$nourut = $nourut + 1;
			$total = 0;
			$html = $html . "<tr> ";
			$html = $html . "<th style='text-align: left'>" . $akun['kode'] . " </th>";
			$html = $html . "<th style='text-align: left'>" . $akun['nama'] . " </th>";
			foreach ($lokasi as $key_lok => $lokasi_array) {
				$query1 = "SELECT b.acc_akun_id,sum(debet-kredit)as jumlah FROM acc_jurnal_ht a inner join acc_jurnal_dt b 
				on a.id=b.jurnal_id
				where b.acc_akun_id=" . $akun['id'] . " 
				and a.tanggal <= '" . $tanggal_akhir . "' and b.lokasi_id=" . $lokasi_array['id'] . "
				group by b.acc_akun_id ;";

				$resJumlah  = $this->db->query($query1)->row_array();
				$jumlah = (!empty($resJumlah['jumlah'])) ? $resJumlah['jumlah'] : 0;
				if (array_key_exists($lokasi_array['id'], $total_per_lokasi)) {
					$total_per_lokasi[$lokasi_array['id']] = $total_per_lokasi[$lokasi_array['id']] + $jumlah;
				} else {
					$total_per_lokasi[$lokasi_array['id']] = $jumlah;
				}

				$total = $total + $jumlah;
				$grandtotal = $grandtotal + $jumlah;
				$html = $html . "<td style='text-align: center'>" . $this->format_number_report($jumlah) . " </td>";
			}
			$html = $html . "<td style='text-align: center'>" . $this->format_number_report($total) . " </td>";
			$html = $html . "</tr>";
		}


		$html = $html . "<tr>";
		$html = $html . "<td style='text-align: center'> </td>";
		$html = $html . "<td style='text-align: center'></td>";
		foreach ($lokasi as $key_lok => $lokasi_array) {

			$html = $html . "<td style='text-align: center'>" . $this->format_number_report($total_per_lokasi[$lokasi_array['id']]) . " </td>";
		}
		$html = $html . "<td style='text-align: center'>" . $this->format_number_report($grandtotal) . " </td>";
		$html = $html . "</tr>";
		$html = $html . "</table>";



		// $filename = 'report_' . time();
		// $this->pdfgenerator->generate($html, $filename, true, 'A4', 'landscape');
		if ($format_laporan == 'xls') {
			$reader = new \PhpOffice\PhpSpreadsheet\Reader\Html;
			$spreadsheet = $reader->loadFromString($html);
			// $reader->setSheetIndex(1);
			//$spreadhseet = $reader->loadFromString($secondHtmlString, $spreadsheet);
			$objWriter = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
			header("Pragma: public");
			header("Expires: 0");
			header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
			header("Content-Type: application/force-download");
			header("Content-Type: application/octet-stream");
			header("Content-Type: application/download");
			header("Content-Disposition: attachment;filename=test.xlsx");
			header("Content-Transfer-Encoding: binary ");

			ob_end_clean();
			ob_start();
			$objWriter->save('php://output');
		} else if ($format_laporan == 'view') {
			echo $html;
		} else {
			$filename = 'report_' . time();
			$this->pdfgenerator->generate($html, $filename, true, 'A3', 'landscape');
		}
	}

	function laporan_neraca_saldo_all_unit_plasma()
	{
		error_reporting(0);
		$format_laporan =  $this->post('format_laporan', true);
		$tanggal_akhir =  $this->post('tgl_akhir', true);

		$akun_dari = $this->post('akun_dari', true);
		$akun_sampai = $this->post('akun_sampai', true);

		$akun_1 = $this->db->query("select * from acc_akun where id=" . $akun_dari . "")->row_array();
		$akun_2 = $this->db->query("select * from acc_akun where id=" . $akun_sampai . "")->row_array();

		$judulLokasi = 'Semua';
		/* Supaya lokasi urut HO,RO,MILL,ESTATE */
		// $lokasi = array();
		// $res_lokasi   = $this->db->query("select * from gbm_organisasi where tipe in ('HO')")->result_array();
		// foreach ($res_lokasi as $key => $value) {
		// 	$lokasi[] = $value;
		// }
		// $res_lokasi   = $this->db->query("select * from gbm_organisasi where tipe in ('RO')")->result_array();
		// foreach ($res_lokasi as $key => $value) {
		// 	$lokasi[] = $value;
		// }
		// $res_lokasi   = $this->db->query("select * from gbm_organisasi where tipe in ('MILL')")->result_array();
		// foreach ($res_lokasi as $key => $value) {
		// 	$lokasi[] = $value;
		// }
		$res_lokasi   = $this->db->query("select * from gbm_organisasi where tipe in ('ESTATE') and kode='SBME'")->result_array();
		foreach ($res_lokasi as $key => $value) {
			$lokasi[] = $value;
		}
		/* END  Supaya lokasi urut HO,RO,MILL,ESTATE */
		$jumlah_lokasi = count($lokasi);

		/* Ambil AKUN */
		$queryAkun = "select * from acc_akun
       			where is_transaksi_akun=1 
				  and (kode between '" . $akun_1['kode']  . "' and '" . $akun_2['kode'] . "')  
				   order by kode ";

		$res = array();


		// var_dump($results);
		$this->load->helper("terbilang");
		$this->load->helper("antech_helper");
		$this->load->library('pdfgenerator');
		if ($format_laporan == 'view') {
			$html = get_header_report_v2();
		} else {
			$html = get_header_pdf_report();
		}
		$html = $html . '<div class="row">
<div class="span12">
	<br>
	<div class="kop-print">
    <div class="kop-nama">PT. ANNAJAH TECHNOLOGY INDONESIA</div>
    <div class="kop-info"> Komplek Vila Sehati B12 , Tapos - Depok</div>
    <div class="kop-info">Telp : 081387373939</div>
  </div>
	  <hr class="kop-print-hr">
</div>
</div>
<h3 class="title">TRIAL BALANCE PER UNIT PLASMA</h3>
  <table class="no_border" style="width:30%">
			
			<tr>
					<td>Akun</td>
					<td>:</td>
					<td>' . $akun_1['kode'] . ' s/d ' . $akun_2['kode'] . '</td>
			</tr>
			<tr>	
					<td>Periode</td>
					<td>:</td>
					<td>' . $tanggal_akhir . '</td>
			</tr>

			
	</table>
	<br>';
		$html = $html . "
<table   border='1' width='100%' style='border-collapse: collapse;'>
<thead>
<tr>
	<th rowspan=2>Kode Akun</td>
	<th rowspan=2>Kode Akun</th>
	<th colspan=" . $jumlah_lokasi . "  style='text-align: center'> Unit </th>
	<th rowspan=2  style='text-align: center'>TOTAL</th>
</tr>
";

		$html = $html . "<tr>";
		/* BUAT JUDUL TABEL BERDASARKAN LOKASI */
		foreach ($lokasi as $key => $value) {
			$html = $html . "<th style='text-align: center'>" . $value['nama'] . "</th>";
		}
		$html = $html . "</tr> </thead>";

		$nourut = 0;

		$grandtotal = 0;
		$total_per_lokasi = array();
		$akuns   = $this->db->query($queryAkun)->result_array();
		foreach ($akuns as $key => $akun) {
			$nourut = $nourut + 1;
			$total = 0;
			$html = $html . "<tr> ";
			$html = $html . "<th style='text-align: left'>" . $akun['kode'] . " </th>";
			$html = $html . "<th style='text-align: left'>" . $akun['nama'] . " </th>";
			foreach ($lokasi as $key_lok => $lokasi_array) {
				$query1 = "SELECT b.acc_akun_id,sum(debet-kredit)as jumlah FROM acc_jurnal_ht a inner join acc_jurnal_dt b 
				on a.id=b.jurnal_id
				where b.acc_akun_id=" . $akun['id'] . " 
				and a.tanggal <= '" . $tanggal_akhir . "' and b.lokasi_id=" . $lokasi_array['id'] . "
				group by b.acc_akun_id ;";

				$resJumlah  = $this->db->query($query1)->row_array();
				$jumlah = (!empty($resJumlah['jumlah'])) ? $resJumlah['jumlah'] : 0;
				if (array_key_exists($lokasi_array['id'], $total_per_lokasi)) {
					$total_per_lokasi[$lokasi_array['id']] = $total_per_lokasi[$lokasi_array['id']] + $jumlah;
				} else {
					$total_per_lokasi[$lokasi_array['id']] = $jumlah;
				}

				$total = $total + $jumlah;
				$grandtotal = $grandtotal + $jumlah;
				$html = $html . "<td style='text-align: center'>" . $this->format_number_report($jumlah) . " </td>";
			}
			$html = $html . "<td style='text-align: center'>" . $this->format_number_report($total) . " </td>";
			$html = $html . "</tr>";
		}


		$html = $html . "<tr>";
		$html = $html . "<td style='text-align: center'> </td>";
		$html = $html . "<td style='text-align: center'></td>";
		foreach ($lokasi as $key_lok => $lokasi_array) {

			$html = $html . "<td style='text-align: center'>" . $this->format_number_report($total_per_lokasi[$lokasi_array['id']]) . " </td>";
		}
		$html = $html . "<td style='text-align: center'>" . $this->format_number_report($grandtotal) . " </td>";
		$html = $html . "</tr>";
		$html = $html . "</table>";



		// $filename = 'report_' . time();
		// $this->pdfgenerator->generate($html, $filename, true, 'A4', 'landscape');
		if ($format_laporan == 'xls') {
			$reader = new \PhpOffice\PhpSpreadsheet\Reader\Html;
			$spreadsheet = $reader->loadFromString($html);
			// $reader->setSheetIndex(1);
			//$spreadhseet = $reader->loadFromString($secondHtmlString, $spreadsheet);
			$objWriter = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
			header("Pragma: public");
			header("Expires: 0");
			header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
			header("Content-Type: application/force-download");
			header("Content-Type: application/octet-stream");
			header("Content-Type: application/download");
			header("Content-Disposition: attachment;filename=test.xlsx");
			header("Content-Transfer-Encoding: binary ");

			ob_end_clean();
			ob_start();
			$objWriter->save('php://output');
		} else if ($format_laporan == 'view') {
			echo $html;
		} else {
			$filename = 'report_' . time();
			$this->pdfgenerator->generate($html, $filename, true, 'A3', 'landscape');
		}
	}
	function laporan_neraca_post()
	{
		$versi_laporan =  $this->post('versi_laporan', true);
		$tipe_laporan =  $this->post('tipe_laporan', true);

		if ($versi_laporan == 'v1') {
			$this->laporan_neraca_v1($tipe_laporan);
		} else if ($versi_laporan == 'v2') {
			$this->laporan_neraca_v2($tipe_laporan);
		} else if ($versi_laporan == 'v3') {
			$this->laporan_neraca_v3($tipe_laporan);
		} else {
			$this->laporan_neraca_v4();
		}
	}
	function laporan_neraca_inti_post()
	{
		$versi_laporan =  $this->post('versi_laporan', true);
		if ($versi_laporan == 'v1') {
			$this->laporan_neraca_inti_v1();
		} else if ($versi_laporan == 'v2') {
			$this->laporan_neraca_inti_v2();
		} else if ($versi_laporan == 'v3') {
			$this->laporan_neraca_inti_v3();
		} else {
			$this->laporan_neraca_inti_v4();
		}
	}
	function laporan_neraca_v1($tipe_laporan)
	{
		error_reporting(0);
		$tanggal_akhir =  $this->post('tgl_akhir', true);
		$format_laporan =  $this->post('format_laporan', true);
		$judulLokasi = 'Semua';
		if ($tipe_laporan == 'v1') {
			/* Supaya lokasi urut HO,RO,MILL,ESTATE */
			$lokasi = array();
			$res_lokasi   = $this->db->query("select * from gbm_organisasi where tipe in ('HO')")->result_array();
			foreach ($res_lokasi as $key => $value) {
				$lokasi[] = $value;
			}
			$res_lokasi   = $this->db->query("select * from gbm_organisasi where tipe in ('RO')")->result_array();
			foreach ($res_lokasi as $key => $value) {
				$lokasi[] = $value;
			}
			$res_lokasi   = $this->db->query("select * from gbm_organisasi where tipe in ('MILL')")->result_array();
			foreach ($res_lokasi as $key => $value) {
				$lokasi[] = $value;
			}
			$res_lokasi   = $this->db->query("select * from gbm_organisasi where tipe in ('ESTATE')")->result_array();
			foreach ($res_lokasi as $key => $value) {
				$lokasi[] = $value;
			}
			/* END  Supaya lokasi urut HO,RO,MILL,ESTATE */
		} else if ($tipe_laporan == 'v2') {
			/* Supaya lokasi urut HO,RO,MILL,ESTATE */
			$lokasi = array();
			$res_lokasi   = $this->db->query("select * from gbm_organisasi where tipe in ('HO')")->result_array();
			foreach ($res_lokasi as $key => $value) {
				$lokasi[] = $value;
			}
			$res_lokasi   = $this->db->query("select * from gbm_organisasi where tipe in ('RO')")->result_array();
			foreach ($res_lokasi as $key => $value) {
				$lokasi[] = $value;
			}
			$res_lokasi   = $this->db->query("select * from gbm_organisasi where tipe in ('MILL')")->result_array();
			foreach ($res_lokasi as $key => $value) {
				$lokasi[] = $value;
			}
			$res_lokasi   = $this->db->query("select * from gbm_organisasi where tipe in ('ESTATE') and kode ='SBNE'")->result_array();
			foreach ($res_lokasi as $key => $value) {
				$lokasi[] = $value;
			}
			/* END  Supaya lokasi urut HO,RO,MILL,ESTATE */
		} else if ($tipe_laporan == 'v3') {
			$res_lokasi   = $this->db->query("select * from gbm_organisasi where tipe in ('ESTATE') and kode ='SBME'")->result_array();
			foreach ($res_lokasi as $key => $value) {
				$lokasi[] = $value;
			}
		}
		$jumlah_lokasi = count($lokasi);

		/* Ambil AKUN */
		$queryAkunAktiva = "select * from acc_akun
       			where is_transaksi_akun=1 
				  and (kode like '1%')  
				   order by kode ";
		/* Ambil AKUN */
		$queryAkunPasiva = "select * from acc_akun
       			where is_transaksi_akun=1 
				  and (kode like '2%' or kode like '3%'    )  
				   order by kode ";

		$res_akun_laba_ditahan = $this->db->query("SELECT * from acc_auto_jurnal 
				   where kode='LABA_RUGI_DITAHAN'")->row_array();
		if (empty($res_akun_laba_ditahan)) {
			//$this->set_response(array("status" => "NOT OK", "data" => "LABA_RUGI_BERJALAN Tidak ada Data Setting Jurnal"), REST_Controller::HTTP_NOT_FOUND);
			//return;
		}
		$akun_laba_ditahan = $res_akun_laba_ditahan['acc_akun_id'];
		$res_akun_laba_berjalan = $this->db->query("SELECT * from acc_auto_jurnal 
				   where kode='LABA_RUGI_BERJALAN'")->row_array();
		if (empty($res_akun_laba_berjalan)) {
			//$this->set_response(array("status" => "NOT OK", "data" => "LABA_RUGI_BERJALAN Tidak ada Data Setting Jurnal"), REST_Controller::HTTP_NOT_FOUND);
			//return;
		}
		$akun_laba_berjalan = $res_akun_laba_berjalan['acc_akun_id'];
		$res = array();
		$tahun_ini = substr($tanggal_akhir, 0, 4);
		$tahun_lalu = ((int)$tahun_ini) - 1;
		$tanggal_lalu = $tahun_lalu . '-12-31';
		$tanggal_mulai = $tahun_ini . '-01-01';

		// var_dump($tanggal_mulai);exit();
		$this->load->helper("terbilang");
		$this->load->helper("antech_helper");
		$this->load->library('pdfgenerator');
		if ($format_laporan == 'view') {
			$html = get_header_report_v2();
		} else {
			$html = get_header_pdf_report();
		}
		$html = $html . '
		<div class="row">
		<div class="span12">
			<br>
		  <div class="kop-print">
		  <div class="kop-nama">PT. ANNAJAH TECHNOLOGY INDONESIA</div>
		  <div class="kop-info"> Komplek Vila Sehati B12 , Tapos - Depok</div>
		  <div class="kop-info">Telp : 081387373939</div>
		</div>
			<hr class="kop-print-hr">
		</div>
		</div>
		<h3 class="title">LAPORAN NERACA V1</h3>
  	<table class="no_border" style="width:30%">
			
			
			<tr>	
					<td style="width:20%">Periode</td>
					<td>:</td>
					<td>' . $tanggal_akhir . '</td>
			</tr>

			
	</table>
	<br>';
		$html = $html . "
<table   border='1' width='100%' style='border-collapse: collapse;'>
<thead>
<tr>
	<td rowspan=2>Kode Akun</td>
	<td rowspan=2>Kode Akun</td>
	<td colspan=" . $jumlah_lokasi . "  style='text-align: center'> Unit </td>
	<td rowspan=2  style='text-align: center'>TOTAL</td>
</tr>
";

		$html = $html . "<tr>";
		/* BUAT JUDUL TABEL BERDASARKAN LOKASI */
		foreach ($lokasi as $key => $value) {
			$html = $html . "<td style='text-align: center'>" . $value['nama'] . "</td>";
		}
		$html = $html . "</tr> </thead>";

		$nourut = 0;
		/* Aktiva */
		$grandtotal = 0;
		$total_per_lokasi = array();
		$akun_aktiva   = $this->db->query($queryAkunAktiva)->result_array();
		foreach ($akun_aktiva as $key => $akun) {
			$nourut = $nourut + 1;
			$total = 0;
			$html = $html . "<tr> ";
			$html = $html . "<td style='text-align: left'>" . $akun['kode'] . " </td>";
			$html = $html . "<td style='text-align: left'>" . $akun['nama'] . " </td>";
			foreach ($lokasi as $key_lok => $lokasi_array) {
				$query1 = "SELECT b.acc_akun_id,sum(debet-kredit)as jumlah FROM acc_jurnal_ht a inner join acc_jurnal_dt b 
				on a.id=b.jurnal_id
				where b.acc_akun_id=" . $akun['id'] . " 
				and a.tanggal <= '" . $tanggal_akhir . "' and b.lokasi_id=" . $lokasi_array['id'] . "
				group by b.acc_akun_id ;";

				$resJumlah  = $this->db->query($query1)->row_array();
				$jumlah = (!empty($resJumlah['jumlah'])) ? $resJumlah['jumlah'] : 0;


				if (array_key_exists($lokasi_array['id'], $total_per_lokasi)) {
					$total_per_lokasi[$lokasi_array['id']] = $total_per_lokasi[$lokasi_array['id']] + $jumlah;
				} else {
					$total_per_lokasi[$lokasi_array['id']] = $jumlah;
				}

				$total = $total + $jumlah;
				$grandtotal = $grandtotal + $jumlah;
				$html = $html . "<td style='text-align: center'>" . $this->format_number_report($jumlah) . " </td>";
			}
			$html = $html . "<td style='text-align: center'>" . $this->format_number_report($total) . " </td>";
			$html = $html . "</tr>";
		}
		$html = $html . "<tr>";
		$html = $html . "<td style='text-align: center'> </td>";
		$html = $html . "<td style='text-align: center'><b>TOTAL AKTIVA</b></td>";
		foreach ($lokasi as $key_lok => $lokasi_array) {

			$html = $html . "<td style='text-align: center'><b>" . $this->format_number_report($total_per_lokasi[$lokasi_array['id']]) . " </b></td>";
		}
		$html = $html . "<td style='text-align: center'><b>" . $this->format_number_report($grandtotal) . " </b></td>";
		$html = $html . "</tr>";

		/* hitung laba ditahan  */
		// $labaditahanTahunLalu = array();
		// foreach ($lokasi as $key_lok => $lokasi_array) {
		// 	$jLaba1 = 0;
		// 	$jLaba2 = 0;
		// 	$jLaba3 = 0;
		// 	$labarugi_ditahan = 0;
		// 	/* hitung laba rugi pendapatan-biaya  */
		// 	$query1 = "SELECT sum(debet-kredit)as jumlah FROM acc_jurnal_ht a 
		// 			inner join acc_jurnal_dt b 	on a.id=b.jurnal_id
		// 			inner join acc_akun c on c.id=b.acc_akun_id
		// 			where ( c.kode like '5%' or c.kode like '6%' or c.kode like '7%' or c.kode like '8%' or c.kode like '9%') 
		// 			and a.tanggal < '" . $tanggal_mulai . "' and b.lokasi_id=" . $lokasi_array['id'] . " ;";



		// 	$resJLaba  = $this->db->query($query1)->row_array();
		// 	$jLaba1 = (!empty($resJLaba['jumlah'])) ? ($resJLaba['jumlah'] * -1) : 0;

		// 	// if ($tanggal_mulai>'2022-12-01'){ // Jika di atas input saldo awal
		// 	/* hitung laba rugi saldo awal  */
		// 	$query1 = "SELECT b.acc_akun_id,sum(debet-kredit)as jumlah FROM acc_jurnal_ht a inner join acc_jurnal_dt b 
		// 			on a.id=b.jurnal_id
		// 			where b.acc_akun_id=" . $akun_laba_berjalan . " 
		// 			and a.tanggal < '" . $tanggal_mulai . "' and b.lokasi_id=" . $lokasi_array['id'] . "
		// 			group by b.acc_akun_id ;";
		// 	$resJLaba  = $this->db->query($query1)->row_array();
		// 	$jLaba2 = (!empty($resJLaba['jumlah'])) ? ($resJLaba['jumlah'] * -1) : 0;
		// 	// }
		// 	/* hitung laba rugi laba ditahan  saldo awal */
		// 	$query1 = "SELECT b.acc_akun_id,sum(debet-kredit)as jumlah FROM acc_jurnal_ht a inner join acc_jurnal_dt b 
		// 		on a.id=b.jurnal_id
		// 		where b.acc_akun_id=" . $akun_laba_ditahan . " 
		// 		and a.tanggal < '" . $tanggal_mulai . "' and b.lokasi_id=" . $lokasi_array['id'] . "
		// 		group by b.acc_akun_id ;";
		// 	$resJLaba  = $this->db->query($query1)->row_array();
		// 	$jLaba3 = (!empty($resJLaba['jumlah'])) ? ($resJLaba['jumlah'] * -1) : 0;
		// 	$labarugi_ditahan = $jLaba1 + $jLaba2 + $jLaba3;
		// 	$labaditahanTahunLalu[$lokasi_array['id']] = $labarugi_ditahan;
		// }
		// var_dump($labaditahanTahunLalu);exit();
		/* Pasiva */
		$grandtotal = 0;
		$total_per_lokasi = array();
		$akun_pasiva   = $this->db->query($queryAkunPasiva)->result_array();
		foreach ($akun_pasiva as $key => $akun) {
			$nourut = $nourut + 1;
			$total = 0;
			$html = $html . "<tr> ";
			$html = $html . "<td style='text-align: left'>" . $akun['kode'] . " </td>";
			$html = $html . "<td style='text-align: left'>" . $akun['nama'] . " </td>";
			foreach ($lokasi as $key_lok => $lokasi_array) {


				/* hitung akun laba berjalan */
				if ($akun_laba_berjalan == $akun['id']) {
					// $query2 = "SELECT sum(debet-kredit)as jumlah FROM acc_jurnal_ht a 
					// inner join acc_jurnal_dt b 	on a.id=b.jurnal_id
					// inner join acc_akun c on c.id=b.acc_akun_id
					// where (c.kode like '5%' or c.kode like '6%' or c.kode like '7%' or c.kode like '8%' or c.kode like '9%') 
					// and (a.tanggal >= '" . $tanggal_mulai . "' and a.tanggal <= '" . $tanggal_akhir . "') and b.lokasi_id=" . $lokasi_array['id'] . " ;";
					$query2 = "SELECT sum(debet-kredit)as jumlah FROM acc_jurnal_ht a 
					inner join acc_jurnal_dt b 	on a.id=b.jurnal_id
					inner join acc_akun c on c.id=b.acc_akun_id
					where (c.kode like '5%' or c.kode like '6%' or c.kode like '7%' or c.kode like '8%' or c.kode like '9%') 
					and (a.tanggal  <= '" . $tanggal_akhir . "') and b.lokasi_id=" . $lokasi_array['id'] . " ;";

					$resJumlahLaba  = $this->db->query($query2)->row_array();
					$jumlahLaba = (!empty($resJumlahLaba['jumlah'])) ? ($resJumlahLaba['jumlah'] * -1) : 0;
					$jumlah =  $jumlahLaba;

					// if ($tanggal_mulai <= '2022-12-31') { // SAldo awal diinput tgl 31 Des 2022
						/* hitung akun laba berjalan yg dinput manual di jurnal */
						$queryLabaBerjalan = "SELECT b.acc_akun_id,sum(debet-kredit)as jumlah FROM acc_jurnal_ht a inner join acc_jurnal_dt b 
						on a.id=b.jurnal_id
						where b.acc_akun_id=" . $akun['id'] . " 
						and a.tanggal <= '" . $tanggal_akhir . "' and b.lokasi_id=" . $lokasi_array['id'] . "
						group by b.acc_akun_id ;";
						$resJumlahLabaBerjalan  = $this->db->query($queryLabaBerjalan)->row_array();
						$JumlahLabaBerjalan = (!empty($resJumlahLabaBerjalan['jumlah'])) ? ($resJumlahLabaBerjalan['jumlah'] * -1) : 0;
						$jumlah = $jumlah + $JumlahLabaBerjalan;
					// }
				} elseif ($akun_laba_ditahan == $akun['id']) {
					// $jumlah = $labaditahanTahunLalu[$lokasi_array['id']];
					$jumlah =0;

					// if ($tanggal_mulai <= '2022-12-31') { // SAldo awal diinput tgl 31 Des 2022
						/* hitung akun laba berjalan yg dinput manual di jurnal */
						$queryLabaDitahan = "SELECT b.acc_akun_id,sum(debet-kredit)as jumlah FROM acc_jurnal_ht a inner join acc_jurnal_dt b 
						on a.id=b.jurnal_id
						where b.acc_akun_id=" . $akun['id'] . " 
						and a.tanggal <= '" . $tanggal_akhir . "' and b.lokasi_id=" . $lokasi_array['id'] . "
						group by b.acc_akun_id ;";
						
						$resJumlahLabaDitahan  = $this->db->query($queryLabaDitahan)->row_array();

						$jumlahLabaDitahan = (!empty($resJumlahLabaDitahan['jumlah'])) ? ($resJumlahLabaDitahan['jumlah'] * -1) : 0;

						$jumlah = $jumlah + $jumlahLabaDitahan;
					// }
				} else {
					$query1 = "SELECT b.acc_akun_id,sum(debet-kredit)as jumlah FROM acc_jurnal_ht a inner join acc_jurnal_dt b 
					on a.id=b.jurnal_id
					where b.acc_akun_id=" . $akun['id'] . " 
					and a.tanggal <= '" . $tanggal_akhir . "' and b.lokasi_id=" . $lokasi_array['id'] . "
					group by b.acc_akun_id ;";

					$resJumlah  = $this->db->query($query1)->row_array();
					$jumlah = (!empty($resJumlah['jumlah'])) ? ($resJumlah['jumlah'] * -1) : 0;
				}
				if (array_key_exists($lokasi_array['id'], $total_per_lokasi)) {
					$total_per_lokasi[$lokasi_array['id']] = $total_per_lokasi[$lokasi_array['id']] + $jumlah;
				} else {
					$total_per_lokasi[$lokasi_array['id']] = $jumlah;
				}

				$total = $total + $jumlah;
				$grandtotal = $grandtotal + $jumlah;
				$html = $html . "<td style='text-align: center'>" . $this->format_number_report($jumlah) . " </td>";
			}
			$html = $html . "<td style='text-align: center'>" . $this->format_number_report($total) . " </td>";
			$html = $html . "</tr>";
		}

		$html = $html . "<tr>";
		$html = $html . "<td style='text-align: center'> </td>";
		$html = $html . "<td style='text-align: center'><b>TOTAL HUTANG & MODAL</b></td>";
		foreach ($lokasi as $key_lok => $lokasi_array) {

			$html = $html . "<td style='text-align: center'><b>" . $this->format_number_report($total_per_lokasi[$lokasi_array['id']]) . " </b></td>";
		}
		$html = $html . "<td style='text-align: center'><b>" . $this->format_number_report($grandtotal) . " </b></td>";
		$html = $html . "</tr>";



		$html = $html . "</table>";



		if ($format_laporan == 'xls') {
			$reader = new \PhpOffice\PhpSpreadsheet\Reader\Html;
			$spreadsheet = $reader->loadFromString($html);
			// $reader->setSheetIndex(1);
			//$spreadhseet = $reader->loadFromString($secondHtmlString, $spreadsheet);
			$objWriter = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
			header("Pragma: public");
			header("Expires: 0");
			header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
			header("Content-Type: application/force-download");
			header("Content-Type: application/octet-stream");
			header("Content-Type: application/download");
			header("Content-Disposition: attachment;filename=test.xlsx");
			header("Content-Transfer-Encoding: binary ");

			ob_end_clean();
			ob_start();
			$objWriter->save('php://output');
		} else if ($format_laporan == 'view') {
			echo $html;
		} else {
			$filename = 'report_' . time();
			$this->pdfgenerator->generate($html, $filename, true, 'A3', 'landscape');
		}
	}
	function laporan_neraca_inti_v1()
	{
		error_reporting(0);
		$tanggal_akhir =  $this->post('tgl_akhir', true);
		$format_laporan =  $this->post('format_laporan', true);
		$judulLokasi = 'Semua';
		/* Supaya lokasi urut HO,RO,MILL,ESTATE */
		$lokasi = array();
		$res_lokasi   = $this->db->query("select * from gbm_organisasi where tipe in ('HO')")->result_array();
		foreach ($res_lokasi as $key => $value) {
			$lokasi[] = $value;
		}
		$res_lokasi   = $this->db->query("select * from gbm_organisasi where tipe in ('RO')")->result_array();
		foreach ($res_lokasi as $key => $value) {
			$lokasi[] = $value;
		}
		$res_lokasi   = $this->db->query("select * from gbm_organisasi where tipe in ('MILL')")->result_array();
		foreach ($res_lokasi as $key => $value) {
			$lokasi[] = $value;
		}
		$res_lokasi   = $this->db->query("select * from gbm_organisasi where tipe in ('ESTATE')  and kode='SBNE'")->result_array();
		foreach ($res_lokasi as $key => $value) {
			$lokasi[] = $value;
		}
		/* END  Supaya lokasi urut HO,RO,MILL,ESTATE */
		$jumlah_lokasi = count($lokasi);

		/* Ambil AKUN */
		$queryAkunAktiva = "select * from acc_akun
       			where is_transaksi_akun=1 
				  and (kode like '1%')  
				   order by kode ";
		/* Ambil AKUN */
		$queryAkunPasiva = "select * from acc_akun
       			where is_transaksi_akun=1 
				  and (kode like '2%' or kode like '3%'    )  
				   order by kode ";

		$res_akun_laba_ditahan = $this->db->query("SELECT * from acc_auto_jurnal 
				   where kode='LABA_RUGI_DITAHAN'")->row_array();
		if (empty($res_akun_laba_ditahan)) {
			//$this->set_response(array("status" => "NOT OK", "data" => "LABA_RUGI_BERJALAN Tidak ada Data Setting Jurnal"), REST_Controller::HTTP_NOT_FOUND);
			//return;
		}
		$akun_laba_ditahan = $res_akun_laba_ditahan['acc_akun_id'];
		$res_akun_laba_berjalan = $this->db->query("SELECT * from acc_auto_jurnal 
				   where kode='LABA_RUGI_BERJALAN'")->row_array();
		if (empty($res_akun_laba_berjalan)) {
			//$this->set_response(array("status" => "NOT OK", "data" => "LABA_RUGI_BERJALAN Tidak ada Data Setting Jurnal"), REST_Controller::HTTP_NOT_FOUND);
			//return;
		}
		$akun_laba_berjalan = $res_akun_laba_berjalan['acc_akun_id'];
		$res = array();
		$tahun_ini = substr($tanggal_akhir, 0, 4);
		$tahun_lalu = ((int)$tahun_ini) - 1;
		$tanggal_lalu = $tahun_lalu . '-12-31';
		$tanggal_mulai = $tahun_ini . '-01-01';

		// var_dump($tanggal_mulai);exit();
		$this->load->helper("terbilang");
		$this->load->helper("antech_helper");
		$this->load->library('pdfgenerator');
		if ($format_laporan == 'view') {
			$html = get_header_report_v2();
		} else {
			$html = get_header_pdf_report();
		}
		$html = $html . '
		<div class="row">
		<div class="span12">
			<br>
		  <div class="kop-print">
		  <div class="kop-nama">PT. ANNAJAH TECHNOLOGY INDONESIA</div>
		  <div class="kop-info"> Komplek Vila Sehati B12 , Tapos - Depok</div>
		  <div class="kop-info">Telp : 081387373939</div>
		</div>
			<hr class="kop-print-hr">
		</div>
		</div>
		<h3 class="title">LAPORAN NERACA V1</h3>
  	<table class="no_border" style="width:30%">
			
			
			<tr>	
					<td style="width:20%">Periode</td>
					<td>:</td>
					<td>' . $tanggal_akhir . '</td>
			</tr>

			
	</table>
	<br>';
		$html = $html . "
<table   border='1' width='100%' style='border-collapse: collapse;'>
<thead>
<tr>
	<td rowspan=2>Kode Akun</td>
	<td rowspan=2>Kode Akun</td>
	<td colspan=" . $jumlah_lokasi . "  style='text-align: center'> Unit </td>
	<td rowspan=2  style='text-align: center'>TOTAL</td>
</tr>
";

		$html = $html . "<tr>";
		/* BUAT JUDUL TABEL BERDASARKAN LOKASI */
		foreach ($lokasi as $key => $value) {
			$html = $html . "<td style='text-align: center'>" . $value['nama'] . "</td>";
		}
		$html = $html . "</tr> </thead>";

		$nourut = 0;
		/* Aktiva */
		$grandtotal = 0;
		$total_per_lokasi = array();
		$akun_aktiva   = $this->db->query($queryAkunAktiva)->result_array();
		foreach ($akun_aktiva as $key => $akun) {
			$nourut = $nourut + 1;
			$total = 0;
			$html = $html . "<tr> ";
			$html = $html . "<td style='text-align: left'>" . $akun['kode'] . " </td>";
			$html = $html . "<td style='text-align: left'>" . $akun['nama'] . " </td>";
			foreach ($lokasi as $key_lok => $lokasi_array) {
				$query1 = "SELECT b.acc_akun_id,sum(debet-kredit)as jumlah FROM acc_jurnal_ht a inner join acc_jurnal_dt b 
				on a.id=b.jurnal_id
				where b.acc_akun_id=" . $akun['id'] . " 
				and a.tanggal <= '" . $tanggal_akhir . "' and b.lokasi_id=" . $lokasi_array['id'] . "
				group by b.acc_akun_id ;";

				$resJumlah  = $this->db->query($query1)->row_array();
				$jumlah = (!empty($resJumlah['jumlah'])) ? $resJumlah['jumlah'] : 0;


				if (array_key_exists($lokasi_array['id'], $total_per_lokasi)) {
					$total_per_lokasi[$lokasi_array['id']] = $total_per_lokasi[$lokasi_array['id']] + $jumlah;
				} else {
					$total_per_lokasi[$lokasi_array['id']] = $jumlah;
				}

				$total = $total + $jumlah;
				$grandtotal = $grandtotal + $jumlah;
				$html = $html . "<td style='text-align: center'>" . $this->format_number_report($jumlah) . " </td>";
			}
			$html = $html . "<td style='text-align: center'>" . $this->format_number_report($total) . " </td>";
			$html = $html . "</tr>";
		}
		$html = $html . "<tr>";
		$html = $html . "<td style='text-align: center'> </td>";
		$html = $html . "<td style='text-align: center'><b>TOTAL AKTIVA</b></td>";
		foreach ($lokasi as $key_lok => $lokasi_array) {

			$html = $html . "<td style='text-align: center'><b>" . $this->format_number_report($total_per_lokasi[$lokasi_array['id']]) . " </b></td>";
		}
		$html = $html . "<td style='text-align: center'><b>" . $this->format_number_report($grandtotal) . " </b></td>";
		$html = $html . "</tr>";

		/* hitung laba ditahan  */
		$labaditahanTahunLalu = array();
		foreach ($lokasi as $key_lok => $lokasi_array) {
			$jLaba1 = 0;
			$jLaba2 = 0;
			$jLaba3 = 0;
			$labarugi_ditahan = 0;
			/* hitung laba rugi pendapatan-biaya  */
			$query1 = "SELECT sum(debet-kredit)as jumlah FROM acc_jurnal_ht a 
					inner join acc_jurnal_dt b 	on a.id=b.jurnal_id
					inner join acc_akun c on c.id=b.acc_akun_id
					where ( c.kode like '5%' or c.kode like '6%' or c.kode like '7%' or c.kode like '8%' or c.kode like '9%') 
					and a.tanggal < '" . $tanggal_mulai . "' and b.lokasi_id=" . $lokasi_array['id'] . " ;";


			$resJLaba  = $this->db->query($query1)->row_array();
			$jLaba1 = (!empty($resJLaba['jumlah'])) ? ($resJLaba['jumlah'] * -1) : 0;

			// if ($tanggal_mulai>'2022-12-01'){ // Jika di atas input saldo awal
			/* hitung laba rugi saldo awal  */
			$query1 = "SELECT b.acc_akun_id,sum(debet-kredit)as jumlah FROM acc_jurnal_ht a inner join acc_jurnal_dt b 
					on a.id=b.jurnal_id
					where b.acc_akun_id=" . $akun_laba_berjalan . " 
					and a.tanggal < '" . $tanggal_mulai . "' and b.lokasi_id=" . $lokasi_array['id'] . "
					group by b.acc_akun_id ;";
			$resJLaba  = $this->db->query($query1)->row_array();
			$jLaba2 = (!empty($resJLaba['jumlah'])) ? ($resJLaba['jumlah'] * -1) : 0;
			// }
			/* hitung laba rugi laba ditahan  saldo awal */
			$query1 = "SELECT b.acc_akun_id,sum(debet-kredit)as jumlah FROM acc_jurnal_ht a inner join acc_jurnal_dt b 
				on a.id=b.jurnal_id
				where b.acc_akun_id=" . $akun_laba_ditahan . " 
				and a.tanggal < '" . $tanggal_mulai . "' and b.lokasi_id=" . $lokasi_array['id'] . "
				group by b.acc_akun_id ;";
			$resJLaba  = $this->db->query($query1)->row_array();
			$jLaba3 = (!empty($resJLaba['jumlah'])) ? ($resJLaba['jumlah'] * -1) : 0;
			$labarugi_ditahan = $jLaba1 + $jLaba2 + $jLaba3;
			$labaditahanTahunLalu[$lokasi_array['id']] = $labarugi_ditahan;
		}
		// var_dump($labaditahanTahunLalu);exit();
		/* Pasiva */
		$grandtotal = 0;
		$total_per_lokasi = array();
		$akun_pasiva   = $this->db->query($queryAkunPasiva)->result_array();
		foreach ($akun_pasiva as $key => $akun) {
			$nourut = $nourut + 1;
			$total = 0;
			$html = $html . "<tr> ";
			$html = $html . "<td style='text-align: left'>" . $akun['kode'] . " </td>";
			$html = $html . "<td style='text-align: left'>" . $akun['nama'] . " </td>";
			foreach ($lokasi as $key_lok => $lokasi_array) {


				/* hitung akun laba berjalan */
				if ($akun_laba_berjalan == $akun['id']) {
					$query2 = "SELECT sum(debet-kredit)as jumlah FROM acc_jurnal_ht a 
					inner join acc_jurnal_dt b 	on a.id=b.jurnal_id
					inner join acc_akun c on c.id=b.acc_akun_id
					where (c.kode like '5%' or c.kode like '6%' or c.kode like '7%' or c.kode like '8%' or c.kode like '9%') 
					and (a.tanggal >= '" . $tanggal_mulai . "' and a.tanggal <= '" . $tanggal_akhir . "') and b.lokasi_id=" . $lokasi_array['id'] . " ;";

					$resJumlahLaba  = $this->db->query($query2)->row_array();
					$jumlahLaba = (!empty($resJumlahLaba['jumlah'])) ? ($resJumlahLaba['jumlah'] * -1) : 0;
					$jumlah =  $jumlahLaba;

					if ($tanggal_mulai <= '2022-12-31') { // SAldo awal diinput tgl 31 Des 2022
						/* hitung akun laba berjalan yg dinput manual di jurnal */
						$queryLabaBerjalan = "SELECT b.acc_akun_id,sum(debet-kredit)as jumlah FROM acc_jurnal_ht a inner join acc_jurnal_dt b 
						on a.id=b.jurnal_id
						where b.acc_akun_id=" . $akun['id'] . " 
						and a.tanggal <= '" . $tanggal_akhir . "' and b.lokasi_id=" . $lokasi_array['id'] . "
						group by b.acc_akun_id ;";
						$resJumlahLabaBerjalan  = $this->db->query($queryLabaBerjalan)->row_array();
						$JumlahLabaBerjalan = (!empty($resJumlahLabaBerjalan['jumlah'])) ? ($resJumlahLabaBerjalan['jumlah'] * -1) : 0;
						// var_dump();
						$jumlah = $jumlah + $JumlahLabaBerjalan;
					}
				} elseif ($akun_laba_ditahan == $akun['id']) {
					$jumlah = $labaditahanTahunLalu[$lokasi_array['id']];

					if ($tanggal_mulai <= '2022-12-31') { // SAldo awal diinput tgl 31 Des 2022
						/* hitung akun laba berjalan yg dinput manual di jurnal */
						$queryLabaDitahan = "SELECT b.acc_akun_id,sum(debet-kredit)as jumlah FROM acc_jurnal_ht a inner join acc_jurnal_dt b 
						on a.id=b.jurnal_id
						where b.acc_akun_id=" . $akun['id'] . " 
						and a.tanggal <= '" . $tanggal_akhir . "' and b.lokasi_id=" . $lokasi_array['id'] . "
						group by b.acc_akun_id ;";
						$resJumlahLabaDitahan  = $this->db->query($queryLabaDitahan)->row_array();

						$jumlahLabaDitahan = (!empty($resJumlahLabaDitahan['jumlah'])) ? ($resJumlahLabaDitahan['jumlah'] * -1) : 0;

						$jumlah = $jumlah + $jumlahLabaDitahan;
					}
				} else {
					$query1 = "SELECT b.acc_akun_id,sum(debet-kredit)as jumlah FROM acc_jurnal_ht a inner join acc_jurnal_dt b 
					on a.id=b.jurnal_id
					where b.acc_akun_id=" . $akun['id'] . " 
					and a.tanggal <= '" . $tanggal_akhir . "' and b.lokasi_id=" . $lokasi_array['id'] . "
					group by b.acc_akun_id ;";

					$resJumlah  = $this->db->query($query1)->row_array();
					$jumlah = (!empty($resJumlah['jumlah'])) ? ($resJumlah['jumlah'] * -1) : 0;
				}
				if (array_key_exists($lokasi_array['id'], $total_per_lokasi)) {
					$total_per_lokasi[$lokasi_array['id']] = $total_per_lokasi[$lokasi_array['id']] + $jumlah;
				} else {
					$total_per_lokasi[$lokasi_array['id']] = $jumlah;
				}

				$total = $total + $jumlah;
				$grandtotal = $grandtotal + $jumlah;
				$html = $html . "<td style='text-align: center'>" . $this->format_number_report($jumlah) . " </td>";
			}
			$html = $html . "<td style='text-align: center'>" . $this->format_number_report($total) . " </td>";
			$html = $html . "</tr>";
		}

		$html = $html . "<tr>";
		$html = $html . "<td style='text-align: center'> </td>";
		$html = $html . "<td style='text-align: center'><b>TOTAL HUTANG & MODAL</b></td>";
		foreach ($lokasi as $key_lok => $lokasi_array) {

			$html = $html . "<td style='text-align: center'><b>" . $this->format_number_report($total_per_lokasi[$lokasi_array['id']]) . " </b></td>";
		}
		$html = $html . "<td style='text-align: center'><b>" . $this->format_number_report($grandtotal) . " </b></td>";
		$html = $html . "</tr>";



		$html = $html . "</table>";



		if ($format_laporan == 'xls') {
			$reader = new \PhpOffice\PhpSpreadsheet\Reader\Html;
			$spreadsheet = $reader->loadFromString($html);
			// $reader->setSheetIndex(1);
			//$spreadhseet = $reader->loadFromString($secondHtmlString, $spreadsheet);
			$objWriter = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
			header("Pragma: public");
			header("Expires: 0");
			header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
			header("Content-Type: application/force-download");
			header("Content-Type: application/octet-stream");
			header("Content-Type: application/download");
			header("Content-Disposition: attachment;filename=test.xlsx");
			header("Content-Transfer-Encoding: binary ");

			ob_end_clean();
			ob_start();
			$objWriter->save('php://output');
		} else if ($format_laporan == 'view') {
			echo $html;
		} else {
			$filename = 'report_' . time();
			$this->pdfgenerator->generate($html, $filename, true, 'A3', 'landscape');
		}
	}
	function laporan_neraca_v2($tipe_laporan)
	{
		error_reporting(0);
		$tanggal_akhir =  $this->post('tgl_akhir', true);
		$format_laporan =  $this->post('format_laporan', true);
		$judulLokasi = 'Semua';

		/* Ambil AKUN */
		$queryAkunAktiva = "select * from acc_akun
       			where is_transaksi_akun=1 
				  and (kode like '1%')  
				   order by kode ";
		/* Ambil AKUN */
		$queryAkunPasiva = "select * from acc_akun
       			where is_transaksi_akun=1 
				  and (kode like '2%' or kode like '3%'    )  
				   order by kode ";

		$res_akun_laba_ditahan = $this->db->query("SELECT * from acc_auto_jurnal 
				   where kode='LABA_RUGI_DITAHAN'")->row_array();
		if (empty($res_akun_laba_ditahan)) {
			//$this->set_response(array("status" => "NOT OK", "data" => "LABA_RUGI_BERJALAN Tidak ada Data Setting Jurnal"), REST_Controller::HTTP_NOT_FOUND);
			//return;
		}
		$akun_laba_ditahan = $res_akun_laba_ditahan['acc_akun_id'];
		$res_akun_laba_berjalan = $this->db->query("SELECT * from acc_auto_jurnal 
				   where kode='LABA_RUGI_BERJALAN'")->row_array();
		if (empty($res_akun_laba_berjalan)) {
			//$this->set_response(array("status" => "NOT OK", "data" => "LABA_RUGI_BERJALAN Tidak ada Data Setting Jurnal"), REST_Controller::HTTP_NOT_FOUND);
			//return;
		}
		$akun_laba_berjalan = $res_akun_laba_berjalan['acc_akun_id'];
		$res = array();
		$tahun_ini = substr($tanggal_akhir, 0, 4);
		$tahun_lalu = ((int)$tahun_ini) - 1;
		$tanggal_lalu = $tahun_lalu . '-12-31';
		$tanggal_mulai = $tahun_ini . '-01-01';

		$res = array();
		// var_dump($results);
		$this->load->helper("terbilang");
		$this->load->helper("antech_helper");
		$this->load->library('pdfgenerator');
		if ($format_laporan == 'view') {
			$html = get_header_report_v2();
		} else {
			$html = get_header_pdf_report();
		}
		$html = $html . '
		<div class="row">
		<div class="span12">
			<br>
		  <div class="kop-print">
		  <div class="kop-nama">PT. ANNAJAH TECHNOLOGY INDONESIA</div>
		  <div class="kop-info"> Komplek Vila Sehati B12 , Tapos - Depok</div>
		  <div class="kop-info">Telp : 081387373939</div>
		</div>
			<hr class="kop-print-hr">
		</div>
		</div>
		<h3 class="title">LAPORAN NERACA V2</h3>
		<table class="no_border" style="width:30%">
				  
				  
				  <tr>	
						  <td style="width:20%">Periode</td>
						  <td>:</td>
						  <td>' . $tanggal_akhir . '</td>
				  </tr>
	  
				  
		  </table>
		  <br>
';
		$html = $html . "
<table   border='1' width='100%' style='border-collapse: collapse;'>
<thead>
<tr>
	<td rowspan=1>Kode Akun</td>
	<td rowspan=1>Kode Akun</td>
	<td rowspan=1  style='text-align: right'>JUMLAH</td>
</tr>
 </thead>
";


		$nourut = 0;
		/* Aktiva */
		$grandtotal = 0;
		$akun_aktiva   = $this->db->query($queryAkunAktiva)->result_array();
		foreach ($akun_aktiva as $key => $akun) {
			$nourut = $nourut + 1;
			$total = 0;
			$html = $html . "<tr> ";
			$html = $html . "<td style='text-align: left'>" . $akun['kode'] . " </td>";
			$html = $html . "<td style='text-align: left'>" . $akun['nama'] . " </td>";
			if ($tipe_laporan == 'v1') {
				$query1 = "SELECT b.acc_akun_id,sum(debet-kredit)as jumlah FROM acc_jurnal_ht a inner join acc_jurnal_dt b 
				on a.id=b.jurnal_id
				where b.acc_akun_id=" . $akun['id'] . " 
				and a.tanggal <= '" . $tanggal_akhir . "' 
				group by b.acc_akun_id ;";
			} else  if ($tipe_laporan == 'v2') { // Selain SBNE
				$query1 = "SELECT b.acc_akun_id,sum(debet-kredit)as jumlah FROM acc_jurnal_ht a inner join acc_jurnal_dt b 
				on a.id=b.jurnal_id
				inner join gbm_organisasi c on b.lokasi_id=c.id
				where b.acc_akun_id=" . $akun['id'] . " 
				and a.tanggal <= '" . $tanggal_akhir . "' 
				and c.kode not in('SBME')
				group by b.acc_akun_id ;";
			} elseif ($tipe_laporan == 'v3') { // SBME saja
				$query1 = "SELECT b.acc_akun_id,sum(debet-kredit)as jumlah FROM acc_jurnal_ht a inner join acc_jurnal_dt b 
				on a.id=b.jurnal_id
				inner join gbm_organisasi c on b.lokasi_id=c.id
				where b.acc_akun_id=" . $akun['id'] . " 
				and a.tanggal <= '" . $tanggal_akhir . "' 
				and c.kode =('SBME')
				group by b.acc_akun_id ;";
			}

			$resJumlah  = $this->db->query($query1)->row_array();
			$jumlah = (!empty($resJumlah['jumlah'])) ? $resJumlah['jumlah'] : 0;


			$total = $total + $jumlah;
			$grandtotal = $grandtotal + $jumlah;
			$html = $html . "<td style='text-align: right'>" . $this->format_number_report($jumlah) . " </td>";


			$html = $html . "</tr>";
		}
		$html = $html . "<tr>";
		$html = $html . "<td style='text-align: center'> </td>";
		$html = $html . "<td style='text-align: center'><b>TOTAL AKTIVA</b></td>";

		$html = $html . "<td style='text-align: right'><b>" . $this->format_number_report($grandtotal) . " </b></td>";
		$html = $html . "</tr>";


		/* hitung laba ditahan  */
		$labaditahanTahunLalu = 0;

		$jLaba1 = 0;
		$jLaba2 = 0;
		$jLaba3 = 0;
		$labarugi_ditahan = 0;
		/* hitung laba rugi pendapatan-biaya  */
		if ($tipe_laporan == 'v1') {
			// $query1 = "SELECT sum(debet-kredit)as jumlah FROM acc_jurnal_ht a 
			// 		inner join acc_jurnal_dt b 	on a.id=b.jurnal_id
			// 		inner join acc_akun c on c.id=b.acc_akun_id
			// 		where ( c.kode like '5%' or c.kode like '6%' or c.kode like '7%' or c.kode like '8%' or c.kode like '9%') 
			// 		and a.tanggal < '" . $tanggal_mulai . "' ";
			$query1 = "SELECT sum(debet-kredit)as jumlah FROM acc_jurnal_ht a 
					inner join acc_jurnal_dt b 	on a.id=b.jurnal_id
					inner join acc_akun c on c.id=b.acc_akun_id
					where ( c.kode like '5%' or c.kode like '6%' or c.kode like '7%' or c.kode like '8%' or c.kode like '9%') 
					and a.tanggal <= '" . $tanggal_akhir . "' ";
		} else 	if ($tipe_laporan == 'v2') {
			$query1 = "SELECT sum(debet-kredit)as jumlah FROM acc_jurnal_ht a 
			inner join acc_jurnal_dt b 	on a.id=b.jurnal_id
			inner join acc_akun c on c.id=b.acc_akun_id
			inner join gbm_organisasi d on b.lokasi_id=d.id
			where ( c.kode like '5%' or c.kode like '6%' or c.kode like '7%' or c.kode like '8%' or c.kode like '9%') 
			and d.kode not in('SBME')
			and a.tanggal < '" . $tanggal_mulai . "' ";
		} else 	if ($tipe_laporan == 'v3') {
			$query1 = "SELECT sum(debet-kredit)as jumlah FROM acc_jurnal_ht a 
			inner join acc_jurnal_dt b 	on a.id=b.jurnal_id
			inner join acc_akun c on c.id=b.acc_akun_id
			inner join gbm_organisasi d on b.lokasi_id=d.id
			where ( c.kode like '5%' or c.kode like '6%' or c.kode like '7%' or c.kode like '8%' or c.kode like '9%') 
			and d.kode  in('SBME')
			and a.tanggal < '" . $tanggal_mulai . "' ";
		}


		$resJLaba  = $this->db->query($query1)->row_array();
		$jLaba1 = (!empty($resJLaba['jumlah'])) ? ($resJLaba['jumlah'] * -1) : 0;

		// if ($tanggal_mulai>'2022-12-01'){ // Jika di atas input saldo awal
		/* hitung laba rugi saldo awal  */
		if ($tipe_laporan == 'v1') {
			$query1 = "SELECT b.acc_akun_id,sum(debet-kredit)as jumlah FROM acc_jurnal_ht a inner join acc_jurnal_dt b 
					on a.id=b.jurnal_id
					where b.acc_akun_id=" . $akun_laba_berjalan . " 
					and a.tanggal < '" . $tanggal_mulai . "' 
					group by b.acc_akun_id ;";
		} else if ($tipe_laporan == 'v2') {
			$query1 = "SELECT b.acc_akun_id,sum(debet-kredit)as jumlah FROM acc_jurnal_ht a inner join acc_jurnal_dt b 
					on a.id=b.jurnal_id
					inner join gbm_organisasi c on b.lokasi_id=c.id
					where b.acc_akun_id=" . $akun_laba_berjalan . " 
					and a.tanggal < '" . $tanggal_mulai . "' 
					and c.kode not in('SBME') 
					group by b.acc_akun_id ;";
		} else if ($tipe_laporan == 'v3') {
			$query1 = "SELECT b.acc_akun_id,sum(debet-kredit)as jumlah FROM acc_jurnal_ht a inner join acc_jurnal_dt b 
					on a.id=b.jurnal_id
					inner join gbm_organisasi c on b.lokasi_id=c.id
					where b.acc_akun_id=" . $akun_laba_berjalan . " 
					and a.tanggal < '" . $tanggal_mulai . "' 
					and c.kode  in('SBME')
					group by b.acc_akun_id ;";
		}
		$resJLaba  = $this->db->query($query1)->row_array();
		$jLaba2 = (!empty($resJLaba['jumlah'])) ? ($resJLaba['jumlah'] * -1) : 0;
		// }
		/* hitung laba rugi laba ditahan  */
		if ($tipe_laporan == 'v1') {
			// $query1 = "SELECT b.acc_akun_id,sum(debet-kredit)as jumlah FROM acc_jurnal_ht a inner join acc_jurnal_dt b 
			// 	on a.id=b.jurnal_id
			// 	where b.acc_akun_id=" . $akun_laba_ditahan . " 
			// 	and a.tanggal < '" . $tanggal_mulai . "'
			// 	group by b.acc_akun_id ;";

			$query1 = "SELECT b.acc_akun_id,sum(debet-kredit)as jumlah FROM acc_jurnal_ht a inner join acc_jurnal_dt b 
				on a.id=b.jurnal_id
				where b.acc_akun_id=" . $akun_laba_ditahan . " 
				and a.tanggal <= '" . $tanggal_akhir . "'
				group by b.acc_akun_id ;";
		} else if ($tipe_laporan == 'v2') {
			$query1 = "SELECT b.acc_akun_id,sum(debet-kredit)as jumlah FROM acc_jurnal_ht a inner join acc_jurnal_dt b 
				on a.id=b.jurnal_id
				inner join gbm_organisasi c on b.lokasi_id=c.id
				where b.acc_akun_id=" . $akun_laba_ditahan . " 
				and a.tanggal < '" . $tanggal_mulai . "'
				and c.kode not in('SBME') 
				group by b.acc_akun_id ;";
		} else if ($tipe_laporan == 'v3') {
			$query1 = "SELECT b.acc_akun_id,sum(debet-kredit)as jumlah FROM acc_jurnal_ht a inner join acc_jurnal_dt b 
				on a.id=b.jurnal_id
				inner join gbm_organisasi c on b.lokasi_id=c.id
				where b.acc_akun_id=" . $akun_laba_ditahan . " 
				and a.tanggal < '" . $tanggal_mulai . "'
				and c.kode in ('SBME') 
				group by b.acc_akun_id ;";
		}
		$resJLaba  = $this->db->query($query1)->row_array();
		$jLaba3 = (!empty($resJLaba['jumlah'])) ? ($resJLaba['jumlah'] * -1) : 0;
		// $labarugi_ditahan = $jLaba1 + $jLaba2 + $jLaba3;
		$labarugi_ditahan =$jLaba3;
		$labaditahanTahunLalu = $labarugi_ditahan;



		/* Pasiva */
		$grandtotal = 0;

		$akun_pasiva   = $this->db->query($queryAkunPasiva)->result_array();
		foreach ($akun_pasiva as $key => $akun) {
			$nourut = $nourut + 1;
			$total = 0;
			$html = $html . "<tr> ";
			$html = $html . "<td style='text-align: left'>" . $akun['kode'] . " </td>";
			$html = $html . "<td style='text-align: left'>" . $akun['nama'] . " </td>";

			$query1 = "SELECT b.acc_akun_id,sum(debet-kredit)as jumlah FROM acc_jurnal_ht a inner join acc_jurnal_dt b 
				on a.id=b.jurnal_id
				where b.acc_akun_id=" . $akun['id'] . " 
				and a.tanggal <= '" . $tanggal_akhir . "' 
				group by b.acc_akun_id ;";

			$resJumlah  = $this->db->query($query1)->row_array();
			$jumlah = (!empty($resJumlah['jumlah'])) ? ($resJumlah['jumlah'] * -1) : 0;

			/* hitung akun laba berjalan */
			if ($akun_laba_berjalan == $akun['id']) {
				if ($tipe_laporan == 'v1') {
					// $query2 = "SELECT sum(debet-kredit)as jumlah FROM acc_jurnal_ht a 
					// inner join acc_jurnal_dt b 	on a.id=b.jurnal_id
					// inner join acc_akun c on c.id=b.acc_akun_id
					// where ( c.kode like '5%' or c.kode like '6%' or c.kode like '7%' or c.kode like '8%' or c.kode like '9%') 
					// and (a.tanggal >= '" . $tanggal_mulai . "' and a.tanggal <= '" . $tanggal_akhir . "') ;";
					$query2 = "SELECT sum(debet-kredit)as jumlah FROM acc_jurnal_ht a 
					inner join acc_jurnal_dt b 	on a.id=b.jurnal_id
					inner join acc_akun c on c.id=b.acc_akun_id
					where ( c.kode like '5%' or c.kode like '6%' or c.kode like '7%' or c.kode like '8%' or c.kode like '9%') 
					and (a.tanggal <= '" . $tanggal_akhir . "') ;";

				} else 	if ($tipe_laporan == 'v2') {
					$query2 = "SELECT sum(debet-kredit)as jumlah FROM acc_jurnal_ht a 
					inner join acc_jurnal_dt b 	on a.id=b.jurnal_id
					inner join acc_akun c on c.id=b.acc_akun_id
					inner join gbm_organisasi d on b.lokasi_id=d.id
					where ( c.kode like '5%' or c.kode like '6%' or c.kode like '7%' or c.kode like '8%' or c.kode like '9%') 
					and (a.tanggal >= '" . $tanggal_mulai . "' and a.tanggal <= '" . $tanggal_akhir . "')
					and c.kode not in ('SBME')  ;";
				} else 	if ($tipe_laporan == 'v3') {
					$query2 = "SELECT sum(debet-kredit)as jumlah FROM acc_jurnal_ht a 
					inner join acc_jurnal_dt b 	on a.id=b.jurnal_id
					inner join acc_akun c on c.id=b.acc_akun_id
					inner join gbm_organisasi d on b.lokasi_id=d.id
					where ( c.kode like '5%' or c.kode like '6%' or c.kode like '7%' or c.kode like '8%' or c.kode like '9%') 
					and (a.tanggal >= '" . $tanggal_mulai . "' and a.tanggal <= '" . $tanggal_akhir . "')
					and c.kode in ('SBME')  ;";
				}

				$resJumlahLaba  = $this->db->query($query2)->row_array();
				$jumlahLaba = (!empty($resJumlahLaba['jumlah'])) ? ($resJumlahLaba['jumlah'] * -1) : 0;
				$jumlah =  $jumlahLaba;

				// if ($tanggal_mulai <= '2022-12-31') { // Saldo awla diinput 31-des 2022
					/* hitung akun laba berjalan yg dinput manual di jurnal */
					if ($tipe_laporan == 'v1') {
						$queryLabaBerjalan = "SELECT b.acc_akun_id,sum(debet-kredit)as jumlah FROM acc_jurnal_ht a inner join acc_jurnal_dt b 
						on a.id=b.jurnal_id
						where b.acc_akun_id=" . $akun['id'] . " 
						and a.tanggal <= '" . $tanggal_akhir . "' 
						group by b.acc_akun_id ;";
					} else if ($tipe_laporan == 'v2') {
						$queryLabaBerjalan = "SELECT b.acc_akun_id,sum(debet-kredit)as jumlah FROM acc_jurnal_ht a inner join acc_jurnal_dt b 
						on a.id=b.jurnal_id
						inner join gbm_organisasi c on b.lokasi_id=c.id
						where b.acc_akun_id=" . $akun['id'] . " 
						and a.tanggal <= '" . $tanggal_akhir . "' 
						and c.kode not in ('SBME')
						group by b.acc_akun_id ;";
					} else if ($tipe_laporan == 'v3') {
						$queryLabaBerjalan = "SELECT b.acc_akun_id,sum(debet-kredit)as jumlah FROM acc_jurnal_ht a inner join acc_jurnal_dt b 
						on a.id=b.jurnal_id
						inner join gbm_organisasi c on b.lokasi_id=c.id
						where b.acc_akun_id=" . $akun['id'] . " 
						and a.tanggal <= '" . $tanggal_akhir . "' 
						and c.kode  in ('SBME')
						group by b.acc_akun_id ;";
					}
					$resJumlahLabaBerjalan  = $this->db->query($queryLabaBerjalan)->row_array();
					$JumlahLabaBerjalan = (!empty($resJumlahLabaBerjalan['jumlah'])) ? ($resJumlahLabaBerjalan['jumlah'] * -1) : 0;
					$jumlah = $jumlah + $JumlahLabaBerjalan;
				// }
			} elseif ($akun_laba_ditahan == $akun['id']) {
				// $jumlah = $labaditahanTahunLalu;
				$jumlah = 0;
				// if ($tanggal_mulai <= '2022-12-31') { // SAldo awal diinput tgl 31 Des 2022
					/* hitung akun laba berjalan yg dinput manual di jurnal */
					if ($tipe_laporan == 'v1') {
						$queryLabaDitahan = "SELECT b.acc_akun_id,sum(debet-kredit)as jumlah FROM acc_jurnal_ht a inner join acc_jurnal_dt b 
						on a.id=b.jurnal_id
						where b.acc_akun_id=" . $akun['id'] . " 
						and a.tanggal <= '" . $tanggal_akhir . "'
						group by b.acc_akun_id ;";
					} else if ($tipe_laporan == 'v2') {
						$queryLabaDitahan = "SELECT b.acc_akun_id,sum(debet-kredit)as jumlah FROM acc_jurnal_ht a inner join acc_jurnal_dt b 
						on a.id=b.jurnal_id
						inner join gbm_organisasi c on b.lokasi_id=c.id
						where b.acc_akun_id=" . $akun['id'] . " 
						and a.tanggal <= '" . $tanggal_akhir . "'
						and c.kode not in ('SBME')
						group by b.acc_akun_id ;";
					}
					if ($tipe_laporan == 'v3') {
						$queryLabaDitahan = "SELECT b.acc_akun_id,sum(debet-kredit)as jumlah FROM acc_jurnal_ht a inner join acc_jurnal_dt b 
						on a.id=b.jurnal_id
						inner join gbm_organisasi c on b.lokasi_id=c.id
						where b.acc_akun_id=" . $akun['id'] . " 
						and a.tanggal <= '" . $tanggal_akhir . "'
						and c.kode  in ('SBME')
						group by b.acc_akun_id ;";
					}

					$resJumlahLabaDitahan  = $this->db->query($queryLabaDitahan)->row_array();

					$jumlahLabaDitahan = (!empty($resJumlahLabaDitahan['jumlah'])) ? ($resJumlahLabaDitahan['jumlah'] * -1) : 0;

					$jumlah = $jumlah + $jumlahLabaDitahan;
				// }
			}

			$total = $total + $jumlah;
			$grandtotal = $grandtotal + $jumlah;
			$html = $html . "<td style='text-align: right'>" . $this->format_number_report($total) . " </td>";
			$html = $html . "</tr>";
		}

		$html = $html . "<tr>";
		$html = $html . "<td style='text-align: center'> </td>";
		$html = $html . "<td style='text-align: center'><b>TOTAL HUTANG & MODAL</b></td>";
		$html = $html . "<td style='text-align: right'><b>" . $this->format_number_report($grandtotal) . " </b></td>";
		$html = $html . "</tr>";
		$html = $html . "</table>";



		if ($format_laporan == 'xls') {
			$reader = new \PhpOffice\PhpSpreadsheet\Reader\Html;
			$spreadsheet = $reader->loadFromString($html);
			// $reader->setSheetIndex(1);
			//$spreadhseet = $reader->loadFromString($secondHtmlString, $spreadsheet);
			$objWriter = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
			header("Pragma: public");
			header("Expires: 0");
			header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
			header("Content-Type: application/force-download");
			header("Content-Type: application/octet-stream");
			header("Content-Type: application/download");
			header("Content-Disposition: attachment;filename=test.xlsx");
			header("Content-Transfer-Encoding: binary ");

			ob_end_clean();
			ob_start();
			$objWriter->save('php://output');
		} else if ($format_laporan == 'view') {
			echo $html;
		} else {
			$filename = 'report_' . time();
			$this->pdfgenerator->generate($html, $filename, true, 'A4', 'landscape');
		}
	}
	function laporan_neraca_inti_v2()
	{
		error_reporting(0);
		$tanggal_akhir =  $this->post('tgl_akhir', true);
		$format_laporan =  $this->post('format_laporan', true);
		$judulLokasi = 'Semua';

		/* Ambil AKUN */
		$queryAkunAktiva = "select * from acc_akun
       			where is_transaksi_akun=1 
				  and (kode like '1%')  
				   order by kode ";
		/* Ambil AKUN */
		$queryAkunPasiva = "select * from acc_akun
       			where is_transaksi_akun=1 
				  and (kode like '2%' or kode like '3%'    )  
				   order by kode ";

		$res_akun_laba_ditahan = $this->db->query("SELECT * from acc_auto_jurnal 
				   where kode='LABA_RUGI_DITAHAN'")->row_array();
		if (empty($res_akun_laba_ditahan)) {
			//$this->set_response(array("status" => "NOT OK", "data" => "LABA_RUGI_BERJALAN Tidak ada Data Setting Jurnal"), REST_Controller::HTTP_NOT_FOUND);
			//return;
		}
		$akun_laba_ditahan = $res_akun_laba_ditahan['acc_akun_id'];
		$res_akun_laba_berjalan = $this->db->query("SELECT * from acc_auto_jurnal 
				   where kode='LABA_RUGI_BERJALAN'")->row_array();
		if (empty($res_akun_laba_berjalan)) {
			//$this->set_response(array("status" => "NOT OK", "data" => "LABA_RUGI_BERJALAN Tidak ada Data Setting Jurnal"), REST_Controller::HTTP_NOT_FOUND);
			//return;
		}
		$akun_laba_berjalan = $res_akun_laba_berjalan['acc_akun_id'];
		$res = array();
		$tahun_ini = substr($tanggal_akhir, 0, 4);
		$tahun_lalu = ((int)$tahun_ini) - 1;
		$tanggal_lalu = $tahun_lalu . '-12-31';
		$tanggal_mulai = $tahun_ini . '-01-01';

		$res = array();
		// var_dump($results);
		$this->load->helper("terbilang");
		$this->load->helper("antech_helper");
		$this->load->library('pdfgenerator');
		if ($format_laporan == 'view') {
			$html = get_header_report_v2();
		} else {
			$html = get_header_pdf_report();
		}
		$html = $html . '
		<div class="row">
		<div class="span12">
			<br>
		  <div class="kop-print">
		  <div class="kop-nama">PT. ANNAJAH TECHNOLOGY INDONESIA</div>
		  <div class="kop-info"> Komplek Vila Sehati B12 , Tapos - Depok</div>
		  <div class="kop-info">Telp : 081387373939</div>
		</div>
			<hr class="kop-print-hr">
		</div>
		</div>
		<h3 class="title">LAPORAN NERACA V2</h3>
		<table class="no_border" style="width:30%">
				  
				  
				  <tr>	
						  <td style="width:20%">Periode</td>
						  <td>:</td>
						  <td>' . $tanggal_akhir . '</td>
				  </tr>
	  
				  
		  </table>
		  <br>
';
		$html = $html . "
<table   border='1' width='100%' style='border-collapse: collapse;'>
<thead>
<tr>
	<td rowspan=1>Kode Akun</td>
	<td rowspan=1>Kode Akun</td>
	<td rowspan=1  style='text-align: right'>JUMLAH</td>
</tr>
 </thead>
";


		$nourut = 0;
		/* Aktiva */
		$grandtotal = 0;
		$akun_aktiva   = $this->db->query($queryAkunAktiva)->result_array();
		foreach ($akun_aktiva as $key => $akun) {
			$nourut = $nourut + 1;
			$total = 0;
			$html = $html . "<tr> ";
			$html = $html . "<td style='text-align: left'>" . $akun['kode'] . " </td>";
			$html = $html . "<td style='text-align: left'>" . $akun['nama'] . " </td>";

			$query1 = "SELECT b.acc_akun_id,sum(debet-kredit)as jumlah FROM acc_jurnal_ht a inner join acc_jurnal_dt b 
				on a.id=b.jurnal_id
				where b.acc_akun_id=" . $akun['id'] . " 
				and a.tanggal <= '" . $tanggal_akhir . "' 
				and b.lokasi_id <>265
				group by b.acc_akun_id ;"; // 265=SBME

			$resJumlah  = $this->db->query($query1)->row_array();
			$jumlah = (!empty($resJumlah['jumlah'])) ? $resJumlah['jumlah'] : 0;


			$total = $total + $jumlah;
			$grandtotal = $grandtotal + $jumlah;
			$html = $html . "<td style='text-align: right'>" . $this->format_number_report($jumlah) . " </td>";


			$html = $html . "</tr>";
		}
		$html = $html . "<tr>";
		$html = $html . "<td style='text-align: center'> </td>";
		$html = $html . "<td style='text-align: center'><b>TOTAL AKTIVA</b></td>";

		$html = $html . "<td style='text-align: right'><b>" . $this->format_number_report($grandtotal) . " </b></td>";
		$html = $html . "</tr>";


		/* hitung laba ditahan  */
		$labaditahanTahunLalu = 0;

		$jLaba1 = 0;
		$jLaba2 = 0;
		$jLaba3 = 0;
		$labarugi_ditahan = 0;
		/* hitung laba rugi pendapatan-biaya  */
		$query1 = "SELECT sum(debet-kredit)as jumlah FROM acc_jurnal_ht a 
					inner join acc_jurnal_dt b 	on a.id=b.jurnal_id
					inner join acc_akun c on c.id=b.acc_akun_id
					where ( c.kode like '5%' or c.kode like '6%' or c.kode like '7%' or c.kode like '8%' or c.kode like '9%') 
					and a.tanggal < '" . $tanggal_mulai . "'
					and b.lokasi_id <>265 ";


		$resJLaba  = $this->db->query($query1)->row_array();
		$jLaba1 = (!empty($resJLaba['jumlah'])) ? ($resJLaba['jumlah'] * -1) : 0;

		// if ($tanggal_mulai>'2022-12-01'){ // Jika di atas input saldo awal
		/* hitung laba rugi saldo awal  */
		$query1 = "SELECT b.acc_akun_id,sum(debet-kredit)as jumlah FROM acc_jurnal_ht a inner join acc_jurnal_dt b 
					on a.id=b.jurnal_id
					where b.acc_akun_id=" . $akun_laba_berjalan . " 
					and a.tanggal < '" . $tanggal_mulai . "' 
					and b.lokasi_id <>265
					group by b.acc_akun_id ;";
		$resJLaba  = $this->db->query($query1)->row_array();
		$jLaba2 = (!empty($resJLaba['jumlah'])) ? ($resJLaba['jumlah'] * -1) : 0;
		// }
		/* hitung laba rugi laba ditahan  */
		$query1 = "SELECT b.acc_akun_id,sum(debet-kredit)as jumlah FROM acc_jurnal_ht a inner join acc_jurnal_dt b 
				on a.id=b.jurnal_id
				where b.acc_akun_id=" . $akun_laba_ditahan . " 
				and a.tanggal < '" . $tanggal_mulai . "'
				and b.lokasi_id <>265
				group by b.acc_akun_id ;";
		$resJLaba  = $this->db->query($query1)->row_array();
		$jLaba3 = (!empty($resJLaba['jumlah'])) ? ($resJLaba['jumlah'] * -1) : 0;
		$labarugi_ditahan = $jLaba1 + $jLaba2 + $jLaba3;
		$labaditahanTahunLalu = $labarugi_ditahan;



		/* Pasiva */
		$grandtotal = 0;

		$akun_pasiva   = $this->db->query($queryAkunPasiva)->result_array();
		foreach ($akun_pasiva as $key => $akun) {
			$nourut = $nourut + 1;
			$total = 0;
			$html = $html . "<tr> ";
			$html = $html . "<td style='text-align: left'>" . $akun['kode'] . " </td>";
			$html = $html . "<td style='text-align: left'>" . $akun['nama'] . " </td>";

			$query1 = "SELECT b.acc_akun_id,sum(debet-kredit)as jumlah FROM acc_jurnal_ht a inner join acc_jurnal_dt b 
				on a.id=b.jurnal_id
				where b.acc_akun_id=" . $akun['id'] . " 
				and a.tanggal <= '" . $tanggal_akhir . "' 
				and b.lokasi_id <>265
				group by b.acc_akun_id ;";

			$resJumlah  = $this->db->query($query1)->row_array();
			$jumlah = (!empty($resJumlah['jumlah'])) ? ($resJumlah['jumlah'] * -1) : 0;

			/* hitung akun laba berjalan */
			if ($akun_laba_berjalan == $akun['id']) {
				$query2 = "SELECT sum(debet-kredit)as jumlah FROM acc_jurnal_ht a 
					inner join acc_jurnal_dt b 	on a.id=b.jurnal_id
					inner join acc_akun c on c.id=b.acc_akun_id
					where ( c.kode like '5%' or c.kode like '6%' or c.kode like '7%' or c.kode like '8%' or c.kode like '9%') 
					and (a.tanggal >= '" . $tanggal_mulai . "' and a.tanggal <= '" . $tanggal_akhir . "') 
					and b.lokasi_id <>265;";

				$resJumlahLaba  = $this->db->query($query2)->row_array();
				$jumlahLaba = (!empty($resJumlahLaba['jumlah'])) ? ($resJumlahLaba['jumlah'] * -1) : 0;
				$jumlah =  $jumlahLaba;

				if ($tanggal_mulai <= '2022-12-31') { // Saldo awla diinput 31-des 2022
					/* hitung akun laba berjalan yg dinput manual di jurnal */
					$queryLabaBerjalan = "SELECT b.acc_akun_id,sum(debet-kredit)as jumlah FROM acc_jurnal_ht a inner join acc_jurnal_dt b 
					on a.id=b.jurnal_id
					where b.acc_akun_id=" . $akun['id'] . " 
					and a.tanggal <= '" . $tanggal_akhir . "' 
					and b.lokasi_id <>265
					group by b.acc_akun_id ;";
					$resJumlahLabaBerjalan  = $this->db->query($queryLabaBerjalan)->row_array();
					$JumlahLabaBerjalan = (!empty($resJumlahLabaBerjalan['jumlah'])) ? ($resJumlahLabaBerjalan['jumlah'] * -1) : 0;
					$jumlah = $jumlah + $JumlahLabaBerjalan;
				}
			} elseif ($akun_laba_ditahan == $akun['id']) {
				$jumlah = $labaditahanTahunLalu;

				if ($tanggal_mulai <= '2022-12-31') { // SAldo awal diinput tgl 31 Des 2022
					/* hitung akun laba berjalan yg dinput manual di jurnal */
					$queryLabaDitahan = "SELECT b.acc_akun_id,sum(debet-kredit)as jumlah FROM acc_jurnal_ht a inner join acc_jurnal_dt b 
					on a.id=b.jurnal_id
					where b.acc_akun_id=" . $akun['id'] . " 
					and a.tanggal <= '" . $tanggal_akhir . "'
					and b.lokasi_id <>265
					group by b.acc_akun_id ;";
					$resJumlahLabaDitahan  = $this->db->query($queryLabaDitahan)->row_array();

					$jumlahLabaDitahan = (!empty($resJumlahLabaDitahan['jumlah'])) ? ($resJumlahLabaDitahan['jumlah'] * -1) : 0;

					$jumlah = $jumlah + $jumlahLabaDitahan;
				}
			}

			$total = $total + $jumlah;
			$grandtotal = $grandtotal + $jumlah;
			$html = $html . "<td style='text-align: right'>" . $this->format_number_report($total) . " </td>";
			$html = $html . "</tr>";
		}

		$html = $html . "<tr>";
		$html = $html . "<td style='text-align: center'> </td>";
		$html = $html . "<td style='text-align: center'><b>TOTAL HUTANG & MODAL</b></td>";
		$html = $html . "<td style='text-align: right'><b>" . $this->format_number_report($grandtotal) . " </b></td>";
		$html = $html . "</tr>";
		$html = $html . "</table>";



		if ($format_laporan == 'xls') {
			$reader = new \PhpOffice\PhpSpreadsheet\Reader\Html;
			$spreadsheet = $reader->loadFromString($html);
			// $reader->setSheetIndex(1);
			//$spreadhseet = $reader->loadFromString($secondHtmlString, $spreadsheet);
			$objWriter = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
			header("Pragma: public");
			header("Expires: 0");
			header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
			header("Content-Type: application/force-download");
			header("Content-Type: application/octet-stream");
			header("Content-Type: application/download");
			header("Content-Disposition: attachment;filename=test.xlsx");
			header("Content-Transfer-Encoding: binary ");

			ob_end_clean();
			ob_start();
			$objWriter->save('php://output');
		} else if ($format_laporan == 'view') {
			echo $html;
		} else {
			$filename = 'report_' . time();
			$this->pdfgenerator->generate($html, $filename, true, 'A4', 'landscape');
		}
	}
	function laporan_neraca_v3($tipe_laporan)
	{
		error_reporting(0);
		$tanggal_akhir =  $this->post('tgl_akhir', true);
		$format_laporan =  $this->post('format_laporan', true);
		$judulLokasi = 'Semua';



		$res_akun_laba_berjalan = $this->db->query("SELECT * from acc_auto_jurnal 
				   where kode='LABA_RUGI_BERJALAN'")->row_array();
		if (empty($res_akun_laba_berjalan)) {
			//$this->set_response(array("status" => "NOT OK", "data" => "LABA_RUGI_BERJALAN Tidak ada Data Setting Jurnal"), REST_Controller::HTTP_NOT_FOUND);
			//return;
		}
		$akun_laba_berjalan = $res_akun_laba_berjalan['acc_akun_id'];
		$res = array();
		// var_dump($results);
		$this->load->helper("terbilang");
		$this->load->helper("antech_helper");
		$this->load->library('pdfgenerator');
		if ($format_laporan == 'view') {
			$html = get_header_report_v2();
		} else {
			$html = get_header_pdf_report();
		}
		$html = $html . '<div class="row">
		<div class="span12">
			<br>
		  <div class="kop-print">
		  <div class="kop-nama">PT. ANNAJAH TECHNOLOGY INDONESIA</div>
		  <div class="kop-info"> Komplek Vila Sehati B12 , Tapos - Depok</div>
		  <div class="kop-info">Telp : 081387373939</div>
		</div>
			<hr class="kop-print-hr">
		</div>
		</div>
		<h3 class="title">LAPORAN NERACA V3</h3>
		<table class="no_border" style="width:30%">
				  
				  
				  <tr>	
						  <td style="width:20%">PERIODE</td>
						  <td>:</td>
						  <td >' . $tanggal_akhir . '</td>
				  </tr>
	  
				  
		  </table>
		  <br>';
		$html = $html . "
<table   border='1' width='100%' style='border-collapse: collapse;'>
<thead>

";

		$total_aktiva = 0;
		/* AKTIVA */
		$res_akun = $this->db->query("SELECT * from acc_akun	where kode='1'")->row_array();
		$html = $html . "<tr> ";
		$html = $html . "<th style='text-align: left'><b>" . $res_akun['nama'] . "</b> </th>";
		$html = $html . "<th style='text-align: left'> </th>";
		$html = $html . "<tr> ";


		/* AKTIVA LANCAR */
		$res_akun = $this->db->query("SELECT * from acc_akun where kode='11'")->row_array();
		$html = $html . "<tr> ";
		$html = $html . "<th style='text-align: left'><b>" . $res_akun['nama'] . "</b> </th>";
		$html = $html . "<th style='text-align: left'> </th>";
		$html = $html . "<tr> </thead>
		";
		$nama_sub = $res_akun['nama'];

		/* AKTIVA LANCAR DETAIL*/
		$res_akun = $this->db->query("select * from acc_akun where LENGTH(kode)=3  and (kode like '11%')   order by kode ")->result_array();
		$sub_total = 0;

		foreach ($res_akun as $key => $akun) {
			if ($tipe_laporan == 'v1') {
				$query = "SELECT sum(debet-kredit)as jumlah FROM acc_jurnal_ht a inner join acc_jurnal_dt b 
				on a.id=b.jurnal_id inner join acc_akun c on b.acc_akun_id=c.id
				where c.kode like '" . $akun['kode'] . "%' 
				and a.tanggal <= '" . $tanggal_akhir . "'  ;";
			} else if ($tipe_laporan == 'v2') {
				$query = "SELECT sum(debet-kredit)as jumlah FROM acc_jurnal_ht a inner join acc_jurnal_dt b 
				on a.id=b.jurnal_id inner join acc_akun c on b.acc_akun_id=c.id
				inner join gbm_organisasi d on b.lokasi_id=d.id
				where c.kode like '" . $akun['kode'] . "%' 
				and a.tanggal <= '" . $tanggal_akhir . "'  
				and d.kode not in ('SBME');";
			} elseif ($tipe_laporan == 'v3') {
				$query = "SELECT sum(debet-kredit)as jumlah FROM acc_jurnal_ht a inner join acc_jurnal_dt b 
				on a.id=b.jurnal_id inner join acc_akun c on b.acc_akun_id=c.id
				inner join gbm_organisasi d on b.lokasi_id=d.id
				where c.kode like '" . $akun['kode'] . "%' 
				and a.tanggal <= '" . $tanggal_akhir . "'
				and d.kode  in ('SBME')  ;";
			}



			$resJumlah  = $this->db->query($query)->row_array();
			$jumlah = (!empty($resJumlah['jumlah'])) ? $resJumlah['jumlah'] : 0;
			$sub_total = $sub_total + $jumlah;
			$total_aktiva = $total_aktiva + $jumlah;
			$html = $html . "<tr> ";
			$html = $html . "<td style='text-align: left'>" . $akun['nama'] . " </td>";
			$html = $html . "<td style='text-align: right'>" . $this->format_number_report($jumlah) . " </td>";
			$html = $html . "</tr>";
		}
		$html = $html . "<tr> ";
		$html = $html . "<td style='text-align: left'><b>Jumlah " . $nama_sub . " </b></td>";
		$html = $html . "<td style='text-align: right'>" . $this->format_number_report($sub_total) . " </td>";
		$html = $html . "</tr>";

		/* AKTIVA TIDAK LANCAR*/
		$res_akun = $this->db->query("SELECT * from acc_akun where kode='12'")->row_array();
		$html = $html . "<tr> ";
		$html = $html . "<td style='text-align: left'><b>" . $res_akun['nama'] . "</b> </td>";
		$html = $html . "<td style='text-align: left'> </td>";
		$html = $html . "<tr> ";
		$nama_sub = $res_akun['nama'];
		/* AKTIVA TIDAK LANCAR DETAIL */
		$res_akun = $this->db->query("select * from acc_akun where LENGTH(kode)=3  and	 (kode like '12%')   order by kode ")->result_array();
		$sub_total = 0;

		foreach ($res_akun as $key => $akun) {
			if ($tipe_laporan == 'v1') {
				$query = "SELECT sum(debet-kredit)as jumlah FROM acc_jurnal_ht a inner join acc_jurnal_dt b 
				on a.id=b.jurnal_id inner join acc_akun c on b.acc_akun_id=c.id
				where c.kode like '" . $akun['kode'] . "%' 
				and a.tanggal <= '" . $tanggal_akhir . "'  ;";
			} else 	if ($tipe_laporan == 'v2') {
				$query = "SELECT sum(debet-kredit)as jumlah FROM acc_jurnal_ht a inner join acc_jurnal_dt b 
				on a.id=b.jurnal_id inner join acc_akun c on b.acc_akun_id=c.id
				inner join gbm_organisasi d on b.lokasi_id=d.id
				where c.kode like '" . $akun['kode'] . "%' 
				and a.tanggal <= '" . $tanggal_akhir . "' 
				and d.kode  not in ('SBME')  ;";
			} else 	if ($tipe_laporan == 'v3') {
				$query = "SELECT sum(debet-kredit)as jumlah FROM acc_jurnal_ht a inner join acc_jurnal_dt b 
				on a.id=b.jurnal_id inner join acc_akun c on b.acc_akun_id=c.id
				inner join gbm_organisasi d on b.lokasi_id=d.id
				where c.kode like '" . $akun['kode'] . "%' 
				and a.tanggal <= '" . $tanggal_akhir . "'
				and d.kode  in ('SBME')   ;";
			}

			$resJumlah  = $this->db->query($query)->row_array();
			$jumlah = (!empty($resJumlah['jumlah'])) ? $resJumlah['jumlah'] : 0;
			$sub_total = $sub_total + $jumlah;
			$total_aktiva = $total_aktiva + $jumlah;
			$html = $html . "<tr> ";
			$html = $html . "<td style='text-align: left'>" . $akun['nama'] . " </td>";
			$html = $html . "<td style='text-align: right'>" . $this->format_number_report($jumlah) . " </td>";
			$html = $html . "</tr>";
		}
		$html = $html . "<tr> ";
		$html = $html . "<td style='text-align: left'><b>Jumlah " . $nama_sub . " </b></td>";
		$html = $html . "<td style='text-align: right'>" . $this->format_number_report($sub_total) . " </td>";
		$html = $html . "</tr>";

		$html = $html . "<tr>";
		$html = $html . "<td style='text-align: center'><b>TOTAL AKTIVA</b></td>";
		$html = $html . "<td style='text-align: right'><b>" . $this->format_number_report($total_aktiva) . " </b></td>";
		$html = $html . "</tr>";

		/* PASIVA */
		$html = $html . "<tr> ";
		$html = $html . "<td style='text-align: left'><b>LIABILITAS DAN EKUITAS</b> </td>";
		$html = $html . "<td style='text-align: left'> </td>";
		$html = $html . "<tr> ";


		$res_akun = $this->db->query("SELECT * from acc_akun	where kode='2'")->row_array();
		$html = $html . "<tr> ";
		$html = $html . "<td style='text-align: left'><b>" . $res_akun['nama'] . "</b> </td>";
		$html = $html . "<td style='text-align: left'> </td>";
		$html = $html . "<tr> ";


		/* LIABILITAS JANGKA PENDEK */
		$res_akun = $this->db->query("SELECT * from acc_akun where kode='21'")->row_array();
		$html = $html . "<tr> ";
		$html = $html . "<td style='text-align: left'><b>" . $res_akun['nama'] . "</b> </td>";
		$html = $html . "<td style='text-align: left'> </td>";
		$html = $html . "<tr> ";
		$nama_sub = $res_akun['nama'];

		/* LIABILITAS DETAIL*/
		$res_akun = $this->db->query("select * from acc_akun where LENGTH(kode)=3  and (kode like '21%')   order by kode ")->result_array();
		$sub_total = 0;
		$total_liabilities = 0;
		$total_pasiva = 0;
		foreach ($res_akun as $key => $akun) {
			if ($tipe_laporan == 'v1') {
				$query = "SELECT sum(debet-kredit)as jumlah FROM acc_jurnal_ht a inner join acc_jurnal_dt b 
				on a.id=b.jurnal_id inner join acc_akun c on b.acc_akun_id=c.id
				where c.kode like '" . $akun['kode'] . "%' 
				and a.tanggal <= '" . $tanggal_akhir . "'  ;";
			} else if ($tipe_laporan == 'v2') {
				$query = "SELECT sum(debet-kredit)as jumlah FROM acc_jurnal_ht a inner join acc_jurnal_dt b 
				on a.id=b.jurnal_id inner join acc_akun c on b.acc_akun_id=c.id
				inner join gbm_organisasi d on b.lokasi_id=d.id
				where c.kode like '" . $akun['kode'] . "%' 
				and a.tanggal <= '" . $tanggal_akhir . "' 
				and d.kode not in ('SBME')  ;";
			} else if ($tipe_laporan == 'v3') {
				$query = "SELECT sum(debet-kredit)as jumlah FROM acc_jurnal_ht a inner join acc_jurnal_dt b 
				on a.id=b.jurnal_id inner join acc_akun c on b.acc_akun_id=c.id
				inner join gbm_organisasi d on b.lokasi_id=d.id
				where c.kode like '" . $akun['kode'] . "%' 
				and a.tanggal <= '" . $tanggal_akhir . "' 
				and d.kode  in ('SBME')  ;";
			}

			$resJumlah  = $this->db->query($query)->row_array();
			$jumlah = (!empty($resJumlah['jumlah'])) ? $resJumlah['jumlah'] * -1 : 0;
			$sub_total = $sub_total + $jumlah;
			$total_liabilities = $total_liabilities + $jumlah;
			$total_pasiva = $total_pasiva + $jumlah;
			$html = $html . "<tr> ";
			$html = $html . "<td style='text-align: left'>" . $akun['nama'] . " </td>";
			$html = $html . "<td style='text-align: right'>" . $this->format_number_report($jumlah) . " </td>";
			$html = $html . "</tr>";
		}
		$html = $html . "<tr> ";
		$html = $html . "<td style='text-align: left'><b>Jumlah " . $nama_sub . " </b></td>";
		$html = $html . "<td style='text-align: right'>" . $this->format_number_report($sub_total) . " </td>";
		$html = $html . "</tr>";

		/* LIABILITAS JANGKA panjang */
		$res_akun = $this->db->query("SELECT * from acc_akun where kode='22'")->row_array();
		$html = $html . "<tr> ";
		$html = $html . "<td style='text-align: left'><b>" . $res_akun['nama'] . "</b> </td>";
		$html = $html . "<td style='text-align: left'> </td>";
		$html = $html . "<tr> ";
		$nama_sub = $res_akun['nama'];

		/* LIABILITAS DETAIL*/
		$res_akun = $this->db->query("select * from acc_akun where LENGTH(kode)=3  and (kode like '22%')   order by kode ")->result_array();
		$sub_total = 0;
		$total_liabilities = 0;
		$total_pasiva = 0;
		foreach ($res_akun as $key => $akun) {
			if ($tipe_laporan == 'v1') {
				$query = "SELECT sum(debet-kredit)as jumlah FROM acc_jurnal_ht a inner join acc_jurnal_dt b 
				on a.id=b.jurnal_id inner join acc_akun c on b.acc_akun_id=c.id
				where c.kode like '" . $akun['kode'] . "%' 
				and a.tanggal <= '" . $tanggal_akhir . "'  ;";
			} else 	if ($tipe_laporan == 'v1') {
				$query = "SELECT sum(debet-kredit)as jumlah FROM acc_jurnal_ht a inner join acc_jurnal_dt b 
				on a.id=b.jurnal_id inner join acc_akun c on b.acc_akun_id=c.id
				inner join gbm_organisasi d on b.lokasi_id=d.id
				where c.kode like '" . $akun['kode'] . "%' 
				and a.tanggal <= '" . $tanggal_akhir . "' 
				and d.kode not in ('SBME')  ;";
			} else 	if ($tipe_laporan == 'v1') {
				$query = "SELECT sum(debet-kredit)as jumlah FROM acc_jurnal_ht a inner join acc_jurnal_dt b 
				on a.id=b.jurnal_id inner join acc_akun c on b.acc_akun_id=c.id
				inner join gbm_organisasi d on b.lokasi_id=d.id
				where c.kode like '" . $akun['kode'] . "%' 
				and a.tanggal <= '" . $tanggal_akhir . "' 
				and d.kode  in ('SBME')  ;";
			}

			$resJumlah  = $this->db->query($query)->row_array();
			$jumlah = (!empty($resJumlah['jumlah'])) ? $resJumlah['jumlah'] * -1 : 0;
			$sub_total = $sub_total + $jumlah;
			$total_liabilities = $total_liabilities + $jumlah;
			$total_pasiva = $total_pasiva + $jumlah;
			$html = $html . "<tr> ";
			$html = $html . "<td style='text-align: left'>" . $akun['nama'] . " </td>";
			$html = $html . "<td style='text-align: right'>" . $this->format_number_report($jumlah) . " </td>";
			$html = $html . "</tr>";
		}
		$html = $html . "<tr> ";
		$html = $html . "<td style='text-align: left'><b>Jumlah " . $nama_sub . " </b></td>";
		$html = $html . "<td style='text-align: right'>" . $this->format_number_report($sub_total) . " </td>";
		$html = $html . "</tr>";

		$html = $html . "<tr>";
		$html = $html . "<td style='text-align: left'><b>JUMLAH LIABILITAS</b></td>";
		$html = $html . "<td style='text-align: right'><b>" . $this->format_number_report($total_liabilities) . " </b></td>";
		$html = $html . "</tr>";

		/* MODAL */
		$res_akun = $this->db->query("SELECT * from acc_akun	where kode='3'")->row_array();
		$html = $html . "<tr> ";
		$html = $html . "<td style='text-align: left'><b>" . $res_akun['nama'] . "</b> </td>";
		$html = $html . "<td style='text-align: left'> </td>";
		$html = $html . "<tr> ";


		/* MMODAL */
		$res_akun = $this->db->query("SELECT * from acc_akun where kode='310'")->row_array();
		$html = $html . "<tr> ";
		$html = $html . "<td style='text-align: left'><b>" . $res_akun['nama'] . "</b> </td>";
		$html = $html . "<td style='text-align: left'> </td>";
		$html = $html . "<tr> ";
		$nama_sub = $res_akun['nama'];

		/* MODAL DETAIL*/
		$res_akun = $this->db->query("select * from acc_akun where LENGTH(kode)=3  and (kode like '310%')   order by kode ")->result_array();
		$sub_total = 0;
		$total_liabilities = 0;
		// $total_pasiva = 0;
		foreach ($res_akun as $key => $akun) {
			if ($tipe_laporan == 'v1') {
				$query = "SELECT sum(debet-kredit)as jumlah FROM acc_jurnal_ht a inner join acc_jurnal_dt b 
				on a.id=b.jurnal_id inner join acc_akun c on b.acc_akun_id=c.id
				where c.kode like '" . $akun['kode'] . "%' 
				and a.tanggal <= '" . $tanggal_akhir . "'  ;";
			} else 	if ($tipe_laporan == 'v2') {
				$query = "SELECT sum(debet-kredit)as jumlah FROM acc_jurnal_ht a inner join acc_jurnal_dt b 
				on a.id=b.jurnal_id inner join acc_akun c on b.acc_akun_id=c.id
				inner join gbm_organisasi d on b.lokasi_id=d.id
				where c.kode like '" . $akun['kode'] . "%' 
				and a.tanggal <= '" . $tanggal_akhir . "' 
				and d.kode not in ('SBME')  ;";
			} else 	if ($tipe_laporan == 'v3') {
				$query = "SELECT sum(debet-kredit)as jumlah FROM acc_jurnal_ht a inner join acc_jurnal_dt b 
				on a.id=b.jurnal_id inner join acc_akun c on b.acc_akun_id=c.id
				inner join gbm_organisasi d on b.lokasi_id=d.id
				where c.kode like '" . $akun['kode'] . "%' 
				and a.tanggal <= '" . $tanggal_akhir . "' 
				and d.kode  in ('SBME')  ;";
			}

			$resJumlah  = $this->db->query($query)->row_array();
			$jumlah = (!empty($resJumlah['jumlah'])) ? $resJumlah['jumlah'] * -1 : 0;
			$sub_total = $sub_total + $jumlah;
			$total_liabilities = $total_liabilities + $jumlah;
			$total_pasiva = $total_pasiva + $jumlah;
			$html = $html . "<tr> ";
			$html = $html . "<td style='text-align: left'>" . $akun['nama'] . " </td>";
			$html = $html . "<td style='text-align: right'>" . $this->format_number_report($jumlah) . " </td>";
			$html = $html . "</tr>";
		}
		$html = $html . "<tr> ";
		$html = $html . "<td style='text-align: left'><b>Jumlah " . $nama_sub . " </b></td>";
		$html = $html . "<td style='text-align: right'>" . $this->format_number_report($sub_total) . " </td>";
		$html = $html . "</tr>";

		/* MODAL LABA RUGI DITAHAN */
		$res_akun = $this->db->query("SELECT * from acc_akun where kode='311'")->row_array();
		$html = $html . "<tr> ";
		$html = $html . "<td style='text-align: left'><b>" . $res_akun['nama'] . "</b> </td>";
		$html = $html . "<td style='text-align: left'> </td>";
		$html = $html . "<tr> ";
		$nama_sub = $res_akun['nama'];

		/* LABA (RUGI) DITAHAN*/
		$res_akun = $this->db->query("select * from acc_akun where LENGTH(kode)=3  and (kode like '311%')   order by kode ")->result_array();
		$sub_total = 0;
		$total_modal = 0;
		// $total_pasiva = 0;
		foreach ($res_akun as $key => $akun) {
			if ($tipe_laporan == 'v1') {
				$query = "SELECT sum(debet-kredit)as jumlah FROM acc_jurnal_ht a inner join acc_jurnal_dt b 
				on a.id=b.jurnal_id inner join acc_akun c on b.acc_akun_id=c.id
				where c.kode like '" . $akun['kode'] . "%' 
				and a.tanggal <= '" . $tanggal_akhir . "'  ;";
			} else 	if ($tipe_laporan == 'v2') {
				$query = "SELECT sum(debet-kredit)as jumlah FROM acc_jurnal_ht a inner join acc_jurnal_dt b 
				on a.id=b.jurnal_id inner join acc_akun c on b.acc_akun_id=c.id
				inner join gbm_organisasi d on b.lokasi_id=d.id
				where c.kode like '" . $akun['kode'] . "%' 
				and a.tanggal <= '" . $tanggal_akhir . "' 
				and d.kode not in ('SBME') ;";
			} else		if ($tipe_laporan == 'v3') {
				$query = "SELECT sum(debet-kredit)as jumlah FROM acc_jurnal_ht a inner join acc_jurnal_dt b 
				on a.id=b.jurnal_id inner join acc_akun c on b.acc_akun_id=c.id
				inner join gbm_organisasi d on b.lokasi_id=d.id
				where c.kode like '" . $akun['kode'] . "%' 
				and a.tanggal <= '" . $tanggal_akhir . "' 
				and d.kode  in ('SBME') ;";
			}

			$resJumlah  = $this->db->query($query)->row_array();
			$jumlah = (!empty($resJumlah['jumlah'])) ? $resJumlah['jumlah'] * -1 : 0;
			$sub_total = $sub_total + $jumlah;
			$total_modal = $total_modal + $jumlah;
			$total_pasiva = $total_pasiva + $jumlah;
			$html = $html . "<tr> ";
			$html = $html . "<td style='text-align: left'>" . $akun['nama'] . " </td>";
			$html = $html . "<td style='text-align: right'>" . $this->format_number_report($jumlah) . " </td>";
			$html = $html . "</tr>";
		}
		$html = $html . "<tr> ";
		$html = $html . "<td style='text-align: left'><b>Jumlah " . $nama_sub . " </b></td>";
		$html = $html . "<td style='text-align: right'>" . $this->format_number_report($sub_total) . " </td>";
		$html = $html . "</tr>";

		// $html = $html . "<tr>";
		// $html = $html . "<td style='text-align: left'><b>JUMLAH MODAL</b></td>";
		// $html = $html . "<td style='text-align: right'><b>" . $this->format_number_report($total_modal) . " </b></td>";
		// $html = $html . "</tr>";


		$html = $html . "<tr>";
		$html = $html . "<td style='text-align: left'><b>TOTAL HUTANG & MODAL</b></td>";
		$html = $html . "<td style='text-align: right'><b>" . $this->format_number_report($total_pasiva) . " </b></td>";
		$html = $html . "</tr>";
		$html = $html . "</table>";



		if ($format_laporan == 'xls') {
			$reader = new \PhpOffice\PhpSpreadsheet\Reader\Html;
			$spreadsheet = $reader->loadFromString($html);
			// $reader->setSheetIndex(1);
			//$spreadhseet = $reader->loadFromString($secondHtmlString, $spreadsheet);
			$objWriter = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
			header("Pragma: public");
			header("Expires: 0");
			header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
			header("Content-Type: application/force-download");
			header("Content-Type: application/octet-stream");
			header("Content-Type: application/download");
			header("Content-Disposition: attachment;filename=test.xlsx");
			header("Content-Transfer-Encoding: binary ");

			ob_end_clean();
			ob_start();
			$objWriter->save('php://output');
		} else if ($format_laporan == 'view') {
			echo $html;
		} else {
			$filename = 'report_' . time();
			$this->pdfgenerator->generate($html, $filename, true, 'A4', 'landscape');
		}
	}
	function laporan_neraca_inti_v3()
	{
		error_reporting(0);
		$tanggal_akhir =  $this->post('tgl_akhir', true);
		$format_laporan =  $this->post('format_laporan', true);
		$judulLokasi = 'Semua';



		$res_akun_laba_berjalan = $this->db->query("SELECT * from acc_auto_jurnal 
				   where kode='LABA_RUGI_BERJALAN'")->row_array();
		if (empty($res_akun_laba_berjalan)) {
			//$this->set_response(array("status" => "NOT OK", "data" => "LABA_RUGI_BERJALAN Tidak ada Data Setting Jurnal"), REST_Controller::HTTP_NOT_FOUND);
			//return;
		}
		$akun_laba_berjalan = $res_akun_laba_berjalan['acc_akun_id'];
		$res = array();
		// var_dump($results);
		$this->load->helper("terbilang");
		$this->load->helper("antech_helper");
		$this->load->library('pdfgenerator');
		if ($format_laporan == 'view') {
			$html = get_header_report_v2();
		} else {
			$html = get_header_pdf_report();
		}
		$html = $html . '<div class="row">
		<div class="span12">
			<br>
		  <div class="kop-print">
		  <div class="kop-nama">PT. ANNAJAH TECHNOLOGY INDONESIA</div>
		  <div class="kop-info"> Komplek Vila Sehati B12 , Tapos - Depok</div>
		  <div class="kop-info">Telp : 081387373939</div>
		</div>
			<hr class="kop-print-hr">
		</div>
		</div>
		<h3 class="title">LAPORAN NERACA V3</h3>
		<table class="no_border" style="width:30%">
				  
				  
				  <tr>	
						  <td style="width:20%">PERIODE</td>
						  <td>:</td>
						  <td >' . $tanggal_akhir . '</td>
				  </tr>
	  
				  
		  </table>
		  <br>';
		$html = $html . "
<table   border='1' width='100%' style='border-collapse: collapse;'>
<thead>

";

		$total_aktiva = 0;
		/* AKTIVA */
		$res_akun = $this->db->query("SELECT * from acc_akun	where kode='1'")->row_array();
		$html = $html . "<tr> ";
		$html = $html . "<th style='text-align: left'><b>" . $res_akun['nama'] . "</b> </th>";
		$html = $html . "<th style='text-align: left'> </th>";
		$html = $html . "<tr> ";


		/* AKTIVA LANCAR */
		$res_akun = $this->db->query("SELECT * from acc_akun where kode='11'")->row_array();
		$html = $html . "<tr> ";
		$html = $html . "<th style='text-align: left'><b>" . $res_akun['nama'] . "</b> </th>";
		$html = $html . "<th style='text-align: left'> </th>";
		$html = $html . "<tr> </thead>
		";
		$nama_sub = $res_akun['nama'];

		/* AKTIVA LANCAR DETAIL*/
		$res_akun = $this->db->query("select * from acc_akun where LENGTH(kode)=3  and (kode like '11%')   order by kode ")->result_array();
		$sub_total = 0;

		foreach ($res_akun as $key => $akun) {

			$query = "SELECT sum(debet-kredit)as jumlah FROM acc_jurnal_ht a inner join acc_jurnal_dt b 
			on a.id=b.jurnal_id inner join acc_akun c on b.acc_akun_id=c.id
			where c.kode like '" . $akun['kode'] . "%' 
			and a.tanggal <= '" . $tanggal_akhir . "' 
			and b.lokasi_id <>265 ;";

			$resJumlah  = $this->db->query($query)->row_array();
			$jumlah = (!empty($resJumlah['jumlah'])) ? $resJumlah['jumlah'] : 0;
			$sub_total = $sub_total + $jumlah;
			$total_aktiva = $total_aktiva + $jumlah;
			$html = $html . "<tr> ";
			$html = $html . "<td style='text-align: left'>" . $akun['nama'] . " </td>";
			$html = $html . "<td style='text-align: right'>" . $this->format_number_report($jumlah) . " </td>";
			$html = $html . "</tr>";
		}
		$html = $html . "<tr> ";
		$html = $html . "<td style='text-align: left'><b>Jumlah " . $nama_sub . " </b></td>";
		$html = $html . "<td style='text-align: right'>" . $this->format_number_report($sub_total) . " </td>";
		$html = $html . "</tr>";

		/* AKTIVA TIDAK LANCAR*/
		$res_akun = $this->db->query("SELECT * from acc_akun where kode='12'")->row_array();
		$html = $html . "<tr> ";
		$html = $html . "<td style='text-align: left'><b>" . $res_akun['nama'] . "</b> </td>";
		$html = $html . "<td style='text-align: left'> </td>";
		$html = $html . "<tr> ";
		$nama_sub = $res_akun['nama'];
		/* AKTIVA TIDAK LANCAR DETAIL */
		$res_akun = $this->db->query("select * from acc_akun where LENGTH(kode)=3  and	 (kode like '12%')   order by kode ")->result_array();
		$sub_total = 0;

		foreach ($res_akun as $key => $akun) {
			$query = "SELECT sum(debet-kredit)as jumlah FROM acc_jurnal_ht a inner join acc_jurnal_dt b 
			on a.id=b.jurnal_id inner join acc_akun c on b.acc_akun_id=c.id
			where c.kode like '" . $akun['kode'] . "%' 
			and a.tanggal <= '" . $tanggal_akhir . "' 
			and b.lokasi_id <>265 ;";

			$resJumlah  = $this->db->query($query)->row_array();
			$jumlah = (!empty($resJumlah['jumlah'])) ? $resJumlah['jumlah'] : 0;
			$sub_total = $sub_total + $jumlah;
			$total_aktiva = $total_aktiva + $jumlah;
			$html = $html . "<tr> ";
			$html = $html . "<td style='text-align: left'>" . $akun['nama'] . " </td>";
			$html = $html . "<td style='text-align: right'>" . $this->format_number_report($jumlah) . " </td>";
			$html = $html . "</tr>";
		}
		$html = $html . "<tr> ";
		$html = $html . "<td style='text-align: left'><b>Jumlah " . $nama_sub . " </b></td>";
		$html = $html . "<td style='text-align: right'>" . $this->format_number_report($sub_total) . " </td>";
		$html = $html . "</tr>";

		$html = $html . "<tr>";
		$html = $html . "<td style='text-align: center'><b>TOTAL AKTIVA</b></td>";
		$html = $html . "<td style='text-align: right'><b>" . $this->format_number_report($total_aktiva) . " </b></td>";
		$html = $html . "</tr>";

		/* PASIVA */
		$html = $html . "<tr> ";
		$html = $html . "<td style='text-align: left'><b>LIABILITAS DAN EKUITAS</b> </td>";
		$html = $html . "<td style='text-align: left'> </td>";
		$html = $html . "<tr> ";


		$res_akun = $this->db->query("SELECT * from acc_akun	where kode='2'")->row_array();
		$html = $html . "<tr> ";
		$html = $html . "<td style='text-align: left'><b>" . $res_akun['nama'] . "</b> </td>";
		$html = $html . "<td style='text-align: left'> </td>";
		$html = $html . "<tr> ";


		/* LIABILITAS JANGKA PENDEK */
		$res_akun = $this->db->query("SELECT * from acc_akun where kode='21'")->row_array();
		$html = $html . "<tr> ";
		$html = $html . "<td style='text-align: left'><b>" . $res_akun['nama'] . "</b> </td>";
		$html = $html . "<td style='text-align: left'> </td>";
		$html = $html . "<tr> ";
		$nama_sub = $res_akun['nama'];

		/* LIABILITAS DETAIL*/
		$res_akun = $this->db->query("select * from acc_akun where LENGTH(kode)=3  and (kode like '21%')   order by kode ")->result_array();
		$sub_total = 0;
		$total_liabilities = 0;
		$total_pasiva = 0;
		foreach ($res_akun as $key => $akun) {
			$query = "SELECT sum(debet-kredit)as jumlah FROM acc_jurnal_ht a inner join acc_jurnal_dt b 
			on a.id=b.jurnal_id inner join acc_akun c on b.acc_akun_id=c.id
			where c.kode like '" . $akun['kode'] . "%' 
			and a.tanggal <= '" . $tanggal_akhir . "' 
			and b.lokasi_id <>265 ;";

			$resJumlah  = $this->db->query($query)->row_array();
			$jumlah = (!empty($resJumlah['jumlah'])) ? $resJumlah['jumlah'] * -1 : 0;
			$sub_total = $sub_total + $jumlah;
			$total_liabilities = $total_liabilities + $jumlah;
			$total_pasiva = $total_pasiva + $jumlah;
			$html = $html . "<tr> ";
			$html = $html . "<td style='text-align: left'>" . $akun['nama'] . " </td>";
			$html = $html . "<td style='text-align: right'>" . $this->format_number_report($jumlah) . " </td>";
			$html = $html . "</tr>";
		}
		$html = $html . "<tr> ";
		$html = $html . "<td style='text-align: left'><b>Jumlah " . $nama_sub . " </b></td>";
		$html = $html . "<td style='text-align: right'>" . $this->format_number_report($sub_total) . " </td>";
		$html = $html . "</tr>";

		/* LIABILITAS JANGKA panjang */
		$res_akun = $this->db->query("SELECT * from acc_akun where kode='22'")->row_array();
		$html = $html . "<tr> ";
		$html = $html . "<td style='text-align: left'><b>" . $res_akun['nama'] . "</b> </td>";
		$html = $html . "<td style='text-align: left'> </td>";
		$html = $html . "<tr> ";
		$nama_sub = $res_akun['nama'];

		/* LIABILITAS DETAIL*/
		$res_akun = $this->db->query("select * from acc_akun where LENGTH(kode)=3  and (kode like '22%')   order by kode ")->result_array();
		$sub_total = 0;
		$total_liabilities = 0;
		$total_pasiva = 0;
		foreach ($res_akun as $key => $akun) {
			$query = "SELECT sum(debet-kredit)as jumlah FROM acc_jurnal_ht a inner join acc_jurnal_dt b 
			on a.id=b.jurnal_id inner join acc_akun c on b.acc_akun_id=c.id
			where c.kode like '" . $akun['kode'] . "%' 
			and a.tanggal <= '" . $tanggal_akhir . "' 
			and b.lokasi_id <>265 ;";

			$resJumlah  = $this->db->query($query)->row_array();
			$jumlah = (!empty($resJumlah['jumlah'])) ? $resJumlah['jumlah'] * -1 : 0;
			$sub_total = $sub_total + $jumlah;
			$total_liabilities = $total_liabilities + $jumlah;
			$total_pasiva = $total_pasiva + $jumlah;
			$html = $html . "<tr> ";
			$html = $html . "<td style='text-align: left'>" . $akun['nama'] . " </td>";
			$html = $html . "<td style='text-align: right'>" . $this->format_number_report($jumlah) . " </td>";
			$html = $html . "</tr>";
		}
		$html = $html . "<tr> ";
		$html = $html . "<td style='text-align: left'><b>Jumlah " . $nama_sub . " </b></td>";
		$html = $html . "<td style='text-align: right'>" . $this->format_number_report($sub_total) . " </td>";
		$html = $html . "</tr>";

		$html = $html . "<tr>";
		$html = $html . "<td style='text-align: left'><b>JUMLAH LIABILITAS</b></td>";
		$html = $html . "<td style='text-align: right'><b>" . $this->format_number_report($total_liabilities) . " </b></td>";
		$html = $html . "</tr>";

		/* MODAL */
		$res_akun = $this->db->query("SELECT * from acc_akun	where kode='3'")->row_array();
		$html = $html . "<tr> ";
		$html = $html . "<td style='text-align: left'><b>" . $res_akun['nama'] . "</b> </td>";
		$html = $html . "<td style='text-align: left'> </td>";
		$html = $html . "<tr> ";


		/* MMODAL */
		$res_akun = $this->db->query("SELECT * from acc_akun where kode='310'")->row_array();
		$html = $html . "<tr> ";
		$html = $html . "<td style='text-align: left'><b>" . $res_akun['nama'] . "</b> </td>";
		$html = $html . "<td style='text-align: left'> </td>";
		$html = $html . "<tr> ";
		$nama_sub = $res_akun['nama'];

		/* MODAL DETAIL*/
		$res_akun = $this->db->query("select * from acc_akun where LENGTH(kode)=3  and (kode like '310%')   order by kode ")->result_array();
		$sub_total = 0;
		$total_liabilities = 0;
		// $total_pasiva = 0;
		foreach ($res_akun as $key => $akun) {
			$query = "SELECT sum(debet-kredit)as jumlah FROM acc_jurnal_ht a inner join acc_jurnal_dt b 
			on a.id=b.jurnal_id inner join acc_akun c on b.acc_akun_id=c.id
			where c.kode like '" . $akun['kode'] . "%' 
			and a.tanggal <= '" . $tanggal_akhir . "' 
			and b.lokasi_id <>265 ;";

			$resJumlah  = $this->db->query($query)->row_array();
			$jumlah = (!empty($resJumlah['jumlah'])) ? $resJumlah['jumlah'] * -1 : 0;
			$sub_total = $sub_total + $jumlah;
			$total_liabilities = $total_liabilities + $jumlah;
			$total_pasiva = $total_pasiva + $jumlah;
			$html = $html . "<tr> ";
			$html = $html . "<td style='text-align: left'>" . $akun['nama'] . " </td>";
			$html = $html . "<td style='text-align: right'>" . $this->format_number_report($jumlah) . " </td>";
			$html = $html . "</tr>";
		}
		$html = $html . "<tr> ";
		$html = $html . "<td style='text-align: left'><b>Jumlah " . $nama_sub . " </b></td>";
		$html = $html . "<td style='text-align: right'>" . $this->format_number_report($sub_total) . " </td>";
		$html = $html . "</tr>";

		/* MODAL LABA RUGI DITAHAN */
		$res_akun = $this->db->query("SELECT * from acc_akun where kode='311'")->row_array();
		$html = $html . "<tr> ";
		$html = $html . "<td style='text-align: left'><b>" . $res_akun['nama'] . "</b> </td>";
		$html = $html . "<td style='text-align: left'> </td>";
		$html = $html . "<tr> ";
		$nama_sub = $res_akun['nama'];

		/* LABA (RUGI) DITAHAN*/
		$res_akun = $this->db->query("select * from acc_akun where LENGTH(kode)=3  and (kode like '311%')   order by kode ")->result_array();
		$sub_total = 0;
		$total_modal = 0;
		// $total_pasiva = 0;
		foreach ($res_akun as $key => $akun) {
			$query = "SELECT sum(debet-kredit)as jumlah FROM acc_jurnal_ht a inner join acc_jurnal_dt b 
			on a.id=b.jurnal_id inner join acc_akun c on b.acc_akun_id=c.id
			where c.kode like '" . $akun['kode'] . "%' 
			and a.tanggal <= '" . $tanggal_akhir . "' 
			and b.lokasi_id <>265 ;";

			$resJumlah  = $this->db->query($query)->row_array();
			$jumlah = (!empty($resJumlah['jumlah'])) ? $resJumlah['jumlah'] * -1 : 0;
			$sub_total = $sub_total + $jumlah;
			$total_modal = $total_modal + $jumlah;
			$total_pasiva = $total_pasiva + $jumlah;
			$html = $html . "<tr> ";
			$html = $html . "<td style='text-align: left'>" . $akun['nama'] . " </td>";
			$html = $html . "<td style='text-align: right'>" . $this->format_number_report($jumlah) . " </td>";
			$html = $html . "</tr>";
		}
		$html = $html . "<tr> ";
		$html = $html . "<td style='text-align: left'><b>Jumlah " . $nama_sub . " </b></td>";
		$html = $html . "<td style='text-align: right'>" . $this->format_number_report($sub_total) . " </td>";
		$html = $html . "</tr>";

		// $html = $html . "<tr>";
		// $html = $html . "<td style='text-align: left'><b>JUMLAH MODAL</b></td>";
		// $html = $html . "<td style='text-align: right'><b>" . $this->format_number_report($total_modal) . " </b></td>";
		// $html = $html . "</tr>";


		$html = $html . "<tr>";
		$html = $html . "<td style='text-align: left'><b>TOTAL HUTANG & MODAL</b></td>";
		$html = $html . "<td style='text-align: right'><b>" . $this->format_number_report($total_pasiva) . " </b></td>";
		$html = $html . "</tr>";
		$html = $html . "</table>";



		if ($format_laporan == 'xls') {
			$reader = new \PhpOffice\PhpSpreadsheet\Reader\Html;
			$spreadsheet = $reader->loadFromString($html);
			// $reader->setSheetIndex(1);
			//$spreadhseet = $reader->loadFromString($secondHtmlString, $spreadsheet);
			$objWriter = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
			header("Pragma: public");
			header("Expires: 0");
			header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
			header("Content-Type: application/force-download");
			header("Content-Type: application/octet-stream");
			header("Content-Type: application/download");
			header("Content-Disposition: attachment;filename=test.xlsx");
			header("Content-Transfer-Encoding: binary ");

			ob_end_clean();
			ob_start();
			$objWriter->save('php://output');
		} else if ($format_laporan == 'view') {
			echo $html;
		} else {
			$filename = 'report_' . time();
			$this->pdfgenerator->generate($html, $filename, true, 'A4', 'landscape');
		}
	}

	function laporan_neraca_v4()
	{
		error_reporting(0);
		$tanggal_akhir =  $this->post('tgl_akhir', true);
		$format_laporan =  $this->post('format_laporan', true);
		$judulLokasi = 'Semua';



		$res_akun_laba_berjalan = $this->db->query("SELECT * from acc_auto_jurnal 
				   where kode='LABA_RUGI_BERJALAN'")->row_array();
		if (empty($res_akun_laba_berjalan)) {
			//$this->set_response(array("status" => "NOT OK", "data" => "LABA_RUGI_BERJALAN Tidak ada Data Setting Jurnal"), REST_Controller::HTTP_NOT_FOUND);
			//return;
		}
		$akun_laba_berjalan = $res_akun_laba_berjalan['acc_akun_id'];
		$res = array();
		// var_dump($results);
		$this->load->helper("terbilang");
		$this->load->helper("antech_helper");
		$this->load->library('pdfgenerator');
		if ($format_laporan == 'view') {
			$html = get_header_report_v2();
		} else {
			$html = get_header_pdf_report();
		}
		$html = $html . '<div class="row">
		<div class="span12">
			<br>
		  <div class="kop-print">
		  <div class="kop-nama">PT. ANNAJAH TECHNOLOGY INDONESIA</div>
		  <div class="kop-info"> Komplek Vila Sehati B12 , Tapos - Depok</div>
		  <div class="kop-info">Telp : 081387373939</div>
		</div>
			<hr class="kop-print-hr">
		</div>
		</div>
		<h3 class="title">LAPORAN NERACA V3</h3>
		<table class="no_border" style="width:30%">
				  
				  
				  <tr>	
						  <td style="width:20%">PERIODE</td>
						  <td>:</td>
						  <td >' . $tanggal_akhir . '</td>
				  </tr>
	  
				  
		  </table>
		  <br>';
		$html = $html . "
<table   border='1' width='100%' style='border-collapse: collapse;'>

";

		$total_aktiva = 0;
		/* AKTIVA */
		$res_akun = $this->db->query("SELECT * from acc_akun	where kode='1'")->row_array();
		$html = $html . "<tr> ";
		$html = $html . "<td style='text-align: left'><b>" . $res_akun['nama'] . "</b> </td>";
		$html = $html . "<td style='text-align: left'> </td>";
		$html = $html . "<tr> ";


		/* AKTIVA LANCAR */
		$res_akun = $this->db->query("SELECT * from acc_akun where kode='11'")->row_array();
		$html = $html . "<tr> ";
		$html = $html . "<td style='text-align: left'><b>" . $res_akun['nama'] . "</b> </td>";
		$html = $html . "<td style='text-align: left'> </td>";
		$html = $html . "<tr> ";
		$nama_sub = $res_akun['nama'];

		/* AKTIVA LANCAR DETAIL*/
		$res_akun = $this->db->query("select * from acc_akun where LENGTH(kode)=3  and (kode like '11%')   order by kode ")->result_array();
		$sub_total = 0;

		foreach ($res_akun as $key => $akun) {

			$query = "SELECT sum(debet-kredit)as jumlah FROM acc_jurnal_ht a inner join acc_jurnal_dt b 
			on a.id=b.jurnal_id inner join acc_akun c on b.acc_akun_id=c.id
			where c.kode like '" . $akun['kode'] . "%' 
			and a.tanggal <= '" . $tanggal_akhir . "'  ;";

			$resJumlah  = $this->db->query($query)->row_array();
			$jumlah = (!empty($resJumlah['jumlah'])) ? $resJumlah['jumlah'] : 0;
			$sub_total = $sub_total + $jumlah;
			$total_aktiva = $total_aktiva + $jumlah;
			$html = $html . "<tr> ";
			$html = $html . "<td style='text-align: left'>" . $akun['nama'] . " </td>";
			$html = $html . "<td style='text-align: right'>" . $this->format_number_report($jumlah) . " </td>";
			$html = $html . "</tr>";
		}
		$html = $html . "<tr> ";
		$html = $html . "<td style='text-align: left'><b>Jumlah " . $nama_sub . " </b></td>";
		$html = $html . "<td style='text-align: right'>" . $this->format_number_report($sub_total) . " </td>";
		$html = $html . "</tr>";

		/* AKTIVA TIDAK LANCAR*/
		$res_akun = $this->db->query("SELECT * from acc_akun where kode='12'")->row_array();
		$html = $html . "<tr> ";
		$html = $html . "<td style='text-align: left'><b>" . $res_akun['nama'] . "</b> </td>";
		$html = $html . "<td style='text-align: left'> </td>";
		$html = $html . "<tr> ";
		$nama_sub = $res_akun['nama'];
		/* AKTIVA TIDAK LANCAR DETAIL */
		$res_akun = $this->db->query("select * from acc_akun where LENGTH(kode)=3  and	 (kode like '12%')   order by kode ")->result_array();
		$sub_total = 0;

		foreach ($res_akun as $key => $akun) {
			$query = "SELECT sum(debet-kredit)as jumlah FROM acc_jurnal_ht a inner join acc_jurnal_dt b 
			on a.id=b.jurnal_id inner join acc_akun c on b.acc_akun_id=c.id
			where c.kode like '" . $akun['kode'] . "%' 
			and a.tanggal <= '" . $tanggal_akhir . "'  ;";

			$resJumlah  = $this->db->query($query)->row_array();
			$jumlah = (!empty($resJumlah['jumlah'])) ? $resJumlah['jumlah'] : 0;
			$sub_total = $sub_total + $jumlah;
			$total_aktiva = $total_aktiva + $jumlah;
			$html = $html . "<tr> ";
			$html = $html . "<td style='text-align: left'>" . $akun['nama'] . " </td>";
			$html = $html . "<td style='text-align: right'>" . $this->format_number_report($jumlah) . " </td>";
			$html = $html . "</tr>";
		}
		$html = $html . "<tr> ";
		$html = $html . "<td style='text-align: left'><b>Jumlah " . $nama_sub . " </b></td>";
		$html = $html . "<td style='text-align: right'>" . $this->format_number_report($sub_total) . " </td>";
		$html = $html . "</tr>";

		$html = $html . "<tr>";
		$html = $html . "<td style='text-align: center'><b>TOTAL AKTIVA</b></td>";
		$html = $html . "<td style='text-align: right'><b>" . $this->format_number_report($total_aktiva) . " </b></td>";
		$html = $html . "</tr>";

		/* PASIVA */
		$html = $html . "<tr> ";
		$html = $html . "<td style='text-align: left'><b>LIABILITAS DAN EKUITAS</b> </td>";
		$html = $html . "<td style='text-align: left'> </td>";
		$html = $html . "<tr> ";


		$res_akun = $this->db->query("SELECT * from acc_akun	where kode='2'")->row_array();
		$html = $html . "<tr> ";
		$html = $html . "<td style='text-align: left'><b>" . $res_akun['nama'] . "</b> </td>";
		$html = $html . "<td style='text-align: left'> </td>";
		$html = $html . "<tr> ";


		/* LIABILITAS JANGKA PENDEK */
		$res_akun = $this->db->query("SELECT * from acc_akun where kode='21'")->row_array();
		$html = $html . "<tr> ";
		$html = $html . "<td style='text-align: left'><b>" . $res_akun['nama'] . "</b> </td>";
		$html = $html . "<td style='text-align: left'> </td>";
		$html = $html . "<tr> ";
		$nama_sub = $res_akun['nama'];

		/* LIABILITAS DETAIL*/
		$res_akun = $this->db->query("select * from acc_akun where LENGTH(kode)=3  and (kode like '21%')   order by kode ")->result_array();
		$sub_total = 0;
		$total_liabilities = 0;
		$total_pasiva = 0;
		foreach ($res_akun as $key => $akun) {
			$query = "SELECT sum(debet-kredit)as jumlah FROM acc_jurnal_ht a inner join acc_jurnal_dt b 
			on a.id=b.jurnal_id inner join acc_akun c on b.acc_akun_id=c.id
			where c.kode like '" . $akun['kode'] . "%' 
			and a.tanggal <= '" . $tanggal_akhir . "'  ;";

			$resJumlah  = $this->db->query($query)->row_array();
			$jumlah = (!empty($resJumlah['jumlah'])) ? $resJumlah['jumlah'] * -1 : 0;
			$sub_total = $sub_total + $jumlah;
			$total_liabilities = $total_liabilities + $jumlah;
			$total_pasiva = $total_pasiva + $jumlah;
			$html = $html . "<tr> ";
			$html = $html . "<td style='text-align: left'>" . $akun['nama'] . " </td>";
			$html = $html . "<td style='text-align: right'>" . $this->format_number_report($jumlah) . " </td>";
			$html = $html . "</tr>";
		}
		$html = $html . "<tr> ";
		$html = $html . "<td style='text-align: left'><b>Jumlah " . $nama_sub . " </b></td>";
		$html = $html . "<td style='text-align: right'>" . $this->format_number_report($sub_total) . " </td>";
		$html = $html . "</tr>";

		/* LIABILITAS JANGKA panjang */
		$res_akun = $this->db->query("SELECT * from acc_akun where kode='22'")->row_array();
		$html = $html . "<tr> ";
		$html = $html . "<td style='text-align: left'><b>" . $res_akun['nama'] . "</b> </td>";
		$html = $html . "<td style='text-align: left'> </td>";
		$html = $html . "<tr> ";
		$nama_sub = $res_akun['nama'];

		/* LIABILITAS DETAIL*/
		$res_akun = $this->db->query("select * from acc_akun where LENGTH(kode)=3  and (kode like '22%')   order by kode ")->result_array();
		$sub_total = 0;
		$total_liabilities = 0;
		$total_pasiva = 0;
		foreach ($res_akun as $key => $akun) {
			$query = "SELECT sum(debet-kredit)as jumlah FROM acc_jurnal_ht a inner join acc_jurnal_dt b 
			on a.id=b.jurnal_id inner join acc_akun c on b.acc_akun_id=c.id
			where c.kode like '" . $akun['kode'] . "%' 
			and a.tanggal <= '" . $tanggal_akhir . "'  ;";

			$resJumlah  = $this->db->query($query)->row_array();
			$jumlah = (!empty($resJumlah['jumlah'])) ? $resJumlah['jumlah'] * -1 : 0;
			$sub_total = $sub_total + $jumlah;
			$total_liabilities = $total_liabilities + $jumlah;
			$total_pasiva = $total_pasiva + $jumlah;
			$html = $html . "<tr> ";
			$html = $html . "<td style='text-align: left'>" . $akun['nama'] . " </td>";
			$html = $html . "<td style='text-align: right'>" . $this->format_number_report($jumlah) . " </td>";
			$html = $html . "</tr>";
		}
		$html = $html . "<tr> ";
		$html = $html . "<td style='text-align: left'><b>Jumlah " . $nama_sub . " </b></td>";
		$html = $html . "<td style='text-align: right'>" . $this->format_number_report($sub_total) . " </td>";
		$html = $html . "</tr>";

		$html = $html . "<tr>";
		$html = $html . "<td style='text-align: left'><b>JUMLAH LIABILITAS</b></td>";
		$html = $html . "<td style='text-align: right'><b>" . $this->format_number_report($total_aktiva) . " </b></td>";
		$html = $html . "</tr>";

		/* MODAL */
		$res_akun = $this->db->query("SELECT * from acc_akun	where kode='3'")->row_array();
		$html = $html . "<tr> ";
		$html = $html . "<td style='text-align: left'><b>" . $res_akun['nama'] . "</b> </td>";
		$html = $html . "<td style='text-align: left'> </td>";
		$html = $html . "<tr> ";


		/* LIABILITAS JANGKA PENDEK */
		$res_akun = $this->db->query("SELECT * from acc_akun where kode='310'")->row_array();
		$html = $html . "<tr> ";
		$html = $html . "<td style='text-align: left'><b>" . $res_akun['nama'] . "</b> </td>";
		$html = $html . "<td style='text-align: left'> </td>";
		$html = $html . "<tr> ";
		$nama_sub = $res_akun['nama'];

		/* LIABILITAS DETAIL*/
		$res_akun = $this->db->query("select * from acc_akun where LENGTH(kode)=3  and (kode like '310%')   order by kode ")->result_array();
		$sub_total = 0;
		$total_liabilities = 0;
		$total_pasiva = 0;
		foreach ($res_akun as $key => $akun) {
			$query = "SELECT sum(debet-kredit)as jumlah FROM acc_jurnal_ht a inner join acc_jurnal_dt b 
			on a.id=b.jurnal_id inner join acc_akun c on b.acc_akun_id=c.id
			where c.kode like '" . $akun['kode'] . "%' 
			and a.tanggal <= '" . $tanggal_akhir . "'  ;";

			$resJumlah  = $this->db->query($query)->row_array();
			$jumlah = (!empty($resJumlah['jumlah'])) ? $resJumlah['jumlah'] * -1 : 0;
			$sub_total = $sub_total + $jumlah;
			$total_liabilities = $total_liabilities + $jumlah;
			$total_pasiva = $total_pasiva + $jumlah;
			$html = $html . "<tr> ";
			$html = $html . "<td style='text-align: left'>" . $akun['nama'] . " </td>";
			$html = $html . "<td style='text-align: right'>" . $this->format_number_report($jumlah) . " </td>";
			$html = $html . "</tr>";
		}
		$html = $html . "<tr> ";
		$html = $html . "<td style='text-align: left'><b>Jumlah " . $nama_sub . " </b></td>";
		$html = $html . "<td style='text-align: right'>" . $this->format_number_report($sub_total) . " </td>";
		$html = $html . "</tr>";

		/* MODAL LABA RUGI DITAHAN */
		$res_akun = $this->db->query("SELECT * from acc_akun where kode='311'")->row_array();
		$html = $html . "<tr> ";
		$html = $html . "<td style='text-align: left'><b>" . $res_akun['nama'] . "</b> </td>";
		$html = $html . "<td style='text-align: left'> </td>";
		$html = $html . "<tr> ";
		$nama_sub = $res_akun['nama'];

		/* LIABILITAS DETAIL*/
		$res_akun = $this->db->query("select * from acc_akun where LENGTH(kode)=3  and (kode like '311%')   order by kode ")->result_array();
		$sub_total = 0;
		$total_liabilities = 0;
		$total_pasiva = 0;
		foreach ($res_akun as $key => $akun) {
			$query = "SELECT sum(debet-kredit)as jumlah FROM acc_jurnal_ht a inner join acc_jurnal_dt b 
			on a.id=b.jurnal_id inner join acc_akun c on b.acc_akun_id=c.id
			where c.kode like '" . $akun['kode'] . "%' 
			and a.tanggal <= '" . $tanggal_akhir . "'  ;";

			$resJumlah  = $this->db->query($query)->row_array();
			$jumlah = (!empty($resJumlah['jumlah'])) ? $resJumlah['jumlah'] * -1 : 0;
			$sub_total = $sub_total + $jumlah;
			$total_liabilities = $total_liabilities + $jumlah;
			$total_pasiva = $total_pasiva + $jumlah;
			$html = $html . "<tr> ";
			$html = $html . "<td style='text-align: left'>" . $akun['nama'] . " </td>";
			$html = $html . "<td style='text-align: right'>" . $this->format_number_report($jumlah) . " </td>";
			$html = $html . "</tr>";
		}
		$html = $html . "<tr> ";
		$html = $html . "<td style='text-align: left'><b>Jumlah " . $nama_sub . " </b></td>";
		$html = $html . "<td style='text-align: right'>" . $this->format_number_report($sub_total) . " </td>";
		$html = $html . "</tr>";

		$html = $html . "<tr>";
		$html = $html . "<td style='text-align: left'><b>JUMLAH LIABILITAS</b></td>";
		$html = $html . "<td style='text-align: right'><b>" . $this->format_number_report($total_aktiva) . " </b></td>";
		$html = $html . "</tr>";


		$html = $html . "<tr>";
		$html = $html . "<td style='text-align: left'><b>TOTAL HUTANG & MODAL</b></td>";
		$html = $html . "<td style='text-align: right'><b>" . $this->format_number_report($total_aktiva) . " </b></td>";
		$html = $html . "</tr>";
		$html = $html . "</table>";



		if ($format_laporan == 'xls') {
			$reader = new \PhpOffice\PhpSpreadsheet\Reader\Html;
			$spreadsheet = $reader->loadFromString($html);
			// $reader->setSheetIndex(1);
			//$spreadhseet = $reader->loadFromString($secondHtmlString, $spreadsheet);
			$objWriter = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
			header("Pragma: public");
			header("Expires: 0");
			header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
			header("Content-Type: application/force-download");
			header("Content-Type: application/octet-stream");
			header("Content-Type: application/download");
			header("Content-Disposition: attachment;filename=test.xlsx");
			header("Content-Transfer-Encoding: binary ");

			ob_end_clean();
			ob_start();
			$objWriter->save('php://output');
		} else if ($format_laporan == 'view') {
			echo $html;
		} else {
			$filename = 'report_' . time();
			$this->pdfgenerator->generate($html, $filename, true, 'A4', 'landscape');
		}
	}
	function laporan_neraca_inti_v4()
	{
		error_reporting(0);
		$tanggal_akhir =  $this->post('tgl_akhir', true);
		$format_laporan =  $this->post('format_laporan', true);
		$judulLokasi = 'Semua';



		$res_akun_laba_berjalan = $this->db->query("SELECT * from acc_auto_jurnal 
				   where kode='LABA_RUGI_BERJALAN'")->row_array();
		if (empty($res_akun_laba_berjalan)) {
			//$this->set_response(array("status" => "NOT OK", "data" => "LABA_RUGI_BERJALAN Tidak ada Data Setting Jurnal"), REST_Controller::HTTP_NOT_FOUND);
			//return;
		}
		$akun_laba_berjalan = $res_akun_laba_berjalan['acc_akun_id'];
		$res = array();
		// var_dump($results);
		$this->load->helper("terbilang");
		$this->load->helper("antech_helper");
		$this->load->library('pdfgenerator');
		if ($format_laporan == 'view') {
			$html = get_header_report_v2();
		} else {
			$html = get_header_pdf_report();
		}
		$html = $html . '<div class="row">
		<div class="span12">
			<br>
		  <div class="kop-print">
		  <div class="kop-nama">PT. ANNAJAH TECHNOLOGY INDONESIA</div>
		  <div class="kop-info"> Komplek Vila Sehati B12 , Tapos - Depok</div>
		  <div class="kop-info">Telp : 081387373939</div>
		</div>
			<hr class="kop-print-hr">
		</div>
		</div>
		<h3 class="title">LAPORAN NERACA V3</h3>
		<table class="no_border" style="width:30%">
				  
				  
				  <tr>	
						  <td style="width:20%">PERIODE</td>
						  <td>:</td>
						  <td >' . $tanggal_akhir . '</td>
				  </tr>
	  
				  
		  </table>
		  <br>';
		$html = $html . "
<table   border='1' width='100%' style='border-collapse: collapse;'>

";

		$total_aktiva = 0;
		/* AKTIVA */
		$res_akun = $this->db->query("SELECT * from acc_akun	where kode='1'")->row_array();
		$html = $html . "<tr> ";
		$html = $html . "<td style='text-align: left'><b>" . $res_akun['nama'] . "</b> </td>";
		$html = $html . "<td style='text-align: left'> </td>";
		$html = $html . "<tr> ";


		/* AKTIVA LANCAR */
		$res_akun = $this->db->query("SELECT * from acc_akun where kode='11'")->row_array();
		$html = $html . "<tr> ";
		$html = $html . "<td style='text-align: left'><b>" . $res_akun['nama'] . "</b> </td>";
		$html = $html . "<td style='text-align: left'> </td>";
		$html = $html . "<tr> ";
		$nama_sub = $res_akun['nama'];

		/* AKTIVA LANCAR DETAIL*/
		$res_akun = $this->db->query("select * from acc_akun where LENGTH(kode)=3  and (kode like '11%')   order by kode ")->result_array();
		$sub_total = 0;

		foreach ($res_akun as $key => $akun) {

			$query = "SELECT sum(debet-kredit)as jumlah FROM acc_jurnal_ht a inner join acc_jurnal_dt b 
			on a.id=b.jurnal_id inner join acc_akun c on b.acc_akun_id=c.id
			where c.kode like '" . $akun['kode'] . "%' 
			and a.tanggal <= '" . $tanggal_akhir . "' 
			and b.lokasi_id <>265 ;";

			$resJumlah  = $this->db->query($query)->row_array();
			$jumlah = (!empty($resJumlah['jumlah'])) ? $resJumlah['jumlah'] : 0;
			$sub_total = $sub_total + $jumlah;
			$total_aktiva = $total_aktiva + $jumlah;
			$html = $html . "<tr> ";
			$html = $html . "<td style='text-align: left'>" . $akun['nama'] . " </td>";
			$html = $html . "<td style='text-align: right'>" . $this->format_number_report($jumlah) . " </td>";
			$html = $html . "</tr>";
		}
		$html = $html . "<tr> ";
		$html = $html . "<td style='text-align: left'><b>Jumlah " . $nama_sub . " </b></td>";
		$html = $html . "<td style='text-align: right'>" . $this->format_number_report($sub_total) . " </td>";
		$html = $html . "</tr>";

		/* AKTIVA TIDAK LANCAR*/
		$res_akun = $this->db->query("SELECT * from acc_akun where kode='12'")->row_array();
		$html = $html . "<tr> ";
		$html = $html . "<td style='text-align: left'><b>" . $res_akun['nama'] . "</b> </td>";
		$html = $html . "<td style='text-align: left'> </td>";
		$html = $html . "<tr> ";
		$nama_sub = $res_akun['nama'];
		/* AKTIVA TIDAK LANCAR DETAIL */
		$res_akun = $this->db->query("select * from acc_akun where LENGTH(kode)=3  and	 (kode like '12%')   order by kode ")->result_array();
		$sub_total = 0;

		foreach ($res_akun as $key => $akun) {
			$query = "SELECT sum(debet-kredit)as jumlah FROM acc_jurnal_ht a inner join acc_jurnal_dt b 
			on a.id=b.jurnal_id inner join acc_akun c on b.acc_akun_id=c.id
			where c.kode like '" . $akun['kode'] . "%' 
			and a.tanggal <= '" . $tanggal_akhir . "' 
			and b.lokasi_id <>265 ;";

			$resJumlah  = $this->db->query($query)->row_array();
			$jumlah = (!empty($resJumlah['jumlah'])) ? $resJumlah['jumlah'] : 0;
			$sub_total = $sub_total + $jumlah;
			$total_aktiva = $total_aktiva + $jumlah;
			$html = $html . "<tr> ";
			$html = $html . "<td style='text-align: left'>" . $akun['nama'] . " </td>";
			$html = $html . "<td style='text-align: right'>" . $this->format_number_report($jumlah) . " </td>";
			$html = $html . "</tr>";
		}
		$html = $html . "<tr> ";
		$html = $html . "<td style='text-align: left'><b>Jumlah " . $nama_sub . " </b></td>";
		$html = $html . "<td style='text-align: right'>" . $this->format_number_report($sub_total) . " </td>";
		$html = $html . "</tr>";

		$html = $html . "<tr>";
		$html = $html . "<td style='text-align: center'><b>TOTAL AKTIVA</b></td>";
		$html = $html . "<td style='text-align: right'><b>" . $this->format_number_report($total_aktiva) . " </b></td>";
		$html = $html . "</tr>";

		/* PASIVA */
		$html = $html . "<tr> ";
		$html = $html . "<td style='text-align: left'><b>LIABILITAS DAN EKUITAS</b> </td>";
		$html = $html . "<td style='text-align: left'> </td>";
		$html = $html . "<tr> ";


		$res_akun = $this->db->query("SELECT * from acc_akun	where kode='2'")->row_array();
		$html = $html . "<tr> ";
		$html = $html . "<td style='text-align: left'><b>" . $res_akun['nama'] . "</b> </td>";
		$html = $html . "<td style='text-align: left'> </td>";
		$html = $html . "<tr> ";


		/* LIABILITAS JANGKA PENDEK */
		$res_akun = $this->db->query("SELECT * from acc_akun where kode='21'")->row_array();
		$html = $html . "<tr> ";
		$html = $html . "<td style='text-align: left'><b>" . $res_akun['nama'] . "</b> </td>";
		$html = $html . "<td style='text-align: left'> </td>";
		$html = $html . "<tr> ";
		$nama_sub = $res_akun['nama'];

		/* LIABILITAS DETAIL*/
		$res_akun = $this->db->query("select * from acc_akun where LENGTH(kode)=3  and (kode like '21%')   order by kode ")->result_array();
		$sub_total = 0;
		$total_liabilities = 0;
		$total_pasiva = 0;
		foreach ($res_akun as $key => $akun) {
			$query = "SELECT sum(debet-kredit)as jumlah FROM acc_jurnal_ht a inner join acc_jurnal_dt b 
			on a.id=b.jurnal_id inner join acc_akun c on b.acc_akun_id=c.id
			where c.kode like '" . $akun['kode'] . "%' 
			and a.tanggal <= '" . $tanggal_akhir . "' 
			and b.lokasi_id <>265 ;";

			$resJumlah  = $this->db->query($query)->row_array();
			$jumlah = (!empty($resJumlah['jumlah'])) ? $resJumlah['jumlah'] * -1 : 0;
			$sub_total = $sub_total + $jumlah;
			$total_liabilities = $total_liabilities + $jumlah;
			$total_pasiva = $total_pasiva + $jumlah;
			$html = $html . "<tr> ";
			$html = $html . "<td style='text-align: left'>" . $akun['nama'] . " </td>";
			$html = $html . "<td style='text-align: right'>" . $this->format_number_report($jumlah) . " </td>";
			$html = $html . "</tr>";
		}
		$html = $html . "<tr> ";
		$html = $html . "<td style='text-align: left'><b>Jumlah " . $nama_sub . " </b></td>";
		$html = $html . "<td style='text-align: right'>" . $this->format_number_report($sub_total) . " </td>";
		$html = $html . "</tr>";

		/* LIABILITAS JANGKA panjang */
		$res_akun = $this->db->query("SELECT * from acc_akun where kode='22'")->row_array();
		$html = $html . "<tr> ";
		$html = $html . "<td style='text-align: left'><b>" . $res_akun['nama'] . "</b> </td>";
		$html = $html . "<td style='text-align: left'> </td>";
		$html = $html . "<tr> ";
		$nama_sub = $res_akun['nama'];

		/* LIABILITAS DETAIL*/
		$res_akun = $this->db->query("select * from acc_akun where LENGTH(kode)=3  and (kode like '22%')   order by kode ")->result_array();
		$sub_total = 0;
		$total_liabilities = 0;
		$total_pasiva = 0;
		foreach ($res_akun as $key => $akun) {
			$query = "SELECT sum(debet-kredit)as jumlah FROM acc_jurnal_ht a inner join acc_jurnal_dt b 
			on a.id=b.jurnal_id inner join acc_akun c on b.acc_akun_id=c.id
			where c.kode like '" . $akun['kode'] . "%' 
			and a.tanggal <= '" . $tanggal_akhir . "' 
			and b.lokasi_id <>265 ;";

			$resJumlah  = $this->db->query($query)->row_array();
			$jumlah = (!empty($resJumlah['jumlah'])) ? $resJumlah['jumlah'] * -1 : 0;
			$sub_total = $sub_total + $jumlah;
			$total_liabilities = $total_liabilities + $jumlah;
			$total_pasiva = $total_pasiva + $jumlah;
			$html = $html . "<tr> ";
			$html = $html . "<td style='text-align: left'>" . $akun['nama'] . " </td>";
			$html = $html . "<td style='text-align: right'>" . $this->format_number_report($jumlah) . " </td>";
			$html = $html . "</tr>";
		}
		$html = $html . "<tr> ";
		$html = $html . "<td style='text-align: left'><b>Jumlah " . $nama_sub . " </b></td>";
		$html = $html . "<td style='text-align: right'>" . $this->format_number_report($sub_total) . " </td>";
		$html = $html . "</tr>";

		$html = $html . "<tr>";
		$html = $html . "<td style='text-align: left'><b>JUMLAH LIABILITAS</b></td>";
		$html = $html . "<td style='text-align: right'><b>" . $this->format_number_report($total_aktiva) . " </b></td>";
		$html = $html . "</tr>";

		/* MODAL */
		$res_akun = $this->db->query("SELECT * from acc_akun	where kode='3'")->row_array();
		$html = $html . "<tr> ";
		$html = $html . "<td style='text-align: left'><b>" . $res_akun['nama'] . "</b> </td>";
		$html = $html . "<td style='text-align: left'> </td>";
		$html = $html . "<tr> ";


		/* LIABILITAS JANGKA PENDEK */
		$res_akun = $this->db->query("SELECT * from acc_akun where kode='310'")->row_array();
		$html = $html . "<tr> ";
		$html = $html . "<td style='text-align: left'><b>" . $res_akun['nama'] . "</b> </td>";
		$html = $html . "<td style='text-align: left'> </td>";
		$html = $html . "<tr> ";
		$nama_sub = $res_akun['nama'];

		/* LIABILITAS DETAIL*/
		$res_akun = $this->db->query("select * from acc_akun where LENGTH(kode)=3  and (kode like '310%')   order by kode ")->result_array();
		$sub_total = 0;
		$total_liabilities = 0;
		$total_pasiva = 0;
		foreach ($res_akun as $key => $akun) {
			$query = "SELECT sum(debet-kredit)as jumlah FROM acc_jurnal_ht a inner join acc_jurnal_dt b 
			on a.id=b.jurnal_id inner join acc_akun c on b.acc_akun_id=c.id
			where c.kode like '" . $akun['kode'] . "%' 
			and a.tanggal <= '" . $tanggal_akhir . "' 
			and b.lokasi_id <>265 ;";

			$resJumlah  = $this->db->query($query)->row_array();
			$jumlah = (!empty($resJumlah['jumlah'])) ? $resJumlah['jumlah'] * -1 : 0;
			$sub_total = $sub_total + $jumlah;
			$total_liabilities = $total_liabilities + $jumlah;
			$total_pasiva = $total_pasiva + $jumlah;
			$html = $html . "<tr> ";
			$html = $html . "<td style='text-align: left'>" . $akun['nama'] . " </td>";
			$html = $html . "<td style='text-align: right'>" . $this->format_number_report($jumlah) . " </td>";
			$html = $html . "</tr>";
		}
		$html = $html . "<tr> ";
		$html = $html . "<td style='text-align: left'><b>Jumlah " . $nama_sub . " </b></td>";
		$html = $html . "<td style='text-align: right'>" . $this->format_number_report($sub_total) . " </td>";
		$html = $html . "</tr>";

		/* MODAL LABA RUGI DITAHAN */
		$res_akun = $this->db->query("SELECT * from acc_akun where kode='311'")->row_array();
		$html = $html . "<tr> ";
		$html = $html . "<td style='text-align: left'><b>" . $res_akun['nama'] . "</b> </td>";
		$html = $html . "<td style='text-align: left'> </td>";
		$html = $html . "<tr> ";
		$nama_sub = $res_akun['nama'];

		/* LIABILITAS DETAIL*/
		$res_akun = $this->db->query("select * from acc_akun where LENGTH(kode)=3  and (kode like '311%')   order by kode ")->result_array();
		$sub_total = 0;
		$total_liabilities = 0;
		$total_pasiva = 0;
		foreach ($res_akun as $key => $akun) {
			$query = "SELECT sum(debet-kredit)as jumlah FROM acc_jurnal_ht a inner join acc_jurnal_dt b 
			on a.id=b.jurnal_id inner join acc_akun c on b.acc_akun_id=c.id
			where c.kode like '" . $akun['kode'] . "%' 
			and a.tanggal <= '" . $tanggal_akhir . "' 
			and b.lokasi_id <>265 ;";

			$resJumlah  = $this->db->query($query)->row_array();
			$jumlah = (!empty($resJumlah['jumlah'])) ? $resJumlah['jumlah'] * -1 : 0;
			$sub_total = $sub_total + $jumlah;
			$total_liabilities = $total_liabilities + $jumlah;
			$total_pasiva = $total_pasiva + $jumlah;
			$html = $html . "<tr> ";
			$html = $html . "<td style='text-align: left'>" . $akun['nama'] . " </td>";
			$html = $html . "<td style='text-align: right'>" . $this->format_number_report($jumlah) . " </td>";
			$html = $html . "</tr>";
		}
		$html = $html . "<tr> ";
		$html = $html . "<td style='text-align: left'><b>Jumlah " . $nama_sub . " </b></td>";
		$html = $html . "<td style='text-align: right'>" . $this->format_number_report($sub_total) . " </td>";
		$html = $html . "</tr>";

		$html = $html . "<tr>";
		$html = $html . "<td style='text-align: left'><b>JUMLAH LIABILITAS</b></td>";
		$html = $html . "<td style='text-align: right'><b>" . $this->format_number_report($total_aktiva) . " </b></td>";
		$html = $html . "</tr>";


		$html = $html . "<tr>";
		$html = $html . "<td style='text-align: left'><b>TOTAL HUTANG & MODAL</b></td>";
		$html = $html . "<td style='text-align: right'><b>" . $this->format_number_report($total_aktiva) . " </b></td>";
		$html = $html . "</tr>";
		$html = $html . "</table>";



		if ($format_laporan == 'xls') {
			$reader = new \PhpOffice\PhpSpreadsheet\Reader\Html;
			$spreadsheet = $reader->loadFromString($html);
			// $reader->setSheetIndex(1);
			//$spreadhseet = $reader->loadFromString($secondHtmlString, $spreadsheet);
			$objWriter = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
			header("Pragma: public");
			header("Expires: 0");
			header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
			header("Content-Type: application/force-download");
			header("Content-Type: application/octet-stream");
			header("Content-Type: application/download");
			header("Content-Disposition: attachment;filename=test.xlsx");
			header("Content-Transfer-Encoding: binary ");

			ob_end_clean();
			ob_start();
			$objWriter->save('php://output');
		} else if ($format_laporan == 'view') {
			echo $html;
		} else {
			$filename = 'report_' . time();
			$this->pdfgenerator->generate($html, $filename, true, 'A4', 'landscape');
		}
	}

	function laporan_equitas_post()
	{
		error_reporting(0);
		$tanggal_akhir =  $this->post('tgl_akhir', true);
		$format_laporan =  $this->post('format_laporan', true);
		$judulLokasi = 'Semua';



		$res_akun_laba_berjalan = $this->db->query("SELECT * from acc_auto_jurnal 
				   where kode='LABA_RUGI_BERJALAN'")->row_array();
		if (empty($res_akun_laba_berjalan)) {
			//$this->set_response(array("status" => "NOT OK", "data" => "LABA_RUGI_BERJALAN Tidak ada Data Setting Jurnal"), REST_Controller::HTTP_NOT_FOUND);
			//return;
		}
		$akun_laba_berjalan = $res_akun_laba_berjalan['acc_akun_id'];
		$res = array();
		// var_dump($results);
		$this->load->helper("terbilang");
		$this->load->helper("antech_helper");
		$this->load->library('pdfgenerator');
		if ($format_laporan == 'view') {
			$html = get_header_report_v2();
		} else {
			$html = get_header_pdf_report();
		}
		$html = $html . '<div class="row">
		<div class="span12">
			<br>
		  <div class="kop-print">
		  <div class="kop-nama">PT. ANNAJAH TECHNOLOGY INDONESIA</div>
		  <div class="kop-info"> Komplek Vila Sehati B12 , Tapos - Depok</div>
		  <div class="kop-info">Telp : 081387373939</div>
		</div>
			<hr class="kop-print-hr">
		</div>
		</div>
		<h3 class="title">LAPORAN PERUBAHAN EKUITAS</h3>
		<h4 class="title">Untuk Tahun yang Berakhir pada 31 Desember 2022 dan 31 Desember 2021
		</h4>
		<table class="no_border" style="width:30%">				  			  
				  <tr>	
						  <td >Ket</td>
						  <td>Modal Saham</td>
						  <td>Uang Muka Setoran Modal</td>
						  <td>Tambahan Modal DiSetor</td>
						  <td>Saldo Laba Defisit</td>
						  <td >Jumlah</td>
				  </tr>			  
		  
		  <br>';

		$sa_jumlah_modal_saham['2020'] = 159032000000;
		$sa_jumlah_um_setoran_modal['2020'] = 0;
		$sa_jumlah_tambahan_modal_disetor['2020'] = 160000000;
		$sa_jumlah_saldo_laba['2020'] = -73987955916.85;
		$total_sa['2020'] = 85204044083.15;

		$modal_saham['2020'] = 0;
		$um_setoran_modal['2020'] = 0;
		$tambahan_modal_disetor['2020'] = 0;
		$saldo_laba['2020'] = 15830037811.02;
		$jumlah_modal_saham['2020'] = 159032000000;
		$jumlah_um_setoran_modal['2020'] = 0;
		$jumlah_tambahan_modal_disetor['2020'] = 160000000;
		$jumlah_saldo_laba['2020'] = -58157918105.83;

		$total['2020'] = $jumlah_modal_saham['2020'] + $jumlah_um_setoran_modal['2020'] + $jumlah_tambahan_modal_disetor['2020'] + $jumlah_saldo_laba['2020'];

		$modal_saham['2021'] = 0;
		$um_setoran_modal['2021'] = 0;
		$tambahan_modal_disetor['2021'] = 0;
		$saldo_laba['2021'] = 44072327549.54;
		$jumlah_modal_saham['2021'] = 159032000000;
		$jumlah_um_setoran_modal['2021'] = 0;
		$jumlah_tambahan_modal_disetor['2021'] = 160000000;
		$jumlah_saldo_laba['2021'] = -14085590556.29;

		$total['2021'] = $jumlah_modal_saham['2021'] + $jumlah_um_setoran_modal['2021'] + $jumlah_tambahan_modal_disetor['2021'] + $jumlah_saldo_laba['2021'];

		$modal_saham['2022'] = 0;
		$um_setoran_modal['2022'] = 0;
		$tambahan_modal_disetor['2022'] = 0;
		$saldo_laba['2022'] = 7720338254.26;
		$jumlah_modal_saham['2022'] = 159032000000;
		$jumlah_um_setoran_modal['2022'] = 0;
		$jumlah_tambahan_modal_disetor['2022'] = 160000000;
		$jumlah_saldo_laba['2022'] = -6365252302.03;

		$total['2022'] = $jumlah_modal_saham['2022'] + $jumlah_um_setoran_modal['2022'] + $jumlah_tambahan_modal_disetor['2022'] + $jumlah_saldo_laba['2022'];

		// $html = $html . "
		// <table   border='1' width='100%' style='border-collapse: collapse;'>";
		/* 2020 */
		$html = $html . "<tr> ";
		$html = $html . "<td> Saldo 31 Desember 2020</td>";
		$html = $html . "<td style='text-align: right'><b>" . $this->format_number_report($sa_jumlah_modal_saham['2020']) . "</b> </td>";
		$html = $html . "<td style='text-align: right'><b>" . $this->format_number_report($sa_jumlah_um_setoran_modal['2020']) . "</b> </td>";
		$html = $html . "<td style='text-align: right'><b>" . $this->format_number_report($sa_jumlah_tambahan_modal_disetor['2020']) . "</b> </td>";
		$html = $html . "<td style='text-align: right'><b>" . $this->format_number_report($sa_jumlah_saldo_laba['2020']) . "</b> </td>";
		$html = $html . "<td style='text-align: right'><b>" . $this->format_number_report($total_sa['2020']) . "</b> </td>";
		$html = $html . "</tr> ";
		$html = $html . "<tr> ";
		$html = $html . "<td>Laba Rugi Bersih</td>";
		$html = $html . "<td style='text-align: right'>" . $this->format_number_report(0) . " </td>";
		$html = $html . "<td style='text-align: right'>" . $this->format_number_report(0) . " </td>";
		$html = $html . "<td style='text-align: right'>" . $this->format_number_report(0) . " </td>";
		$html = $html . "<td style='text-align: right'>" . $this->format_number_report($saldo_laba['2020']) . "</b> </td>";
		$html = $html . "<td style='text-align: right'>" . $this->format_number_report($saldo_laba['2020']) . "</b> </td>";
		$html = $html . "</tr> ";
		$html = $html . "<tr> ";
		$html = $html . "<td>Saldo 31 Desember 2020</td>";
		$html = $html . "<td style='text-align: right'><b>" . $this->format_number_report($jumlah_modal_saham['2020']) . "</b> </td>";
		$html = $html . "<td style='text-align: right'><b>" . $this->format_number_report($jumlah_um_setoran_modal['2020']) . "</b> </td>";
		$html = $html . "<td style='text-align: right'><b>" . $this->format_number_report($jumlah_tambahan_modal_disetor['2020']) . "</b> </td>";
		$html = $html . "<td style='text-align: right'><b>" . $this->format_number_report($jumlah_saldo_laba['2020']) . "</b> </td>";
		$html = $html . "<td style='text-align: right'><b>" . $this->format_number_report($total['2020']) . "</b> </td>";
		$html = $html . "</tr> ";

		/* 2021 */
		$html = $html . "<tr> ";
		$html = $html . "<td>Reklasifikasi Uang Muka Setoran Modal</td>";
		$html = $html . "<td style='text-align: right'>" . $this->format_number_report($um_setoran_modal['2021']) . " </td>";
		$html = $html . "<td style='text-align: right'>" . $this->format_number_report(0) . " </td>";
		$html = $html . "<td style='text-align: right'>" . $this->format_number_report(0) . " </td>";
		$html = $html . "<td style='text-align: right'>" . $this->format_number_report(0) . "</b> </td>";
		$html = $html . "<td style='text-align: right'>" . $this->format_number_report($um_setoran_modal['2021']) . "</b> </td>";
		$html = $html . "</tr> ";
		$html = $html . "<tr> ";
		$html = $html . "<td>Tambahan Modal Saham</td>";
		$html = $html . "<td style='text-align: right'>" . $this->format_number_report(0) . " </td>";
		$html = $html . "<td style='text-align: right'>" . $this->format_number_report($tambahan_modal_disetor['2021']) . " </td>";
		$html = $html . "<td style='text-align: right'>" . $this->format_number_report(0) . " </td>";
		$html = $html . "<td style='text-align: right'>" . $this->format_number_report(0) . "</b> </td>";
		$html = $html . "<td style='text-align: right'>" . $this->format_number_report($tambahan_modal_disetor['2021']) . "</b> </td>";
		$html = $html . "</tr> ";
		$html = $html . "<td>Laba (Rugi) Bersih </td>";
		$html = $html . "<td style='text-align: right'>" . $this->format_number_report(0) . " </td>";
		$html = $html . "<td style='text-align: right'>" . $this->format_number_report(0) . " </td>";
		$html = $html . "<td style='text-align: right'>" . $this->format_number_report(0) . " </td>";
		$html = $html . "<td style='text-align: right'>" . $this->format_number_report($saldo_laba['2021']) . "</b> </td>";
		$html = $html . "<td style='text-align: right'>" . $this->format_number_report($saldo_laba['2021']) . "</b> </td>";
		$html = $html . "</tr> ";
		$html = $html . "<tr> ";
		$html = $html . "<td>Saldo 31 Desember 2021</td>";
		$html = $html . "<td style='text-align: right'><b>" . $this->format_number_report($jumlah_modal_saham['2021']) . "</b> </td>";
		$html = $html . "<td style='text-align: right'><b>" . $this->format_number_report($jumlah_um_setoran_modal['2021']) . "</b> </td>";
		$html = $html . "<td style='text-align: right'><b>" . $this->format_number_report($jumlah_tambahan_modal_disetor['2021']) . "</b> </td>";
		$html = $html . "<td style='text-align: right'><b>" . $this->format_number_report($jumlah_saldo_laba['2021']) . "</b> </td>";
		$html = $html . "<td style='text-align: right'><b>" . $this->format_number_report($total['2021']) . "</b> </td>";
		$html = $html . "</tr> ";

		/* 2022 */
		$html = $html . "<tr> ";
		$html = $html . "<td>Reklasifikasi Uang Muka Setoran Modal</td>";
		$html = $html . "<td style='text-align: right'>" . $this->format_number_report($um_setoran_modal['2022']) . " </td>";
		$html = $html . "<td style='text-align: right'>" . $this->format_number_report(0) . " </td>";
		$html = $html . "<td style='text-align: right'>" . $this->format_number_report(0) . " </td>";
		$html = $html . "<td style='text-align: right'>" . $this->format_number_report(0) . "</b> </td>";
		$html = $html . "<td style='text-align: right'>" . $this->format_number_report($um_setoran_modal['2022']) . "</b> </td>";
		$html = $html . "</tr> ";
		$html = $html . "<tr> ";
		$html = $html . "<td>Tambahan Modal Saham</td>";
		$html = $html . "<td style='text-align: right'>" . $this->format_number_report(0) . " </td>";
		$html = $html . "<td style='text-align: right'>" . $this->format_number_report($tambahan_modal_disetor['2022']) . " </td>";
		$html = $html . "<td style='text-align: right'>" . $this->format_number_report(0) . " </td>";
		$html = $html . "<td style='text-align: right'>" . $this->format_number_report(0) . "</b> </td>";
		$html = $html . "<td style='text-align: right'>" . $this->format_number_report($tambahan_modal_disetor['2022']) . "</b> </td>";
		$html = $html . "</tr> ";
		$html = $html . "<td>Laba (Rugi) Bersih </td>";
		$html = $html . "<td style='text-align: right'>" . $this->format_number_report(0) . " </td>";
		$html = $html . "<td style='text-align: right'>" . $this->format_number_report(0) . " </td>";
		$html = $html . "<td style='text-align: right'>" . $this->format_number_report(0) . " </td>";
		$html = $html . "<td style='text-align: right'>" . $this->format_number_report($saldo_laba['2022']) . "</b> </td>";
		$html = $html . "<td style='text-align: right'>" . $this->format_number_report($saldo_laba['2022']) . "</b> </td>";
		$html = $html . "</tr> ";
		$html = $html . "<tr> ";
		$html = $html . "<td>Saldo 31 Desember 2022</td>";
		$html = $html . "<td style='text-align: right'><b>" . $this->format_number_report($jumlah_modal_saham['2022']) . "</b> </td>";
		$html = $html . "<td style='text-align: right'><b>" . $this->format_number_report($jumlah_um_setoran_modal['2022']) . "</b> </td>";
		$html = $html . "<td style='text-align: right'><b>" . $this->format_number_report($jumlah_tambahan_modal_disetor['2022']) . "</b> </td>";
		$html = $html . "<td style='text-align: right'><b>" . $this->format_number_report($jumlah_saldo_laba['2022']) . "</b> </td>";
		$html = $html . "<td style='text-align: right'><b>" . $this->format_number_report($total['2022']) . "</b> </td>";
		$html = $html . "</tr> ";
		$html = $html . "</table>";
		echo $html;
		exit();



		$total_aktiva = 0;
		/* AKTIVA */
		$res_akun = $this->db->query("SELECT * from acc_akun	where kode='1'")->row_array();
		$html = $html . "<tr> ";
		$html = $html . "<td style='text-align: left'><b>" . $res_akun['nama'] . "</b> </td>";
		$html = $html . "<td style='text-align: left'> </td>";
		$html = $html . "<tr> ";


		/* AKTIVA LANCAR */
		$res_akun = $this->db->query("SELECT * from acc_akun where kode='11'")->row_array();
		$html = $html . "<tr> ";
		$html = $html . "<td style='text-align: left'><b>" . $res_akun['nama'] . "</b> </td>";
		$html = $html . "<td style='text-align: left'> </td>";
		$html = $html . "<tr> ";
		$nama_sub = $res_akun['nama'];

		/* AKTIVA LANCAR DETAIL*/
		$res_akun = $this->db->query("select * from acc_akun where LENGTH(kode)=3  and (kode like '11%')   order by kode ")->result_array();
		$sub_total = 0;

		foreach ($res_akun as $key => $akun) {

			$query = "SELECT sum(debet-kredit)as jumlah FROM acc_jurnal_ht a inner join acc_jurnal_dt b 
			on a.id=b.jurnal_id inner join acc_akun c on b.acc_akun_id=c.id
			where c.kode like '" . $akun['kode'] . "%' 
			and a.tanggal <= '" . $tanggal_akhir . "'  ;";

			$resJumlah  = $this->db->query($query)->row_array();
			$jumlah = (!empty($resJumlah['jumlah'])) ? $resJumlah['jumlah'] : 0;
			$sub_total = $sub_total + $jumlah;
			$total_aktiva = $total_aktiva + $jumlah;
			$html = $html . "<tr> ";
			$html = $html . "<td style='text-align: left'>" . $akun['nama'] . " </td>";
			$html = $html . "<td style='text-align: right'>" . $this->format_number_report($jumlah) . " </td>";
			$html = $html . "</tr>";
		}
		$html = $html . "<tr> ";
		$html = $html . "<td style='text-align: left'><b>Jumlah " . $nama_sub . " </b></td>";
		$html = $html . "<td style='text-align: right'>" . $this->format_number_report($sub_total) . " </td>";
		$html = $html . "</tr>";

		/* AKTIVA TIDAK LANCAR*/
		$res_akun = $this->db->query("SELECT * from acc_akun where kode='12'")->row_array();
		$html = $html . "<tr> ";
		$html = $html . "<td style='text-align: left'><b>" . $res_akun['nama'] . "</b> </td>";
		$html = $html . "<td style='text-align: left'> </td>";
		$html = $html . "<tr> ";
		$nama_sub = $res_akun['nama'];
		/* AKTIVA TIDAK LANCAR DETAIL */
		$res_akun = $this->db->query("select * from acc_akun where LENGTH(kode)=3  and	 (kode like '12%')   order by kode ")->result_array();
		$sub_total = 0;

		foreach ($res_akun as $key => $akun) {
			$query = "SELECT sum(debet-kredit)as jumlah FROM acc_jurnal_ht a inner join acc_jurnal_dt b 
			on a.id=b.jurnal_id inner join acc_akun c on b.acc_akun_id=c.id
			where c.kode like '" . $akun['kode'] . "%' 
			and a.tanggal <= '" . $tanggal_akhir . "'  ;";

			$resJumlah  = $this->db->query($query)->row_array();
			$jumlah = (!empty($resJumlah['jumlah'])) ? $resJumlah['jumlah'] : 0;
			$sub_total = $sub_total + $jumlah;
			$total_aktiva = $total_aktiva + $jumlah;
			$html = $html . "<tr> ";
			$html = $html . "<td style='text-align: left'>" . $akun['nama'] . " </td>";
			$html = $html . "<td style='text-align: right'>" . $this->format_number_report($jumlah) . " </td>";
			$html = $html . "</tr>";
		}
		$html = $html . "<tr> ";
		$html = $html . "<td style='text-align: left'><b>Jumlah " . $nama_sub . " </b></td>";
		$html = $html . "<td style='text-align: right'>" . $this->format_number_report($sub_total) . " </td>";
		$html = $html . "</tr>";

		$html = $html . "<tr>";
		$html = $html . "<td style='text-align: center'><b>TOTAL AKTIVA</b></td>";
		$html = $html . "<td style='text-align: right'><b>" . $this->format_number_report($total_aktiva) . " </b></td>";
		$html = $html . "</tr>";

		/* PASIVA */
		$html = $html . "<tr> ";
		$html = $html . "<td style='text-align: left'><b>LIABILITAS DAN EKUITAS</b> </td>";
		$html = $html . "<td style='text-align: left'> </td>";
		$html = $html . "<tr> ";


		$res_akun = $this->db->query("SELECT * from acc_akun	where kode='2'")->row_array();
		$html = $html . "<tr> ";
		$html = $html . "<td style='text-align: left'><b>" . $res_akun['nama'] . "</b> </td>";
		$html = $html . "<td style='text-align: left'> </td>";
		$html = $html . "<tr> ";


		/* LIABILITAS JANGKA PENDEK */
		$res_akun = $this->db->query("SELECT * from acc_akun where kode='21'")->row_array();
		$html = $html . "<tr> ";
		$html = $html . "<td style='text-align: left'><b>" . $res_akun['nama'] . "</b> </td>";
		$html = $html . "<td style='text-align: left'> </td>";
		$html = $html . "<tr> ";
		$nama_sub = $res_akun['nama'];

		/* LIABILITAS DETAIL*/
		$res_akun = $this->db->query("select * from acc_akun where LENGTH(kode)=3  and (kode like '21%')   order by kode ")->result_array();
		$sub_total = 0;
		$total_liabilities = 0;
		$total_pasiva = 0;
		foreach ($res_akun as $key => $akun) {
			$query = "SELECT sum(debet-kredit)as jumlah FROM acc_jurnal_ht a inner join acc_jurnal_dt b 
			on a.id=b.jurnal_id inner join acc_akun c on b.acc_akun_id=c.id
			where c.kode like '" . $akun['kode'] . "%' 
			and a.tanggal <= '" . $tanggal_akhir . "'  ;";

			$resJumlah  = $this->db->query($query)->row_array();
			$jumlah = (!empty($resJumlah['jumlah'])) ? $resJumlah['jumlah'] * -1 : 0;
			$sub_total = $sub_total + $jumlah;
			$total_liabilities = $total_liabilities + $jumlah;
			$total_pasiva = $total_pasiva + $jumlah;
			$html = $html . "<tr> ";
			$html = $html . "<td style='text-align: left'>" . $akun['nama'] . " </td>";
			$html = $html . "<td style='text-align: right'>" . $this->format_number_report($jumlah) . " </td>";
			$html = $html . "</tr>";
		}
		$html = $html . "<tr> ";
		$html = $html . "<td style='text-align: left'><b>Jumlah " . $nama_sub . " </b></td>";
		$html = $html . "<td style='text-align: right'>" . $this->format_number_report($sub_total) . " </td>";
		$html = $html . "</tr>";

		/* LIABILITAS JANGKA panjang */
		$res_akun = $this->db->query("SELECT * from acc_akun where kode='22'")->row_array();
		$html = $html . "<tr> ";
		$html = $html . "<td style='text-align: left'><b>" . $res_akun['nama'] . "</b> </td>";
		$html = $html . "<td style='text-align: left'> </td>";
		$html = $html . "<tr> ";
		$nama_sub = $res_akun['nama'];

		/* LIABILITAS DETAIL*/
		$res_akun = $this->db->query("select * from acc_akun where LENGTH(kode)=3  and (kode like '22%')   order by kode ")->result_array();
		$sub_total = 0;
		$total_liabilities = 0;
		$total_pasiva = 0;
		foreach ($res_akun as $key => $akun) {
			$query = "SELECT sum(debet-kredit)as jumlah FROM acc_jurnal_ht a inner join acc_jurnal_dt b 
			on a.id=b.jurnal_id inner join acc_akun c on b.acc_akun_id=c.id
			where c.kode like '" . $akun['kode'] . "%' 
			and a.tanggal <= '" . $tanggal_akhir . "'  ;";

			$resJumlah  = $this->db->query($query)->row_array();
			$jumlah = (!empty($resJumlah['jumlah'])) ? $resJumlah['jumlah'] * -1 : 0;
			$sub_total = $sub_total + $jumlah;
			$total_liabilities = $total_liabilities + $jumlah;
			$total_pasiva = $total_pasiva + $jumlah;
			$html = $html . "<tr> ";
			$html = $html . "<td style='text-align: left'>" . $akun['nama'] . " </td>";
			$html = $html . "<td style='text-align: right'>" . $this->format_number_report($jumlah) . " </td>";
			$html = $html . "</tr>";
		}
		$html = $html . "<tr> ";
		$html = $html . "<td style='text-align: left'><b>Jumlah " . $nama_sub . " </b></td>";
		$html = $html . "<td style='text-align: right'>" . $this->format_number_report($sub_total) . " </td>";
		$html = $html . "</tr>";

		$html = $html . "<tr>";
		$html = $html . "<td style='text-align: left'><b>JUMLAH LIABILITAS</b></td>";
		$html = $html . "<td style='text-align: right'><b>" . $this->format_number_report($total_aktiva) . " </b></td>";
		$html = $html . "</tr>";

		/* MODAL */
		$res_akun = $this->db->query("SELECT * from acc_akun	where kode='3'")->row_array();
		$html = $html . "<tr> ";
		$html = $html . "<td style='text-align: left'><b>" . $res_akun['nama'] . "</b> </td>";
		$html = $html . "<td style='text-align: left'> </td>";
		$html = $html . "<tr> ";


		/* LIABILITAS JANGKA PENDEK */
		$res_akun = $this->db->query("SELECT * from acc_akun where kode='310'")->row_array();
		$html = $html . "<tr> ";
		$html = $html . "<td style='text-align: left'><b>" . $res_akun['nama'] . "</b> </td>";
		$html = $html . "<td style='text-align: left'> </td>";
		$html = $html . "<tr> ";
		$nama_sub = $res_akun['nama'];

		/* LIABILITAS DETAIL*/
		$res_akun = $this->db->query("select * from acc_akun where LENGTH(kode)=3  and (kode like '310%')   order by kode ")->result_array();
		$sub_total = 0;
		$total_liabilities = 0;
		$total_pasiva = 0;
		foreach ($res_akun as $key => $akun) {
			$query = "SELECT sum(debet-kredit)as jumlah FROM acc_jurnal_ht a inner join acc_jurnal_dt b 
			on a.id=b.jurnal_id inner join acc_akun c on b.acc_akun_id=c.id
			where c.kode like '" . $akun['kode'] . "%' 
			and a.tanggal <= '" . $tanggal_akhir . "'  ;";

			$resJumlah  = $this->db->query($query)->row_array();
			$jumlah = (!empty($resJumlah['jumlah'])) ? $resJumlah['jumlah'] * -1 : 0;
			$sub_total = $sub_total + $jumlah;
			$total_liabilities = $total_liabilities + $jumlah;
			$total_pasiva = $total_pasiva + $jumlah;
			$html = $html . "<tr> ";
			$html = $html . "<td style='text-align: left'>" . $akun['nama'] . " </td>";
			$html = $html . "<td style='text-align: right'>" . $this->format_number_report($jumlah) . " </td>";
			$html = $html . "</tr>";
		}
		$html = $html . "<tr> ";
		$html = $html . "<td style='text-align: left'><b>Jumlah " . $nama_sub . " </b></td>";
		$html = $html . "<td style='text-align: right'>" . $this->format_number_report($sub_total) . " </td>";
		$html = $html . "</tr>";

		/* MODAL LABA RUGI DITAHAN */
		$res_akun = $this->db->query("SELECT * from acc_akun where kode='311'")->row_array();
		$html = $html . "<tr> ";
		$html = $html . "<td style='text-align: left'><b>" . $res_akun['nama'] . "</b> </td>";
		$html = $html . "<td style='text-align: left'> </td>";
		$html = $html . "<tr> ";
		$nama_sub = $res_akun['nama'];

		/* LIABILITAS DETAIL*/
		$res_akun = $this->db->query("select * from acc_akun where LENGTH(kode)=3  and (kode like '311%')   order by kode ")->result_array();
		$sub_total = 0;
		$total_liabilities = 0;
		$total_pasiva = 0;
		foreach ($res_akun as $key => $akun) {
			$query = "SELECT sum(debet-kredit)as jumlah FROM acc_jurnal_ht a inner join acc_jurnal_dt b 
			on a.id=b.jurnal_id inner join acc_akun c on b.acc_akun_id=c.id
			where c.kode like '" . $akun['kode'] . "%' 
			and a.tanggal <= '" . $tanggal_akhir . "'  ;";

			$resJumlah  = $this->db->query($query)->row_array();
			$jumlah = (!empty($resJumlah['jumlah'])) ? $resJumlah['jumlah'] * -1 : 0;
			$sub_total = $sub_total + $jumlah;
			$total_liabilities = $total_liabilities + $jumlah;
			$total_pasiva = $total_pasiva + $jumlah;
			$html = $html . "<tr> ";
			$html = $html . "<td style='text-align: left'>" . $akun['nama'] . " </td>";
			$html = $html . "<td style='text-align: right'>" . $this->format_number_report($jumlah) . " </td>";
			$html = $html . "</tr>";
		}
		$html = $html . "<tr> ";
		$html = $html . "<td style='text-align: left'><b>Jumlah " . $nama_sub . " </b></td>";
		$html = $html . "<td style='text-align: right'>" . $this->format_number_report($sub_total) . " </td>";
		$html = $html . "</tr>";

		$html = $html . "<tr>";
		$html = $html . "<td style='text-align: left'><b>JUMLAH LIABILITAS</b></td>";
		$html = $html . "<td style='text-align: right'><b>" . $this->format_number_report($total_aktiva) . " </b></td>";
		$html = $html . "</tr>";


		$html = $html . "<tr>";
		$html = $html . "<td style='text-align: left'><b>TOTAL HUTANG & MODAL</b></td>";
		$html = $html . "<td style='text-align: right'><b>" . $this->format_number_report($total_aktiva) . " </b></td>";
		$html = $html . "</tr>";
		$html = $html . "</table>";



		if ($format_laporan == 'xls') {
			$reader = new \PhpOffice\PhpSpreadsheet\Reader\Html;
			$spreadsheet = $reader->loadFromString($html);
			// $reader->setSheetIndex(1);
			//$spreadhseet = $reader->loadFromString($secondHtmlString, $spreadsheet);
			$objWriter = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
			header("Pragma: public");
			header("Expires: 0");
			header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
			header("Content-Type: application/force-download");
			header("Content-Type: application/octet-stream");
			header("Content-Type: application/download");
			header("Content-Disposition: attachment;filename=test.xlsx");
			header("Content-Transfer-Encoding: binary ");

			ob_end_clean();
			ob_start();
			$objWriter->save('php://output');
		} else if ($format_laporan == 'view') {
			echo $html;
		} else {
			$filename = 'report_' . time();
			$this->pdfgenerator->generate($html, $filename, true, 'A4', 'landscape');
		}
	}
	function laporan_laba_rugi_post()
	{

		$versi_laporan =  $this->post('versi_laporan', true);
		$tipe_laporan =  $this->post('tipe_laporan', true);
		if ($versi_laporan == 'v1') {
			$this->laporan_laba_rugi_v1($tipe_laporan);
		} else if ($versi_laporan == 'v2') {
			$this->laporan_laba_rugi_v2($tipe_laporan);
		} else {
			$this->laporan_laba_rugi_v2($tipe_laporan);
		}
	}
	function laporan_laba_rugi_inti_post()
	{
		$versi_laporan =  $this->post('versi_laporan', true);
		if ($versi_laporan == 'v1') {
			$this->laporan_laba_rugi_inti_v1();
		} else if ($versi_laporan == 'v2') {
			$this->laporan_laba_rugi_inti_v2();
		} else {
			$this->laporan_laba_rugi_inti_v2();
		}
	}
	function laporan_laba_rugi_v1($tipe_laporan)
	{
		error_reporting(0);
		$lokasi_id     = $this->post('lokasi_id', true);
		$tanggal_mulai =  $this->post('tgl_mulai', true);
		$tanggal_akhir =  $this->post('tgl_akhir', true);
		$format_laporan =  $this->post('format_laporan', true);
		$judulLokasi = 'Semua';

		/* Ambil AKUN */
		$queryAkun5 = "select * from acc_akun where  (kode like '5%')  order by kode ";
		/* Ambil AKUN */
		$queryAkun6 = "select * from acc_akun where   (kode like '6%')     order by kode ";
		/* Ambil AKUN */
		$queryAkun7 = "select * from acc_akun	where  (kode like '7%')    order by kode ";
		/* Ambil AKUN */
		$queryAkun8 = "select * from acc_akun	where  (kode like '8%')    order by kode ";
		/* Ambil AKUN */
		$queryAkun9 = "select * from acc_akun where (kode like '9%')  order by kode ";


		$res = array();
		// var_dump($results);
		$this->load->helper("terbilang");
		$this->load->helper("antech_helper");
		$this->load->library('pdfgenerator');
		if ($format_laporan == 'view') {
			$html = get_header_report_v2();
		} else {
			$html = get_header_pdf_report();
		}
		$html = $html . '
		<div class="row">
  <div class="span12">
	<br>
	<div class="kop-print">
    <div class="kop-nama">PT. ANNAJAH TECHNOLOGY INDONESIA</div>
    <div class="kop-info"> Komplek Vila Sehati B12 , Tapos - Depok</div>
    <div class="kop-info">Telp : 081387373939</div>
  </div>
	  <hr class="kop-print-hr">
  </div>
  </div>
  <h3 class="title">LAPORAN LABA RUGI</h3>
<h3>Periode: ' . $tanggal_mulai . ' s/d ' . $tanggal_akhir . '  </h3>';
		$html = $html . "
<table   border='1' width='100%' style='border-collapse: collapse;'>
<thead>
 </thead>
";


		/* Note: Utk Plasma Nilai akun pembelian Plasma di Pindah menjadi Penjualan Plasma
		3407 - 6310131 - Pembelian TBS Plasma
		3708 - 5110207 - Penjualan TBS Plasma Ke PT. XXX
		*/
		// $queryPembelianTBSPlasma = "SELECT b.acc_akun_id,sum(debet-kredit)as jumlah FROM acc_jurnal_ht a inner join acc_jurnal_dt b 
		// on a.id=b.jurnal_id
		// inner join gbm_organisasi c on b.lokasi_id=c.id
		// where b.acc_akun_id=3407 
		// and (a.tanggal >= '" . $tanggal_mulai . "' and a.tanggal <= '" . $tanggal_akhir . "' )
		// and  c.kode  in('DPHO','DPAM')
		// group by b.acc_akun_id ;";
		// $resJumlahPembelianTBSPlasma  = $this->db->query($queryPembelianTBSPlasma)->row_array();
		// $nilaiPembelianTBSHOMill =  (!empty($resJumlahPembelianTBSPlasma['jumlah'])) ? ($resJumlahPembelianTBSPlasma['jumlah']) : 0;

		/* QTy TBS */
		// if ($tipe_laporan == 'v3') {

		// 	$resJumlah = $this->db->query("SELECT SUM(jum_kg_pks)AS jumlah,SUM(jum_ha)AS ha FROM est_produksi_panen_ht a
		// 	INNER JOIN est_produksi_panen_dt b ON a.id=b.produksi_panen_id
		// 	INNER JOIN gbm_organisasi c ON a.divisi_id=c.id
		// 	INNER JOIN gbm_organisasi d ON c.parent_id=d.id 
		// 	INNER JOIN gbm_organisasi e ON d.parent_id=e.id 
		// 	where e.kode='SBME' 
		// 	and a.tanggal>='" . $tanggal_mulai . "' and a.tanggal<='" . $tanggal_akhir . "' ")->row_array();

		// 	$jumlah = (!empty($resJumlah['jumlah'])) ? ($resJumlah['jumlah']) : 0;
		// } else {
		// 	$query1 = "SELECT sum(qty_real) as jumlah FROM acc_sales_invoice a 
		// 	INNER JOIN sls_kontrak b ON a.sls_kontrak_id=b.id
		// 	INNER JOIN  inv_item c ON b.produk_id=c.id
		// 	WHERE c.tipe_produk='TBS'  
		// 	and (a.tanggal >= '" . $tanggal_mulai . "' and a.tanggal <= '" . $tanggal_akhir . "' ) ;";
		// 	$resJumlah  = $this->db->query($query1)->row_array();
		// 	$jumlah = (!empty($resJumlah['jumlah'])) ? ($resJumlah['jumlah']) : 0;
		// }
		// $html = $html . "<tr>";
		// $html = $html . "<td style='text-align: left'></td>";
		// $html = $html . "<td style='text-align: left'>Quantity TBS </td>";
		// $html = $html . "<td style='text-align: right'><b>" . $this->format_number_report($jumlah) . " </b></td>";
		// $html = $html . "</tr>";


		/* QTy CPO */
		// if ($tipe_laporan == 'v3') {
		// 	$jumlah = 0;
		// } else {
		// 	$query1 = "SELECT sum(qty_real) as jumlah FROM acc_sales_invoice a 
		// 	INNER JOIN sls_kontrak b ON a.sls_kontrak_id=b.id
		// 	INNER JOIN  inv_item c ON b.produk_id=c.id
		// 	WHERE c.tipe_produk='CPO'  
		// 	and (a.tanggal >= '" . $tanggal_mulai . "' and a.tanggal <= '" . $tanggal_akhir . "' ) ;";
		// 	$resJumlah  = $this->db->query($query1)->row_array();
		// 	$jumlah = (!empty($resJumlah['jumlah'])) ? ($resJumlah['jumlah']) : 0;
		// }

		// $html = $html . "<tr>";
		// $html = $html . "<td style='text-align: left'></td>";
		// $html = $html . "<td style='text-align: left'>Quantity CPO </td>";
		// $html = $html . "<td style='text-align: right'><b>" . $this->format_number_report($jumlah) . " </b></td>";
		// $html = $html . "</tr>";

		/* QTy PK */
		// if ($tipe_laporan == 'v3') {
		// 	$jumlah = 0;
		// } else {
		// 	$query1 = "SELECT sum(qty_real) as jumlah FROM acc_sales_invoice a 
		// 	INNER JOIN sls_kontrak b ON a.sls_kontrak_id=b.id
		// 	INNER JOIN  inv_item c ON b.produk_id=c.id
		// 	WHERE c.tipe_produk='PK'  
		// 	and (a.tanggal >= '" . $tanggal_mulai . "' and a.tanggal <= '" . $tanggal_akhir . "' ) ;";
		// 	$resJumlah  = $this->db->query($query1)->row_array();
		// 	$jumlah = (!empty($resJumlah['jumlah'])) ? ($resJumlah['jumlah']) : 0;
		// }
		// $html = $html . "<tr>";
		// $html = $html . "<td style='text-align: left'></td>";
		// $html = $html . "<td style='text-align: left'>Quantity PK </td>";
		// $html = $html . "<td style='text-align: right'><b>" . $this->format_number_report($jumlah) . " </b></td>";
		// $html = $html . "</tr>";

		/* QTy PK SHELL */
		// if ($tipe_laporan == 'v3') {
		// 	$jumlah = 0;
		// } else {
		// 	$query1 = "SELECT sum(qty_real) as jumlah FROM acc_sales_invoice a 
		// 	INNER JOIN sls_kontrak b ON a.sls_kontrak_id=b.id
		// 	INNER JOIN  inv_item c ON b.produk_id=c.id
		// 	WHERE c.tipe_produk='CANGKANG'  
		// 	and (a.tanggal >= '" . $tanggal_mulai . "' and a.tanggal <= '" . $tanggal_akhir . "' ) ;";
		// 	$resJumlah  = $this->db->query($query1)->row_array();
		// 	$jumlah = (!empty($resJumlah['jumlah'])) ? ($resJumlah['jumlah']) : 0;
		// }
		// $html = $html . "<tr>";
		// $html = $html . "<td style='text-align: left'></td>";
		// $html = $html . "<td style='text-align: left'>Quantity PK Shell</td>";
		// $html = $html . "<td style='text-align: right'><b>" . $this->format_number_report($jumlah) . " </b></td>";
		// $html = $html . "</tr>";
		$nourut = 0;
		/* Pendapatan Kepala 5 */
		$grandtotal = 0;
		$total_kepala5 = 0;
		$total_kepala6 = 0;
		$total_kepala7 = 0;
		$total_kepala8 = 0;
		$total_kepala9 = 0;
		$totalBiaya = 0;
		$totalLabaRugi = 0;
		$sub_total = 0;
		$akun_kepala5   = $this->db->query($queryAkun5)->result_array();
		foreach ($akun_kepala5 as $key => $akun) {
			$nourut = $nourut + 1;
			$total = 0;
			$html = $html . "<tr> ";
			if ($akun['is_transaksi_akun'] == 1) {
				$html = $html . "<td style='text-align: left'>" . $akun['kode'] . " </td>";
				$html = $html . "<td style='text-align: left'>" . $akun['nama'] . " </td>";
				if ($lokasi_id) {
					$query1 = "SELECT b.acc_akun_id,sum(debet-kredit)as jumlah FROM acc_jurnal_ht a inner join acc_jurnal_dt b 
						on a.id=b.jurnal_id
						where b.acc_akun_id=" . $akun['id'] . " 
						and (a.tanggal >= '" . $tanggal_mulai . "' and a.tanggal <= '" . $tanggal_akhir . "' )
						and  b.lokasi_id=" . $lokasi_id . "
						group by b.acc_akun_id ;";
				} else {
					if ($tipe_laporan == 'v1') {
						$query1 = "SELECT b.acc_akun_id,sum(debet-kredit)as jumlah FROM acc_jurnal_ht a inner join acc_jurnal_dt b 
						on a.id=b.jurnal_id
						where b.acc_akun_id=" . $akun['id'] . " 
						and (a.tanggal >= '" . $tanggal_mulai . "' and a.tanggal <= '" . $tanggal_akhir . "' )
						group by b.acc_akun_id ;";
					} else if ($tipe_laporan == 'v2') {
						$query1 = "SELECT b.acc_akun_id,sum(debet-kredit)as jumlah FROM acc_jurnal_ht a inner join acc_jurnal_dt b 
						on a.id=b.jurnal_id
						inner join gbm_organisasi c on b.lokasi_id=c.id
						where b.acc_akun_id=" . $akun['id'] . " 
						and (a.tanggal >= '" . $tanggal_mulai . "' and a.tanggal <= '" . $tanggal_akhir . "' )
						and  c.kode not in('SBME')
						group by b.acc_akun_id ;";
					} else if ($tipe_laporan == 'v3') {
						$query1 = "SELECT b.acc_akun_id,sum(debet-kredit)as jumlah FROM acc_jurnal_ht a inner join acc_jurnal_dt b 
							on a.id=b.jurnal_id
							inner join gbm_organisasi c on b.lokasi_id=c.id
							where b.acc_akun_id=" . $akun['id'] . " 
							and (a.tanggal >= '" . $tanggal_mulai . "' and a.tanggal <= '" . $tanggal_akhir . "' )
							and  c.kode  in('SBME')
							group by b.acc_akun_id ;";
					}
				}

				$resJumlah  = $this->db->query($query1)->row_array();
				$jumlah = (!empty($resJumlah['jumlah'])) ? ($resJumlah['jumlah'] * -1) : 0;
				if ($tipe_laporan == 'v3') {
					/* Note:
						    Utk Plasma, Nilai akun pembelian Plasma di Pindah menjadi Penjualan Plasma
							3407 - 6310131 - Pembelian TBS Plasma
							3708 - 5110207 - Penjualan TBS Plasma Ke PT. XXX
						   JIka akun penjualan TBS plasma maka dijumlahkan Nilai Pembelian TBS HO/MILL

						*/
					if ($akun['id'] == '3708' || $akun['id'] == 3708) {
						$jumlah = $jumlah + $nilaiPembelianTBSHOMill;
					}
				}

				$total = $total + $jumlah;
				$sub_total = $sub_total + $jumlah;
				$grandtotal = $grandtotal + $jumlah;
				$html = $html . "<td style='text-align: right'>" . $this->format_number_report($jumlah) . " </td>";
			} else {
				$html = $html . "<td style='text-align: left'><b>" . $akun['kode'] . "</b> </td>";
				$html = $html . "<td style='text-align: left'><b>" . $akun['nama'] . "</b> </td>";
				$html = $html . "<td style='text-align: right'> </td>";
			}


			$html = $html . "</tr>";
		}

		$html = $html . "<tr>";
		$html = $html . "<td style='text-align: center'> </td>";
		$html = $html . "<td style='text-align: left'><b>TOTAL PENDAPATAN</b></td>";

		$html = $html . "<td style='text-align: right'><b>" . $this->format_number_report($sub_total) . " </b></td>";
		$html = $html . "</tr>";

		$total_kepala5 = $sub_total;

		/* KEPALA 6 */
		$akun_kepala6   = $this->db->query($queryAkun6)->result_array();
		$sub_total = 0;
		foreach ($akun_kepala6 as $key => $akun) {
			$nourut = $nourut + 1;
			$total = 0;
			$html = $html . "<tr> ";

			if ($akun['is_transaksi_akun'] == 1) {
				$html = $html . "<td style='text-align: left'>" . $akun['kode'] . " </td>";
				$html = $html . "<td style='text-align: left'>" . $akun['nama'] . " </td>";
				if ($lokasi_id) {
					$query1 = "SELECT b.acc_akun_id,sum(debet-kredit)as jumlah FROM acc_jurnal_ht a inner join acc_jurnal_dt b 
					on a.id=b.jurnal_id
					where b.acc_akun_id=" . $akun['id'] . " 
					and (a.tanggal >= '" . $tanggal_mulai . "' and a.tanggal <= '" . $tanggal_akhir . "' )
					and  b.lokasi_id=" . $lokasi_id . "
					group by b.acc_akun_id ;";
				} else {
					if ($tipe_laporan == 'v1') {
						$query1 = "SELECT b.acc_akun_id,sum(debet-kredit)as jumlah FROM acc_jurnal_ht a inner join acc_jurnal_dt b 
						on a.id=b.jurnal_id
						where b.acc_akun_id=" . $akun['id'] . " 
						and (a.tanggal >= '" . $tanggal_mulai . "' and a.tanggal <= '" . $tanggal_akhir . "' )
						group by b.acc_akun_id ;";
					} else if ($tipe_laporan == 'v2') {
						$query1 = "SELECT b.acc_akun_id,sum(debet-kredit)as jumlah FROM acc_jurnal_ht a inner join acc_jurnal_dt b 
						on a.id=b.jurnal_id
						inner join gbm_organisasi c on b.lokasi_id=c.id
						where b.acc_akun_id=" . $akun['id'] . " 
						and (a.tanggal >= '" . $tanggal_mulai . "' and a.tanggal <= '" . $tanggal_akhir . "' )
						and  c.kode  not in('SBME')
						group by b.acc_akun_id ;";
					} else if ($tipe_laporan == 'v3') {
						$query1 = "SELECT b.acc_akun_id,sum(debet-kredit)as jumlah FROM acc_jurnal_ht a inner join acc_jurnal_dt b 
						on a.id=b.jurnal_id
						inner join gbm_organisasi c on b.lokasi_id=c.id
						where b.acc_akun_id=" . $akun['id'] . " 
						and (a.tanggal >= '" . $tanggal_mulai . "' and a.tanggal <= '" . $tanggal_akhir . "' )
						and  c.kode   in('SBME')
						group by b.acc_akun_id ;";
					}
				}


				$resJumlah  = $this->db->query($query1)->row_array();
				$jumlah = (!empty($resJumlah['jumlah'])) ? ($resJumlah['jumlah']) : 0;
				// if ($tipe_laporan == 'v3') {
				// 	// JIka akun Pembelian TBS plasma maka di nolkan saja
				// 	if ($akun['id']=='3407' || $akun['id']=3407){
				// 		$jumlah=0;
				// 	}
				// }

				$total = $total + $jumlah;
				$sub_total = $sub_total + $jumlah;
				$grandtotal = $grandtotal + $jumlah;
				$html = $html . "<td style='text-align: right'>" . $this->format_number_report($jumlah) . " </td>";
			} else {
				$html = $html . "<td style='text-align: left'><b>" . $akun['kode'] . "</b> </td>";
				$html = $html . "<td style='text-align: left'><b>" . $akun['nama'] . "</b> </td>";

				$html = $html . "<td style='text-align: right'> </td>";
			}
		}
		$total_kepala6 = $sub_total;
		$html = $html . "<tr>";
		$html = $html . "<td style='text-align: center'> </td>";
		$html = $html . "<td style='text-align: left'><b>TOTAL BIAYA LANGSUNG</b></td>";
		$html = $html . "<td style='text-align: right'><b>" . $this->format_number_report($sub_total) . " </b></td>";
		$html = $html . "</tr>";

		/* KEPALA 7 */
		$akun_kepala7  = $this->db->query($queryAkun7)->result_array();
		$sub_total = 0;
		foreach ($akun_kepala7 as $key => $akun) {
			$nourut = $nourut + 1;
			$total = 0;
			$html = $html . "<tr> ";

			if ($akun['is_transaksi_akun'] == 1) {
				$html = $html . "<td style='text-align: left'>" . $akun['kode'] . " </td>";
				$html = $html . "<td style='text-align: left'>" . $akun['nama'] . " </td>";

				if ($lokasi_id) {
					$query1 = "SELECT b.acc_akun_id,sum(debet-kredit)as jumlah FROM acc_jurnal_ht a inner join acc_jurnal_dt b 
					on a.id=b.jurnal_id
					where b.acc_akun_id=" . $akun['id'] . " 
					and (a.tanggal >= '" . $tanggal_mulai . "' and a.tanggal <= '" . $tanggal_akhir . "' )
					and  b.lokasi_id=" . $lokasi_id . "
					group by b.acc_akun_id ;";
				} else {
					if ($tipe_laporan == 'v1') {
						$query1 = "SELECT b.acc_akun_id,sum(debet-kredit)as jumlah FROM acc_jurnal_ht a inner join acc_jurnal_dt b 
						on a.id=b.jurnal_id
						where b.acc_akun_id=" . $akun['id'] . " 
						and (a.tanggal >= '" . $tanggal_mulai . "' and a.tanggal <= '" . $tanggal_akhir . "' )
						group by b.acc_akun_id ;";
					} else 	if ($tipe_laporan == 'v2') {
						$query1 = "SELECT b.acc_akun_id,sum(debet-kredit)as jumlah FROM acc_jurnal_ht a inner join acc_jurnal_dt b 
						on a.id=b.jurnal_id
						inner join gbm_organisasi c on b.lokasi_id=c.id
						where b.acc_akun_id=" . $akun['id'] . " 
						and (a.tanggal >= '" . $tanggal_mulai . "' and a.tanggal <= '" . $tanggal_akhir . "' )
						and  c.kode not  in('SBME')
						group by b.acc_akun_id ;";
					} else 	if ($tipe_laporan == 'v3') {
						$query1 = "SELECT b.acc_akun_id,sum(debet-kredit)as jumlah FROM acc_jurnal_ht a inner join acc_jurnal_dt b 
						on a.id=b.jurnal_id
						inner join gbm_organisasi c on b.lokasi_id=c.id
						where b.acc_akun_id=" . $akun['id'] . " 
						and (a.tanggal >= '" . $tanggal_mulai . "' and a.tanggal <= '" . $tanggal_akhir . "' )
						and  c.kode  in('SBME')
						group by b.acc_akun_id ;";
					}
				}


				$resJumlah  = $this->db->query($query1)->row_array();
				$jumlah = (!empty($resJumlah['jumlah'])) ? ($resJumlah['jumlah']) : 0;


				$total = $total + $jumlah;
				$sub_total = $sub_total + $jumlah;
				$grandtotal = $grandtotal + $jumlah;
				$html = $html . "<td style='text-align: right'>" . $this->format_number_report($jumlah) . " </td>";
			} else {
				$html = $html . "<td style='text-align: left'><b>" . $akun['kode'] . "</b> </td>";
				$html = $html . "<td style='text-align: left'><b>" . $akun['nama'] . "</b> </td>";

				$html = $html . "<td style='text-align: right'> </td>";
			}
		}
		$total_kepala7 = $sub_total;
		$html = $html . "<tr>";
		$html = $html . "<td style='text-align: center'> </td>";
		$html = $html . "<td style='text-align: left'><b>TOTAL BIAYA TAK LANGSUNG</b></td>";
		$html = $html . "<td style='text-align: right'><b>" . $this->format_number_report($sub_total) . " </b></td>";
		$html = $html . "</tr>";

		/* KEPALA 8 */
		$akun_kepala8  = $this->db->query($queryAkun8)->result_array();
		$sub_total = 0;
		foreach ($akun_kepala8 as $key => $akun) {
			$nourut = $nourut + 1;
			$total = 0;
			$html = $html . "<tr> ";

			if ($akun['is_transaksi_akun'] == 1) {
				$html = $html . "<td style='text-align: left'>" . $akun['kode'] . " </td>";
				$html = $html . "<td style='text-align: left'>" . $akun['nama'] . " </td>";

				if ($lokasi_id) {
					$query1 = "SELECT b.acc_akun_id,sum(debet-kredit)as jumlah FROM acc_jurnal_ht a inner join acc_jurnal_dt b 
					on a.id=b.jurnal_id
					where b.acc_akun_id=" . $akun['id'] . " 
					and (a.tanggal >= '" . $tanggal_mulai . "' and a.tanggal <= '" . $tanggal_akhir . "' )
					and  b.lokasi_id=" . $lokasi_id . "
					group by b.acc_akun_id ;";
				} else {
					if ($tipe_laporan == 'v1') {
						$query1 = "SELECT b.acc_akun_id,sum(debet-kredit)as jumlah FROM acc_jurnal_ht a inner join acc_jurnal_dt b 
						on a.id=b.jurnal_id
						where b.acc_akun_id=" . $akun['id'] . " 
						and (a.tanggal >= '" . $tanggal_mulai . "' and a.tanggal <= '" . $tanggal_akhir . "' )
						group by b.acc_akun_id ;";
					} else 	if ($tipe_laporan == 'v2') {
						$query1 = "SELECT b.acc_akun_id,sum(debet-kredit)as jumlah FROM acc_jurnal_ht a inner join acc_jurnal_dt b 
						on a.id=b.jurnal_id
						inner join gbm_organisasi c on b.lokasi_id=c.id
						where b.acc_akun_id=" . $akun['id'] . " 
						and (a.tanggal >= '" . $tanggal_mulai . "' and a.tanggal <= '" . $tanggal_akhir . "' )
						and  c.kode not  in('SBME')
						group by b.acc_akun_id ;";
					} else 	if ($tipe_laporan == 'v3') {
						$query1 = "SELECT b.acc_akun_id,sum(debet-kredit)as jumlah FROM acc_jurnal_ht a inner join acc_jurnal_dt b 
						on a.id=b.jurnal_id
						inner join gbm_organisasi c on b.lokasi_id=c.id
						where b.acc_akun_id=" . $akun['id'] . " 
						and (a.tanggal >= '" . $tanggal_mulai . "' and a.tanggal <= '" . $tanggal_akhir . "' )
						and  c.kode  in('SBME')
						group by b.acc_akun_id ;";
					}
				}


				$resJumlah  = $this->db->query($query1)->row_array();
				$jumlah = (!empty($resJumlah['jumlah'])) ? ($resJumlah['jumlah'] * -1) : 0;

				$total = $total + $jumlah;
				$sub_total = $sub_total + $jumlah;
				$grandtotal = $grandtotal + $jumlah;
				$html = $html . "<td style='text-align: right'>" . $this->format_number_report($jumlah) . " </td>";
			} else {
				$html = $html . "<td style='text-align: left'><b>" . $akun['kode'] . "</b> </td>";
				$html = $html . "<td style='text-align: left'><b>" . $akun['nama'] . "</b> </td>";

				$html = $html . "<td style='text-align: right'> </td>";
			}
		}
		$total_kepala8 = $sub_total;
		$html = $html . "<tr>";
		$html = $html . "<td style='text-align: center'> </td>";
		$html = $html . "<td style='text-align: left'><b>TOTAL PENDAPATAN LAIN-LAIN</b></td>";
		$html = $html . "<td style='text-align: right'><b>" . $this->format_number_report($sub_total) . " </b></td>";
		$html = $html . "</tr>";


		/* KEPALA 9 */
		$akun_kepala9   = $this->db->query($queryAkun9)->result_array();
		$sub_total = 0;
		foreach ($akun_kepala9 as $key => $akun) {
			$nourut = $nourut + 1;
			$total = 0;
			$html = $html . "<tr> ";

			if ($akun['is_transaksi_akun'] == 1) {
				$html = $html . "<td style='text-align: left'>" . $akun['kode'] . " </td>";
				$html = $html . "<td style='text-align: left'>" . $akun['nama'] . " </td>";

				if ($lokasi_id) {
					$query1 = "SELECT b.acc_akun_id,sum(debet-kredit)as jumlah FROM acc_jurnal_ht a inner join acc_jurnal_dt b 
					on a.id=b.jurnal_id
					where b.acc_akun_id=" . $akun['id'] . " 
					and (a.tanggal >= '" . $tanggal_mulai . "' and a.tanggal <= '" . $tanggal_akhir . "' )
					and  b.lokasi_id=" . $lokasi_id . "
					group by b.acc_akun_id ;";
				} else {
					if ($tipe_laporan == 'v1') {
						$query1 = "SELECT b.acc_akun_id,sum(debet-kredit)as jumlah FROM acc_jurnal_ht a inner join acc_jurnal_dt b 
						on a.id=b.jurnal_id
						where b.acc_akun_id=" . $akun['id'] . " 
						and (a.tanggal >= '" . $tanggal_mulai . "' and a.tanggal <= '" . $tanggal_akhir . "' )
						group by b.acc_akun_id ;";
					} else if ($tipe_laporan == 'v2') {
						$query1 = "SELECT b.acc_akun_id,sum(debet-kredit)as jumlah FROM acc_jurnal_ht a inner join acc_jurnal_dt b 
						on a.id=b.jurnal_id
						inner join gbm_organisasi c on b.lokasi_id=c.id
						where b.acc_akun_id=" . $akun['id'] . " 
						and (a.tanggal >= '" . $tanggal_mulai . "' and a.tanggal <= '" . $tanggal_akhir . "' )
						and  c.kode not in('SBME')
						group by b.acc_akun_id ;";
					} else if ($tipe_laporan == 'v3') {
						$query1 = "SELECT b.acc_akun_id,sum(debet-kredit)as jumlah FROM acc_jurnal_ht a inner join acc_jurnal_dt b 
						on a.id=b.jurnal_id
						inner join gbm_organisasi c on b.lokasi_id=c.id
						where b.acc_akun_id=" . $akun['id'] . " 
						and (a.tanggal >= '" . $tanggal_mulai . "' and a.tanggal <= '" . $tanggal_akhir . "' )
						and  c.kode  in('SBME')
						group by b.acc_akun_id ;";
					}
				}


				$resJumlah  = $this->db->query($query1)->row_array();
				$jumlah = (!empty($resJumlah['jumlah'])) ? ($resJumlah['jumlah']) : 0;


				$total = $total + $jumlah;
				$sub_total = $sub_total + $jumlah;
				$grandtotal = $grandtotal + $jumlah;
				$html = $html . "<td style='text-align: right'>" . $this->format_number_report($jumlah) . " </td>";
			} else {
				$html = $html . "<td style='text-align: left'><b>" . $akun['kode'] . "</b> </td>";
				$html = $html . "<td style='text-align: left'><b>" . $akun['nama'] . "</b> </td>";

				$html = $html . "<td style='text-align: right'> </td>";
			}
		}
		$total_kepala9 = $sub_total;
		$html = $html . "<tr>";
		$html = $html . "<td style='text-align: center'> </td>";
		$html = $html . "<td style='text-align: left'><b>BIAYA LAIN-LAIN</b></td>";
		$html = $html . "<td style='text-align: right'><b>" . $this->format_number_report($sub_total) . " </b></td>";
		$html = $html . "</tr>";

		$totalLabaRugi = ($total_kepala5 + $total_kepala8) - ($total_kepala6 + $total_kepala7+$total_kepala9);
		$html = $html . "<tr>";
		$html = $html . "<td style='text-align: center'> </td>";
		$html = $html . "<td style='text-align: left'><b>TOTAL LABA RUGI</b></td>";
		$html = $html . "<td style='text-align: right'><b>" . $this->format_number_report($totalLabaRugi) . " </b></td>";
		$html = $html . "</tr>";
		$html = $html . "</table>";

		if ($format_laporan == 'xls') {
			$reader = new \PhpOffice\PhpSpreadsheet\Reader\Html;
			$spreadsheet = $reader->loadFromString($html);
			// $reader->setSheetIndex(1);
			//$spreadhseet = $reader->loadFromString($secondHtmlString, $spreadsheet);
			$objWriter = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
			header("Pragma: public");
			header("Expires: 0");
			header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
			header("Content-Type: application/force-download");
			header("Content-Type: application/octet-stream");
			header("Content-Type: application/download");
			header("Content-Disposition: attachment;filename=test.xlsx");
			header("Content-Transfer-Encoding: binary ");

			ob_end_clean();
			ob_start();
			$objWriter->save('php://output');
		} else if ($format_laporan == 'view') {
			echo $html;
		} else {
			$filename = 'report_' . time();
			$this->pdfgenerator->generate($html, $filename, true, 'A4', 'landscape');
		}
	}
	function laporan_laba_rugi_inti_v1()
	{
		error_reporting(0);
		$lokasi_id     = $this->post('lokasi_id', true);
		$tanggal_mulai =  $this->post('tgl_mulai', true);
		$tanggal_akhir =  $this->post('tgl_akhir', true);
		$format_laporan =  $this->post('format_laporan', true);
		$judulLokasi = 'Semua';

		/* Ambil AKUN */
		$queryAkun5 = "select * from acc_akun where  (kode like '5%')  order by kode ";
		/* Ambil AKUN */
		$queryAkun6 = "select * from acc_akun where   (kode like '6%')     order by kode ";
		/* Ambil AKUN */
		$queryAkun7 = "select * from acc_akun	where  (kode like '7%')    order by kode ";
		/* Ambil AKUN */
		$queryAkun9 = "select * from acc_akun where (kode like '9%')  order by kode ";


		$res = array();
		// var_dump($results);
		$this->load->helper("terbilang");
		$this->load->helper("antech_helper");
		$this->load->library('pdfgenerator');
		if ($format_laporan == 'view') {
			$html = get_header_report_v2();
		} else {
			$html = get_header_pdf_report();
		}
		$html = $html . '
		<div class="row">
  <div class="span12">
	<br>
	<div class="kop-print">
    <div class="kop-nama">PT. ANNAJAH TECHNOLOGY INDONESIA</div>
    <div class="kop-info"> Komplek Vila Sehati B12 , Tapos - Depok</div>
    <div class="kop-info">Telp : 081387373939</div>
  </div>
	  <hr class="kop-print-hr">
  </div>
  </div>
  <h3 class="title">LAPORAN LABA RUGI</h3>
<h3>Periode: ' . $tanggal_mulai . ' s/d ' . $tanggal_akhir . '  </h3>';
		$html = $html . "
<table   border='1' width='100%' style='border-collapse: collapse;'>
<thead>
 </thead>
";


		/* QTy TBS */
		$query1 = "SELECT sum(qty_real) as jumlah FROM acc_sales_invoice a 
		INNER JOIN sls_kontrak b ON a.sls_kontrak_id=b.id
		INNER JOIN  inv_item c ON b.produk_id=c.id
		WHERE c.tipe_produk='TBS'  
		-- and a.jenis_invoice<>'UANG MUKA'
		and (a.tanggal >= '" . $tanggal_mulai . "' and a.tanggal <= '" . $tanggal_akhir . "' ) ;";
		$resJumlah  = $this->db->query($query1)->row_array();
		$jumlah = (!empty($resJumlah['jumlah'])) ? ($resJumlah['jumlah']) : 0;
		$pendapatan_usaha = $jumlah;
		$html = $html . "<tr>";
		$html = $html . "<td style='text-align: left'></td>";
		$html = $html . "<td style='text-align: left'>Quantity TBS </td>";
		$html = $html . "<td style='text-align: right'><b>" . $this->format_number_report($jumlah) . " </b></td>";
		$html = $html . "</tr>";

		/* QTy CPO */
		$query1 = "SELECT sum(qty_real) as jumlah FROM acc_sales_invoice a 
		INNER JOIN sls_kontrak b ON a.sls_kontrak_id=b.id
		INNER JOIN  inv_item c ON b.produk_id=c.id
		WHERE c.tipe_produk='CPO'  
		-- and a.jenis_invoice<>'UANG MUKA'
		and (a.tanggal >= '" . $tanggal_mulai . "' and a.tanggal <= '" . $tanggal_akhir . "' ) ;";
		$resJumlah  = $this->db->query($query1)->row_array();
		$jumlah = (!empty($resJumlah['jumlah'])) ? ($resJumlah['jumlah']) : 0;
		$pendapatan_usaha = $jumlah;
		$html = $html . "<tr>";
		$html = $html . "<td style='text-align: left'></td>";
		$html = $html . "<td style='text-align: left'>Quantity CPO </td>";
		$html = $html . "<td style='text-align: right'><b>" . $this->format_number_report($jumlah) . " </b></td>";
		$html = $html . "</tr>";

		/* QTy PK */
		$query1 = "SELECT sum(qty_real) as jumlah FROM acc_sales_invoice a 
		INNER JOIN sls_kontrak b ON a.sls_kontrak_id=b.id
		INNER JOIN  inv_item c ON b.produk_id=c.id
		WHERE c.tipe_produk='PK'  
		--  and a.jenis_invoice<>'UANG MUKA'
		and (a.tanggal >= '" . $tanggal_mulai . "' and a.tanggal <= '" . $tanggal_akhir . "' ) ;";
		$resJumlah  = $this->db->query($query1)->row_array();
		$jumlah = (!empty($resJumlah['jumlah'])) ? ($resJumlah['jumlah']) : 0;
		$pendapatan_usaha = $jumlah;
		$html = $html . "<tr>";
		$html = $html . "<td style='text-align: left'></td>";
		$html = $html . "<td style='text-align: left'>Quantity PK </td>";
		$html = $html . "<td style='text-align: right'><b>" . $this->format_number_report($jumlah) . " </b></td>";
		$html = $html . "</tr>";

		/* QTy PK SHELL */
		$query1 = "SELECT sum(qty_real) as jumlah FROM acc_sales_invoice a 
		INNER JOIN sls_kontrak b ON a.sls_kontrak_id=b.id
		INNER JOIN  inv_item c ON b.produk_id=c.id
		WHERE c.tipe_produk='CANGKANG'  
		-- and a.jenis_invoice<>'UANG MUKA'
		and (a.tanggal >= '" . $tanggal_mulai . "' and a.tanggal <= '" . $tanggal_akhir . "' ) ;";
		$resJumlah  = $this->db->query($query1)->row_array();
		$jumlah = (!empty($resJumlah['jumlah'])) ? ($resJumlah['jumlah']) : 0;
		$pendapatan_usaha = $jumlah;
		$html = $html . "<tr>";
		$html = $html . "<td style='text-align: left'></td>";
		$html = $html . "<td style='text-align: left'>Quantity PK Shell</td>";
		$html = $html . "<td style='text-align: right'><b>" . $this->format_number_report($jumlah) . " </b></td>";
		$html = $html . "</tr>";
		$nourut = 0;
		/* Pendapatan Kepala 5 */
		$grandtotal = 0;
		$total_kepala5 = 0;
		$total_kepala6 = 0;
		$total_kepala7 = 0;
		$total_kepala9 = 0;
		$totalBiaya = 0;
		$totalLabaRugi = 0;
		$sub_total = 0;
		$akun_kepala5   = $this->db->query($queryAkun5)->result_array();
		foreach ($akun_kepala5 as $key => $akun) {
			$nourut = $nourut + 1;
			$total = 0;
			$html = $html . "<tr> ";

			if ($akun['is_transaksi_akun'] == 1) {
				$html = $html . "<td style='text-align: left'>" . $akun['kode'] . " </td>";
				$html = $html . "<td style='text-align: left'>" . $akun['nama'] . " </td>";
				if ($lokasi_id) {
					$query1 = "SELECT b.acc_akun_id,sum(debet-kredit)as jumlah FROM acc_jurnal_ht a inner join acc_jurnal_dt b 
					on a.id=b.jurnal_id
					where b.acc_akun_id=" . $akun['id'] . " 
					and (a.tanggal >= '" . $tanggal_mulai . "' and a.tanggal <= '" . $tanggal_akhir . "' )
					and  b.lokasi_id=" . $lokasi_id . "
					group by b.acc_akun_id ;";
				} else {
					$query1 = "SELECT b.acc_akun_id,sum(debet-kredit)as jumlah FROM acc_jurnal_ht a inner join acc_jurnal_dt b 
					on a.id=b.jurnal_id
					where b.acc_akun_id=" . $akun['id'] . " 
					and (a.tanggal >= '" . $tanggal_mulai . "' and a.tanggal <= '" . $tanggal_akhir . "' )
					and  b.lokasi_id <>265
					group by b.acc_akun_id ;";
				}


				$resJumlah  = $this->db->query($query1)->row_array();
				$jumlah = (!empty($resJumlah['jumlah'])) ? ($resJumlah['jumlah'] * -1) : 0;


				$total = $total + $jumlah;
				$sub_total = $sub_total + $jumlah;
				$grandtotal = $grandtotal + $jumlah;
				$html = $html . "<td style='text-align: right'>" . $this->format_number_report($jumlah) . " </td>";
			} else {
				$html = $html . "<td style='text-align: left'><b>" . $akun['kode'] . "</b> </td>";
				$html = $html . "<td style='text-align: left'><b>" . $akun['nama'] . "</b> </td>";
				$html = $html . "<td style='text-align: right'> </td>";
			}


			$html = $html . "</tr>";
		}

		$html = $html . "<tr>";
		$html = $html . "<td style='text-align: center'> </td>";
		$html = $html . "<td style='text-align: left'><b>TOTAL PENDAPATAN</b></td>";

		$html = $html . "<td style='text-align: right'><b>" . $this->format_number_report($sub_total) . " </b></td>";
		$html = $html . "</tr>";

		$total_kepala5 = $sub_total;

		/* KEPALA 6 */
		$akun_kepala6   = $this->db->query($queryAkun6)->result_array();
		$sub_total = 0;
		foreach ($akun_kepala6 as $key => $akun) {
			$nourut = $nourut + 1;
			$total = 0;
			$html = $html . "<tr> ";

			if ($akun['is_transaksi_akun'] == 1) {
				$html = $html . "<td style='text-align: left'>" . $akun['kode'] . " </td>";
				$html = $html . "<td style='text-align: left'>" . $akun['nama'] . " </td>";
				if ($lokasi_id) {
					$query1 = "SELECT b.acc_akun_id,sum(debet-kredit)as jumlah FROM acc_jurnal_ht a inner join acc_jurnal_dt b 
					on a.id=b.jurnal_id
					where b.acc_akun_id=" . $akun['id'] . " 
					and (a.tanggal >= '" . $tanggal_mulai . "' and a.tanggal <= '" . $tanggal_akhir . "' )
					and  b.lokasi_id=" . $lokasi_id . "
					group by b.acc_akun_id ;";
				} else {
					$query1 = "SELECT b.acc_akun_id,sum(debet-kredit)as jumlah FROM acc_jurnal_ht a inner join acc_jurnal_dt b 
					on a.id=b.jurnal_id
					where b.acc_akun_id=" . $akun['id'] . " 
					and (a.tanggal >= '" . $tanggal_mulai . "' and a.tanggal <= '" . $tanggal_akhir . "' )
					and  b.lokasi_id <>265
					group by b.acc_akun_id ;";
				}


				$resJumlah  = $this->db->query($query1)->row_array();
				$jumlah = (!empty($resJumlah['jumlah'])) ? ($resJumlah['jumlah']) : 0;


				$total = $total + $jumlah;
				$sub_total = $sub_total + $jumlah;
				$grandtotal = $grandtotal + $jumlah;
				$html = $html . "<td style='text-align: right'>" . $this->format_number_report($jumlah) . " </td>";
			} else {
				$html = $html . "<td style='text-align: left'><b>" . $akun['kode'] . "</b> </td>";
				$html = $html . "<td style='text-align: left'><b>" . $akun['nama'] . "</b> </td>";

				$html = $html . "<td style='text-align: right'> </td>";
			}
		}
		$total_kepala6 = $sub_total;
		$html = $html . "<tr>";
		$html = $html . "<td style='text-align: center'> </td>";
		$html = $html . "<td style='text-align: left'><b>TOTAL BEBAN PRODUKSI LANGSUNG</b></td>";
		$html = $html . "<td style='text-align: right'><b>" . $this->format_number_report($sub_total) . " </b></td>";
		$html = $html . "</tr>";

		/* KEPALA 7 */
		$akun_kepala7  = $this->db->query($queryAkun7)->result_array();
		$sub_total = 0;
		foreach ($akun_kepala7 as $key => $akun) {
			$nourut = $nourut + 1;
			$total = 0;
			$html = $html . "<tr> ";

			if ($akun['is_transaksi_akun'] == 1) {
				$html = $html . "<td style='text-align: left'>" . $akun['kode'] . " </td>";
				$html = $html . "<td style='text-align: left'>" . $akun['nama'] . " </td>";

				if ($lokasi_id) {
					$query1 = "SELECT b.acc_akun_id,sum(debet-kredit)as jumlah FROM acc_jurnal_ht a inner join acc_jurnal_dt b 
					on a.id=b.jurnal_id
					where b.acc_akun_id=" . $akun['id'] . " 
					and (a.tanggal >= '" . $tanggal_mulai . "' and a.tanggal <= '" . $tanggal_akhir . "' )
					and  b.lokasi_id=" . $lokasi_id . "
					group by b.acc_akun_id ;";
				} else {
					$query1 = "SELECT b.acc_akun_id,sum(debet-kredit)as jumlah FROM acc_jurnal_ht a inner join acc_jurnal_dt b 
					on a.id=b.jurnal_id
					where b.acc_akun_id=" . $akun['id'] . " 
					and (a.tanggal >= '" . $tanggal_mulai . "' and a.tanggal <= '" . $tanggal_akhir . "' )
					and  b.lokasi_id <>265
					group by b.acc_akun_id ;";
				}


				$resJumlah  = $this->db->query($query1)->row_array();
				$jumlah = (!empty($resJumlah['jumlah'])) ? ($resJumlah['jumlah']) : 0;


				$total = $total + $jumlah;
				$sub_total = $sub_total + $jumlah;
				$grandtotal = $grandtotal + $jumlah;
				$html = $html . "<td style='text-align: right'>" . $this->format_number_report($jumlah) . " </td>";
			} else {
				$html = $html . "<td style='text-align: left'><b>" . $akun['kode'] . "</b> </td>";
				$html = $html . "<td style='text-align: left'><b>" . $akun['nama'] . "</b> </td>";

				$html = $html . "<td style='text-align: right'> </td>";
			}
		}
		$total_kepala7 = $sub_total;
		$html = $html . "<tr>";
		$html = $html . "<td style='text-align: center'> </td>";
		$html = $html . "<td style='text-align: left'><b>TOTAL BEBAN PRODUKSI TIDAK LANGSUNG</b></td>";
		$html = $html . "<td style='text-align: right'><b>" . $this->format_number_report($sub_total) . " </b></td>";
		$html = $html . "</tr>";

		/* KEPALA 9 */
		$akun_kepala9   = $this->db->query($queryAkun9)->result_array();
		$sub_total = 0;
		foreach ($akun_kepala9 as $key => $akun) {
			$nourut = $nourut + 1;
			$total = 0;
			$html = $html . "<tr> ";

			if ($akun['is_transaksi_akun'] == 1) {
				$html = $html . "<td style='text-align: left'>" . $akun['kode'] . " </td>";
				$html = $html . "<td style='text-align: left'>" . $akun['nama'] . " </td>";

				if ($lokasi_id) {
					$query1 = "SELECT b.acc_akun_id,sum(debet-kredit)as jumlah FROM acc_jurnal_ht a inner join acc_jurnal_dt b 
					on a.id=b.jurnal_id
					where b.acc_akun_id=" . $akun['id'] . " 
					and (a.tanggal >= '" . $tanggal_mulai . "' and a.tanggal <= '" . $tanggal_akhir . "' )
					and  b.lokasi_id=" . $lokasi_id . "
					group by b.acc_akun_id ;";
				} else {
					$query1 = "SELECT b.acc_akun_id,sum(debet-kredit)as jumlah FROM acc_jurnal_ht a inner join acc_jurnal_dt b 
					on a.id=b.jurnal_id
					where b.acc_akun_id=" . $akun['id'] . " 
					and (a.tanggal >= '" . $tanggal_mulai . "' and a.tanggal <= '" . $tanggal_akhir . "' )
					and  b.lokasi_id <>265
					group by b.acc_akun_id ;";
				}


				$resJumlah  = $this->db->query($query1)->row_array();
				$jumlah = (!empty($resJumlah['jumlah'])) ? ($resJumlah['jumlah'] * -1) : 0;


				$total = $total + $jumlah;
				$sub_total = $sub_total + $jumlah;
				$grandtotal = $grandtotal + $jumlah;
				$html = $html . "<td style='text-align: right'>" . $this->format_number_report($jumlah) . " </td>";
			} else {
				$html = $html . "<td style='text-align: left'><b>" . $akun['kode'] . "</b> </td>";
				$html = $html . "<td style='text-align: left'><b>" . $akun['nama'] . "</b> </td>";

				$html = $html . "<td style='text-align: right'> </td>";
			}
		}
		$total_kepala9 = $sub_total;
		$html = $html . "<tr>";
		$html = $html . "<td style='text-align: center'> </td>";
		$html = $html . "<td style='text-align: left'><b>TOTAL PENDAPATAN DAN BEBAN USAHA LAINNYA</b></td>";
		$html = $html . "<td style='text-align: right'><b>" . $this->format_number_report($sub_total) . " </b></td>";
		$html = $html . "</tr>";

		$totalLabaRugi = ($total_kepala5 + $total_kepala9) - ($total_kepala6 + $total_kepala7);
		$html = $html . "<tr>";
		$html = $html . "<td style='text-align: center'> </td>";
		$html = $html . "<td style='text-align: left'><b>TOTAL LABA RUGI</b></td>";
		$html = $html . "<td style='text-align: right'><b>" . $this->format_number_report($totalLabaRugi) . " </b></td>";
		$html = $html . "</tr>";
		$html = $html . "</table>";

		if ($format_laporan == 'xls') {
			$reader = new \PhpOffice\PhpSpreadsheet\Reader\Html;
			$spreadsheet = $reader->loadFromString($html);
			// $reader->setSheetIndex(1);
			//$spreadhseet = $reader->loadFromString($secondHtmlString, $spreadsheet);
			$objWriter = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
			header("Pragma: public");
			header("Expires: 0");
			header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
			header("Content-Type: application/force-download");
			header("Content-Type: application/octet-stream");
			header("Content-Type: application/download");
			header("Content-Disposition: attachment;filename=test.xlsx");
			header("Content-Transfer-Encoding: binary ");

			ob_end_clean();
			ob_start();
			$objWriter->save('php://output');
		} else if ($format_laporan == 'view') {
			echo $html;
		} else {
			$filename = 'report_' . time();
			$this->pdfgenerator->generate($html, $filename, true, 'A4', 'landscape');
		}
	}
	function laporan_laba_rugi_v2($tipe_laporan)
	{
		error_reporting(0);

		$lokasi_id     = $this->post('lokasi_id', true);
		$tanggal_mulai =  $this->post('tgl_mulai', true);
		$tanggal_akhir =  $this->post('tgl_akhir', true);
		$format_laporan =  $this->post('format_laporan', true);
		$judulLokasi = 'Semua';

		$this->load->helper("antech_helper");
		$this->load->library('pdfgenerator');
		if ($format_laporan == 'view') {
			$html = get_header_report_v2();
		} else {
			$html = get_header_pdf_report();
		}

		$html = $html . '
		<div class="row">
  <div class="span12">
	<br>
	<div class="kop-print">
    <div class="kop-nama">PT. ANNAJAH TECHNOLOGY INDONESIA</div>
    <div class="kop-info"> Komplek Vila Sehati B12 , Tapos - Depok</div>
    <div class="kop-info">Telp : 081387373939</div>
  </div>
	  <hr class="kop-print-hr">
  </div>
  </div>
  <h3 class="title">LAPORAN LABA RUGI</h3>
<h3>Periode: ' . $tanggal_mulai . ' s/d ' . $tanggal_akhir . '  </h3>';
		$html = $html . "
<table   border='1' width='100%' style='border-collapse: collapse;'>
<thead>
 </thead>
";

		/* Note: Utk Plasma Nilai akun pembelian Plasma di Pindah menjadi Penjualan Plasma
		3407 - 6310131 - Pembelian TBS Plasma
		3708 - 5110207 - Penjualan TBS Plasma Ke PT. XXX
		*/
		// $queryPembelianTBSPlasma = "SELECT b.acc_akun_id,sum(debet-kredit)as jumlah FROM acc_jurnal_ht a inner join acc_jurnal_dt b 
		// on a.id=b.jurnal_id
		// inner join gbm_organisasi c on b.lokasi_id=c.id
		// where b.acc_akun_id=3407 
		// and (a.tanggal >= '" . $tanggal_mulai . "' and a.tanggal <= '" . $tanggal_akhir . "' )
		// and  c.kode  in('DPHO','DPAM')
		// group by b.acc_akun_id ;";
		// $resJumlahPembelianTBSPlasma  = $this->db->query($queryPembelianTBSPlasma)->row_array();
		// $nilaiPembelianTBSHOMill =  (!empty($resJumlahPembelianTBSPlasma['jumlah'])) ? ($resJumlahPembelianTBSPlasma['jumlah']) : 0;


		/* QTy TBS */
		// if ($tipe_laporan == 'v3') {

		// 	$resJumlah = $this->db->query("SELECT SUM(jum_kg_pks)AS jumlah,SUM(jum_ha)AS ha FROM est_produksi_panen_ht a
		// 	INNER JOIN est_produksi_panen_dt b ON a.id=b.produksi_panen_id
		// 	INNER JOIN gbm_organisasi c ON a.divisi_id=c.id
		// 	INNER JOIN gbm_organisasi d ON c.parent_id=d.id 
		// 	INNER JOIN gbm_organisasi e ON d.parent_id=e.id 
		// 	where e.kode='SBME' 
		// 	and a.tanggal>='" . $tanggal_mulai . "' and a.tanggal<='" . $tanggal_akhir . "' ")->row_array();

		// 	$jumlah = (!empty($resJumlah['jumlah'])) ? ($resJumlah['jumlah']) : 0;
		// } else {
		// 	$query1 = "SELECT sum(qty_real) as jumlah FROM acc_sales_invoice a 
		// 	INNER JOIN sls_kontrak b ON a.sls_kontrak_id=b.id
		// 	INNER JOIN  inv_item c ON b.produk_id=c.id
		// 	WHERE c.tipe_produk='TBS'  
		// 	and (a.tanggal >= '" . $tanggal_mulai . "' and a.tanggal <= '" . $tanggal_akhir . "' ) ;";

		// 	$resJumlah  = $this->db->query($query1)->row_array();
		// 	$jumlah = (!empty($resJumlah['jumlah'])) ? ($resJumlah['jumlah']) : 0;
		// }

		// $html = $html . "<tr>";
		// $html = $html . "<td style='text-align: left'>Quantity TBS </td>";
		// $html = $html . "<td style='text-align: right'><b>" . $this->format_number_report($jumlah) . " </b></td>";
		// $html = $html . "</tr>";

		/* QTy CPO */
		// if ($tipe_laporan == 'v3') {
		// 	$jumlah = 0;
		// } else {
		// 	$query1 = "SELECT sum(qty_real) as jumlah FROM acc_sales_invoice a 
		// 	INNER JOIN sls_kontrak b ON a.sls_kontrak_id=b.id
		// 	INNER JOIN  inv_item c ON b.produk_id=c.id
		// 	WHERE c.tipe_produk='CPO' 
		// 	and (a.tanggal >= '" . $tanggal_mulai . "' and a.tanggal <= '" . $tanggal_akhir . "' ) ;";

		// 	$resJumlah  = $this->db->query($query1)->row_array();

		// 	$jumlah = (!empty($resJumlah['jumlah'])) ? ($resJumlah['jumlah']) : 0;
		// }
		// $html = $html . "<tr>";
		// $html = $html . "<td style='text-align: left'>Quantity CPO </td>";
		// $html = $html . "<td style='text-align: right'><b>" . $this->format_number_report($jumlah) . " </b></td>";
		// $html = $html . "</tr>";

		/* QTy PK */
		// if ($tipe_laporan == 'v3') {
		// 	$jumlah = 0;
		// } else {
		// 	$query1 = "SELECT sum(qty_real) as jumlah FROM acc_sales_invoice a 
		// 	INNER JOIN sls_kontrak b ON a.sls_kontrak_id=b.id
		// 	INNER JOIN  inv_item c ON b.produk_id=c.id
		// 	WHERE c.tipe_produk='PK'  
		// 	-- and a.jenis_invoice<>'UANG MUKA'
		// 	and (a.tanggal >= '" . $tanggal_mulai . "' and a.tanggal <= '" . $tanggal_akhir . "' ) ;";
		// 	$resJumlah  = $this->db->query($query1)->row_array();
		// 	$jumlah = (!empty($resJumlah['jumlah'])) ? ($resJumlah['jumlah']) : 0;
		// }
		// $html = $html . "<tr>";
		// $html = $html . "<td style='text-align: left'>Quantity PK </td>";
		// $html = $html . "<td style='text-align: right'><b>" . $this->format_number_report($jumlah) . " </b></td>";
		// $html = $html . "</tr>";

		/* QTy PK SHELL */
		// if ($tipe_laporan == 'v3') {
		// 	$jumlah = 0;
		// } else {
		// 	$query1 = "SELECT sum(qty_real) as jumlah FROM acc_sales_invoice a 
		// 	INNER JOIN sls_kontrak b ON a.sls_kontrak_id=b.id
		// 	INNER JOIN  inv_item c ON b.produk_id=c.id
		// 		WHERE c.tipe_produk='CANGKANG'  
		// 		and (a.tanggal >= '" . $tanggal_mulai . "' and a.tanggal <= '" . $tanggal_akhir . "' ) ;";
		// 	$resJumlah  = $this->db->query($query1)->row_array();
		// 	$jumlah = (!empty($resJumlah['jumlah'])) ? ($resJumlah['jumlah']) : 0;
		// }
		// $html = $html . "<tr>";
		// $html = $html . "<td style='text-align: left'>Quantity PK Shell</td>";
		// $html = $html . "<td style='text-align: right'><b>" . $this->format_number_report($jumlah) . " </b></td>";
		// $html = $html . "</tr>";

		/* PENDAPATAN USAHA */
		if ($lokasi_id) {
			$query1 = "SELECT sum(debet-kredit)as jumlah FROM acc_jurnal_ht a inner join acc_jurnal_dt b 
			on a.id=b.jurnal_id
			inner join acc_akun c on b.acc_akun_id=c.id
			where c.kode like '5%' 
			and (a.tanggal >= '" . $tanggal_mulai . "' and a.tanggal <= '" . $tanggal_akhir . "' )
			and  b.lokasi_id=" . $lokasi_id . " ;";
		} else {
			if ($tipe_laporan == 'v1') {
				$query1 = "SELECT sum(debet-kredit)as jumlah FROM acc_jurnal_ht a inner join acc_jurnal_dt b 
				on a.id=b.jurnal_id
				inner join acc_akun c on b.acc_akun_id=c.id
				where c.kode like '5%' 
				and (a.tanggal >= '" . $tanggal_mulai . "' and a.tanggal <= '" . $tanggal_akhir . "' ) ;";
			} else if ($tipe_laporan == 'v2') {
				$query1 = "SELECT sum(debet-kredit)as jumlah FROM acc_jurnal_ht a inner join acc_jurnal_dt b 
				on a.id=b.jurnal_id
				inner join acc_akun c on b.acc_akun_id=c.id
				inner join gbm_organisasi d on b.lokasi_id=d.id
				where c.kode like '5%' 
				and d.kode not in ('SBME')
				and (a.tanggal >= '" . $tanggal_mulai . "' and a.tanggal <= '" . $tanggal_akhir . "' ) ;";
			} else if ($tipe_laporan == 'v3') {
				$query1 = "SELECT sum(debet-kredit)as jumlah FROM acc_jurnal_ht a inner join acc_jurnal_dt b 
				on a.id=b.jurnal_id
				inner join acc_akun c on b.acc_akun_id=c.id
				inner join gbm_organisasi d on b.lokasi_id=d.id
				where c.kode like '5%' 
				and d.kode  in ('SBME')
				and (a.tanggal >= '" . $tanggal_mulai . "' and a.tanggal <= '" . $tanggal_akhir . "' ) ;";
			}
		}

		$resJumlah  = $this->db->query($query1)->row_array();

		$pendapatan_usaha = (!empty($resJumlah['jumlah'])) ? ($resJumlah['jumlah'] * -1) : 0;

		if ($tipe_laporan == 'v3') {
			/* Note: Utk Plasma, Nilai akun pembelian Plasma di Pindah menjadi Penjualan Plasma
				3407 - 6310131 - Pembelian TBS Plasma
				3708 - 5110207 - Penjualan TBS Plasma Ke PT. XXX
				   JIka akun penjualan TBS plasma maka dijumlahkan Nilai Pembelian TBS HO/MILL
			*/
			$pendapatan_usaha = $pendapatan_usaha + $nilaiPembelianTBSHOMill;
		}
		$html = $html . "<tr>";
		$html = $html . "<td style='text-align: left'>PENDAPATAN USAHA </td>";
		$html = $html . "<td style='text-align: right'>" . $this->format_number_report($pendapatan_usaha) . " </td>";
		$html = $html . "</tr>";

		/* BEBAN POKOK PENDAPATAN */
		if ($lokasi_id) {
			$query1 = "SELECT sum(debet-kredit)as jumlah FROM acc_jurnal_ht a inner join acc_jurnal_dt b 
			on a.id=b.jurnal_id
			inner join acc_akun c on b.acc_akun_id=c.id
			where c.kode like '6%' 
			and (a.tanggal >= '" . $tanggal_mulai . "' and a.tanggal <= '" . $tanggal_akhir . "' )
			and  b.lokasi_id=" . $lokasi_id . " ;";
		} else {
			if ($tipe_laporan == 'v1') {
				$query1 = "SELECT sum(debet-kredit)as jumlah FROM acc_jurnal_ht a inner join acc_jurnal_dt b 
				on a.id=b.jurnal_id
				inner join acc_akun c on b.acc_akun_id=c.id
				where c.kode like '6%' 
				and (a.tanggal >= '" . $tanggal_mulai . "' and a.tanggal <= '" . $tanggal_akhir . "' ) ;";
			} else 	if ($tipe_laporan == 'v2') {
				$query1 = "SELECT sum(debet-kredit)as jumlah FROM acc_jurnal_ht a inner join acc_jurnal_dt b 
				on a.id=b.jurnal_id
				inner join gbm_organisasi d on b.lokasi_id=d.id
				inner join acc_akun c on b.acc_akun_id=c.id
				where c.kode like '6%' 
				and d.kode not in ('SBME')
				and (a.tanggal >= '" . $tanggal_mulai . "' and a.tanggal <= '" . $tanggal_akhir . "' ) ;";
			} else 	if ($tipe_laporan == 'v3') {
				$query1 = "SELECT sum(debet-kredit)as jumlah FROM acc_jurnal_ht a inner join acc_jurnal_dt b 
				on a.id=b.jurnal_id
				inner join acc_akun c on b.acc_akun_id=c.id
				inner join gbm_organisasi d on b.lokasi_id=d.id
				where c.kode like '6%' 
				and d.kode  in ('SBME')
				and (a.tanggal >= '" . $tanggal_mulai . "' and a.tanggal <= '" . $tanggal_akhir . "' ) ;";
			}
		}
		$resJumlah  = $this->db->query($query1)->row_array();
		$beban_pokok_pendapatan = (!empty($resJumlah['jumlah'])) ? ($resJumlah['jumlah']) : 0;

		$html = $html . "<tr>";
		$html = $html . "<td style='text-align: left'>BEBAN POKOK PENDAPATAN </td>";
		$html = $html . "<td style='text-align: right'>" . $this->format_number_report($beban_pokok_pendapatan) . " </td>";
		$html = $html . "</tr>";

		$laba_kotor = $pendapatan_usaha - $beban_pokok_pendapatan;
		$html = $html . "<tr>";
		$html = $html . "<td style='text-align: left'>LABA KOTOR </td>";
		$html = $html . "<td style='text-align: right'><b>" . $this->format_number_report($laba_kotor) . " </b></td>";
		$html = $html . "</tr>";

		/* BEBAN UMUM ADMINISTRASI */
		if ($lokasi_id) {
			$query1 = "SELECT sum(debet-kredit)as jumlah FROM acc_jurnal_ht a inner join acc_jurnal_dt b 
			on a.id=b.jurnal_id
			inner join acc_akun c on b.acc_akun_id=c.id
			where c.kode like '7%' 
			and (a.tanggal >= '" . $tanggal_mulai . "' and a.tanggal <= '" . $tanggal_akhir . "' )
			and  b.lokasi_id=" . $lokasi_id . " ;";
		} else {
			if ($tipe_laporan == 'v1') {
				$query1 = "SELECT sum(debet-kredit)as jumlah FROM acc_jurnal_ht a inner join acc_jurnal_dt b 
				on a.id=b.jurnal_id
				inner join acc_akun c on b.acc_akun_id=c.id
				where c.kode like '7%' 
				and (a.tanggal >= '" . $tanggal_mulai . "' and a.tanggal <= '" . $tanggal_akhir . "' ) ;";
			} else 	if ($tipe_laporan == 'v2') {
				$query1 = "SELECT sum(debet-kredit)as jumlah FROM acc_jurnal_ht a inner join acc_jurnal_dt b 
				on a.id=b.jurnal_id
				inner join acc_akun c on b.acc_akun_id=c.id
				inner join gbm_organisasi d on b.lokasi_id=d.id
				where c.kode like '7%' 
				and d.kode not in ('SBME')
				and (a.tanggal >= '" . $tanggal_mulai . "' and a.tanggal <= '" . $tanggal_akhir . "' ) ;";
			} else 	if ($tipe_laporan == 'v3') {
				$query1 = "SELECT sum(debet-kredit)as jumlah FROM acc_jurnal_ht a inner join acc_jurnal_dt b 
				on a.id=b.jurnal_id
				inner join acc_akun c on b.acc_akun_id=c.id
				inner join gbm_organisasi d on b.lokasi_id=d.id
				where c.kode like '7%' 
				and d.kode  in ('SBME')
				and (a.tanggal >= '" . $tanggal_mulai . "' and a.tanggal <= '" . $tanggal_akhir . "' ) ;";
			}
		}
		$resJumlah  = $this->db->query($query1)->row_array();
		$beban_umum_administrasi = (!empty($resJumlah['jumlah'])) ? ($resJumlah['jumlah']) : 0;
		$html = $html . "<tr>";
		$html = $html . "<td style='text-align: left'> BEBAN UMUM ADMINISTRASI </td>";
		$html = $html . "<td style='text-align: right'>" . $this->format_number_report($beban_umum_administrasi) . " </td>";
		$html = $html . "</tr>";

		/* JUMLAH PENDAPATAN (BEBAN) OPERASIONAL */
		$jumlah_pendapatan_beban_operasional = $laba_kotor - $beban_umum_administrasi;
		$html = $html . "<tr>";
		$html = $html . "<td style='text-align: left'> JUMLAH PENDAPATAN (BEBAN) OPERASIONAL </td>";
		$html = $html . "<td style='text-align: right'><b>" . $this->format_number_report($jumlah_pendapatan_beban_operasional) . " </b></td>";
		$html = $html . "</tr>";

		/*Pendapatan Lain-lain */
		if ($lokasi_id) {
			$query1 = "SELECT sum(debet-kredit)as jumlah FROM acc_jurnal_ht a inner join acc_jurnal_dt b 
			on a.id=b.jurnal_id
			inner join acc_akun c on b.acc_akun_id=c.id
			where c.kode like '911%' 
			and (a.tanggal >= '" . $tanggal_mulai . "' and a.tanggal <= '" . $tanggal_akhir . "' )
			and  b.lokasi_id=" . $lokasi_id . " ;";
		} else {
			if ($tipe_laporan == 'v1') {
				$query1 = "SELECT sum(debet-kredit)as jumlah FROM acc_jurnal_ht a inner join acc_jurnal_dt b 
				on a.id=b.jurnal_id
				inner join acc_akun c on b.acc_akun_id=c.id
				where c.kode like '911%' 
				and (a.tanggal >= '" . $tanggal_mulai . "' and a.tanggal <= '" . $tanggal_akhir . "' ) ;";
			} else if ($tipe_laporan == 'v2') {
				$query1 = "SELECT sum(debet-kredit)as jumlah FROM acc_jurnal_ht a inner join acc_jurnal_dt b 
				on a.id=b.jurnal_id
				inner join acc_akun c on b.acc_akun_id=c.id
				inner join gbm_organisasi d on b.lokasi_id=d.id
				where c.kode like '911%' 
				and d.kode not in ('SBME')
				and (a.tanggal >= '" . $tanggal_mulai . "' and a.tanggal <= '" . $tanggal_akhir . "' ) ;";
			} else if ($tipe_laporan == 'v3') {
				$query1 = "SELECT sum(debet-kredit)as jumlah FROM acc_jurnal_ht a inner join acc_jurnal_dt b 
				on a.id=b.jurnal_id
				inner join acc_akun c on b.acc_akun_id=c.id
				inner join gbm_organisasi d on b.lokasi_id=d.id
				where c.kode like '911%' 
				and d.kode  in ('SBME')
				and (a.tanggal >= '" . $tanggal_mulai . "' and a.tanggal <= '" . $tanggal_akhir . "' ) ;";
			}
		}
		$resJumlah  = $this->db->query($query1)->row_array();
		$pendapatan_lain_lain = (!empty($resJumlah['jumlah'])) ? ($resJumlah['jumlah']) : 0;
		$html = $html . "<tr>";
		$html = $html . "<td style='text-align: left'>Pendapatan Lain-lain </td>";
		$html = $html . "<td style='text-align: right'>" . $this->format_number_report($pendapatan_lain_lain) . " </td>";
		$html = $html . "</tr>";

		/* beban Lain-lain */
		if ($lokasi_id) {
			$query1 = "SELECT sum(debet-kredit)as jumlah FROM acc_jurnal_ht a inner join acc_jurnal_dt b 
			on a.id=b.jurnal_id
			inner join acc_akun c on b.acc_akun_id=c.id
			where c.kode like '912%' 
			and (a.tanggal >= '" . $tanggal_mulai . "' and a.tanggal <= '" . $tanggal_akhir . "' )
			and  b.lokasi_id=" . $lokasi_id . " ;";
		} else {
			if ($tipe_laporan == 'v1') {
				$query1 = "SELECT sum(debet-kredit)as jumlah FROM acc_jurnal_ht a inner join acc_jurnal_dt b 
				on a.id=b.jurnal_id
				inner join acc_akun c on b.acc_akun_id=c.id
				where c.kode like '912%' 
				and (a.tanggal >= '" . $tanggal_mulai . "' and a.tanggal <= '" . $tanggal_akhir . "' ) ;";
			} else if ($tipe_laporan == 'v2') {
				$query1 = "SELECT sum(debet-kredit)as jumlah FROM acc_jurnal_ht a inner join acc_jurnal_dt b 
				on a.id=b.jurnal_id
				inner join acc_akun c on b.acc_akun_id=c.id
				inner join gbm_organisasi d on b.lokasi_id=d.id
				where c.kode like '912%' 
				and d.kode not in ('SBME')
				and (a.tanggal >= '" . $tanggal_mulai . "' and a.tanggal <= '" . $tanggal_akhir . "' ) ;";
			} else if ($tipe_laporan == 'v3') {
				$query1 = "SELECT sum(debet-kredit)as jumlah FROM acc_jurnal_ht a inner join acc_jurnal_dt b 
				on a.id=b.jurnal_id
				inner join acc_akun c on b.acc_akun_id=c.id
				inner join gbm_organisasi d on b.lokasi_id=d.id
				where c.kode like '912%' 
				and d.kode  in ('SBME')
				and (a.tanggal >= '" . $tanggal_mulai . "' and a.tanggal <= '" . $tanggal_akhir . "' ) ;";
			}
		}
		$resJumlah  = $this->db->query($query1)->row_array();
		$beban_lain_lain = (!empty($resJumlah['jumlah'])) ? ($resJumlah['jumlah']) : 0;
		$html = $html . "<tr>";
		$html = $html . "<td style='text-align: left'>beban Lain-lain </td>";
		$html = $html . "<td style='text-align: right'>" . $this->format_number_report($beban_lain_lain) . "</td>";
		$html = $html . "</tr>";

		/*Jumlah Pendapatan beban Lain-lain */

		$jumlah_pendapatan_beban_lain_lain = $pendapatan_lain_lain + $beban_lain_lain;
		$html = $html . "<tr>";
		$html = $html . "<td style='text-align: left'>Jumlah Pendapatan beban Lain-lain </td>";
		$html = $html . "<td style='text-align: right'><b>" . $this->format_number_report($jumlah_pendapatan_beban_lain_lain) . " </b></td>";
		$html = $html . "</tr>";

		/*LABA (RUGI) SEBELUM PAJAK */
		$laba_rugi_sebelum_pajak = $jumlah_pendapatan_beban_operasional - $jumlah_pendapatan_beban_lain_lain;
		$html = $html . "<tr>";
		$html = $html . "<td style='text-align: left'>LABA (RUGI) SEBELUM PAJAK </td>";
		$html = $html . "<td style='text-align: right'><b>" . $this->format_number_report($laba_rugi_sebelum_pajak) . " </b></td>";
		$html = $html . "</tr>";

		/*BEBAN PAJAK PENGHASILAN */

		$beban_pajak = 0;
		$html = $html . "<tr>";
		$html = $html . "<td style='text-align: left'>BEBAN PAJAK PENGHASILAN </td>";
		$html = $html . "<td style='text-align: right'><b>" . $this->format_number_report($beban_pajak) . " </b></td>";
		$html = $html . "</tr>";
		$totalLabaRugi = $laba_rugi_sebelum_pajak + $beban_pajak;
		$html = $html . "<tr>";
		$html = $html . "<td style='text-align: left'><b>TOTAL LABA RUGI</b></td>";
		$html = $html . "<td style='text-align: right'><b>" . $this->format_number_report($totalLabaRugi) . " </b></td>";
		$html = $html . "</tr>";
		$html = $html . "</table>";

		if ($format_laporan == 'xls') {
			echo $html;
			return;
			$reader = new \PhpOffice\PhpSpreadsheet\Reader\Html;
			$spreadsheet = $reader->loadFromString($html);
			// $reader->setSheetIndex(1);
			//$spreadhseet = $reader->loadFromString($secondHtmlString, $spreadsheet);
			$objWriter = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
			header("Pragma: public");
			header("Expires: 0");
			header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
			header("Content-Type: application/force-download");
			header("Content-Type: application/octet-stream");
			header("Content-Type: application/download");
			header("Content-Disposition: attachment;filename=test.xlsx");
			header("Content-Transfer-Encoding: binary ");

			ob_end_clean();
			ob_start();
			$objWriter->save('php://output');
		} else if ($format_laporan == 'view') {
			echo $html;
		} else {
			$filename = 'report_' . time();
			$this->pdfgenerator->generate($html, $filename, true, 'A4', 'landscape');
		}
	}
	function laporan_laba_rugi_inti_v2()
	{
		error_reporting(0);
		$lokasi_id     = $this->post('lokasi_id', true);
		$tanggal_mulai =  $this->post('tgl_mulai', true);
		$tanggal_akhir =  $this->post('tgl_akhir', true);
		$format_laporan =  $this->post('format_laporan', true);
		$judulLokasi = 'Semua';

		$this->load->helper("antech_helper");
		$this->load->library('pdfgenerator');
		if ($format_laporan == 'view') {
			$html = get_header_report_v2();
		} else {
			$html = get_header_pdf_report();
		}
		$html = $html . '
		<div class="row">
  <div class="span12">
	<br>
	<div class="kop-print">
    <div class="kop-nama">PT. ANNAJAH TECHNOLOGY INDONESIA</div>
    <div class="kop-info"> Komplek Vila Sehati B12 , Tapos - Depok</div>
    <div class="kop-info">Telp : 081387373939</div>
  </div>
	  <hr class="kop-print-hr">
  </div>
  </div>
  <h3 class="title">LAPORAN LABA RUGI</h3>
<h3>Periode: ' . $tanggal_mulai . ' s/d ' . $tanggal_akhir . '  </h3>';
		$html = $html . "
<table   border='1' width='100%' style='border-collapse: collapse;'>
<thead>
 </thead>
";

		/* QTy TBS */
		$query1 = "SELECT sum(qty_real) as jumlah FROM acc_sales_invoice a 
		INNER JOIN sls_kontrak b ON a.sls_kontrak_id=b.id
		INNER JOIN  inv_item c ON b.produk_id=c.id
		WHERE c.tipe_produk='TBS'  
		-- and a.jenis_invoice<>'UANG MUKA'
		and (a.tanggal >= '" . $tanggal_mulai . "' and a.tanggal <= '" . $tanggal_akhir . "' ) ;";
		$resJumlah  = $this->db->query($query1)->row_array();
		$jumlah = (!empty($resJumlah['jumlah'])) ? ($resJumlah['jumlah']) : 0;
		$pendapatan_usaha = $jumlah;
		$html = $html . "<tr>";
		$html = $html . "<td style='text-align: left'>Quantity TBS </td>";
		$html = $html . "<td style='text-align: right'><b>" . $this->format_number_report($jumlah) . " </b></td>";
		$html = $html . "</tr>";

		/* QTy CPO */

		$query1 = "SELECT sum(qty_real) as jumlah FROM acc_sales_invoice a 
		INNER JOIN sls_kontrak b ON a.sls_kontrak_id=b.id
		INNER JOIN  inv_item c ON b.produk_id=c.id
		WHERE c.tipe_produk='CPO' 
		-- and a.jenis_invoice<>'UANG MUKA'
		and (a.tanggal >= '" . $tanggal_mulai . "' and a.tanggal <= '" . $tanggal_akhir . "' ) ;";

		$resJumlah  = $this->db->query($query1)->row_array();
		$jumlah = (!empty($resJumlah['jumlah'])) ? ($resJumlah['jumlah']) : 0;
		$pendapatan_usaha = $jumlah;
		$html = $html . "<tr>";
		$html = $html . "<td style='text-align: left'>Quantity CPO </td>";
		$html = $html . "<td style='text-align: right'><b>" . $this->format_number_report($jumlah) . " </b></td>";
		$html = $html . "</tr>";

		/* QTy PK */
		$query1 = "SELECT sum(qty_real) as jumlah FROM acc_sales_invoice a 
		INNER JOIN sls_kontrak b ON a.sls_kontrak_id=b.id
		INNER JOIN  inv_item c ON b.produk_id=c.id
		WHERE c.tipe_produk='PK'  
		-- and a.jenis_invoice<>'UANG MUKA'
		and (a.tanggal >= '" . $tanggal_mulai . "' and a.tanggal <= '" . $tanggal_akhir . "' ) ;";
		$resJumlah  = $this->db->query($query1)->row_array();
		$jumlah = (!empty($resJumlah['jumlah'])) ? ($resJumlah['jumlah']) : 0;
		$pendapatan_usaha = $jumlah;
		$html = $html . "<tr>";
		$html = $html . "<td style='text-align: left'>Quantity PK </td>";
		$html = $html . "<td style='text-align: right'><b>" . $this->format_number_report($jumlah) . " </b></td>";
		$html = $html . "</tr>";

		/* QTy PK SHELL */
		$query1 = "SELECT sum(qty_real) as jumlah FROM acc_sales_invoice a 
		INNER JOIN sls_kontrak b ON a.sls_kontrak_id=b.id
		INNER JOIN  inv_item c ON b.produk_id=c.id
			WHERE c.tipe_produk='CANGKANG'  
			-- and a.jenis_invoice<>'UANG MUKA'
			and (a.tanggal >= '" . $tanggal_mulai . "' and a.tanggal <= '" . $tanggal_akhir . "' ) ;";
		$resJumlah  = $this->db->query($query1)->row_array();
		$jumlah = (!empty($resJumlah['jumlah'])) ? ($resJumlah['jumlah']) : 0;
		$pendapatan_usaha = $jumlah;
		$html = $html . "<tr>";
		$html = $html . "<td style='text-align: left'>Quantity PK Shell</td>";
		$html = $html . "<td style='text-align: right'><b>" . $this->format_number_report($jumlah) . " </b></td>";
		$html = $html . "</tr>";

		/* PENDAPATAN USAHA */
		if ($lokasi_id) {
			$query1 = "SELECT sum(debet-kredit)as jumlah FROM acc_jurnal_ht a inner join acc_jurnal_dt b 
			on a.id=b.jurnal_id
			inner join acc_akun c on b.acc_akun_id=c.id
			where c.kode like '5%' 
			and (a.tanggal >= '" . $tanggal_mulai . "' and a.tanggal <= '" . $tanggal_akhir . "' )
			and  b.lokasi_id=" . $lokasi_id . " ;";
		} else {
			$query1 = "SELECT sum(debet-kredit)as jumlah FROM acc_jurnal_ht a inner join acc_jurnal_dt b 
			on a.id=b.jurnal_id
			inner join acc_akun c on b.acc_akun_id=c.id
			where c.kode like '5%' 
			and (a.tanggal >= '" . $tanggal_mulai . "' and a.tanggal <= '" . $tanggal_akhir . "' )
			and  b.lokasi_id <>265 ;";
		}
		$resJumlah  = $this->db->query($query1)->row_array();
		$pendapatan_usaha = (!empty($resJumlah['jumlah'])) ? ($resJumlah['jumlah'] * -1) : 0;
		$html = $html . "<tr>";
		$html = $html . "<td style='text-align: left'>PENDAPATAN USAHA </td>";
		$html = $html . "<td style='text-align: right'>" . $this->format_number_report($pendapatan_usaha) . " </td>";
		$html = $html . "</tr>";

		/* BEBAN POKOK PENDAPATAN */
		if ($lokasi_id) {
			$query1 = "SELECT sum(debet-kredit)as jumlah FROM acc_jurnal_ht a inner join acc_jurnal_dt b 
			on a.id=b.jurnal_id
			inner join acc_akun c on b.acc_akun_id=c.id
			where c.kode like '6%' 
			and (a.tanggal >= '" . $tanggal_mulai . "' and a.tanggal <= '" . $tanggal_akhir . "' )
			and  b.lokasi_id=" . $lokasi_id . " ;";
		} else {
			$query1 = "SELECT sum(debet-kredit)as jumlah FROM acc_jurnal_ht a inner join acc_jurnal_dt b 
			on a.id=b.jurnal_id
			inner join acc_akun c on b.acc_akun_id=c.id
			where c.kode like '6%' 
			and (a.tanggal >= '" . $tanggal_mulai . "' and a.tanggal <= '" . $tanggal_akhir . "' ) 
			and  b.lokasi_id <>265;";
		}
		$resJumlah  = $this->db->query($query1)->row_array();
		$beban_pokok_pendapatan = (!empty($resJumlah['jumlah'])) ? ($resJumlah['jumlah']) : 0;
		$html = $html . "<tr>";
		$html = $html . "<td style='text-align: left'>BEBAN POKOK PENDAPATAN </td>";
		$html = $html . "<td style='text-align: right'>" . $this->format_number_report($beban_pokok_pendapatan) . " </td>";
		$html = $html . "</tr>";

		$laba_kotor = $pendapatan_usaha - $beban_pokok_pendapatan;
		$html = $html . "<tr>";
		$html = $html . "<td style='text-align: left'>LABA KOTOR </td>";
		$html = $html . "<td style='text-align: right'><b>" . $this->format_number_report($laba_kotor) . " </b></td>";
		$html = $html . "</tr>";

		/* BEBAN UMUM ADMINISTRASI */
		if ($lokasi_id) {
			$query1 = "SELECT sum(debet-kredit)as jumlah FROM acc_jurnal_ht a inner join acc_jurnal_dt b 
			on a.id=b.jurnal_id
			inner join acc_akun c on b.acc_akun_id=c.id
			where c.kode like '7%' 
			and (a.tanggal >= '" . $tanggal_mulai . "' and a.tanggal <= '" . $tanggal_akhir . "' )
			and  b.lokasi_id=" . $lokasi_id . " ;";
		} else {
			$query1 = "SELECT sum(debet-kredit)as jumlah FROM acc_jurnal_ht a inner join acc_jurnal_dt b 
			on a.id=b.jurnal_id
			inner join acc_akun c on b.acc_akun_id=c.id
			where c.kode like '7%' 
			and (a.tanggal >= '" . $tanggal_mulai . "' and a.tanggal <= '" . $tanggal_akhir . "' )
			and  b.lokasi_id <>265 ;";
		}
		$resJumlah  = $this->db->query($query1)->row_array();
		$beban_umum_administrasi = (!empty($resJumlah['jumlah'])) ? ($resJumlah['jumlah']) : 0;
		$html = $html . "<tr>";
		$html = $html . "<td style='text-align: left'> BEBAN UMUM ADMINISTRASI </td>";
		$html = $html . "<td style='text-align: right'>" . $this->format_number_report($beban_umum_administrasi) . " </td>";
		$html = $html . "</tr>";

		/* JUMLAH PENDAPATAN (BEBAN) OPERASIONAL */
		$jumlah_pendapatan_beban_operasional = $laba_kotor - $beban_umum_administrasi;
		$html = $html . "<tr>";
		$html = $html . "<td style='text-align: left'> JUMLAH PENDAPATAN (BEBAN) OPERASIONAL </td>";
		$html = $html . "<td style='text-align: right'><b>" . $this->format_number_report($jumlah_pendapatan_beban_operasional) . " </b></td>";
		$html = $html . "</tr>";

		/*Pendapatan Lain-lain */
		if ($lokasi_id) {
			$query1 = "SELECT sum(debet-kredit)as jumlah FROM acc_jurnal_ht a inner join acc_jurnal_dt b 
			on a.id=b.jurnal_id
			inner join acc_akun c on b.acc_akun_id=c.id
			where c.kode like '911%' 
			and (a.tanggal >= '" . $tanggal_mulai . "' and a.tanggal <= '" . $tanggal_akhir . "' )
			and  b.lokasi_id=" . $lokasi_id . " ;";
		} else {
			$query1 = "SELECT sum(debet-kredit)as jumlah FROM acc_jurnal_ht a inner join acc_jurnal_dt b 
			on a.id=b.jurnal_id
			inner join acc_akun c on b.acc_akun_id=c.id
			where c.kode like '911%' 
			and (a.tanggal >= '" . $tanggal_mulai . "' and a.tanggal <= '" . $tanggal_akhir . "' ) 
			and  b.lokasi_id <>265;";
		}
		$resJumlah  = $this->db->query($query1)->row_array();
		$pendapatan_lain_lain = (!empty($resJumlah['jumlah'])) ? ($resJumlah['jumlah']) : 0;
		$html = $html . "<tr>";
		$html = $html . "<td style='text-align: left'>Pendapatan Lain-lain </td>";
		$html = $html . "<td style='text-align: right'>" . $this->format_number_report($pendapatan_lain_lain) . " </td>";
		$html = $html . "</tr>";

		/* beban Lain-lain */
		if ($lokasi_id) {
			$query1 = "SELECT sum(debet-kredit)as jumlah FROM acc_jurnal_ht a inner join acc_jurnal_dt b 
			on a.id=b.jurnal_id
			inner join acc_akun c on b.acc_akun_id=c.id
			where c.kode like '912%' 
			and (a.tanggal >= '" . $tanggal_mulai . "' and a.tanggal <= '" . $tanggal_akhir . "' )
			and  b.lokasi_id=" . $lokasi_id . " ;";
		} else {
			$query1 = "SELECT sum(debet-kredit)as jumlah FROM acc_jurnal_ht a inner join acc_jurnal_dt b 
			on a.id=b.jurnal_id
			inner join acc_akun c on b.acc_akun_id=c.id
			where c.kode like '912%' 
			and (a.tanggal >= '" . $tanggal_mulai . "' and a.tanggal <= '" . $tanggal_akhir . "' ) 
			and  b.lokasi_id <>265;";
		}
		$resJumlah  = $this->db->query($query1)->row_array();
		$beban_lain_lain = (!empty($resJumlah['jumlah'])) ? ($resJumlah['jumlah']) : 0;
		$html = $html . "<tr>";
		$html = $html . "<td style='text-align: left'>beban Lain-lain </td>";
		$html = $html . "<td style='text-align: right'>" . $this->format_number_report($beban_lain_lain) . "</td>";
		$html = $html . "</tr>";

		/*Jumlah Pendapatan beban Lain-lain */

		$jumlah_pendapatan_beban_lain_lain = $pendapatan_lain_lain + $beban_lain_lain;
		$html = $html . "<tr>";
		$html = $html . "<td style='text-align: left'>Jumlah Pendapatan beban Lain-lain </td>";
		$html = $html . "<td style='text-align: right'><b>" . $this->format_number_report($jumlah_pendapatan_beban_lain_lain) . " </b></td>";
		$html = $html . "</tr>";

		/*LABA (RUGI) SEBELUM PAJAK */
		$laba_rugi_sebelum_pajak = $jumlah_pendapatan_beban_operasional - $jumlah_pendapatan_beban_lain_lain;
		$html = $html . "<tr>";
		$html = $html . "<td style='text-align: left'>LABA (RUGI) SEBELUM PAJAK </td>";
		$html = $html . "<td style='text-align: right'><b>" . $this->format_number_report($laba_rugi_sebelum_pajak) . " </b></td>";
		$html = $html . "</tr>";

		/*BEBAN PAJAK PENGHASILAN */

		$beban_pajak = 0;
		$html = $html . "<tr>";
		$html = $html . "<td style='text-align: left'>BEBAN PAJAK PENGHASILAN </td>";
		$html = $html . "<td style='text-align: right'><b>" . $this->format_number_report($beban_pajak) . " </b></td>";
		$html = $html . "</tr>";
		$totalLabaRugi = $laba_rugi_sebelum_pajak + $beban_pajak;
		$html = $html . "<tr>";
		$html = $html . "<td style='text-align: left'><b>TOTAL LABA RUGI</b></td>";
		$html = $html . "<td style='text-align: right'><b>" . $this->format_number_report($totalLabaRugi) . " </b></td>";
		$html = $html . "</tr>";
		$html = $html . "</table>";

		if ($format_laporan == 'xls') {
			$reader = new \PhpOffice\PhpSpreadsheet\Reader\Html;
			$spreadsheet = $reader->loadFromString($html);
			// $reader->setSheetIndex(1);
			//$spreadhseet = $reader->loadFromString($secondHtmlString, $spreadsheet);
			$objWriter = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
			header("Pragma: public");
			header("Expires: 0");
			header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
			header("Content-Type: application/force-download");
			header("Content-Type: application/octet-stream");
			header("Content-Type: application/download");
			header("Content-Disposition: attachment;filename=test.xlsx");
			header("Content-Transfer-Encoding: binary ");

			ob_end_clean();
			ob_start();
			$objWriter->save('php://output');
		} else if ($format_laporan == 'view') {
			echo $html;
		} else {
			$filename = 'report_' . time();
			$this->pdfgenerator->generate($html, $filename, true, 'A4', 'landscape');
		}
	}
	function laporan_buku_besar_post()
	{
		// ini_set('display_errors', 1);
		// ini_set('post_max_size', '99500M');
		// ini_set('upload_max_size', '100000M');
		// ini_set('memory_limit', '50000M');
		// ini_set('max_execution_time', '0');

		ini_set("memory_limit", "-1");
		set_time_limit(0);
		$format_laporan =  $this->post('format_laporan', true);
		$lokasi_id = $this->post('lokasi_id', true);
		// $akun_id = $this->post('akun_id', true);
		$akun_dari = $this->post('akun_dari', true);
		$akun_sampai = $this->post('akun_sampai', true);
		$tanggal_mulai =  $this->post('tgl_mulai', true);
		$tanggal_akhir = $this->post('tgl_akhir', true);


		// $akun = $this->db->query("select * from acc_akun where id=" . $akun_id . "")->row_array();
		$res_akun_dari = $this->db->query("select * from acc_akun where id=" . $akun_dari . "")->row_array();
		$res_akun_sampai = $this->db->query("select * from acc_akun where id=" . $akun_sampai . "")->row_array();
		$nama_lokasi = "Semua";
		if ($lokasi_id) {
			$lokasi = $this->db->query("select * from gbm_organisasi where id=" . $lokasi_id . ";")->row_array();
			$nama_lokasi = $lokasi['nama'];
		}
		$res_akun = $this->db->query("select * from acc_akun where kode between '" . $res_akun_dari['kode'] . "' and '" . $res_akun_sampai['kode'] . "' order by kode")->result_array();

		//$this->set_response(array("status" => "OK", "data" => $data), REST_Controller::HTTP_OK);
		$this->load->helper("terbilang");
		$this->load->helper("antech_helper");
		$this->load->library('pdfgenerator');
		if ($format_laporan == 'view') {
			$html = get_header_report_v2();
		} else {
			$html = get_header_pdf_report();
		}
		$html = $html . '<div class="row">
  <div class="span12">
	  <br>
	<div class="kop-print">
    <div class="kop-nama">PT. ANNAJAH TECHNOLOGY INDONESIA</div>
    <div class="kop-info"> Komplek Vila Sehati B12 , Tapos - Depok</div>
    <div class="kop-info">Telp : 081387373939</div>
  	</div>
	  <hr class="kop-print-hr">
  </div>
  </div>
  <h3 class="title">LAPORAN BUKU BESAR</h3>
  <table class="no_border" style="width:30%">
			
			<tr>
					<td>Lokasi</td>
					<td>:</td>
					<td>' . $nama_lokasi . '</td>
			</tr>
			<tr>	
					<td>Periode</td>
					<td>:</td>
					<td>' . $tanggal_mulai . ' s/d ' . $tanggal_akhir . '</td>
			</tr>
			<tr>	
					<td>Akun</td>
					<td>:</td>
					<td>' . $res_akun_dari['kode'] . ' - ' . $res_akun_dari['nama'] . ' s/d ' . $res_akun_sampai['kode'] . ' - ' . $res_akun_sampai['nama'] . '    </td>
			</tr>

			
	</table>
	<br>
  ';

		foreach ($res_akun as $key => $akun) {
			$akun_id = $akun['id'];
			# code...

			if ($lokasi_id) {
				$querySaldoAwal = " SELECT b.lokasi_id,b.acc_akun_id,sum(debet-kredit)as saldo FROM acc_jurnal_ht a inner join acc_jurnal_dt b 
			on a.id=b.jurnal_id
			where b.acc_akun_id=" . $akun_id . " and  b.lokasi_id=" . $lokasi_id . " and a.tanggal < '" . $tanggal_mulai . "'
			group by b.lokasi_id,b.acc_akun_id ;";
			} else {
				$querySaldoAwal = " SELECT b.lokasi_id,b.acc_akun_id,sum(debet-kredit)as saldo FROM acc_jurnal_ht a inner join acc_jurnal_dt b 
			on a.id=b.jurnal_id
			where b.acc_akun_id=" . $akun_id . " and a.tanggal < '" . $tanggal_mulai . "'
			group by b.lokasi_id,b.acc_akun_id ;";
			}


			$saldoAwal    = $this->db->query($querySaldoAwal)->row_array();
			$data['saldo_awal'] = (!empty($saldoAwal)) ? $saldoAwal['saldo'] : 0;
			//    print_r($querySaldoAwal);exit();

			if ($lokasi_id) {
				$queryTransaksi   = "SELECT 
			a.tanggal,a.no_jurnal,b.no_referensi, 
			b.lokasi_id,b.acc_akun_id,b.debet,b.kredit,b.ket ,
			c.kode as kode_blok,c.nama as nama_blok,d.kode as kode_afdeling,d.nama as nama_afdeling,
			e.kode as kode_kendaraan,e.nama as nama_kendaraan,f.kode as kode_traksi,f.nama as nama_traksi,
			b.umur_tanam_blok,g.tahuntanam
			FROM acc_jurnal_ht a inner join acc_jurnal_dt b 
			on a.id=b.jurnal_id
			left join gbm_organisasi c on b.blok_stasiun_id =c.id
			left join gbm_organisasi d on c.parent_id =d.id
			left join trk_kendaraan e on b.kendaraan_mesin_id =e.id
			left join gbm_organisasi f on f.id=e.traksi_id
			left join gbm_blok g on c.id=g.organisasi_id
			where  b.acc_akun_id=" . $akun_id . " and  b.lokasi_id=" . $lokasi_id . "
			and a.tanggal >= '" . $tanggal_mulai . "'   and a.tanggal <= '" . $tanggal_akhir . "'
			order by a.tanggal  ;
			";
			} else {
				$queryTransaksi   = "SELECT 
			a.tanggal,a.no_jurnal,b.no_referensi, 
			b.lokasi_id,b.acc_akun_id,b.debet,b.kredit,b.ket ,
			c.kode as kode_blok,c.nama as nama_blok,d.kode as kode_afdeling,d.nama as nama_afdeling,
			e.kode as kode_kendaraan,e.nama as nama_kendaraan,f.kode as kode_traksi,f.nama as nama_traksi,
			b.umur_tanam_blok,g.tahuntanam
			FROM acc_jurnal_ht a inner join acc_jurnal_dt b 
			on a.id=b.jurnal_id
			left join gbm_organisasi c on b.blok_stasiun_id =c.id
			left join gbm_organisasi d on c.parent_id =d.id
			left join trk_kendaraan e on b.kendaraan_mesin_id =e.id
			left join gbm_organisasi f on f.id=e.traksi_id
			left join gbm_blok g on c.id=g.organisasi_id
			where  b.acc_akun_id=" . $akun_id . " 
			and a.tanggal >= '" . $tanggal_mulai . "'   and a.tanggal <= '" . $tanggal_akhir . "'
			order by a.tanggal  ;
			";
			}



			$results  = $this->db->query($queryTransaksi)->result_array();
			$data['transaksi'] = $results;
			$html = $html . '<h2>' . $akun['kode'] . ' - ' . $akun['nama'] . '</h2>';
			$html = $html . ' <table  border="1" width="100%" style="border-collapse: collapse;" >
			<thead>
			<tr>
                    <th width="4%" rowspan="2">No.</th>
                    <th rowspan="2">Keterangan</th>
                    <th rowspan="2">No Jurnal</th>
					<th rowspan="2">No ref</th>
                    <th rowspan="2">Tgl</th>
					<th rowspan="2">Blok</th>
					<th rowspan="2">Afd</th>
					<th rowspan="2">Tahun Tanam</th>
					<th rowspan="2">Kendaraan</th>
                    <th colspan="2" style="text-align: center;">Transaksi</th>
                    <th colspan="2" style="text-align: center;">Saldo </th>
                   
                </tr>
				<tr>

                    <th style="text-align: center;">Dr</th>
                    <th style="text-align: center;">Cr</th>
					<th style="text-align: center;">Dr</th>
                    <th style="text-align: center;">Cr</th>
					
                   

                </tr>
		
			</thead>
			<tbody>		

			 <tr class=":: arc-content">
                    <td style="position:relative;">
                    </td>
                    <td>
                        Saldo awal
                    </td>
                    <td>
                    </td>
					<td>
                    </td>
                    <td>

                    </td>
                    <td>

                    </td>
                    <td>
                    </td>
					<td>
                    </td>
					<td>
                    </td>
					<td>
                    </td>
					<td>
                    </td>
                    <td style="text-align: right;">

                        ' . $this->format_number_report(($data['saldo_awal'] > 0) ? $data['saldo_awal'] : 0) . '

                    </td>
					<td style="text-align: right;">

					' . $this->format_number_report(($data['saldo_awal'] < 0) ? $data['saldo_awal'] * -1 : 0) . '

				</td>

                </tr>';


			$total_saldo = $data['saldo_awal'];
			$tdebet = 0;
			$tkredit = 0;
			$no = 0;
			foreach ($data['transaksi'] as $key => $m) {

				$actual_link = "http://$_SERVER[HTTP_HOST]" . "/klinik_api/api/GlobalReport/acc_laporan_jurnal?no_jurnal=" . $m['no_jurnal'] . "";
				$tdebet = $tdebet + $m['debet'];

				$tkredit = $tkredit + $m['kredit'];
				$total_saldo = $total_saldo + $m['debet'] - $m['kredit'];

				$no++;
				$html = $html . ' 	<tr class=":: arc-content">
						<td style="position:relative;">
							' . ($no) . '

						</td>
						<td>
						' . $m['ket'] . ' 
						
						</td>
						<td><a href="' . $actual_link  . '" target="_blank"> ' . $m['no_jurnal'] . ' </a>
						<td>
						' . $m['no_referensi'] . ' 
						
						</td>
						<td>
							' . $m['tanggal'] . ' 
						</td>
						<td>
							' . ($m['kode_blok']) . ' - ' . ($m['nama_blok']) . ' 
						</td>
						<td>
						' . ($m['nama_afdeling']) . ' 
						</td>
						<td>
						' . ($m['tahuntanam']) . ' - ' . ($m['umur_tanam_blok']) . ' 
						</td>
						<td>
							' . $m['kode_kendaraan'] . ' - ' . $m['nama_kendaraan'] . ' 
						</td>';


				if ($m['debet'] > 0) {

					$html = $html . '<td style="text-align: right;">' . $this->format_number_report($m['debet']) . '</td>';
				} else {
					$html = $html . '<td style="text-align: right;">-</td>';
				}
				if ($m['kredit'] > 0) {

					$html = $html . '<td style="text-align: right;">' . $this->format_number_report($m['kredit']) . '</td>';
				} else {
					$html = $html . '<td style="text-align: right;">-</td>';
				}


				$html = $html . '<td style="text-align: right;">' . $this->format_number_report($total_saldo > 0 ? $total_saldo : 0) . '</td>';
				$html = $html . '<td style="text-align: right;">' . $this->format_number_report($total_saldo < 0 ? $total_saldo * -1 : 0) . '</td>';

				$html = $html . '											
					</tr>';
			}

			$html = $html . ' 	
			<tr class=":: arc-content">
                    <td style="position:relative;">
                        &nbsp;

                    </td>
                    <td>
                        &nbsp;
                    </td>
                    <td>
                        &nbsp;
                    </td>
                    <td>
                        &nbsp;
                    </td>
					<td>
					&nbsp;
					</td>
					<td>
					&nbsp;
					</td>
					<td>
					&nbsp;
					</td>
					<td>
					&nbsp;
					</td>
					<td>
					&nbsp;
					</td>
                    <td style="text-align: right;">
                       ' . $this->format_number_report($tdebet) . '

                    </td>
                    <td style="text-align: right;">
                        ' . $this->format_number_report($tkredit) . '

                    </td>
                    <td>
                        &nbsp;
                    </td>
					<td>
                        &nbsp;
                    </td>
                </tr>
				</tbody>
				</table>
				<hr><br>';
		}
		// $filename = 'report_' . time();
		// $this->pdfgenerator->generate($html, $filename, true, 'A4', 'landscape');
		if ($format_laporan == 'xls') {
			$reader = new \PhpOffice\PhpSpreadsheet\Reader\Html;
			$spreadsheet = $reader->loadFromString($html);
			// $reader->setSheetIndex(1);
			//$spreadhseet = $reader->loadFromString($secondHtmlString, $spreadsheet);
			$objWriter = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
			header("Pragma: public");
			header("Expires: 0");
			header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
			header("Content-Type: application/force-download");
			header("Content-Type: application/octet-stream");
			header("Content-Type: application/download");
			header("Content-Disposition: attachment;filename=test.xlsx");
			header("Content-Transfer-Encoding: binary ");

			ob_end_clean();
			ob_start();
			$objWriter->save('php://output');
		} else if ($format_laporan == 'view') {
			echo $html;
		} else {
			$filename = 'report_' . time();
			$this->pdfgenerator->generate($html, $filename, true, 'A3', 'landscape');
		}
	}
	function laporan_jurnal_post()
	{

		// ini_set('display_errors', 1);
		// ini_set('post_max_size', '99500M');
		// ini_set('upload_max_size', '100000M');
		// ini_set('memory_limit', '50000M');
		// ini_set('max_execution_time', '0');
		ini_set("memory_limit", "-1");
		set_time_limit(0);
		$format_laporan =  $this->post('format_laporan', true);
		$lokasi_id = $this->post('lokasi_id', true);
		$akun_dari = $this->post('akun_dari', true);
		$akun_sampai = $this->post('akun_sampai', true);
		$tanggal_mulai =  $this->post('tgl_mulai', true);
		$tanggal_akhir = $this->post('tgl_akhir', true);
		$ket = $this->post('ket', true);
		$no_jurnal = $this->post('no_jurnal', true);
		$no_ref = $this->post('no_ref', true);
		// $lokasi_id = 263;
		// $akun_dari = 1557;
		// $akun_sampai = 2512;
		// $tanggal_mulai = '2022-01-01';
		// $tanggal_akhir = '2022-11-01';
		// $ket = '';
		// $no_jurnal = '';
		// $no_ref = '';
		$akun_1 = $this->db->query("select * from acc_akun where id=" . $akun_dari . "")->row_array();
		$akun_2 = $this->db->query("select * from acc_akun where id=" . $akun_sampai . "")->row_array();


		$queryTransaksi   = "SELECT 
		a.tanggal,a.no_jurnal,a.no_ref, b.lokasi_id,b.acc_akun_id,b.debet,b.kredit,
		b.ket ,c.kode as kode_akun,c.nama as nama_akun,
		d.kode as kode_blok,d.nama as nama_blok,e.kode as kode_afdeling,e.nama as nama_afdeling,
		f.kode as kode_kendaraan,f.nama as nama_kendaraan,g.kode as kode_traksi,g.nama as nama_traksi,
		b.umur_tanam_blok,h.tahuntanam,b.no_referensi,a.keterangan as deskripsi,
		i.kode as kode_kegiatan,i.nama as nama_kegiatan
		FROM acc_jurnal_ht a inner join acc_jurnal_dt b 
		on a.id=b.jurnal_id inner join acc_akun c on b.acc_akun_id=c.id
		left join gbm_organisasi d on b.blok_stasiun_id =d.id
		left join gbm_organisasi e on d.parent_id =e.id
		left join trk_kendaraan f on b.kendaraan_mesin_id =f.id
		left join gbm_organisasi g on g.id=f.traksi_id
		left join gbm_blok h on d.id=h.organisasi_id
		left join acc_kegiatan i on b.kegiatan_id=i.id
         where  (c.kode between '" . $akun_1['kode']  . "' and '" . $akun_2['kode'] . "') 
		 and b.lokasi_id=" . $lokasi_id . "
          and a.tanggal >= '" . $tanggal_mulai . "'   and a.tanggal <= '" . $tanggal_akhir . "'
        ";

		if ($ket) {
			$queryTransaksi = 	$queryTransaksi . " and b.ket like '%" . $ket . "%'";
		}
		if ($no_jurnal) {
			$queryTransaksi = 	$queryTransaksi . " and a.no_jurnal like '%" . $no_jurnal . "%'";
		}
		if ($no_ref) {
			$queryTransaksi = 	$queryTransaksi . " and b.no_referensi like '%" . $no_ref . "%'";
		}
		$queryTransaksi = 	$queryTransaksi . " order by a.tanggal,a.no_jurnal,b.id";

		if ($format_laporan == 'csv') {
			$this->load->helper('csv');
			$results  = $this->db->query($queryTransaksi);
			query_to_csv($query, TRUE, 'jurnal.csv');
			exit;
		}
		$results  = $this->db->query($queryTransaksi)->result_array();
		$data['transaksi'] = $results;

		$lokasi = $this->db->query("select * from gbm_organisasi where id=" . $lokasi_id . ";")->row_array();
		// $t='';
		// foreach ($results as $key => $m) {
		// 	// $t=$t. $m['no_jurnal'].'<br>';
		// 	// $x=$m;
		// }
		//  echo (count($results));

		//$this->set_response(array("status" => "OK", "data" => $data), REST_Controller::HTTP_OK);
		$this->load->helper("terbilang");
		$this->load->helper("antech_helper");
		$this->load->library('pdfgenerator');
		if ($format_laporan == 'view') {
			$html = get_header_report_v2();
		} else {
			$html = get_header_pdf_report();
		}
		$html = $html . '
<div class="row">
  <div class="span12">
	  <br>
	<div class="kop-print">
    <div class="kop-nama">PT. ANNAJAH TECHNOLOGY INDONESIA</div>
    <div class="kop-info"> Komplek Vila Sehati B12 , Tapos - Depok</div>
    <div class="kop-info">Telp : 081387373939</div>
  	</div>
	  <hr class="kop-print-hr">
  </div>
</div>
		<h3 class="title">LAPORAN JURNAL</h3>
  <table class="no_border" style="width:30%">
			
			<tr>
					<td>Lokasi</td>
					<td>:</td>
					<td>' . $lokasi['nama'] . '</td>
			</tr>
			<tr>	
					<td>Periode</td>
					<td>:</td>
					<td>' . $tanggal_mulai . ' s/d ' . $tanggal_akhir . '</td>
			</tr>
			<tr>	
					<td>Akun</td>
					<td>:</td>
					<td>' . $akun_1['kode'] . ' s/d ' . $akun_2['kode'] . '</td>
			</tr>

			
	</table>
	<br>
 ';

		$html = $html . ' <table  border="1" width="100%" style="border-collapse: collapse;" >
			<thead>
			<tr>
                    <th width="4%">No.</th>
                    <th >Akun</th>
					<th >Kegiatan</th>
					<th >Deskripsi</th>
					<th >Keterangan</th>
                    <th >No Jurnal</th>
					<th >No ref</th>
                    <th >Tgl</th>
					<th rowspan="2">Blok</th>
					<th rowspan="2">AFd</th>
					<th rowspan="2">Tahun Tanam</th>
					<th rowspan="2">Kendaraan</th>
					
                    <th  style="text-align: right;">Dr</th>
					<th  style="text-align: right;">Cr</th>                
                </tr>				
			</thead>
			<tbody>	';
		$tdebet = 0;
		$tkredit = 0;
		$no = 0;
		foreach ($data['transaksi'] as $key => $m) {
			$tdebet = $tdebet + $m['debet'];
			$tkredit = $tkredit + $m['kredit'];
			$no++;
			$html = $html . ' 	<tr class=":: arc-content">
						<td style="position:relative;">
							' . ($no) . '

						</td>
						<td>
						' . $m['kode_akun'] . '-' . $m['nama_akun'] . '
						
						</td>
						<td>
						' . $m['kode_kegiatan'] . '-' . $m['nama_kegiatan'] . '
						
						</td>
						<td>
						' . $m['deskripsi'] . ' 
						
						</td>
						<td>
						' . $m['ket'] . ' 
						
						</td>
						<td>
						' . $m['no_jurnal'] . ' 
						
						</td>
						<td>
						' . $m['no_referensi'] . ' 
						
						</td>
						<td>
							' . $m['tanggal'] . ' 
						</td>
						<td>
							' . ($m['kode_blok']) . ' - ' . ($m['nama_blok']) . ' 
						</td>
						<td>
						' . ($m['nama_afdeling']) . ' 
						</td>
						<td>
						' . ($m['tahuntanam']) . ' - ' . ($m['umur_tanam_blok']) . ' 
						</td>
						<td>
							' . $m['kode_kendaraan'] . ' - ' . $m['nama_kendaraan'] . ' 
						</td>';

			if ($m['debet'] > 0) {

				$html = $html . '<td style="text-align: right;">' . $this->format_number_report($m['debet']) . '</td>';
			} else {
				$html = $html . '<td style="text-align: right;">-</td>';
			}
			if ($m['kredit'] > 0) {

				$html = $html . '<td style="text-align: right;">' . $this->format_number_report($m['kredit']) . '</td>';
			} else {
				$html = $html . '<td style="text-align: right;">-</td>';
			}

			$html = $html . '											
					</tr>';
		}

		$html = $html . ' 	
			<tr class=":: arc-content">
                    <td style="position:relative;">
                        &nbsp;

                    </td>
                    <td>
                        &nbsp;
                    </td>
					<td>
                        &nbsp;
                    </td>
					<td>
                        &nbsp;
                    </td>
					<td>
                        &nbsp;
                    </td>
					<td>
                        &nbsp;
                    </td>
					<td>
					&nbsp;
				   </td>
                    <td>
                        &nbsp;
                    </td>
                    <td>
                        &nbsp;
                    </td>
					<td>
					&nbsp;
					</td>
					<td>
					&nbsp;
					</td>
					<td>
					&nbsp;
					</td>
                    <td style="text-align: right;">
                       ' . $this->format_number_report($tdebet) . '

                    </td>
                    <td style="text-align: right;">
                        ' . $this->format_number_report($tkredit) . '

                    </td>
                    
                </tr>
				</tbody>
				</table>
						';
		// $filename = 'report_' . time();
		// $this->pdfgenerator->generate($html, $filename, true, 'A4', 'landscape');
		if ($format_laporan == 'xls') {
			$reader = new \PhpOffice\PhpSpreadsheet\Reader\Html;
			$spreadsheet = $reader->loadFromString($html);
			// $reader->setSheetIndex(1);
			//$spreadhseet = $reader->loadFromString($secondHtmlString, $spreadsheet);
			$objWriter = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
			header("Pragma: public");
			header("Expires: 0");
			header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
			header("Content-Type: application/force-download");
			header("Content-Type: application/octet-stream");
			header("Content-Type: application/download");
			header("Content-Disposition: attachment;filename=test.xlsx");
			header("Content-Transfer-Encoding: binary ");

			ob_end_clean();
			ob_start();
			$objWriter->save('php://output');
		} else if ($format_laporan == 'view') {
			echo $html;
			// echo count($data['transaksi']);
		} else {
			$filename = 'report_' . time();
			$this->pdfgenerator->generate($html, $filename, true, 'A3', 'landscape');
		}
	}

	function export_jurnal_post()
	{

		// ini_set('display_errors', 1);
		// ini_set('post_max_size', '99500M');
		// ini_set('upload_max_size', '100000M');
		// ini_set('memory_limit', '50000M');
		// ini_set('max_execution_time', '0');
		ini_set("memory_limit", "-1");
		set_time_limit(0);
		$lokasi_id = $this->post('lokasi_id', true);

		$tanggal_mulai =  $this->post('tgl_mulai', true);
		$tanggal_akhir = $this->post('tgl_akhir', true);
		$queryTransaksi   = "SELECT 
		lok.kode as lokasi,a.tanggal,a.no_jurnal,a.no_ref,
		c.kode as kode_akun,c.nama as nama_akun,b.ket ,b.debet,b.kredit,
		d.kode as kode_blok,d.nama as nama_blok,e.kode as kode_afdeling,e.nama as nama_afdeling,
		f.kode as kode_kendaraan,f.nama as nama_kendaraan,g.kode as kode_traksi,g.nama as nama_traksi,
		b.umur_tanam_blok,h.tahuntanam,b.no_referensi,a.keterangan as deskripsi,
		i.kode as kode_kegiatan,i.nama as nama_kegiatan
		FROM acc_jurnal_ht a inner join acc_jurnal_dt b on a.id=b.jurnal_id 
		inner join acc_akun c on b.acc_akun_id=c.id
		inner join gbm_organisasi lok on b.lokasi_id=lok.id
		left join gbm_organisasi d on b.blok_stasiun_id =d.id
		left join gbm_organisasi e on d.parent_id =e.id
		left join trk_kendaraan f on b.kendaraan_mesin_id =f.id
		left join gbm_organisasi g on g.id=f.traksi_id
		left join gbm_blok h on d.id=h.organisasi_id
		left join acc_kegiatan i on b.kegiatan_id=i.id
         where  1=1  
          and a.tanggal >= '" . $tanggal_mulai . "'   and a.tanggal <= '" . $tanggal_akhir . "'
        ";

		if ($lokasi_id) {
			$queryTransaksi = 	$queryTransaksi . " and b.lokasi_id = " . $lokasi_id . " ";
		}

		$queryTransaksi = 	$queryTransaksi . " order by a.tanggal,a.no_jurnal,b.id";

		$this->load->helper('csv');
		$results  = $this->db->query($queryTransaksi);
		query_to_csv($results, TRUE, 'jurnal.csv');
	}
	function laporan_buku_besar_get($tanggal_mulai, $tanggal_akhir, $akun_id, $lokasi_id)
	{
		$format_laporan = 'view';
		// $format_laporan =  $this->post('format_laporan', true);
		// $lokasi_id = $this->post('lokasi_id', true);
		// $akun_id = $this->post('akun_id', true);
		// $tanggal_mulai =  $this->post('tgl_mulai', true);
		// $tanggal_akhir = $this->post('tgl_akhir', true);

		if ($lokasi_id && $lokasi_id != null && $lokasi_id != '') {
			$querySaldoAwal = " SELECT b.lokasi_id,b.acc_akun_id,sum(debet-kredit)as saldo FROM acc_jurnal_ht a inner join acc_jurnal_dt b 
		on a.id=b.jurnal_id
        where b.acc_akun_id=" . $akun_id . " and  b.lokasi_id=" . $lokasi_id . " and a.tanggal < '" . $tanggal_mulai . "'
         group by b.lokasi_id,b.acc_akun_id ;";
		} else {
			$querySaldoAwal = " SELECT b.lokasi_id,b.acc_akun_id,sum(debet-kredit)as saldo FROM acc_jurnal_ht a inner join acc_jurnal_dt b 
		on a.id=b.jurnal_id
        where b.acc_akun_id=" . $akun_id . "  and a.tanggal < '" . $tanggal_mulai . "'
         group by b.lokasi_id,b.acc_akun_id ;";
		}


		$saldoAwal    = $this->db->query($querySaldoAwal)->row_array();
		$data['saldo_awal'] = (!empty($saldoAwal)) ? $saldoAwal['saldo'] : 0;
		//    print_r($querySaldoAwal);exit();

		if ($lokasi_id && $lokasi_id != null && $lokasi_id != '') {
			$queryTransaksi   = "SELECT a.tanggal,a.no_jurnal,b.no_referensi, b.lokasi_id,b.acc_akun_id,b.debet,b.kredit,b.ket  FROM acc_jurnal_ht a inner join acc_jurnal_dt b 
		on a.id=b.jurnal_id
         where  b.acc_akun_id=" . $akun_id . " and  b.lokasi_id=" . $lokasi_id . "
          and a.tanggal >= '" . $tanggal_mulai . "'   and a.tanggal <= '" . $tanggal_akhir . "'
          order by a.tanggal  ;
          ";
		} else {
			$queryTransaksi   = "SELECT a.tanggal,a.no_jurnal,b.no_referensi, b.lokasi_id,b.acc_akun_id,b.debet,b.kredit,b.ket  FROM acc_jurnal_ht a inner join acc_jurnal_dt b 
		on a.id=b.jurnal_id
         where  b.acc_akun_id=" . $akun_id . " 
          and a.tanggal >= '" . $tanggal_mulai . "'   and a.tanggal <= '" . $tanggal_akhir . "'
          order by a.tanggal  ;
          ";
		}

		$results  = $this->db->query($queryTransaksi)->result_array();
		$data['transaksi'] = $results;

		$akun = $this->db->query("select * from acc_akun where id=" . $akun_id . "")->row_array();
		$lokasi = $this->db->query("select * from gbm_organisasi where id=" . $lokasi_id . ";")->row_array();

		//$this->set_response(array("status" => "OK", "data" => $data), REST_Controller::HTTP_OK);
		$this->load->helper("terbilang");
		$this->load->helper("antech_helper");
		$this->load->library('pdfgenerator');
		if ($format_laporan == 'view') {
			$html = get_header_report();
		} else {
			$html = get_header_pdf_report();
		}
		$html = $html . '<div class="row">
  <div class="span12">
	  <br>
	  <div class="kop-print">
		 </div>
	  <hr class="kop-print-hr">
  </div>
  </div>
  <h2>Laporan Buku Besar</h2>
  <h3>Periode  : ' . $tanggal_mulai . ' s/d ' . $tanggal_akhir . '</h3>
  <h3>Lokasi   : ' . $lokasi['nama'] . '</h3>
  <h3>' . $akun['kode'] . ' - ' . $akun['nama'] . '</h3>';

		$html = $html . ' <table  border="1" width="100%" style="border-collapse: collapse;" >
			<thead>
			<tr>
                    <th width="4%" rowspan="2">No.</th>
                    <th rowspan="2">Keterangan</th>
                    <th rowspan="2">No Jurnal</th>
					<th rowspan="2">No ref</th>
                    <th rowspan="2">Tgl</th>
                    <th colspan="2" style="text-align: center;">Transaksi</th>
                    <th colspan="2" style="text-align: center;">Saldo </th>
                   
                </tr>
				<tr>

                    <th style="text-align: center;">Dr</th>
                    <th style="text-align: center;">Cr</th>
					<th style="text-align: center;">Dr</th>
                    <th style="text-align: center;">Cr</th>
					
                   

                </tr>
		
			</thead>
			<tbody>		

			 <tr class=":: arc-content">
                    <td style="position:relative;">
                    </td>
                    <td>
                        Saldo awal
                    </td>
                    <td>
                    </td>
					<td>
                    </td>
                    <td>

                    </td>
                    <td>

                    </td>
                    <td>

                    </td>

                    <td style="text-align: right;">

                        ' . $this->format_number_report(($data['saldo_awal'] > 0) ? $data['saldo_awal'] : 0) . '

                    </td>
					<td style="text-align: right;">

					' . $this->format_number_report(($data['saldo_awal'] < 0) ? $data['saldo_awal'] * -1 : 0) . '

				</td>

                </tr>';


		$total_saldo = $data['saldo_awal'];
		$tdebet = 0;
		$tkredit = 0;
		$no = 0;
		foreach ($data['transaksi'] as $key => $m) {


			$tdebet = $tdebet + $m['debet'];

			$tkredit = $tkredit + $m['kredit'];
			$total_saldo = $total_saldo + $m['debet'] - $m['kredit'];

			$no++;
			$html = $html . ' 	<tr class=":: arc-content">
						<td style="position:relative;">
							' . ($no) . '

						</td>
						<td>
						' . $m['ket'] . ' 
						
						</td>
						<td>
						' . $m['no_jurnal'] . ' 
						
						</td>
						<td>
						' . $m['no_referensi'] . ' 
						
						</td>
						<td>
							' . $m['tanggal'] . ' 
						</td>';

			if ($m['debet'] > 0) {

				$html = $html . '<td style="text-align: right;">' . $this->format_number_report($m['debet']) . '</td>';
			} else {
				$html = $html . '<td style="text-align: right;">-</td>';
			}
			if ($m['kredit'] > 0) {

				$html = $html . '<td style="text-align: right;">' . $this->format_number_report($m['kredit']) . '</td>';
			} else {
				$html = $html . '<td style="text-align: right;">-</td>';
			}


			$html = $html . '<td style="text-align: right;">' . $this->format_number_report($total_saldo > 0 ? $total_saldo : 0) . '</td>';
			$html = $html . '<td style="text-align: right;">' . $this->format_number_report($total_saldo < 0 ? $total_saldo * -1 : 0) . '</td>';

			$html = $html . '											
					</tr>';
		}

		$html = $html . ' 	
			<tr class=":: arc-content">
                    <td style="position:relative;">
                        &nbsp;

                    </td>
                    <td>
                        &nbsp;
                    </td>
                    <td>
                        &nbsp;
                    </td>
                    <td>
                        &nbsp;
                    </td>
					<td>
					&nbsp;
					</td>
                    <td style="text-align: right;">
                       ' . $this->format_number_report($tdebet) . '

                    </td>
                    <td style="text-align: right;">
                        ' . $this->format_number_report($tkredit) . '

                    </td>
                    <td>
                        &nbsp;
                    </td>
					<td>
                        &nbsp;
                    </td>
                </tr>
				</tbody>
				</table>
						';
		// $filename = 'report_' . time();
		// $this->pdfgenerator->generate($html, $filename, true, 'A4', 'landscape');
		if ($format_laporan == 'xls') {
			$reader = new \PhpOffice\PhpSpreadsheet\Reader\Html;
			$spreadsheet = $reader->loadFromString($html);
			// $reader->setSheetIndex(1);
			//$spreadhseet = $reader->loadFromString($secondHtmlString, $spreadsheet);
			$objWriter = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
			header("Pragma: public");
			header("Expires: 0");
			header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
			header("Content-Type: application/force-download");
			header("Content-Type: application/octet-stream");
			header("Content-Type: application/download");
			header("Content-Disposition: attachment;filename=test.xlsx");
			header("Content-Transfer-Encoding: binary ");

			ob_end_clean();
			ob_start();
			$objWriter->save('php://output');
		} else if ($format_laporan == 'view') {
			echo $html;
		} else {
			$filename = 'report_' . time();
			$this->pdfgenerator->generate($html, $filename, true, 'A4', 'landscape');
		}
	}
	function laporan_cost_blok_detail_post()
	{

		error_reporting(0);
		$estate_id     = $this->post('estate_id', true);
		$tgl_mulai =  $this->post('tgl_mulai', true);
		$tgl_akhir = $this->post('tgl_akhir', true);
		$blok_id = $this->post('blok_id', true);
		$format_laporan     = $this->post('format_laporan', true);


		$retrieveEstate = $this->db->query("select * from gbm_organisasi where id=" . $estate_id . "")->row_array();
		$retrieveBlok = $this->db->query("select * from gbm_organisasi where id=" . $blok_id . "")->row_array();

		$retrieveCost = $this->db->query("SELECT tanggal,kode_blok,nama_blok,nama_afdeling,nama_rayon,nama_estate,
			SUM(debet-kredit)AS total,
			sum(CASE WHEN jenis = 'UPAH' THEN (debet-kredit) else 0 end) as upah,
				sum(CASE WHEN jenis = 'BAHAN' THEN (debet-kredit) else 0 end) as bahan,
					sum(CASE WHEN jenis = '' THEN (debet-kredit) else 0 end) as lainnya
			FROM est_cost_blok_vw 
			where kode_estate='" . $retrieveEstate['kode'] . "' 
			and kode_blok='" . $retrieveBlok['kode'] . "' and tanggal>='" . $tgl_mulai . "' and tanggal<='" . $tgl_akhir . "'
			GROUP BY  tanggal, kode_blok,nama_blok,nama_afdeling,nama_rayon,nama_estate		
			 order by kode_blok,tanggal")->result_array();

		// var_dump($results);
		$this->load->helper("terbilang");
		$this->load->helper("antech_helper");
		$this->load->library('pdfgenerator');
		if ($format_laporan == 'pdf') {
			$html = get_header_pdf_report();
		} else {
			$html = get_header_report_v2();
		}


		$html = $html . '
	<div class="row">
  	<div class="span12">
	  <br>
	<div class="kop-print">
	  <div class="kop-nama">PT. ANNAJAH TECHNOLOGY INDONESIA</div>
	  <div class="kop-info"> Komplek Vila Sehati B12 , Tapos - Depok</div>
	  <div class="kop-info">Telp : 081387373939</div>
	</div>
	  <hr class="kop-print-hr">
  	</div>
  	</div>
  <h3 class="title">LAPORAN RINCIAN PER BLOK</h3>
  <table class="no_border" style="width:30%">
			
			<tr>
					<td>ESTATE</td>
					<td>:</td>
					<td>' .  $retrieveEstate['nama'] . '</td>
			</tr>
			<tr>
					<td>BLOK</td>
					<td>:</td>
					<td>' .  $retrieveBlok['nama'] . '</td>
			</tr>
			<tr>	
					<td>PERIODE</td>
					<td>:</td>
					<td>' . $tgl_mulai . ' s/d ' . $tgl_akhir . '</td>
			</tr>
			
	</table>
			<br>';

		$html = $html . ' <table  border="1" width="100%" style="border-collapse: collapse;" >
			<thead>
				<tr>
				<th style="text-align: center;" width="4%">No.</th>			
				<th>Tanggal</th>
				<th style="text-align: center;">Upah </th>
				<th style="text-align: center;">Bahan</th>
				<th style="text-align: center;">Lainnya </th>
				<th style="text-align: center;">Total </th>
				
				</tr>
			</thead>
			<tbody>';


		$no = 0;
		$bahan = 0;
		$lainnya = 0;
		$upah = 0;
		$total = 0;


		foreach ($retrieveCost as $key => $m) {
			$no++;
			$upah = $upah + $m['upah'];
			$bahan = $bahan + $m['bahan'];
			$lainnya = $lainnya + $m['lainnya'];
			$total = $total + $m['total'];
			$html = $html . ' 	<tr class=":: arc-content">
						<td style="position:relative; text-align: center;">	' . ($no) . '</td>
						<td>' . tgl_indo_normal($m['tanggal']) . ' </td>
						
						<td style="text-align: right;">' . $this->format_number_report($m['upah']) . ' 
						<td style="text-align: right;">' . $this->format_number_report($m['bahan']) . ' 
						<td style="text-align: right;">' . $this->format_number_report($m['lainnya']) . ' 
						<td style="text-align: right;">' . $this->format_number_report($m['total']) . ' 
													
						</td>';

			$html = $html . '</tr>';
		}

		$html = $html . ' 	
						<tr class=":: arc-content">
						<td style="position:relative;">
							&nbsp;

						</td>
						<td>
							&nbsp;
						</td>
					
						<td style="text-align: right;">
						' . $this->format_number_report($upah) . ' 
						</td>
						<td style="text-align: right;">
						' . $this->format_number_report($bahan) . ' 
						</td>
						<td style="text-align: right;">
						' . $this->format_number_report($lainnya) . ' 
						</td>
						<td style="text-align: right;">
						' . $this->format_number_report($total) . ' 
						</td>
						
						
						</tr>
								</tbody>
							</table>
						';
		if ($format_laporan == 'xls') {
			$reader = new \PhpOffice\PhpSpreadsheet\Reader\Html;
			$spreadsheet = $reader->loadFromString($html);
			// $reader->setSheetIndex(1);
			//$spreadhseet = $reader->loadFromString($secondHtmlString, $spreadsheet);
			$objWriter = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
			header("Pragma: public");
			header("Expires: 0");
			header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
			header("Content-Type: application/force-download");
			header("Content-Type: application/octet-stream");
			header("Content-Type: application/download");
			header("Content-Disposition: attachment;filename=test.xlsx");
			header("Content-Transfer-Encoding: binary ");

			ob_end_clean();
			ob_start();
			$objWriter->save('php://output');
		} else if ($format_laporan == 'view') {
			echo $html;
		} else {
			$filename = 'report_' . time();
			$this->pdfgenerator->generate($html, $filename, true, 'A4', 'landscape');
		}
	}
	function laporan_cost_blok_rekap_post()
	{

		error_reporting(0);
		$estate_id     = $this->post('estate_id', true);
		$tgl_mulai =  $this->post('tgl_mulai', true);
		$tgl_akhir = $this->post('tgl_akhir', true);
		// $blok_id = $this->post('blok_id', true);
		$format_laporan     = $this->post('format_laporan', true);


		$retrieveEstate = $this->db->query("select * from gbm_organisasi where id=" . $estate_id . "")->row_array();
		//$retrieveBlok = $this->db->query("select * from gbm_organisasi where id=" . $blok_id . "")->row_array();

		$retrieveCost = $this->db->query("SELECT kode_blok,nama_blok,nama_afdeling,
		nama_rayon,nama_estate,tahuntanam,intiplasma,
			SUM(debet-kredit)AS total,
			sum(CASE WHEN jenis = 'UPAH' THEN (debet-kredit) else 0 end) as upah,
				sum(CASE WHEN jenis = 'BAHAN' THEN (debet-kredit) else 0 end) as bahan,
					sum(CASE WHEN jenis = '' THEN (debet-kredit) else 0 end) as lainnya
			FROM est_cost_blok_vw 
			where kode_estate='" . $retrieveEstate['kode'] . "' 
			and tanggal>='" . $tgl_mulai . "' and tanggal<='" . $tgl_akhir . "'
			GROUP BY  kode_blok,nama_blok,nama_afdeling,nama_rayon,nama_estate,tahuntanam,intiplasma		
			 order by kode_blok")->result_array();

		// var_dump($results);
		$this->load->helper("terbilang");
		$this->load->helper("antech_helper");
		$this->load->library('pdfgenerator');
		if ($format_laporan == 'pdf') {
			$html = get_header_pdf_report();
		} else {
			$html = get_header_report_v2();
		}


		$html = $html . '
		<div class="row">
  	<div class="span12">
	  <br>
	<div class="kop-print">
	  <div class="kop-nama">PT. ANNAJAH TECHNOLOGY INDONESIA</div>
	  <div class="kop-info"> Komplek Vila Sehati B12 , Tapos - Depok</div>
	  <div class="kop-info">Telp : 081387373939</div>
	</div>
	  <hr class="kop-print-hr">
  	</div>
  	</div>
  <h3 class="title">LAPORAN REKAP COST BLOK</h3>
  <table class="no_border" style="width:30%">
			
			<tr>
					<td>ESTATE</td>
					<td>:</td>
					<td>' .  $retrieveEstate['nama'] . '</td>
			</tr>
			
			<tr>	
					<td>PERIODE</td>
					<td>:</td>
					<td>' . $tgl_mulai . ' s/d ' . $tgl_akhir . '</td>
			</tr>
			
	</table>
			<br>';

		$html = $html . ' <table   border="1" width="100%" style="border-collapse: collapse;">
			<thead>
				<tr>
				<th style="text-align: center;" width="4%">No.</th>			
				<th>Afdeling</th>
				<th>Blok</th>
				<th>Tahun Tanam</th>
				<th>Inti/Plasma</th>
				<th style="text-align: center;">Upah </th>
				<th style="text-align: center;">Bahan</th>
				<th style="text-align: center;">Lainnya </th>
				<th style="text-align: center;">Total </th>
				
				</tr>
			</thead>
			<tbody>';


		$no = 0;
		$bahan = 0;
		$lainnya = 0;
		$upah = 0;
		$total = 0;


		foreach ($retrieveCost as $key => $m) {
			$no++;
			$upah = $upah + $m['upah'];
			$bahan = $bahan + $m['bahan'];
			$lainnya = $lainnya + $m['lainnya'];
			$total = $total + $m['total'];
			$html = $html . ' 	<tr class=":: arc-content">
						<td style="position:relative; text-align: center;">	' . ($no) . '</td>
						<td>
						' . $m['nama_afdeling'] . ' 
						
						</td>
						<td style="text-align: center;">
						' . $m['nama_blok'] . ' 
						
						</td>
						<td style="text-align: center;">
							' . $m['tahuntanam'] . ' 
						</td>
						<td style="text-align: center;">
							' . $m['intiplasma'] . ' 
						</td>
						
						<td style="text-align: right;">' . $this->format_number_report($m['upah']) . ' 
						<td style="text-align: right;">' . $this->format_number_report($m['bahan']) . ' 
						<td style="text-align: right;">' . $this->format_number_report($m['lainnya']) . ' 
						<td style="text-align: right;">' . $this->format_number_report($m['total']) . ' 
													
						</td>';

			$html = $html . '</tr>';
		}

		$html = $html . ' 	
						<tr class=":: arc-content">
						<td style="position:relative;">
							&nbsp;

						</td>
						
						<td>
							&nbsp;
						</td>
						<td>
							&nbsp;
						</td>
						<td>
							&nbsp;
						</td>
						<td>
							&nbsp;
						</td>
					
					
						<td style="text-align: right;">
						' . $this->format_number_report($upah) . ' 
						</td>
						<td style="text-align: right;">
						' . $this->format_number_report($bahan) . ' 
						</td>
						<td style="text-align: right;">
						' . $this->format_number_report($lainnya) . ' 
						</td>
						<td style="text-align: right;">
						' . $this->format_number_report($total) . ' 
						</td>
						
						
						</tr>
								</tbody>
							</table>
						';
		if ($format_laporan == 'xls') {
			$reader = new \PhpOffice\PhpSpreadsheet\Reader\Html;
			$spreadsheet = $reader->loadFromString($html);
			// $reader->setSheetIndex(1);
			//$spreadhseet = $reader->loadFromString($secondHtmlString, $spreadsheet);
			$objWriter = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
			header("Pragma: public");
			header("Expires: 0");
			header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
			header("Content-Type: application/force-download");
			header("Content-Type: application/octet-stream");
			header("Content-Type: application/download");
			header("Content-Disposition: attachment;filename=test.xlsx");
			header("Content-Transfer-Encoding: binary ");

			ob_end_clean();
			ob_start();
			$objWriter->save('php://output');
		} else if ($format_laporan == 'view') {
			echo $html;
		} else {
			$filename = 'report_' . time();
			$this->pdfgenerator->generate($html, $filename, true, 'A4', 'landscape');
		}
	}


	function laporan_cost_afdeling_post()
	{

		error_reporting(0);
		$estate_id     = $this->post('estate_id', true);
		$tgl_mulai =  $this->post('tgl_mulai', true);
		$tgl_akhir = $this->post('tgl_akhir', true);
		$afdeling_id = $this->post('afdeling_id', true);
		$format_laporan     = $this->post('format_laporan', true);

		$nama_afdeling = "Semua";
		$retrieveEstate = $this->db->query("select * from gbm_organisasi where id=" . $estate_id . "")->row_array();
		if ($afdeling_id) {
			$retrieveAfdeling = $this->db->query("select * from gbm_organisasi where id=" . $afdeling_id . "")->row_array();
			$nama_afdeling = $retrieveAfdeling['nama'];
			$retrieve_Ha = $this->db->query("SELECT sum(b.luasareaproduktif)AS ha FROM gbm_organisasi a   INNER  JOIN gbm_blok b ON a.id=b.organisasi_id
			INNER JOIN gbm_organisasi c ON a.parent_id=c.id INNER JOIN  gbm_organisasi d ON c.parent_id=d.id
			INNER JOIN  gbm_organisasi e ON d.parent_id=e.id where c.id=" . $afdeling_id . "")->row_array();

			// $retrieve_kg = $this->db->query("SELECT SUM(hasil_kerja_brondolan)AS brondolan_kg,SUM(hasil_kerja_kg)AS jjg_kg 
			// from est_bkm_panen_dt a inner JOIN est_bkm_panen_ht h ON a.bkm_panen_id=h.id
			// INNER JOIN gbm_organisasi b ON a.blok_id=b.id
			// INNER JOIN gbm_blok c ON b.id=c.organisasi_id
			// INNER JOIN gbm_organisasi d ON b.parent_id=d.id INNER JOIN  gbm_organisasi e ON d.parent_id=e.id
			// INNER JOIN  gbm_organisasi f ON e.parent_id=f.id
			//  where d.id=" . $afdeling_id . "
			//  and h.tanggal>='" . $tgl_mulai . "' and h.tanggal<='" . $tgl_akhir . "'")->row_array();
			$retrieve_kg = $this->db->query("SELECT SUM(jum_kg_pks)AS kg,SUM(jum_ha)AS ha FROM est_produksi_panen_ht a
			INNER JOIN est_produksi_panen_dt b ON a.id=b.produksi_panen_id
			INNER JOIN gbm_organisasi c ON a.divisi_id=c.id
			INNER JOIN gbm_organisasi d ON c.parent_id=d.id 
			where a.divisi_id=" . $afdeling_id . " 
			and a.tanggal>='" . $tgl_mulai . "' and a.tanggal<='" . $tgl_akhir . "' ")->row_array();
		} else {
			$retrieve_Ha = $this->db->query("SELECT sum(b.luasareaproduktif)AS ha FROM gbm_organisasi a   INNER  JOIN gbm_blok b ON a.id=b.organisasi_id
			INNER JOIN gbm_organisasi c ON a.parent_id=c.id INNER JOIN  gbm_organisasi d ON c.parent_id=d.id
			INNER JOIN  gbm_organisasi e ON d.parent_id=e.id where e.id=" . $estate_id . "")->row_array();

			// $retrieve_kg = $this->db->query("SELECT SUM(hasil_kerja_brondolan)AS brondolan_kg,SUM(hasil_kerja_kg)AS jjg_kg
			//  from est_bkm_panen_dt a inner JOIN est_bkm_panen_ht h ON a.bkm_panen_id=h.id
			//  INNER JOIN gbm_organisasi b ON a.blok_id=b.id
			// INNER JOIN gbm_blok c ON b.id=c.organisasi_id
			// INNER JOIN gbm_organisasi d ON b.parent_id=d.id INNER JOIN  gbm_organisasi e ON d.parent_id=e.id
			// INNER JOIN  gbm_organisasi f ON e.parent_id=f.id where f.id=" . $estate_id . " 
			// and h.tanggal>='" . $tgl_mulai . "' and h.tanggal<='" . $tgl_akhir . "' ")->row_array();
			$retrieve_kg = $this->db->query("SELECT SUM(jum_kg_pks)AS kg,SUM(jum_ha)AS ha FROM est_produksi_panen_ht a
			INNER JOIN est_produksi_panen_dt b ON a.id=b.produksi_panen_id
			INNER JOIN gbm_organisasi c ON a.divisi_id=c.id
			INNER JOIN gbm_organisasi d ON c.parent_id=d.id 
			where d.parent_id=" . $estate_id . " 
			and a.tanggal>='" . $tgl_mulai . "' and a.tanggal<='" . $tgl_akhir . "' ")->row_array();
		}
		$luas_ha = 0;
		$jumlah_kg = 0;
		if ($retrieve_Ha) {
			$luas_ha = $retrieve_Ha['ha'];
		}
		if ($retrieve_kg) {
			$jumlah_kg = $retrieve_kg['kg'] ? +$retrieve_kg['kg'] : 0;
		}


		$this->load->helper("terbilang");
		$this->load->helper("antech_helper");
		$this->load->library('pdfgenerator');
		if ($format_laporan == 'pdf') {
			$html = get_header_pdf_report();
		} else {
			$html = get_header_report_v2();
		}


		$html = $html . '
		<div class="row">
  	<div class="span12">
	  <br>
	<div class="kop-print">
	  <div class="kop-nama">PT. ANNAJAH TECHNOLOGY INDONESIA</div>
	  <div class="kop-info"> Komplek Vila Sehati B12 , Tapos - Depok</div>
	  <div class="kop-info">Telp : 081387373939</div>
	</div>
	  <hr class="kop-print-hr">
  	</div>
  	</div>
  <h3 class="title">LAPORAN RINCIAN COST ESTATE </h3>
  <table class="no_border" style="width:45%">
			
			<tr>
					<td>ESTATE</td>
					<td>:</td>
					<td>' .  $retrieveEstate['nama'] . '</td>
					<td>PRODUKSI</td>
					<td>:</td>
					<td>' . number_format($jumlah_kg) . '</td>
					
			</tr>
			
			<tr>	

					<td>AFDELING</td>
					<td>:</td>
					<td>' .  $nama_afdeling . '</td>
					<td>LUAS</td>
					<td>:</td>
					<td>' . number_format($luas_ha) . '</td>
					
			</tr>
			<tr>
					<td>PERIODE</td>
					<td>:</td>
					<td>' . $tgl_mulai . ' s/d ' . $tgl_akhir . '</td>
			</tr>
			
	</table><br>';

		$html = $html . ' <table   border="1" width="100%" style="border-collapse: collapse;">
			<thead>
				<tr>
				<th style="text-align: center;" width="4%">No.</th>			
				<th>Kode Akun</th>
				<th>Nama Akun</th>
				<th style="text-align: center;">Biaya </th>
				<th style="text-align: center;">Cost/Ha</th>
				<th style="text-align: center;">Cost/Kg </th>
				
				</tr>
			</thead>
			<tbody>';

		$ha = 0;
		$produksi = 0;
		$total_biaya = 0;
		$total_cost_per_ha = 0;
		$total_cost_per_kg = 0;
		$kelompok_biaya = array('PNN' => 'Panen', 'PML' => 'Perawatan', 'PMK' => 'Pemupukan');
		foreach ($kelompok_biaya as $key => $kel) {

			$sql = "";
			/* COST PANEN,Perawatan,Pemupukan  */
			if ($retrieveAfdeling) {
				$sql = "SELECT kode_akun,nama_akun,nama_estate,
				SUM(debet-kredit)AS jumlah_biaya
				FROM est_cost_blok_vw 
				where kode_estate='" . $retrieveEstate['kode'] . "' 
				and kode_lokasi_jurnal='" . $retrieveEstate['kode'] . "'
				and kode_afdeling='" . $retrieveAfdeling['kode'] . "' and tanggal>='" . $tgl_mulai . "' and tanggal<='" . $tgl_akhir . "'
				and kelompok_biaya='" . $key . "'
				GROUP BY   kode_akun,nama_akun, kode_afdeling,nama_afdeling,nama_rayon,nama_estate		
				order by kode_akun";
			} else {
				$sql = "SELECT kode_akun,nama_akun,nama_estate,
				SUM(debet-kredit)AS jumlah_biaya
				FROM est_cost_blok_vw 
				where kode_estate='" . $retrieveEstate['kode'] . "' 
				and kode_lokasi_jurnal='" . $retrieveEstate['kode'] . "'
				and tanggal>='" . $tgl_mulai . "' and tanggal<='" . $tgl_akhir . "'
				and kelompok_biaya='" . $key . "'
				GROUP BY   kode_akun,nama_akun, nama_estate		
				order by kode_akun";
			}

			$retrieveCost = $this->db->query($sql)->result_array();
			$no = 0;
			$jumlah_biaya = 0;

			$total = 0;
			$jumlah_cost_per_ha = 0;
			$jumlah_cost_per_kg = 0;

			foreach ($retrieveCost as $key => $m) {
				$no++;
				$cost_per_ha = ($luas_ha == 0 || $m['jumlah_biaya'] == 0) ? 0 : ($m['jumlah_biaya'] / $luas_ha);
				$cost_per_kg = ($jumlah_kg == 0 || $m['jumlah_biaya'] == 0) ? 0 : ($m['jumlah_biaya'] / $jumlah_kg);
				$jumlah_biaya = $jumlah_biaya + $m['jumlah_biaya'];
				$jumlah_cost_per_ha = $jumlah_cost_per_ha + $cost_per_ha;
				$jumlah_cost_per_kg = $jumlah_cost_per_kg + $cost_per_kg;
				$total_biaya = $total_biaya + $m['jumlah_biaya'];
				$total_cost_per_ha = $total_cost_per_ha + $cost_per_ha;
				$total_cost_per_kg = $total_cost_per_kg + $cost_per_kg;
				$html = $html . ' 	<tr class=":: arc-content">
						<td style="position:relative; text-align: center;">	' . ($no) . '</td>
						<td>' . $m['kode_akun'] . ' </td>
						<td>' . $m['nama_akun'] . ' </td>
						<td style="text-align: right;">' . $this->format_number_report($m['jumlah_biaya']) . ' 
						<td style="text-align: right;">' . $this->format_number_report($cost_per_ha) . ' 
						<td style="text-align: right;">' . $this->format_number_report($cost_per_kg) . ' 									
						</td>';
				$html = $html . '</tr>';
			}
			$html = $html . ' 	<tr class=":: arc-content">
		<td colspan=3 style="position:relative;"><b> Total By ' . $kel . '	</b> </td>
		<td style="text-align: right;"><b> ' . $this->format_number_report($jumlah_biaya) . ' </b>
		<td style="text-align: right;"><b> ' . $this->format_number_report($jumlah_cost_per_ha) . '</b> 
		<td style="text-align: right;"><b> ' . $this->format_number_report($jumlah_cost_per_kg) . ' </b>									
		</td> </tr>';
			/* END COST   */
		}

		$html = $html . ' 	
			<tr class=":: arc-content">
			<td  colspan=3 style="position:relative;">
			<b> 	TOTAL</b> 

			</td>
			
			<td style="text-align: right;">
			<b> ' . $this->format_number_report($total_biaya) . ' </b> 
			</td>
			<td style="text-align: right;">
			<b> ' . $this->format_number_report($total_cost_per_ha) . ' </b> 
			</td>
			<td style="text-align: right;">
			<b> ' . $this->format_number_report($total_cost_per_kg) . ' </b> 
			</td>
			
			
			
			</tr>
					</tbody>
				</table>
			';
		if ($format_laporan == 'xls') {
			$reader = new \PhpOffice\PhpSpreadsheet\Reader\Html;
			$spreadsheet = $reader->loadFromString($html);
			// $reader->setSheetIndex(1);
			//$spreadhseet = $reader->loadFromString($secondHtmlString, $spreadsheet);
			$objWriter = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
			header("Pragma: public");
			header("Expires: 0");
			header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
			header("Content-Type: application/force-download");
			header("Content-Type: application/octet-stream");
			header("Content-Type: application/download");
			header("Content-Disposition: attachment;filename=test.xlsx");
			header("Content-Transfer-Encoding: binary ");

			ob_end_clean();
			ob_start();
			$objWriter->save('php://output');
		} else if ($format_laporan == 'view') {
			echo $html;
		} else {
			$filename = 'report_' . time();
			$this->pdfgenerator->generate($html, $filename, true, 'A4', 'landscape');
		}
	}
	function laporan_cost_afdeling__by_tipe_kegiatan_post()
	{

		error_reporting(0);
		$estate_id     = $this->post('estate_id', true);
		$tgl_mulai =  $this->post('tgl_mulai', true);
		$tgl_akhir = $this->post('tgl_akhir', true);
		$afdeling_id = $this->post('afdeling_id', true);
		$format_laporan     = $this->post('format_laporan', true);

		$nama_afdeling = "Semua";
		$retrieveEstate = $this->db->query("select * from gbm_organisasi where id=" . $estate_id . "")->row_array();
		if ($afdeling_id) {
			$retrieveAfdeling = $this->db->query("select * from gbm_organisasi where id=" . $afdeling_id . "")->row_array();
			$nama_afdeling = $retrieveAfdeling['nama'];
			$retrieve_Ha = $this->db->query("SELECT sum(b.luasareaproduktif)AS ha FROM gbm_organisasi a   INNER  JOIN gbm_blok b ON a.id=b.organisasi_id
			INNER JOIN gbm_organisasi c ON a.parent_id=c.id INNER JOIN  gbm_organisasi d ON c.parent_id=d.id
			INNER JOIN  gbm_organisasi e ON d.parent_id=e.id where c.id=" . $afdeling_id . "")->row_array();

			$retrieve_kg = $this->db->query("SELECT SUM(hasil_kerja_brondolan)AS brondolan_kg,SUM(hasil_kerja_kg)AS jjg_kg 
			from est_bkm_panen_dt a inner JOIN est_bkm_panen_ht h ON a.bkm_panen_id=h.id
			INNER JOIN gbm_organisasi b ON a.blok_id=b.id
			INNER JOIN gbm_blok c ON b.id=c.organisasi_id
			INNER JOIN gbm_organisasi d ON b.parent_id=d.id INNER JOIN  gbm_organisasi e ON d.parent_id=e.id
			INNER JOIN  gbm_organisasi f ON e.parent_id=f.id
			 where d.id=" . $afdeling_id . "
			 and h.tanggal>='" . $tgl_mulai . "' and h.tanggal<='" . $tgl_akhir . "'")->row_array();
		} else {
			$retrieve_Ha = $this->db->query("SELECT sum(b.luasareaproduktif)AS ha FROM gbm_organisasi a   INNER  JOIN gbm_blok b ON a.id=b.organisasi_id
			INNER JOIN gbm_organisasi c ON a.parent_id=c.id INNER JOIN  gbm_organisasi d ON c.parent_id=d.id
			INNER JOIN  gbm_organisasi e ON d.parent_id=e.id where e.id=" . $estate_id . "")->row_array();

			$retrieve_kg = $this->db->query("SELECT SUM(hasil_kerja_brondolan)AS brondolan_kg,SUM(hasil_kerja_kg)AS jjg_kg
			 from est_bkm_panen_dt a inner JOIN est_bkm_panen_ht h ON a.bkm_panen_id=h.id
			 INNER JOIN gbm_organisasi b ON a.blok_id=b.id
			INNER JOIN gbm_blok c ON b.id=c.organisasi_id
			INNER JOIN gbm_organisasi d ON b.parent_id=d.id INNER JOIN  gbm_organisasi e ON d.parent_id=e.id
			INNER JOIN  gbm_organisasi f ON e.parent_id=f.id where f.id=" . $estate_id . " 
			and h.tanggal>='" . $tgl_mulai . "' and h.tanggal<='" . $tgl_akhir . "' ")->row_array();
		}
		$luas_ha = 0;
		$jumlah_kg = 0;
		if ($retrieve_Ha) {
			$luas_ha = $retrieve_Ha['ha'];
		}
		if ($retrieve_kg) {
			$jumlah_kg = $retrieve_kg['brondolan_kg'] + $retrieve_kg['jjg_kg'];
		}


		$this->load->helper("terbilang");
		$this->load->helper("antech_helper");
		$this->load->library('pdfgenerator');
		if ($format_laporan == 'pdf') {
			$html = get_header_pdf_report();
		} else {
			$html = get_header_report_v2();
		}


		$html = $html . '
		<div class="row">
  	<div class="span12">
	  <br>
	<div class="kop-print">
	  <div class="kop-nama">PT. ANNAJAH TECHNOLOGY INDONESIA</div>
	  <div class="kop-info"> Komplek Vila Sehati B12 , Tapos - Depok</div>
	  <div class="kop-info">Telp : 081387373939</div>
	</div>
	  <hr class="kop-print-hr">
  	</div>
  	</div>
  <h3 class="title">LAPORAN RINCIAN COST</h3>
  <table class="no_border" style="width:45%">
			
			<tr>
					<td>ESTATE</td>
					<td>:</td>
					<td>' .  $retrieveEstate['nama'] . '</td>
					<td>PRODUKSI</td>
					<td>:</td>
					<td>' . number_format($jumlah_kg) . '</td>
					
			</tr>
			
			<tr>	

					<td>AFDELING</td>
					<td>:</td>
					<td>' .  $nama_afdeling . '</td>
					<td>LUAS</td>
					<td>:</td>
					<td>' . number_format($luas_ha) . '</td>
					
			</tr>
			<tr>
					<td>PERIODE</td>
					<td>:</td>
					<td>' . tgl_indo($tgl_mulai) . ' s/d ' . tgl_indo($tgl_akhir) . '</td>
			</tr>
			
	</table><br>';

		$html = $html . ' <table   border="1" width="100%" style="border-collapse: collapse;">
			<thead>
				<tr>
				<th width="4%">No.</th>			
				<th>Kode Kegiatan</th>
				<th>Nama Kegiatan</th>
				<th style="text-align: right;">Biaya </th>
				<th style="text-align: right;">Cost/Ha</th>
				<th style="text-align: right;">Cost/Kg </th>
				
				</tr>
			</thead>
			<tbody>';

		$ha = 0;
		$produksi = 0;
		$total_biaya = 0;
		$total_cost_per_ha = 0;
		$total_cost_per_kg = 0;
		$kelompok_biaya = array('PNN' => 'Panen', 'PML' => 'Perawatan', 'PMK' => 'Pemupukan');
		foreach ($kelompok_biaya as $key => $kel) {

			$sql = "";
			/* COST PANEN,Perawatan,Pemupukan  */
			if ($retrieveAfdeling) {
				$sql = "SELECT kode_kelompok_kegiatan,nama_kelompok_kegiatan,kode_kegiatan,nama_kegiatan,nama_estate,
				SUM(debet-kredit)AS jumlah_biaya
				FROM est_cost_blok_vw 
				where kode_estate='" . $retrieveEstate['kode'] . "' 
				and kode_afdeling='" . $retrieveAfdeling['kode'] . "' and tanggal>='" . $tgl_mulai . "' and tanggal<='" . $tgl_akhir . "'
				and tipe_kegiatan='" . $key . "'
				GROUP BY   kode_kelompok_kegiatan,nama_kelompok_kegiatan,kode_kegiatan,nama_kegiatan, kode_afdeling,nama_afdeling,nama_rayon,nama_estate		
				order by nama_kegiatan";
			} else {
				$sql = "SELECT kode_kelompok_kegiatan,nama_kelompok_kegiatan,kode_kegiatan,nama_kegiatan,nama_estate,
				SUM(debet-kredit)AS jumlah_biaya
				FROM est_cost_blok_vw 
				where kode_estate='" . $retrieveEstate['kode'] . "' 
				and tanggal>='" . $tgl_mulai . "' and tanggal<='" . $tgl_akhir . "'
				and tipe_kegiatan='" . $key . "'
				GROUP BY   kode_kelompok_kegiatan,nama_kelompok_kegiatan,kode_kegiatan,nama_kegiatan, nama_estate		
				order by nama_kegiatan";
			}

			$retrieveCost = $this->db->query($sql)->result_array();
			$no = 0;
			$jumlah_biaya = 0;

			$total = 0;
			$jumlah_cost_per_ha = 0;
			$jumlah_cost_per_kg = 0;

			foreach ($retrieveCost as $key => $m) {
				$no++;
				$cost_per_ha = ($luas_ha == 0 || $m['jumlah_biaya'] == 0) ? 0 : ($m['jumlah_biaya'] / $luas_ha);
				$cost_per_kg = ($jumlah_kg == 0 || $m['jumlah_biaya'] == 0) ? 0 : ($m['jumlah_biaya'] / $jumlah_kg);
				$jumlah_biaya = $jumlah_biaya + $m['jumlah_biaya'];
				$jumlah_cost_per_ha = $jumlah_cost_per_ha + $cost_per_ha;
				$jumlah_cost_per_kg = $jumlah_cost_per_kg + $cost_per_kg;
				$total_biaya = $total_biaya + $m['jumlah_biaya'];
				$total_cost_per_ha = $total_cost_per_ha + $cost_per_ha;
				$total_cost_per_kg = $total_cost_per_kg + $cost_per_kg;
				$html = $html . ' 	<tr class=":: arc-content">
						<td style="position:relative;">	' . ($no) . '</td>
						<td>' . $m['kode_kegiatan'] . ' </td>
						<td>' . $m['nama_kegiatan'] . ' </td>
						<td style="text-align: right;">' . $this->format_number_report($m['jumlah_biaya']) . ' 
						<td style="text-align: right;">' . $this->format_number_report($cost_per_ha) . ' 
						<td style="text-align: right;">' . $this->format_number_report($cost_per_kg) . ' 									
						</td>';
				$html = $html . '</tr>';
			}
			$html = $html . ' 	<tr class=":: arc-content">
		<td colspan=3 style="position:relative;"><b> Total By ' . $kel . '	</b> </td>
		<td style="text-align: right;"><b> ' . $this->format_number_report($jumlah_biaya) . ' </b>
		<td style="text-align: right;"><b> ' . $this->format_number_report($jumlah_cost_per_ha) . '</b> 
		<td style="text-align: right;"><b> ' . $this->format_number_report($jumlah_cost_per_kg) . ' </b>									
		</td> </tr>';
			/* END COST   */
		}

		$html = $html . ' 	
			<tr class=":: arc-content">
			<td  colspan=3 style="position:relative;">
			<b> 	TOTAL</b> 

			</td>
			
			<td style="text-align: right;">
			<b> ' . $this->format_number_report($total_biaya) . ' </b> 
			</td>
			<td style="text-align: right;">
			<b> ' . $this->format_number_report($total_cost_per_ha) . ' </b> 
			</td>
			<td style="text-align: right;">
			<b> ' . $this->format_number_report($total_cost_per_kg) . ' </b> 
			</td>
			
			
			
			</tr>
					</tbody>
				</table>
			';
		if ($format_laporan == 'xls') {
			$reader = new \PhpOffice\PhpSpreadsheet\Reader\Html;
			$spreadsheet = $reader->loadFromString($html);
			// $reader->setSheetIndex(1);
			//$spreadhseet = $reader->loadFromString($secondHtmlString, $spreadsheet);
			$objWriter = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
			header("Pragma: public");
			header("Expires: 0");
			header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
			header("Content-Type: application/force-download");
			header("Content-Type: application/octet-stream");
			header("Content-Type: application/download");
			header("Content-Disposition: attachment;filename=test.xlsx");
			header("Content-Transfer-Encoding: binary ");

			ob_end_clean();
			ob_start();
			$objWriter->save('php://output');
		} else if ($format_laporan == 'view') {
			echo $html;
		} else {
			$filename = 'report_' . time();
			$this->pdfgenerator->generate($html, $filename, true, 'A4', 'landscape');
		}
	}
	function laporan_cost_stasiun_post()
	{

		error_reporting(0);
		$mill_id    = $this->post('mill_id', true);
		$tgl_mulai =  $this->post('tgl_mulai', true);
		$tgl_akhir = $this->post('tgl_akhir', true);
		$stasiun_id = $this->post('stasiun_id', true);
		$format_laporan     = $this->post('format_laporan', true);

		$nama_afdeling = "Semua";
		$retrieveMill = $this->db->query("select * from gbm_organisasi where id=" . $mill_id . "")->row_array();
		if ($stasiun_id) {
			$retrieveStasiun = $this->db->query("select * from gbm_organisasi where id=" . $stasiun_id . "")->row_array();
			$nama_stasiun = $retrieveStasiun['nama'];
		} else {
		}
		$luas_ha = 0;
		$jumlah_kg = 0;
		// if ($retrieve_Ha) {
		// 	$luas_ha = $retrieve_Ha['ha'];
		// }
		// if ($retrieve_kg) {
		// 	$jumlah_kg = $retrieve_kg['brondolan_kg'] + $retrieve_kg['jjg_kg'];
		// }


		$this->load->helper("terbilang");
		$this->load->helper("antech_helper");
		$this->load->library('pdfgenerator');
		if ($format_laporan == 'pdf') {
			$html = get_header_pdf_report();
		} else {
			$html = get_header_report_v2();
		}


		$html = $html . '
		<div class="row">
  	<div class="span12">
	  <br>
	<div class="kop-print">
	  <div class="kop-nama">PT. ANNAJAH TECHNOLOGY INDONESIA</div>
	  <div class="kop-info"> Komplek Vila Sehati B12 , Tapos - Depok</div>
	  <div class="kop-info">Telp : 081387373939</div>
	</div>
	  <hr class="kop-print-hr">
  	</div>
  	</div>
  <h3 class="title">Laporan Cost Stasiun </h3>
  <table class="no_border" style="width:35%">
			
			<tr>
					<td>Mill</td>
					<td>:</td>
					<td>' .  $retrieveMill['nama'] . '</td>
					
			</tr>
			<tr>
					<td>Periode</td>
					<td>:</td>
					<td>' . tgl_indo($tgl_mulai) . ' s/d ' . tgl_indo($tgl_akhir) . '</td>
			</tr>
			
	</table><br>
		';

		$html = $html . ' <table  >
			<thead>
				<tr>	
				<th>No Akun</th>
				<th>Nama Akun</th>
				<th>Biaya (Rp)</th>				
				</tr>
			</thead>
			<tbody>';

		$ha = 0;
		$produksi = 0;
		$total_biaya = 0;
		$total_cost_per_ha = 0;
		$total_cost_per_kg = 0;
		$head_akun_stasiun = '6330';
		$kelompok_biaya = array('63102', '63201', '63202', '63203', '63204', '63205', '63301', '63302', '63303', '63304', '63305', '63306', '63401');
		$no = 0;
		foreach ($kelompok_biaya as $key => $kel) {
			$retrieveHead = $this->db->query("select * from acc_akun where kode='" . $kel . "'")->row_array();
			$retrieveAkun = $this->db->query("select * from acc_akun where kode like '" . $kel . "%' and is_transaksi_akun=1")->result_array();
			$sql = "";
			$jum_biaya_by_stasiun = 0;

			foreach ($retrieveAkun as $key => $akun) {
				/* COST   */
				if ($stasiun_id) {
					$sql = "SELECT b.acc_akun_id,c.kode,c.nama, sum(debet-kredit)as jumlah_biaya
					FROM acc_jurnal_ht a 
					inner join acc_jurnal_dt b on a.id=b.jurnal_id 
					INNER JOIN acc_akun c ON b.acc_akun_id=c.id
					where b.lokasi_id='" . $mill_id . "' 
					and a.tanggal>='" . $tgl_mulai . "' and a.tanggal<='" . $tgl_akhir . "'
					and c.kode ='" . $akun['kode'] . "'
					GROUP BY b.acc_akun_id,c.kode,c.nama
					order by c.kode";
				} else {
					$sql = "SELECT b.acc_akun_id,c.kode,c.nama, sum(debet-kredit)as jumlah_biaya
					FROM acc_jurnal_ht a 
					inner join acc_jurnal_dt b on a.id=b.jurnal_id 
					INNER JOIN acc_akun c ON b.acc_akun_id=c.id
					where b.lokasi_id='" . $mill_id . "' 
					and a.tanggal>='" . $tgl_mulai . "' and a.tanggal<='" . $tgl_akhir . "'
					and c.kode ='" . $akun['kode'] . "'
					GROUP BY b.acc_akun_id,c.kode,c.nama
					order by c.kode";
				}
				// var_dump($sql);
				// exit();
				$retrieveCost = $this->db->query($sql)->row_array();


				$jumlah_biaya = 0;
				if ($retrieveCost) {
					$jumlah_biaya = $retrieveCost['jumlah_biaya'];
				}
				$jum_biaya_by_stasiun =	$jum_biaya_by_stasiun + $jumlah_biaya;
				$total_biaya = $total_biaya + $jumlah_biaya;

				$no++;

				$html = $html . ' 	<tr class=":: arc-content">
						<td>' . $akun['kode'] . ' </td>
						<td>' . $akun['nama'] . ' </td>
						<td style="text-align: right;">' . $this->format_number_report($jumlah_biaya) . ' 							
						</td>';
				$html = $html . '</tr>';
			}
			$html = $html . ' 	<tr class=":: arc-content">
		<td colspan=2 style="position:relative;"><b> Total By ' . $retrieveHead['nama'] . '	</b> </td>
		<td style="text-align: right;"><b> ' . $this->format_number_report($jum_biaya_by_stasiun) . ' </b>
										
		</td> </tr>';
			/* END COST   */
		}

		$html = $html . ' 	
			<tr class=":: arc-content">
			<td  colspan=2 style="position:relative;">
			<b> 	TOTAL</b> 

			</td>
			
			<td style="text-align: right;">
			<b> ' . $this->format_number_report($total_biaya) . ' </b> 
			</td>
				
			</tr>
					</tbody>
				</table>
			';
		if ($format_laporan == 'xls') {
			$reader = new \PhpOffice\PhpSpreadsheet\Reader\Html;
			$spreadsheet = $reader->loadFromString($html);
			// $reader->setSheetIndex(1);
			//$spreadhseet = $reader->loadFromString($secondHtmlString, $spreadsheet);
			$objWriter = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
			header("Pragma: public");
			header("Expires: 0");
			header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
			header("Content-Type: application/force-download");
			header("Content-Type: application/octet-stream");
			header("Content-Type: application/download");
			header("Content-Disposition: attachment;filename=test.xlsx");
			header("Content-Transfer-Encoding: binary ");

			ob_end_clean();
			ob_start();
			$objWriter->save('php://output');
		} else if ($format_laporan == 'view') {
			echo $html;
		} else {
			$filename = 'report_' . time();
			$this->pdfgenerator->generate($html, $filename, true, 'A4', 'landscape');
		}
	}
	function laporan_cost_afdeling_bgt_post()
	{

		error_reporting(0);
		// $estate_id     = $this->post('estate_id', true);
		// $tgl_mulai =  $this->post('tgl_mulai', true);
		// $tgl_akhir = $this->post('tgl_akhir', true);
		$nama_bulan     = $this->post('nama_bulan', true);
		$tahun     = $this->post('tahun', true);
		$estate_id     = $this->post('estate_id', true);
		$periode =  $this->post('periode', true);
		$tgl_mulai =  $periode . "-01";
		$d2 = new DateTime($tgl_mulai);
		$d2->modify('last day of this month');
		$tgl_akhir = $d2->format('Y-m-d');
		$afdeling_id = $this->post('afdeling_id', true);
		$format_laporan     = $this->post('format_laporan', true);

		$nama_afdeling = "Semua";
		$retrieveEstate = $this->db->query("select * from gbm_organisasi where id=" . $estate_id . "")->row_array();
		if ($afdeling_id) {
			$retrieveAfdeling = $this->db->query("select * from gbm_organisasi where id=" . $afdeling_id . "")->row_array();
			$nama_afdeling = $retrieveAfdeling['nama'];
			$retrieve_Ha = $this->db->query("SELECT sum(b.luasareaproduktif)AS ha FROM gbm_organisasi a   INNER  JOIN gbm_blok b ON a.id=b.organisasi_id
			INNER JOIN gbm_organisasi c ON a.parent_id=c.id INNER JOIN  gbm_organisasi d ON c.parent_id=d.id
			INNER JOIN  gbm_organisasi e ON d.parent_id=e.id where c.id=" . $afdeling_id . "")->row_array();

			$retrieve_kg = $this->db->query("SELECT SUM(hasil_kerja_brondolan)AS brondolan_kg,SUM(hasil_kerja_kg)AS jjg_kg 
			from est_bkm_panen_dt a inner JOIN est_bkm_panen_ht h ON a.bkm_panen_id=h.id
			INNER JOIN gbm_organisasi b ON a.blok_id=b.id
			INNER JOIN gbm_blok c ON b.id=c.organisasi_id
			INNER JOIN gbm_organisasi d ON b.parent_id=d.id INNER JOIN  gbm_organisasi e ON d.parent_id=e.id
			INNER JOIN  gbm_organisasi f ON e.parent_id=f.id
			 where d.id=" . $afdeling_id . "
			 and h.tanggal>='" . $tgl_mulai . "' and h.tanggal<='" . $tgl_akhir . "'")->row_array();
		} else {
			$retrieve_Ha = $this->db->query("SELECT sum(b.luasareaproduktif)AS ha FROM gbm_organisasi a   INNER  JOIN gbm_blok b ON a.id=b.organisasi_id
			INNER JOIN gbm_organisasi c ON a.parent_id=c.id INNER JOIN  gbm_organisasi d ON c.parent_id=d.id
			INNER JOIN  gbm_organisasi e ON d.parent_id=e.id where e.id=" . $estate_id . "")->row_array();

			$retrieve_kg = $this->db->query("SELECT SUM(hasil_kerja_brondolan)AS brondolan_kg,SUM(hasil_kerja_kg)AS jjg_kg
			 from est_bkm_panen_dt a inner JOIN est_bkm_panen_ht h ON a.bkm_panen_id=h.id
			 INNER JOIN gbm_organisasi b ON a.blok_id=b.id
			INNER JOIN gbm_blok c ON b.id=c.organisasi_id
			INNER JOIN gbm_organisasi d ON b.parent_id=d.id INNER JOIN  gbm_organisasi e ON d.parent_id=e.id
			INNER JOIN  gbm_organisasi f ON e.parent_id=f.id where f.id=" . $estate_id . " 
			and h.tanggal>='" . $tgl_mulai . "' and h.tanggal<='" . $tgl_akhir . "' ")->row_array();
		}
		$luas_ha = 0;
		$jumlah_kg = 0;
		if ($retrieve_Ha) {
			$luas_ha = $retrieve_Ha['ha'];
		}
		if ($retrieve_kg) {
			$jumlah_kg = $retrieve_kg['brondolan_kg'] + $retrieve_kg['jjg_kg'];
		}


		$this->load->helper("terbilang");
		$this->load->helper("antech_helper");
		$this->load->library('pdfgenerator');
		if ($format_laporan == 'pdf') {
			$html = get_header_pdf_report();
		} else {
			$html = get_header_report();
		}


		$html = $html . '
		<h2>Laporan Rincian Cost </h2>
		<h3>Estate:' . $retrieveEstate['nama'] . ' </h3>
		<h3>Afd:' . $nama_afdeling . ' </h3>
		<h4>' . $nama_bulan . ' - ' . $tahun . ' </h4>
		<h4>Luas: ' . number_format($luas_ha) . ' </h4>
		<h4>Produksi: ' . number_format($jumlah_kg) .  ' </h4>';

		$html = $html . ' <table  >
			<thead>
				<tr>
					<th rowspan=2 width="4%">No.</th>			
					<th rowspan=2>Kode Kegiatan</th>
					<th rowspan=2>Nama Kegiatan</th>
					<th colspan=3>Real</th>
					<th colspan=3>Budget</th>
				</tr>
				<tr>
					<th style="text-align: right;">Biaya </th>
					<th style="text-align: right;">Cost/Ha</th>
					<th style="text-align: right;">Cost/Kg </th>
					<th style="text-align: right;">Biaya </th>
					<th style="text-align: right;">Cost/Ha</th>
					<th style="text-align: right;">Cost/Kg </th>
				
				</tr>
			</thead>
			<tbody>';

		$ha = 0;
		$produksi = 0;
		$total_biaya = 0;
		$total_cost_per_ha = 0;
		$total_cost_per_kg = 0;
		$kelompok_biaya = array('PNN' => 'Panen', 'PML' => 'Perawatan', 'PMK' => 'Pemupukan');
		foreach ($kelompok_biaya as $key => $kel) {

			$sql = "";
			/* COST PANEN,Perawatan,Pemupukan  */
			if ($retrieveAfdeling) {
				$sql = "SELECT kode_kelompok_kegiatan,nama_kelompok_kegiatan,kode_kegiatan,nama_kegiatan,nama_estate,
				SUM(debet-kredit)AS jumlah_biaya
				FROM est_cost_blok_vw 
				where kode_estate='" . $retrieveEstate['kode'] . "' 
				and kode_afdeling='" . $retrieveAfdeling['kode'] . "' and tanggal>='" . $tgl_mulai . "' and tanggal<='" . $tgl_akhir . "'
				and tipe_kegiatan='" . $key . "'
				GROUP BY   kode_kelompok_kegiatan,nama_kelompok_kegiatan,kode_kegiatan,nama_kegiatan, kode_afdeling,nama_afdeling,nama_rayon,nama_estate		
				order by nama_kegiatan";
			} else {
				$sql = "SELECT kode_kelompok_kegiatan,nama_kelompok_kegiatan,kode_kegiatan,nama_kegiatan,nama_estate,
				SUM(debet-kredit)AS jumlah_biaya
				FROM est_cost_blok_vw 
				where kode_estate='" . $retrieveEstate['kode'] . "' 
				and tanggal>='" . $tgl_mulai . "' and tanggal<='" . $tgl_akhir . "'
				and tipe_kegiatan='" . $key . "'
				GROUP BY   kode_kelompok_kegiatan,nama_kelompok_kegiatan,kode_kegiatan,nama_kegiatan, nama_estate		
				order by nama_kegiatan";
			}

			$retrieveCost = $this->db->query($sql)->result_array();
			$no = 0;
			$jumlah_biaya = 0;

			$total = 0;
			$jumlah_cost_per_ha = 0;
			$jumlah_cost_per_kg = 0;

			foreach ($retrieveCost as $key => $m) {
				$no++;
				$cost_per_ha = ($luas_ha == 0 || $m['jumlah_biaya'] == 0) ? 0 : ($m['jumlah_biaya'] / $luas_ha);
				$cost_per_kg = ($jumlah_kg == 0 || $m['jumlah_biaya'] == 0) ? 0 : ($m['jumlah_biaya'] / $jumlah_kg);
				$jumlah_biaya = $jumlah_biaya + $m['jumlah_biaya'];
				$jumlah_cost_per_ha = $jumlah_cost_per_ha + $cost_per_ha;
				$jumlah_cost_per_kg = $jumlah_cost_per_kg + $cost_per_kg;
				$total_biaya = $total_biaya + $m['jumlah_biaya'];
				$total_cost_per_ha = $total_cost_per_ha + $cost_per_ha;
				$total_cost_per_kg = $total_cost_per_kg + $cost_per_kg;
				$html = $html . ' 	<tr class=":: arc-content">
						<td style="position:relative;">	' . ($no) . '</td>
						<td>' . $m['kode_kegiatan'] . ' </td>
						<td>' . $m['nama_kegiatan'] . ' </td>
						<td style="text-align: right;">' . $this->format_number_report($m['jumlah_biaya']) . ' 
						<td style="text-align: right;">' . $this->format_number_report($cost_per_ha) . ' 
						<td style="text-align: right;">' . $this->format_number_report($cost_per_kg) . ' 									
						<td style="text-align: right;"><b> ' . $this->format_number_report(0) . ' </b>
						<td style="text-align: right;"><b> ' . $this->format_number_report(0) . '</b> 
						<td style="text-align: right;"><b> ' . $this->format_number_report(0) . ' </b>	
						</td>';
				$html = $html . '</tr>';
			}
			$html = $html . ' 	<tr class=":: arc-content">
		<td colspan=3 style="position:relative;"><b> Total By ' . $kel . '	</b> </td>
		<td style="text-align: right;"><b> ' . $this->format_number_report($jumlah_biaya) . ' </b>
		<td style="text-align: right;"><b> ' . $this->format_number_report($jumlah_cost_per_ha) . '</b> 
		<td style="text-align: right;"><b> ' . $this->format_number_report($jumlah_cost_per_kg) . ' </b>
		<td style="text-align: right;"><b> ' . $this->format_number_report(0) . ' </b>
		<td style="text-align: right;"><b> ' . $this->format_number_report(0) . '</b> 
		<td style="text-align: right;"><b> ' . $this->format_number_report(0) . ' </b>									
		</td> </tr>';
			/* END COST   */
		}

		$html = $html . ' 	
			<tr class=":: arc-content">
			<td  colspan=3 style="position:relative;">
			<b> 	TOTAL</b> 

			</td>
			
			<td style="text-align: right;">
				<b> ' . $this->format_number_report($total_biaya) . ' </b> 
			</td>
			<td style="text-align: right;">
				<b> ' . $this->format_number_report($total_cost_per_ha) . ' </b> 
			</td>
			<td style="text-align: right;">
				<b> ' . $this->format_number_report($total_cost_per_kg) . ' </b> 
			</td>
			<td style="text-align: right;">
				<b> ' . $this->format_number_report(0) . ' </b> 
			</td>
			<td style="text-align: right;">
				<b> ' . $this->format_number_report(0) . ' </b> 
			</td>
			<td style="text-align: right;">
				<b> ' . $this->format_number_report(0) . ' </b> 
			</td>
			
			
			
			</tr>
					</tbody>
				</table>
			';
		if ($format_laporan == 'xls') {
			$reader = new \PhpOffice\PhpSpreadsheet\Reader\Html;
			$spreadsheet = $reader->loadFromString($html);
			// $reader->setSheetIndex(1);
			//$spreadhseet = $reader->loadFromString($secondHtmlString, $spreadsheet);
			$objWriter = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
			header("Pragma: public");
			header("Expires: 0");
			header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
			header("Content-Type: application/force-download");
			header("Content-Type: application/octet-stream");
			header("Content-Type: application/download");
			header("Content-Disposition: attachment;filename=test.xlsx");
			header("Content-Transfer-Encoding: binary ");

			ob_end_clean();
			ob_start();
			$objWriter->save('php://output');
		} else if ($format_laporan == 'view') {
			echo $html;
		} else {
			$filename = 'report_' . time();
			$this->pdfgenerator->generate($html, $filename, true, 'A4', 'landscape');
		}
	}

	/* Laporan Cost Price By Estete + Detail Afdeling */
	function laporan_cost_afdeling_rekap_post()
	{

		error_reporting(0);
		$estate_id     = $this->post('estate_id', true);
		$tgl_mulai =  $this->post('tgl_mulai', true);
		$tgl_akhir = $this->post('tgl_akhir', true);
		$format_laporan     = $this->post('format_laporan', true);
		$retrieveEstate = $this->db->query("select * from gbm_organisasi where id=" . $estate_id . "")->row_array();

		$this->load->helper("terbilang");
		$this->load->helper("antech_helper");
		$this->load->library('pdfgenerator');
		if ($format_laporan == 'pdf') {
			$html = get_header_pdf_report();
		} else {
			$html = get_header_report_v2();
		}
		$html = $html . '
		<div class="row">
  	<div class="span12">
	  <br>
	<div class="kop-print">
	  <div class="kop-nama">PT. ANNAJAH TECHNOLOGY INDONESIA</div>
	  <div class="kop-info"> Komplek Vila Sehati B12 , Tapos - Depok</div>
	  <div class="kop-info">Telp : 081387373939</div>
	</div>
	  <hr class="kop-print-hr">
  	</div>
  	</div>
  <h3 class="title">LAPORAN COST PRICE</h3>
  <table class="no_border" style="width:35%">
			
			<tr>
					<td>ESTATE</td>
					<td>:</td>
					<td>' .  $retrieveEstate['nama'] . '</td>
			</tr>
			<tr>	
					<td>PERIODE</td>
					<td>:</td>
					<td>' . $tgl_mulai . ' s/d ' . $tgl_akhir . '</td>
			</tr>
			
	</table>
			<br>
		';

		$html = $html . ' <table  >
			<thead>
				<tr>
				<th>Kelompok Biaya</th>
				<th style="text-align: center;">Biaya </th>
				<th style="text-align: center;">Cost/Kg </th>	
				<th style="text-align: center;">Produksi(Kg) </th>				
				</tr>
			</thead>
			<tbody>';
		$retrieveAfdeling = $this->db->query("select a.id,a.kode,a.nama from gbm_organisasi a inner join gbm_organisasi b on a.parent_id=b.id where a.tipe='AFDELING'
		 and b.parent_id=" . $estate_id . " and a.nama like '%AFDELING%'")->result_array();

		$total_kg = 0;
		/* BY AFDELING */
		foreach ($retrieveAfdeling as $key => $afd) {
			$jum_biaya = 0;
			$retrieve_Ha = $this->db->query("SELECT sum(b.luasareaproduktif)AS ha FROM gbm_organisasi a   INNER  JOIN gbm_blok b ON a.id=b.organisasi_id
			INNER JOIN gbm_organisasi c ON a.parent_id=c.id INNER JOIN  gbm_organisasi d ON c.parent_id=d.id
			INNER JOIN  gbm_organisasi e ON d.parent_id=e.id where c.id=" . $afd['id'] . "")->row_array();

			// $retrieve_kg = $this->db->query("SELECT SUM(hasil_kerja_brondolan)AS brondolan_kg,SUM(hasil_kerja_kg)AS jjg_kg 
			// from est_bkm_panen_dt a inner JOIN est_bkm_panen_ht h ON a.bkm_panen_id=h.id
			// INNER JOIN gbm_organisasi b ON a.blok_id=b.id
			// INNER JOIN gbm_blok c ON b.id=c.organisasi_id
			// INNER JOIN gbm_organisasi d ON b.parent_id=d.id INNER JOIN  gbm_organisasi e ON d.parent_id=e.id
			// INNER JOIN  gbm_organisasi f ON e.parent_id=f.id
			//  where d.id=" . $afd['id'] . "
			//  and h.tanggal>='" . $tgl_mulai . "' and h.tanggal<='" . $tgl_akhir . "'")->row_array();
			$retrieve_kg = $this->db->query("SELECT SUM(jum_kg_pks)AS kg,SUM(jum_ha)AS ha FROM est_produksi_panen_ht a
			INNER JOIN est_produksi_panen_dt b ON a.id=b.produksi_panen_id
			INNER JOIN gbm_organisasi c ON a.divisi_id=c.id
			INNER JOIN gbm_organisasi d ON c.parent_id=d.id 
		 	where a.divisi_id=" . $afd['id'] . "
			 and a.tanggal>='" . $tgl_mulai . "' and a.tanggal<='" . $tgl_akhir . "'")->row_array();

			// var_dump($retrieve_kg);exit();

			$luas_ha = 0;
			$jumlah_kg = 0;
			if ($retrieve_Ha) {
				$luas_ha = $retrieve_Ha['ha'];
			}
			if ($retrieve_kg) {
				$jumlah_kg = $retrieve_kg['kg'] ? $retrieve_kg['kg'] : 0;
			}
			$total_kg = $total_kg + $jumlah_kg;
			/* PANEN HEADER */
			$sqlPanenRekap = "SELECT nama_estate,
			 SUM(debet-kredit)AS jumlah_biaya
			 FROM est_cost_blok_vw 
			 where kode_estate='" . $retrieveEstate['kode'] . "' 
			 and kode_lokasi_jurnal='" . $retrieveEstate['kode'] . "' 
			 and kode_afdeling='" . $afd['kode'] . "' and tanggal>='" . $tgl_mulai . "' and tanggal<='" . $tgl_akhir . "'
			 and kelompok_biaya='PNN'
			 GROUP BY   nama_estate,kode_afdeling,nama_afdeling,nama_rayon,nama_estate		
			";
			//  var_dump($sqlPanenRekap);exit();
			$retrieveCost = $this->db->query($sqlPanenRekap)->row_array();
			$jum_biaya = $jum_biaya +	$retrieveCost['jumlah_biaya'];
			$html = $html . ' 	<tr class=":: arc-content">
					 	<td> PANEN </td>
						<td style="text-align: right;">' . $this->format_number_report($retrieveCost['jumlah_biaya']) . ' 
						<td style="text-align: right;">' . $this->format_number_report($retrieveCost['jumlah_biaya'] / $jumlah_kg) . ' 		
						<td style="text-align: right;">' . $this->format_number_report($jumlah_kg) . ' 										
						</td>';
			$html = $html . '</tr>';
			/* PANEN DETAIL */
			$sqlPanen = "SELECT kode_akun,nama_akun,nama_estate,
			 SUM(debet-kredit)AS jumlah_biaya
			 FROM est_cost_blok_vw 
			 where kode_estate='" . $retrieveEstate['kode'] . "' 
			  and kode_lokasi_jurnal='" . $retrieveEstate['kode'] . "'
			 and kode_afdeling='" . $afd['kode'] . "' and tanggal>='" . $tgl_mulai . "' and tanggal<='" . $tgl_akhir . "'
			 and kelompok_biaya='PNN'
			 GROUP BY   kode_akun,nama_akun, kode_afdeling,nama_afdeling,nama_rayon,nama_estate		
			 order by kode_akun";
			$retrieveCost = $this->db->query($sqlPanen)->result_array();
			foreach ($retrieveCost as $key => $m) {
				$cost_per_ha = ($luas_ha == 0 || $m['jumlah_biaya'] == 0) ? 0 : ($m['jumlah_biaya'] / $luas_ha);
				$cost_per_kg = ($jumlah_kg == 0 || $m['jumlah_biaya'] == 0) ? 0 : ($m['jumlah_biaya'] / $jumlah_kg);
				$html = $html . ' 	<tr class=":: arc-content">
						<td>&nbsp;&nbsp -' . $m['nama_akun'] . ' </td>
						<td style="text-align: right;">' . $this->format_number_report($m['jumlah_biaya']) . ' 
						<td style="text-align: right;">' . $this->format_number_report($cost_per_kg) . ' 
						<td style="text-align: right;">' . $this->format_number_report($jumlah_kg) . ' 																			
						</td>';
				$html = $html . '</tr>';
			}

			/* PERAWATAN HEADER */
			$sqlPerawatanRekap = "SELECT nama_estate,
			SUM(debet-kredit)AS jumlah_biaya
			FROM est_cost_blok_vw 
			where kode_estate='" . $retrieveEstate['kode'] . "' 
			 and kode_lokasi_jurnal='" . $retrieveEstate['kode'] . "'
			and kode_afdeling='" . $afd['kode'] . "' and tanggal>='" . $tgl_mulai . "' and tanggal<='" . $tgl_akhir . "'
			and kelompok_biaya='PML'
			GROUP BY  kode_afdeling,nama_afdeling,nama_rayon,nama_estate		
			";
			$retrieveCost = $this->db->query($sqlPerawatanRekap)->row_array();
			$jum_biaya = $jum_biaya +	$retrieveCost['jumlah_biaya'];
			$html = $html . ' 	<tr class=":: arc-content">
						<td> PERAWATAN </td>
					   <td style="text-align: right;">' . $this->format_number_report($retrieveCost['jumlah_biaya']) . ' 
					   <td style="text-align: right;">' . $this->format_number_report($retrieveCost['jumlah_biaya'] / $jumlah_kg) . ' 
					   <td style="text-align: right;">' . $this->format_number_report($jumlah_kg) . ' 																			
					   </td>';
			$html = $html . '</tr>';

			/* PEMUPUKAN HEADER */
			$sqlPemupukanRekap = "SELECT nama_estate,
			SUM(debet-kredit)AS jumlah_biaya
			FROM est_cost_blok_vw 
			where kode_estate='" . $retrieveEstate['kode'] . "' 
			 and kode_lokasi_jurnal='" . $retrieveEstate['kode'] . "'
			and kode_afdeling='" . $afd['kode'] . "' and tanggal>='" . $tgl_mulai . "' and tanggal<='" . $tgl_akhir . "'
			and kelompok_biaya='PMK'
			GROUP BY   kode_afdeling,nama_afdeling,nama_rayon,nama_estate		
			";
			$retrieveCost = $this->db->query($sqlPemupukanRekap)->row_array();
			$jum_biaya = $jum_biaya +	$retrieveCost['jumlah_biaya'];
			$html = $html . ' 	<tr class=":: arc-content">
						<td> PEMUPUKAN </td>
					   <td style="text-align: right;">' . $this->format_number_report($retrieveCost['jumlah_biaya']) . ' 
					   <td style="text-align: right;">' . $this->format_number_report($retrieveCost['jumlah_biaya'] / $jumlah_kg) . ' 									
					   <td style="text-align: right;">' . $this->format_number_report($jumlah_kg) . ' 											 
					   </td>';
			$html = $html . '</tr>';

			$html = $html . ' 	<tr class=":: arc-content">
						<td><b> TOTAL BIAYA ' . $afd['nama'] . '</b></td>
						<td style="text-align: right;"><b>' . $this->format_number_report($jum_biaya) . ' </b>
						<td style="text-align: right;"><b>' . $this->format_number_report($jum_biaya / $jumlah_kg) . ' </b>									
						<td style="text-align: right;"><b>' . $this->format_number_report($jumlah_kg) . ' </b>										
						</td>';
			$html = $html . '</tr>';
			$html = $html . ' 	<tr class=":: arc-content">
			<td colspan=4></td>
			</tr>';
		} /* END BY AFDELING */


		/*  ==== start BY NULL AFDELING ( Tidak teralokasi ke BLOK)  ===*/
		/* PANEN HEADER */
		$jum_biaya = 0;
		$sqlPanenRekap = "SELECT kode_lokasi_jurnal,
		SUM(debet-kredit)AS jumlah_biaya
		FROM est_cost_blok_v2_vw 
		where 
		(kode_afdeling IS NULL or kode_lokasi_jurnal<>kode_estate)
		AND kode_lokasi_jurnal='" . $retrieveEstate['kode'] . "' 
		and tanggal>='" . $tgl_mulai . "' and tanggal<='" . $tgl_akhir . "'
		and kelompok_biaya='PNN'
		GROUP BY  kode_lokasi_jurnal		
	   ";
		//  var_dump($sqlPanenRekap);exit();
		$retrieveCost = $this->db->query($sqlPanenRekap)->row_array();
		$jum_biaya = $jum_biaya +	$retrieveCost['jumlah_biaya'];
		$html = $html . ' 	<tr class=":: arc-content">
					<td> PANEN </td>
				   <td style="text-align: right;">' . $this->format_number_report($retrieveCost['jumlah_biaya']) . ' 
				   <td style="text-align: right;"> 		
				   <td style="text-align: right;">										
				   </td>';
		$html = $html . '</tr>';
		/* PANEN DETAIL */
		$sqlPanen = "SELECT kode_akun,nama_akun,kode_lokasi_jurnal,
		SUM(debet-kredit)AS jumlah_biaya
		FROM est_cost_blok_v2_vw 
		where (kode_afdeling IS NULL or kode_lokasi_jurnal<>kode_estate)
		and  kode_lokasi_jurnal='" . $retrieveEstate['kode'] . "' 
		 and tanggal>='" . $tgl_mulai . "' and tanggal<='" . $tgl_akhir . "'
		and kelompok_biaya='PNN'
		GROUP BY   kode_akun,nama_akun, kode_lokasi_jurnal
		order by kode_akun";
		$retrieveCost = $this->db->query($sqlPanen)->result_array();
		foreach ($retrieveCost as $key => $m) {
			//    $cost_per_ha = ($luas_ha == 0 || $m['jumlah_biaya'] == 0) ? 0 : ($m['jumlah_biaya'] / $luas_ha);
			//    $cost_per_kg = ($jumlah_kg == 0 || $m['jumlah_biaya'] == 0) ? 0 : ($m['jumlah_biaya'] / $jumlah_kg);
			$html = $html . ' 	<tr class=":: arc-content">
				   <td>&nbsp;&nbsp -' . $m['nama_akun'] . ' </td>
				   <td style="text-align: right;">' . $this->format_number_report($m['jumlah_biaya']) . ' 
				   <td style="text-align: right;"> 
				   <td style="text-align: right;">																		
				   </td>';
			$html = $html . '</tr>';
		}

		/* PERAWATAN HEADER */
		$sqlPerawatanRekap = "SELECT kode_lokasi_jurnal,
	   SUM(debet-kredit)AS jumlah_biaya
	   FROM est_cost_blok_v2_vw 
	  where (kode_afdeling IS NULL or kode_lokasi_jurnal<>kode_estate)
	   and kode_lokasi_jurnal='" . $retrieveEstate['kode'] . "' 
	   and tanggal>='" . $tgl_mulai . "' and tanggal<='" . $tgl_akhir . "'
	   and kelompok_biaya='PML'
	   GROUP BY  kode_lokasi_jurnal	
	   ";
		$retrieveCost = $this->db->query($sqlPerawatanRekap)->row_array();
		if ($retrieveCost && $retrieveCost['jumlah_biaya'] != 0) {
			$jum_biaya = $jum_biaya +	$retrieveCost['jumlah_biaya'];
			$html = $html . ' 	<tr class=":: arc-content">
						<td> PERAWATAN </td>
						<td style="text-align: right;">' . $this->format_number_report($retrieveCost['jumlah_biaya']) . ' 
						<td style="text-align: right;">
						<td style="text-align: right;">																		
						</td>';
			$html = $html . '</tr>';
		}

		/* PEMUPUKAN HEADER */
		$sqlPemupukanRekap = "SELECT kode_lokasi_jurnal,
	   SUM(debet-kredit)AS jumlah_biaya
	   FROM est_cost_blok_v2_vw 
	   where 	(kode_afdeling IS NULL or kode_lokasi_jurnal<>kode_estate)
	   and kode_lokasi_jurnal='" . $retrieveEstate['kode'] . "' 
	   and tanggal>='" . $tgl_mulai . "' and tanggal<='" . $tgl_akhir . "'
	   and kelompok_biaya='PMK'
	   GROUP BY   kode_lokasi_jurnal		
	   ";
		$retrieveCost = $this->db->query($sqlPemupukanRekap)->row_array();
		if ($retrieveCost && $retrieveCost['jumlah_biaya'] != 0) {
			$jum_biaya = $jum_biaya +	$retrieveCost['jumlah_biaya'];
			$html = $html . ' 	<tr class=":: arc-content">
						<td> PEMUPUKAN </td>
						<td style="text-align: right;">' . $this->format_number_report($retrieveCost['jumlah_biaya']) . ' 
						<td style="text-align: right;"></td> 									
						<td style="text-align: right;"></td>';

			$html = $html . '</tr>';
		}

		$html = $html . ' 	<tr class=":: arc-content">
				   <td><b> TOTAL BIAYA BLM TERALOKASI</b></td>
				   <td style="text-align: right;"><b>' . $this->format_number_report($jum_biaya) . ' </b>
				   <td style="text-align: right;"><b></b>									
				   <td style="text-align: right;"><b></b>										
				   </td>';
		$html = $html . '</tr>';
		$html = $html . ' 	<tr class=":: arc-content">
	   <td colspan=4></td>
	   </tr>';
		/* ======= End by NUll Afdeling =======/*


		/* BY ESTATE */
		$total_biaya = 0;
		$retrieve_Ha = $this->db->query("SELECT sum(b.luasareaproduktif)AS ha FROM gbm_organisasi a   INNER  JOIN gbm_blok b ON a.id=b.organisasi_id
			INNER JOIN gbm_organisasi c ON a.parent_id=c.id INNER JOIN  gbm_organisasi d ON c.parent_id=d.id
			INNER JOIN  gbm_organisasi e ON d.parent_id=e.id where e.id=" . $estate_id . "")->row_array();

		// $retrieve_kg = $this->db->query("SELECT SUM(hasil_kerja_brondolan)AS brondolan_kg,SUM(hasil_kerja_kg)AS jjg_kg
		// 	 from est_bkm_panen_dt a inner JOIN est_bkm_panen_ht h ON a.bkm_panen_id=h.id
		// 	 INNER JOIN gbm_organisasi b ON a.blok_id=b.id
		// 	INNER JOIN gbm_blok c ON b.id=c.organisasi_id
		// 	INNER JOIN gbm_organisasi d ON b.parent_id=d.id INNER JOIN  gbm_organisasi e ON d.parent_id=e.id
		// 	INNER JOIN  gbm_organisasi f ON e.parent_id=f.id where f.id=" . $estate_id . " 
		// 	and h.tanggal>='" . $tgl_mulai . "' and h.tanggal<='" . $tgl_akhir . "' ")->row_array();
		$retrieve_kg = $this->db->query("SELECT SUM(jum_kg_pks)AS kg,SUM(jum_ha)AS ha FROM est_produksi_panen_ht a
		INNER JOIN est_produksi_panen_dt b ON a.id=b.produksi_panen_id
		INNER JOIN gbm_organisasi c ON a.divisi_id=c.id
		INNER JOIN gbm_organisasi d ON c.parent_id=d.id 
	   where d.parent_id=" . $estate_id . " 
	   and a.tanggal>='" . $tgl_mulai . "' and a.tanggal<='" . $tgl_akhir . "' ")->row_array();

		$luas_ha = 0;
		$jumlah_kg = 0;
		if ($retrieve_Ha) {
			$luas_ha = $retrieve_Ha['ha'];
		}
		if ($retrieve_kg) {
			$jumlah_kg = $retrieve_kg['kg'] ?  $retrieve_kg['kg'] : 0;
		}
		/* PANEN HEADER */
		$sqlPanenRekap = "SELECT kode_lokasi_jurnal,
		SUM(debet-kredit)AS jumlah_biaya
		FROM est_cost_blok_v2_vw 
		where kode_lokasi_jurnal='" . $retrieveEstate['kode'] . "' 
		and tanggal>='" . $tgl_mulai . "' and tanggal<='" . $tgl_akhir . "'
		and kelompok_biaya='PNN'
		GROUP BY  kode_lokasi_jurnal		
		";
		$retrieveCost = $this->db->query($sqlPanenRekap)->row_array();
		$total_biaya = $total_biaya + $retrieveCost['jumlah_biaya'];
		$html = $html . ' 	<tr class=":: arc-content">
					<td> PANEN </td>
				   <td style="text-align: right;">' . $this->format_number_report($retrieveCost['jumlah_biaya']) . ' 
				   <td style="text-align: right;">' . $this->format_number_report($retrieveCost['jumlah_biaya'] / $jumlah_kg) . ' 				
				   <td style="text-align: right;">' . $this->format_number_report($jumlah_kg) . ' 									
				   </td>';
		$html = $html . '</tr>';
		/* PANEN DETAIL */
		$sqlPanen = "SELECT kode_akun,nama_akun,kode_lokasi_jurnal,
		SUM(debet-kredit)AS jumlah_biaya
		FROM est_cost_blok_v2_vw 
		where kode_lokasi_jurnal='" . $retrieveEstate['kode'] . "' 
		 and tanggal>='" . $tgl_mulai . "' and tanggal<='" . $tgl_akhir . "'
		and kelompok_biaya='PNN'
		GROUP BY  kode_akun,nama_akun,kode_lokasi_jurnal		
		order by kode_akun";
		$retrieveCost = $this->db->query($sqlPanen)->result_array();
		foreach ($retrieveCost as $key => $m) {
			$cost_per_ha = ($luas_ha == 0 || $m['jumlah_biaya'] == 0) ? 0 : ($m['jumlah_biaya'] / $luas_ha);
			$cost_per_kg = ($jumlah_kg == 0 || $m['jumlah_biaya'] == 0) ? 0 : ($m['jumlah_biaya'] / $jumlah_kg);
			$html = $html . ' 	<tr class=":: arc-content">
				   <td>&nbsp;&nbsp -' . $m['nama_akun'] . ' </td>
				   <td style="text-align: right;">' . $this->format_number_report($m['jumlah_biaya']) . ' 
				   <td style="text-align: right;">' . $this->format_number_report($cost_per_kg) . ' 								
				   <td style="text-align: right;">' . $this->format_number_report($jumlah_kg) . ' 										
				   </td>';
			$html = $html . '</tr>';
		}

		/* PERAWATAN HEADER */
		$sqlPerawatanRekap = "SELECT kode_lokasi_jurnal,
	   SUM(debet-kredit)AS jumlah_biaya
	   FROM est_cost_blok_v2_vw 
	   where kode_lokasi_jurnal='" . $retrieveEstate['kode'] . "' 
	   and tanggal>='" . $tgl_mulai . "' and tanggal<='" . $tgl_akhir . "'
	   and kelompok_biaya='PML'
	   GROUP BY  kode_lokasi_jurnal		
	   ";
		$retrieveCost = $this->db->query($sqlPerawatanRekap)->row_array();
		$total_biaya = $total_biaya + $retrieveCost['jumlah_biaya'];
		$html = $html . ' 	<tr class=":: arc-content">
				   <td> PERAWATAN </td>
				  <td style="text-align: right;">' . $this->format_number_report($retrieveCost['jumlah_biaya']) . ' 
				  <td style="text-align: right;">' . $this->format_number_report($retrieveCost['jumlah_biaya'] / $jumlah_kg) . ' 									
				  <td style="text-align: right;">' . $this->format_number_report($jumlah_kg) . ' 										
				  </td>';
		$html = $html . '</tr>';

		/* PEMUPUKAN HEADER */
		$sqlPemupukanRekap = "SELECT kode_lokasi_jurnal,
	   SUM(debet-kredit)AS jumlah_biaya
	   FROM est_cost_blok_v2_vw 
	   where kode_lokasi_jurnal='" . $retrieveEstate['kode'] . "' 
	   and tanggal>='" . $tgl_mulai . "' and tanggal<='" . $tgl_akhir . "'
	   and kelompok_biaya='PMK' 
	   GROUP BY   kode_lokasi_jurnal		
	  ";
		$retrieveCost = $this->db->query($sqlPemupukanRekap)->row_array();
		$total_biaya = $total_biaya + $retrieveCost['jumlah_biaya'];
		$html = $html . ' 	<tr class=":: arc-content">
				   <td> PEMUPUKAN </td>
				  <td style="text-align: right;">' . $this->format_number_report($retrieveCost['jumlah_biaya']) . ' 
				  <td style="text-align: right;">' . $this->format_number_report($retrieveCost['jumlah_biaya'] / $jumlah_kg) . ' 									
				  <td style="text-align: right;">' . $this->format_number_report($jumlah_kg) . ' 										 
				  </td>';
		$html = $html . '</tr>';
		$head_akun = '7';
		$sqlBiayaUmum = "SELECT d.kode as kode_estate,
		SUM(debet-kredit)AS jumlah_biaya
		FROM acc_jurnal_ht a inner join acc_jurnal_dt b 
		on a.id=b.jurnal_id inner join acc_akun c
		on b.acc_akun_id=c.id 
		inner join gbm_organisasi d on b.lokasi_id=d.id
		where d.kode='" . $retrieveEstate['kode'] . "' 
		and a.tanggal>='" . $tgl_mulai . "' and a.tanggal<='" . $tgl_akhir . "'
		and c.kode like'" . $head_akun . "%'
		GROUP BY  d.kode";
		$retrieveCost = $this->db->query($sqlBiayaUmum)->row_array();
		$total_biaya = $total_biaya + $retrieveCost['jumlah_biaya'];
		$html = $html . ' 	<tr class=":: arc-content">
				   <td> UMUM </td>
				  <td style="text-align: right;">' . $this->format_number_report($retrieveCost['jumlah_biaya']) . ' 
				  <td style="text-align: right;">' . $this->format_number_report($retrieveCost['jumlah_biaya'] / $jumlah_kg) . ' 									
				  <td style="text-align: right;">' . $this->format_number_report($jumlah_kg) . ' 										 
				  </td>';
		$html = $html . '</tr>';

		$html = $html . ' 	<tr class=":: arc-content">
		<td><b> TOTAL BIAYA ESTATE ' . $retrieveEstate['nama'] . '</b></td>
		<td style="text-align: right;"><b>' . $this->format_number_report($total_biaya) . ' </b>
		<td style="text-align: right;"><b>' . $this->format_number_report($total_biaya / $jumlah_kg) . ' </b>	
		<td style="text-align: right;"><b>' . $this->format_number_report($jumlah_kg) . ' 	 </b>																		
		</td>';
		$html = $html . '</tr>';

		$html = $html . ' 	
			</tbody>
			</table>
			';
		if ($format_laporan == 'xls') {
			$reader = new \PhpOffice\PhpSpreadsheet\Reader\Html;
			$spreadsheet = $reader->loadFromString($html);
			// $reader->setSheetIndex(1);
			//$spreadhseet = $reader->loadFromString($secondHtmlString, $spreadsheet);
			$objWriter = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
			header("Pragma: public");
			header("Expires: 0");
			header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
			header("Content-Type: application/force-download");
			header("Content-Type: application/octet-stream");
			header("Content-Type: application/download");
			header("Content-Disposition: attachment;filename=test.xlsx");
			header("Content-Transfer-Encoding: binary ");

			ob_end_clean();
			ob_start();
			$objWriter->save('php://output');
		} else if ($format_laporan == 'view') {
			echo $html;
		} else {
			$filename = 'report_' . time();
			$this->pdfgenerator->generate($html, $filename, true, 'A4', 'landscape');
		}
	}

	/* Laporan Cost Price By Estet */
	function laporan_cost_price_by_estate_post()
	{

		error_reporting(0);
		$estate_id     = $this->post('estate_id', true);
		$tgl_mulai =  $this->post('tgl_mulai', true);
		$tgl_akhir = $this->post('tgl_akhir', true);
		$format_laporan     = $this->post('format_laporan', true);
		$versi_laporan     = $this->post('versi_laporan', true);

		$retrieveEstate = $this->db->query("select * from gbm_organisasi where id=" . $estate_id . "")->row_array();

		$this->load->helper("terbilang");
		$this->load->helper("antech_helper");
		$this->load->library('pdfgenerator');
		if ($format_laporan == 'pdf') {
			$html = get_header_pdf_report();
		} else {
			$html = get_header_report_v2();
		}
		$html = $html . '
		<div class="row">
  	<div class="span12">
	  <br>
	<div class="kop-print">
	  <div class="kop-nama">PT. ANNAJAH TECHNOLOGY INDONESIA</div>
	  <div class="kop-info"> Komplek Vila Sehati B12 , Tapos - Depok</div>
	  <div class="kop-info">Telp : 081387373939</div>
	</div>
	  <hr class="kop-print-hr">
  	</div>
  	</div>
  <h3 class="title">LAPORAN COST PRICE</h3>
  <table class="no_border" style="width:35%">
			
			<tr>
					<td>ESTATE</td>
					<td>:</td>
					<td>' .  $retrieveEstate['nama'] . '</td>
			</tr>
			<tr>	
					<td>PERIODE</td>
					<td>:</td>
					<td>' . $tgl_mulai . ' s/d ' . $tgl_akhir . '</td>
			</tr>
			
	</table>
			<br>
		';

		$html = $html . ' <table  >
			<thead>
				<tr>
				<th>Kelompok Biaya</th>
				<th style="text-align: center;">Biaya </th>
				<th style="text-align: center;">Cost/Kg </th>	
				<th style="text-align: center;">Produksi(Kg) </th>				
				</tr>
			</thead>
			<tbody>';


		/* BY ESTATE */
		$total_biaya = 0;
		$retrieve_Ha = $this->db->query("SELECT sum(b.luasareaproduktif)AS ha FROM gbm_organisasi a   INNER  JOIN gbm_blok b ON a.id=b.organisasi_id
			INNER JOIN gbm_organisasi c ON a.parent_id=c.id INNER JOIN  gbm_organisasi d ON c.parent_id=d.id
			INNER JOIN  gbm_organisasi e ON d.parent_id=e.id where e.id=" . $estate_id . "")->row_array();

		$retrieve_kg = $this->db->query("SELECT SUM(jum_kg_pks)AS kg,SUM(jum_ha)AS ha FROM est_produksi_panen_ht a
		INNER JOIN est_produksi_panen_dt b ON a.id=b.produksi_panen_id
		INNER JOIN gbm_organisasi c ON a.divisi_id=c.id
		INNER JOIN gbm_organisasi d ON c.parent_id=d.id 
	   where d.parent_id=" . $estate_id . " 
	   and a.tanggal>='" . $tgl_mulai . "' and a.tanggal<='" . $tgl_akhir . "' ")->row_array();

		$luas_ha = 0;
		$jumlah_kg = 0;
		if ($retrieve_Ha) {
			$luas_ha = $retrieve_Ha['ha'];
		}
		if ($retrieve_kg) {
			$jumlah_kg = $retrieve_kg['kg'] ?  $retrieve_kg['kg'] : 0;
		}
		/* PANEN HEADER */
		$sqlPanenRekap = "SELECT kode_lokasi_jurnal,
		SUM(debet-kredit)AS jumlah_biaya
		FROM est_cost_blok_v2_vw 
		where kode_lokasi_jurnal='" . $retrieveEstate['kode'] . "' 
		and tanggal>='" . $tgl_mulai . "' and tanggal<='" . $tgl_akhir . "'
		and kelompok_biaya='PNN'
		GROUP BY  kode_lokasi_jurnal		
		";
		$retrieveCost = $this->db->query($sqlPanenRekap)->row_array();

		$total_biaya = $total_biaya + $retrieveCost['jumlah_biaya'];
		$html = $html . ' 	<tr class=":: arc-content">
					<td> PANEN </td>
				   <td style="text-align: right;">' . $this->format_number_report($retrieveCost['jumlah_biaya']) . ' 
				   <td style="text-align: right;">' . $this->format_number_report($retrieveCost['jumlah_biaya'] / $jumlah_kg) . ' 				
				   <td style="text-align: right;">' . $this->format_number_report($jumlah_kg) . ' 									
				   </td>';
		$html = $html . '</tr>';
		/* PANEN DETAIL */
		$sqlPanen = "SELECT kode_akun,nama_akun,kode_lokasi_jurnal,
		SUM(debet-kredit)AS jumlah_biaya
		FROM est_cost_blok_v2_vw 
		where kode_lokasi_jurnal='" . $retrieveEstate['kode'] . "' 
		 and tanggal>='" . $tgl_mulai . "' and tanggal<='" . $tgl_akhir . "'
		and kelompok_biaya='PNN'
		GROUP BY  kode_akun,nama_akun,kode_lokasi_jurnal		
		order by kode_akun";
		$retrieveCost = $this->db->query($sqlPanen)->result_array();
		foreach ($retrieveCost as $key => $m) {
			$cost_per_ha = ($luas_ha == 0 || $m['jumlah_biaya'] == 0) ? 0 : ($m['jumlah_biaya'] / $luas_ha);
			$cost_per_kg = ($jumlah_kg == 0 || $m['jumlah_biaya'] == 0) ? 0 : ($m['jumlah_biaya'] / $jumlah_kg);
			$html = $html . ' 	<tr class=":: arc-content">
				   <td>&nbsp;&nbsp -' . $m['nama_akun'] . ' </td>
				   <td style="text-align: right;">' . $this->format_number_report($m['jumlah_biaya']) . ' 
				   <td style="text-align: right;">' . $this->format_number_report($cost_per_kg) . ' 								
				   <td style="text-align: right;">' . $this->format_number_report($jumlah_kg) . ' 										
				   </td>';
			$html = $html . '</tr>';
		}

		/* PERAWATAN HEADER */
		$sqlPerawatanRekap = "SELECT kode_lokasi_jurnal,
	   SUM(debet-kredit)AS jumlah_biaya
	   FROM est_cost_blok_v2_vw 
	   where kode_lokasi_jurnal='" . $retrieveEstate['kode'] . "' 
	   and tanggal>='" . $tgl_mulai . "' and tanggal<='" . $tgl_akhir . "'
	   and kelompok_biaya='PML'
	   GROUP BY  kode_lokasi_jurnal		
	   ";
		$retrieveCost = $this->db->query($sqlPerawatanRekap)->row_array();
		$total_biaya = $total_biaya + $retrieveCost['jumlah_biaya'];
		$html = $html . ' 	<tr class=":: arc-content">
				   <td> PERAWATAN </td>
				  <td style="text-align: right;">' . $this->format_number_report($retrieveCost['jumlah_biaya']) . ' 
				  <td style="text-align: right;">' . $this->format_number_report($retrieveCost['jumlah_biaya'] / $jumlah_kg) . ' 									
				  <td style="text-align: right;">' . $this->format_number_report($jumlah_kg) . ' 										
				  </td>';
		$html = $html . '</tr>';

		/* PEMUPUKAN HEADER */
		$sqlPemupukanRekap = "SELECT kode_lokasi_jurnal,
	   SUM(debet-kredit)AS jumlah_biaya
	   FROM est_cost_blok_v2_vw 
	   where kode_lokasi_jurnal='" . $retrieveEstate['kode'] . "' 
	   and tanggal>='" . $tgl_mulai . "' and tanggal<='" . $tgl_akhir . "'
	   and kelompok_biaya='PMK' 
	   GROUP BY   kode_lokasi_jurnal		
	  ";
		$retrieveCost = $this->db->query($sqlPemupukanRekap)->row_array();
		$total_biaya = $total_biaya + $retrieveCost['jumlah_biaya'];
		$html = $html . ' 	<tr class=":: arc-content">
				   <td> PEMUPUKAN </td>
				  <td style="text-align: right;">' . $this->format_number_report($retrieveCost['jumlah_biaya']) . ' 
				  <td style="text-align: right;">' . $this->format_number_report($retrieveCost['jumlah_biaya'] / $jumlah_kg) . ' 									
				  <td style="text-align: right;">' . $this->format_number_report($jumlah_kg) . ' 										 
				  </td>';
		$html = $html . '</tr>';
		$head_akun = '7';
		$sqlBiayaUmum = "SELECT d.kode as kode_estate,
		SUM(debet-kredit)AS jumlah_biaya
		FROM acc_jurnal_ht a inner join acc_jurnal_dt b 
		on a.id=b.jurnal_id inner join acc_akun c
		on b.acc_akun_id=c.id 
		inner join gbm_organisasi d on b.lokasi_id=d.id
		where d.kode='" . $retrieveEstate['kode'] . "' 
		and a.tanggal>='" . $tgl_mulai . "' and a.tanggal<='" . $tgl_akhir . "'
		and c.kode like'" . $head_akun . "%'
		GROUP BY  d.kode";
		$retrieveCost = $this->db->query($sqlBiayaUmum)->row_array();
		$total_biaya = $total_biaya + $retrieveCost['jumlah_biaya'];
		$html = $html . ' 	<tr class=":: arc-content">
				   <td> UMUM </td>
				  <td style="text-align: right;">' . $this->format_number_report($retrieveCost['jumlah_biaya']) . ' 
				  <td style="text-align: right;">' . $this->format_number_report($retrieveCost['jumlah_biaya'] / $jumlah_kg) . ' 									
				  <td style="text-align: right;">' . $this->format_number_report($jumlah_kg) . ' 										 
				  </td>';
		$html = $html . '</tr>';


		$html = $html . ' 	<tr class=":: arc-content">
		<td><b> TOTAL BIAYA ESTATE ' . $retrieveEstate['nama'] . '</b></td>
		<td style="text-align: right;"><b>' . $this->format_number_report($total_biaya) . ' </b>
		<td style="text-align: right;"><b>' . $this->format_number_report($total_biaya / $jumlah_kg) . ' </b>	
		<td style="text-align: right;"><b>' . $this->format_number_report($jumlah_kg) . ' 	 </b>																		
		</td>';
		$html = $html . '</tr>';


		if ($versi_laporan == 'v2') {
			$html = $html . '<tr></tr>';

			/* BIAYA TAMBAHAN (HO)*/
			$head_akun = '7';
			$sqlBiayaUmum = "SELECT d.kode as kode_estate,
			SUM(debet-kredit)AS jumlah_biaya
			FROM acc_jurnal_ht a inner join acc_jurnal_dt b 
			on a.id=b.jurnal_id inner join acc_akun c
			on b.acc_akun_id=c.id 
			inner join gbm_organisasi d on b.lokasi_id=d.id
			where d.kode='DPHO' 
			and a.tanggal>='" . $tgl_mulai . "' and a.tanggal<='" . $tgl_akhir . "'
			and c.kode between '7110101' and '7150203'
			GROUP BY  d.kode";
			$retrieveCost = $this->db->query($sqlBiayaUmum)->row_array();
			$total_biaya = $total_biaya + ($retrieveCost['jumlah_biaya'] / 2);
			$html = $html . ' 	<tr class=":: arc-content">
				   <td> UMUM HO</td>
				  <td style="text-align: right;">' . $this->format_number_report(($retrieveCost['jumlah_biaya'] / 2)) . ' 
				  <td style="text-align: right;">' . $this->format_number_report(($retrieveCost['jumlah_biaya'] / 2) / $jumlah_kg) . ' 									
				  <td style="text-align: right;">' . $this->format_number_report($jumlah_kg) . ' 										 
				  </td>';
			$html = $html . '</tr>';

			// akun transport 7189901 - Biaya Transport Penjualan Phk-3 //
			$sqlTransport = "SELECT 
			SUM(debet-kredit)AS jumlah_biaya
			FROM acc_jurnal_ht a inner join acc_jurnal_dt b 
			on a.id=b.jurnal_id inner join acc_akun c
			on b.acc_akun_id=c.id 
			inner join gbm_organisasi d on b.lokasi_id=d.id
			where
			 a.tanggal>='" . $tgl_mulai . "' and a.tanggal<='" . $tgl_akhir . "'
			and c.kode in('7189901')
			";
			$retrieveCost = $this->db->query($sqlTransport)->row_array();
			$total_biaya = $total_biaya + ($retrieveCost['jumlah_biaya'] / 2);
			$html = $html . ' 	<tr class=":: arc-content">
				   <td> Angkut CPO dan PK </td>
				  <td style="text-align: right;">' . $this->format_number_report($retrieveCost['jumlah_biaya'] / 2) . ' 
				  <td style="text-align: right;">' . $this->format_number_report(($retrieveCost['jumlah_biaya'] / 2) / $jumlah_kg) . ' 									
				  <td style="text-align: right;">' . $this->format_number_report($jumlah_kg) . ' 										 
				  </td>';
			$html = $html . '</tr>';

			$html = $html . ' 	<tr class=":: arc-content">
			<td><b> TOTAL BIAYA  </b></td>
			<td style="text-align: right;"><b>' . $this->format_number_report($total_biaya) . ' </b>
			<td style="text-align: right;"><b>' . $this->format_number_report($total_biaya / $jumlah_kg) . ' </b>	
			<td style="text-align: right;"><b>' . $this->format_number_report($jumlah_kg) . ' 	 </b>																		
			</td>';
			$html = $html . '</tr>';
			/* BIAYA TAMBAHAN (HO)*/
		}

		$html = $html . ' 	
			</tbody>
			</table>
			';

		if ($format_laporan == 'xls') {
			$reader = new \PhpOffice\PhpSpreadsheet\Reader\Html;
			$spreadsheet = $reader->loadFromString($html);
			// $reader->setSheetIndex(1);
			//$spreadhseet = $reader->loadFromString($secondHtmlString, $spreadsheet);
			$objWriter = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
			header("Pragma: public");
			header("Expires: 0");
			header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
			header("Content-Type: application/force-download");
			header("Content-Type: application/octet-stream");
			header("Content-Type: application/download");
			header("Content-Disposition: attachment;filename=test.xlsx");
			header("Content-Transfer-Encoding: binary ");

			ob_end_clean();
			ob_start();
			$objWriter->save('php://output');
		} else if ($format_laporan == 'view') {
			echo $html;
		} else {
			$filename = 'report_' . time();
			$this->pdfgenerator->generate($html, $filename, true, 'A4', 'landscape');
		}
	}
	function laporan_cost_afdeling_rekap_by_tipe_kegiatan_post()
	{

		error_reporting(0);
		$estate_id     = $this->post('estate_id', true);
		$tgl_mulai =  $this->post('tgl_mulai', true);
		$tgl_akhir = $this->post('tgl_akhir', true);
		$format_laporan     = $this->post('format_laporan', true);
		$retrieveEstate = $this->db->query("select * from gbm_organisasi where id=" . $estate_id . "")->row_array();

		$this->load->helper("terbilang");
		$this->load->helper("antech_helper");
		$this->load->library('pdfgenerator');
		if ($format_laporan == 'pdf') {
			$html = get_header_pdf_report();
		} else {
			$html = get_header_report_v2();
		}
		$html = $html . '
		<div class="row">
  	<div class="span12">
	  <br>
	<div class="kop-print">
	  <div class="kop-nama">PT. ANNAJAH TECHNOLOGY INDONESIA</div>
	  <div class="kop-info"> Komplek Vila Sehati B12 , Tapos - Depok</div>
	  <div class="kop-info">Telp : 081387373939</div>
	</div>
	  <hr class="kop-print-hr">
  	</div>
  	</div>
  <h3 class="title">LAPORAN COST PRICE</h3>
  <table class="no_border" style="width:35%">
			
			<tr>
					<td>ESTATE</td>
					<td>:</td>
					<td>' .  $retrieveEstate['nama'] . '</td>
			</tr>
			<tr>	
					<td>PERIODE</td>
					<td>:</td>
					<td>' . $tgl_mulai . ' s/d ' . $tgl_akhir . '</td>
			</tr>
			
	</table>
			<br>
		';

		$html = $html . ' <table  >
			<thead>
				<tr>
				<th>Kelompok Biaya</th>
				<th style="text-align: right;">Biaya </th>
				<th style="text-align: right;">Cost/Kg </th>				
				</tr>
			</thead>
			<tbody>';
		$retrieveAfdeling = $this->db->query("select a.id,a.kode,a.nama from gbm_organisasi a inner join gbm_organisasi b on a.parent_id=b.id where a.tipe='AFDELING'
		 and b.parent_id=" . $estate_id . " and a.nama like '%AFDELING%'")->result_array();

		/* BY AFDELING */
		foreach ($retrieveAfdeling as $key => $afd) {
			$jum_biaya = 0;
			$retrieve_Ha = $this->db->query("SELECT sum(b.luasareaproduktif)AS ha FROM gbm_organisasi a   INNER  JOIN gbm_blok b ON a.id=b.organisasi_id
			INNER JOIN gbm_organisasi c ON a.parent_id=c.id INNER JOIN  gbm_organisasi d ON c.parent_id=d.id
			INNER JOIN  gbm_organisasi e ON d.parent_id=e.id where c.id=" . $afd['id'] . "")->row_array();

			$retrieve_kg = $this->db->query("SELECT SUM(hasil_kerja_brondolan)AS brondolan_kg,SUM(hasil_kerja_kg)AS jjg_kg 
			from est_bkm_panen_dt a inner JOIN est_bkm_panen_ht h ON a.bkm_panen_id=h.id
			INNER JOIN gbm_organisasi b ON a.blok_id=b.id
			INNER JOIN gbm_blok c ON b.id=c.organisasi_id
			INNER JOIN gbm_organisasi d ON b.parent_id=d.id INNER JOIN  gbm_organisasi e ON d.parent_id=e.id
			INNER JOIN  gbm_organisasi f ON e.parent_id=f.id
			 where d.id=" . $afd['id'] . "
			 and h.tanggal>='" . $tgl_mulai . "' and h.tanggal<='" . $tgl_akhir . "'")->row_array();

			$luas_ha = 0;
			$jumlah_kg = 0;
			if ($retrieve_Ha) {
				$luas_ha = $retrieve_Ha['ha'];
			}
			if ($retrieve_kg) {
				$jumlah_kg = $retrieve_kg['brondolan_kg'] + $retrieve_kg['jjg_kg'];
			}
			/* PANEN HEADER */
			$sqlPanenRekap = "SELECT kode_kelompok_kegiatan,nama_kelompok_kegiatan,nama_estate,
			 SUM(debet-kredit)AS jumlah_biaya
			 FROM est_cost_blok_vw 
			 where kode_estate='" . $retrieveEstate['kode'] . "' 
			 and kode_afdeling='" . $afd['kode'] . "' and tanggal>='" . $tgl_mulai . "' and tanggal<='" . $tgl_akhir . "'
			 and tipe_kegiatan='PNN'
			 GROUP BY   kode_kelompok_kegiatan,nama_kelompok_kegiatan,kode_afdeling,nama_afdeling,nama_rayon,nama_estate		
			 order by kode_kelompok_kegiatan";
			//  var_dump($sqlPanenRekap);exit();
			$retrieveCost = $this->db->query($sqlPanenRekap)->row_array();
			$jum_biaya = $jum_biaya +	$retrieveCost['jumlah_biaya'];
			$html = $html . ' 	<tr class=":: arc-content">
					 	<td> PANEN </td>
						<td style="text-align: right;">' . $this->format_number_report($retrieveCost['jumlah_biaya']) . ' 
						<td style="text-align: right;">' . $this->format_number_report($retrieveCost['jumlah_biaya'] / $jumlah_kg) . ' 									
						</td>';
			$html = $html . '</tr>';
			/* PANEN DETAIL */
			$sqlPanen = "SELECT kode_kelompok_kegiatan,nama_kelompok_kegiatan,kode_kegiatan,nama_kegiatan,nama_estate,
			 SUM(debet-kredit)AS jumlah_biaya
			 FROM est_cost_blok_vw 
			 where kode_estate='" . $retrieveEstate['kode'] . "' 
			 and kode_afdeling='" . $afd['kode'] . "' and tanggal>='" . $tgl_mulai . "' and tanggal<='" . $tgl_akhir . "'
			 and tipe_kegiatan='PNN'
			 GROUP BY   kode_kelompok_kegiatan,nama_kelompok_kegiatan,kode_kegiatan,nama_kegiatan, kode_afdeling,nama_afdeling,nama_rayon,nama_estate		
			 order by nama_kegiatan";
			$retrieveCost = $this->db->query($sqlPanen)->result_array();
			foreach ($retrieveCost as $key => $m) {
				$cost_per_ha = ($luas_ha == 0 || $m['jumlah_biaya'] == 0) ? 0 : ($m['jumlah_biaya'] / $luas_ha);
				$cost_per_kg = ($jumlah_kg == 0 || $m['jumlah_biaya'] == 0) ? 0 : ($m['jumlah_biaya'] / $jumlah_kg);
				$html = $html . ' 	<tr class=":: arc-content">
						<td>&nbsp;&nbsp -' . $m['nama_kegiatan'] . ' </td>
						<td style="text-align: right;">' . $this->format_number_report($m['jumlah_biaya']) . ' 
						<td style="text-align: right;">' . $this->format_number_report($cost_per_kg) . ' 									
						</td>';
				$html = $html . '</tr>';
			}

			/* PERAWATAN HEADER */
			$sqlPerawatanRekap = "SELECT kode_kelompok_kegiatan,nama_kelompok_kegiatan,nama_estate,
			SUM(debet-kredit)AS jumlah_biaya
			FROM est_cost_blok_vw 
			where kode_estate='" . $retrieveEstate['kode'] . "' 
			and kode_afdeling='" . $afd['kode'] . "' and tanggal>='" . $tgl_mulai . "' and tanggal<='" . $tgl_akhir . "'
			and tipe_kegiatan='PML'
			GROUP BY   kode_kelompok_kegiatan,nama_kelompok_kegiatan,kode_afdeling,nama_afdeling,nama_rayon,nama_estate		
			order by kode_kelompok_kegiatan";
			$retrieveCost = $this->db->query($sqlPerawatanRekap)->row_array();
			$jum_biaya = $jum_biaya +	$retrieveCost['jumlah_biaya'];
			$html = $html . ' 	<tr class=":: arc-content">
						<td> PERAWATAN </td>
					   <td style="text-align: right;">' . $this->format_number_report($retrieveCost['jumlah_biaya']) . ' 
					   <td style="text-align: right;">' . $this->format_number_report($retrieveCost['jumlah_biaya'] / $jumlah_kg) . ' 									
					   </td>';
			$html = $html . '</tr>';

			/* PEMUPUKAN HEADER */
			$sqlPemupukanRekap = "SELECT kode_kelompok_kegiatan,nama_kelompok_kegiatan,nama_estate,
			SUM(debet-kredit)AS jumlah_biaya
			FROM est_cost_blok_vw 
			where kode_estate='" . $retrieveEstate['kode'] . "' 
			and kode_afdeling='" . $afd['kode'] . "' and tanggal>='" . $tgl_mulai . "' and tanggal<='" . $tgl_akhir . "'
			and tipe_kegiatan='PMK'
			GROUP BY   kode_kelompok_kegiatan,nama_kelompok_kegiatan,kode_afdeling,nama_afdeling,nama_rayon,nama_estate		
			order by kode_kelompok_kegiatan";
			$retrieveCost = $this->db->query($sqlPemupukanRekap)->row_array();
			$jum_biaya = $jum_biaya +	$retrieveCost['jumlah_biaya'];
			$html = $html . ' 	<tr class=":: arc-content">
						<td> PEMUPUKAN </td>
					   <td style="text-align: right;">' . $this->format_number_report($retrieveCost['jumlah_biaya']) . ' 
					   <td style="text-align: right;">' . $this->format_number_report($retrieveCost['jumlah_biaya'] / $jumlah_kg) . ' 									
					   </td>';
			$html = $html . '</tr>';

			$html = $html . ' 	<tr class=":: arc-content">
						<td><b> TOTAL BIAYA ' . $afd['nama'] . '</b></td>
						<td style="text-align: right;"><b>' . $this->format_number_report($jum_biaya) . ' </b>
						<td style="text-align: right;"><b>' . $this->format_number_report($jum_biaya / $jumlah_kg) . ' </b>									
						</td>';
			$html = $html . '</tr>';
			$html = $html . ' 	<tr class=":: arc-content">
			<td colspan=3></td>
			</tr>';
		} /* END BY AFDELING */

		/* BY ESTATE */
		$total_biaya = 0;
		$retrieve_Ha = $this->db->query("SELECT sum(b.luasareaproduktif)AS ha FROM gbm_organisasi a   INNER  JOIN gbm_blok b ON a.id=b.organisasi_id
			INNER JOIN gbm_organisasi c ON a.parent_id=c.id INNER JOIN  gbm_organisasi d ON c.parent_id=d.id
			INNER JOIN  gbm_organisasi e ON d.parent_id=e.id where e.id=" . $estate_id . "")->row_array();

		// $retrieve_kg = $this->db->query("SELECT SUM(hasil_kerja_brondolan)AS brondolan_kg,SUM(hasil_kerja_kg)AS jjg_kg
		// 	 from est_bkm_panen_dt a inner JOIN est_bkm_panen_ht h ON a.bkm_panen_id=h.id
		// 	 INNER JOIN gbm_organisasi b ON a.blok_id=b.id
		// 	INNER JOIN gbm_blok c ON b.id=c.organisasi_id
		// 	INNER JOIN gbm_organisasi d ON b.parent_id=d.id INNER JOIN  gbm_organisasi e ON d.parent_id=e.id
		// 	INNER JOIN  gbm_organisasi f ON e.parent_id=f.id where f.id=" . $estate_id . " 
		// 	and h.tanggal>='" . $tgl_mulai . "' and h.tanggal<='" . $tgl_akhir . "' ")->row_array();
		$retrieve_kg = $this->db->query("SELECT SUM(jum_kg_pks)AS kg,SUM(jum_ha)AS ha
		 FROM est_produksi_panen_ht a
		INNER JOIN est_produksi_panen_dt b ON a.id=b.produksi_panen_id
		INNER JOIN gbm_organisasi c ON a.divisi_id=c.id
		INNER JOIN gbm_organisasi d ON c.parent_id=d.id 
		where d.parent_id=" . $estate_id . " 
	   		and a.tanggal>='" . $tgl_mulai . "' and a.tanggal<='" . $tgl_akhir . "' ")->row_array();

		$luas_ha = 0;
		$jumlah_kg = 0;
		if ($retrieve_Ha) {
			$luas_ha = $retrieve_Ha['ha'];
		}
		if ($retrieve_kg) {
			$jumlah_kg = $retrieve_kg['kg'];
		}
		/* PANEN HEADER */
		$sqlPanenRekap = "SELECT kode_kelompok_kegiatan,nama_kelompok_kegiatan,nama_estate,
		SUM(debet-kredit)AS jumlah_biaya
		FROM est_cost_blok_vw 
		where kode_estate='" . $retrieveEstate['kode'] . "' 
		and tanggal>='" . $tgl_mulai . "' and tanggal<='" . $tgl_akhir . "'
		and tipe_kegiatan='PNN'
		GROUP BY   kode_kelompok_kegiatan,nama_kelompok_kegiatan,nama_estate		
		order by kode_kelompok_kegiatan";
		$retrieveCost = $this->db->query($sqlPanenRekap)->row_array();
		$total_biaya = $total_biaya + $retrieveCost['jumlah_biaya'];
		$html = $html . ' 	<tr class=":: arc-content">
					<td> PANEN </td>
				   <td style="text-align: right;">' . $this->format_number_report($retrieveCost['jumlah_biaya']) . ' 
				   <td style="text-align: right;">' . $this->format_number_report($retrieveCost['jumlah_biaya'] / $jumlah_kg) . ' 									
				   </td>';
		$html = $html . '</tr>';
		/* PANEN DETAIL */
		$sqlPanen = "SELECT kode_kelompok_kegiatan,nama_kelompok_kegiatan,kode_kegiatan,nama_kegiatan,nama_estate,
		SUM(debet-kredit)AS jumlah_biaya
		FROM est_cost_blok_vw 
		where kode_estate='" . $retrieveEstate['kode'] . "' 
		 and tanggal>='" . $tgl_mulai . "' and tanggal<='" . $tgl_akhir . "'
		and tipe_kegiatan='PNN'
		GROUP BY   kode_kelompok_kegiatan,nama_kelompok_kegiatan,kode_kegiatan,nama_kegiatan,nama_estate		
		order by nama_kegiatan";
		$retrieveCost = $this->db->query($sqlPanen)->result_array();
		foreach ($retrieveCost as $key => $m) {
			$cost_per_ha = ($luas_ha == 0 || $m['jumlah_biaya'] == 0) ? 0 : ($m['jumlah_biaya'] / $luas_ha);
			$cost_per_kg = ($jumlah_kg == 0 || $m['jumlah_biaya'] == 0) ? 0 : ($m['jumlah_biaya'] / $jumlah_kg);
			$html = $html . ' 	<tr class=":: arc-content">
				   <td>&nbsp;&nbsp -' . $m['nama_kegiatan'] . ' </td>
				   <td style="text-align: right;">' . $this->format_number_report($m['jumlah_biaya']) . ' 
				   <td style="text-align: right;">' . $this->format_number_report($cost_per_kg) . ' 									
				   </td>';
			$html = $html . '</tr>';
		}

		/* PERAWATAN HEADER */
		$sqlPerawatanRekap = "SELECT kode_kelompok_kegiatan,nama_kelompok_kegiatan,nama_estate,
	   SUM(debet-kredit)AS jumlah_biaya
	   FROM est_cost_blok_vw 
	   where kode_estate='" . $retrieveEstate['kode'] . "' 
	   and tanggal>='" . $tgl_mulai . "' and tanggal<='" . $tgl_akhir . "'
	   and tipe_kegiatan='PML'
	   GROUP BY   kode_kelompok_kegiatan,nama_kelompok_kegiatan,nama_estate		
	   order by kode_kelompok_kegiatan";
		$retrieveCost = $this->db->query($sqlPerawatanRekap)->row_array();
		$total_biaya = $total_biaya + $retrieveCost['jumlah_biaya'];
		$html = $html . ' 	<tr class=":: arc-content">
				   <td> PERAWATAN </td>
				  <td style="text-align: right;">' . $this->format_number_report($retrieveCost['jumlah_biaya']) . ' 
				  <td style="text-align: right;">' . $this->format_number_report($retrieveCost['jumlah_biaya'] / $jumlah_kg) . ' 									
				  </td>';
		$html = $html . '</tr>';

		/* PEMUPUKAN HEADER */
		$sqlPemupukanRekap = "SELECT kode_kelompok_kegiatan,nama_kelompok_kegiatan,nama_estate,
	   SUM(debet-kredit)AS jumlah_biaya
	   FROM est_cost_blok_vw 
	   where kode_estate='" . $retrieveEstate['kode'] . "' 
	   and tanggal>='" . $tgl_mulai . "' and tanggal<='" . $tgl_akhir . "'
	   and tipe_kegiatan='PMK' 
	   GROUP BY   kode_kelompok_kegiatan,nama_kelompok_kegiatan,nama_estate		
	   order by kode_kelompok_kegiatan";
		$retrieveCost = $this->db->query($sqlPemupukanRekap)->row_array();
		$total_biaya = $total_biaya + $retrieveCost['jumlah_biaya'];
		$html = $html . ' 	<tr class=":: arc-content">
				   <td> PEMUPUKAN </td>
				  <td style="text-align: right;">' . $this->format_number_report($retrieveCost['jumlah_biaya']) . ' 
				  <td style="text-align: right;">' . $this->format_number_report($retrieveCost['jumlah_biaya'] / $jumlah_kg) . ' 									
				  </td>';
		$html = $html . '</tr>';
		$head_akun = '7';
		$sqlBiayaUmum = "SELECT d.kode as kode_estate,
		SUM(debet-kredit)AS jumlah_biaya
		FROM acc_jurnal_ht a inner join acc_jurnal_dt b 
		on a.id=b.jurnal_id inner join acc_akun c
		on b.acc_akun_id=c.id 
		inner join gbm_organisasi d on b.lokasi_id=d.id
		where d.kode='" . $retrieveEstate['kode'] . "' 
		and a.tanggal>='" . $tgl_mulai . "' and a.tanggal<='" . $tgl_akhir . "'
		and c.kode like'" . $head_akun . "%'
		GROUP BY  d.kode";
		$retrieveCost = $this->db->query($sqlBiayaUmum)->row_array();
		$total_biaya = $total_biaya + $retrieveCost['jumlah_biaya'];
		$html = $html . ' 	<tr class=":: arc-content">
				   <td> UMUM </td>
				  <td style="text-align: right;">' . $this->format_number_report($retrieveCost['jumlah_biaya']) . ' 
				  <td style="text-align: right;">' . $this->format_number_report($retrieveCost['jumlah_biaya'] / $jumlah_kg) . ' 									
				  </td>';
		$html = $html . '</tr>';

		$html = $html . ' 	<tr class=":: arc-content">
		<td><b> TOTAL BIAYA ESTATE ' . $retrieveEstate['nama'] . '</b></td>
		<td style="text-align: right;"><b>' . $this->format_number_report($total_biaya) . ' </b>
		<td style="text-align: right;"><b>' . $this->format_number_report($total_biaya / $jumlah_kg) . ' </b>									
		</td>';
		$html = $html . '</tr>';

		$html = $html . ' 	
			</tbody>
			</table>
			';
		if ($format_laporan == 'xls') {
			$reader = new \PhpOffice\PhpSpreadsheet\Reader\Html;
			$spreadsheet = $reader->loadFromString($html);
			// $reader->setSheetIndex(1);
			//$spreadhseet = $reader->loadFromString($secondHtmlString, $spreadsheet);
			$objWriter = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
			header("Pragma: public");
			header("Expires: 0");
			header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
			header("Content-Type: application/force-download");
			header("Content-Type: application/octet-stream");
			header("Content-Type: application/download");
			header("Content-Disposition: attachment;filename=test.xlsx");
			header("Content-Transfer-Encoding: binary ");

			ob_end_clean();
			ob_start();
			$objWriter->save('php://output');
		} else if ($format_laporan == 'view') {
			echo $html;
		} else {
			$filename = 'report_' . time();
			$this->pdfgenerator->generate($html, $filename, true, 'A4', 'landscape');
		}
	}
	function laporan_cost_afdeling_rekap_bgt_post()
	{

		error_reporting(0);
		$nama_bulan     = $this->post('nama_bulan', true);
		$tahun     = $this->post('tahun', true);
		$estate_id     = $this->post('estate_id', true);
		$periode =  $this->post('periode', true);
		$tgl_mulai =  $periode . "-01";
		$d2 = new DateTime($tgl_mulai);
		$d2->modify('last day of this month');
		$tgl_akhir = $d2->format('Y-m-d');
		$format_laporan     = $this->post('format_laporan', true);
		$retrieveEstate = $this->db->query("select * from gbm_organisasi where id=" . $estate_id . "")->row_array();

		$this->load->helper("terbilang");
		$this->load->helper("antech_helper");
		$this->load->library('pdfgenerator');
		if ($format_laporan == 'pdf') {
			$html = get_header_pdf_report();
		} else {
			$html = get_header_report();
		}
		$html = $html . '
		<h2>Laporan Cost Price </h2>
		<h3>Estate:' . $retrieveEstate['nama'] . ' </h3>
		<h4>' . $nama_bulan . '  ' . $tahun . ' </h4>';

		$html = $html . ' <table  >
			<thead>
				<tr>
					<th rowspan=2>Kelompok Biaya</th>
					<th colspan=2>Real</th>
					<th colspan=2>Budget</th>
				</tr>
				<tr>
					<th style="text-align: right;">Biaya </th>
					<th style="text-align: right;">Cost/Kg </th>
					<th style="text-align: right;">Biaya </th>
					<th style="text-align: right;">Cost/Kg </th>				
				</tr>
			</thead>
			<tbody>';
		$retrieveAfdeling = $this->db->query("select a.id,a.kode,a.nama from gbm_organisasi a inner join gbm_organisasi b on a.parent_id=b.id where a.tipe='AFDELING'
		 and b.parent_id=" . $estate_id . " and a.nama like '%AFDELING%'")->result_array();

		/* BY AFDELING */
		foreach ($retrieveAfdeling as $key => $afd) {
			$jum_biaya = 0;
			$retrieve_Ha = $this->db->query("SELECT sum(b.luasareaproduktif)AS ha FROM gbm_organisasi a   INNER  JOIN gbm_blok b ON a.id=b.organisasi_id
			INNER JOIN gbm_organisasi c ON a.parent_id=c.id INNER JOIN  gbm_organisasi d ON c.parent_id=d.id
			INNER JOIN  gbm_organisasi e ON d.parent_id=e.id where c.id=" . $afd['id'] . "")->row_array();

			$retrieve_kg = $this->db->query("SELECT SUM(hasil_kerja_brondolan)AS brondolan_kg,SUM(hasil_kerja_kg)AS jjg_kg 
			from est_bkm_panen_dt a inner JOIN est_bkm_panen_ht h ON a.bkm_panen_id=h.id
			INNER JOIN gbm_organisasi b ON a.blok_id=b.id
			INNER JOIN gbm_blok c ON b.id=c.organisasi_id
			INNER JOIN gbm_organisasi d ON b.parent_id=d.id INNER JOIN  gbm_organisasi e ON d.parent_id=e.id
			INNER JOIN  gbm_organisasi f ON e.parent_id=f.id
			 where d.id=" . $afd['id'] . "
			 and h.tanggal>='" . $tgl_mulai . "' and h.tanggal<='" . $tgl_akhir . "'")->row_array();

			$luas_ha = 0;
			$jumlah_kg = 0;
			if ($retrieve_Ha) {
				$luas_ha = $retrieve_Ha['ha'];
			}
			if ($retrieve_kg) {
				$jumlah_kg = $retrieve_kg['brondolan_kg'] + $retrieve_kg['jjg_kg'];
			}
			/* PANEN HEADER */
			$sqlPanenRekap = "SELECT kode_kelompok_kegiatan,nama_kelompok_kegiatan,nama_estate,
			 SUM(debet-kredit)AS jumlah_biaya
			 FROM est_cost_blok_vw 
			 where kode_estate='" . $retrieveEstate['kode'] . "' 
			 and kode_afdeling='" . $afd['kode'] . "' and tanggal>='" . $tgl_mulai . "' and tanggal<='" . $tgl_akhir . "'
			 and tipe_kegiatan='PNN'
			 GROUP BY   kode_kelompok_kegiatan,nama_kelompok_kegiatan,kode_afdeling,nama_afdeling,nama_rayon,nama_estate		
			 order by kode_kelompok_kegiatan";
			//  var_dump($sqlPanenRekap);exit();
			$retrieveCost = $this->db->query($sqlPanenRekap)->row_array();
			$jum_biaya = $jum_biaya +	$retrieveCost['jumlah_biaya'];
			$html = $html . ' 	<tr class=":: arc-content">
					 	<td> PANEN </td>
						<td style="text-align: right;">' . $this->format_number_report($retrieveCost['jumlah_biaya']) . ' 
						<td style="text-align: right;">' . $this->format_number_report($retrieveCost['jumlah_biaya'] / $jumlah_kg) . ' 			
						<td style="text-align: right;">' . $this->format_number_report(0) . ' 		
						<td style="text-align: right;">' . $this->format_number_report(0) . ' 								
						</td>';
			$html = $html . '</tr>';
			/* PANEN DETAIL */
			$sqlPanen = "SELECT kode_kelompok_kegiatan,nama_kelompok_kegiatan,kode_kegiatan,nama_kegiatan,nama_estate,
			 SUM(debet-kredit)AS jumlah_biaya
			 FROM est_cost_blok_vw 
			 where kode_estate='" . $retrieveEstate['kode'] . "' 
			 and kode_afdeling='" . $afd['kode'] . "' and tanggal>='" . $tgl_mulai . "' and tanggal<='" . $tgl_akhir . "'
			 and tipe_kegiatan='PNN'
			 GROUP BY   kode_kelompok_kegiatan,nama_kelompok_kegiatan,kode_kegiatan,nama_kegiatan, kode_afdeling,nama_afdeling,nama_rayon,nama_estate		
			 order by nama_kegiatan";
			$retrieveCost = $this->db->query($sqlPanen)->result_array();
			foreach ($retrieveCost as $key => $m) {
				$cost_per_ha = ($luas_ha == 0 || $m['jumlah_biaya'] == 0) ? 0 : ($m['jumlah_biaya'] / $luas_ha);
				$cost_per_kg = ($jumlah_kg == 0 || $m['jumlah_biaya'] == 0) ? 0 : ($m['jumlah_biaya'] / $jumlah_kg);
				$html = $html . ' 	<tr class=":: arc-content">
						<td>&nbsp;&nbsp -' . $m['nama_kegiatan'] . ' </td>
						<td style="text-align: right;">' . $this->format_number_report($m['jumlah_biaya']) . ' 
						<td style="text-align: right;">' . $this->format_number_report($cost_per_kg) . ' 									
						<td style="text-align: right;">' . $this->format_number_report(0) . ' 		
						<td style="text-align: right;">' . $this->format_number_report(0) . ' 	
						</td>';
				$html = $html . '</tr>';
			}

			/* PERAWATAN HEADER */
			$sqlPerawatanRekap = "SELECT kode_kelompok_kegiatan,nama_kelompok_kegiatan,nama_estate,
			SUM(debet-kredit)AS jumlah_biaya
			FROM est_cost_blok_vw 
			where kode_estate='" . $retrieveEstate['kode'] . "' 
			and kode_afdeling='" . $afd['kode'] . "' and tanggal>='" . $tgl_mulai . "' and tanggal<='" . $tgl_akhir . "'
			and tipe_kegiatan='PML'
			GROUP BY   kode_kelompok_kegiatan,nama_kelompok_kegiatan,kode_afdeling,nama_afdeling,nama_rayon,nama_estate		
			order by kode_kelompok_kegiatan";
			$retrieveCost = $this->db->query($sqlPerawatanRekap)->row_array();
			$jum_biaya = $jum_biaya +	$retrieveCost['jumlah_biaya'];
			$html = $html . ' 	<tr class=":: arc-content">
						<td> PERAWATAN </td>
					   <td style="text-align: right;">' . $this->format_number_report($retrieveCost['jumlah_biaya']) . ' 
					   <td style="text-align: right;">' . $this->format_number_report($retrieveCost['jumlah_biaya'] / $jumlah_kg) . ' 									
					   <td style="text-align: right;">' . $this->format_number_report(0) . ' 		
					   <td style="text-align: right;">' . $this->format_number_report(0) . ' 	 
					   </td>';
			$html = $html . '</tr>';

			/* PEMUPUKAN HEADER */
			$sqlPemupukanRekap = "SELECT kode_kelompok_kegiatan,nama_kelompok_kegiatan,nama_estate,
			SUM(debet-kredit)AS jumlah_biaya
			FROM est_cost_blok_vw 
			where kode_estate='" . $retrieveEstate['kode'] . "' 
			and kode_afdeling='" . $afd['kode'] . "' and tanggal>='" . $tgl_mulai . "' and tanggal<='" . $tgl_akhir . "'
			and tipe_kegiatan='PMK'
			GROUP BY   kode_kelompok_kegiatan,nama_kelompok_kegiatan,kode_afdeling,nama_afdeling,nama_rayon,nama_estate		
			order by kode_kelompok_kegiatan";
			$retrieveCost = $this->db->query($sqlPemupukanRekap)->row_array();
			$jum_biaya = $jum_biaya +	$retrieveCost['jumlah_biaya'];
			$html = $html . ' 	<tr class=":: arc-content">
						<td> PEMUPUKAN </td>
					   <td style="text-align: right;">' . $this->format_number_report($retrieveCost['jumlah_biaya']) . ' 
					   <td style="text-align: right;">' . $this->format_number_report($retrieveCost['jumlah_biaya'] / $jumlah_kg) . ' 									
					   <td style="text-align: right;">' . $this->format_number_report(0) . ' 		
					   <td style="text-align: right;">' . $this->format_number_report(0) . ' 	  
					   </td>';
			$html = $html . '</tr>';

			$html = $html . ' 	<tr class=":: arc-content">
						<td><b> TOTAL BIAYA ' . $afd['nama'] . '</b></td>
						<td style="text-align: right;"><b>' . $this->format_number_report($jum_biaya) . ' </b>
						<td style="text-align: right;"><b>' . $this->format_number_report($jum_biaya / $jumlah_kg) . ' </b>									
						<td style="text-align: right;">' . $this->format_number_report(0) . ' 		
						<td style="text-align: right;">' . $this->format_number_report(0) . ' 	
						</td>';
			$html = $html . '</tr>';
			$html = $html . ' 	<tr class=":: arc-content">
			<td colspan=3></td>
			</tr>';
		} /* END BY AFDELING */

		/* BY ESTATE */
		$total_biaya = 0;
		$retrieve_Ha = $this->db->query("SELECT sum(b.luasareaproduktif)AS ha FROM gbm_organisasi a   INNER  JOIN gbm_blok b ON a.id=b.organisasi_id
			INNER JOIN gbm_organisasi c ON a.parent_id=c.id INNER JOIN  gbm_organisasi d ON c.parent_id=d.id
			INNER JOIN  gbm_organisasi e ON d.parent_id=e.id where e.id=" . $estate_id . "")->row_array();

		$retrieve_kg = $this->db->query("SELECT SUM(hasil_kerja_brondolan)AS brondolan_kg,SUM(hasil_kerja_kg)AS jjg_kg
			 from est_bkm_panen_dt a inner JOIN est_bkm_panen_ht h ON a.bkm_panen_id=h.id
			 INNER JOIN gbm_organisasi b ON a.blok_id=b.id
			INNER JOIN gbm_blok c ON b.id=c.organisasi_id
			INNER JOIN gbm_organisasi d ON b.parent_id=d.id INNER JOIN  gbm_organisasi e ON d.parent_id=e.id
			INNER JOIN  gbm_organisasi f ON e.parent_id=f.id where f.id=" . $estate_id . " 
			and h.tanggal>='" . $tgl_mulai . "' and h.tanggal<='" . $tgl_akhir . "' ")->row_array();

		$luas_ha = 0;
		$jumlah_kg = 0;
		if ($retrieve_Ha) {
			$luas_ha = $retrieve_Ha['ha'];
		}
		if ($retrieve_kg) {
			$jumlah_kg = $retrieve_kg['brondolan_kg'] + $retrieve_kg['jjg_kg'];
		}
		/* PANEN HEADER */
		$sqlPanenRekap = "SELECT kode_kelompok_kegiatan,nama_kelompok_kegiatan,nama_estate,
		SUM(debet-kredit)AS jumlah_biaya
		FROM est_cost_blok_vw 
		where kode_estate='" . $retrieveEstate['kode'] . "' 
		and tanggal>='" . $tgl_mulai . "' and tanggal<='" . $tgl_akhir . "'
		and tipe_kegiatan='PNN'
		GROUP BY   kode_kelompok_kegiatan,nama_kelompok_kegiatan,nama_estate		
		order by kode_kelompok_kegiatan";
		$retrieveCost = $this->db->query($sqlPanenRekap)->row_array();
		$total_biaya = $total_biaya + $retrieveCost['jumlah_biaya'];
		$html = $html . ' 	<tr class=":: arc-content">
					<td> PANEN </td>
				   <td style="text-align: right;">' . $this->format_number_report($retrieveCost['jumlah_biaya']) . ' 
				   <td style="text-align: right;">' . $this->format_number_report($retrieveCost['jumlah_biaya'] / $jumlah_kg) . ' 									
				   <td style="text-align: right;">' . $this->format_number_report(0) . ' 		
				   <td style="text-align: right;">' . $this->format_number_report(0) . ' 	 
				   </td>';
		$html = $html . '</tr>';
		/* PANEN DETAIL */
		$sqlPanen = "SELECT kode_kelompok_kegiatan,nama_kelompok_kegiatan,kode_kegiatan,nama_kegiatan,nama_estate,
		SUM(debet-kredit)AS jumlah_biaya
		FROM est_cost_blok_vw 
		where kode_estate='" . $retrieveEstate['kode'] . "' 
		 and tanggal>='" . $tgl_mulai . "' and tanggal<='" . $tgl_akhir . "'
		and tipe_kegiatan='PNN'
		GROUP BY   kode_kelompok_kegiatan,nama_kelompok_kegiatan,kode_kegiatan,nama_kegiatan,nama_estate		
		order by nama_kegiatan";
		$retrieveCost = $this->db->query($sqlPanen)->result_array();
		foreach ($retrieveCost as $key => $m) {
			$cost_per_ha = ($luas_ha == 0 || $m['jumlah_biaya'] == 0) ? 0 : ($m['jumlah_biaya'] / $luas_ha);
			$cost_per_kg = ($jumlah_kg == 0 || $m['jumlah_biaya'] == 0) ? 0 : ($m['jumlah_biaya'] / $jumlah_kg);
			$html = $html . ' 	<tr class=":: arc-content">
				   <td>&nbsp;&nbsp -' . $m['nama_kegiatan'] . ' </td>
				   <td style="text-align: right;">' . $this->format_number_report($m['jumlah_biaya']) . ' 
				   <td style="text-align: right;">' . $this->format_number_report($cost_per_kg) . ' 									
				   <td style="text-align: right;">' . $this->format_number_report(0) . ' 		
				   <td style="text-align: right;">' . $this->format_number_report(0) . ' 	  
				   </td>';
			$html = $html . '</tr>';
		}

		/* PERAWATAN HEADER */
		$sqlPerawatanRekap = "SELECT kode_kelompok_kegiatan,nama_kelompok_kegiatan,nama_estate,
	   SUM(debet-kredit)AS jumlah_biaya
	   FROM est_cost_blok_vw 
	   where kode_estate='" . $retrieveEstate['kode'] . "' 
	   and tanggal>='" . $tgl_mulai . "' and tanggal<='" . $tgl_akhir . "'
	   and tipe_kegiatan='PML'
	   GROUP BY   kode_kelompok_kegiatan,nama_kelompok_kegiatan,nama_estate		
	   order by kode_kelompok_kegiatan";
		$retrieveCost = $this->db->query($sqlPerawatanRekap)->row_array();
		$total_biaya = $total_biaya + $retrieveCost['jumlah_biaya'];
		$html = $html . ' 	<tr class=":: arc-content">
				   <td> PERAWATAN </td>
				  <td style="text-align: right;">' . $this->format_number_report($retrieveCost['jumlah_biaya']) . ' 
				  <td style="text-align: right;">' . $this->format_number_report($retrieveCost['jumlah_biaya'] / $jumlah_kg) . ' 									
				  <td style="text-align: right;">' . $this->format_number_report(0) . ' 		
				  <td style="text-align: right;">' . $this->format_number_report(0) . ' 	 
				  </td>';
		$html = $html . '</tr>';

		/* PEMUPUKAN HEADER */
		$sqlPemupukanRekap = "SELECT kode_kelompok_kegiatan,nama_kelompok_kegiatan,nama_estate,
	   SUM(debet-kredit)AS jumlah_biaya
	   FROM est_cost_blok_vw 
	   where kode_estate='" . $retrieveEstate['kode'] . "' 
	   and tanggal>='" . $tgl_mulai . "' and tanggal<='" . $tgl_akhir . "'
	   and tipe_kegiatan='PMK'
	   GROUP BY   kode_kelompok_kegiatan,nama_kelompok_kegiatan,nama_estate		
	   order by kode_kelompok_kegiatan";
		$retrieveCost = $this->db->query($sqlPemupukanRekap)->row_array();
		$total_biaya = $total_biaya + $retrieveCost['jumlah_biaya'];
		$html = $html . ' 	<tr class=":: arc-content">
				   <td> PEMUPUKAN </td>
				  <td style="text-align: right;">' . $this->format_number_report($retrieveCost['jumlah_biaya']) . ' 
				  <td style="text-align: right;">' . $this->format_number_report($retrieveCost['jumlah_biaya'] / $jumlah_kg) . ' 									
				  <td style="text-align: right;">' . $this->format_number_report(0) . ' 		
				  <td style="text-align: right;">' . $this->format_number_report(0) . ' 	 
				  </td>';
		$html = $html . '</tr>';
		$head_akun = '7';
		$sqlBiayaUmum = "SELECT d.kode as kode_estate,
		SUM(debet-kredit)AS jumlah_biaya
		FROM acc_jurnal_ht a inner join acc_jurnal_dt b 
		on a.id=b.jurnal_id inner join acc_akun c
		on b.acc_akun_id=c.id 
		inner join gbm_organisasi d on b.lokasi_id=d.id
		where d.kode='" . $retrieveEstate['kode'] . "' 
		and a.tanggal>='" . $tgl_mulai . "' and a.tanggal<='" . $tgl_akhir . "'
		and c.kode like'" . $head_akun . "%'
		GROUP BY  d.kode";
		$retrieveCost = $this->db->query($sqlBiayaUmum)->row_array();
		$total_biaya = $total_biaya + $retrieveCost['jumlah_biaya'];
		$html = $html . ' 	<tr class=":: arc-content">
				   <td> UMUM </td>
				  <td style="text-align: right;">' . $this->format_number_report($retrieveCost['jumlah_biaya']) . ' 
				  <td style="text-align: right;">' . $this->format_number_report($retrieveCost['jumlah_biaya'] / $jumlah_kg) . ' 									
				  <td style="text-align: right;">' . $this->format_number_report(0) . ' 		
				  <td style="text-align: right;">' . $this->format_number_report(0) . ' 	
				  </td>';
		$html = $html . '</tr>';

		$html = $html . ' 	<tr class=":: arc-content">
		<td><b> TOTAL BIAYA ESTATE ' . $retrieveEstate['nama'] . '</b></td>
		<td style="text-align: right;"><b>' . $this->format_number_report($total_biaya) . ' </b>
		<td style="text-align: right;"><b>' . $this->format_number_report($total_biaya / $jumlah_kg) . ' </b>									
		<td style="text-align: right;">' . $this->format_number_report(0) . ' 		
		<td style="text-align: right;">' . $this->format_number_report(0) . ' 	
		</td>';
		$html = $html . '</tr>';

		$html = $html . ' 	
			</tbody>
			</table>
			';
		if ($format_laporan == 'xls') {
			$reader = new \PhpOffice\PhpSpreadsheet\Reader\Html;
			$spreadsheet = $reader->loadFromString($html);
			// $reader->setSheetIndex(1);
			//$spreadhseet = $reader->loadFromString($secondHtmlString, $spreadsheet);
			$objWriter = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
			header("Pragma: public");
			header("Expires: 0");
			header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
			header("Content-Type: application/force-download");
			header("Content-Type: application/octet-stream");
			header("Content-Type: application/download");
			header("Content-Disposition: attachment;filename=test.xlsx");
			header("Content-Transfer-Encoding: binary ");

			ob_end_clean();
			ob_start();
			$objWriter->save('php://output');
		} else if ($format_laporan == 'view') {
			echo $html;
		} else {
			$filename = 'report_' . time();
			$this->pdfgenerator->generate($html, $filename, true, 'A4', 'landscape');
		}
	}
	function laporan_biaya_umum_post()
	{

		error_reporting(0);
		$estate_id     = $this->post('estate_id', true);
		$tgl_mulai =  $this->post('tgl_mulai', true);
		$tgl_akhir = $this->post('tgl_akhir', true);
		$format_laporan     = $this->post('format_laporan', true);

		$retrieveEstate = $this->db->query("select * from gbm_organisasi where id=" . $estate_id . "")->row_array();
		$retrieve_Ha = $this->db->query("SELECT sum(b.luasareaproduktif)AS ha FROM gbm_organisasi a   INNER  JOIN gbm_blok b ON a.id=b.organisasi_id
			INNER JOIN gbm_organisasi c ON a.parent_id=c.id INNER JOIN  gbm_organisasi d ON c.parent_id=d.id
			INNER JOIN  gbm_organisasi e ON d.parent_id=e.id where e.id=" . $estate_id . "")->row_array();

		// $retrieve_kg = $this->db->query("SELECT SUM(hasil_kerja_brondolan)AS brondolan_kg,SUM(hasil_kerja_kg)AS jjg_kg
		// 	 from est_bkm_panen_dt a inner JOIN est_bkm_panen_ht h ON a.bkm_panen_id=h.id
		// 	 INNER JOIN gbm_organisasi b ON a.blok_id=b.id
		// 	INNER JOIN gbm_blok c ON b.id=c.organisasi_id
		// 	INNER JOIN gbm_organisasi d ON b.parent_id=d.id INNER JOIN  gbm_organisasi e ON d.parent_id=e.id
		// 	INNER JOIN  gbm_organisasi f ON e.parent_id=f.id where f.id=" . $estate_id . " 
		// 	and h.tanggal>='" . $tgl_mulai . "' and h.tanggal<='" . $tgl_akhir . "' ")->row_array();

		$retrieve_kg = $this->db->query("SELECT SUM(jum_kg_pks)AS kg,SUM(jum_ha)AS ha FROM est_produksi_panen_ht a
		INNER JOIN est_produksi_panen_dt b ON a.id=b.produksi_panen_id
		INNER JOIN gbm_organisasi c ON a.divisi_id=c.id
		INNER JOIN gbm_organisasi d ON c.parent_id=d.id 
	   where d.parent_id=" . $estate_id . " 
	   and a.tanggal>='" . $tgl_mulai . "' and a.tanggal<='" . $tgl_akhir . "' ")->row_array();

		$luas_ha = 0;
		$jumlah_kg = 0;
		if ($retrieve_Ha) {
			$luas_ha = $retrieve_Ha['ha'];
		}
		if ($retrieve_kg) {
			$jumlah_kg = $retrieve_kg['kg'] ? $retrieve_kg['kg'] : 0;
		}


		$this->load->helper("terbilang");
		$this->load->helper("antech_helper");
		$this->load->library('pdfgenerator');
		if ($format_laporan == 'pdf') {
			$html = get_header_pdf_report();
		} else {
			$html = get_header_report_v2();
		}


		$html = $html . '
		<div class="row">
  	<div class="span12">
	  <br>
	<div class="kop-print">
	  <div class="kop-nama">PT. ANNAJAH TECHNOLOGY INDONESIA</div>
	  <div class="kop-info"> Komplek Vila Sehati B12 , Tapos - Depok</div>
	  <div class="kop-info">Telp : 081387373939</div>
	</div>
	  <hr class="kop-print-hr">
  	</div>
  	</div>
  <h3 class="title">LAPORAN RINCIAN COST - BIAYA UMUM</h3>
  <table class="no_border" style="width:35%">
			
			<tr>
					<td>ESTATE</td>
					<td>:</td>
					<td>' .  $retrieveEstate['nama'] . '</td>
					<td>PRODUKSI</td>
					<td>:</td>
					<td>' . number_format($jumlah_kg) . '</td>
			</tr>
			<tr>	
					<td>PERIODE</td>
					<td>:</td>
					<td>' . $tgl_mulai . ' s/d ' . $tgl_akhir . '</td>
					<td>LUAS</td>
					<td>:</td>
					<td>' . number_format($luas_ha) . '</td>
			</tr>
			
	</table>
			<br>';

		$html = $html . ' <table  border="1" width="100%" style="border-collapse: collapse;" >
			<thead>
				<tr>
				<th style="text-align: center;" width="4%">No.</th>			
				<th>Kode Akun</th>
				<th>Nama Akun</th>
				<th style="text-align: center;">Biaya </th>
				<th style="text-align: center;">Cost/Ha </th>
				<th style="text-align: center;">Cost/Kg </th>	
				</tr>
			</thead>
			<tbody>';


		$ha = 0;
		$produksi = 0;
		$total_biaya = 0;
		$total_cost_per_ha = 0;
		$total_cost_per_kg = 0;
		$head_akun = '7';

		$sql = "";
		/* COST   */
		$sql = "SELECT c.kode as kode_akun,c.nama as nama_akun,d.kode as kode_estate,
				SUM(debet-kredit)AS jumlah_biaya
				FROM acc_jurnal_ht a inner join acc_jurnal_dt b 
				on a.id=b.jurnal_id inner join acc_akun c
				on b.acc_akun_id=c.id 
				inner join gbm_organisasi d on b.lokasi_id=d.id
				where d.kode='" . $retrieveEstate['kode'] . "' 
				and a.tanggal>='" . $tgl_mulai . "' and a.tanggal<='" . $tgl_akhir . "'
				and c.kode like'" . $head_akun . "%'
				GROUP BY  c.kode,c.nama,d.kode		
				order by c.kode";
		// var_dump($sql);exit();

		$retrieveCost = $this->db->query($sql)->result_array();

		$no = 0;
		$jumlah_biaya = 0;

		$total = 0;
		$jumlah_cost_per_ha = 0;
		$jumlah_cost_per_kg = 0;

		foreach ($retrieveCost as $key => $m) {
			$no++;
			$cost_per_ha = ($luas_ha == 0 || $m['jumlah_biaya'] == 0) ? 0 : ($m['jumlah_biaya'] / $luas_ha);
			$cost_per_kg = ($jumlah_kg == 0 || $m['jumlah_biaya'] == 0) ? 0 : ($m['jumlah_biaya'] / $jumlah_kg);
			$jumlah_biaya = $jumlah_biaya + $m['jumlah_biaya'];
			$jumlah_cost_per_ha = $jumlah_cost_per_ha + $cost_per_ha;
			$jumlah_cost_per_kg = $jumlah_cost_per_kg + $cost_per_kg;
			$total_biaya = $total_biaya + $m['jumlah_biaya'];
			$total_cost_per_ha = $total_cost_per_ha + $cost_per_ha;
			$total_cost_per_kg = $total_cost_per_kg + $cost_per_kg;
			$html = $html . ' 	<tr class=":: arc-content">
						<td style="position:relative; text-align: center;">	' . ($no) . '</td>
						<td>' . $m['kode_akun'] . ' </td>
						<td>' . $m['nama_akun'] . ' </td>
						<td style="text-align: right;">' . $this->format_number_report($m['jumlah_biaya'], 2) . ' 
						<td style="text-align: right;">' . $this->format_number_report($m['jumlah_biaya'] / $luas_ha, 2) . ' 
						<td style="text-align: right;">' . $this->format_number_report($m['jumlah_biaya'] / $jumlah_kg, 2) . ' 									
						</td>';
			$html = $html . '</tr>';
		}
		$html = $html . ' 	<tr class=":: arc-content">
		<td colspan=3 style="position:relative;"> Total By UMUM	</td>
		<td style="text-align: right;">' . $this->format_number_report($jumlah_biaya, 2) . ' 
		<td style="text-align: right;">' . $this->format_number_report($jumlah_biaya / $luas_ha, 2) . ' 
		<td style="text-align: right;">' . $this->format_number_report($jumlah_biaya / $jumlah_kg, 2) . ' 									
		</td> </tr>';
		/* END COST   */


		// $html = $html . ' 	
		// 	<tr class=":: arc-content">
		// 	<td  colspan=3 style="position:relative;">
		// 		&nbsp;

		// 	</td>

		// 	<td style="text-align: right;">
		// 	' . $this->format_number_report($total_biaya) . ' 
		// 	</td>
		// 	<td style="text-align: right;">
		// 	' . $this->format_number_report($total_cost_per_ha) . ' 
		// 	</td>
		// 	<td style="text-align: right;">
		// 	' . $this->format_number_report($total_cost_per_ha) . ' 
		// 	</td>



		// 	</tr>
		// 		</tbody>
		// 		</table>
		// 	';
		$html = $html . ' 	
			<tr class=":: arc-content">
			</tr>
			</tbody>
			</table>
			';
		if ($format_laporan == 'xls') {
			$reader = new \PhpOffice\PhpSpreadsheet\Reader\Html;
			$spreadsheet = $reader->loadFromString($html);
			// $reader->setSheetIndex(1);
			//$spreadhseet = $reader->loadFromString($secondHtmlString, $spreadsheet);
			$objWriter = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
			header("Pragma: public");
			header("Expires: 0");
			header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
			header("Content-Type: application/force-download");
			header("Content-Type: application/octet-stream");
			header("Content-Type: application/download");
			header("Content-Disposition: attachment;filename=test.xlsx");
			header("Content-Transfer-Encoding: binary ");

			ob_end_clean();
			ob_start();
			$objWriter->save('php://output');
		} else if ($format_laporan == 'view') {
			echo $html;
		} else {
			$filename = 'report_' . time();
			$this->pdfgenerator->generate($html, $filename, true, 'A4', 'landscape');
		}
	}

	function laporan_biaya_umum_mill_post()
	{

		error_reporting(0);
		$mill_id    = $this->post('mill_id', true);
		$tgl_mulai =  $this->post('tgl_mulai', true);
		$tgl_akhir = $this->post('tgl_akhir', true);
		$stasiun_id = $this->post('stasiun_id', true);
		$format_laporan     = $this->post('format_laporan', true);

		$retrieveMill = $this->db->query("select * from gbm_organisasi where id=" . $mill_id . "")->row_array();

		$this->load->helper("terbilang");
		$this->load->helper("antech_helper");
		$this->load->library('pdfgenerator');
		if ($format_laporan == 'pdf') {
			$html = get_header_pdf_report();
		} else {
			$html = get_header_report_v2();
		}


		$html = $html . '<div class="row">
		<div class="span12">
		<br>
	  <div class="kop-print">
		<div class="kop-nama">PT. ANNAJAH TECHNOLOGY INDONESIA</div>
		<div class="kop-info"> Komplek Vila Sehati B12 , Tapos - Depok</div>
		<div class="kop-info">Telp : 081387373939</div>
	  </div>
		<hr class="kop-print-hr">
		</div>
		</div>
	<h3 class="title">Laporan Biaya Umum/Over Head Mill</h3>
	<table class="no_border" style="width:35%">
			  
			  <tr>
					  <td>Mill</td>
					  <td>:</td>
					  <td>' .  $retrieveMill['nama'] . '</td>
					  
			  </tr>
			  <tr>
					  <td>Periode</td>
					  <td>:</td>
					  <td>' . tgl_indo($tgl_mulai) . ' s/d ' . tgl_indo($tgl_akhir) . '</td>
			  </tr>
			  
	  </table><br>
		';

		$html = $html . ' <table  >
			<thead>
				<tr>	
				<th>No Akun</th>
				<th>Nama Akun</th>
				<th>Biaya (Rp)</th>				
				</tr>
			</thead>
			<tbody>';

		$produksi = 0;
		$total_biaya = 0;

		$kelompok_biaya = array('71101', '71102', '712', '71301', '71302', '71303', '71304', '71305', '71306', '71307', '71308', '714', '71501', '71502'); // '71899 dikeluarkan
		$no = 0;
		foreach ($kelompok_biaya as $key => $kel) {
			$retrieveHead = $this->db->query("select * from acc_akun where kode='" . $kel . "'")->row_array();
			$retrieveAkun = $this->db->query("select * from acc_akun where (kode like '" . $kel . "%') and is_transaksi_akun=1")->result_array();
			$sql = "";
			$jum_biaya_by_stasiun = 0;

			foreach ($retrieveAkun as $key => $akun) {
				/* COST   */

				$sql = "SELECT b.acc_akun_id,c.kode,c.nama, sum(debet-kredit)as jumlah_biaya
					FROM acc_jurnal_ht a 
					inner join acc_jurnal_dt b on a.id=b.jurnal_id 
					INNER JOIN acc_akun c ON b.acc_akun_id=c.id
					where b.lokasi_id='" . $mill_id . "' 
					and a.tanggal>='" . $tgl_mulai . "' and a.tanggal<='" . $tgl_akhir . "'
					and c.kode ='" . $akun['kode'] . "'
					GROUP BY b.acc_akun_id,c.kode,c.nama
					order by c.kode";

				// var_dump($sql);
				// exit();
				$retrieveCost = $this->db->query($sql)->row_array();


				$jumlah_biaya = 0;
				if ($retrieveCost) {
					$jumlah_biaya = $retrieveCost['jumlah_biaya'];
				}
				$jum_biaya_by_stasiun =	$jum_biaya_by_stasiun + $jumlah_biaya;
				$total_biaya = $total_biaya + $jumlah_biaya;

				$no++;

				$html = $html . ' 	<tr class=":: arc-content">
						<td>' . $akun['kode'] . ' </td>
						<td>' . $akun['nama'] . ' </td>
						<td style="text-align: right;">' . $this->format_number_report($jumlah_biaya) . ' 							
						</td>';
				$html = $html . '</tr>';
			}
			$html = $html . ' 	<tr class=":: arc-content">
		<td colspan=2 style="position:relative;"><b> Total By ' . $retrieveHead['nama'] . '	</b> </td>
		<td style="text-align: right;"><b> ' . $this->format_number_report($jum_biaya_by_stasiun) . ' </b>
										
		</td> </tr>';
			/* END COST   */
		}

		$html = $html . ' 	
			<tr class=":: arc-content">
			<td  colspan=2 style="position:relative;">
			<b> 	TOTAL</b> 

			</td>
			
			<td style="text-align: right;">
			<b> ' . $this->format_number_report($total_biaya) . ' </b> 
			</td>
				
			</tr>
					</tbody>
				</table>
			';
		if ($format_laporan == 'xls') {
			$reader = new \PhpOffice\PhpSpreadsheet\Reader\Html;
			$spreadsheet = $reader->loadFromString($html);
			// $reader->setSheetIndex(1);
			//$spreadhseet = $reader->loadFromString($secondHtmlString, $spreadsheet);
			$objWriter = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
			header("Pragma: public");
			header("Expires: 0");
			header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
			header("Content-Type: application/force-download");
			header("Content-Type: application/octet-stream");
			header("Content-Type: application/download");
			header("Content-Disposition: attachment;filename=test.xlsx");
			header("Content-Transfer-Encoding: binary ");

			ob_end_clean();
			ob_start();
			$objWriter->save('php://output');
		} else if ($format_laporan == 'view') {
			echo $html;
		} else {
			$filename = 'report_' . time();
			$this->pdfgenerator->generate($html, $filename, true, 'A4', 'landscape');
		}
	}

	function laporan_biaya_rekap_mill_post()
	{
		$versi_laporan     = $this->post('versi_laporan', true);
		if ($versi_laporan == 'v1') {
			$this->laporan_biaya_rekap_mill_v1();
		} else {
			$this->laporan_biaya_rekap_mill_v2();
		}
	}
	function laporan_biaya_rekap_mill_v1()
	{
		error_reporting(0);
		$mill_id    = $this->post('mill_id', true);
		$tgl_mulai =  $this->post('tgl_mulai', true);
		$tgl_akhir = $this->post('tgl_akhir', true);
		$stasiun_id = $this->post('stasiun_id', true);
		$format_laporan     = $this->post('format_laporan', true);

		$retrieveMill = $this->db->query("select * from gbm_organisasi where id=" . $mill_id . "")->row_array();

		$this->load->helper("terbilang");
		$this->load->helper("antech_helper");
		$this->load->library('pdfgenerator');
		if ($format_laporan == 'pdf') {
			$html = get_header_pdf_report();
		} else {
			$html = get_header_report_v2();
		}
		/* =============== berat produk ============================================= */
		// $retrieveTBS = $this->db->query("select SUM(berat_bersih)as beratkg 
		// from pks_timbangan_terima_tbs_vw 
		// where mill_id=" . $mill_id . " 
		// and nama_produk='TBS'
		// and tanggal>='" . $tgl_mulai . "' and tanggal<='" . $tgl_akhir . "'")->row_array();
		$retrieveTBS = $this->db->query("select SUM(dua_hi)as beratkg 
		from pks_lhp
		where mill_id=" . $mill_id . " 
		and tanggal>='" . $tgl_mulai . "' and tanggal<='" . $tgl_akhir . "'")->row_array();
		$beratTbs = $retrieveTBS['beratkg'] ? $retrieveTBS['beratkg'] : 0;

		$retrieveCpo = $this->db->query("SELECT SUM(netto_kirim)as beratkg
		FROM  pks_timbangan_kirim_vw 
		where mill_id=" . $mill_id . " 
		and nama_produk='CPO'
		and tanggal>='" . $tgl_mulai . "' and tanggal<='" . $tgl_akhir . "'")->row_array();
		$beratCpo = $retrieveCpo['beratkg'] ? $retrieveCpo['beratkg'] : 0;

		$retrievePk = $this->db->query("SELECT SUM(netto_kirim)as beratkg
		FROM  pks_timbangan_kirim_vw 
		where mill_id=" . $mill_id . "
		and nama_produk='PK'
		and tanggal>='" . $tgl_mulai . "' and tanggal<='" . $tgl_akhir . "'")->row_array();
		$beratPk = $retrievePk['beratkg'] ? $retrievePk['beratkg'] : 0;
		/* =============== berat produk ============================================= */


		$html = $html . '
		<div class="row">
		<div class="span12">
		<br>
	  <div class="kop-print">
		<div class="kop-nama">PT. ANNAJAH TECHNOLOGY INDONESIA</div>
		<div class="kop-info"> Komplek Vila Sehati B12 , Tapos - Depok</div>
		<div class="kop-info">Telp : 081387373939</div>
	  </div>
		<hr class="kop-print-hr">
		</div>
		</div>
	<h3 class="title">Laporan Rekap Biaya PKS</h3>
	<table class="no_border" style="width:35%">
			  
			  <tr>
					  <td>Mill</td>
					  <td>:</td>
					  <td>' .  $retrieveMill['nama'] . '</td>
					  
			  </tr>
			  <tr>
					  <td>Periode</td>
					  <td>:</td>
					  <td>' . tgl_indo($tgl_mulai) . ' s/d ' . tgl_indo($tgl_akhir) . '</td>
			  </tr>
			  
	  </table><br>
		
		';

		$html = $html . ' <table  border="1" width="100%" style="border-collapse: collapse;" >
			<thead>
				<tr>	
					<th rowspan="2">No Akun</th>
					<th rowspan="2">Nama Akun</th>
					<th rowspan="2">Biaya (Rp)</th>
					<th>Rp/Kg CPO</th>	
					<th>Rp/Kg PK</th>	
					<th>Rp/Kg TBS</th>	
					
				</tr>
				<tr>
					<th>' . $this->format_number_report($beratCpo / 1000, 2) . '</th>	
					<th>' . $this->format_number_report($beratPk / 1000, 2) . '</th>	
					<th>' . $this->format_number_report($beratTbs / 1000, 2) . '</th>		
								
				</tr>
			</thead>
			<tbody>';

		$produksi = 0;
		$total_biaya = 0;
		$total_rp_kg_cpo = 0;
		$total_rp_kg_pk = 0;
		$total_rp_kg_tbs = 0;
		$kelompok_biaya_A = array('63102', '63201', '63202', '63203', '63204', '63205', '63301', '63302', '63303', '63304', '63305', '63306', '63401');

		$kelompok_biaya_B = array('71101', '71102', '712', '71301', '71302', '71303', '71304', '71305', '71306', '71307', '71308', '714', '71501', '71502'); // '71899 dikeluarkan
		$no = 0;



		/* =============== COST  A ============================================= */
		$html = $html . ' 	<tr class=":: arc-content">
			<td colspan=6 style="position:relative;"><b> A. BIAYA PENGOLAHAN 	</b> </td>
			</td> </tr>';
		$jum_biaya_by_kelompok = 0;
		$rp_kg_cpo_by_kelompok = 0;
		$rp_kg_pk_by_kelompok = 0;
		$rp_kg_tbs_by_kelompok = 0;
		foreach ($kelompok_biaya_A as $key => $kel) {
			$retrieveHead = $this->db->query("select * from acc_akun where kode='" . $kel . "'")->row_array();
			$sql = "";
			$sql = "SELECT  sum(debet-kredit)as jumlah_biaya
					FROM acc_jurnal_ht a 
					inner join acc_jurnal_dt b on a.id=b.jurnal_id 
					INNER JOIN acc_akun c ON b.acc_akun_id=c.id
					where b.lokasi_id='" . $mill_id . "' 
					and a.tanggal>='" . $tgl_mulai . "' and a.tanggal<='" . $tgl_akhir . "'
					and c.kode like'" . $kel . "%'
					";

			$retrieveCost = $this->db->query($sql)->row_array();

			$jumlah_biaya = 0;
			if ($retrieveCost) {
				$jumlah_biaya = $retrieveCost['jumlah_biaya'];
			}
			$jum_biaya_by_kelompok =	$jum_biaya_by_kelompok + $jumlah_biaya;
			$total_biaya = $total_biaya + $jumlah_biaya;

			$no++;
			$rp_per_kg_cpo = $jumlah_biaya / $beratCpo;
			$rp_per_kg_pk = $jumlah_biaya / $beratPk;
			$rp_per_kg_tbs = $jumlah_biaya / $beratTbs;
			$rp_kg_cpo_by_kelompok = $rp_kg_cpo_by_kelompok + $rp_per_kg_cpo;
			$rp_kg_pk_by_kelompok = $rp_kg_pk_by_kelompok + $rp_per_kg_pk;
			$rp_kg_tbs_by_kelompok = $rp_kg_tbs_by_kelompok + $rp_per_kg_tbs;
			$total_rp_kg_cpo = $total_rp_kg_cpo  + $rp_per_kg_cpo;
			$total_rp_kg_pk = $total_rp_kg_pk  + $rp_per_kg_pk;
			$total_rp_kg_tbs = $total_rp_kg_tbs  + $rp_per_kg_tbs;
			$html = $html . ' 	<tr class=":: arc-content">
						<td>' . $kel . ' </td>
						<td>' . $retrieveHead['nama'] . ' </td>
						<td style="text-align: right;">' . $this->format_number_report($jumlah_biaya) . ' </td> 		
						<td style="text-align: right;">' . $this->format_number_report($rp_per_kg_cpo) . ' 	</td> 	
						<td style="text-align: right;">' . $this->format_number_report($rp_per_kg_pk) . ' </td> 		
						<td style="text-align: right;">' . $this->format_number_report($rp_per_kg_tbs) . ' </td> ';

			$html = $html . '</tr>';
		}
		$html = $html . ' 	<tr class=":: arc-content">
		<td colspan=2 style="position:relative;"><b> TOTAL 	</b> </td>
		<td style="text-align: right;"><b> ' . $this->format_number_report($jum_biaya_by_kelompok) . ' </b>	</td>			
		<td style="text-align: right;"><b> ' . $this->format_number_report($rp_kg_cpo_by_kelompok) . ' </b>	</td>			
		<td style="text-align: right;"><b> ' . $this->format_number_report($rp_kg_pk_by_kelompok) . ' </b>	</td>			
		<td style="text-align: right;"><b> ' . $this->format_number_report($rp_kg_tbs_by_kelompok) . ' </b>	</td>								
		 </tr>';
		/* ========================== END COST A =================================== */

		/* ============================ COST B ============== */
		$html = $html . ' 	<tr class=":: arc-content">
		<td colspan=6 style="position:relative;"><b> B. BIAYA OVERHEAD 	</b> </td>
		</td> </tr>';
		$jum_biaya_by_kelompok = 0;
		$rp_kg_cpo_by_kelompok = 0;
		$rp_kg_pk_by_kelompok = 0;
		$rp_kg_tbs_by_kelompok = 0;
		foreach ($kelompok_biaya_B as $key => $kel) {
			$retrieveHead = $this->db->query("select * from acc_akun where kode='" . $kel . "'")->row_array();
			$sql = "";
			$sql = "SELECT  sum(debet-kredit)as jumlah_biaya
				FROM acc_jurnal_ht a 
				inner join acc_jurnal_dt b on a.id=b.jurnal_id 
				INNER JOIN acc_akun c ON b.acc_akun_id=c.id
				where b.lokasi_id='" . $mill_id . "' 
				and a.tanggal>='" . $tgl_mulai . "' and a.tanggal<='" . $tgl_akhir . "'
				and c.kode like'" . $kel . "%'
				";

			$retrieveCost = $this->db->query($sql)->row_array();
			$jumlah_biaya = 0;
			if ($retrieveCost) {
				$jumlah_biaya = $retrieveCost['jumlah_biaya'];
			}
			$jum_biaya_by_kelompok =	$jum_biaya_by_kelompok + $jumlah_biaya;

			$total_biaya = $total_biaya + $jumlah_biaya;
			$no++;
			$rp_per_kg_cpo = $jumlah_biaya / $beratCpo;
			$rp_per_kg_pk = $jumlah_biaya / $beratPk;
			$rp_per_kg_tbs = $jumlah_biaya / $beratTbs;
			$rp_kg_cpo_by_kelompok = $rp_kg_cpo_by_kelompok + $rp_per_kg_cpo;
			$rp_kg_pk_by_kelompok = $rp_kg_pk_by_kelompok + $rp_per_kg_pk;
			$rp_kg_tbs_by_kelompok = $rp_kg_tbs_by_kelompok + $rp_per_kg_tbs;
			$total_rp_kg_cpo = $total_rp_kg_cpo  + $rp_per_kg_cpo;
			$total_rp_kg_pk = $total_rp_kg_pk  + $rp_per_kg_pk;
			$total_rp_kg_tbs = $total_rp_kg_tbs  + $rp_per_kg_tbs;
			$html = $html . ' 	<tr class=":: arc-content">
					<td>' . $kel . ' </td>
					<td>' . $retrieveHead['nama'] . ' </td>
					<td style="text-align: right;">' . $this->format_number_report($jumlah_biaya) . ' 							
					<td style="text-align: right;">' . $this->format_number_report($rp_per_kg_cpo) . ' 	</td> 	
					<td style="text-align: right;">' . $this->format_number_report($rp_per_kg_pk) . ' </td> 		
					<td style="text-align: right;">' . $this->format_number_report($rp_per_kg_tbs) . ' </td> ';

			$html = $html . '</tr>';
		}
		$html = $html . ' 	<tr class=":: arc-content">
		<td colspan=2 style="position:relative;"><b> TOTAL 	</b> </td>
		<td style="text-align: right;"><b> ' . $this->format_number_report($jum_biaya_by_kelompok) . ' </b>		</td>							
		<td style="text-align: right;"><b> ' . $this->format_number_report($rp_kg_cpo_by_kelompok) . ' </b>	</td>			
		<td style="text-align: right;"><b> ' . $this->format_number_report($rp_kg_pk_by_kelompok) . ' </b>	</td>			
		<td style="text-align: right;"><b> ' . $this->format_number_report($rp_kg_tbs_by_kelompok) . ' </b>	</td>								
		 </tr>';
		/* ============END COST B =============================*/

		$html = $html . ' 	
			<tr class=":: arc-content">
			<td  colspan=2 style="position:relative;">
			<b> GRAND	TOTAL</b> 

			</td>
			
			<td style="text-align: right;">	<b> ' . $this->format_number_report($total_biaya) . ' </b> </td>
			<td style="text-align: right;">	<b> ' . $this->format_number_report($total_rp_kg_cpo) . ' </b> </td>
			<td style="text-align: right;">	<b> ' . $this->format_number_report($total_rp_kg_pk) . ' </b> </td>
			<td style="text-align: right;">	<b> ' . $this->format_number_report($total_rp_kg_tbs) . ' </b> </td>
				
			</tr>
					</tbody>
				</table>
			';
		if ($format_laporan == 'xls') {
			$reader = new \PhpOffice\PhpSpreadsheet\Reader\Html;
			$spreadsheet = $reader->loadFromString($html);
			// $reader->setSheetIndex(1);
			//$spreadhseet = $reader->loadFromString($secondHtmlString, $spreadsheet);
			$objWriter = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
			header("Pragma: public");
			header("Expires: 0");
			header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
			header("Content-Type: application/force-download");
			header("Content-Type: application/octet-stream");
			header("Content-Type: application/download");
			header("Content-Disposition: attachment;filename=test.xlsx");
			header("Content-Transfer-Encoding: binary ");

			ob_end_clean();
			ob_start();
			$objWriter->save('php://output');
		} else if ($format_laporan == 'view') {
			echo $html;
		} else {
			$filename = 'report_' . time();
			$this->pdfgenerator->generate($html, $filename, true, 'A4', 'landscape');
		}
	}
	function laporan_biaya_rekap_mill_v2()
	{
		error_reporting(0);
		$mill_id    = $this->post('mill_id', true);
		$tgl_mulai =  $this->post('tgl_mulai', true);
		$tgl_akhir = $this->post('tgl_akhir', true);
		$stasiun_id = $this->post('stasiun_id', true);
		$format_laporan     = $this->post('format_laporan', true);

		$retrieveMill = $this->db->query("select * from gbm_organisasi where id=" . $mill_id . "")->row_array();

		$this->load->helper("terbilang");
		$this->load->helper("antech_helper");
		$this->load->library('pdfgenerator');
		if ($format_laporan == 'pdf') {
			$html = get_header_pdf_report();
		} else {
			$html = get_header_report_v2();
		}

		$html = $html . '
		<div class="row">
		<div class="span12">
		<br>
	  <div class="kop-print">
		<div class="kop-nama">PT. ANNAJAH TECHNOLOGY INDONESIA</div>
		<div class="kop-info"> Komplek Vila Sehati B12 , Tapos - Depok</div>
		<div class="kop-info">Telp : 081387373939</div>
	  </div>
		<hr class="kop-print-hr">
		</div>
		</div>
	<h3 class="title">Laporan Rekap Biaya PKS</h3>
	<table class="no_border" style="width:35%">
			  
			  <tr>
					  <td>Mill</td>
					  <td>:</td>
					  <td>' .  $retrieveMill['nama'] . '</td>
					  
			  </tr>
			  <tr>
					  <td>Periode</td>
					  <td>:</td>
					  <td>' . tgl_indo($tgl_mulai) . ' s/d ' . tgl_indo($tgl_akhir) . '</td>
			  </tr>
			  
	  </table><br>
		';

		$html = $html . ' <table  border="1" width="100%" style="border-collapse: collapse;" >
			<thead>
				<tr>	
				<th>No Akun</th>
				<th>Nama Akun</th>
				<th>Biaya (Rp)</th>				
				</tr>
			</thead>
			<tbody>';

		$produksi = 0;
		$total_biaya = 0;
		$kelompok_biaya_A = array('63102', '63201', '63202', '63203', '63204', '63205', '63301', '63302', '63303', '63304', '63305', '63306', '63401');

		$kelompok_biaya_B = array('71101', '71102', '712', '71301', '71302', '71303', '71304', '71305', '71306', '71307', '71308', '714', '71501', '71502'); // '71899 dikeluarkan
		$no = 0;

		/* =============== COST  A ============================================= */
		$html = $html . ' 	<tr class=":: arc-content">
			<td colspan=3 style="position:relative;"><b> A. BIAYA PENGOLAHAN 	</b> </td>
			</td> </tr>';
		$jum_biaya_by_kelompok = 0;
		foreach ($kelompok_biaya_A as $key => $kel) {
			$retrieveHead = $this->db->query("select * from acc_akun where kode='" . $kel . "'")->row_array();
			$sql = "";
			$sql = "SELECT  sum(debet-kredit)as jumlah_biaya
					FROM acc_jurnal_ht a 
					inner join acc_jurnal_dt b on a.id=b.jurnal_id 
					INNER JOIN acc_akun c ON b.acc_akun_id=c.id
					where b.lokasi_id='" . $mill_id . "' 
					and a.tanggal>='" . $tgl_mulai . "' and a.tanggal<='" . $tgl_akhir . "'
					and c.kode like '" . $kel . "%'
					";

			$retrieveCost = $this->db->query($sql)->row_array();

			$jumlah_biaya = 0;
			if ($retrieveCost) {
				$jumlah_biaya = $retrieveCost['jumlah_biaya'];
			}
			$jum_biaya_by_kelompok =	$jum_biaya_by_kelompok + $jumlah_biaya;
			$total_biaya = $total_biaya + $jumlah_biaya;

			$no++;
			$html = $html . ' 	<tr class=":: arc-content">
						<td>' . $kel . ' </td>
						<td>' . $retrieveHead['nama'] . ' </td>
						<td style="text-align: right;">' . $this->format_number_report($jumlah_biaya) . ' 							
						</td>';
			$html = $html . '</tr>';
		}
		$html = $html . ' 	<tr class=":: arc-content">
		<td colspan=2 style="position:relative;"><b> TOTAL 	</b> </td>
		<td style="text-align: right;"><b> ' . $this->format_number_report($jum_biaya_by_kelompok) . ' </b>									
		</td> </tr>';
		/* ========================== END COST A =================================== */

		/* ============================ COST B ============== */
		$html = $html . ' 	<tr class=":: arc-content">
		<td colspan=3 style="position:relative;"><b> B. BIAYA OVERHEAD 	</b> </td>
		</td> </tr>';
		$jum_biaya_by_kelompok = 0;
		foreach ($kelompok_biaya_B as $key => $kel) {
			$retrieveHead = $this->db->query("select * from acc_akun where kode='" . $kel . "'")->row_array();
			$sql = "";
			$sql = "SELECT  sum(debet-kredit)as jumlah_biaya
				FROM acc_jurnal_ht a 
				inner join acc_jurnal_dt b on a.id=b.jurnal_id 
				INNER JOIN acc_akun c ON b.acc_akun_id=c.id
				where b.lokasi_id='" . $mill_id . "' 
				and a.tanggal>='" . $tgl_mulai . "' and a.tanggal<='" . $tgl_akhir . "'
				and c.kode like'" . $kel . "%'
				";

			$retrieveCost = $this->db->query($sql)->row_array();
			$jumlah_biaya = 0;
			if ($retrieveCost) {
				$jumlah_biaya = $retrieveCost['jumlah_biaya'];
			}
			$jum_biaya_by_kelompok =	$jum_biaya_by_kelompok + $jumlah_biaya;
			$total_biaya = $total_biaya + $jumlah_biaya;
			$no++;
			$html = $html . ' 	<tr class=":: arc-content">
					<td>' . $kel . ' </td>
					<td>' . $retrieveHead['nama'] . ' </td>
					<td style="text-align: right;">' . $this->format_number_report($jumlah_biaya) . ' 							
					</td>';
			$html = $html . '</tr>';
		}
		$html = $html . ' 	<tr class=":: arc-content">
		<td colspan=2 style="position:relative;"><b> TOTAL 	</b> </td>
		<td style="text-align: right;"><b> ' . $this->format_number_report($jum_biaya_by_kelompok) . ' </b>									
		</td> </tr>';
		/* ============END COST B =============================*/

		$html = $html . ' 	
			<tr class=":: arc-content">
			<td  colspan=2 style="position:relative;">
			<b> GRAND	TOTAL</b> 

			</td>
			
			<td style="text-align: right;">
			<b> ' . $this->format_number_report($total_biaya) . ' </b> 
			</td>
				
			</tr>
					</tbody>
				</table>
			';
		if ($format_laporan == 'xls') {
			$reader = new \PhpOffice\PhpSpreadsheet\Reader\Html;
			$spreadsheet = $reader->loadFromString($html);
			// $reader->setSheetIndex(1);
			//$spreadhseet = $reader->loadFromString($secondHtmlString, $spreadsheet);
			$objWriter = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
			header("Pragma: public");
			header("Expires: 0");
			header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
			header("Content-Type: application/force-download");
			header("Content-Type: application/octet-stream");
			header("Content-Type: application/download");
			header("Content-Disposition: attachment;filename=test.xlsx");
			header("Content-Transfer-Encoding: binary ");

			ob_end_clean();
			ob_start();
			$objWriter->save('php://output');
		} else if ($format_laporan == 'view') {
			echo $html;
		} else {
			$filename = 'report_' . time();
			$this->pdfgenerator->generate($html, $filename, true, 'A4', 'landscape');
		}
	}
	function laporan_biaya_umum_bgt_post()
	{

		error_reporting(0);

		$nama_bulan     = $this->post('nama_bulan', true);
		$tahun     = $this->post('tahun', true);
		$estate_id     = $this->post('estate_id', true);
		$periode =  $this->post('periode', true);
		$tgl_mulai =  $periode . "-01";
		$d2 = new DateTime($tgl_mulai);
		$d2->modify('last day of this month');
		$tgl_akhir = $d2->format('Y-m-d');
		$format_laporan     = $this->post('format_laporan', true);

		$retrieveEstate = $this->db->query("select * from gbm_organisasi where id=" . $estate_id . "")->row_array();
		$retrieve_Ha = $this->db->query("SELECT sum(b.luasareaproduktif)AS ha FROM gbm_organisasi a   INNER  JOIN gbm_blok b ON a.id=b.organisasi_id
			INNER JOIN gbm_organisasi c ON a.parent_id=c.id INNER JOIN  gbm_organisasi d ON c.parent_id=d.id
			INNER JOIN  gbm_organisasi e ON d.parent_id=e.id where e.id=" . $estate_id . "")->row_array();

		$retrieve_kg = $this->db->query("SELECT SUM(hasil_kerja_brondolan)AS brondolan_kg,SUM(hasil_kerja_kg)AS jjg_kg
			 from est_bkm_panen_dt a inner JOIN est_bkm_panen_ht h ON a.bkm_panen_id=h.id
			 INNER JOIN gbm_organisasi b ON a.blok_id=b.id
			INNER JOIN gbm_blok c ON b.id=c.organisasi_id
			INNER JOIN gbm_organisasi d ON b.parent_id=d.id INNER JOIN  gbm_organisasi e ON d.parent_id=e.id
			INNER JOIN  gbm_organisasi f ON e.parent_id=f.id where f.id=" . $estate_id . " 
			and h.tanggal>='" . $tgl_mulai . "' and h.tanggal<='" . $tgl_akhir . "' ")->row_array();


		$luas_ha = 0;
		$jumlah_kg = 0;
		if ($retrieve_Ha) {
			$luas_ha = $retrieve_Ha['ha'];
		}
		if ($retrieve_kg) {
			$jumlah_kg = $retrieve_kg['brondolan_kg'] + $retrieve_kg['jjg_kg'];
		}


		$this->load->helper("terbilang");
		$this->load->helper("antech_helper");
		$this->load->library('pdfgenerator');
		if ($format_laporan == 'pdf') {
			$html = get_header_pdf_report();
		} else {
			$html = get_header_report();
		}


		$html = $html . '
		<h2>Laporan Rincian Cost </h2>
		<h3>Estate:' . $retrieveEstate['nama'] . ' </h3>
		<h4>' . $nama_bulan . ' - ' . $tahun . ' </h4>
		<h4>Luas: ' . number_format($luas_ha) . ' </h4>
		<h4>Produksi: ' . number_format($jumlah_kg) .  ' </h4>';;

		$html = $html . ' <table  >
			<thead>
				<tr>
					<th rowspan=2 width="4%">No.</th>			
					<th rowspan=2>Kode Akun</th>
					<th rowspan=2>Nama Akun</th>
					<th  colspan=3 >Real</th>
					<th  colspan=3 >Budget</th>
				</tr>
				<tr>
					<th style="text-align: right;">Biaya </th>
					<th style="text-align: right;">Cost/Ha </th>
					<th style="text-align: right;">Cost/Kg </th>	
					<th style="text-align: right;">Biaya </th>
					<th style="text-align: right;">Cost/Ha </th>
					<th style="text-align: right;">Cost/Kg </th>
				</tr>
			</thead>
			<tbody>';


		$ha = 0;
		$produksi = 0;
		$total_biaya = 0;
		$total_cost_per_ha = 0;
		$total_cost_per_kg = 0;
		$head_akun = '7';

		$sql = "";
		/* COST   */
		$sql = "SELECT c.kode as kode_akun,c.nama as nama_akun,d.kode as kode_estate,
				SUM(debet-kredit)AS jumlah_biaya
				FROM acc_jurnal_ht a inner join acc_jurnal_dt b 
				on a.id=b.jurnal_id inner join acc_akun c
				on b.acc_akun_id=c.id 
				inner join gbm_organisasi d on b.lokasi_id=d.id
				where d.kode='" . $retrieveEstate['kode'] . "' 
				and a.tanggal>='" . $tgl_mulai . "' and a.tanggal<='" . $tgl_akhir . "'
				and c.kode like'" . $head_akun . "%'
				GROUP BY  c.kode,c.nama,d.kode		
				order by c.kode";
		// var_dump($sql);exit();

		$retrieveCost = $this->db->query($sql)->result_array();

		$no = 0;
		$jumlah_biaya = 0;

		$total = 0;
		$jumlah_cost_per_ha = 0;
		$jumlah_cost_per_kg = 0;

		foreach ($retrieveCost as $key => $m) {
			$no++;
			$cost_per_ha = ($luas_ha == 0 || $m['jumlah_biaya'] == 0) ? 0 : ($m['jumlah_biaya'] / $luas_ha);
			$cost_per_kg = ($jumlah_kg == 0 || $m['jumlah_biaya'] == 0) ? 0 : ($m['jumlah_biaya'] / $jumlah_kg);
			$jumlah_biaya = $jumlah_biaya + $m['jumlah_biaya'];
			$jumlah_cost_per_ha = $jumlah_cost_per_ha + $cost_per_ha;
			$jumlah_cost_per_kg = $jumlah_cost_per_kg + $cost_per_kg;
			$total_biaya = $total_biaya + $m['jumlah_biaya'];
			$total_cost_per_ha = $total_cost_per_ha + $cost_per_ha;
			$total_cost_per_kg = $total_cost_per_kg + $cost_per_kg;
			$html = $html . ' 	<tr class=":: arc-content">
						<td style="position:relative;">	' . ($no) . '</td>
						<td>' . $m['kode_akun'] . ' </td>
						<td>' . $m['nama_akun'] . ' </td>
						<td style="text-align: right;">' . $this->format_number_report($m['jumlah_biaya'], 2) . ' 
						<td style="text-align: right;">' . $this->format_number_report($m['jumlah_biaya'] / $luas_ha, 2) . ' 
						<td style="text-align: right;">' . $this->format_number_report($m['jumlah_biaya'] / $jumlah_kg, 2) . ' 	
						<td style="text-align: right;">' . $this->format_number_report(0) . ' 
						<td style="text-align: right;">' . $this->format_number_report(0) . ' 
						<td style="text-align: right;">' . $this->format_number_report(0) . ' 										
						</td>';
			$html = $html . '</tr>';
		}
		$html = $html . ' 	<tr class=":: arc-content">
		<td colspan=3 style="position:relative;"> Total By UMUM	</td>
		<td style="text-align: right;">' . $this->format_number_report($jumlah_biaya, 2) . ' 
		<td style="text-align: right;">' . $this->format_number_report($jumlah_biaya / $luas_ha, 2) . ' 
		<td style="text-align: right;">' . $this->format_number_report($jumlah_biaya / $jumlah_kg, 2) . ' 									
		<td style="text-align: right;">' . $this->format_number_report(0) . ' 
		<td style="text-align: right;">' . $this->format_number_report(0) . ' 
		<td style="text-align: right;">' . $this->format_number_report(0) . ' 
		</td> </tr>';
		/* END COST   */


		// $html = $html . ' 	
		// 	<tr class=":: arc-content">
		// 	<td  colspan=3 style="position:relative;">
		// 		&nbsp;

		// 	</td>

		// 	<td style="text-align: right;">
		// 	' . $this->format_number_report($total_biaya) . ' 
		// 	</td>
		// 	<td style="text-align: right;">
		// 	' . $this->format_number_report($total_cost_per_ha) . ' 
		// 	</td>
		// 	<td style="text-align: right;">
		// 	' . $this->format_number_report($total_cost_per_ha) . ' 
		// 	</td>



		// 	</tr>
		// 		</tbody>
		// 		</table>
		// 	';
		$html = $html . ' 	
			<tr class=":: arc-content">
			</tr>
			</tbody>
			</table>
			';
		if ($format_laporan == 'xls') {
			$reader = new \PhpOffice\PhpSpreadsheet\Reader\Html;
			$spreadsheet = $reader->loadFromString($html);
			// $reader->setSheetIndex(1);
			//$spreadhseet = $reader->loadFromString($secondHtmlString, $spreadsheet);
			$objWriter = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
			header("Pragma: public");
			header("Expires: 0");
			header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
			header("Content-Type: application/force-download");
			header("Content-Type: application/octet-stream");
			header("Content-Type: application/download");
			header("Content-Disposition: attachment;filename=test.xlsx");
			header("Content-Transfer-Encoding: binary ");

			ob_end_clean();
			ob_start();
			$objWriter->save('php://output');
		} else if ($format_laporan == 'view') {
			echo $html;
		} else {
			$filename = 'report_' . time();
			$this->pdfgenerator->generate($html, $filename, true, 'A4', 'landscape');
		}
	}

	function laporan_cost_stasiun_detail_post()
	{

		error_reporting(0);
		$mill_id     = $this->post('mill_id', true);
		$tgl_mulai =  $this->post('tgl_mulai', true);
		$tgl_akhir = $this->post('tgl_akhir', true);
		$stasiun_id = $this->post('stasiun_id', true);
		$format_laporan     = $this->post('format_laporan', true);


		$retrieveMill = $this->db->query("select * from gbm_organisasi where id=" . $mill_id . "")->row_array();
		$retrieveStasiun = $this->db->query("select * from gbm_organisasi where id=" . $stasiun_id . "")->row_array();

		// $retrieveCost = $this->db->query("SELECT tanggal,kode_blok,nama_blok,nama_afdeling,nama_rayon,nama_estate,
		// 	SUM(debet-kredit)AS total,
		// 	sum(CASE WHEN jenis = 'UPAH' THEN (debet-kredit) else 0 end) as upah,
		// 		sum(CASE WHEN jenis = 'BAHAN' THEN (debet-kredit) else 0 end) as bahan,
		// 			sum(CASE WHEN jenis = '' THEN (debet-kredit) else 0 end) as lainnya
		// 	FROM est_cost_blok_vw 
		// 	where kode_estate='" . $retrieveEstate['kode'] . "' 
		// 	and kode_blok='" . $retrieveBlok['kode'] . "' and tanggal>='" . $tgl_mulai . "' and tanggal<='" . $tgl_akhir . "'
		// 	GROUP BY  tanggal, kode_blok,nama_blok,nama_afdeling,nama_rayon,nama_estate		
		// 	 order by kode_blok,tanggal")->result_array();

		$retrieveCost = $this->db->query("SELECT tanggal,kode_stasiun,nama_stasiun,
			 SUM(debet-kredit)AS total,
			 sum(CASE WHEN jenis = 'UPAH' THEN (debet-kredit) else 0 end) as upah,
				 sum(CASE WHEN jenis = 'BAHAN' THEN (debet-kredit) else 0 end) as bahan,
					 sum(CASE WHEN jenis = '' THEN (debet-kredit) else 0 end) as lainnya
			 FROM pks_cost_per_stasiun 
			 where kode_mill='" . $retrieveMill['kode'] . "' 
			 and kode_stasiun='" . $retrieveStasiun['kode'] . "'
			 and tanggal>='" . $tgl_mulai . "' and tanggal<='" . $tgl_akhir . "'
			 GROUP BY  tanggal,kode_stasiun,nama_stasiun		
			  order by nama_stasiun,tanggal")->result_array();

		$this->load->helper("antech_helper");
		$this->load->library('pdfgenerator');
		if ($format_laporan == 'pdf') {
			$html = get_header_pdf_report();
		} else {
			$html = get_header_report();
		}


		$html = $html . '
<h2>Laporan Rincian Cost Stasiun</h2>
<h3>Mill:' . $retrieveMill['nama'] . ' </h3>
<h3>Stasiun:' . $retrieveStasiun['nama'] . ' </h3>
<h4>' . $tgl_mulai . ' - ' . $tgl_akhir . ' </h4>';

		$html = $html . ' <table  >
			<thead>
				<tr>
				<th width="4%">No.</th>			
				<th>Tanggal</th>
				<th style="text-align: right;">Upah </th>
				<th style="text-align: right;">Bahan</th>
				<th style="text-align: right;">Lainnya </th>
				<th style="text-align: right;">Total </th>
				
				</tr>
			</thead>
			<tbody>';


		$no = 0;
		$bahan = 0;
		$lainnya = 0;
		$upah = 0;
		$total = 0;


		foreach ($retrieveCost as $key => $m) {
			$no++;
			$upah = $upah + $m['upah'];
			$bahan = $bahan + $m['bahan'];
			$lainnya = $lainnya + $m['lainnya'];
			$total = $total + $m['total'];
			$html = $html . ' 	<tr class=":: arc-content">
						<td style="position:relative;">	' . ($no) . '</td>
						<td>' . $m['tanggal'] . ' </td>
						
						<td style="text-align: right;">' . $this->format_number_report($m['upah']) . ' 
						<td style="text-align: right;">' . $this->format_number_report($m['bahan']) . ' 
						<td style="text-align: right;">' . $this->format_number_report($m['lainnya']) . ' 
						<td style="text-align: right;">' . $this->format_number_report($m['total']) . ' 
													
						</td>';

			$html = $html . '</tr>';
		}

		$html = $html . ' 	
						<tr class=":: arc-content">
						<td style="position:relative;">
							&nbsp;

						</td>
						<td>
							&nbsp;
						</td>
					
						<td style="text-align: right;">
						' . $this->format_number_report($upah) . ' 
						</td>
						<td style="text-align: right;">
						' . $this->format_number_report($bahan) . ' 
						</td>
						<td style="text-align: right;">
						' . $this->format_number_report($lainnya) . ' 
						</td>
						<td style="text-align: right;">
						' . $this->format_number_report($total) . ' 
						</td>
						
						
						</tr>
								</tbody>
							</table>
						';
		if ($format_laporan == 'xls') {
			$reader = new \PhpOffice\PhpSpreadsheet\Reader\Html;
			$spreadsheet = $reader->loadFromString($html);
			// $reader->setSheetIndex(1);
			//$spreadhseet = $reader->loadFromString($secondHtmlString, $spreadsheet);
			$objWriter = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
			header("Pragma: public");
			header("Expires: 0");
			header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
			header("Content-Type: application/force-download");
			header("Content-Type: application/octet-stream");
			header("Content-Type: application/download");
			header("Content-Disposition: attachment;filename=test.xlsx");
			header("Content-Transfer-Encoding: binary ");

			ob_end_clean();
			ob_start();
			$objWriter->save('php://output');
		} else if ($format_laporan == 'view') {
			echo $html;
		} else {
			$filename = 'report_' . time();
			$this->pdfgenerator->generate($html, $filename, true, 'A4', 'landscape');
		}
	}
	function laporan_cost_stasiun_rekap_post()
	{

		error_reporting(0);
		$mill_id     = $this->post('mill_id', true);
		$tgl_mulai =  $this->post('tgl_mulai', true);
		$tgl_akhir = $this->post('tgl_akhir', true);
		// $blok_id = $this->post('blok_id', true);
		$format_laporan     = $this->post('format_laporan', true);


		$retrieveMill = $this->db->query("select * from gbm_organisasi where id=" . $mill_id . "")->row_array();

		$retrieveCost = $this->db->query("SELECT kode_stasiun,nama_stasiun,
			SUM(debet-kredit)AS total,
			sum(CASE WHEN jenis = 'UPAH' THEN (debet-kredit) else 0 end) as upah,
				sum(CASE WHEN jenis = 'BAHAN' THEN (debet-kredit) else 0 end) as bahan,
					sum(CASE WHEN jenis = '' THEN (debet-kredit) else 0 end) as lainnya
			FROM pks_cost_per_stasiun 
			where kode_mill='" . $retrieveMill['kode'] . "' 
			and tanggal>='" . $tgl_mulai . "' and tanggal<='" . $tgl_akhir . "'
			GROUP BY  kode_stasiun,nama_stasiun		
			 order by nama_stasiun")->result_array();

		// var_dump($results);
		$this->load->helper("terbilang");
		$this->load->helper("antech_helper");
		$this->load->library('pdfgenerator');
		if ($format_laporan == 'pdf') {
			$html = get_header_pdf_report();
		} else {
			$html = get_header_report();
		}


		$html = $html . '
<h2>Laporan Rekap Cost Stasiun</h2>
<h3>Mill:' . $retrieveMill['nama'] . ' </h3>
<h4>' . $tgl_mulai . ' - ' . $tgl_akhir . ' </h4>';

		$html = $html . ' <table  >
			<thead>
				<tr>
				<th width="4%">No.</th>			
				<th>Stasiun</th>
				<th style="text-align: right;">Upah </th>
				<th style="text-align: right;">Bahan</th>
				<th style="text-align: right;">Lainnya </th>
				<th style="text-align: right;">Total </th>
				
				</tr>
			</thead>
			<tbody>';


		$no = 0;
		$bahan = 0;
		$lainnya = 0;
		$upah = 0;
		$total = 0;


		foreach ($retrieveCost as $key => $m) {
			$no++;
			$upah = $upah + $m['upah'];
			$bahan = $bahan + $m['bahan'];
			$lainnya = $lainnya + $m['lainnya'];
			$total = $total + $m['total'];
			$html = $html . ' 	<tr class=":: arc-content">
						<td style="position:relative;">	' . ($no) . '</td>
						<td>
						' . $m['nama_stasiun'] . ' 
						
						</td>
												
						<td style="text-align: right;">' . $this->format_number_report($m['upah']) . ' 
						<td style="text-align: right;">' . $this->format_number_report($m['bahan']) . ' 
						<td style="text-align: right;">' . $this->format_number_report($m['lainnya']) . ' 
						<td style="text-align: right;">' . $this->format_number_report($m['total']) . ' 
													
						</td>';

			$html = $html . '</tr>';
		}

		$html = $html . ' 	
						<tr class=":: arc-content">
						<td style="position:relative;">
							&nbsp;

						</td>
						
						
						<td>
							&nbsp;
						</td>
					
					
						<td style="text-align: right;">
						' . $this->format_number_report($upah) . ' 
						</td>
						<td style="text-align: right;">
						' . $this->format_number_report($bahan) . ' 
						</td>
						<td style="text-align: right;">
						' . $this->format_number_report($lainnya) . ' 
						</td>
						<td style="text-align: right;">
						' . $this->format_number_report($total) . ' 
						</td>
						
						
						</tr>
								</tbody>
							</table>
						';
		if ($format_laporan == 'xls') {
			$reader = new \PhpOffice\PhpSpreadsheet\Reader\Html;
			$spreadsheet = $reader->loadFromString($html);
			// $reader->setSheetIndex(1);
			//$spreadhseet = $reader->loadFromString($secondHtmlString, $spreadsheet);
			$objWriter = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
			header("Pragma: public");
			header("Expires: 0");
			header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
			header("Content-Type: application/force-download");
			header("Content-Type: application/octet-stream");
			header("Content-Type: application/download");
			header("Content-Disposition: attachment;filename=test.xlsx");
			header("Content-Transfer-Encoding: binary ");

			ob_end_clean();
			ob_start();
			$objWriter->save('php://output');
		} else if ($format_laporan == 'view') {
			echo $html;
		} else {
			$filename = 'report_' . time();
			$this->pdfgenerator->generate($html, $filename, true, 'A4', 'landscape');
		}
	}

	function laporan_pemakaian_bahan_stasiun_post()
	{

		error_reporting(0);

		$mill_id     = $this->post('mill_id', true);
		$tgl_mulai =  $this->post('tgl_mulai', true);
		$tgl_akhir = $this->post('tgl_akhir', true);
		$stasiun_id = $this->post('stasiun_id', true);
		$format_laporan     = $this->post('format_laporan', true);

		$retrieveMill = $this->db->query("select * from gbm_organisasi where id=" . $mill_id . "")->row_array();
		$nama_stasiun = "Semua";
		$q = "SELECT a.*,b.kode as kode_item,b.nama as nama_item,c.kode as uom,d.kode AS kode_mesin,d.kode AS nama_mesin, e.kode AS kode_stasiun,e.nama AS nama_stasiun,
		f.kode AS kode_kegiatan,f.nama AS nama_kegiatan,e.parent_id AS lokasi_id
		 FROM inv_transaksi_harian a 
		INNER JOIN inv_item b ON a.item_id=b.id
		INNER JOIN gbm_uom c ON b.uom_id=c.id
		INNER JOIN gbm_organisasi d ON a.blok_stasiun_id=d.id
		INNER JOIN gbm_organisasi e ON d.parent_id=e.id
		INNER JOIN acc_kegiatan f ON a.kegiatan_id=f.id
		where e.parent_id='" . $mill_id . "' 
			 and a.tanggal>='" . $tgl_mulai . "' and a.tanggal<='" . $tgl_akhir . "'";
		if ($stasiun_id) {
			$retrieveStasiun = $this->db->query("select * from gbm_organisasi where id=" . $stasiun_id . "")->row_array();
			$nama_stasiun = $retrieveStasiun['nama'];
			$q = $q . " and e.id=" . $stasiun_id . "";
		}
		$q = $q . "  ORDER BY e.nama,a.tanggal ;";

		$retrieveCost = $this->db->query($q)->result_array();
		// var_dump($retrieveCost);return;
		$this->load->helper("antech_helper");
		$this->load->library('pdfgenerator');
		if ($format_laporan == 'pdf') {
			$html = get_header_pdf_report();
		} else {
			$html = get_header_report();
		}


		$html = $html . '
<h2>Laporan Pemakaian Bahan Stasiun</h2>
<h3>Mill:' . $retrieveMill['nama'] . ' </h3>
<h3>Stasiun:' . $nama_stasiun . ' </h3>
<h4>' . $tgl_mulai . ' - ' . $tgl_akhir . ' </h4>';

		$html = $html . ' <table>
			<thead>
				<tr>
				<th width="4%">No.</th>	
				<th>Stasiun</th>	
				<th>Mesin</th>		
				<th>Tanggal</th>
				<th>No Transaki</th>
				<th>Alokasi/kegiatan</th>
				<th>Item </th>
				<th>uom</th>
				<th style="text-align: right;">Qty </th>
				<th style="text-align: right;">Harga </th>
				<th style="text-align: right;">Nilai </th>				
				</tr>
			</thead>
			<tbody>';

		$no = 0;
		$sub_total = 0;
		$total = 0;
		for ($i = 0; $i < count($retrieveCost); $i++) {
			// foreach ($retrieveCost as $key => $m) {
			$m = $retrieveCost[$i];
			$no++;
			$sub_total = $sub_total + $m['nilai_keluar'];
			$total = $total + $m['nilai_keluar'];
			$html = $html . ' 	<tr class=":: arc-content">
						<td style="position:relative;">	' . ($no) . '</td>
						<td>' . $m['nama_stasiun'] . ' </td>
						<td>' . $m['nama_mesin'] . ' </td>
						<td>' . $m['tanggal'] . ' </td>
						<td>' . $m['no_transaksi'] . ' </td>
						<td>' . $m['nama_kegiatan'] . ' </td>
						<td>' . $m['kode_item'] . ' - ' . $m['nama_item'] . ' </td>
						<td>' . $m['uom'] . ' </td>						
						<td style="text-align: right;">' . $this->format_number_report($m['qty_keluar']) . ' 
						<td style="text-align: right;">' . $this->format_number_report($m['nilai_keluar'] / $m['qty_keluar']) . ' 
						<td style="text-align: right;">' . $this->format_number_report($m['nilai_keluar']) . ' 																			
						</td>';
			$html = $html . '</tr>';

			if ($i < (count($retrieveCost) - 1)) {
				if (($i + 1) <= (count($retrieveCost) - 1)) {
					if ($m['kode_stasiun'] != $retrieveCost[($i + 1)]['kode_stasiun']) {
						$html = $html . ' 	
						<tr class=":: arc-content">
						<td colspan=10 style="text-align: right;"><b>SUB TOTAL  ' . $m['nama_stasiun'] . '</b>
						</td>
						
						<td style="text-align: right;"><b>' . $this->format_number_report($sub_total) . ' </b>	
						</td>		
						</tr>
						';
						$sub_total = 0;
						$no = 0;
					}
				}
			} else {
				$html = $html . ' 	
						<tr class=":: arc-content">
						<td colspan=10 style="text-align: right;"><b>SUB TOTAL  ' . $m['nama_stasiun'] . '</b>
						</td>
						<td style="text-align: right;">' . $this->format_number_report($sub_total) . ' 	
						</td>		
						</tr>
						';
				$sub_total = 0;
				$no = 0;
			}
		}

		$html = $html . ' 	
						<tr class=":: arc-content">
						<td colspan=10 style="text-align: right;"><b>TOTAL</b>
						</td>
						<td style="text-align: right;"><b>' . $this->format_number_report($total) . ' </b>				
						</tr>
						</tbody>
						</table>
						';
		if ($format_laporan == 'xls') {
			$reader = new \PhpOffice\PhpSpreadsheet\Reader\Html;
			$spreadsheet = $reader->loadFromString($html);
			// $reader->setSheetIndex(1);
			//$spreadhseet = $reader->loadFromString($secondHtmlString, $spreadsheet);
			$objWriter = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
			header("Pragma: public");
			header("Expires: 0");
			header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
			header("Content-Type: application/force-download");
			header("Content-Type: application/octet-stream");
			header("Content-Type: application/download");
			header("Content-Disposition: attachment;filename=test.xlsx");
			header("Content-Transfer-Encoding: binary ");

			ob_end_clean();
			ob_start();
			$objWriter->save('php://output');
		} else if ($format_laporan == 'view') {
			echo $html;
		} else {
			$filename = 'report_' . time();
			$this->pdfgenerator->generate($html, $filename, true, 'A4', 'landscape');
		}
	}
	function laporan_pemakaian_bahan_estate_post()
	{

		error_reporting(0);

		$estate_id     = $this->post('estate_id', true);
		$tgl_mulai =  $this->post('tgl_mulai', true);
		$tgl_akhir = $this->post('tgl_akhir', true);
		$afdeling_id = $this->post('afdeling_id', true);
		$format_laporan     = $this->post('format_laporan', true);

		$retrieveEstate = $this->db->query("select * from gbm_organisasi where id=" . $estate_id . "")->row_array();
		$nama_afdeling = "Semua";
		$q = "SELECT a.*,b.kode as kode_item,b.nama as nama_item,c.kode as uom,d.kode AS kode_blok,d.kode AS nama_blok, e.kode AS kode_afdeling,e.nama AS nama_afdeling,
		f.kode AS kode_kegiatan,f.nama AS nama_kegiatan,g.parent_id AS lokasi_id
		 FROM inv_transaksi_harian a 
		INNER JOIN inv_item b ON a.item_id=b.id
		INNER JOIN gbm_uom c ON b.uom_id=c.id
		INNER JOIN gbm_organisasi d ON a.blok_stasiun_id=d.id
		INNER JOIN gbm_organisasi e ON d.parent_id=e.id
		INNER JOIN acc_kegiatan f ON a.kegiatan_id=f.id
		INNER JOIN gbm_organisasi g ON g.id=e.parent_id
		where g.parent_id='" . $estate_id . "' 
			 and a.tanggal>='" . $tgl_mulai . "' and a.tanggal<='" . $tgl_akhir . "'";
		if ($afdeling_id) {
			$retrieveStasiun = $this->db->query("select * from gbm_organisasi where id=" . $afdeling_id . "")->row_array();
			$nama_stasiun = $retrieveStasiun['nama'];
			$q = $q . " and e.id=" . $afdeling_id . "";
		}
		$q = $q . "  ORDER BY e.nama,a.tanggal ;";

		$retrieveCost = $this->db->query($q)->result_array();
		// var_dump($retrieveCost);return;
		$this->load->helper("antech_helper");
		$this->load->library('pdfgenerator');
		if ($format_laporan == 'pdf') {
			$html = get_header_pdf_report();
		} else {
			$html = get_header_report_v2();
		}


		$html = $html . '
		<div class="row">
  	<div class="span12">
	  <br>
	<div class="kop-print">
	  <div class="kop-nama">PT. ANNAJAH TECHNOLOGY INDONESIA</div>
	  <div class="kop-info"> Komplek Vila Sehati B12 , Tapos - Depok</div>
	  <div class="kop-info">Telp : 081387373939</div>
	</div>
	  <hr class="kop-print-hr">
  	</div>
  	</div>
  <h3 class="title">LAPORAN RINCIAN PEMAKAIAN BAHAN PER BLOK</h3>
  <table class="no_border" style="width:30%">
			
			<tr>
					<td>ESTATE</td>
					<td>:</td>
					<td>' .  $retrieveEstate['nama'] . '</td>
			</tr>
			<tr>
					<td>AFDELING</td>
					<td>:</td>
					<td>' .  $nama_afdeling . '</td>
			</tr>
			<tr>	
					<td>PERIODE</td>
					<td>:</td>
					<td>' . $tgl_mulai . ' s/d ' . $tgl_akhir . '</td>
			</tr>
			
	</table>
			<br>';

		$html = $html . ' <table  border="1" width="100%" style="border-collapse: collapse;">
			<thead>
				<tr>
				<th style="text-align: center;" width="4%">No.</th>	
				<th>Afdeling</th>	
				<th>Blok</th>		
				<th>Tanggal</th>
				<th>No Transaki</th>
				<th>Alokasi/kegiatan</th>
				<th>Item </th>
				<th>uom</th>
				<th style="text-align: center;">Qty </th>
				<th style="text-align: center;">Harga </th>
				<th style="text-align: center;">Nilai </th>				
				</tr>
			</thead>
			<tbody>';

		$no = 0;
		$sub_total = 0;
		$total = 0;
		for ($i = 0; $i < count($retrieveCost); $i++) {
			// foreach ($retrieveCost as $key => $m) {
			$m = $retrieveCost[$i];
			$no++;
			$sub_total = $sub_total + $m['nilai_keluar'];
			$total = $total + $m['nilai_keluar'];
			$html = $html . ' 	<tr class=":: arc-content">
						<td style="position:relative; text-align: center;">	' . ($no) . '</td>
						<td>' . $m['nama_afdeling'] . ' </td>
						<td style="text-align: center;">' . $m['kode_blok'] . ' </td>
						<td style="text-align: center;">' . tgl_indo_normal($m['tanggal']) . ' </td>
						<td>' . $m['no_transaksi'] . ' </td>
						<td>' . $m['nama_kegiatan'] . ' </td>
						<td>' . $m['kode_item'] . ' - ' . $m['nama_item'] . ' </td>
						<td style="text-align: center;">' . $m['uom'] . ' </td>						
						<td style="text-align: right;">' . $this->format_number_report($m['qty_keluar']) . ' 
						<td style="text-align: right;">' . $this->format_number_report($m['nilai_keluar'] / $m['qty_keluar']) . ' 
						<td style="text-align: right;">' . $this->format_number_report($m['nilai_keluar']) . ' 																			
						</td>';
			$html = $html . '</tr>';

			if ($i < (count($retrieveCost) - 1)) {
				if (($i + 1) <= (count($retrieveCost) - 1)) {
					if ($m['kode_afdeling'] != $retrieveCost[($i + 1)]['kode_afdeling']) {
						$html = $html . ' 	
						<tr class=":: arc-content">
						<td colspan=10 style="text-align: right;"><b>SUB TOTAL  ' . $m['nama_afdeling'] . '</b>
						</td>
						
						<td style="text-align: right;"><b>' . $this->format_number_report($sub_total) . ' </b>	
						</td>		
						</tr>
						';
						$sub_total = 0;
						$no = 0;
					}
				}
			} else {
				$html = $html . ' 	
						<tr class=":: arc-content">
						<td colspan=10 style="text-align: right;"><b>SUB TOTAL  ' . $m['nama_afdeling'] . '</b>
						</td>
						<td style="text-align: right;">' . $this->format_number_report($sub_total) . ' 	
						</td>		
						</tr>
						';
				$sub_total = 0;
				$no = 0;
			}
		}

		$html = $html . ' 	
						<tr class=":: arc-content">
						<td colspan=10 style="text-align: right;"><b>TOTAL</b>
						</td>
						<td style="text-align: right;"><b>' . $this->format_number_report($total) . ' </b>				
						</tr>
						</tbody>
						</table>
						';
		if ($format_laporan == 'xls') {
			$reader = new \PhpOffice\PhpSpreadsheet\Reader\Html;
			$spreadsheet = $reader->loadFromString($html);
			// $reader->setSheetIndex(1);
			//$spreadhseet = $reader->loadFromString($secondHtmlString, $spreadsheet);
			$objWriter = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
			header("Pragma: public");
			header("Expires: 0");
			header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
			header("Content-Type: application/force-download");
			header("Content-Type: application/octet-stream");
			header("Content-Type: application/download");
			header("Content-Disposition: attachment;filename=test.xlsx");
			header("Content-Transfer-Encoding: binary ");

			ob_end_clean();
			ob_start();
			$objWriter->save('php://output');
		} else if ($format_laporan == 'view') {
			echo $html;
		} else {
			$filename = 'report_' . time();
			$this->pdfgenerator->generate($html, $filename, true, 'A4', 'landscape');
		}
	}
	function laporan_pemakaian_bahan_traksi_post()
	{

		error_reporting(0);

		$traksi_id     = $this->post('traksi_id', true);
		$tgl_mulai =  $this->post('tgl_mulai', true);
		$tgl_akhir = $this->post('tgl_akhir', true);
		$kendaraan_id = $this->post('kendaraan_id', true);
		$format_laporan     = $this->post('format_laporan', true);

		$retrieveEstate = $this->db->query("select * from gbm_organisasi where id=" . $traksi_id . "")->row_array();
		$nama_kendaraan = "Semua";
		$q = "select a.*,b.kode as kode_item,b.nama as nama_item,c.kode as uom,d.kode AS kode_kendaraan,d.nama AS nama_kendaraan, e.kode AS kode_traksi,e.nama AS nama_traksi,
		f.kode AS kode_kegiatan,f.nama AS nama_kegiatan
		 FROM inv_transaksi_harian a 
		INNER JOIN inv_item b ON a.item_id=b.id
		INNER JOIN gbm_uom c ON b.uom_id=c.id
		INNER JOIN trk_kendaraan d ON a.kendaraan_id=d.id
		INNER JOIN gbm_organisasi e ON d.traksi_id=e.id
		INNER JOIN acc_kegiatan f ON a.kegiatan_id=f.id
		where e.id='" . $traksi_id . "' 
			 and a.tanggal>='" . $tgl_mulai . "' and a.tanggal<='" . $tgl_akhir . "'";
		if ($kendaraan_id) {
			$retrieveKendaraan = $this->db->query("select * from trk_kendaraan where id=" . $kendaraan_id . "")->row_array();
			$nama_kendaraan = $retrieveKendaraan['nama'];
			$q = $q . " and d.id=" . $kendaraan_id . "";
		}
		$q = $q . "  ORDER BY d.nama,a.tanggal ;";

		$retrieveCost = $this->db->query($q)->result_array();
		// var_dump($retrieveCost);return;
		$this->load->helper("antech_helper");
		$this->load->library('pdfgenerator');
		if ($format_laporan == 'pdf') {
			$html = get_header_pdf_report();
		} else {
			$html = get_header_report_v2();
		}


		$html = $html . '
<div class="row">
  <div class="span12">
	  <br>
	<div class="kop-print">
    <div class="kop-nama">PT. ANNAJAH TECHNOLOGY INDONESIA</div>
    <div class="kop-info"> Komplek Vila Sehati B12 , Tapos - Depok</div>
    <div class="kop-info">Telp : 081387373939</div>
  	</div>
	  <hr class="kop-print-hr">
  </div>
</div>
		<h3 class="title">Laporan Pemakaian Bahan Traksi</h3>
  <table class="no_border" style="width:30%">
			
			<tr>
					<td>Traksi</td>
					<td>:</td>
					<td>' .  $retrieveTraksi['nama'] . '</td>
			</tr>
			<tr>
					<td>Kendaraan/AB/Mesin</td>
					<td>:</td>
					<td>' .  $nama_kendaraan . '</td>
			</tr>
			<tr>	
					<td>Periode</td>
					<td>:</td>
					<td>' . tgl_indo($tgl_mulai) . ' s/d ' . tgl_indo($tgl_akhir) . '</td>
			</tr>

			
	</table>
	<br>';

		$html = $html . ' <table>
			<thead>
				<tr>
				<th width="4%">No.</th>	
				<th>Kendaraan/AB/Mesin</th>	
				<th>Tanggal</th>
				<th>No Transaki</th>
				<th>Alokasi/kegiatan</th>
				<th>Item </th>
				<th>uom</th>
				<th style="text-align: center;">Qty </th>
				<th style="text-align: center;">Harga (Rp)</th>
				<th style="text-align: center;">Nilai (Rp)</th>				
				</tr>
			</thead>
			<tbody>';

		$no = 0;
		$sub_total = 0;
		$total = 0;
		for ($i = 0; $i < count($retrieveCost); $i++) {
			// foreach ($retrieveCost as $key => $m) {
			$m = $retrieveCost[$i];
			$no++;
			$sub_total = $sub_total + $m['nilai_keluar'];
			$total = $total + $m['nilai_keluar'];
			$html = $html . ' 	<tr class=":: arc-content">
						<td style="position:relative; text-align: center;">	' . ($no) . '</td>
						<td>' . $m['nama_kendaraan'] . ' - ' . $m['kode_kendaraan'] . ' </td>
						<td>' . tgl_indo_normal($m['tanggal']) . ' </td>
						<td>' . $m['no_transaksi'] . ' </td>
						<td>' . $m['nama_kegiatan'] . ' </td>
						<td>' . $m['kode_item'] . ' - ' . $m['nama_item'] . ' </td>
						<td style="text-align: center;">' . $m['uom'] . ' </td>						
						<td style="text-align: right;">' . $this->format_number_report($m['qty_keluar']) . ' 
						<td style="text-align: right;">' . $this->format_number_report($m['nilai_keluar'] / $m['qty_keluar']) . ' 
						<td style="text-align: right;">' . $this->format_number_report($m['nilai_keluar']) . ' 																			
						</td>';
			$html = $html . '</tr>';

			if ($i < (count($retrieveCost) - 1)) {
				if (($i + 1) <= (count($retrieveCost) - 1)) {
					if ($m['kode_kendaraan'] != $retrieveCost[($i + 1)]['kode_kendaraan']) {
						$html = $html . ' 	
						<tr class=":: arc-content">
						<td colspan=9 style="text-align: right;"><b>SUB TOTAL  ' . $m['nama_kendaraan'] . '</b>
						</td>
						
						<td style="text-align: right;"><b>' . $this->format_number_report($sub_total) . ' </b>	
						</td>		
						</tr>
						';
						$sub_total = 0;
						$no = 0;
					}
				}
			} else {
				$html = $html . ' 	
						<tr class=":: arc-content">
						<td colspan=9 style="text-align: right;"><b>SUB TOTAL  ' . $m['nama_kendaraan'] . '</b>
						</td>
						<td style="text-align: right;">' . $this->format_number_report($sub_total) . ' 	
						</td>		
						</tr>
						';
				$sub_total = 0;
				$no = 0;
			}
		}

		$html = $html . ' 	
						<tr class=":: arc-content">
						<td colspan=9 style="text-align: right;"><b>TOTAL</b>
						</td>
						<td style="text-align: right;"><b>' . $this->format_number_report($total) . ' </b>				
						</tr>
						</tbody>
						</table>
						';
		if ($format_laporan == 'xls') {
			$reader = new \PhpOffice\PhpSpreadsheet\Reader\Html;
			$spreadsheet = $reader->loadFromString($html);
			// $reader->setSheetIndex(1);
			//$spreadhseet = $reader->loadFromString($secondHtmlString, $spreadsheet);
			$objWriter = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
			header("Pragma: public");
			header("Expires: 0");
			header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
			header("Content-Type: application/force-download");
			header("Content-Type: application/octet-stream");
			header("Content-Type: application/download");
			header("Content-Disposition: attachment;filename=test.xlsx");
			header("Content-Transfer-Encoding: binary ");

			ob_end_clean();
			ob_start();
			$objWriter->save('php://output');
		} else if ($format_laporan == 'view') {
			echo $html;
		} else {
			$filename = 'report_' . time();
			$this->pdfgenerator->generate($html, $filename, true, 'A4', 'landscape');
		}
	}
	function laporan_cost_blok_rekap_kegiatan_post()
	{

		error_reporting(0);
		$estate_id     = $this->post('estate_id', true);
		$tgl_mulai =  $this->post('tgl_mulai', true);
		$tgl_akhir = $this->post('tgl_akhir', true);
		// $blok_id = $this->post('blok_id', true);
		$format_laporan     = $this->post('format_laporan', true);


		$retrieveEstate = $this->db->query("select * from gbm_organisasi where id=" . $estate_id . "")->row_array();
		//$retrieveBlok = $this->db->query("select * from gbm_organisasi where id=" . $blok_id . "")->row_array();

		$retrieveCost = $this->db->query("SELECT kode_blok,nama_blok,nama_afdeling,nama_rayon,
		nama_estate,kode_kegiatan,nama_kegiatan,tahuntanam,intiplasma,
		SUM(debet-kredit)AS total
			FROM est_cost_blok_vw 
			where kode_estate='" . $retrieveEstate['kode'] . "' 
			and tanggal>='" . $tgl_mulai . "' and tanggal<='" . $tgl_akhir . "'
			GROUP by kode_blok,nama_blok,nama_afdeling,nama_rayon,nama_estate,kode_kegiatan,nama_kegiatan,tahuntanam,intiplasma		
			 order by kode_blok,nama_kegiatan")->result_array();

		// var_dump($results);
		$this->load->helper("terbilang");
		$this->load->helper("antech_helper");
		$this->load->library('pdfgenerator');
		if ($format_laporan == 'pdf') {
			$html = get_header_pdf_report();
		} else {
			$html = get_header_report_v2();
		}


		$html = $html . '
		
	<div class="row">
  	<div class="span12">
	  <br>
	<div class="kop-print">
	  <div class="kop-nama">PT. ANNAJAH TECHNOLOGY INDONESIA</div>
	  <div class="kop-info"> Komplek Vila Sehati B12 , Tapos - Depok</div>
	  <div class="kop-info">Telp : 081387373939</div>
	</div>
	  <hr class="kop-print-hr">
  	</div>
  	</div>
  <h3 class="title">LAPORAN REKAP COST BLOK BY KEGIATAN</h3>
  <table class="no_border" style="width:30%">
			
			<tr>
					<td>ESTATE</td>
					<td>:</td>
					<td>' .  $retrieveEstate['nama'] . '</td>
			</tr>
			
			<tr>	
					<td>PERIODE</td>
					<td>:</td>
					<td>' . $tgl_mulai . ' s/d ' . $tgl_akhir . '</td>
			</tr>
			
	</table> <br><br>';

		$html = $html . ' <table   border="1" width="100%" style="border-collapse: collapse;">
			<thead>
				<tr>
				<th style="text-align: center;" width="4%">No.</th>			
				<th>Afdeling</th>
				<th>Blok</th>
				<th>Tahun Tanam</th>
				<th>Inti/Plasma</th>
				<th>Kegiatan</th>
				<th style="text-align: center;">Total </th>
				
				</tr>
			</thead>
			<tbody>';


		$no = 0;
		$total = 0;


		foreach ($retrieveCost as $key => $m) {
			$no++;
			$total = $total + $m['total'];
			$html = $html . ' 	<tr class=":: arc-content">
						<td style="position:relative; text-align: center;">	' . ($no) . '</td>
						<td>
						' . $m['nama_afdeling'] . ' 
						
						</td>
						<td style="text-align: center;">
						' . $m['nama_blok'] . ' 
						
						</td>
						<td style="text-align: center;">
							' . $m['tahuntanam'] . ' 
						</td>
						<td style="text-align: center;">
							' . $m['intiplasma'] . ' 
						</td>
						<td>
						' . $m['nama_kegiatan'] . ' 
						</td>
						<td style="text-align: right;">' . $this->format_number_report($m['total']) . ' 
													
						</td>';

			$html = $html . '</tr>';
		}

		$html = $html . ' 	
						<tr class=":: arc-content">
						<td style="position:relative;">
							&nbsp;

						</td>
						
						<td>
							&nbsp;
						</td>
						<td>
						&nbsp;
						</td>
						<td>
							&nbsp;
						</td>
						<td>
							&nbsp;
						</td>
						<td>
							&nbsp;
						</td>
						
						<td style="text-align: right;">
						' . $this->format_number_report($total) . ' 
						</td>
						
						
						</tr>
								</tbody>
							</table>
						';
		if ($format_laporan == 'xls') {
			$reader = new \PhpOffice\PhpSpreadsheet\Reader\Html;
			$spreadsheet = $reader->loadFromString($html);
			// $reader->setSheetIndex(1);
			//$spreadhseet = $reader->loadFromString($secondHtmlString, $spreadsheet);
			$objWriter = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
			header("Pragma: public");
			header("Expires: 0");
			header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
			header("Content-Type: application/force-download");
			header("Content-Type: application/octet-stream");
			header("Content-Type: application/download");
			header("Content-Disposition: attachment;filename=test.xlsx");
			header("Content-Transfer-Encoding: binary ");

			ob_end_clean();
			ob_start();
			$objWriter->save('php://output');
		} else if ($format_laporan == 'view') {
			echo $html;
		} else {
			$filename = 'report_' . time();
			$this->pdfgenerator->generate($html, $filename, true, 'A4', 'landscape');
		}
	}
	function laporan_panen_perbulan_post()
	{
		error_reporting(0);

		$estate_id     = $this->post('estate_id', true);
		$periode =  $this->post('periode', true);


		$date = new DateTime($periode . '-01');
		$date->modify('last day of this month');
		$last_day_this_month = $date->format('Y-m-d');
		(int)$jumhari = date('d', strtotime($last_day_this_month));
		$tgl_mulai = $periode . '-01';
		$tgl_akhir = $periode . '-' . sprintf("%02d", $jumhari);

		$retrieveEstate = $this->db->query("select * from gbm_organisasi where id=" . $estate_id . "")->row_array();

		// var_dump($results);
		$this->load->helper("terbilang");
		$this->load->helper("antech_helper");
		$this->load->library('pdfgenerator');
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
	  
  
	</style>
  ";

		// 		$html = $html . '<div class="row">
		//   <div class="span12">
		// 	  <br>
		// 	  <div class="kop-print">
		// 		  <img src=data:image/png;base64,' . base64_encode(file_get_contents(get_logo_config())) . ' alt="image" >
		// 		  <div class="kop-nama">' . get_pengaturan('nama-company', 'value') . '</div>
		// 		  <div class="kop-info">Alamat : ' . get_pengaturan('alamat', 'value') . ', Telepon :' . get_pengaturan('telp', 'value') . '</div>
		// 	  </div>
		// 	  <hr class="kop-print-hr">
		//   </div>
		//   </div>

		$html = $html . '<div class="row">
<div class="span12">
	<br>

</div>
</div>
<h2>Laporan Panen Per Bulan</h2>
<h3>estate:' . $retrieveEstate['nama'] . ' </h3>
<h4>' . $tgl_mulai . ' - ' . $tgl_akhir . ' </h4>';

		$html = $html . "

<table   border='1' width='100%' style='border-collapse: collapse;'>
<thead>
<tr>
	<td rowspan=3 >No</td>
	<td rowspan=3>Afdeling</td>
	<td rowspan=3>Blok</td>
	<td rowspan=3>Inti/Plasma</td>
	<td rowspan=3>Luas</td>
	<td colspan=" . ($jumhari * 4) . "  style='text-align: center'> " . $periode . "  </td>
	<td colspan=4 rowspan=2  style='text-align: center'>TOTAL</td>
</tr>
";

		$html = $html . "<tr>";
		for ($i = 1; $i < ($jumhari + 1); $i++) {
			$html = $html . "<td style='text-align: center' colspan=4>" . $i . "</td>";
		}

		$html = $html . "</tr> ";
		$html = $html . "<tr>";
		for ($i = 1; $i < ($jumhari + 1); $i++) {
			$html = $html . "<td style='text-align: center'>Jjg</td>";
			$html = $html . "<td style='text-align: center'>Kg</td>";
			$html = $html . "<td style='text-align: center'>Luas</td>";
			$html = $html . "<td style='text-align: center'>Hk</td>";
		}
		$html = $html . "<td style='text-align: center'>Jjg</td>";
		$html = $html . "<td style='text-align: center'>Kg</td>";
		$html = $html . "<td style='text-align: center'>Luas</td>";
		$html = $html . "<td style='text-align: center'>Hk</td>";
		$html = $html . "</tr> </thead>";
		$nourut = 0;
		$grandtotal = 0;
		$totalPerHari = array();
		$totalPerHari = [];
		// retrive data rayon  
		$qry = "SELECT DISTINCT blok_id,kode_blok,nama_blok,nama_afdeling,intiplasma,luasareaproduktif FROM est_bkm_panen_vw WHERE id_estate=" . $estate_id . " and tanggal>='" . $tgl_mulai . "' 
		and tanggal<='" . $tgl_akhir . "' ";
		$retrieveBlok = $this->db->query($qry)->result_array();

		for ($i = 1; $i < ($jumhari + 1); $i++) {
			$totalPerHari[] = 0;
		}

		foreach ($retrieveBlok as $key => $d) {
			$html = $html . "<tr>";
			$totalkg = 0;
			$nourut = $nourut + 1;
			$html = $html . "<td style='text-align: center'>" . $nourut . "</td>";
			$html = $html . "<td style='text-align: center'>" . $d['nama_afdeling'] . "</td>";
			$html = $html . "<td style='text-align: center'>" . $d['nama_blok'] . "</td>";
			$html = $html . "<td style='text-align: center'>" . $d['intiplasma'] . "</td>";
			$html = $html . "<td style='text-align: center'>" . $d['luasareaproduktif'] . "</td>";
			$total_hasil_kerja_jjg = 0;
			$total_hasil_kerja_kg = 0;
			$total_hasil_kerja_luas = 0;
			$total_jumlah_hk = 0;
			for ($i = 1; $i < ($jumhari + 1); $i++) {
				$tgl = $periode  . '-' . sprintf("%02d", $i);
				$retrievePanen = $this->db->query("SELECT SUM(hasil_kerja_jjg)as hasil_kerja_jjg,
				SUM(hasil_kerja_kg)as hasil_kerja_kg,SUM(hasil_kerja_luas)as hasil_kerja_luas,
				SUM(jumlah_hk)as jumlah_hk
				 FROM est_bkm_panen_vw WHERE (blok_id =" . $d['blok_id'] . ")
				and id_estate=" . $estate_id . " and tanggal='" . $tgl . "'")->row_array();
				$hasil_kerja_jjg = $retrievePanen['hasil_kerja_jjg'] ? $retrievePanen['hasil_kerja_jjg'] : 0;
				$hasil_kerja_kg = $retrievePanen['hasil_kerja_kg'] ? $retrievePanen['hasil_kerja_kg'] : 0;
				$hasil_kerja_luas = $retrievePanen['hasil_kerja_luas'] ? $retrievePanen['hasil_kerja_luas'] : 0;
				$jumlah_hk = $retrievePanen['jumlah_hk'] ? $retrievePanen['jumlah_hk'] : 0;
				$index = "idx" . $i;
				// $jum=0;
				if (array_key_exists($i, $totalPerHari)) {
					$totalPerHari[$i - 1] = $totalPerHari[$i - 1] + $hasil_kerja_jjg;
				} else {
					$totalPerHari[] = $hasil_kerja_jjg;
				}

				$total_hasil_kerja_jjg = $total_hasil_kerja_jjg + $hasil_kerja_jjg;
				$total_hasil_kerja_kg = $total_hasil_kerja_kg + $hasil_kerja_kg;
				$total_hasil_kerja_luas = $total_hasil_kerja_luas + $hasil_kerja_luas;
				$total_jumlah_hk = $total_jumlah_hk + $jumlah_hk;
				$grandtotal = $grandtotal + $hasil_kerja_jjg;
				$html = $html . "<td style='text-align: center'>" . $hasil_kerja_jjg . " </td>";
				$html = $html . "<td style='text-align: center'>" . $hasil_kerja_kg . " </td>";
				$html = $html . "<td style='text-align: center'>" . $hasil_kerja_luas . " </td>";
				$html = $html . "<td style='text-align: center'>" . $jumlah_hk . " </td>";
			}
			$html = $html . "<td style='text-align: center'>" . $total_hasil_kerja_jjg . " </td>";
			$html = $html . "<td style='text-align: center'>" . $total_hasil_kerja_kg . " </td>";
			$html = $html . "<td style='text-align: center'>" . $total_hasil_kerja_luas . " </td>";
			$html = $html . "<td style='text-align: center'>" . $total_jumlah_hk . " </td>";
			$html = $html . "</tr>";
		}



		// // retrive data SUpplier  
		// $qry = "SELECT DISTINCT supplier_id,nama_supplier FROM pks_timbangan_terima_tbs_vw WHERE (supplier_id IS NOT NULL AND supplier_id <>0)
		// and estate_id=" . $estate_id . " and tanggal>='" . $tgl_mulai . "' 
		// and tanggal<='" . $tgl_akhir . "'  " . $where;
		// $retrieveSupplier = $this->db->query($qry)->result_array();

		// foreach ($retrieveSupplier as $key => $s) {
		// 	$html = $html . "<tr>";
		// 	$totalkg = 0;
		// 	$nourut = $nourut + 1;
		// 	$html = $html . "<td style='text-align: center'>" . $nourut . "</td>";
		// 	$html = $html . "<td style='text-align: center'>" . $s['nama_supplier'] . "</td>";
		// 	for ($i = 1; $i < ($jumhari + 1); $i++) {

		// 		$tgl = $periode  . '-' . sprintf("%02d", $i);
		// 		$retrieveKgSupp = $this->db->query("SELECT SUM(berat_bersih)as beratkg FROM pks_timbangan_terima_tbs_vw WHERE (supplier_id =" . $s['supplier_id'] . ")
		// 		and estate_id=" . $estate_id . " and tanggal='" . $tgl . "'")->row_array();
		// 		$beratkg = $retrieveKgSupp['beratkg'] ? $retrieveKgSupp['beratkg'] : 0;
		// 		$totalPerHari[$i - 1] = ($totalPerHari[$i - 1] ? $totalPerHari[$i - 1] : 0) + $beratkg;
		// 		$totalkg = $totalkg + $beratkg;
		// 		$grandtotal = $grandtotal + $beratkg;
		// 		$html = $html . "<td style='text-align: center'>" . $this->format_number_report($beratkg) . " </td>";
		// 	}
		// 	$html = $html . "<td style='text-align: center'>" . $this->format_number_report($totalkg) . " </td>";
		// 	$html = $html . "</tr>";
		// }


		// $html = $html . "<tr>";
		// $html = $html . "<td style='text-align: center'> </td>";
		// $html = $html . "<td style='text-align: center'></td>";
		// for ($i = 1; $i < ($jumhari + 1); $i++) {

		// 	$html = $html . "<td style='text-align: center'>" . $this->format_number_report($totalPerHari[$i - 1]) . " </td>";
		// }
		// $html = $html . "<td style='text-align: right'>" . $this->format_number_report($grandtotal) . " </td>";
		// $html = $html . "</tr>";
		$html = $html . "</table>";



		// $filename = 'report_' . time();
		// $this->pdfgenerator->generate($html, $filename, true, 'A4', 'landscape');
		echo $html;
	}

	function laporan_cost_traksi_detail_post()
	{

		error_reporting(0);
		$traksi_id     = $this->post('traksi_id', true);
		$tgl_mulai =  $this->post('tgl_mulai', true);
		$tgl_akhir = $this->post('tgl_akhir', true);
		$kendaraan_id = $this->post('kendaraan_id', true);
		$format_laporan     = $this->post('format_laporan', true);

		$retrieveTraksi = $this->db->query("select * from gbm_organisasi where id=" . $traksi_id . "")->row_array();
		$retrieveKendaraan = $this->db->query("select * from trk_kendaraan where id=" . $kendaraan_id . "")->row_array();

		$retrieveCost = $this->db->query("SELECT tanggal,ket,no_jurnal,no_ref,kode_kendaraan,nama_kendaraan,nama_traksi,
				(debet-kredit)AS nilai_biaya
				FROM trk_cost_kendaraan_vw
					where
			 kendaraan_mesin_id='" . $kendaraan_id . "' and tanggal>='" . $tgl_mulai . "' and tanggal<='" . $tgl_akhir . "'
			 order by tanggal,no_ref")->result_array();

		// var_dump($results);
		$this->load->helper("terbilang");
		$this->load->helper("antech_helper");
		$this->load->library('pdfgenerator');
		if ($format_laporan == 'pdf') {
			$html = get_header_pdf_report();
		} else {
			$html = get_header_report_v2();
		}


		$html = $html . '
<div class="row">
  <div class="span12">
	  <br>
	<div class="kop-print">
    <div class="kop-nama">PT. ANNAJAH TECHNOLOGY INDONESIA</div>
    <div class="kop-info"> Komplek Vila Sehati B12 , Tapos - Depok</div>
    <div class="kop-info">Telp : 081387373939</div>
  	</div>
	  <hr class="kop-print-hr">
  </div>
</div>
		<h3 class="title">Laporan Rincian Biaya Kendaraan/AB/Mesin</h3>
  <table class="no_border" style="width:30%">
			
			<tr>
					<td>Traksi</td>
					<td>:</td>
					<td>' .  $retrieveTraksi['nama'] . '</td>
			</tr>
			<tr>	
					<td>Periode</td>
					<td>:</td>
					<td>' . tgl_indo($tgl_mulai) . ' s/d ' . tgl_indo($tgl_akhir) . '</td>
			</tr>
			<tr>	
					<td>Kendaraan/AB/Mesin</td>
					<td>:</td>
					<td>' . $retrieveKendaraan['nama'] . ' - ' . $retrieveKendaraan['kode'] . '</td>
			</tr>

			
	</table>
	<br>';

		$html = $html . ' <table  >
			<thead>
				<tr>
				<th width="4%">No.</th>			
				<th>Tanggal</th>
				<th>Keterangan</th>
				<th>No Jurnal</th>
				<th>No Ref</th>
				<th style="text-align: center;">Nilai Biaya (Rp) </th>
	
				</tr>
			</thead>
			<tbody>';


		$no = 0;
		$bahan = 0;
		$lainnya = 0;
		$upah = 0;
		$total = 0;


		foreach ($retrieveCost as $key => $m) {
			$no++;
			// $upah = $upah + $m['upah'];
			// $bahan = $bahan + $m['bahan'];
			// $lainnya = $lainnya + $m['lainnya'];
			$total = $total + $m['nilai_biaya'];
			$html = $html . ' 	<tr class=":: arc-content">
						<td style="position:relative; text-align: center;">	' . ($no) . '</td>
						<td>' . $m['tanggal'] . ' </td>
						<td>' . $m['ket'] . ' </td>
						<td>' . $m['no_jurnal'] . ' </td>
						<td>' . $m['no_ref'] . ' </td>
						<td style="text-align: right;">' . $this->format_number_report($m['nilai_biaya']) . ' 
													
						</td>';

			$html = $html . '</tr>';
		}

		$html = $html . ' 	
						<tr class=":: arc-content">
						<td style="position:relative;">
							&nbsp;

						</td>
						<td>
							&nbsp;
						</td>
					
						<td style="text-align: right;">
						
						</td>
						<td style="text-align: right;">
						
						</td>
						<td style="text-align: right;">
						
						</td>
						<td style="text-align: right;">
						' . $this->format_number_report($total) . ' 
						</td>
						
						
						</tr>
								</tbody>
							</table>
						';
		if ($format_laporan == 'xls') {
			$reader = new \PhpOffice\PhpSpreadsheet\Reader\Html;
			$spreadsheet = $reader->loadFromString($html);
			// $reader->setSheetIndex(1);
			//$spreadhseet = $reader->loadFromString($secondHtmlString, $spreadsheet);
			$objWriter = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
			header("Pragma: public");
			header("Expires: 0");
			header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
			header("Content-Type: application/force-download");
			header("Content-Type: application/octet-stream");
			header("Content-Type: application/download");
			header("Content-Disposition: attachment;filename=test.xlsx");
			header("Content-Transfer-Encoding: binary ");

			ob_end_clean();
			ob_start();
			$objWriter->save('php://output');
		} else if ($format_laporan == 'view') {
			echo $html;
		} else {
			$filename = 'report_' . time();
			$this->pdfgenerator->generate($html, $filename, true, 'A4', 'landscape');
		}
	}
	function laporan_cost_traksi_rekap_v1_post()
	{

		error_reporting(0);
		$traksi_id     = $this->post('traksi_id', true);
		$tgl_mulai =  $this->post('tgl_mulai', true);
		$tgl_akhir = $this->post('tgl_akhir', true);
		// $blok_id = $this->post('blok_id', true);
		$format_laporan     = $this->post('format_laporan', true);


		$retrieveTraksi = $this->db->query("select * from gbm_organisasi where id=" . $traksi_id . "")->row_array();
		//$retrieveBlok = $this->db->query("select * from gbm_organisasi where id=" . $blok_id . "")->row_array();

		$retrieveCost = $this->db->query("SELECT kendaraan_mesin_id,kode_kendaraan,nama_kendaraan,nama_traksi,
		SUM(debet-kredit)AS total,
		sum(CASE WHEN jenis = 'UPAH' THEN (debet-kredit) else 0 end) as upah,
			sum(CASE WHEN jenis = 'BAHAN' THEN (debet-kredit) else 0 end) as bahan,
				sum(CASE WHEN jenis = '' THEN (debet-kredit) else 0 end) as lainnya
		FROM trk_cost_kendaraan_vw 
			where traksi_id='" . $traksi_id . "' 
			and tanggal>='" . $tgl_mulai . "' and tanggal<='" . $tgl_akhir . "'
			GROUP BY  kendaraan_mesin_id,kode_kendaraan,nama_kendaraan,nama_traksi		
			 order by kode_kendaraan")->result_array();

		// var_dump($results);
		$this->load->helper("terbilang");
		$this->load->helper("antech_helper");
		$this->load->library('pdfgenerator');
		if ($format_laporan == 'pdf') {
			$html = get_header_pdf_report();
		} else {
			$html = get_header_report();
		}


		$html = $html . '
<h2>Laporan Rekap Cost Kendaraan/Alat Berat/Mesin</h2>
<h3>Estate:' . $retrieveTraksi['nama'] . ' </h3>
<h4>' . $tgl_mulai . ' - ' . $tgl_akhir . ' </h4>';

		$html = $html . ' <table  >
			<thead>
				<tr>
				<th width="4%">No.</th>			
				<th>Traksi</th>
				<th>Kendaraan/AB/Mesin</th>
				<th style="text-align: right;">Upah </th>
				<th style="text-align: right;">Bahan Bakar/Spare Part</th>
				<th style="text-align: right;">Lainnya </th>
				<th style="text-align: right;">Total </th>
				<th style="text-align: right;">Jumlah Km/Hm </th>
				<th style="text-align: right;">Biaya per Km/Hm </th>
				
				</tr>
			</thead>
			<tbody>';


		$no = 0;
		$bahan = 0;
		$lainnya = 0;
		$upah = 0;
		$total = 0;


		foreach ($retrieveCost as $key => $m) {
			$q = "	SELECT sum(b.km_hm_jumlah)AS jum_km,a.kendaraan_id 
			FROM trk_kegiatan_kendaraan_ht a 
			inner join trk_kegiatan_kendaraan_log b on a.id=b.trk_kegiatan_kendaraan_id 
			inner join trk_kendaraan c on a.kendaraan_id =c.id 
			inner join acc_kegiatan e on b.acc_kegiatan_id=e.id 
			inner join acc_akun f on e.acc_akun_id=f.id 
			inner join gbm_organisasi g on b.blok_id=g.id
			inner join gbm_organisasi h on g.parent_id=h.id
			inner join gbm_organisasi i on h.parent_id=i.id
			where 1=1 
			and a.kendaraan_id=" . $m['kendaraan_mesin_id'] . "
			and a.tanggal >='" . $tgl_mulai . "' and a.tanggal <='" . $tgl_akhir . "'";
			$res_trk = $this->db->query($q)->row_array();
			$jum_km = 0;
			if ($res_trk) {
				$jum_km = $res_trk['jum_km'];
			}

			$no++;
			$upah = $upah + $m['upah'];
			$bahan = $bahan + $m['bahan'];
			$lainnya = $lainnya + $m['lainnya'];
			$total = $total + $m['total'];
			$biaya_per_km = 0;
			if ($jum_km == 0 || $m['total'] == 0) {
				$biaya_per_km = 0;
			} else {
				$biaya_per_km = $m['total'] / $jum_km;
			}
			$html = $html . ' 	<tr class=":: arc-content">
						<td style="position:relative;">	' . ($no) . '</td>
						<td>
						' . $m['nama_traksi'] . ' 
						
						</td>
						<td>
						' . $m['nama_kendaraan'] . ' - ' . $m['kode_kendaraan'] . '
						
						</td>
											
						<td style="text-align: right;">' . $this->format_number_report($m['upah']) . ' 
						<td style="text-align: right;">' . $this->format_number_report($m['bahan']) . ' 
						<td style="text-align: right;">' . $this->format_number_report($m['lainnya']) . ' 
						<td style="text-align: right;">' . $this->format_number_report($m['total']) . ' 
						<td style="text-align: right;">' . $this->format_number_report($jum_km) . ' 	
						<td style="text-align: right;">' . $this->format_number_report($biaya_per_km) . ' 													
						</td>';

			$html = $html . '</tr>';
		}

		$html = $html . ' 	
						<tr class=":: arc-content">
						<td style="position:relative;">
							&nbsp;

						</td>
						
						<td>
							&nbsp;
						</td>
						<td>
							&nbsp;
						</td>
						
					
						<td style="text-align: right;">
						' . $this->format_number_report($upah) . ' 
						</td>
						<td style="text-align: right;">
						' . $this->format_number_report($bahan) . ' 
						</td>
						<td style="text-align: right;">
						' . $this->format_number_report($lainnya) . ' 
						</td>
						<td style="text-align: right;">
						' . $this->format_number_report($total) . ' 
						</td>
						<td>
							&nbsp;
						</td>
						<td>
							&nbsp;
						</td>
						
						</tr>
								</tbody>
							</table>
						';
		if ($format_laporan == 'xls') {
			$reader = new \PhpOffice\PhpSpreadsheet\Reader\Html;
			$spreadsheet = $reader->loadFromString($html);
			// $reader->setSheetIndex(1);
			//$spreadhseet = $reader->loadFromString($secondHtmlString, $spreadsheet);
			$objWriter = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
			header("Pragma: public");
			header("Expires: 0");
			header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
			header("Content-Type: application/force-download");
			header("Content-Type: application/octet-stream");
			header("Content-Type: application/download");
			header("Content-Disposition: attachment;filename=test.xlsx");
			header("Content-Transfer-Encoding: binary ");

			ob_end_clean();
			ob_start();
			$objWriter->save('php://output');
		} else if ($format_laporan == 'view') {
			echo $html;
		} else {
			$filename = 'report_' . time();
			$this->pdfgenerator->generate($html, $filename, true, 'A4', 'landscape');
		}
	}
	function laporan_cost_traksi_rekap_post()
	{

		error_reporting(0);
		$traksi_id     = $this->post('traksi_id', true);
		$tgl_mulai =  $this->post('tgl_mulai', true);
		$tgl_akhir = $this->post('tgl_akhir', true);
		// $blok_id = $this->post('blok_id', true);
		$format_laporan     = $this->post('format_laporan', true);


		$retrieveTraksi = $this->db->query("select * from gbm_organisasi where id=" . $traksi_id . "")->row_array();
		$q = "select d.kode as kode_unit,d.nama as nama_unit, b.kendaraan_mesin_id,sum(CASE WHEN c.kode = '4110201' THEN (debet-kredit) else 0 end) as gaji,
		sum(CASE WHEN c.kode = '4110202' THEN (debet-kredit) else 0 end) as premi,
		sum(CASE WHEN c.kode = '4110203' THEN (debet-kredit) else 0 end) as bbm_pelumas,
		sum(CASE WHEN c.kode = '4110204' THEN (debet-kredit) else 0 end) as sparepart,
		sum(CASE WHEN c.kode = '4110205' THEN (debet-kredit) else 0 end) as reparasi,
		sum(CASE WHEN c.kode = '4110206' THEN (debet-kredit) else 0 end) as asuransi,
		sum(CASE WHEN c.kode = '4110207' THEN (debet-kredit) else 0 end) as lain_lain
		from acc_jurnal_ht a 
		INNER JOIN acc_jurnal_dt b on`a`.`id` = `b`.`jurnal_id`
		INNER JOIN acc_akun c on`b`.`acc_akun_id` = `c`.id
		INNER JOIN trk_kendaraan d ON b.kendaraan_mesin_id=d.id
		INNER JOIN gbm_organisasi e ON d.traksi_id=e.id
		where d.traksi_id='" . $traksi_id . "' 
		and a.tanggal>='" . $tgl_mulai . "' and a.tanggal<='" . $tgl_akhir . "'
		GROUP BY d.kode,d.nama,b.kendaraan_mesin_id
		order by d.nama";
		// var_dump($q);exit();

		$retrieveCost = $this->db->query($q)->result_array();

		// var_dump($results);
		$this->load->helper("terbilang");
		$this->load->helper("antech_helper");
		$this->load->library('pdfgenerator');
		if ($format_laporan == 'pdf') {
			$html = get_header_pdf_report();
		} else {
			$html = get_header_report_v2();
		}


		$html = $html . '
<div class="row">
  <div class="span12">
	  <br>
	<div class="kop-print">
    <div class="kop-nama">PT. ANNAJAH TECHNOLOGY INDONESIA</div>
    <div class="kop-info"> Komplek Vila Sehati B12 , Tapos - Depok</div>
    <div class="kop-info">Telp : 081387373939</div>
  	</div>
	  <hr class="kop-print-hr">
  </div>
</div>
		<h3 class="title">Laporan Rekap Biaya Kendaraan/Alat Berat/Mesin</h3>
  <table class="no_border" style="width:30%">
			
			<tr>
					<td>Traksi</td>
					<td>:</td>
					<td>' .  $retrieveTraksi['nama'] . '</td>
			</tr>
			<tr>	
					<td>Periode</td>
					<td>:</td>
					<td>' . tgl_indo($tgl_mulai) . ' s/d ' . tgl_indo($tgl_akhir) . '</td>
			</tr>

			
	</table>
	<br>';

		$html = $html . ' <table  >
			<thead>
				<tr>
				<th width="4%">No.</th>			
				<th>Nama Unit</th>
				<th>Kode Unit</th>
				<th style="text-align: center;">Gaji (Rp)</th>
				<th style="text-align: center;">Premi (Rp)</th>
				<th style="text-align: center;">BBM & Pelumas (Rp)</th>
				<th style="text-align: center;">Suku Cadang (Rp)</th>
				<th style="text-align: center;">Reparasi (Rp)</th>
				<th style="text-align: center;">Asuransi (Rp)</th>
				<th style="text-align: center;">Lain Lain (Rp)</th>
				<th style="text-align: center;">Jumlah (Rp)</th>
				<th colspan=3 style="text-center: right;">Rincian (Rp)</th>
				</tr>
			</thead>
			<tbody>';


		$no = 0;
		$total_gaji = 0;
		$total_premi = 0;
		$total_bbm = 0;
		$total_reparasi = 0;
		$total_asuransi = 0;
		$total_lainnya = 0;
		$total_sparepart = 0;
		$total_biaya = 0;
		foreach ($retrieveCost as $key => $m) {
			$no++;
			$total_gaji = $total_gaji + $m['gaji'];
			$total_premi = $total_premi + $m['premi'];
			$total_bbm = $total_bbm + $m['bbm_pelumas'];
			$total_sparepart = $total_sparepart + $m['sparepart'];
			$total_reparasi = $total_reparasi + $m['reparasi'];
			$total_asuransi = $total_asuransi + $m['asuransi'];
			$total_lainnya = $total_lainnya + $m['lain_lain'];
			$jum_biaya = $m['gaji'] + $m['premi'] + $m['bbm_pelumas'] + $m['sparepart'] + $m['reparasi'] + $m['asuransi'] + $m['lain_lain'];
			$total_biaya = $total_biaya + $jum_biaya;
			$actual_link_detail_cost = "http://$_SERVER[HTTP_HOST]" . "/klinik_api/api/GlobalReport/trk_cost_detail/" . $m['kendaraan_mesin_id'] . "/" . $tgl_mulai . "/" . $tgl_akhir .   "";
			$actual_link_detail_pemakaian_inv = "http://$_SERVER[HTTP_HOST]" . "/klinik_api/api/GlobalReport/trk_pemakaian_inventory/" . $m['kendaraan_mesin_id'] . "/" . $tgl_mulai . "/" . $tgl_akhir .   "";
			$actual_link_detail_kegiatan = "http://$_SERVER[HTTP_HOST]" . "/klinik_api/api/GlobalReport/trk_kegiatan_detail/" . $m['kendaraan_mesin_id'] . "/" . $tgl_mulai . "/" . $tgl_akhir .   "";
			$html = $html . ' 	<tr class=":: arc-content">
						<td style="position:relative; text-align: center;">	' . ($no) . '</td>
						<td>
						' . $m['nama_unit'] . ' 
						
						</td>
						<td>
						' .  $m['kode_unit'] . '
						
						</td>
											
						<td style="text-align: right;">' . $this->format_number_report($m['gaji']) . ' </td>
						<td style="text-align: right;">' . $this->format_number_report($m['premi']) . ' </td>
						<td style="text-align: right;">' . $this->format_number_report($m['bbm_pelumas']) . ' </td>
						<td style="text-align: right;">' . $this->format_number_report($m['sparepart']) . ' </td>
						<td style="text-align: right;">' . $this->format_number_report($m['reparasi']) . ' 	</td>
						<td style="text-align: right;">' . $this->format_number_report($m['asuransi']) . ' </td>	
						<td style="text-align: right;">' . $this->format_number_report($m['lain_lain']) . ' 	</td>		
						<td style="text-align: right;">' . $this->format_number_report($jum_biaya) . ' 	</td>	
						<td><a href="' . $actual_link_detail_cost  . '" target="_blank"> Detail Biaya </a>		</td>	
						<td><a href="' . $actual_link_detail_pemakaian_inv  . '" target="_blank"> Detail BBM, Sparepart </a>		</td>			
						<td><a href="' . $actual_link_detail_kegiatan  . '" target="_blank"> Detail Kegiatan </a>		</td>																																					
						';

			$html = $html . '</tr>';
		}

		$html = $html . ' 	
						<tr class=":: arc-content">
						<td style="position:relative;">
							&nbsp;

						</td>
						
						<td>
							&nbsp;
						</td>
						<td>
							&nbsp;
						</td>
						<td style="text-align: right;">
						' . $this->format_number_report($total_gaji) . ' 
						</td>
						<td style="text-align: right;">
						' . $this->format_number_report($total_premi) . ' 
						</td>
						<td style="text-align: right;">
						' . $this->format_number_report($total_bbm) . ' 
						</td>
					
						<td style="text-align: right;">
						' . $this->format_number_report($total_sparepart) . ' 
						</td>
						<td style="text-align: right;">
						' . $this->format_number_report($total_reparasi) . ' 
						</td>
						<td style="text-align: right;">
						' . $this->format_number_report($total_asuransi) . ' 
						</td>
						<td style="text-align: right;">
						' . $this->format_number_report($total_lainnya) . ' 
						</td>
						<td style="text-align: right;">
						' . $this->format_number_report($total_biaya) . ' 
						</td>
												
						</tr>
						</tbody>
						</table>
						';
		if ($format_laporan == 'xls') {
			$reader = new \PhpOffice\PhpSpreadsheet\Reader\Html;
			$spreadsheet = $reader->loadFromString($html);
			// $reader->setSheetIndex(1);
			//$spreadhseet = $reader->loadFromString($secondHtmlString, $spreadsheet);
			$objWriter = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
			header("Pragma: public");
			header("Expires: 0");
			header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
			header("Content-Type: application/force-download");
			header("Content-Type: application/octet-stream");
			header("Content-Type: application/download");
			header("Content-Disposition: attachment;filename=test.xlsx");
			header("Content-Transfer-Encoding: binary ");

			ob_end_clean();
			ob_start();
			$objWriter->save('php://output');
		} else if ($format_laporan == 'view') {
			echo $html;
		} else {
			$filename = 'report_' . time();
			$this->pdfgenerator->generate($html, $filename, true, 'A4', 'landscape');
		}
	}
	function laporan_traksi_rasio_pemakaian_bbm_post()
	{

		error_reporting(0);
		$traksi_id     = $this->post('traksi_id', true);
		$tgl_mulai =  $this->post('tgl_mulai', true);
		$tgl_akhir = $this->post('tgl_akhir', true);
		// $blok_id = $this->post('blok_id', true);
		$format_laporan     = $this->post('format_laporan', true);


		$retrieveTraksi = $this->db->query("select * from gbm_organisasi where id=" . $traksi_id . "")->row_array();
		//$retrieveBlok = $this->db->query("select * from gbm_organisasi where id=" . $blok_id . "")->row_array();

		$retrieveCost = $this->db->query("SELECT kendaraan_mesin_id,kode_kendaraan,nama_kendaraan,nama_traksi,
		SUM(debet-kredit)AS total,
		sum(CASE WHEN jenis = 'UPAH' THEN (debet-kredit) else 0 end) as upah,
			sum(CASE WHEN jenis = 'BAHAN' THEN (debet-kredit) else 0 end) as bahan,
				sum(CASE WHEN jenis = '' THEN (debet-kredit) else 0 end) as lainnya
		FROM trk_cost_kendaraan_vw 
			where traksi_id='" . $traksi_id . "' 
			and tanggal>='" . $tgl_mulai . "' and tanggal<='" . $tgl_akhir . "'
			and acc_akun_id in (select id from acc_akun where kode in('4110201', '4110202','4110203','4110204','4110205','4110206','4110207'))
			GROUP BY  kendaraan_mesin_id,kode_kendaraan,nama_kendaraan,nama_traksi		
			 order by kode_kendaraan")->result_array();

		// var_dump($results);
		$this->load->helper("terbilang");
		$this->load->helper("antech_helper");
		$this->load->library('pdfgenerator');
		if ($format_laporan == 'pdf') {
			$html = get_header_pdf_report();
		} else {
			$html = get_header_report_v2();
		}


		$html = $html . '
		<div class="row">
		<div class="span12">
			<br>
		  <div class="kop-print">
		  <div class="kop-nama">PT. ANNAJAH TECHNOLOGY INDONESIA</div>
		  <div class="kop-info"> Komplek Vila Sehati B12 , Tapos - Depok</div>
		  <div class="kop-info">Telp : 081387373939</div>
			</div>
			<hr class="kop-print-hr">
		</div>
	  </div>
			  <h3 class="title">Laporan Rasio Pemakain BBM dan Biaya Kendaraan/Alat Berat/Mesin</h3>
		<table class="no_border" style="width:30%">
				  
				  <tr>
						  <td>Traksi</td>
						  <td>:</td>
						  <td>' .  $retrieveTraksi['nama'] . '</td>
				  </tr>
				  <tr>	
						  <td>Periode</td>
						  <td>:</td>
						  <td>' . tgl_indo($tgl_mulai) . ' s/d ' . tgl_indo($tgl_akhir) . '</td>
				  </tr>
	  
				  
		  </table>
		  <br>';

		$html = $html . ' <table  >
			<thead>
				<tr>
				<th width="4%">No.</th>			
				<th>Nama Unit</th>
				<th>Kode Unit</th>
				<th style="text-align: center;">Km/Hm/jam </th>
				<th style="text-align: center;">BBM/Liter </th>
				<th style="text-align: center;">Liter/Km,Hm,Jam</th>
				<th style="text-align: center;">Biaya (Rp)</th>
				<th style="text-align: center;">Rp/Km,Hm,Jam</th>
				<th colspan=3 style="text-center: right;">Rincian </th>				
				</tr>
			</thead>
			<tbody>';


		$no = 0;
		$bahan = 0;
		$lainnya = 0;
		$upah = 0;
		$total = 0;
		$total_bbm = 0;

		foreach ($retrieveCost as $key => $m) {
			$q1 = "	SELECT sum(b.km_hm_jumlah)AS jum_km,a.kendaraan_id 
			FROM trk_kegiatan_kendaraan_ht a 
			inner join trk_kegiatan_kendaraan_log b on a.id=b.trk_kegiatan_kendaraan_id 
			inner join trk_kendaraan c on a.kendaraan_id =c.id 
			left join acc_kegiatan e on b.acc_kegiatan_id=e.id 
			left join acc_akun f on e.acc_akun_id=f.id 
			left join gbm_organisasi g on b.blok_id=g.id
			left join gbm_organisasi h on g.parent_id=h.id
			left join gbm_organisasi i on h.parent_id=i.id
			where 1=1 
			and a.kendaraan_id=" . $m['kendaraan_mesin_id'] . "
			and a.tanggal >='" . $tgl_mulai . "' and a.tanggal <='" . $tgl_akhir . "'
			group by a.kendaraan_id ";
			$res_trk = $this->db->query($q1)->row_array();
			$jum_km = 0;
			if ($res_trk) {
				$jum_km = $res_trk['jum_km'];
			}

			$no++;
			$upah = $upah + $m['upah'];
			$bahan = $bahan + $m['bahan'];
			$lainnya = $lainnya + $m['lainnya'];
			$total = $total + $m['total'];
			$biaya_per_km = 0;
			if ($jum_km == 0 || $m['total'] == 0) {
				$biaya_per_km = 0;
			} else {
				$biaya_per_km = $m['total'] / $jum_km;
			}

			// Pemakaian Solar item_id=9///
			$q2 = "	SELECT SUM(qty_keluar)AS qty ,SUM(nilai_keluar)AS nilai ,kendaraan_id 
			FROM inv_transaksi_harian 
			WHERE item_id=9 AND tipe='PEMAKAIAN'
			and kendaraan_id=" . $m['kendaraan_mesin_id'] . "
			and tanggal >='" . $tgl_mulai . "' and tanggal <='" . $tgl_akhir . "'
			GROUP BY kendaraan_id";
			$res_bbm = $this->db->query($q2)->row_array();
			$jum_bbm = 0;
			if ($res_bbm) {
				$jum_bbm = $res_bbm['qty'];
			}
			$total_bbm = $total_bbm + $jum_bbm;
			if ($jum_bbm == 0 || $jum_km == 0) {
				$liter_per_km = 0;
			} else {
				$liter_per_km = $jum_bbm / $jum_km;
			}
			$actual_link_detail_cost = "http://$_SERVER[HTTP_HOST]" . "/klinik_api/api/GlobalReport/trk_cost_detail/" . $m['kendaraan_mesin_id'] . "/" . $tgl_mulai . "/" . $tgl_akhir .   "";
			$actual_link_detail_pemakaian_inv = "http://$_SERVER[HTTP_HOST]" . "/klinik_api/api/GlobalReport/trk_pemakaian_inventory/" . $m['kendaraan_mesin_id'] . "/" . $tgl_mulai . "/" . $tgl_akhir .   "";
			$actual_link_detail_kegiatan = "http://$_SERVER[HTTP_HOST]" . "/klinik_api/api/GlobalReport/trk_kegiatan_detail/" . $m['kendaraan_mesin_id'] . "/" . $tgl_mulai . "/" . $tgl_akhir .   "";

			$html = $html . ' 	<tr class=":: arc-content">
						<td style="position:relative; text-align: center;">	' . ($no) . '</td>
						<td>
						' . $m['nama_kendaraan'] . ' 
						
						</td>
						<td>
						' . $m['kode_kendaraan'] . '
						
						</td>
						<td style="text-align: right;">' . $this->format_number_report($jum_km) . ' 				
						<td style="text-align: right;">' . $this->format_number_report($jum_bbm) . ' 
						<td style="text-align: right;">' . $this->format_number_report($liter_per_km) . ' 
						<td style="text-align: right;">' . $this->format_number_report($m['total']) . ' 
							
						<td style="text-align: right;">' . $this->format_number_report($biaya_per_km) . ' 													
						<td><a href="' . $actual_link_detail_cost  . '" target="_blank"> Detail Biaya </a>		</td>	
						<td><a href="' . $actual_link_detail_pemakaian_inv  . '" target="_blank"> Detail BBM, Sparepart </a>		</td>			
						<td><a href="' . $actual_link_detail_kegiatan  . '" target="_blank"> Detail Kegiatan </a>		</td>																																					
		
						</td>';

			$html = $html . '</tr>';
		}

		$html = $html . ' 	
						<tr class=":: arc-content">
						<td style="position:relative;">
							&nbsp;

						</td>
						
						<td>
							&nbsp;
						</td>
						<td>
							&nbsp;
						</td>
						<td style="text-align: right;">
						
						</td>
					
						<td style="text-align: right;"><b>
						' . $this->format_number_report($total_bbm) . ' </b>
						</td>
						
						<td style="text-align: right;">
						
						</td>
						
						<td style="text-align: right;"><b>
						' . $this->format_number_report($total) . ' </b>
						</td>
						<td style="text-align: right;">
						
						</td>
						</tr>
								</tbody>
							</table>
						';
		if ($format_laporan == 'xls') {
			$reader = new \PhpOffice\PhpSpreadsheet\Reader\Html;
			$spreadsheet = $reader->loadFromString($html);
			// $reader->setSheetIndex(1);
			//$spreadhseet = $reader->loadFromString($secondHtmlString, $spreadsheet);
			$objWriter = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
			header("Pragma: public");
			header("Expires: 0");
			header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
			header("Content-Type: application/force-download");
			header("Content-Type: application/octet-stream");
			header("Content-Type: application/download");
			header("Content-Disposition: attachment;filename=test.xlsx");
			header("Content-Transfer-Encoding: binary ");

			ob_end_clean();
			ob_start();
			$objWriter->save('php://output');
		} else if ($format_laporan == 'view') {
			echo $html;
		} else {
			$filename = 'report_' . time();
			$this->pdfgenerator->generate($html, $filename, true, 'A4', 'landscape');
		}
	}
	public function laporan_traksi_rekap_pemakaian_bbm_post()
	{
		error_reporting(0);
		$traksi_id     = $this->post('traksi_id', true);
		$tgl_mulai =  $this->post('tgl_mulai', true);
		$tgl_akhir = $this->post('tgl_akhir', true);
		// $blok_id = $this->post('blok_id', true);
		$format_laporan     = $this->post('format_laporan', true);


		$retrieveTraksi = $this->db->query("select * from gbm_organisasi where id=" . $traksi_id . "")->row_array();



		$d1 = new DateTime($tgl_mulai);
		$d2 = new DateTime($tgl_akhir);
		$interval = $d1->diff($d2);
		$jumlah_hari = $interval->days;

		$q0 = "SELECT a.*,b.nama AS jenis FROM trk_kendaraan a INNER JOIN trk_jenis_traksi b
		ON a.jenis_id=b.id 
		where traksi_id=" . $traksi_id . "";
		$q0 =	$q0 . " order by b.nama";
		$arrhd = $this->db->query($q0)->result_array();
		$no = 0;
		$strNo = '';

		$this->load->helper("terbilang");
		$this->load->helper("antech_helper");
		$this->load->library('pdfgenerator');
		if ($format_laporan == 'pdf') {
			$html = get_header_pdf_report();
		} else {
			$html = get_header_report_v2();
		}
		$html = $html . '
		<div class="row">
		<div class="span12">
			<br>
		  <div class="kop-print">
		  <div class="kop-nama">PT. ANNAJAH TECHNOLOGY INDONESIA</div>
		  <div class="kop-info"> Komplek Vila Sehati B12 , Tapos - Depok</div>
		  <div class="kop-info">Telp : 081387373939</div>
			</div>
			<hr class="kop-print-hr">
		</div>
	  </div>
			  <h3 class="title">Laporan Rekap Pemakain BBM</h3>
		<table class="no_border" style="width:30%">
				  
				  <tr>
						  <td>Traksi</td>
						  <td>:</td>
						  <td>' .  $retrieveTraksi['nama'] . '</td>
				  </tr>
				  <tr>	
						  <td>Periode</td>
						  <td>:</td>
						  <td>' . tgl_indo($tgl_mulai) . ' s/d ' . tgl_indo($tgl_akhir) . '</td>
				  </tr>
	  
				  
		  </table>
		  <br>';
		$html = $html . "	
		<table   border='1' width='100%' style='border-collapse: collapse;'>
		<thead>
		<tr>
			<th rowspan=3 >No</td>
			<th rowspan=3>Unit</th>
			<th rowspan=3>Kode</th>
			<th colspan=" . (($jumlah_hari + 1) * 2) . "  style='text-align: center'> Tanggal  </th>
			
			<th rowspan=2 colspan=2 style='text-align: center'>Jumlah</th>
		</tr>
		";
		$html = $html . "<tr>";
		while ($d1 <= $d2) {
			$ddmm = $d1->format('d-m');
			$dd = $d1->format('d');
			// $html = $html . "<td colspan='2' style='text-align: center'>" . $ddmm . "</td>";
			$html = $html . "<th colspan='2' style='text-align: center'>" . $dd . "</th>";
			$d1->modify('+1 day');
		}
		$html = $html . "</tr>";
		$d1 = new DateTime($tgl_mulai);
		while ($d1 <= $d2) {
			$ddmm = $d1->format('d-m');
			$html = $html . "<th style='text-align: center'>Qty</th>";
			$html = $html . "<th style='text-align: center'>Nilai (Rp)</th>";
			$d1->modify('+1 day');
		}
		$html = $html . "<th style='text-align: center'>Qty</th>";
		$html = $html . "<th style='text-align: center'>Nilai (Rp)</th>";
		$html = $html . "</tr> </thead>";
		$qtyPerHari = array();
		$qtyPerHari = [];
		$nilaiPerHari = array();
		$nilaiPerHari = [];
		$totalNilai = 0;
		$totalQty = 0;
		foreach ($arrhd as $hd) {
			$no++;
			//$actual_link ='http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
			$actual_link = "http://$_SERVER[HTTP_HOST]" . "/klinik_api/api/GlobalReport/trk_pemakaian_inventory/" . $hd['id'] . "/" . $tgl_mulai . "/" . $tgl_akhir .   "";
			$html = $html . "<tr>";
			$html = $html . "<td style='text-align: center'>" . $no . "</td>";
			$html = $html . "<td style='text-align: left'> <a href='" . $actual_link . "" . "' target='_blank'> " . $hd['nama'] . " </a></td>";
			$html = $html . "<td style='text-align: left'> <a href='" . $actual_link . "" . "' target='_blank'> " . $hd['kode'] . " </a></td>";
			$d1 = new DateTime($tgl_mulai);
			$jum_qty = 0;
			$jum_nilai = 0;
			while ($d1 <= $d2) {
				$tgl = $d1->format('Y-m-d');
				/* ngambil data biaya dann qty per periode */
				$qBiaya = "SELECT SUM(qty_keluar)AS qty ,SUM(nilai_keluar)AS nilai ,kendaraan_id 
				FROM inv_transaksi_harian 
				WHERE item_id=9 AND tipe='PEMAKAIAN'
				and kendaraan_id=" . $hd['id'] . "
				and tanggal ='" . $tgl . "' 
				GROUP BY kendaraan_id;";
				$resBiaya = $this->db->query($qBiaya)->row_array();
				$jum_qty = $jum_qty + $resBiaya['qty'];
				$jum_nilai = $jum_nilai + $resBiaya['nilai'];
				$totalQty = $totalQty + $resBiaya['qty'];
				$totalNilai = $totalNilai + $resBiaya['nilai'];

				$html = $html . "<td style='text-align: right'>" . number_format($resBiaya['qty']) . " </td>";
				$html = $html . "<td style='text-align: right'> " . number_format($resBiaya['nilai']) . " </td>";
				$yymmdd = $d1->format('Ymd');
				if (array_key_exists($yymmdd, $nilaiPerHari)) {
					$nilaiPerHari[$yymmdd] = $nilaiPerHari[$yymmdd] + $resBiaya['nilai'];
				} else {
					$nilaiPerHari[$yymmdd] = $resBiaya['nilai'];
				}
				if (array_key_exists($yymmdd, $qtyPerHari)) {
					$qtyPerHari[$yymmdd] = $qtyPerHari[$yymmdd] + $resBiaya['qty'];
				} else {
					$qtyPerHari[$yymmdd] = $resBiaya['qty'];
				}
				$d1->modify('+1 day');
			}
			$html = $html . "<td style='text-align: right'>" . number_format($jum_qty) . " </td>";
			$html = $html . "<td style='text-align: right'>" . number_format($jum_nilai) . " </td>";
			$html = $html . "</tr>";
		}

		$html = $html . "<tr>";
		$html = $html . "<td style='text-align: center'> </td>";
		$html = $html . "<td style='text-align: center'></td>";
		$html = $html . "<td style='text-align: center'></td>";

		$d1 = new DateTime($tgl_mulai);
		while ($d1 <= $d2) {
			$yymmdd = $d1->format('Ymd');
			$html = $html . "<td style='text-align: right'><b>" . number_format($qtyPerHari[$yymmdd]) . " </b></td>";
			$html = $html . "<td style='text-align: right'><b>" . number_format($nilaiPerHari[$yymmdd]) . "</b> </td>";
			$d1->modify('+1 day');
		}
		$html = $html . "<td style='text-align: right'><b>" . number_format($totalQty) . " </b> </td>";
		$html = $html . "<td style='text-align: right'><b>" . number_format($totalNilai) . " </b> </td>";
		$html = $html . "</tr>";
		$html = $html . "</table>";

		if ($format_laporan == 'xls') {
			// $reader = new \PhpOffice\PhpSpreadsheet\Reader\Html;
			// $spreadsheet = $reader->loadFromString($html);
			// // $reader->setSheetIndex(1);
			// //$spreadhseet = $reader->loadFromString($secondHtmlString, $spreadsheet);
			// $objWriter = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
			// header("Pragma: public");
			// header("Expires: 0");
			// header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
			// header("Content-Type: application/force-download");
			// header("Content-Type: application/octet-stream");
			// header("Content-Type: application/download");
			// header("Content-Disposition: attachment;filename=test.xlsx");
			// header("Content-Transfer-Encoding: binary ");

			// ob_end_clean();
			// ob_start();
			// $objWriter->save('php://output');
			echo $html;
		} else if ($format_laporan == 'view') {
			echo $html;
		} else {
			$filename = 'report_' . time();
			$this->pdfgenerator->generate($html, $filename, true, 'A4', 'landscape');
		}
	}
	function laporan_cost_workshop_rekap_post()
	{

		error_reporting(0);
		$workshop_id     = $this->post('workshop_id', true);
		$tgl_mulai =  $this->post('tgl_mulai', true);
		$tgl_akhir = $this->post('tgl_akhir', true);
		// $blok_id = $this->post('blok_id', true);
		$format_laporan     = $this->post('format_laporan', true);


		$retrieveWorkshop = $this->db->query("select * from gbm_organisasi where id=" . $workshop_id . "")->row_array();
		//$retrieveBlok = $this->db->query("select * from gbm_organisasi where id=" . $blok_id . "")->row_array();

		$retrieveCost = $this->db->query("SELECT tanggal,kode_workshop,nama_workshop,
		SUM(debet-kredit)AS total,
		sum(CASE WHEN jenis = 'UPAH' THEN (debet-kredit) else 0 end) as upah,
			sum(CASE WHEN jenis = 'BAHAN' THEN (debet-kredit) else 0 end) as bahan,
				sum(CASE WHEN jenis = '' THEN (debet-kredit) else 0 end) as lainnya
		FROM wrk_cost_vw
			where workshop_id='" . $workshop_id . "' 
			and tanggal>='" . $tgl_mulai . "' and tanggal<='" . $tgl_akhir . "'
			and kredit<=0
			GROUP BY  tanggal,kode_workshop,nama_workshop		
			 order by kode_workshop,tanggal")->result_array();

		// var_dump($results);
		$this->load->helper("antech_helper");
		$this->load->library('pdfgenerator');
		if ($format_laporan == 'pdf') {
			$html = get_header_pdf_report();
		} else {
			$html = get_header_report_v2();
		}


		$html = $html . '
		<div class="row">
		<div class="span12">
			<br>
		  <div class="kop-print">
		  <div class="kop-nama">PT. ANNAJAH TECHNOLOGY INDONESIA</div>
		  <div class="kop-info"> Komplek Vila Sehati B12 , Tapos - Depok</div>
		  <div class="kop-info">Telp : 081387373939</div>
			</div>
			<hr class="kop-print-hr">
		</div>
	  </div>
			  <h3 class="title">Laporan Rekap Cost Workshop</h3>
		<table class="no_border" style="width:30%">
				  
				  <tr>
						  <td>Traksi</td>
						  <td>:</td>
						  <td>' .  $retrieveWorkshop['nama'] . '</td>
				  </tr>
				  <tr>	
						  <td>Periode</td>
						  <td>:</td>
						  <td>' . tgl_indo($tgl_mulai) . ' s/d ' . tgl_indo($tgl_akhir) . '</td>
				  </tr>
	  
				  
		  </table>
		  <br>';

		$html = $html . ' <table  >
			<thead>
				<tr>
				<th width="4%">No.</th>			
				<th>Workshop</th>
				<th>Tanggal</th>
				<th style="text-align: center;">Upah (Rp) </th>
				<th style="text-align: center;">Bahan/Spare Part (Rp) </th>
				<th style="text-align: center;">Lainnya (Rp) </th>
				<th style="text-align: center;">Total(Rp) </th>
				
				</tr>
			</thead>
			<tbody>';


		$no = 0;
		$bahan = 0;
		$lainnya = 0;
		$upah = 0;
		$total = 0;


		foreach ($retrieveCost as $key => $m) {
			$no++;
			$upah = $upah + $m['upah'];
			$bahan = $bahan + $m['bahan'];
			$lainnya = $lainnya + $m['lainnya'];
			$total = $total + $m['total'];
			$html = $html . ' 	<tr class=":: arc-content">
						<td style="position:relative; text-align: center;">	' . ($no) . '</td>
						<td>
						' . $m['nama_workshop'] . ' 
						
						</td>
						<td style="text-align: center;">
						' . tgl_indo_normal($m['tanggal']) . ' 
						
						</td>
											
						<td style="text-align: right;">' . $this->format_number_report($m['upah']) . ' 
						<td style="text-align: right;">' . $this->format_number_report($m['bahan']) . ' 
						<td style="text-align: right;">' . $this->format_number_report($m['lainnya']) . ' 
						<td style="text-align: right;">' . $this->format_number_report($m['total']) . ' 
													
						</td>';

			$html = $html . '</tr>';
		}

		$html = $html . ' 	
						<tr class=":: arc-content">
						<td style="position:relative;">
							&nbsp;

						</td>
						
						<td>
							&nbsp;
						</td>
						<td>
							&nbsp;
						</td>
						
					
						<td style="text-align: right;">
						' . $this->format_number_report($upah) . ' 
						</td>
						<td style="text-align: right;">
						' . $this->format_number_report($bahan) . ' 
						</td>
						<td style="text-align: right;">
						' . $this->format_number_report($lainnya) . ' 
						</td>
						<td style="text-align: right;">
						' . $this->format_number_report($total) . ' 
						</td>
						
						
						</tr>
								</tbody>
							</table>
						';
		if ($format_laporan == 'xls') {
			$reader = new \PhpOffice\PhpSpreadsheet\Reader\Html;
			$spreadsheet = $reader->loadFromString($html);
			// $reader->setSheetIndex(1);
			//$spreadhseet = $reader->loadFromString($secondHtmlString, $spreadsheet);
			$objWriter = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
			header("Pragma: public");
			header("Expires: 0");
			header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
			header("Content-Type: application/force-download");
			header("Content-Type: application/octet-stream");
			header("Content-Type: application/download");
			header("Content-Disposition: attachment;filename=test.xlsx");
			header("Content-Transfer-Encoding: binary ");

			ob_end_clean();
			ob_start();
			$objWriter->save('php://output');
		} else if ($format_laporan == 'view') {
			echo $html;
		} else {
			$filename = 'report_' . time();
			$this->pdfgenerator->generate($html, $filename, true, 'A4', 'landscape');
		}
	}
	function laporan_cashflow_post()
	{
		error_reporting(0);

		// $mill_id     = $this->post('mill_id', true);
		$periode =  $this->post('periode', true);
		$format_laporan     = $this->post('format_laporan', true);

		$date = new DateTime($periode . '-01');
		$date->modify('last day of this month');
		$last_day_this_month = $date->format('Y-m-d');
		(int)$jumhari = date('d', strtotime($last_day_this_month));
		$tgl_mulai = $periode . '-01';
		$tgl_akhir = $periode . '-' . sprintf("%02d", $jumhari);

		$akunnabank_id = 2865; // BNI CBD a/c 5577779989
		$arrBank = array();
		$query1 = "SELECT b.acc_akun_id,sum(debet)as debet,sum(kredit)as kredit FROM acc_jurnal_ht a inner join acc_jurnal_dt b 
				on a.id=b.jurnal_id
				where b.acc_akun_id=" . $akunnabank_id . " 
				and a.tanggal >= '" . $tgl_mulai . "'  and a.tanggal <= '" . $tgl_akhir . "'
				group by b.acc_akun_id ;";
		$retrieveBank = $this->db->query($query1)->result_array();

		foreach ($retrieveBank as $key => $bank) {
			$arrBank[$bank['tgl']] = $bank['nilai'];
		}


		$retrieveSupp = $this->db->query("SELECT * from gbm_supplier order by kode_supplier")->result_array();
		$arrSupp = array();
		foreach ($retrieveSupp as $key => $supp) {
			$arrSupp[$supp['kode_supplier']] = $supp;
		}

		$retrieveAp = $this->db->query("SELECT b.kode_supplier,a.supplier_id,SUM(nilai_invoice)as nilai,DAY(tanggal_tempo)AS tgl 
		FROM acc_ap_invoice_ht a INNER JOIN gbm_supplier b
		ON a.supplier_id=b.id 
		where DATE_FORMAT(tanggal_tempo, '%Y-%m') ='" . $periode . "'
		GROUP BY a.supplier_id,b.kode_supplier,DAY(tanggal_tempo)
		 order by b.nama_supplier")->result_array();
		$arrAp = array();
		$arrSupplier = array();
		foreach ($retrieveAp as $key => $ap) {
			$arrSupplier[$ap['kode_supplier']] = $arrSupp[$ap['kode_supplier']];
			$arrAp[$ap['kode_supplier']][$ap['tgl']] = $ap['nilai'];
		}


		$this->load->helper("antech_helper");
		$this->load->library('pdfgenerator');
		if ($format_laporan == 'pdf') {
			$html = get_header_pdf_report();
		} else {
			$html = get_header_report_v2();
		}
		$html = $html . '<div class="row">
		<div class="span12">
			<br>
		  <div class="kop-print">
		  <div class="kop-nama">PT. ANNAJAH TECHNOLOGY INDONESIA</div>
		  <div class="kop-info"> Komplek Vila Sehati B12 , Tapos - Depok</div>
		  <div class="kop-info">Telp : 081387373939</div>
		</div>
			<hr class="kop-print-hr">
		</div>
		</div>
		<h3 class="title">LAPORAN CASH FLOW</h3>
		<table class="no_border" style="width:30%">
				  
				
				  <tr>	
						  <td>Periode</td>
						  <td>:</td>
						  <td>' . $tgl_mulai . ' s/d ' . $tgl_akhir . '</td>
				  </tr>
	  
				  
		  </table>
		  <br>

';

		$html = $html . "

<table   border='1' width='100%' style='border-collapse: collapse;'>
<thead>
<tr>
	<td rowspan=2>Keterangan</td>
	<td colspan=" . $jumhari . "  style='text-align: center'> " . $periode . "  </td>
	<td rowspan=2  style='text-align: center'>Nilai</td>
</tr>
";
		$html = $html . "<tr>";

		for ($i = 1; $i < ($jumhari + 1); $i++) {
			$html = $html . "<td style='text-align: center'>" . $i . "</td>";
		}
		$html = $html . "</tr> </thead>";


		/*  bank BNI */
		$html = $html . "<tr>";
		$html = $html . "<td style='text-align: left'>BNI</td>";
		for ($i = 1; $i < ($jumhari + 1); $i++) {
			$nilai = $arrBank[$i];
			$html = $html . "<td style='text-align: center'>" . $this->format_number_report($nilai) . " </td>";
		}
		$html = $html . "<td style='text-align: center'>" . $this->format_number_report(0) . " </td>";
		$html = $html . "</tr>";

		$html = $html . "<tr>";
		$nourut = 0;
		$grandtotal = 0;
		$totalPerHari = array();
		$totalPerHari = [];

		/* looping supplier (AP) */
		foreach ($arrSupplier as $key => $s) {
			$html = $html . "<tr>";
			$html = $html . "<td style='text-align: left'>" . $s['nama_supplier'] . "</td>";
			$totalperSupp = 0;
			for ($i = 1; $i < ($jumhari + 1); $i++) {
				$totalPerHari[] = 0;


				$nourut = $nourut + 1;
				$nilai = $arrAp[$key][$i];
				if (array_key_exists($i, $totalPerHari)) {
					$totalPerHari[$i - 1] = $totalPerHari[$i - 1] + $nilai;
				} else {
					$totalPerHari[] = $nilai;
				}

				$totalperSupp = $totalperSupp + $nilai;
				$grandtotal = $grandtotal + $nilai;
				$html = $html . "<td style='text-align: center'>" . $this->format_number_report($nilai) . " </td>";
			}
			$html = $html . "<td style='text-align: center'>" . $this->format_number_report($totalperSupp) . " </td>";
			$html = $html . "</tr>";
		}


		$html = $html . "<tr>";
		$html = $html . "<td style='text-align: right'></td>";
		for ($i = 1; $i < ($jumhari + 1); $i++) {

			$html = $html . "<td style='text-align: right'>" . $this->format_number_report($totalPerHari[$i - 1]) . " </td>";
		}
		$html = $html . "<td style='text-align: right'>" . $this->format_number_report($grandtotal) . " </td>";
		$html = $html . "</tr>";
		$html = $html . "</table>";

		if ($format_laporan == 'xls') {
			$reader = new \PhpOffice\PhpSpreadsheet\Reader\Html;
			$spreadsheet = $reader->loadFromString($html);
			// $reader->setSheetIndex(1);
			//$spreadhseet = $reader->loadFromString($secondHtmlString, $spreadsheet);
			$objWriter = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
			header("Pragma: public");
			header("Expires: 0");
			header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
			header("Content-Type: application/force-download");
			header("Content-Type: application/octet-stream");
			header("Content-Type: application/download");
			header("Content-Disposition: attachment;filename=test.xlsx");
			header("Content-Transfer-Encoding: binary ");

			ob_end_clean();
			ob_start();
			$objWriter->save('php://output');
		} else if ($format_laporan == 'view') {
			echo $html;
		} else {
			$filename = 'report_' . time();
			$this->pdfgenerator->generate($html, $filename, true, 'A4', 'landscape');
		}
	}
	function format_number_report($angka)
	{
		$format_laporan     = $this->post('format_laporan', true);
		// if ($fmt_laporan ) {
		// 	$format_laporan     = $this->post('format_laporan', true);
		// }else{
		// 	return number_format($angka);
		// }
		if ($format_laporan == 'xls') {
			return $angka;
		} else {
			if ($angka == 0) {
				return '';
			}
			return number_format($angka, 2);
		}
	}
}
